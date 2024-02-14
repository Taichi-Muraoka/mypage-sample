<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Schedule;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

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
        $first_date = date('Y/m/d', strtotime('first day of previous month'));

        // 先月末日
        $last_date = date('Y/m/d', strtotime('last day of previous month'));

        $editData = [
            'target_date_from' => $first_date,
            'target_date_to' => $last_date
        ];

        return view('pages.admin.overtime', [
            'rules' => $this->rulesForSearch(null),
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
        $validator = Validator::make($request->all(), $this->rulesForSearch($request));
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
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // 検索結果取得
        $overtime_worker = $this->getSearchResult($form);

        // ページネータで返却
        return $this->getListAndPaginator($request, $overtime_worker, function ($items) {

            // データ加工
            foreach ($items as $item) {
                // 時間変換
                $item->sum_minites = $this->dtConversionHourMinites($item->sum_minites);
                $item->over_time = $this->dtConversionHourMinites($item->over_time);
                $item->late_time = $this->dtConversionHourMinites($item->late_time);
                $item->over_late_time = $this->dtConversionHourMinites($item->over_late_time);
            }

            return $items;
        });
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return mixed ルール
     */
    private function rulesForSearch(?Request $request)
    {
        $rules = array();

        // $this->debug($request['target_date_from']);

        $ruleTargetDate = Schedule::getFieldRule('target_date');
        
        // FromとToの大小チェックバリデーションを追加(Fromが存在する場合のみ)
        $validateFromTo = [];
        $keyFrom = 'target_date_from';
        if (isset($request[$keyFrom]) && filled($request[$keyFrom])) {
            $validateFromTo = ['after_or_equal:' . $keyFrom];
        }

        $rules += ['target_date_from' => $ruleTargetDate];
        $rules += ['target_date_to' => array_merge($validateFromTo, $ruleTargetDate)];

        return $rules;
    }

    /**
     * 詳細取得（CSV出力の確認モーダル用）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        // ここでの処理は特になし
        return [];
    }

    /**
     * モーダル処理（CSV出力）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function execModal(Request $request)
    {
        //--------------
        // 一覧出力
        //--------------
        // formを取得
        $form = $request->all();

        // 検索結果を取得
        $overtime_worker = $this->getSearchResult($form)
            // 結果を取得
            ->get();

        //---------------------
        // CSV出力内容を配列に保持
        //---------------------
        $arrayCsv = [];

        // ヘッダ
        $arrayCsv[] = Lang::get(
            'message.file.overtime_output.header'
        );

        // 一覧詳細
        foreach ($overtime_worker as $data) {
            // 一行出力
            $arrayCsv[] = [
                $data->tutor_id,
                $data->tutor_name,
                // $data->target_dateが日付型ではないためこちらのフォーマットを使用
                date('Y/m/d', strtotime($data->target_date)),
                $this->dtConversionHourMinites($data->sum_minites),
                $this->dtConversionHourMinites($data->over_time),
                $this->dtConversionHourMinites($data->late_time),
                $this->dtConversionHourMinites($data->over_late_time),
            ];
        }

        //---------------------
        // ファイル名の取得と出力
        //---------------------
        $filename = Lang::get(
            'message.file.overtime_output.name',
            [
                'outputDate' => date("Ymd")
            ]
        );

        // ファイルダウンロードヘッダーの指定
        $this->fileDownloadHeader($filename, true);

        // CSVを出力する
        $this->outputCsv($arrayCsv);

        return;
    }

    /**
     * 検索結果取得(一覧と一覧出力CSV用)
     * 検索結果一覧を表示するとのCSVのダウンロードが同じため共通化
     *
     * @param mixed $form 検索フォーム
     */
    private function getSearchResult($form)
    {
        // クエリを作成
        $query = Schedule::query()
            ->whereNotNull('tutor_id');

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        }

        // 日付の絞り込み条件
        $query->SearchTargetDateFrom($form);
        $query->SearchTargetDateTo($form);

        // サブクエリ作成
        $sub_query = DB::table($query)
            ->select(
                'schedule_id',
                'tutor_id',
                'target_date',
                'start_time',
                'end_time',
                'minites',
            )
            ->selectRaw('CASE WHEN TIME_TO_SEC(start_time) >= TIME_TO_SEC(?) THEN minites ELSE 0 END AS late_time1', ['22:00'])
            ->selectRaw('CASE WHEN (TIME_TO_SEC(end_time) > TIME_TO_SEC(?) and TIME_TO_SEC(start_time) < TIME_TO_SEC(?)) 
                THEN (TIME_TO_SEC(end_time) - TIME_TO_SEC(?)) / 60 ELSE 0 END AS late_time2', ['22:00', '22:00', '22:00']);

        // 超過勤務者・深夜労働取得
        $overtime_worker = DB::table($sub_query)
            ->select(
                'tutor_id',
                'target_date'
            )
            ->selectRaw('SUM(minites) as sum_minites')
            ->selectRaw('CASE WHEN SUM(minites) > 480 THEN SUM(minites) - 480 ELSE 0 END as over_time')
            ->selectRaw('SUM(late_time1) + SUM(late_time2) as late_time')
            ->selectRaw('CASE WHEN (SUM(late_time1) + SUM(late_time2) > 0 and SUM(minites) > 480) and SUM(late_time1) + SUM(late_time2) >= SUM(minites) - 480 THEN SUM(minites) - 480
                WHEN (SUM(late_time1) + SUM(late_time2) > 0 and SUM(minites) > 480) and SUM(late_time1) + SUM(late_time2) <= SUM(minites) - 480 THEN SUM(late_time1) + SUM(late_time2)
                ELSE 0 END AS over_late_time')
            ->groupBy('tutor_id', 'target_date')
            ->having(function ($orQuery) {
                $orQuery->havingRaw('over_time > 0')
                    ->orHavingRaw('late_time > 0');
            })
            ->orderBy('target_date')
            ->orderBy('tutor_id');

        // 講師名JOINと深夜超過
        $overtime_worker_join_tutor = DB::table($overtime_worker, 'overtime_worker')
            ->select(
                'overtime_worker.tutor_id',
                'target_date',
                'sum_minites',
                'over_time',
                'late_time',
                'over_late_time',
                'tutors.name as tutor_name',
            )
            // 講師名の取得
            ->leftJoin('tutors', 'overtime_worker.tutor_id', '=', 'tutors.tutor_id');

        return $overtime_worker_join_tutor;
    }
}
