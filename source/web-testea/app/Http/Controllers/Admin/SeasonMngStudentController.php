<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\SeasonMng;
use App\Models\SeasonStudentRequest;
use App\Models\SeasonStudentTime;
use App\Models\SeasonTutorRequest;
use App\Models\SeasonTutorPeriod;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TutorCampus;
use App\Models\TutorSubject;
use App\Models\Schedule;
use App\Models\MstSubject;
use App\Models\MstCourse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FuncSeasonTrait;
use App\Http\Controllers\Traits\FuncScheduleTrait;
use App\Exceptions\ReadDataValidateException;

/**
 * 特別期間講習 生徒提出スケジュール - コントローラ
 */
class SeasonMngStudentController extends Controller
{

    // 機能共通処理：特別期間講習
    use FuncSeasonTrait;
    // 機能共通処理：スケジュール関連
    use FuncScheduleTrait;

    /**
     * コマ組み講師選択ID名prefix
     */
    const PLAN_SELTID_PREFIX = "sel_tid_";

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
        // 特別期間リストを取得
        $seasonList = $this->fncSasnGetGetSeasonList();

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 生徒リストを取得
        $students = $this->mdlGetStudentList();

        // 生徒登録ステータスのプルダウン取得
        $regStatusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_5);

        // コマ組みステータスのプルダウン取得
        $planStatusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_47);

        return view('pages.admin.season_mng_student', [
            'seasonList' => $seasonList,
            'rooms' => $rooms,
            'students' => $students,
            'regStatusList' => $regStatusList,
            'planStatusList' => $planStatusList,
            'editData' => null,
            'rules' => $this->rulesForSearch()
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
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 特別期間コード
        $validationSeasonList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $list = $this->fncSasnGetGetSeasonList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒
        $validationStudentList =  function ($attribute, $value, $fail) {

            // 生徒リストを取得
            $list = $this->mdlGetStudentList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒登録ステータス
        $validationRegStatusList =  function ($attribute, $value, $fail) {

            // ステータスリストを取得
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_5);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック コマ組みステータス
        $validationPlanStatusList =  function ($attribute, $value, $fail) {

            // ステータスリストを取得
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_47);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 特別期間コード
        $rules += SeasonStudentRequest::fieldRules('season_cd', [$validationSeasonList]);
        // 校舎コード
        $rules += SeasonStudentRequest::fieldRules('campus_cd', [$validationRoomList]);
        // 生徒ID
        $rules += SeasonStudentRequest::fieldRules('student_id', [$validationStudentList]);
        // 登録ステータス
        $rules += SeasonStudentRequest::fieldRules('regist_status', [$validationRegStatusList]);
        // コマ組みステータス
        $rules += SeasonStudentRequest::fieldRules('plan_status', [$validationPlanStatusList]);

        return $rules;
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
        Validator::make($request->all(), $this->rulesForSearch())->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成（生徒日程連絡情報）
        $query = SeasonStudentRequest::query();

        // 特別期間コードの絞り込み条件
        $query->SearchSeasonCd($form);

        // 校舎の絞り込み条件
        $query->SearchCampusCd($form);

        // 生徒の絞り込み条件
        $query->SearchSid($form);

        // 登録ステータスの絞り込み条件
        $query->SearchRegistStatus($form);

        // コマ組みステータスの絞り込み条件
        $query->SearchPlanStatus($form);

        // 特別期間講習 生徒連絡情報表示用のquery作成・データ取得
        // ガードあり
        $SeasonRequests = $this->fncSasnGetSeasonStudentQuery($query)
            ->orderby('season_student_requests.season_cd', 'desc')
            ->orderby('season_student_requests.apply_date', 'desc')
            ->orderby('season_student_requests.student_id', 'asc')
            ->orderby('season_student_requests.campus_cd', 'asc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $SeasonRequests);
    }

    //==========================
    // スケジュール詳細画面
    //==========================

    /**
     * 生徒提出スケジュール詳細画面
     *
     * @param int $seasonStudentId 生徒連絡情報ID
     * @return view
     */
    public function detail($seasonStudentId)
    {
        // IDのバリデーション
        $this->validateIds($seasonStudentId);

        // 現在日を取得
        $today = date("Y-m-d");

        // データを取得（生徒連絡情報）ガードあり
        // 生徒登録状態＝登録済のデータのみ
        $seasonStudent = $this->fncSasnGetSeasonStudent($seasonStudentId, AppConst::CODE_MASTER_5_1);

        // クエリを作成（特別期間講習管理情報）
        $exists = SeasonMng::where('season_cd', $seasonStudent->season_cd)
            ->where('campus_cd', $seasonStudent->campus_cd)
            // 生徒受付期間内
            ->where('season_mng.s_start_date', '<=', $today)
            ->where('season_mng.s_end_date', '>=', $today)
            ->exists();

        $planBtnDisabled = true;
        if ($exists) {
            // 生徒受付期間内ならば、コマ組みボタンを押下可とする
            $planBtnDisabled = false;
        }

        // 特別期間日付リストを取得（校舎コード・特別期間コード指定）
        $dateList = $this->fncSasnGetSeasonDate($seasonStudent->campus_cd, $seasonStudent->season_cd);

        // 登録済みスケジュール情報を取得する
        // クエリを作成（スケジュール情報）
        $query = Schedule::query();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // データを取得
        $schedules = $query
            ->select(
                'schedules.target_date',
                'schedules.period_no',
                'schedules.subject_cd',
                'tutors.name as tutor_name',
                'mst_subjects.name as subject_name',
                'mst_codes.name as tentative_status_name',
            )
            // 科目名の取得
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('schedules.subject_cd', 'mst_subjects.subject_cd');
            })
            // 講師名の取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('schedules.tutor_id', 'tutors.tutor_id');
            })
            // コードマスターとJOIN（仮登録状態）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.tentative_status', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_36);
            })
            // 対象の生徒ID
            ->where('schedules.student_id', $seasonStudent->student_id)
            // 校舎コードで絞り込み
            ->where('schedules.campus_cd', $seasonStudent->campus_cd)
            // 授業種別＝特別期間講習
            ->where('schedules.lesson_kind', AppConst::CODE_MASTER_31_2)
            // 対象の特別期間の日付範囲
            ->whereIn('schedules.target_date', array_column($dateList, 'dateYmd'))
            ->orderBy('schedules.target_date')
            ->orderBy('schedules.period_no')
            ->get();

        // データを取得（受講回数情報）
        $subjectTimes = $this->fncSasnGetSeasonStudentTime($seasonStudentId);

        // 登録スケジュールについて、教科毎の件数を取得
        $lessonCount = $schedules->groupBy('subject_cd')
            ->map(function ($lesson) {
                return $lesson->count();
            });

        foreach ($subjectTimes as $subject) {
            // 受講回数情報に教科毎のスケジュール件数を付加
            // 登録がない教科には0をセットする
            $subject->count = $lessonCount[$subject->subject_cd] ?? 0;
        }

        // コマ組みステータスのプルダウン取得
        $planStatusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_47);

        return view('pages.admin.season_mng_student-detail', [
            'rules' => $this->rulesForInputStatus(null),
            'seasonStudent' => $seasonStudent,
            'subjectTimesList' => $subjectTimes,
            'schedules' => $schedules,
            'planStatusList' => $planStatusList,
            'planBtnDisabled' => $planBtnDisabled,
            'editData' => $seasonStudent,
        ]);
    }

    /**
     * バリデーション(コマ組みステータス登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInput(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInputStatus());
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(コマ組みステータス登録用)
     *
     * @return array ルール
     */
    private function rulesForInputStatus()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 特別期間コード
        $validationSeasonList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $list = $this->fncSasnGetGetSeasonList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        // 独自バリデーション: リストのチェック コマ組みステータス
        $validationPlanStatusList =  function ($attribute, $value, $fail) {

            // ステータスリストを取得
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_47);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 生徒連絡ID
        $rules += SeasonStudentRequest::fieldRules('season_student_id', ['required']);
        // 校舎コード
        $rules += SeasonStudentRequest::fieldRules('campus_cd', ['required', $validationRoomList]);
        // 特別期間コード
        $rules += SeasonStudentRequest::fieldRules('season_cd', ['required', $validationSeasonList]);
        // コマ組みステータス
        $rules += SeasonStudentRequest::fieldRules('plan_status', ['required', $validationPlanStatusList]);

        return $rules;
    }

    /**
     * 編集処理(コマ組みステータス登録)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function update(Request $request)
    {
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInputStatus())->validate();

        // MEMO: 必ず登録する項目のみに絞る。
        $form = $request->only(
            'plan_status'
        );

        // 対象データを取得(IDでユニークに取る)
        $seasonStudent = SeasonStudentRequest::where('season_student_id', $request['season_student_id'])
            ->where('campus_cd', $request['campus_cd'])
            ->where('season_cd', $request['season_cd'])
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $seasonStudent->fill($form)->save();

        return;
    }

    //==========================
    // コマ組み画面
    //==========================

    /**
     * 生徒教科別コマ組み画面
     *
     * @param int $seasonStudentId 生徒連絡情報ID
     * @param string $subjectCd 科目コード
     * @return view
     */
    public function plan($seasonStudentId, $subjectCd)
    {
        // IDのバリデーション
        $this->validateIds($seasonStudentId);
        // コードのバリデーション
        $this->validateIds($subjectCd);

        // 現在日を取得
        $today = date("Y-m-d");

        // クエリを作成（生徒実施回数情報）
        $query = SeasonStudentTime::query();

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $model = new SeasonStudentRequest;
        $query->where($this->guardRoomAdminTableWithRoomCd($model));

        // データを取得
        $seasonStudent = $query
            ->select(
                'season_student_times.season_student_id',
                'season_student_requests.season_cd',
                'season_student_requests.campus_cd',
                'season_student_requests.student_id',
                DB::raw('LEFT(season_student_requests.season_cd, 4) as year'),
                'mst_codes.gen_item2 as season_name',
                'room_names.room_name as campus_name',
                'students.name as student_name',
                'season_student_times.subject_cd',
                'mst_subjects.name as subject_name',
                'season_student_times.times'
            )
            // 生徒連絡情報とJOIN
            ->sdJoin(SeasonStudentRequest::class, function ($join) {
                $join->on('season_student_times.season_student_id', 'season_student_requests.season_student_id');
            })
            // 校舎名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('season_student_requests.campus_cd', '=', 'room_names.code');
            })
            // 生徒名の取得
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('season_student_requests.student_id', 'students.student_id');
            })
            // 科目名の取得
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('season_student_times.subject_cd', 'mst_subjects.subject_cd');
            })
            // コードマスターとJOIN（期間区分）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on(DB::raw('RIGHT(season_student_requests.season_cd, 2)'), '=', 'mst_codes.gen_item1')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_38);
            })
            // 生徒連絡IDを指定
            ->where('season_student_times.season_student_id', $seasonStudentId)
            // 科目コードを指定
            ->where('season_student_times.subject_cd', $subjectCd)
            // 登録済データのみ表示可
            ->where('season_student_requests.regist_status', AppConst::CODE_MASTER_5_1)
            ->firstOrFail();

        // 特別期間講習管理情報の生徒受付期間内かチェック
        SeasonMng::where('season_cd', $seasonStudent->season_cd)
            ->where('campus_cd', $seasonStudent->campus_cd)
            // 生徒受付期間内
            ->where('season_mng.s_start_date', '<=', $today)
            ->where('season_mng.s_end_date', '>=', $today)
            ->firstOrFail();

        // 時限リストを取得（校舎・時間割区分から）
        $periodList = $this->mdlGetPeriodListByKind($seasonStudent['campus_cd'], AppConst::CODE_MASTER_37_1);

        // 特別期間日付リストを取得（校舎・特別期間コード指定）
        $dateList = $this->fncSasnGetSeasonDate($seasonStudent['campus_cd'], $seasonStudent['season_cd']);

        // データを取得（生徒連絡コマ情報）
        $studentPeriods = $this->fncSasnGetSeasonStudentPeriod($seasonStudentId);

        // チェックボックスをセットするための値を生成（連絡コマ情報）
        // 例：['20231225_1', '20231226_2']
        // 授業不可コマ情報を$exceptData にセット（グレー網掛け部）
        $exceptData = [];
        foreach ($studentPeriods as $datePeriod) {
            // 配列に追加
            array_push($exceptData, $datePeriod->lesson_date->format('Ymd') . '_' . $datePeriod->period_no);
        }

        // 期間中の授業情報取得
        // クエリを作成（スケジュール情報）
        $query = Schedule::query();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // データを取得
        $schedules = $query
            ->select(
                'schedules.target_date',
                'schedules.period_no',
                'mst_courses.short_name as course_sname',
                'mst_subjects.name as subject_name',
                'tutors.name as tutor_name'
            )
            // 講師名の取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('schedules.tutor_id', 'tutors.tutor_id');
            })
            // 科目名の取得
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('schedules.subject_cd', 'mst_subjects.subject_cd');
            })
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd');
            })
            // 対象の生徒ID
            ->where('schedules.student_id', $seasonStudent->student_id)
            // 対象の特別期間の日付範囲
            ->whereIn('schedules.target_date', array_column($dateList, 'dateYmd'))
            ->orderBy('schedules.target_date')
            ->orderBy('schedules.period_no')
            ->get();

        // チェックボックスをセットするための値を生成（スケジュール情報）
        // 例：['20231225_1', '20231226_2']
        // 登録済みスケジュール情報を$chkWsData にセット（グリーン網掛け部）
        $chkWsData = [];
        $lessonInfo = [];
        foreach ($schedules as $schedule) {
            // 配列に追加
            $datePeriodKey = $schedule->target_date->format('Ymd') . '_' . $schedule->period_no;
            array_push($chkWsData, $datePeriodKey);
            // $lessonInfo に表示する授業情報をセット
            array_push($lessonInfo, [
                // '20231225_1'の形式
                'key' => $datePeriodKey,
                // 講師名（講師名なしの場合はコース名）
                'tutor' => $schedule->tutor_name ?? $schedule->course_sname,
                // 科目名
                'subject' => $schedule->subject_name ?? ""
            ]);
        }

        return view('pages.admin.season_mng_student-plan', [
            'seasonStudent' => $seasonStudent,
            'periodList' => $periodList,
            'dateList' => $dateList,
            'editData' => [
                'chkWs' => $chkWsData,
            ],
            'exceptData' => $exceptData,
            'lessonInfo' => $lessonInfo,
        ]);
    }

    /**
     * 登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function createPlan(Request $request)
    {

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInputPlan($request))->validate();

        // 登録データの関連バリデーション + 登録データを$regDatasにセット
        try {
            $regDatas = $this->validateScheduleRelated($request);
        } catch (ReadDataValidateException  $e) {
            // 通常は事前にバリデーションするため、ここはありえないのでエラーとする
            return $this->responseErr();
        }

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request, $regDatas) {
            //------------------------
            // スケジュール登録
            //------------------------
            foreach ($regDatas as $regData) {
                // スケジュール情報登録
                $this->fncScheCreateSchedule($regData, $regData['target_date'], $regData['booth_cd'], AppConst::CODE_MASTER_32_1);
            }
        });

        return;
    }

    /**
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInputPlan(Request $request)
    {
        // リクエストデータチェック（選択内容チェック）
        $validator = Validator::make($request->all(), $this->rulesForInputPlan($request));
        if (count($validator->errors()) != 0) {
            // 項目チェックエラーがある場合はここでエラー情報を返す
            return $validator->errors();
        }

        // 登録データの関連バリデーション
        try {
            $datas = $this->validateScheduleRelated($request);
        } catch (ReadDataValidateException $e) {
            // 入力項目とは別のバリデーションエラーとして返却
            return ['validate_schedule' => [$e->getMessage()]];
        }
    }

    /**
     * バリデーションルールを取得(コマ組み登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInputPlan(?Request $request)
    {

        $requestSelTutors = [];
        if ($request) {
            // $request から講師選択のみ抽出
            $requestSelTutors = array_filter($request->input(), function ($value, $key) {
                return str_starts_with($key, self::PLAN_SELTID_PREFIX) && !empty($value);
            }, ARRAY_FILTER_USE_BOTH);
        }

        $rules = array();

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 特別期間コード
        $validationSeasonList =  function ($attribute, $value, $fail) {

            // 特別期間リストを取得
            $list = $this->fncSasnGetGetSeasonList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 科目コード
        $validationSubjectList =  function ($attribute, $value, $fail) {

            // 科目リストを取得
            $list = $this->mdlGetSubjectList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 講師選択が１つ以上あるかチェック
        $validationSelectCount = function ($attribute, $value, $fail) use ($requestSelTutors) {

            // 講師選択が1つもない場合エラーとする
            if (count($requestSelTutors) == 0) {
                return false;
            }
            return true;
        };

        // 独自バリデーション: 講師選択の数が希望回数内かチェック
        $validationSelectCountOver = function ($attribute, $value, $fail) use ($request, $requestSelTutors) {

            if (!$request) {
                return true;
            }
            if (
                !$request->filled('campus_cd') || !$request->filled('season_student_id') ||
                !$request->filled('season_cd') || !$request->filled('subject_cd')
            ) {
                // 検索項目がrequestにない場合はチェックしない
                return true;
            }

            // 特別期間日付リストを取得（校舎コード・特別期間コード指定）
            $dateList = $this->fncSasnGetSeasonDate($request['campus_cd'], $request['season_cd']);

            // 校舎コードガード用model
            $model = new SeasonStudentRequest;

            // 科目別実施希望回数を取得（生徒実施回数情報）
            $seasonStudent = SeasonStudentTime::select(
                'season_student_requests.student_id',
                'season_student_times.times'
            )
                // 生徒連絡情報とJOIN
                ->sdJoin(SeasonStudentRequest::class, function ($join) {
                    $join->on('season_student_times.season_student_id', 'season_student_requests.season_student_id');
                })
                // 生徒連絡IDを指定
                ->where('season_student_times.season_student_id', $request['season_student_id'])
                // 科目コードを指定
                ->where('season_student_times.subject_cd', $request['subject_cd'])
                // 校舎コードを指定
                ->where('season_student_requests.campus_cd', $request['campus_cd'])
                // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd($model))
                // 登録済データのみ表示可
                ->where('season_student_requests.regist_status', AppConst::CODE_MASTER_5_1)
                ->firstOrFail();

            // 登録済みスケジュール数を取得（スケジュール情報）
            $scheduleCnt = Schedule::
                // 対象の生徒ID
                where('schedules.student_id', $seasonStudent->student_id)
                // 校舎コードを指定
                ->where('schedules.campus_cd', $request['campus_cd'])
                // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                // 対象の特別期間の日付範囲
                ->whereIn('schedules.target_date', array_column($dateList, 'dateYmd'))
                // 科目コードを指定
                ->where('schedules.subject_cd', $request['subject_cd'])
                // 授業種別を指定（特別期間講習）
                ->where('schedules.lesson_kind', AppConst::CODE_MASTER_31_2)
                ->count();

            // （講師選択の数＋登録済み講習スケジュール数）が受講希望回数を超えていたらエラーとする
            if (count($requestSelTutors) + $scheduleCnt > $seasonStudent->times) {
                return false;
            }
            return true;
        };

        // 独自バリデーション: 講師選択の内容が正しいかチェック
        $validationDateId = function ($attribute, $value, $fail) use ($request, $requestSelTutors) {

            if (!$request->filled('campus_cd') || !$request->filled('season_cd')) {
                // 検索項目がrequestにない場合はチェックしない
                return true;
            }

            // 特別期間日付リストを取得（校舎コード・特別期間コード指定）
            $dateIdList = $this->fncSasnGetSeasonDate($request['campus_cd'], $request['season_cd']);

            // 時限リストを取得（講師ID・時間割区分から）
            $periodList = $this->mdlGetPeriodListByKind($request['campus_cd'], AppConst::CODE_MASTER_37_1);

            // 講師選択のチェック
            foreach ($requestSelTutors as $key => $val) {
                $datePeriodKey = str_replace(self::PLAN_SELTID_PREFIX, "", $key);
                $datePeriod = $this->fncSasnSplitDatePeriodKey($datePeriodKey);

                // 日付のチェック。配列に存在するか
                if (!in_array($datePeriod['dateId'], array_column($dateIdList, 'dateId'))) {
                    // 存在しない場合はエラー
                    return false;
                }

                // 時限のチェック。配列のキーとして存在するか
                if (!isset($periodList[$datePeriod['period_no']])) {
                    // 存在しない場合はエラー
                    return false;
                }

                // 講師IDのチェック
                $list = $this->mdlGetTutorList($request['campus_cd']);
                if (!isset($list[$val])) {
                    // 不正な値エラー
                    return false;
                }
            }
            return true;
        };

        // 生徒連絡ID
        $rules += SeasonStudentRequest::fieldRules('season_student_id', ['required']);
        // 校舎コード
        $rules += SeasonStudentRequest::fieldRules('campus_cd', ['required', $validationRoomList]);
        // 特別期間コード
        $rules += SeasonStudentRequest::fieldRules('season_cd', ['required', $validationSeasonList]);
        // 科目コード
        $rules += SeasonStudentTime::fieldRules('subject_cd', ['required', $validationSubjectList]);

        // 入力項目と紐づけないバリデーションは以下のように指定する
        // 講師選択チェック
        Validator::extendImplicit('array_required', $validationSelectCount);
        Validator::extendImplicit('invalid_count_of_select', $validationSelectCountOver);
        Validator::extendImplicit('invalid_input', $validationDateId);
        $rules += ['validate_selTutor' => ['array_required', 'invalid_count_of_select', 'invalid_input']];

        return $rules;
    }

    /**
     * スケジュール登録データの関連バリデーション
     * 登録データの設定も行う
     * バリデーションエラー時はException発生し、処理を継続しない
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 登録データ（スケジュール情報）
     */
    private function validateScheduleRelated(?Request $request)
    {
        $regDatas = [];

        $campusCd = $request->input('campus_cd');
        $subjectCd = $request->input('subject_cd');
        $studentId = $request->input('student_id');

        // $request から講師選択のみ抽出
        $requestSelTutors = array_filter($request->input(), function ($value, $key) {
            return str_starts_with($key, self::PLAN_SELTID_PREFIX) && !empty($value);
        }, ARRAY_FILTER_USE_BOTH);

        // ブースマスタから対象校舎のブースを取得（両者通塾用）
        $howToKind = AppConst::CODE_MASTER_33_0;
        $boothFirst = null;
        $arrMstBooths = $this->fncScheGetBoothFromMst($campusCd, AppConst::CODE_MASTER_33_0);
        if ($arrMstBooths) {
            $boothFirst = $arrMstBooths[0];
        } else {
            // 両者通塾用のブースがない場合、家庭教師用のブースを取得（家庭教師教室対応）
            $arrMstBooths = $this->fncScheGetBoothFromMst($campusCd, AppConst::CODE_MASTER_33_4);
            if ($arrMstBooths) {
                $howToKind = AppConst::CODE_MASTER_33_4;
                $boothFirst = $arrMstBooths[0];
            } else {
                // 両者通塾用・家庭教師用のブースがない場合、両者オンライン用のブースを取得（オンライン教室対応）
                $arrMstBooths = $this->fncScheGetBoothFromMst($campusCd, AppConst::CODE_MASTER_33_2);
                if ($arrMstBooths) {
                    $howToKind = AppConst::CODE_MASTER_33_2;
                    $boothFirst = $arrMstBooths[0];
                }
            }
        }

        // 個別指導コース情報の取得（コース種別・給与算出種別から）
        $course = $this->fncScheGetCourseInfoByKind(AppConst::CODE_MASTER_42_1, AppConst::CODE_MASTER_25_1);
        $courseCd = $course->course_cd;

        // 講師選択情報毎にループ
        foreach ($requestSelTutors as $key => $val) {

            // $requestの講師選択情報を切り出し
            $datePeriodKey = str_replace(self::PLAN_SELTID_PREFIX, "", $key);
            $datePeriod = $this->fncSasnSplitDatePeriodKey($datePeriodKey);

            $targetDate = $datePeriod['lesson_date'];
            $periodNo = $datePeriod['period_no'];
            $tutorId = $val;

            // 校舎・時間割区分・指定時限から、対応する時間割情報を取得
            $periodInfo = $this->fncScheGetTimetableByPeriod($campusCd, AppConst::CODE_MASTER_37_1, $periodNo);
            $startTime = $periodInfo->start_time;
            $endTime = $periodInfo->end_time;

            // 講師スケジュール重複チェック
            $chk = $this->fncScheChkDuplidateTid(
                $targetDate,
                $startTime,
                $endTime,
                $tutorId,
                null,
                false
            );
            if (!$chk) {
                // 講師スケジュール重複
                throw new ReadDataValidateException(Lang::get('validation.duplicate_tutor')
                    . "(" . $targetDate .  " " . $periodNo . "限" . ")");
            }

            // 生徒のスケジュール重複チェック
            $chk = $this->fncScheChkDuplidateSid(
                $targetDate,
                $startTime,
                $endTime,
                $studentId,
                null,
                false
            );
            if (!$chk) {
                // 生徒スケジュール重複
                throw new ReadDataValidateException(Lang::get('validation.duplicate_student')
                    . "(" . $targetDate .  " " . $periodNo . "限" . ")");
            }

            // ブースのチェック・空きブース取得
            $booth = $this->fncScheSearchBooth(
                $campusCd,
                $boothFirst,
                $targetDate,
                $periodNo,
                $howToKind,
                null,
                false
            );
            if (!$booth) {
                // 空きブース無し
                throw new ReadDataValidateException(Lang::get('validation.duplicate_booth')
                    . "(" . $targetDate .  " " . $periodNo . "限" . ")");
            }

            // スケジュール情報セット
            $scheduleData = [
                'campus_cd' => $campusCd,
                'target_date' => $targetDate,
                'period_no' => $periodNo,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'booth_cd' => $booth,
                'subject_cd' => $subjectCd,
                'course_kind' => AppConst::CODE_MASTER_42_1,
                'course_cd' => $courseCd,
                'tutor_id' => $tutorId,
                'student_id' => $studentId,
                'lesson_kind' => AppConst::CODE_MASTER_31_2,
                'how_to_kind' => $howToKind,
                'tentative_status' => AppConst::CODE_MASTER_36_1,
                'memo' => null,
            ];
            // スケジュール情報格納
            array_push($regDatas, $scheduleData);
        }
        return $regDatas;
    }

    //==========================
    // モーダル
    //==========================

    /**
     * 担当講師リスト取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 担当講師リスト
     */
    public function getDataSelectTutor(Request $request)
    {
        $campusCd = $request->input('campus_cd');
        $subjectCd = $request->input('subject_cd');
        $datePeriodKey = $request->input('date_period_key');

        // [ガード] 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $campusCd = $request->input('campus_cd');
        $this->guardRoomAdminRoomcd($campusCd);

        // [ガード] 科目コードがプルダウンの中にあるかチェック
        $subjectList = $this->mdlGetSubjectList();
        $this->guardListValue($subjectList, $subjectCd);

        // 日付時限キー（日付_時限）を分割
        $datePeriod = $this->fncSasnSplitDatePeriodKey($datePeriodKey);
        $lessonDate = $datePeriod['lesson_date'];
        $periodNo = $datePeriod['period_no'];

        //---------------------------
        // 講師リスト取得
        //---------------------------

        // 除外対象講師の取得（対象日・コマで授業不可連絡）
        // クエリを作成（講師連絡コマ情報）
        $querySeasonTutor = SeasonTutorRequest::select('tutor_id')
            // 講師連絡情報とJOIN
            ->sdJoin(SeasonTutorPeriod::class, function ($join) {
                $join->on('season_tutor_requests.season_tutor_id', 'season_tutor_periods.season_tutor_id');
            })
            // 日付・時限で絞り込み
            ->where('season_tutor_periods.lesson_date', $lessonDate)
            ->where('season_tutor_periods.period_no', $periodNo);

        // 対象日・コマで既に授業登録済みの講師
        // クエリを作成（スケジュール情報）
        $querySchedule = Schedule::select('tutor_id')
            // 日付・時限で絞り込み
            ->where('target_date', $lessonDate)
            ->where('period_no', $periodNo);

        // 2つのqueryをUNIONし、除外対象講師リストを取得
        $exceptTutors = $querySeasonTutor
            ->union($querySchedule)
            ->get()
            ->whereNotNull('tutor_id');

        // 担当講師リストの取得（校舎・科目で絞り込み・上記の講師を除外）
        // クエリを作成（講師情報）
        $query = Tutor::query();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $model = new TutorCampus;
        $query->where($this->guardRoomAdminTableWithRoomCd($model));

        // データを取得
        $tutors = $query
            ->select(
                'tutors.tutor_id as id',
                'tutors.name as value',
                'tutors.name_kana'
            )
            // 講師所属情報とJOIN
            ->sdJoin(TutorCampus::class, function ($join) {
                $join->on('tutors.tutor_id', 'tutor_campuses.tutor_id');
            })
            // 講師担当科目情報とJOIN
            ->sdJoin(TutorSubject::class, function ($join) {
                $join->on('tutors.tutor_id', 'tutor_subjects.tutor_id');
            })
            // 校舎コードで絞り込み
            ->where('tutor_campuses.campus_cd', $campusCd)
            // 科目コードで絞り込み
            ->where('tutor_subjects.subject_cd', $subjectCd)
            // 対象日・コマで授業不可予定の講師を除外
            ->whereNotIn('tutors.tutor_id', $exceptTutors)
            ->orderby('tutors.name_kana')
            ->get();

        return $tutors;
    }
}
