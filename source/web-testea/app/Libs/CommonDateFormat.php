<?php

namespace App\Libs;

use Illuminate\Support\Carbon;
use Closure;

/**
 * 日付フォーマットの共通処理
 */
class CommonDateFormat
{
    /**
     * 日付を曜日ありでフォーマット
     * @param   $dateData 日付
     * @return  フォーマット後の文字列(YYYY/MM/DD(曜日))
     */
    public static function formatYmdDay($dateData = null)
    {
        $targetDate = null;
        if (isset($dateData)) {
            $targetDate = Carbon::parse($dateData);
        }else{
            $targetDate = Carbon::now();
        }

        return Carbon::parse($targetDate)->isoFormat('YYYY/MM/DD(ddd)');
    }
}
