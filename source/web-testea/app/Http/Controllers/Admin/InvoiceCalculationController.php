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
 * 請求情報算出 - コントローラ
 */
class InvoiceCalculationController extends Controller
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
        return view('pages.admin.invoice_calculation', [
            'rules' => $this->rulesForSearch(),
        ]);
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
    // 請求算出情報一覧（対象月の詳細）
    //==========================

    /**
     * 詳細画面
     *
     * @param int $date 対象月
     * @return view
     */
    public function detail($date)
    {
        return view('pages.admin.invoice_calculation-detail',[
            'editData' => null,
        ]);
    }

}
