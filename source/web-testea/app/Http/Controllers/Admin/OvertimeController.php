<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\CodeMaster;
use App\Models\Schedule;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FuncReportTrait;

/**
 * 超過勤務者一覧 - コントローラ
 */
class OvertimeController extends Controller
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
        // 先月初日
        $first_date = date('Y/m/01', strtotime('-1 month'));

        // 今月末日
        $last_date = date('Y/m/t', strtotime('-1 month'));

        $editData = [
            'target_date_from' => $first_date,
            'target_date_to' => $last_date
        ];

        return view('pages.admin.overtime', [
            'rules' => $this->rulesForSearch(),
            'editData' => $editData
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
        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch())->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = Schedule::query();

        // 日付の絞り込み条件
        $query->SearchTargetDateFrom($form);
        $query->SearchTargetDateTo($form);

        $overtime_worker = $query
            ->whereNotNull('tutor_id')
            ->select(
                'tutor_id',
                'target_date',
            )
            ->selectRaw('SUM(minites) as sum_minites')
            ->selectRaw('CASE WHEN SUM(minites) > 480 THEN SUM(minites) - 480 ELSE 0 END AS over_time')
            // ->selectRaw('CASE WHEN start_time >= (22:00:00) THEN SUM(minites) ELSE 0 END AS late_time')
            ->groupBy('tutor_id', 'target_date')
            ->orderBy('target_date')
            ->orderBy('tutor_id');

        // ページネータで返却
        return $this->getListAndPaginator($request, $overtime_worker);
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return mixed ルール
     */
    private function rulesForSearch()
    {
        $rules = array();

        return $rules;
    }

}
