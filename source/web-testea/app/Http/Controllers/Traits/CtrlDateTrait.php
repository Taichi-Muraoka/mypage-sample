<?php

namespace App\Http\Controllers\Traits;

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
}
