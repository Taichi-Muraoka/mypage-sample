<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
// use App\Models\Grades;
use App\Models\GradesDetail;
use App\Models\CodeMaster;
use App\Models\ExtTrialMaster;
use Illuminate\Support\Facades\DB;
use App\Consts\AppConst;
// use App\Http\Controllers\Traits\FuncGradesTrait;
use Carbon\Carbon;

/**
 * 授業実施登録 - コントローラ
 */
class AttendanceController extends Controller
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
        return view('pages.tutor.attendance', [
            'rules' => null,
            'editData' => null
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
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getData(Request $request)
    {
        return [
        ];
    }

    /**
     * モーダル処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function execModal(Request $request)
    {
        return;
    }

}
