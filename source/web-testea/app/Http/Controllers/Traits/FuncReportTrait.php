<?php

namespace App\Http\Controllers\Traits;

use App\Models\ExtSchedule;
use App\Consts\AppConst;

/**
 * 授業報告 - 機能共通処理
 */
trait FuncReportTrait
{
    /**
     * 授業時間数の間隔・最大値
     * constがTraitで定義できないので変数にした
     */
    private $REPORT_MINUTES_INTERVAL = 5;
    private $REPORT_MINUTES_MIN = 5;
    private $REPORT_MINUTES_MAX = 200;

    /**
     * 授業時間数のプルダウンメニュー取得
     *
     * @return array
     */
    private function getMenuOfMinutes()
    {
        $minutes = array();
        for ($i = $this->REPORT_MINUTES_MIN; $i <= $this->REPORT_MINUTES_MAX; $i += $this->REPORT_MINUTES_INTERVAL) {
            $minutes += [$i => ["value" => $i]];
        }
        return $minutes;
    }

    /**
     * 授業報告書用スケジュールリスト（個別教室用）取得
     *
     * @param integer $tid 教師No
     * @param string $roomcd 教室コード（指定しない場合はnull）
     * @param string $sid 生徒ID（指定しない場合はnull）
     * @return array
     */
    private function getScheduleListReport($tid, $roomcd, $sid = null)
    {
        // 授業日・開始時刻が現在日付時刻以前の授業のみ登録可とする
        $today_date = date("Y/m/d");
        $today_time = date("H:i");

        // レギュラー＋個別講習の抽出条件
        $scheLessonTypes = [AppConst::EXT_GENERIC_MASTER_109_0, AppConst::EXT_GENERIC_MASTER_109_1];

        // 生徒No.に紐づくスケジュール（レギュラー＋個別講習）を取得する。
        $query = ExtSchedule::query();
        $lessons = $query
            ->select(
                'id',
                'lesson_date',
                'start_time'
            )
            // 自分の受け持ちのスケジュールのみ
            ->where('ext_schedule.tid', '=', $tid)
            ->whereIn('ext_schedule.lesson_type', $scheLessonTypes)

            // 以下の条件はクロージャで記述(orを含むため)
            ->where(function ($query) use ($today_date, $today_time) {
                // 授業日・開始時刻が現在日付時刻以前の授業のみ
                $query->where('ext_schedule.lesson_date', '<', $today_date)
                    ->orwhere(function ($orQuery) use ($today_date, $today_time) {
                        $orQuery->where('ext_schedule.lesson_date', '=', $today_date)
                            ->where('ext_schedule.start_time', '<=', $today_time);
                    });
            })
            // 後日振替のレコードを除外
            ->where(function ($orQuery) {
                // 出欠・振替コードが2（振替）以外 ※NULLのものを含む
                $orQuery->whereNotIn('ext_schedule.atd_status_cd', [AppConst::ATD_STATUS_CD_2])
                    ->orWhereNull('ext_schedule.atd_status_cd');
            })
            // 教室が指定された場合のみ絞り込み
            ->when($roomcd, function ($query) use ($roomcd) {
                return $query->where('.roomcd', $roomcd);
            })
            // 生徒IDが指定された倍のみ絞り込み
            ->when($sid, function ($query) use ($sid) {
                return $query->where('.sid', $sid);
            })
            ->orderBy('ext_schedule.lesson_date', 'desc')
            ->orderBy('ext_schedule.start_time', 'desc')
            ->get();

        // 個別教室のスケジュールプルダウンメニューを作成
        $scheduleMaster = $this->mdlGetScheduleMasterList($lessons);

        return $scheduleMaster;
    }
}
