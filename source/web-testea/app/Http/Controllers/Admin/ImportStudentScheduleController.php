<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ExtGenericMaster;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ReadDataValidateException;
use Illuminate\Support\Facades\Lang;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Log;

/**
 * 生徒スケジュール取り込み - コントローラ
 */
class ImportStudentScheduleController extends Controller
{
    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
    }


    //==========================
    // 取込
    //==========================

    /**
     * 取込画面
     *
     * @return view
     */
    public function index()
    {
        return view('pages.admin.import_student_schedule');
    }

    /**
     * 新規登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function create(Request $request)
    {
        return;
    }

    /**
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInput(Request $request)
    {
        return;
    }

    /**
     * アップロードされたファイルを読み込む
     * バリデーションも行う
     *
     * @param string $path ファイルパス
     * @return array csv取込データ
     */
    private function readData($path)
    {
        return;
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput()
    {
        return;
    }
}
