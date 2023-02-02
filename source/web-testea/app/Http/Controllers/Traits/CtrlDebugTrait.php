<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Debug - コントローラ共通処理
 */
trait CtrlDebugTrait
{

    /**
     * デバッグログを出力
     */
    protected function debug($msg)
    {
        // 呼び出し元の関数を取得
        $dbg = debug_backtrace();
        $callFunction = $dbg[1]['function'];
        Log::debug($callFunction . '-----------');
        Log::debug($msg);
    }
}
