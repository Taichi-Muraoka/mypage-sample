<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Invoice;
use App\Http\Controllers\Traits\FuncInvoiceTrait;

/**
 * 請求情報 - コントローラ
 */
class InvoiceController extends Controller
{

    // 機能共通処理：請求書
    use FuncInvoiceTrait;

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
        return view('pages.student.invoice');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // クエリを作成
        $query = Invoice::query();

        // データを取得
        $invoices = $query
            ->select(
                'invoice_date'
            )
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // ソート
            ->orderby('invoice_date', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $invoices, function ($items) {
            // 請求年月を年月yyyymmで渡す
            foreach ($items as $item) {
                $item['date'] = $item->invoice_date->format('Ym');
            }

            return $items;
        });
    }

    //==========================
    // 詳細
    //==========================

    /**
     * 詳細画面
     *
     * @param date $date 日付
     * @return view
     */
    public function detail($date)
    {
        // MEMO: ログインアカウントのIDでデータを取得するのでガードは不要

        // IDのバリデーション
        $this->validateIds($date);

        // ログイン者の情報を取得する
        $account = Auth::user();
        $sid = $account->account_id;

        // データの取得
        $dtlData = $this->getInvoiceDetail($date, $sid);

        return view('pages.student.invoice-detail', [
            'invoice_import' => $dtlData['invoice_import'],
            'invoice' => $dtlData['invoice'],
            'invoice_detail' => $dtlData['invoice_detail'],
            // PDF用にIDを渡す
            'editData' => [
                'date' => $date
            ]
        ]);
    }

    /**
     * PDF出力
     *
     * @param date $date 日付
     * @return void
     */
    public function pdf($date)
    {
        // MEMO: ログインアカウントのIDでデータを取得するのでガードは不要

        // IDのバリデーション
        $this->validateIds($date);

        // ログイン者の情報を取得する
        $account = Auth::user();
        $sid = $account->account_id;

        // データの取得
        $dtlData = $this->getInvoiceDetail($date, $sid);

        $pdfData = [
            'invoice_import' => $dtlData['invoice_import'],
            'invoice' => $dtlData['invoice'],
            'invoice_detail' => $dtlData['invoice_detail'],
        ];

        // 請求書PDFの出力(管理画面でも使用するので共通化)
        $this->outputPdfInvoice($pdfData);

        // 特になし
        return;
    }
}
