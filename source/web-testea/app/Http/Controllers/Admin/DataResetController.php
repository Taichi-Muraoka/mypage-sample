<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Exceptions\ReadDataValidateException;
use App\Models\ExtStudentKihon;
use App\Models\BatchMng;
use App\Models\Office;
use App\Models\CodeMaster;
use App\Consts\AppConst;

/**
 * 保持期限データ削除 - コントローラ
 */
class DataResetController extends Controller
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
    // 一覧
    //==========================

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        return;
    }

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        return view('pages.admin.data_reset', [
            'rules' => $this->rulesForInput(),
            'editData' => ["this_year"=>"2023年2月"]
        ]);
    }

    /**
     * 取込処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function create(Request $request)
    {
        return;
    }

    /**
     * バリデーション(取込用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInput(Request $request)
    {
        return;
    }

    /**
     * バリデーションルールを取得(取込用)
     *
     * @return array ルール
     */
    private function rulesForInput()
    {
        return;
    }
}
