<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Models\MstSystem;

/**
 * 日付 - コントローラ共通処理
 */
trait CtrlDateTrait
{
    //==========================
    // 関数名を区別するために
    // dt(日付)を先頭につける
    //==========================

    //------------------------------
    // 日付の取得
    //------------------------------

    /**
     * 当年度・前年度・翌年度・6年前・5年前・4年前の開始日・終了日の文字列を取得する。時刻の有無はオプション。
     *
     *
     * @param string "present"or"prev"or"next"or"6yearsAgo"or"5yearsAgo"or"4yearsAgo", "start"or"end", 時刻を含める場合はtrue
     * @return string 日付の文字列
     */
    protected function dtGetFiscalDate(String $year = "present", String $day = "start", $time = false)
    {

        // システムマスタから当年度を取得
        $currentYear = MstSystem::select('value_num')
            ->where('key_id', AppConst::SYSTEM_KEY_ID_1)
            ->whereNotNull('value_num')
            ->firstOrFail();

        // 当年度開始日 当年度/03/01
        $curStartdate = $currentYear->value_num . '/03/01';
        $dateStr = $curStartdate;

        if ($year === "present" && $day === "end") {
            // 当年度終了日 ＝ 当年度開始日 + 1 year - 1 day
            $dateStr = date('Y/m/d', strtotime('+1 year -1 day ' . $curStartdate));
        } elseif ($year === "prev" && $day === "start") {
            // 前年度開始日 ＝ 当年度開始日 - 1 year
            $dateStr = date('Y/m/d', strtotime('-1 year ' . $curStartdate));
        } elseif ($year === "prev" && $day === "end") {
            // 前年度終了日 ＝ 当年度開始日 - 1 day
            $dateStr = date('Y/m/d', strtotime('-1 day ' . $curStartdate));
        } elseif ($year === "next" && $day === "start") {
            // 翌年度開始日 ＝ 当年度開始日 + 1 year
            $dateStr = date('Y/m/d', strtotime('+1 year ' . $curStartdate));
        } elseif ($year === "next" && $day === "end") {
            // 翌年度終了日 ＝ 当年度開始日 + 2 year - 1 day
            $dateStr = date('Y/m/d', strtotime('+2 year -1 day ' . $curStartdate));
        } elseif ($year === "4yearsAgo" && $day === "start") {
            // 4年前の年度開始日 ＝ 当年度開始日 - 4 year
            $dateStr = date('Y/m/d', strtotime('-4 year ' . $curStartdate));
        } elseif ($year === "4yearsAgo" && $day === "end") {
            // 4年前の年度終了日 ＝ 当年度開始日 - 3 year - 1 day
            $dateStr = date('Y/m/d', strtotime('-3 year -1 day' . $curStartdate));
        } elseif ($year === "5yearsAgo" && $day === "start") {
            // 5年前の年度開始日 ＝ 当年度開始日 - 5 year
            $dateStr = date('Y/m/d', strtotime('-5 year ' . $curStartdate));
        } elseif ($year === "5yearsAgo" && $day === "end") {
            // 5年前の年度終了日 ＝ 当年度開始日 - 4 year - 1 day
            $dateStr = date('Y/m/d', strtotime('-4 year -1 day' . $curStartdate));
        } elseif ($year === "6yearsAgo" && $day === "start") {
            // 6年前の年度開始日 ＝ 当年度開始日 -6 year
            $dateStr = date('Y/m/d', strtotime('-6 year ' . $curStartdate));
        } elseif ($year === "6yearsAgo" && $day === "end") {
            // 6年前の年度終了日 ＝ 当年度開始日 - 5 year - 1 day
            $dateStr = date('Y/m/d', strtotime('-5 year -1 day' . $curStartdate));
        }

        // 時刻を付加する場合（オプション）
        if ($time) {
            if ($day === "start") {
                $dateStr = $dateStr . ' 00:00:00';
            } elseif ($day === "end") {
                $dateStr = $dateStr . ' 23:59:59';
            }
        }

        return $dateStr;
    }

    /**
     * 日付から曜日コードを返す
     *
     * @param string 日付の文字列
     * @return int   曜日コード
     */
    protected function dtGetDayOfWeekCd(String $dt)
    {
        $dayno = date('w', strtotime($dt));
        if ($dayno == 0) {
            // 日曜日 = 0 を 曜日コードの値に変換する
            $dayno = AppConst::CODE_MASTER_16_7;
        }
        return $dayno;
    }

    /**
     * 対象日付が指定範囲内かどうかをチェック
     *
     * @param $target_date
     * @param $from_date
     * @param $to_date
     * @return bool true:範囲内、false:範囲外
     */
    protected function dtCheckDateFromTo($target_date, $from_date, $to_date)
    {
        $targetDate = strtotime($target_date);

        if (
            $targetDate >= strtotime($from_date) &&
            $targetDate <= strtotime($to_date)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 日付を/区切りの形式にフォーマットする
     *
     * @param $target_date
     * @return string   YYYY/MM/DD形式
     */
    protected function dtFormatYmd($target_date)
    {
        if ($target_date == null || $target_date == '') {
            return '';
        } else {
            return date('Y/m/d', strtotime($target_date));
        }
    }

    /**
     * システム日時を基準に対象授業日の範囲を取得
     * 振替調整・生徒欠席連絡で使用
     *
     * @param bool  $adminFlg 管理者設定時true（省略時false）
     * @return array 開始日～終了日
     */
    protected function dtGetTargetDateFromTo($adminFlg = false)
    {
        $nowTime = date('H:i');
        $fromDate = null;
        $toDate = null;
        if ($nowTime < '22:00') {
            // 現在時刻が22時までは、翌日～翌日より1ヶ月(30日)先
            $fromDate = date('Y/m/d', strtotime('+1 day'));
            $toDate = date('Y/m/d', strtotime('+31 day'));
        } else {
            // 現在時刻が22時以降は、翌々日～翌々日より1ヶ月(30日)先
            $fromDate = date('Y/m/d', strtotime('+2 day'));
            $toDate = date('Y/m/d', strtotime('+32 day'));
        }
        // 管理者設定時は、当日も許可する
        if ($adminFlg == true) {
            $fromDate = date('Y/m/d');
        }

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate
        ];
    }

    /**
     * 時刻文字列がH:i形式かどうかをチェック
     *
     * @param $inputTime
     * @return bool
     */
    protected function dtCheckTimeFormat($inputTime)
    {
        // 入力文字列が正規表現パターンに一致するかチェック
        // ゼロなしでも許可とする。とりあえずコロン区切り
        if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5]?[0-9]$/', $inputTime)) {
            // 一致した場合はtrueを返す
            return true;
        } else {
            // 一致しない場合はfalseを返す
            return false;
        }
    }

    /**
     * 分を時間に変換
     *
     * @param 授業時間(分)
     * @return 授業時間(時間)
     */
    protected function dtConversionTime($minutes)
    {
        $time = floor($minutes / 60 * 10) / 10;

        return $time;
    }

    /**
     * 分を時間に変換（少数第2位まで表示）
     *
     * @param 授業時間(分)
     * @return 授業時間(時間)
     */
    protected function dtConversionTimeDecimal($minutes)
    {
        $time = floor($minutes / 60 * 100) / 100;

        return $time;
    }

    /**
     * 分を時間に変換
     *
     * @param 授業時間(分)
     * @return 授業時間(〇時間〇分)
     */
    public function dtConversionHourMinutes($minutes)
    {
        $conversion_hour = floor($minutes / 60 * 10) / 10;

        $hour = floor($conversion_hour);

        $minutes = floor($minutes % 60 * 10) / 10;

        return $hour . '時間' . $minutes . '分';
    }
}
