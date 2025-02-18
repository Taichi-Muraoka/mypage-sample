<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

/**
 * 応答 - コントローラ共通処理
 */
trait CtrlResponseTrait
{
    /**
     * 何らかのエラーが発生した際の応答(登録処理時など)
     */
    protected function responseErr()
    {
        return ['error' => Lang::get('validation.invalid_error')];
    }

    /**
     * 不正なデータに対する応答 
     */
    protected function illegalResponseErr()
    {
        // 不正アクセスなのでログに残す
        // 呼び出し元の情報を表示
        $dbg = debug_backtrace();
        Log::info("illegal response. from - " . $dbg[0]['class'] . "(" . $dbg[0]['line'] . ")");

        // not foundを返却
        abort(404);
    }
}
