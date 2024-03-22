<?php

namespace App\Http\Controllers\Traits;
use App\Consts\AppConst;

/**
 * 授業情報検索 - 機能共通処理
 */
trait FuncStudentClassTrait
{

    //==========================
    // 関数名を区別するために
    // fncStclを先頭につける
    //==========================

    /**
     * 授業情報検索画面用の報告書ステータス取得
     *
     * @param object $schedule スケジュール情報
     * @param int $statusList 報告書ステータスリスト
     * @param int $approvalStatus 報告書承認ステータス
     * @return string 画面表示用報告書ステータス（文字列）
     */
    private function fncStclGetReportStatus($schedule, $statusList, $approvalStatus)
    {
        $today = date("Y-m-d");
        $reportStatus = null;

        // 面談・自習は登録不要ステータスを設定
        if ($schedule['course_kind'] == AppConst::CODE_MASTER_42_3 || $schedule['course_kind'] == AppConst::CODE_MASTER_42_4) {
            $reportStatus = $statusList[AppConst::CODE_MASTER_4_0]->value;
        } else if ($schedule['report_id'] != null) {
            // 授業報告書登録済みの場合は、報告書の承認ステータスを設定
            if ($approvalStatus == AppConst::CODE_MASTER_4_1) {
                $reportStatus = $statusList[AppConst::CODE_MASTER_4_1]->value;
            }
            if ($approvalStatus == AppConst::CODE_MASTER_4_2) {
                $reportStatus = $statusList[AppConst::CODE_MASTER_4_2]->value;
            }
            if ($approvalStatus == AppConst::CODE_MASTER_4_3) {
                $reportStatus = $statusList[AppConst::CODE_MASTER_4_3]->value;
            }
        } else {
            // 授業日が当日以降(未実施)または当日欠席、未振替、振替中の場合、―（登録不要）を設定
            if (
                $schedule['target_date'] >= $today ||
                $schedule['absent_status'] == AppConst::CODE_MASTER_35_1 || 
                $schedule['absent_status'] == AppConst::CODE_MASTER_35_2 || 
                $schedule['absent_status'] == AppConst::CODE_MASTER_35_3 || 
                $schedule['absent_status'] == AppConst::CODE_MASTER_35_4
            ) {
                $reportStatus = $statusList[AppConst::CODE_MASTER_4_0]->value;
            }
            // 授業日が当日以前かつ出欠ステータスが「実施前・出席」の場合、×（要登録・差戻し）を設定
            if ($schedule['target_date'] < $today && $schedule['absent_status'] == AppConst::CODE_MASTER_35_0) {
                $reportStatus = $statusList[AppConst::CODE_MASTER_4_3]->value;
            }
        }

        return $reportStatus;
    }
}
