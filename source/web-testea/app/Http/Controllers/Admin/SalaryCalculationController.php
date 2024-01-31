<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\Schedule;
use App\Models\Surcharge;
use App\Models\SalaryMng;
use App\Models\SalarySummary;
use App\Models\SalaryTravelCost;
use App\Models\CodeMaster;
use Illuminate\Support\Facades\DB;
use App\Models\MstSystem;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

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
        // 全体管理者でない場合は画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        return view('pages.admin.salary_calculation');
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
            'state',
            'name as state_name'
        )
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('salary_mng.state', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_24);
            })
            ->where('salary_date', '=', $idDate)
            ->firstOrFail();

        // 確定済みの場合あらかじめ日付のフォーマットをかけておく
        $confirm_date = null;
        if ($salary_mng->confirm_date != null) {
            $confirm_date = $salary_mng->confirm_date->format('Y年m月d日');
        }

        // ボタンの制御
        $calc_disable = false;
        $comfirm_disable = false;
        if ($salary_mng->state == AppConst::CODE_MASTER_24_2) {
            $calc_disable = true;
        }
        if ($salary_mng->state != AppConst::CODE_MASTER_24_1) {
            $comfirm_disable = true;
        }

        return view('pages.admin.salary_calculation-detail', [
            'salary_mng' => $salary_mng,
            'confirm_date' => $confirm_date,
            'calc_disable' => $calc_disable,
            'comfirm_disable' => $comfirm_disable,
            'editData' => [
                'id' => $date
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

                try {
                    // トランザクション(例外時は自動的にロールバック)
                    DB::transaction(function () use ($request) {

                        // dateの形式のバリデーションと変換
                        $idDate = $this->fmYmToDate($request['id']);

                        // 給与算出情報から対象月のレコードを物理削除する
                        SalarySummary::where('salary_date', '=', $idDate)
                            ->forceDelete();

                        // 給与算出交通費情報から対象月のレコードを物理削除する
                        SalaryTravelCost::where('salary_date', '=', $idDate)
                            ->forceDelete();

                        // 月末を取得
                        $last_date = date('Y-m-d', strtotime('last day of ' . $idDate));

                        // 翌月を取得
                        $next_month = date('Y-m-d', strtotime('next month', strtotime($idDate)));

                        // スケジュール情報のクエリを作成
                        $schedule_query = Schedule::query()
                            ->whereNotNull('tutor_id')
                            ->whereIn('absent_status', [AppConst::CODE_MASTER_35_0])
                            ->whereBetween('target_date', [$idDate, $last_date]);

                        // 追加請求情報のクエリを作成
                        $surcharge_query = Surcharge::query()
                            ->where('approval_status', AppConst::CODE_MASTER_2_1)
                            ->where('payment_status', AppConst::CODE_MASTER_27_0)
                            ->where('payment_date', $next_month);

                        // スケジュール情報取得し、授業時間カウントのサブクエリを作成
                        $schedule_sub_query = DB::table($schedule_query)
                            ->select(
                                'tutor_id',
                                'course_cd'
                            )
                            ->selectRaw('SUM(minites) as sum_minutes')
                            ->groupBy('tutor_id', 'course_cd');

                        // コース別時間集計
                        $course_counts = DB::table($schedule_sub_query, 'course_counts')
                            ->select(
                                'course_counts.tutor_id',
                                'course_counts.course_cd',
                                'sum_minutes',
                                'mst_courses.summary_kind as summary_kind',
                                'tutors.hourly_base_wage as hourly_base_wage'
                            )
                            // 給与算出種別の取得
                            ->leftJoin('mst_courses', 'course_counts.course_cd', '=', 'mst_courses.course_cd')
                            // ベース給の取得
                            ->leftJoin('tutors', 'course_counts.tutor_id', '=', 'tutors.tutor_id')
                            ->get();

                        // 追加請求集計
                        $surcharge_counts = DB::table($surcharge_query)
                            ->select(
                                'tutor_id',
                                'mst_codes.sub_code as summary_kind'
                            )
                            ->selectRaw('SUM(minutes) as sum_minutes')
                            ->selectRaw('SUM(tuition) as sum_tuition')
                            // 給与算出種別の取得
                            ->join('mst_codes', function ($join) {
                                $join->on('surcharge_kind', '=', 'mst_codes.code')
                                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_26);
                            })
                            ->groupBy('tutor_id', 'summary_kind')
                            ->get();

                        // コースごとに集計結果を保存
                        foreach ($course_counts as $course_count) {
                            // 給与算出情報にデータを挿入する
                            $salary_summary = new SalarySummary;
                            $salary_summary->tutor_id = $course_count->tutor_id;
                            $salary_summary->salary_date = $idDate;
                            $salary_summary->summary_kind = $course_count->summary_kind;
                            if ($course_count->course_cd == AppConst::COURSE_CD_10100) {
                                $salary_summary->hour_payment = $course_count->hourly_base_wage;
                            }
                            $salary_summary->hour = $this->conversion_time($course_count->sum_minutes);
                            // 保存
                            $salary_summary->save();
                        }

                        // 事務作業給をシステムマスタから取得
                        $hourly_wage = MstSystem::where('key_id', AppConst::SYSTEM_KEY_ID_2)
                            ->firstOrFail();

                        // 追加請求の集計結果を保存
                        foreach ($surcharge_counts as $surcharge_count) {
                            // 給与算出情報にデータを挿入する
                            $salary_summary = new SalarySummary;
                            $salary_summary->tutor_id = $surcharge_count->tutor_id;
                            $salary_summary->salary_date = $idDate;
                            $salary_summary->summary_kind = $surcharge_count->summary_kind;
                            if ($surcharge_count->summary_kind == AppConst::CODE_MASTER_26_SUB_8) {
                                $salary_summary->hour_payment = $hourly_wage->value_num;
                            }
                            $salary_summary->hour = $this->conversion_time($surcharge_count->sum_minutes);
                            $salary_summary->amount = $surcharge_count->sum_tuition;
                            // 保存
                            $salary_summary->save();
                        }

                        // 講師の出社回数を集計
                        $count_goto_office = DB::table($schedule_query)
                            ->select(
                                'tutor_id',
                                'campus_cd'
                            )
                            ->selectRaw('COUNT(DISTINCT target_date) as goto_office')
                            ->groupBy('tutor_id', 'campus_cd');

                        // 交通費を取得
                        $travel_costs = DB::table($count_goto_office, 'travel_costs')
                            ->select(
                                'travel_costs.tutor_id',
                                'travel_costs.campus_cd',
                                'goto_office',
                                'tutor_campuses.travel_cost as travel_cost'
                            )
                            // 講師・校舎別に交通費を取得
                            ->join('tutor_campuses', function ($join) {
                                $join->on('travel_costs.tutor_id', '=', 'tutor_campuses.tutor_id')
                                    ->on('travel_costs.campus_cd', '=', 'tutor_campuses.campus_cd');
                            })
                            ->get();

                        // 給与算出交通費情報を保存
                        foreach ($travel_costs as $travel_cost) {
                            // 連番カウント
                            $count_seq = SalaryTravelCost::where('salary_date', $idDate)
                                ->where('tutor_id', $travel_cost->tutor_id)
                                ->count();

                            // 給与算出交通費情報にデータを挿入する
                            $salary_travel_cost = new SalaryTravelCost;
                            $salary_travel_cost->tutor_id = $travel_cost->tutor_id;
                            $salary_travel_cost->seq = $count_seq + 1;
                            $salary_travel_cost->salary_date = $idDate;
                            $salary_travel_cost->campus_cd = $travel_cost->campus_cd;
                            $salary_travel_cost->unit_price = $travel_cost->travel_cost;
                            $salary_travel_cost->times = $travel_cost->goto_office;
                            $salary_travel_cost->amount = (int)$travel_cost->travel_cost * $travel_cost->goto_office;
                            // 保存
                            $salary_travel_cost->save();
                        }

                        // 給与算出管理の処理状態を集計済みにする
                        $salary_mng = SalaryMng::where('salary_date', $idDate)->firstOrFail();
                        $salary_mng->state = AppConst::CODE_MASTER_24_1;
                        $salary_mng->save();
                    });
                } catch (\Exception  $e) {
                    // この時点では補足できないエラーとして、詳細は返さずエラーとする
                    Log::error($e);
                    return $this->illegalResponseErr();
                }

                return;
            case "#modal-dtl-confirm":
                // IDのバリデーション
                $this->validateIdsFromRequest($request, 'id');

                try {
                    // トランザクション(例外時は自動的にロールバック)
                    DB::transaction(function () use ($request) {

                        // dateの形式のバリデーションと変換
                        $idDate = $this->fmYmToDate($request['id']);

                        // 翌月を取得
                        $next_month = date('Y-m-d', strtotime('next month', strtotime($idDate)));

                        // 給与算出管理の処理状態を確定済みにする
                        $salary_mng = SalaryMng::where('salary_date', $idDate)->firstOrFail();
                        $salary_mng->confirm_date = now();
                        $salary_mng->state = AppConst::CODE_MASTER_24_2;
                        $salary_mng->save();

                        // 追加請求情報のクエリを作成
                        $surcharges = Surcharge::query()
                            ->where('approval_status', AppConst::CODE_MASTER_2_1)
                            ->where('payment_status', AppConst::CODE_MASTER_27_0)
                            ->where('payment_date', $next_month)
                            ->get();

                        foreach ($surcharges as $surcharge) {
                            $surcharge->payment_status = AppConst::CODE_MASTER_27_1;
                            $surcharge->save();
                        }
                    });
                } catch (\Exception  $e) {
                    // この時点では補足できないエラーとして、詳細は返さずエラーとする
                    Log::error($e);
                    return $this->illegalResponseErr();
                }
                return;
            case "#modal-dtl-output":
                //--------------
                // 一覧出力
                //--------------
                // formを取得
                $form = $request->all();
                $form['tutor_id'] = null;

                // 検索結果を取得
                $details = $this->getResult($form)
                    // 結果を取得
                    ->get();

                //---------------------
                // CSV出力内容を配列に保持
                //---------------------
                $arrayCsv = [];

                // ヘッダ
                $arrayCsv[] = Lang::get(
                    'message.file.salary_output.header'
                );

                // 一覧詳細
                foreach ($details as $data) {
                    // 一行出力
                    $arrayCsv[] = [
                        $data->tutor_id,
                        $data->tutor_name,
                        $data->hourly_base_wage,
                        $data->hour_personal,
                        $data->hour_two,
                        $data->hour_three,
                        $data->hour_group,
                        $data->hour_home,
                        $data->hour_practice,
                        $data->hour_high,
                        $data->hour_work,
                        $data->hour_payment,
                        $data->cost,
                        $data->untaxed_cost,
                        $data->amount1,
                        $data->unit_price1,
                        $data->times1,
                        $data->amount2,
                        $data->unit_price2,
                        $data->times2,
                        $data->amount3,
                        $data->unit_price3,
                        $data->times3
                    ];
                }

                //---------------------
                // ファイル名の取得と出力
                //---------------------
                $filename = Lang::get(
                    'message.file.salary_output.name',
                    [
                        'outputDate' => date("Ymd")
                    ]
                );

                // ファイルダウンロードヘッダーの指定
                $this->fileDownloadHeader($filename, true);

                // CSVを出力する
                $this->outputCsv($arrayCsv);

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
        // formを取得
        $form = $request->all();
        $form['tutor_id'] = null;

        $details = $this->getResult($form);

        // ページネータで返却
        return $this->getListAndPaginator($request, $details);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getDataDetail(Request $request)
    {
        // formを取得
        $form = $request->all();

        $details = $this->getResult($form);

        return $details;
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        // dateの形式のバリデーションと変換
        $salary_date = $this->fmYmToDate($request['id']);

        return [
            'salary_date' => $salary_date
        ];
    }

    /**
     * 検索結果取得(一覧と一覧出力CSV用)
     * 検索結果一覧を表示するとのCSVのダウンロードが同じため共通化
     *
     * @param mixed $form 検索フォーム
     */
    private function getResult($form)
    {
        // dateの形式のバリデーションと変換
        $idDate = $this->fmYmToDate($form['id']);

        // 詳細モーダルと一覧でクエリ分岐
        if ($form['tutor_id'] != null) {
            // 給与算出情報のクエリ作成
            $summary_query = SalarySummary::query()
                ->where('tutor_id', $form['tutor_id'])
                ->where('salary_date', $idDate)
                ->select(
                    'tutor_id',
                    'salary_date',
                    'summary_kind'
                )
                ->selectRaw('SUM(hour) as hour')
                ->selectRaw('SUM(amount) as amount')
                ->groupBy('tutor_id', 'salary_date', 'summary_kind');

            // 給与算出交通費情報のクエリ作成
            $travel_cost_query = SalaryTravelCost::query()
                ->where('tutor_id', $form['tutor_id'])
                ->where('salary_date', $idDate)
                ->select(
                    'tutor_id',
                    'salary_date',
                    'seq',
                    'unit_price',
                    'times',
                    'amount'
                )
                ->groupBy('tutor_id', 'salary_date', 'seq', 'unit_price', 'times', 'amount');
        } else {
            // 給与算出情報のクエリ作成
            $summary_query = SalarySummary::query()
                ->where('salary_date', $idDate)
                ->select(
                    'tutor_id',
                    'salary_date',
                    'summary_kind'
                )
                ->selectRaw('SUM(hour) as hour')
                ->selectRaw('SUM(amount) as amount')
                ->groupBy('tutor_id', 'salary_date', 'summary_kind');

            // 給与算出交通費情報のクエリ作成
            $travel_cost_query = SalaryTravelCost::query()
                ->where('salary_date', $idDate)
                ->select(
                    'tutor_id',
                    'salary_date',
                    'seq',
                    'unit_price',
                    'times',
                    'amount'
                )
                ->groupBy('tutor_id', 'salary_date', 'seq', 'unit_price', 'times', 'amount');
        }

        // 給与算出情報から取得
        $summary_details = DB::table($summary_query)
            ->select(
                'tutor_id',
                'salary_date'
            )
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN hour ELSE 0 END) AS hour_personal', [AppConst::CODE_MASTER_25_1])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN hour ELSE 0 END) AS hour_two', [AppConst::CODE_MASTER_25_2])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN hour ELSE 0 END) AS hour_three', [AppConst::CODE_MASTER_25_3])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN hour ELSE 0 END) AS hour_home', [AppConst::CODE_MASTER_25_5])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN hour ELSE 0 END) AS hour_practice', [AppConst::CODE_MASTER_25_6])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN hour ELSE 0 END) AS hour_high', [AppConst::CODE_MASTER_25_7])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN hour ELSE 0 END) AS hour_group', [AppConst::CODE_MASTER_25_4])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN hour ELSE 0 END) AS hour_work', [AppConst::CODE_MASTER_25_8])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN amount ELSE 0 END) AS cost', [AppConst::CODE_MASTER_25_9])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN amount ELSE 0 END) AS untaxed_cost', [AppConst::CODE_MASTER_25_10])
            ->selectRaw('0 as unit_price1')
            ->selectRaw('0 as times1')
            ->selectRaw('0 as amount1')
            ->selectRaw('0 as unit_price2')
            ->selectRaw('0 as times2')
            ->selectRaw('0 as amount2')
            ->selectRaw('0 as unit_price3')
            ->selectRaw('0 as times3')
            ->selectRaw('0 as amount3')
            ->groupBy('tutor_id', 'salary_date');

        // 給与算出交通費情報から取得
        $travel_cost_details = DB::table($travel_cost_query)
            ->select(
                'tutor_id',
                'salary_date'
            )
            ->selectRaw('0 as hour_personal')
            ->selectRaw('0 as hour_two')
            ->selectRaw('0 as hour_three')
            ->selectRaw('0 as hour_home')
            ->selectRaw('0 as hour_practice')
            ->selectRaw('0 as hour_high')
            ->selectRaw('0 as hour_group')
            ->selectRaw('0 as hour_work')
            ->selectRaw('0 as cost')
            ->selectRaw('0 as untaxed_cost')
            ->selectRaw('MAX(CASE WHEN seq = 1 THEN unit_price ELSE 0 END) AS unit_price1')
            ->selectRaw('MAX(CASE WHEN seq = 1 THEN times ELSE 0 END) AS times1')
            ->selectRaw('MAX(CASE WHEN seq = 1 THEN amount ELSE 0 END) AS amount1')
            ->selectRaw('MAX(CASE WHEN seq = 2 THEN unit_price ELSE 0 END) AS unit_price2')
            ->selectRaw('MAX(CASE WHEN seq = 2 THEN times ELSE 0 END) AS times2')
            ->selectRaw('MAX(CASE WHEN seq = 2 THEN amount ELSE 0 END) AS amount2')
            ->selectRaw('MAX(CASE WHEN seq = 3 THEN unit_price ELSE 0 END) AS unit_price3')
            ->selectRaw('MAX(CASE WHEN seq = 3 THEN times ELSE 0 END) AS times3')
            ->selectRaw('MAX(CASE WHEN seq = 3 THEN amount ELSE 0 END) AS amount3')
            ->groupBy('tutor_id', 'salary_date');

        // unionで結合
        $uniondata = $summary_details->union($travel_cost_details);

        // unionで結合したデータをまとめる
        $sum_uniondata = DB::table($uniondata)
            ->select('tutor_id', 'salary_date')
            ->selectRaw('SUM(hour_personal) AS hour_personal')
            ->selectRaw('SUM(hour_two) AS hour_two')
            ->selectRaw('SUM(hour_three) AS hour_three')
            ->selectRaw('SUM(hour_home) AS hour_home')
            ->selectRaw('SUM(hour_practice) AS hour_practice')
            ->selectRaw('SUM(hour_high) AS hour_high')
            ->selectRaw('SUM(hour_group) AS hour_group')
            ->selectRaw('SUM(hour_work) AS hour_work')
            ->selectRaw('SUM(cost) AS cost')
            ->selectRaw('SUM(untaxed_cost) AS untaxed_cost')
            ->selectRaw('SUM(unit_price1) AS unit_price1')
            ->selectRaw('SUM(times1) AS times1')
            ->selectRaw('SUM(amount1) AS amount1')
            ->selectRaw('SUM(unit_price2) AS unit_price2')
            ->selectRaw('SUM(times2) AS times2')
            ->selectRaw('SUM(amount2) AS amount2')
            ->selectRaw('SUM(unit_price3) AS unit_price3')
            ->selectRaw('SUM(times3) AS times3')
            ->selectRaw('SUM(amount3) AS amount3')
            ->groupBy('tutor_id', 'salary_date');

        // 事務作業給をシステムマスタから取得
        $hourly_wage = MstSystem::where('key_id', AppConst::SYSTEM_KEY_ID_2)
            ->firstOrFail();

        // 講師名や時給を取得
        $details = DB::table($sum_uniondata, 'sum_uniondata')
            ->select(
                'sum_uniondata.tutor_id',
                'salary_date',
                'hour_personal',
                'hour_two',
                'hour_three',
                'hour_home',
                'hour_practice',
                'hour_high',
                'hour_group',
                'hour_work',
                'cost',
                'untaxed_cost',
                'unit_price1',
                'times1',
                'amount1',
                'unit_price2',
                'times2',
                'amount2',
                'unit_price3',
                'times3',
                'amount3',
                'tutors.name as tutor_name',
                'tutors.hourly_base_wage as hourly_base_wage'
            )
            ->selectRaw('? as hour_payment', [$hourly_wage->value_num])
            // 講師名の取得
            ->leftJoin('tutors', 'sum_uniondata.tutor_id', '=', 'tutors.tutor_id');

        // 詳細モーダルと一覧で取得方法違う
        if ($form['tutor_id'] != null) {
            return $details->first();
        } else {
            return $details;
        }
    }

    /**
     * 分を時間に変換
     *
     * @param 授業時間(分)
     * @return 授業時間(時間)
     */
    public function conversion_time($minites)
    {
        $time = floor($minites / 60 * 10) / 10;

        return $time;
    }
}
