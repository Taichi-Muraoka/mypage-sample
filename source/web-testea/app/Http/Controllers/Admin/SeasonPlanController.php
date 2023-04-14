<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\ExtStudentKihon;
use App\Models\ExtRoom;
use App\Models\ExtGenericMaster;
use App\Models\Invoice;
use App\Models\Account;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use App\Http\Controllers\Traits\FuncInvoiceTrait;
use App\Http\Controllers\Traits\FuncAgreementTrait;
use Illuminate\Support\Facades\Lang;

/**
 * 特別期間講習コマ組み - コントローラ
 */
class SeasonPlanController extends Controller
{

    // 機能共通処理：

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
        return view('pages.admin.season_plan');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // ページネータで返却（モック用）
        return $this->getListAndPaginatorMock();
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {
        return;
    }

    //==========================
    // 自動コマ組み実行画面
    //==========================

    /**
     * 詳細画面
     *
     * @param int $id 期間ID
     * @return view
     */
    public function autoExec($id)
    {
        return view('pages.admin.season_plan-autoexec',[
            'editData' => null,
        ]);
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function searchAutoExec(Request $request)
    {
        // ページネータで返却（モック用）
        return $this->getListAndPaginatorMock();
    }

    /**
     * アンマッチリストダウンロード
     *
     * @param int $csvId csvID
     * @return mixed ファイル
     */
    public function download($csvId)
    {
        return;
    }

}
