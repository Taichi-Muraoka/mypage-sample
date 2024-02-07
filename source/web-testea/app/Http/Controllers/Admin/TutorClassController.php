<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\CodeMaster;
use App\Models\Student;
use App\Models\Schedule;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * 講師授業集計 - コントローラ
 */
class TutorClassController extends Controller
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
        // 校舎プルダウン
        $rooms = $this->mdlGetRoomList(false);

        // 先月初日
        $first_date = date('Y/m/d', strtotime('first day of previous month'));

        // 先月末日
        $last_date = date('Y/m/d', strtotime('last day of previous month'));

        $editData = [
            'target_date_from' => $first_date,
            'target_date_to' => $last_date
        ];

        return view('pages.admin.tutor_class', [
            'rules' => $this->rulesForSearch(null),
            'editData' => $editData,
            'rooms' => $rooms
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
        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = Schedule::query()
            ->where('tentative_status', AppConst::CODE_MASTER_36_0)
            ->whereNotNull('tutor_id')
            ->where('absent_status', AppConst::CODE_MASTER_35_0)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd());

        // 校舎コード選択による絞り込み条件
        if (isset($form['campus_cd']) && filled($form['campus_cd'])) {
            // 検索フォームから取得（スコープ）
            $query->SearchCampusCd($form);
        }

        // 日付の絞り込み条件
        $query->SearchTargetDateFrom($form);
        $query->SearchTargetDateTo($form);

        // スケジュール情報取得し、授業時間カウントのサブクエリを作成
        $course_sub_query = DB::table($query, 'schedules')
            ->select(
                'schedules.tutor_id',
                'schedules.course_cd',
                'mst_courses.summary_kind as summary_kind'
            )
            ->selectRaw('SUM(minites) as sum_minites')
            // コースマスタとJoin
            ->leftJoin('mst_courses', 'schedules.course_cd', '=', 'mst_courses.course_cd')
            ->groupBy('schedules.tutor_id', 'schedules.course_cd', 'summary_kind');

        // コース別時間集計
        $course_count = DB::table($course_sub_query)
            ->select(
                'tutor_id'
            )
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN sum_minites ELSE 0 END) AS personal_min', [AppConst::CODE_MASTER_25_1])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN sum_minites ELSE 0 END) AS two_min', [AppConst::CODE_MASTER_25_2])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN sum_minites ELSE 0 END) AS three_min', [AppConst::CODE_MASTER_25_3])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN sum_minites ELSE 0 END) AS home_min', [AppConst::CODE_MASTER_25_5])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN sum_minites ELSE 0 END) AS exercise_min', [AppConst::CODE_MASTER_25_6])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN sum_minites ELSE 0 END) AS high_min', [AppConst::CODE_MASTER_25_7])
            ->selectRaw('MAX(CASE WHEN summary_kind = ? THEN sum_minites ELSE 0 END) AS group_min', [AppConst::CODE_MASTER_25_4])
            ->selectRaw('0 as normal_sub_get')
            ->selectRaw('0 as emergency_sub_get')
            ->selectRaw('0 as normal_sub_out')
            ->selectRaw('0 as emergency_sub_out')
            ->selectRaw('0 as trial_class')
            ->groupBy('tutor_id');

        // 代講（受）集計
        $substitute_get_count = DB::table($query)
            ->whereIn('substitute_kind', [AppConst::CODE_MASTER_34_1, AppConst::CODE_MASTER_34_2])
            ->select(
                'tutor_id'
            )
            ->selectRaw('0 AS personal_min')
            ->selectRaw('0 AS two_min')
            ->selectRaw('0 AS three_min')
            ->selectRaw('0 AS home_min')
            ->selectRaw('0 AS exercise_min')
            ->selectRaw('0 AS high_min')
            ->selectRaw('0 AS group_min')
            ->selectRaw('SUM(CASE WHEN substitute_kind = ? THEN 1 ELSE 0 END) as normal_sub_get', [AppConst::CODE_MASTER_34_1])
            ->selectRaw('SUM(CASE WHEN substitute_kind = ? THEN 1 ELSE 0 END) as emergency_sub_get', [AppConst::CODE_MASTER_34_2])
            ->selectRaw('0 as normal_sub_out')
            ->selectRaw('0 as emergency_sub_out')
            ->selectRaw('0 as trial_class')
            ->groupBy('tutor_id');

        // 代講（出）集計
        $substitute_out_count = DB::table($query)
            ->whereIn('substitute_kind', [AppConst::CODE_MASTER_34_1, AppConst::CODE_MASTER_34_2])
            ->select(
                'absent_tutor_id as tutor_id',
            )
            ->selectRaw('0 AS personal_min')
            ->selectRaw('0 AS two_min')
            ->selectRaw('0 AS three_min')
            ->selectRaw('0 AS home_min')
            ->selectRaw('0 AS exercise_min')
            ->selectRaw('0 AS high_min')
            ->selectRaw('0 AS group_min')
            ->selectRaw('0 as normal_sub_get')
            ->selectRaw('0 as emergency_sub_get')
            ->selectRaw('SUM(CASE WHEN substitute_kind = ? THEN 1 ELSE 0 END) as normal_sub_out', [AppConst::CODE_MASTER_34_1])
            ->selectRaw('SUM(CASE WHEN substitute_kind = ? THEN 1 ELSE 0 END) as emergency_sub_out', [AppConst::CODE_MASTER_34_2])
            ->selectRaw('0 as trial_class')
            ->groupBy('absent_tutor_id');

        // 体験授業集計
        $trial_class_count = DB::table($query)
            ->whereIn('lesson_kind', [AppConst::CODE_MASTER_31_5])
            ->select(
                'tutor_id',
            )
            ->selectRaw('0 AS personal_min')
            ->selectRaw('0 AS two_min')
            ->selectRaw('0 AS three_min')
            ->selectRaw('0 AS home_min')
            ->selectRaw('0 AS exercise_min')
            ->selectRaw('0 AS high_min')
            ->selectRaw('0 AS group_min')
            ->selectRaw('0 as normal_sub_get')
            ->selectRaw('0 as emergency_sub_get')
            ->selectRaw('0 as normal_sub_out')
            ->selectRaw('0 as emergency_sub_out')
            ->selectRaw('COUNT(schedule_id) as trial_class')
            ->groupBy('tutor_id');

        // unionで結合
        $uniondata = $course_count
            ->union($substitute_get_count)
            ->union($substitute_out_count)
            ->union($trial_class_count);

        // unionで結合したデータをまとめる
        $schedule_count = DB::table($uniondata, 'uniondata')
            ->select(
                'uniondata.tutor_id'
            )
            ->selectRaw('SUM(personal_min) AS personal_min')
            ->selectRaw('SUM(two_min) AS two_min')
            ->selectRaw('SUM(three_min) AS three_min')
            ->selectRaw('SUM(home_min) AS home_min')
            ->selectRaw('SUM(exercise_min) AS exercise_min')
            ->selectRaw('SUM(high_min) AS high_min')
            ->selectRaw('SUM(group_min) AS group_min')
            ->selectRaw('SUM(normal_sub_get) as normal_sub_get')
            ->selectRaw('SUM(emergency_sub_get) as emergency_sub_get')
            ->selectRaw('SUM(normal_sub_out) as normal_sub_out')
            ->selectRaw('SUM(emergency_sub_out) as emergency_sub_out')
            ->selectRaw('SUM(trial_class) as trial_class')
            ->groupBy('uniondata.tutor_id');

        // 講師名をJoinしたデータ
        $schedule_count_join_tutor = DB::table($schedule_count, 'schedule_count')
            ->select(
                'schedule_count.tutor_id',
                'tutors.name as tutor_name',
                'personal_min',
                'two_min',
                'three_min',
                'home_min',
                'exercise_min',
                'high_min',
                'group_min',
                'normal_sub_get',
                'emergency_sub_get',
                'normal_sub_out',
                'emergency_sub_out',
                'trial_class'
            )
            // 講師名の取得
            ->leftJoin('tutors', 'schedule_count.tutor_id', '=', 'tutors.tutor_id');

        return $this->getListAndPaginator($request, $schedule_count_join_tutor, function ($items) use ($form) {
            // データ加工
            foreach ($items as $item) {
                // 検索条件を付与(モーダル表示に使用)
                $item->campus_cd = $form['campus_cd'];
                $item->target_date_from = $form['target_date_from'];
                $item->target_date_to = $form['target_date_to'];
                // 授業(分)を授業(時間)に変換(小数点1位以下を切り捨て)
                $item->personal_min = $this->dtConversionTime($item->personal_min);
                $item->two_min = $this->dtConversionTime($item->two_min);
                $item->three_min = $this->dtConversionTime($item->three_min);
                $item->home_min = $this->dtConversionTime($item->home_min);
                $item->exercise_min = $this->dtConversionTime($item->exercise_min);
                $item->high_min = $this->dtConversionTime($item->high_min);
                $item->group_min = $this->dtConversionTime($item->group_min);
            }

            return $items;
        });
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
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 校舎
        $validationCampusList =  function ($attribute, $value, $fail) {
            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 日付チェック
        $validationDateFromTo = function ($attribute, $value, $fail) use ($request) {
            $from_date = $request['target_date_from'];
            $to_date = $request['target_date_to'];

            $from_date_plus_half_year = Carbon::parse($from_date)->addMonthsNoOverflow(6)->toDateString();

            if ($from_date_plus_half_year < $to_date) {
                // 不正な値エラー
                return $fail(Lang::get('validation.target_date_term'));
            }
        };

        $ruleTargetDate = Schedule::getFieldRule('target_date');
        // FromとToの大小チェックバリデーションを追加(Fromが存在する場合のみ)
        $validateFromTo = [];
        $keyFrom = 'target_date_from';
        if (isset($request[$keyFrom]) && filled($request[$keyFrom])) {
            $validateFromTo = ['after_or_equal:' . $keyFrom];
        }

        $rules += Schedule::fieldRules('campus_cd', [$validationCampusList]);
        $rules += ['target_date_from' => array_merge(['required'], $ruleTargetDate, [$validationDateFromTo])];
        $rules += ['target_date_to' => array_merge(['required'], $validateFromTo, $ruleTargetDate)];

        return $rules;
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getData(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request);

        // formを取得
        $form = $request->all();

        // 講師ID取得
        $tutor_id = $request->input('tutor_id');

        // クエリ作成
        $query = Schedule::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // 校舎コード選択による絞り込み条件
        if ($request->input('campus_cd') != null) {
            // 検索フォームから取得（スコープ）
            $query->SearchCampusCd($form);
        }

        // 日付の絞り込み条件
        $query->SearchTargetDateFrom($form);
        $query->SearchTargetDateTo($form);

        $schedules = $query
            ->select(
                'schedules.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'students.enter_date as enter_date',
                'schedules.target_date',
                // 会員ステータス
                'mst_codes.name as stu_status',
            )
            ->where('tutor_id', '=', $tutor_id)
            ->where('lesson_kind', '=', AppConst::CODE_MASTER_31_5)
            ->where('schedules.tentative_status', AppConst::CODE_MASTER_36_0)
            ->whereNotNull('schedules.tutor_id')
            ->whereNull('schedules.deleted_at')
            ->where('schedules.absent_status', AppConst::CODE_MASTER_35_0)
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'schedules.student_id', '=', 'students.student_id')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('students.stu_status', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_28);
            })
            ->get();

        return [
            'schedules' => $schedules
        ];
    }
}
