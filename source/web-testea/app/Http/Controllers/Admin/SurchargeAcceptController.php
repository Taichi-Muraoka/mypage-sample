<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\ExtStudentKihon;
use App\Models\ExtRirekisho;
use App\Models\CodeMaster;
use App\Models\ExtSchedule;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncReportTrait;

/**
 * 追加請求申請受付 - コントローラ
 */
class SurchargeAcceptController extends Controller
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
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        return view('pages.admin.surcharge_accept', [
            'rules' => $this->rulesForSearch(),
            'editData' => null
        ]);
    }

    /**
     * バリデーション(検索用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForSearch(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForSearch());
        return $validator->errors();
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array  検索結果
     */
    public function search(Request $request)
    {
        // ページネータで返却（モック用）
        return $this->getListAndPaginatorMock();
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        return ['id' => $request->id];
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return mixed ルール
     */
    private function rulesForSearch()
    {
        return;
    }

    //==========================
    // 編集
    //==========================

    /**
     * 編集画面
     *
     * @param int reportId 授業報告書ID
     * @return view
     */
    public function edit($reportId)
    {
        return view('pages.admin.surcharge_accept-edit', [
            'editData' => null,
            'rules' => $this->rulesForInput(null),
        ]);
    }

    /**
     * 編集処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function update(Request $request)
    {
        return;
    }

    /**
     * 削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function delete(Request $request)
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
     * バリデーションルールを取得(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        return;
    }

}
