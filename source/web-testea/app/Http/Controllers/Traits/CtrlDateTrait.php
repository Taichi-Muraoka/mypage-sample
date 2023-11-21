<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;

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
     * 当年度・前年度・翌年度の開始日・終了日の文字列を取得する。時刻の有無はオプション。
     * 
     *
     * @param string "present"or"prev"or"next", "start"or"end", 時刻を含める場合はtrue
     * @return string 日付の文字列
     */
    protected function dtGetFiscalDate(String $year = "present", String $day = "start", $time = false)
    {
        $dateStr = date('Y', strtotime('-3 month')) . '/04/01';
        if ($time) {
            $dateStr = $dateStr . ' 00:00:00';
        }
        if ($year === "present" && $day === "end") {
            $dateStr = date('Y', strtotime('+9 month')) . '/03/31';
            if ($time) {
                $dateStr = $dateStr . ' 23:59:59';
            }
        } elseif ($year === "prev" && $day === "start") {
            $dateStr = date('Y', strtotime('-1 year -3 month')) . '/04/01';
            if ($time) {
                $dateStr = $dateStr . ' 00:00:00';
            }
        } elseif ($year === "prev" && $day === "end") {
            $dateStr = date('Y', strtotime('-3 month')) . '/03/31';
            if ($time) {
                $dateStr = $dateStr . ' 23:59:59';
            }
        } elseif ($year === "next" && $day === "start") {
            $dateStr = date('Y', strtotime('+9 month')) . '/04/01';
            if ($time) {
                $dateStr = $dateStr . ' 00:00:00';
            }
        } elseif ($year === "next" && $day === "end") {
            $dateStr = date('Y', strtotime('+1 year +9 month')) . '/03/31';
            if ($time) {
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

        if ($targetDate >= strtotime($from_date) &&
            $targetDate <= strtotime($to_date)) {
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
        if ($target_date == null || $target_date == ''){
            return '';
        }else{
            return date('Y/m/d', strtotime($target_date));
        }
    }
}
