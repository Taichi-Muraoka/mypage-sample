<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Models\ExtStudentKihon;
use App\Models\ExtRoom;
use App\Models\ExtGenericMaster;
use App\Models\ExtRegular;
use App\Models\ExtHomeTeacherStd;
use App\Models\ExtExtraIndDetail;
use App\Models\ExtExtraIndividual;
use App\Models\Account;
use App\Models\ExtRirekisho;
use App\Models\ExtRegularDetail;
use App\Models\ExtHomeTeacherStdDetail;
use App\Models\CodeMaster;
use App\Http\Controllers\Traits\CtrlResponseTrait;

/**
 * 契約内容 - 機能共通処理
 */
trait FuncAgreementTrait
{
    // 応答共通処理
    use CtrlResponseTrait;

    /**
     * 生徒の契約内容を取得する
     * 
     * @param integer $sid 生徒ID
     */
    private function getStudentAgreement($sid)
    {

        // 生徒の基本情報を取得する
        $query = ExtStudentKihon::query();
        $student = $query
            ->select(
                'sid',
                'name',
                'ext_generic_master.name1 AS cls_name',
                'accounts.email'
            )
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'ext_student_kihon.cls_cd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_000_112);
            })
            ->sdLeftJoin(Account::class, function ($join) {
                $join->on('accounts.account_id', '=', 'ext_student_kihon.sid')
                    ->where('accounts.account_type', '=', AppConst::CODE_MASTER_7_1);
            })
            ->where('ext_student_kihon.sid', '=', $sid)
            ->firstOrFail();

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // 生徒No.から所属教室を取得する。
        $query = ExtRoom::query();
        $rooms = $query
            ->select(
                'sid',
                'roomcd',
                'room_name'
            )
            ->where('ext_room.sid', '=', $sid)
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('ext_room.roomcd', '=', 'room_names.code');
            })
            ->orderByRaw('CAST(ext_room.roomcd AS signed) asc')
            ->get();

        // 複数教室所属の場合を考慮し、教室名を連結する
        $str_room_names = "";
        foreach ($rooms as $room) {
            $str_room_names = $str_room_names . " " . $room->room_name;
        };
        $str_room_names = ltrim($str_room_names);

        // 当年度（の4月1日）を取得する。
        $fiscal_start_date = $this->dtGetFiscalDate("present", "start");
        // 当年度（の3月31日）を取得する。
        $fiscal_end_date = $this->dtGetFiscalDate("present", "end");

        // 規定情報を取得する。
        $query = ExtRegular::query();
        $regular = $query
            ->select(
                'roomcd',
                'sid',
                'r_seq',
                'startdate',
                'enddate',
                'regular_summary',
                'tuition'
            )
            // 生徒IDの指定
            ->where('ext_regular.sid', '=', $sid)
            // 当年度のデータを取得
            // 期間の絞り込み条件を修正
            // 契約開始日が年度終了日以前 かつ 契約終了日が年度開始日以後
            ->where('ext_regular.startdate', '<=', $fiscal_end_date)
            ->where('ext_regular.enddate', '>=', $fiscal_start_date)
            ->orderBy('ext_regular.startdate', 'desc')
            ->get();

        // 家庭教師標準情報を取得する。
        $query = ExtHomeTeacherStd::query();
        $home_teacher_std = $query
            ->select(
                'roomcd',
                'sid',
                'std_seq',
                'startdate',
                'enddate',
                'std_summary',
                'tuition'
            )
            // 生徒IDの指定
            ->where('ext_home_teacher_std.sid', '=', $sid)
            // 当年度のデータを取得
            // 期間の絞り込み条件を修正
            // 契約開始日が年度終了日以前 かつ 契約終了日が年度開始日以後
            ->where('ext_home_teacher_std.startdate', '<=', $fiscal_end_date)
            ->where('ext_home_teacher_std.enddate', '>=', $fiscal_start_date)
            ->orderBy('ext_home_teacher_std.startdate', 'desc')
            ->get();

        // 短期個別講習情報明細から生徒No.に紐づく当年度内の授業を全て取得する
        $i_seqs = $this->getExtraIndDetailSeqs($sid);

        // 短期個別講習を取得する
        $query = ExtExtraIndividual::query();
        $extra_individual = $query
            ->select(
                'roomcd',
                'sid',
                'i_seq',
                'name',
                'price',
                'room_name'
            )
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('ext_extra_individual.roomcd', '=', 'room_names.code');
            })
            ->where('ext_extra_individual.sid', '=', $sid)
            ->whereIn('ext_extra_individual.i_seq', $i_seqs)
            ->orderBy('ext_extra_individual.i_seq', 'desc')
            ->get();

        return [
            'student' => $student,
            'roomcds' => $str_room_names,
            'regular' => $regular,
            'home_teacher_std' => $home_teacher_std,
            'extra_individual' => $extra_individual
        ];
    }

    /**
     * 生徒の規定情報を取得する
     * 
     * @param integer $sid 生徒ID
     * @param integer $roomcd 教室コード
     * @param integer $seq シーケンス
     */
    private function getStudentRegular($sid, $roomcd, $seq)
    {
        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // 当年度（の4月1日）を取得する。
        $fiscal_start_date = $this->dtGetFiscalDate("present", "start");
        // 当年度（の3月31日）を取得する。
        $fiscal_end_date = $this->dtGetFiscalDate("present", "end");

        $query = ExtRegular::query();
        $regular = $query
            ->select(
                'startdate',
                'enddate',
                'regular_summary',
                'tuition',
                'room_name'
            )
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('ext_regular.roomcd', '=', 'room_names.code');
            })
            // キー項目で指定
            ->where('ext_regular.sid', '=', $sid)
            ->where('ext_regular.roomcd', '=', $roomcd)
            ->where('ext_regular.r_seq', '=', $seq)
            // [ガード] 当年度のデータを取得(古いseqを指定されても見えないようにする)
            // 期間の絞り込み条件を修正
            // 契約開始日が年度終了日以前 かつ 契約終了日が年度開始日以後
            ->where('ext_regular.startdate', '<=', $fiscal_end_date)
            ->where('ext_regular.enddate', '>=', $fiscal_start_date)
            ->firstOrFail();

        $query = ExtRegularDetail::query();
        $regular_details = $query
            ->select(
                'start_time',
                'r_minutes',
                'r_count',
                'ext_rirekisho.name AS teacher_name',
                'ext_generic_master.name1 AS curriculum_name',
                'mst_codes.name AS weekday'
            )
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('ext_rirekisho.tid', '=', 'ext_regular_detail.tid');
            })
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_codes.code', '=', 'ext_regular_detail.weekdaycd')
                    ->where('mst_codes.data_type', '=', AppConst::CODE_MASTER_16);
            })
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'ext_regular_detail.curriculumcd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_000_114);
            })
            ->where('ext_regular_detail.sid', '=', $sid)
            ->where('ext_regular_detail.roomcd', '=', $roomcd)
            ->where('ext_regular_detail.r_seq', '=', $seq)
            ->orderBy('ext_regular_detail.rd_seq', 'asc')
            ->get();

        return [
            'dtl_room_name' => $regular->room_name,
            'dtl_startdate' => $regular->startdate,
            'dtl_enddate' => $regular->enddate,
            'dtl_regular_summary' => $regular->regular_summary,
            'dtl_tuition' => $regular->tuition,
            'regular_details' => $regular_details
        ];
    }

    /**
     * 生徒の家庭教師標準情報を取得する
     * 
     * @param integer $sid 生徒ID
     * @param integer $roomcd 教室コード
     * @param integer $seq シーケンス
     */
    private function getStudentHomeTeacherStd($sid, $roomcd, $seq)
    {

        // 当年度（の4月1日）を取得する。
        $fiscal_start_date = $this->dtGetFiscalDate("present", "start");
        // 当年度（の3月31日）を取得する。
        $fiscal_end_date = $this->dtGetFiscalDate("present", "end");

        $query = ExtHomeTeacherStd::query();
        $home_teacher_std = $query
            ->select(
                'startdate',
                'enddate',
                'std_summary',
                'tuition'
            )
            // キー項目で指定
            ->where('ext_home_teacher_std.sid', '=', $sid)
            ->where('ext_home_teacher_std.roomcd', '=', $roomcd)
            ->where('ext_home_teacher_std.std_seq', '=', $seq)
            // [ガード] 当年度のデータを取得(古いseqを指定されても見えないようにする)
            // 期間の絞り込み条件を修正
            // 契約開始日が年度終了日以前 かつ 契約終了日が年度開始日以後
            ->where('ext_home_teacher_std.startdate', '<=', $fiscal_end_date)
            ->where('ext_home_teacher_std.enddate', '>=', $fiscal_start_date)
            ->firstOrFail();

        $query = ExtHomeTeacherStdDetail::query();
        $home_teacher_std_details = $query
            ->select(
                'std_minutes',
                'std_count',
                'ext_rirekisho.name AS teacher_name',
            )
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('ext_rirekisho.tid', '=', 'ext_home_teacher_std_detail.tid');
            })
            ->where('ext_home_teacher_std_detail.sid', '=', $sid)
            ->where('ext_home_teacher_std_detail.roomcd', '=', $roomcd)
            ->where('ext_home_teacher_std_detail.std_seq', '=', $seq)
            ->orderBy('ext_home_teacher_std_detail.std_dtl_seq', 'asc')
            ->get();

        return [
            'dtl_startdate' => $home_teacher_std->startdate,
            'dtl_enddate' => $home_teacher_std->enddate,
            'dtl_std_summary' => $home_teacher_std->std_summary,
            'dtl_tuition' => $home_teacher_std->tuition,
            'home_teacher_std_details' => $home_teacher_std_details
        ];
    }

    /**
     * 生徒の短期個別講習を取得する
     * 
     * @param integer $sid 生徒ID
     * @param integer $roomcd 教室コード
     * @param integer $seq シーケンス
     */
    private function getStudentExtraIndividual($sid, $roomcd, $seq)
    {

        //---------------------------------
        // 指定されたseqが参照できるかチェック
        //---------------------------------

        // 短期個別講習情報明細から生徒No.に紐づく当年度内の授業を全て取得する
        $i_seqs = $this->getExtraIndDetailSeqs($sid);

        // 存在しない場合はエラー
        if (!in_array($seq, $i_seqs)) {
            $this->illegalResponseErr();
        }

        //-------------------
        // データの取得
        //-------------------

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        $query = ExtExtraIndividual::query();
        $extra_individual = $query
            ->select(
                'name',
                'price',
                'room_name'
            )
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('ext_extra_individual.roomcd', '=', 'room_names.code');
            })
            ->where('ext_extra_individual.sid', '=', $sid)
            ->where('ext_extra_individual.roomcd', '=', $roomcd)
            ->where('ext_extra_individual.i_seq', '=', $seq)
            ->firstOrFail();

        $query = ExtExtraIndDetail::query();
        $extra_ind_details = $query
            ->select(
                'extra_date',
                'r_minutes',
                'start_time',
                'ext_rirekisho.name AS teacher_name',
                'ext_generic_master.name1 AS curriculum_name'
            )
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('ext_rirekisho.tid', '=', 'ext_extra_ind_detail.tid');
            })
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'ext_extra_ind_detail.curriculumcd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_000_114);
            })
            ->where('ext_extra_ind_detail.sid', '=', $sid)
            ->where('ext_extra_ind_detail.roomcd', '=', $roomcd)
            ->where('ext_extra_ind_detail.i_seq', '=', $seq)
            ->orderBy('ext_extra_ind_detail.extra_date', 'asc')
            ->orderBy('ext_extra_ind_detail.period_no', 'asc')
            ->get();

        return [
            'dtl_room_name' => $extra_individual->room_name,
            'dtl_name' => $extra_individual->name,
            'dtl_price' => $extra_individual->price,
            'extra_ind_details' => $extra_ind_details
        ];
    }

    /**
     * 短期個別講習情報明細から生徒No.に紐づく当年度内の授業を全て取得する
     * 
     * @param int $sid 生徒ID
     * @return array seqの配列
     */
    private function getExtraIndDetailSeqs($sid)
    {
        // 当年度（の4月1日）を取得する。
        $fiscal_start_date = $this->dtGetFiscalDate("present", "start");
        // 当年度（の3月31日）を取得する。
        $fiscal_end_date = $this->dtGetFiscalDate("present", "end");

        // 短期個別講習情報明細から生徒No.に紐づく当年度内の授業を全て取得する
        $query = ExtExtraIndDetail::query();
        $extra_ind_details = $query
            ->select(
                'sid',
                'i_seq',
                'extra_date'
            )
            // 生徒IDの指定
            ->where('ext_extra_ind_detail.sid', '=', $sid)
            // 当年度のデータを取得
            ->whereBetween('ext_extra_ind_detail.extra_date', [$fiscal_start_date, $fiscal_end_date])
            ->get();

        // 当年度内の全てのコマから個別講習連番を抽出する
        $i_seqs = [];
        foreach ($extra_ind_details as $extra_ind_detail) {
            array_push($i_seqs, $extra_ind_detail->i_seq);
        }
        $i_seqs = array_unique($i_seqs);

        return $i_seqs;
    }
}
