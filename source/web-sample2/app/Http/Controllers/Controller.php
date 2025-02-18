<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Controllers\Traits\CtrlCsvTrait;
use App\Http\Controllers\Traits\CtrlDebugTrait;
use App\Http\Controllers\Traits\CtrlFileTrait;
use App\Http\Controllers\Traits\CtrlFormTrait;
use App\Http\Controllers\Traits\CtrlModelTrait;
use App\Http\Controllers\Traits\CtrlDateTrait;
use App\Http\Controllers\Traits\CtrlResponseTrait;
use App\Http\Controllers\Traits\GuardTrait;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // モデル共通処理
    use CtrlModelTrait;

    // Form共通処理
    use CtrlFormTrait;

    // File共通処理
    use CtrlFileTrait;

    // CSV共通処理
    use CtrlCsvTrait;

    // Debug共通処理
    use CtrlDebugTrait;

    // 日付共通処理
    use CtrlDateTrait;

    // 応答共通処理
    use CtrlResponseTrait;

    // ガード共通処理
    use GuardTrait;
}
