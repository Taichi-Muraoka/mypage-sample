<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Salary;
use App\Http\Controllers\Traits\FuncSalaryTrait;

/**
 * 給与明細 - コントローラ
 */
class SalaryController extends Controller
{

    // 機能共通処理：給与
    use FuncSalaryTrait;

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

        return view('pages.tutor.salary');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {

        // 給与明細を取得する
        $query = Salary::query();
        $salarys = $query
            ->select(
                'salary_date'
            )
            // 自分のアカウントIDでガードを掛ける（tid）
            ->where($this->guardTutorTableWithTid())
            // ソート順
            ->orderBy('salary_date', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $salarys, function ($items) {
            // IDは年月
            foreach ($items as $item) {
                $item['id'] = $item->salary_date->format('Ym');
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
     * @param date $date 年月（YYYYMM）
     * @return view
     */
    public function detail($date)
    {
        // MEMO: ログインアカウントのIDでデータを取得するのでガードは不要

        // IDのバリデーション
        $this->validateIds($date);

        // データの取得
        $account = Auth::user();
        $dtlData = $this->getSalaryDetail($account->account_id, $date);

        return view('pages.tutor.salary-detail', [
            'salary' => $dtlData['salary'],
            'salary_detail_1' => $dtlData['salary_detail_1'],
            'salary_detail_2' => $dtlData['salary_detail_2'],
            'salary_detail_3' => $dtlData['salary_detail_3'],
            'salary_detail_4' => $dtlData['salary_detail_4'],
            // PDF用にIDを渡す
            'editData' => [
                'date' => $date
            ]
        ]);
    }

    /**
     * PDF出力
     *
     * @param date 年月（YYYYMM）
     * @return void
     */
    public function pdf($date)
    {
        // MEMO: ログインアカウントのIDでデータを取得するのでガードは不要

        // IDのバリデーション
        $this->validateIds($date);

        // データの取得
        $account = Auth::user();
        $dtlData = $this->getSalaryDetail($account->account_id, $date);

        // 給与PDFの出力(管理画面でも使用するので共通化)
        $this->outputPdfSalary($dtlData);

        return;
    }
}
