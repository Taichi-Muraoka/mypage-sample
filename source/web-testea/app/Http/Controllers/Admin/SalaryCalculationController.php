<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\Schedule;
use App\Models\SalaryMng;
use App\Models\Invoice;
use App\Models\Account;
use App\Models\CodeMaster;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use App\Http\Controllers\Traits\FuncInvoiceTrait;
use App\Http\Controllers\Traits\FuncAgreementTrait;
use Illuminate\Support\Facades\Lang;

/**
 * 給与情報算出 - コントローラ
 */
class SalaryCalculationController extends Controller
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
        return view('pages.admin.salary_calculation', [
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
        // 当月を取得
        $present_month = date('Y-m') . '-01';

        // 給与情報取込を取得
        $query = SalaryMng::query();
        $salary_mngs = $query
            ->select(
                'salary_date',
                'confirm_date',
                'state',
                'name as state_name'
            )
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('salary_mng.state', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_24);
            })
            ->where('salary_date', '<=', $present_month)
            ->orderBy('salary_date', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $salary_mngs, function ($items) {
            // IDは年月
            foreach ($items as $item) {
                $item['id'] = $item->salary_date->format('Ym');
            }

            return $items;
        });
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
    // 給与算出情報一覧（対象月の詳細）
    //==========================

    /**
     * 詳細画面
     *
     * @param int $date 対象月
     * @return view
     */
    public function detail($date)
    {
        // 全体管理者でない場合は画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // IDのバリデーション
        $this->validateIds($date);

        // dateの形式のバリデーションと変換
        $idDate = $this->fmYmToDate($date);

        // $this->debug($idDate);

        // 当月を取得
        $present_month = date('Y-m') . '-01';

        // 取込可能な年月か確認する
        if ($present_month < $idDate) {
            $this->illegalResponseErr();
        }

        // 給与情報取込を取得
        $salary_mng = SalaryMng::select(
            'salary_date',
            'confirm_date',
            'name as state_name'
            )
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('salary_mng.state', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_24);
            })
            ->where('salary_date', '=', $idDate)
            ->firstOrFail();

        return view('pages.admin.salary_calculation-detail',[
            'salary_mng' => $salary_mng,
            'editData' => [
                'salaryDate' => $date
            ]
        ]);
    }

    /**
     * モーダル処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function execModal(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl-calc":
                // IDのバリデーション
                $this->validateIdsFromRequest($request, 'id');

                // dateの形式のバリデーションと変換
                $idDate = $this->fmYmToDate($request['id']);

                // 月末を取得
                $last_date = date('Y-m-d', strtotime('last day of ' . $idDate));

                $this->debug($last_date);

                // クエリを作成
                $query = Schedule::query()
                    ->whereNotNull('tutor_id')
                    ->whereIn('absent_status', [AppConst::CODE_MASTER_35_0])
                    ->whereBetween('target_date', [$idDate, $last_date]);

                // スケジュール情報取得し、授業時間カウントのサブクエリを作成
                $course_sub_query = DB::table($query)
                    ->select(
                        'tutor_id',
                        'course_cd',
                    )
                    ->selectRaw('SUM(minites) as sum_minites')
                    ->groupBy('tutor_id', 'course_cd');

                // コース別時間集計
                $course_count = DB::table($course_sub_query)
                    ->select(
                        'tutor_id'
                    )
                    ->selectRaw('MAX(CASE WHEN course_cd = ? THEN sum_minites ELSE 0 END) AS personal_min', [AppConst::COURSE_CD_10100])
                    ->selectRaw('MAX(CASE WHEN course_cd = ? THEN sum_minites ELSE 0 END) AS two_min', [AppConst::COURSE_CD_10200])
                    ->selectRaw('MAX(CASE WHEN course_cd = ? THEN sum_minites ELSE 0 END) AS three_min', [AppConst::COURSE_CD_10300])
                    ->selectRaw('MAX(CASE WHEN course_cd = ? THEN sum_minites ELSE 0 END) AS home_min', [AppConst::COURSE_CD_10400])
                    ->selectRaw('MAX(CASE WHEN course_cd = ? THEN sum_minites ELSE 0 END) AS exercise_min', [AppConst::COURSE_CD_10500])
                    ->selectRaw('MAX(CASE WHEN course_cd = ? THEN sum_minites ELSE 0 END) AS high_min', [AppConst::COURSE_CD_10600])
                    ->selectRaw('MAX(CASE WHEN course_cd = ? THEN sum_minites ELSE 0 END) AS group_min', [AppConst::COURSE_CD_20100])
                    ->groupBy('tutor_id');

                // $this->debug($course_count);

                return;
            case "#modal-dtl-confirm":

                return;
            case "#modal-dtl-output":

                return;
            default:
                // 該当しない場合
                $this->illegalResponseErr();
        }
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function searchDetail(Request $request)
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
    public function getDataDetail(Request $request)
    {
        // $this->debug($request);
        return;
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        return;
    }

}
