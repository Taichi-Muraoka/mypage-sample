<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\CodeMaster;
use App\Models\MstSystem;
use App\Models\Surcharge;
use App\Models\Tutor;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

/**
 * 追加請求 - 機能共通処理
 */
trait FuncSurchargeTrait
{
    //==========================
    // 講師・管理者共通
    //==========================
    /**
     * 一覧を取得
     */
    private function getSurchargeList(?Request $request)
    {
        // 本部ありで校舎名を取得する用のクエリ 後述使用
        $campus_names = $this->mdlGetRoomQuery();

        // クエリ作成
        $query = Surcharge::query();
        $query->select(
            'surcharges.surcharge_id',
            'surcharges.tutor_id',
            'surcharges.apply_date',
            'surcharges.surcharge_kind',
            'surcharges.minutes',
            'surcharges.tuition',
            'surcharges.approval_status',
            'surcharges.payment_date',
            'surcharges.payment_status',
            // 校舎の名称（本部あり）
            'campus_names.room_name as campus_name',
            // 講師の名称
            'tutors.name as tutor_name',
            // コードマスタの名称（請求種別）
            'mst_codes_26.name as surcharge_kind_name',
            // コードマスタの名称（承認ステータス）
            'mst_codes_2.name as approval_status_name',
            // コードマスタの名称（支払状況）
            'mst_codes_27.name as payment_status_name',
        )
            // 校舎名の取得JOIN
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('surcharges.campus_cd', '=', 'campus_names.code');
            })
            // 講師情報とJOIN
            ->sdLeftJoin(Tutor::class, 'surcharges.tutor_id', '=', 'tutors.tutor_id')
            // コードマスターとJOIN 請求種別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('surcharges.surcharge_kind', '=', 'mst_codes_26.code')
                    ->where('mst_codes_26.data_type', AppConst::CODE_MASTER_26);
            }, 'mst_codes_26')
            // コードマスターとJOIN 承認ステータス
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('surcharges.approval_status', '=', 'mst_codes_2.code')
                    ->where('mst_codes_2.data_type', AppConst::CODE_MASTER_2);
            }, 'mst_codes_2')
            // コードマスターとJOIN 支払状況
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('surcharges.payment_status', '=', 'mst_codes_27.code')
                    ->where('mst_codes_27.data_type', AppConst::CODE_MASTER_27);
            }, 'mst_codes_27');

        // ログイン者によってボタン押下制御・ガード・ソート順を分岐
        if (AuthEx::isAdmin()) {
            // 管理者の場合
            // 承認ステータス「承認待ち」のコードを取得
            $statusCd = AppConst::CODE_MASTER_2_0;

            // 承認ステータス「承認待ち」に該当しない場合、trueをセットする（承認・更新不可）
            $query->selectRaw(
                "CASE
                    WHEN approval_status != $statusCd THEN true
                END AS disabled_btn"
            );

            // 校舎の絞り込み条件
            if (AuthEx::isRoomAdmin()) {
                // 教室管理者の場合、自分の教室コードのみにガードを掛ける
                $query->where($this->guardRoomAdminTableWithRoomCd());
            } else {
                // 本部管理者の場合検索フォームから取得
                $query->SearchCampusCd($request);
            }

            // 講師の絞り込み条件
            $query->SearchTutorId($request);
            // 請求種別の絞り込み条件
            $query->SearchSurchargeKind($request);
            // ステータスの絞り込み条件
            $query->SearchApprovalStatus($request);
            // 支払状況の絞り込み条件
            $query->SearchPaymentStatus($request);
            // 申請日の絞り込み条件
            $query->SearchApplyDateFrom($request);
            $query->SearchApplyDateTo($request);

            $query->orderBy('apply_date', 'desc')
                ->orderBy('working_date', 'desc');
        }
        if (AuthEx::isTutor()) {
            // 講師の場合
            // 承認ステータス「差戻し」のコードを取得
            $remandCd = AppConst::CODE_MASTER_2_2;

            // 承認ステータス「差戻し」に該当しない場合、trueをセットする（更新不可）
            $query->selectRaw(
                "CASE
                    WHEN approval_status != $remandCd THEN true
                END AS disabled_btn"
            )
                // 自分の講師IDのみにガードを掛ける
                ->where($this->guardTutorTableWithTid())
                ->orderBy('apply_date', 'desc');
        }

        return $query;
    }

    /**
     * 詳細モーダルを取得
     */
    private function getSurchargeDetail($surchargeId)
    {
        // 本部ありで校舎名を取得する用のクエリ 後述使用
        $campus_names = $this->mdlGetRoomQuery();

        // クエリを作成
        $query = Surcharge::query();

        if (AuthEx::isAdmin()) {
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        }
        if (AuthEx::isTutor()) {
            // 講師の場合、自分の講師IDのみにガードを掛ける
            $query->where($this->guardTutorTableWithTid());
        }

        $surcharge = $query->select(
            'surcharges.tutor_id',
            'surcharges.apply_date',
            'surcharges.surcharge_kind',
            'surcharges.working_date',
            'surcharges.start_time',
            'surcharges.minutes',
            'surcharges.tuition',
            'surcharges.comment',
            'surcharges.approval_status',
            'surcharges.payment_date',
            'surcharges.payment_status',
            'surcharges.admin_comment',
            // 校舎の名称（本部あり）
            'campus_names.room_name as campus_name',
            // 講師の名称
            'tutors.name as tutor_name',
            // コードマスタのサブコード（請求種別）
            'mst_codes_26.sub_code',
            // コードマスタの名称（請求種別）
            'mst_codes_26.name as surcharge_kind_name',
            // コードマスタの名称（承認ステータス）
            'mst_codes_2.name as approval_status_name',
            // コードマスタの名称（支払状況）
            'mst_codes_27.name as payment_status_name',
        )
            // 校舎名の取得JOIN
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('surcharges.campus_cd', '=', 'campus_names.code');
            })
            // 講師情報とJOIN
            ->sdLeftJoin(Tutor::class, 'surcharges.tutor_id', '=', 'tutors.tutor_id')
            // コードマスターとJOIN 請求種別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('surcharges.surcharge_kind', '=', 'mst_codes_26.code')
                    ->where('mst_codes_26.data_type', AppConst::CODE_MASTER_26);
            }, 'mst_codes_26')
            // コードマスターとJOIN 承認ステータス
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('surcharges.approval_status', '=', 'mst_codes_2.code')
                    ->where('mst_codes_2.data_type', AppConst::CODE_MASTER_2);
            }, 'mst_codes_2')
            // コードマスターとJOIN 支払状況
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('surcharges.payment_status', '=', 'mst_codes_27.code')
                    ->where('mst_codes_27.data_type', AppConst::CODE_MASTER_27);
            }, 'mst_codes_27')
            // 詳細ボタン押下時に指定したIDで絞り込み
            ->where('surcharges.surcharge_id', $surchargeId)
            ->first();

        return $surcharge;
    }

    //==========================
    // 講師用
    //==========================
    /**
     * 対象データを取得
     * 編集・削除時に使用
     */
    private function getTargetSurchargeTutor($surchargeId)
    {
        // データを取得 (請求種別のサブコードも併せて取得する)
        $surcharge = Surcharge::select('surcharges.*', 'mst_codes.sub_code')
            // コードマスターとJOIN 請求種別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('surcharges.surcharge_kind', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_26);
            })
            ->where('surcharge_id', $surchargeId)
            // 自分の講師IDのみにガードを掛ける
            ->where($this->guardTutorTableWithTid())
            // ステータス「差戻し」のみ編集可能とする
            ->where('approval_status', AppConst::CODE_MASTER_2_2)
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        return $surcharge;
    }

    /**
     * データ保存
     */
    private function saveToSurchargeTutor($request, $surcharge)
    {
        // 共通保存項目
        $surcharge->campus_cd =  $request['campus_cd'];
        $surcharge->apply_date = Carbon::now();
        $surcharge->surcharge_kind =  $request['surcharge_kind'];
        $surcharge->working_date = $request['working_date'];
        $surcharge->comment = $request['comment'];
        $surcharge->approval_status = AppConst::CODE_MASTER_2_0;
        $surcharge->payment_date = null;
        $surcharge->payment_status = AppConst::CODE_MASTER_27_0;
        $surcharge->admin_comment = null;

        // 請求種別によって保存項目分岐
        if ($request['sub_code'] == AppConst::CODE_MASTER_26_SUB_8) {
            // 請求種別サブコード8 時給の場合
            // システムマスタ「事務作業給」を取得
            $workPay = MstSystem::where('key_id', AppConst::SYSTEM_KEY_ID_2)->first();

            // MEMO:保留
            // 時間(分)/60×事務作業給で金額計算
            $tuition = ($request['minutes'] / 60) * $workPay->value_num;

            // 金額は小数点を四捨五入した値を保存
            $surcharge->tuition = round($tuition);

            $surcharge->start_time = $request['start_time'];
            $surcharge->minutes = $request['minutes'];
        }
        if ($request['sub_code'] != AppConst::CODE_MASTER_26_SUB_8) {
            // 請求種別サブコード8以外 固定金額の場合
            $surcharge->tuition = $request['tuition'];
            $surcharge->start_time = null;
            $surcharge->minutes = null;
        }

        // 保存
        $surcharge->save();
    }

    //==========================
    // 管理者用
    //==========================
    /**
     * 対象データを取得
     * 承認・編集時に使用
     */
    private function getTargetSurchargeAdmin($surchargeId)
    {
        // 本部ありで校舎名を取得する用のクエリ 後述使用
        $campus_names = $this->mdlGetRoomQuery();

        // データ取得
        $query = Surcharge::query();
        $surcharge = $query->select(
            'surcharges.*',
            // 校舎の名称（本部あり）
            'campus_names.room_name as campus_name',
            // 講師の名称
            'tutors.name as tutor_name',
            // コードマスタの名称（請求種別）
            'mst_codes_26.name as surcharge_kind_name',
        )
            // 校舎名の取得JOIN
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('surcharges.campus_cd', '=', 'campus_names.code');
            })
            // 講師情報とJOIN
            ->sdLeftJoin(Tutor::class, 'surcharges.tutor_id', '=', 'tutors.tutor_id')
            // コードマスターとJOIN 請求種別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('surcharges.surcharge_kind', '=', 'mst_codes_26.code')
                    ->where('mst_codes_26.data_type', AppConst::CODE_MASTER_26);
            }, 'mst_codes_26')
            //指定の申請IDに絞る
            ->where('surcharge_id', $surchargeId)
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // ステータス「承認待ち」のみ更新可能とする
            ->where('approval_status', AppConst::CODE_MASTER_2_0)
            ->firstOrFail();

        return $surcharge;
    }

    /**
     * 支払年月リストを作成
     */
    private function getPaymentDateList($workingDate)
    {
        // 支払年月リストを作成 翌月以降3ヶ月分（例：2023/02～2023/04）
        $paymentDateList = [];
        for ($i = 1; $i <= 3; $i++) {
            // 実施日を基に加算する
            $addDate = strtotime($workingDate . '+' . $i . 'month');
            // codeはY-m形式(データ保存用)、valueはY/m形式(画面表示用)とする
            $paymentDateList += array(date('Y-m', $addDate) => ["value" => date('Y/m', $addDate)]);
        }

        return $paymentDateList;
    }
}
