<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\StudentCampus;
use App\Models\Notice;
use App\Models\NoticeGroup;
use App\Models\NoticeDestination;
use App\Models\NoticeTemplate;
use App\Consts\AppConst;

/**
 * お知らせ - 機能共通処理
 */
trait FuncNoticeTrait
{
    /**
     * テンプレートメニューの取得
     *
     * @return array 定型文情報取得
     */
    private function getMenuOfNoticeTemplate()
    {
        return NoticeTemplate::select(
            'template_id',
            'template_name as value',
        )
            ->orderBy('order_code', 'asc')
            ->get()
            ->keyBy('template_id');
    }

    /**
     * 宛先グループメニューの取得
     *
     * @return array 宛先グループリスト
     */
    private function getMenuOfNoticeGroup()
    {
        return NoticeGroup::select(
            'notice_group_id',
            'group_name as value'
        )
            ->orderBy('notice_group_id', 'asc')
            ->get();
    }

    /**
     * 生徒基本情報を取得
     *
     * @return mixed 生徒情報
     */
    private function getStudentInfo()
    {
        // ログイン者の情報を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        // 生徒情報から学年・入会日を取得する。
        $query = Student::query();
        $student = $query
            ->select(
                'grade_cd',
                'enter_date'
            )
            ->where('student_id', '=', $account_id)
            ->firstOrFail();

        return $student;
    }

    /**
     * 対象お知らせIDの取得(生徒)
     *
     * @return array お知らせIDの配列
     */
    private function getStudentNoticeIds()
    {

        // ログイン者の情報を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        // 当年度・前年度の範囲
        $fiscal_start_date = $this->dtGetFiscalDate("present", "start", true);
        $fiscal_end_date = $this->dtGetFiscalDate("present", "end", true);
        $prev_fiscal_start_date = $this->dtGetFiscalDate("prev", "start", true);
        $prev_fiscal_end_date = $this->dtGetFiscalDate("prev", "end", true);

        // 生徒情報から学年・入会日を取得する。
        $student = $this->getStudentInfo();
        $present_cls = $student->grade_cd;

        // 生徒No.から所属教室を取得する。
        $query = StudentCampus::query();
        $rooms = $query
            ->select(
                'campus_cd'
            )
            ->where('student_id', '=', $account_id)
            ->get();

        // 複数所属の場合を考慮し、campus_cdを取り出す。
        $roomcds = [];
        foreach ($rooms as $room) {
            array_push($roomcds, $room->campus_cd);
        }

        // 生徒の学年からお知らせグループIDを取り出す。
        $query = NoticeGroup::query();
        $notice_groups = $query
            ->select(
                'notice_group_id'
            )
            ->where('cls_cd', '=', $present_cls)
            ->first();

        if (isset($notice_groups['notice_group_id'])) {
            $present_group_id = $notice_groups->notice_group_id;
        } else {
            $present_group_id = 0;
        }

        // 前年度のグループIDを取得するため
        $query = NoticeGroup::query();
        $notice_groups_prev = $query
            ->select(
                'notice_group_id',
                'cls_cd'
            )
            ->Where('cls_cd_next', '=', $student->grade_cd)
            ->first();

        if (isset($notice_groups_prev['notice_group_id'])) {
            $prev_group_id = $notice_groups_prev->notice_group_id;
        } else {
            $prev_group_id = 0;
        }

        // 宛先情報から対象となる個別のお知らせIDを抽出する。
        $query = NoticeDestination::query();
        $notice_destinations = $query
            ->select(
                'notice_destinations.notice_id'
            )
            ->sdLeftJoin(Notice::class, 'notices.notice_id', '=', 'notice_destinations.notice_id')
            ->where('notice_destinations.destination_type', '=', AppConst::CODE_MASTER_15_2)
            ->where('notice_destinations.student_id', '=', $account_id)
            ->whereBetween('notices.regist_time', [$prev_fiscal_start_date, $fiscal_end_date])
            ->get();

        // 宛先情報から対象となるグループ一斉のお知らせIDを抽出する。
        $query = NoticeDestination::query();
        $notice_destinations_group = $query
            ->select(
                'notice_destinations.notice_id',
                'notices.regist_time'
            )
            ->sdLeftJoin(Notice::class, 'notices.notice_id', '=', 'notice_destinations.notice_id')
            ->where('destination_type', '=', AppConst::CODE_MASTER_15_1)
            ->where(function ($orQuery) use ($present_group_id, $prev_group_id, $fiscal_start_date, $fiscal_end_date, $prev_fiscal_start_date, $prev_fiscal_end_date) {
                $orQuery
                    ->where(function ($orQuery) use ($present_group_id, $fiscal_start_date, $fiscal_end_date) {
                        $orQuery
                            ->where('notice_destinations.notice_group_id', '=', $present_group_id)
                            ->whereBetween('notices.regist_time', [$fiscal_start_date, $fiscal_end_date]);
                    })
                    ->orWhere(function ($orQuery) use ($prev_group_id, $prev_fiscal_start_date, $prev_fiscal_end_date) {
                        $orQuery
                            ->where('notice_destinations.notice_group_id', '=', $prev_group_id)
                            ->whereBetween('notices.regist_time', [$prev_fiscal_start_date, $prev_fiscal_end_date]);
                    });
            })
            ->where(function ($orQuery) use ($roomcds) {
                $orQuery->whereIn('notice_destinations.campus_cd', $roomcds)
                    ->orWhereNull('notice_destinations.campus_cd');
            })
            ->get();

        // お知らせIDを抽出する。
        $notice_ids = [];
        if (count($notice_destinations) > 0) {
            foreach ($notice_destinations as $notice) {
                array_push($notice_ids, $notice->notice_id);
            }
        }
        if (count($notice_destinations_group) > 0) {
            foreach ($notice_destinations_group as $notice) {
                array_push($notice_ids, $notice->notice_id);
            }
        }
        $notice_ids = array_unique($notice_ids);
        return $notice_ids;
    }

    /**
     * 対象お知らせIDの取得(教師)
     *
     * @return array お知らせIDの配列
     */
    private function getTutorNoticeIds()
    {
        // ログイン者の情報を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        // 当年度・前年度の範囲
        $fiscal_end_date = $this->dtGetFiscalDate("present", "end", true);
        $prev_fiscal_start_date = $this->dtGetFiscalDate("prev", "start", true);

        // 教師一斉のお知らせグループIDを取り出す。
        $query = NoticeGroup::query();
        $notice_groups = $query
            ->select(
                'notice_group_id'
            )
            ->where('group_type', AppConst::NOTICE_GROUP_TYPE_2)
            ->get();

        $notice_group_ids = [];
        if (count($notice_groups) > 0) {
            foreach ($notice_groups as $notice_group) {
                array_push($notice_group_ids, $notice_group->notice_group_id);
            }
        }
        $notice_group_ids = array_unique($notice_group_ids);

        // 宛先情報から対象となる個別のお知らせIDを抽出する。
        $query = NoticeDestination::query();
        $notice_destinations = $query
            ->select(
                'notice_destinations.notice_id'
            )
            ->sdLeftJoin(Notice::class, 'notices.notice_id', '=', 'notice_destinations.notice_id')
            ->where('destination_type', '=', AppConst::CODE_MASTER_15_3)
            ->where('tutor_id', '=', $account_id)
            ->whereBetween('notices.regist_time', [$prev_fiscal_start_date, $fiscal_end_date])
            ->get();

        // 宛先情報から対象となるグループ一斉のお知らせIDを抽出する。
        $query = NoticeDestination::query();
        $notice_destinations_group = $query
            ->select(
                'notice_destinations.notice_id'
            )
            ->sdLeftJoin(Notice::class, 'notices.notice_id', '=', 'notice_destinations.notice_id')
            ->where('destination_type', '=', AppConst::CODE_MASTER_15_1)
            ->whereIn('.notice_group_id', $notice_group_ids)
            ->whereBetween('notices.regist_time', [$prev_fiscal_start_date, $fiscal_end_date])
            ->get();

        // お知らせIDを抽出する。
        $notice_ids = [];
        if (count($notice_destinations) > 0) {
            foreach ($notice_destinations as $notice) {
                array_push($notice_ids, $notice->notice_id);
            }
        }
        if (count($notice_destinations_group) > 0) {
            foreach ($notice_destinations_group as $notice) {
                array_push($notice_ids, $notice->notice_id);
            }
        }
        $notice_ids = array_unique($notice_ids);

        return $notice_ids;
    }

    /**
     * [ガード] お知らせIDが自分が見れるIDかチェックする
     * 
     * @param int $noticeId お知らせID
     */
    private function guardNoticeId($noticeId)
    {

        // ログイン者の情報を取得する。
        $account = Auth::user();
        $account_type = $account->account_type;

        switch ($account_type) {
            case AppConst::CODE_MASTER_7_1:
                //---------------
                // 生徒
                //---------------

                // お知らせIDの取得
                $notice_ids = $this->getStudentNoticeIds();

                // 生徒情報の取得
                $student = $this->getStudentInfo();

                // 生徒入会日
                $enter_date = $student->enter_date;

                // お知らせIDから生徒入会日以降のお知らせを取得する。
                Notice::query()
                    // 対象のお知らせIDで絞る
                    ->whereIn('notice_id', $notice_ids)
                    // 入会日がある場合は、入会日以降のデータを取得
                    ->when($enter_date, function ($query, $enter_date) {
                        return $query->where('regist_time', '>=', $enter_date);
                    })
                    // キー項目指定されたお知らせID
                    ->where('notice_id', '=', $noticeId)
                    // 見つからない場合はエラー
                    ->firstOrFail();

                break;
            case AppConst::CODE_MASTER_7_2:
                //---------------
                // 教師
                //---------------

                // お知らせIDの取得
                $notice_ids = $this->getTutorNoticeIds();

                // お知らせIDからお知らせを取得する。
                // こっちはSQL発行しなくても$notice_idsの存在チェックで良いが、生徒に合わせた
                Notice::query()
                    // 対象のお知らせIDで絞る
                    ->whereIn('notice_id', $notice_ids)
                    // キー項目指定されたお知らせID
                    ->where('notice_id', '=', $noticeId)
                    // 見つからない場合はエラー
                    ->firstOrFail();

                break;
            default:
                // 該当しない場合
                $this->illegalResponseErr();
        }
    }
}
