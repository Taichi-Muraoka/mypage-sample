<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\ReportUnit;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\MstCourse;
use App\Models\MstSubject;
use App\Models\MstText;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FuncReportTrait;

/**
 * 授業報告書 - コントローラ
 */
class ReportRegistController extends Controller
{

    // 機能共通処理：授業報告
    use FuncReportTrait;

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
        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // コースリストを取得
        $courses = $this->mdlGetCourseList();

        // 報告書承認リストを取得（サブコード指定で絞り込み）
        $subCodes = [AppConst::CODE_MASTER_4_SUB_1];
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_4, $subCodes);

        return view('pages.tutor.report_regist', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'courses' => $courses,
            'statusList' => $statusList,
            'editData' => null
        ]);
    }

    /**
     * 生徒情報取得（校舎リスト選択時）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 生徒情報
     */
    public function getDataSelectSearch(Request $request)
    {
        // campus_cdを取得
        $campus_cd = $request->input('id');

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // 生徒リスト取得
        if ($campus_cd == -1 || !filled($campus_cd)) {
            // -1 または 空白の場合、自分の受け持ちの生徒だけに絞り込み
            // 生徒リストを取得
            $students = $this->mdlGetStudentListForT(null, $account_id);
        } else {
            $students = $this->mdlGetStudentListForT($campus_cd, $account_id);
        }

        return [
            'selectItems' => $this->objToArray($students),
        ];
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

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // クエリを作成
        $query = Report::query();

        // 校舎コード選択による絞り込み条件
        // -1 は未選択状態のため、-1以外の場合に校舎コードの絞り込みを行う
        if (isset($form['campus_cd']) && filled($form['campus_cd']) && $form['campus_cd'] != -1) {
            // 検索フォームから取得（スコープ）
            $query->SearchCampusCd($form);
        }

        // コースコード選択により絞り込み条件
        if (isset($form['course_cd']) && filled($form['course_cd'])) {
            // 検索フォームから取得（スコープ）
            $query->SearchCourseCd($form);
        }

        // 承認ステータス選択により絞り込み条件
        if (isset($form['approval_status']) && filled($form['approval_status'])) {
            // 検索フォームから取得（スコープ）
            $query->SearchApprovalStatus($form);
        }

        // 生徒IDの検索（スコープで指定する）
        $query->SearchSid($form);

        // 校舎名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // 受け持ち生徒リスト（配列）取得
        $myStudents = $this->mdlGetStudentArrayForT();

        // データを取得
        $reports = $query
            ->select(
                'reports.report_id as id',
                'reports.lesson_date',
                'reports.period_no',
                'reports.tutor_id',
                'reports.approval_status',
                'room_names.room_name as room_name',
                'mst_courses.name as course_name',
                'mst_codes.name as status_name',
                'tutors.name as tutor_name',
                'students.name as student_name'
            )
            // スケジュール情報のJOIN
            ->sdJoin(Schedule::class, 'reports.schedule_id', '=', 'schedules.schedule_id')
            // 受講生徒情報のJOIN
            ->sdLeftJoin(ClassMember::class, 'schedules.schedule_id', '=', 'class_members.schedule_id')
            // 校舎名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'room_names.code');
            })
            // 講師名の取得
            ->sdLeftJoin(Tutor::class, 'reports.tutor_id', '=', 'tutors.tutor_id')
            // 生徒名の取得
            ->sdLeftJoin(Student::class, 'reports.student_id', '=', 'students.student_id')
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, 'reports.course_cd', '=', 'mst_courses.course_cd')
            // 報告書承認ステータス名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('reports.approval_status', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_4);
            })
            // ガード）担当生徒で絞り込み
            // 以下の条件はクロージャで記述(orを含むため)
            ->where(function ($query) use ($myStudents) {
                // スケジュール情報から絞り込み（１対１授業）
                $query->whereIn('schedules.student_id', $myStudents)
                    // または受講生徒情報から絞り込み（１対多授業）
                    ->orWhereIn('class_members.student_id', $myStudents);
            })
            // 自分の報告書かどうかで取得条件切り分け
            // 以下の条件はクロージャで記述(orを含むため)
            ->where(function ($query) use ($account_id) {
                // 自分の報告書は承認ステータス全て取得
                $query->where('reports.tutor_id', $account_id)
                    // 自分以外の報告書は承認済みのもののみ
                    ->OrWhere(function ($query) use ($account_id) {
                        $query->where('reports.tutor_id', '<>', $account_id)
                            ->where('reports.approval_status', AppConst::CODE_MASTER_4_2);
                    });
            })
            ->distinct()
            ->orderby('lesson_date', 'desc')
            ->orderby('period_no', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $reports);
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

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 初期表示の時はエラーを発生させないようにする
            if ($value == -1) return;

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒名
        $validationStudentsList =  function ($attribute, $value, $fail) {

            // ログイン者の情報を取得する
            $account = Auth::user();
            $account_id = $account->account_id;
            $students = $this->mdlGetStudentListForT(null, $account_id);
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        $rules += ['campus_cd' => [$validationRoomList]];
        $rules += Report::fieldRules('student_id', [$validationStudentsList]);

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
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $id = $request->input('id');

        // データを取得
        $report = $this->getReport($id);

        // 集団授業の場合受講生徒名取得
        if ($report->course_kind == AppConst::CODE_MASTER_42_2) {
            $class_member_names = $this->getClassMember($report->schedule_id);
        } else {
            $class_member_names = [];
        }

        foreach (AppConst::REPORT_SUBCODES as $subCode) {
            if (ReportUnit::where('report_units.sub_cd', '=', $subCode)->exists()) {
                // 可変変数名をセット
                $lesson_text = 'lesson_text' . $subCode;

                // 教材名
                $$lesson_text = $this->getReportText($report->report_id, $subCode);

                // 単元分類・単元
                for ($i = 1; $i <= 3; $i++) {
                    // 可変変数名をセット
                    $lesson_category = 'lesson_category' . $subCode . '_' . $i;
                    $$lesson_category = $this->getReportCategory($report->report_id, $subCode, $i);
                }
            }
            // 存在しない場合nullにする
            else {
                $lesson_text = 'lesson_text' . $subCode;

                // 教材名
                $$lesson_text = null;
                // 単元分類・単元
                for ($i = 1; $i <= 3; $i++) {
                    $lesson_category = 'lesson_category' . $subCode . '_' . $i;
                    $$lesson_category = null;
                }
            }
        }

        return [
            'campus_name' => $report->campus_name,
            'regist_date' => $report->regist_date,
            'lesson_date' => $report->lesson_date,
            'period_no' => $report->period_no,
            'course_name' => $report->course_name,
            'course_kind' => $report->course_kind,
            'student_name' => $report->student_name,
            'class_member_name' => $class_member_names,
            'subject_name' => $report->subject_name,
            'monthly_goal' => $report->monthly_goal,
            'test_contents' => $report->test_contents,
            'test_score' => $report->test_score,
            'test_full_score' => $report->test_full_score,
            'achievement' => intval($report->achievement),
            'goodbad_point' => $report->goodbad_point,
            'solution' => $report->solution,
            'others_comment' => $report->others_comment,
            'status' => $report->status,
            'admin_comment' => $report->admin_comment,
            // 授業教材１
            'lesson_text1' => $lesson_textL1,
            'lesson_category1_1' => $lesson_categoryL1_1,
            'lesson_category1_2' => $lesson_categoryL1_2,
            'lesson_category1_3' => $lesson_categoryL1_3,
            // 授業教材２
            'lesson_text2' => $lesson_textL2,
            'lesson_category2_1' => $lesson_categoryL2_1,
            'lesson_category2_2' => $lesson_categoryL2_2,
            'lesson_category2_3' => $lesson_categoryL2_3,
            // 宿題教材１
            'homework_text1' => $lesson_textH1,
            'homework_category1_1' => $lesson_categoryH1_1,
            'homework_category1_2' => $lesson_categoryH1_2,
            'homework_category1_3' => $lesson_categoryH1_3,
            // 宿題教材２
            'homework_text2' => $lesson_textH2,
            'homework_category2_1' => $lesson_categoryH2_1,
            'homework_category2_2' => $lesson_categoryH2_2,
            'homework_category2_3' => $lesson_categoryH2_3,
        ];
    }

    //==========================
    // 登録・編集・削除
    //==========================

    /**
     * 授業情報取得（スケジュールより）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 校舎、生徒、コース、科目情報
     */
    public function getDataSelect(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $schedule_id = $request->input('id');

        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        $query = Schedule::query();

        $lesson = $query
            // キーの指定
            ->where('schedules.schedule_id', '=', $schedule_id)
            ->select(
                'schedules.schedule_id',
                'schedules.campus_cd',
                // 校舎名
                'campus_names.room_name as campus_name',
                'schedules.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'schedules.course_cd',
                // コース名
                'mst_courses.name as course_name',
                // コース種別
                'mst_courses.course_kind as course_kind',
                'schedules.subject_cd',
                // 科目名
                'mst_subjects.name as subject_name',
                'schedules.regular_class_id',
                'schedules.report_id'
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'schedules.student_id', '=', 'students.student_id')
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd');
            })
            // 科目名の取得
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('mst_subjects.subject_cd', '=', 'schedules.subject_cd');
            })
            ->firstOrFail();

        // レギュラー授業かどうか
        $exists = Schedule::query()
            ->where('schedules.regular_class_id', '=', $lesson->regular_class_id)
            // 授業報告書IDがあるもの
            ->whereNotNull('report_id')
            ->exists();

        // 前回授業データ
        $last_data = [];

        // レギュラー授業だった場合前回の授業報告書内容を取得
        // ※レギュラ授業かつ、前回の授業報告書が存在するかつ、授業報告書IDが存在しないもの
        // ※授業報告書IDが存在しないものを条件に入れないと編集データに前回報告書がきてしまう
        if ($lesson->regular_class_id != null and $exists and $lesson->report_id == null) {
            // 前回授業を取得
            $last_lesson = Schedule::query()
                ->where('schedules.regular_class_id', '=', $lesson->regular_class_id)
                // 授業報告書IDがあるもの
                ->whereNotNull('report_id')
                // 最新の授業日
                ->latest('target_date')
                ->first();

            // データを取得
            $report = $this->getReport($last_lesson->report_id);

            $last_data += [
                // 前回のレギュラー授業がある場合のフラグ
                'flag' => 1,
                'lesson_report_id' => $lesson->report_id,
                'schedule_id' => $last_lesson->schedule_id,
                'report_id' => $report->report_id,
                'regular_class_id' => $lesson->regular_class_id,
                'last_regular_class_id' => $last_lesson->regular_class_id,
                'monthly_goal' => $report->monthly_goal,
            ];
            // 教材単元情報を取得（サブコード毎に取得する）
            foreach (AppConst::REPORT_SUBCODES as $subCode) {
                if ($subCode == AppConst::REPORT_SUBCODE_3) {
                    break;
                }
                $unit_exists = ReportUnit::where('report_units.report_id', '=', $report->report_id)
                    ->where('report_units.sub_cd', '=', $subCode)
                    ->exists();
                if ($unit_exists) {
                    // データがある場合のみlast_dataにセット
                    $report_unit = $this->getReportUnit($report->report_id, $subCode);
                    $last_data += [
                        'text_cd_' . $subCode => $report_unit->text_cd,
                        'text_name_' . $subCode => $report_unit->free_text_name,
                        'text_page_' . $subCode => $report_unit->text_page1,
                        'unit_category_cd1_' . $subCode => $report_unit->unit_category_cd1,
                        'unit_category_cd2_' . $subCode => $report_unit->unit_category_cd2,
                        'unit_category_cd3_' . $subCode => $report_unit->unit_category_cd3,
                        'unit_cd1_' . $subCode => $report_unit->unit_cd1,
                        'unit_cd2_' . $subCode => $report_unit->unit_cd2,
                        'unit_cd3_' . $subCode => $report_unit->unit_cd3,
                        'category_name1_' . $subCode => $report_unit->free_category_name1,
                        'category_name2_' . $subCode => $report_unit->free_category_name2,
                        'category_name3_' . $subCode => $report_unit->free_category_name3,
                        'unit_name1_' . $subCode => $report_unit->free_unit_name1,
                        'unit_name2_' . $subCode => $report_unit->free_unit_name2,
                        'unit_name3_' . $subCode => $report_unit->free_unit_name3,
                    ];
                }
            }
        }
        else if ($exists != true) {
            // レギュラー授業じゃない場合
            $last_data += ['flag' => 2];
        }
        else {
            // 編集データ
            $last_data += ['flag' => 0];
        }

        // 教材リストを取得（授業科目コード指定）
        $texts = $this->mdlGetTextList($lesson->subject_cd, null, null);

        // 集団授業の場合受講生徒名取得
        if ($lesson->course_kind == AppConst::CODE_MASTER_42_2) {
            $class_member_names = $this->getClassMember($lesson->schedule_id);
        } else {
            $class_member_names = [];
        }

        return [
            'last_data' => $last_data,
            'regular_class_id' => $lesson->regular_class_id,
            'campus_name' => $lesson->campus_name,
            'course_name' => $lesson->course_name,
            'course_kind' => $lesson->course_kind,
            'student_name' => $lesson->student_name,
            'class_member_name' => $class_member_names,
            'subject_name' => $lesson->subject_name,
            'selectItems' => $this->objToArray($texts)
        ];
    }

    /**
     * 単元分類情報取得（教材リスト選択時）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 単元分類リスト
     */
    public function getDataSelectText(Request $request)
    {
        // 教材コードを取得
        $textCd = $request->input('text_cd');

        $query = MstText::query();
        $text = $query
            ->select(
                'grade_cd',
                't_subject_cd'
            )
            ->where('text_cd', '=', $textCd)
            ->firstOrFail();

        // 単元分類リストを取得
        $categores = $this->mdlGetUnitCategoryList($text->grade_cd, $text->t_subject_cd);

        return [
            'selectItems' => $this->objToArray($categores)
        ];
    }

    /**
     * 単元情報取得（単元分類リスト選択時）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 単元リスト
     */
    public function getDataSelectCategory(Request $request)
    {
        // 単元分類コードを取得
        $categoryCd = $request->input('unit_category_cd');

        // 単元リストを取得
        $units = $this->mdlGetUnitList($categoryCd);

        return [
            'selectItems' => $this->objToArray($units)
        ];
    }

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        $query = Schedule::query();

        // 授業情報を取得
        $lessons = $query
            // 自分のアカウントIDでガードを掛ける（tid）
            ->where($this->guardTutorTableWithTid())
            // キーの指定
            ->where('schedules.tutor_id', '=', $account_id)
            ->where(function ($orQuery) {
                // 出欠・振替コードが0 実施前・出席のもののみ
                $orQuery->where('schedules.absent_status', [AppConst::CODE_MASTER_35_0]);
            })
            ->where(function ($orQuery) {
                // 授業報告書IDがNULL
                $orQuery->orWhereNull('schedules.report_id');
            })
            ->where('schedules.target_date', '<=', now())
            ->orderBy('target_date', 'asc')->orderBy('period_no', 'asc')
            ->get();

        $lesson_list = $this->mdlGetScheduleMasterList($lessons);

        // テンプレートは編集と同じ
        return view('pages.tutor.report_regist-input', [
            'editData' => null,
            'rules' => $this->rulesForInput(null),
            'lesson_list' => $lesson_list,
        ]);
    }

    /**
     * 登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function create(Request $request)
    {
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            // クエリ作成
            $query = Schedule::query();

            // 授業報告書情報登録
            $lesson = $query
                ->where('schedule_id', '=', $request['id'])
                ->firstOrFail();

            $report = new Report;

            $form = $request->only(
                'monthly_goal',
                'test_contents',
                'test_score',
                'test_full_score',
                'achievement',
                'goodbad_point',
                'solution',
                'others_comment',
            );

            $report->tutor_id = $lesson->tutor_id;
            $report->schedule_id = $lesson->schedule_id;
            $report->campus_cd = $lesson->campus_cd;
            $report->course_cd = $lesson->course_cd;
            $report->lesson_date = $lesson->target_date;
            $report->period_no = $lesson->period_no;
            $report->student_id = $lesson->student_id;
            $report->approval_status = AppConst::CODE_MASTER_2_0;
            $report->regist_date = now();
            $report->fill($form)->save();

            // スケジュール情報更新
            $lesson->report_id = $report->report_id;
            $lesson->save();

            // 授業報告書教材単元情報登録
            foreach (AppConst::REPORT_SUBCODES as $subCode) {
                if ($request['text_cd_' . $subCode] != null) {
                    $report_unit = new ReportUnit;
                    $report_unit->report_id = $report->report_id;
                    $report_unit->sub_cd = $subCode;
                    $report_unit->text_cd = $request['text_cd_' . $subCode];
                    $report_unit->free_text_name = $request['text_name_' . $subCode];
                    $report_unit->text_page = $request['text_page_' . $subCode];
                    $report_unit->unit_category_cd1 = $request['unit_category_cd1_' . $subCode];
                    $report_unit->free_category_name1 = $request['category_name1_' . $subCode];
                    $report_unit->unit_cd1 = $request['unit_cd1_' . $subCode];
                    $report_unit->free_unit_name1 = $request['unit_name1_' . $subCode];
                    $report_unit->unit_category_cd2 = $request['unit_category_cd2_' . $subCode];
                    $report_unit->free_category_name2 = $request['category_name2_' . $subCode];
                    $report_unit->unit_cd2 = $request['unit_cd2_' . $subCode];
                    $report_unit->free_unit_name2 = $request['unit_name2_' . $subCode];
                    $report_unit->unit_category_cd3 = $request['unit_category_cd3_' . $subCode];
                    $report_unit->free_category_name3 = $request['category_name3_' . $subCode];
                    $report_unit->unit_cd3 = $request['unit_cd3_' . $subCode];
                    $report_unit->free_unit_name3 = $request['unit_name3_' . $subCode];
                    // フリー入力の項目チェック
                    if (substr($request['text_cd_' . $subCode], -2) != AppConst::REPORT_OTHER_TEXT_UNIT_CODE) {
                        $report_unit->free_text_name = null;
                    }
                    if (substr($request['unit_category_cd1_' . $subCode], -2) != AppConst::REPORT_OTHER_TEXT_UNIT_CODE) {
                        $report_unit->free_category_name1 = null;
                    }
                    if (substr($request['unit_category_cd2_' . $subCode], -2) != AppConst::REPORT_OTHER_TEXT_UNIT_CODE) {
                        $report_unit->free_category_name2 = null;
                    }
                    if (substr($request['unit_category_cd3_' . $subCode], -2) != AppConst::REPORT_OTHER_TEXT_UNIT_CODE) {
                        $report_unit->free_category_name3 = null;
                    }
                    if (substr($request['unit_cd1_' . $subCode], -2) != AppConst::REPORT_OTHER_TEXT_UNIT_CODE) {
                        $report_unit->free_unit_name1 = null;
                    }
                    if (substr($request['unit_cd2_' . $subCode], -2) != AppConst::REPORT_OTHER_TEXT_UNIT_CODE) {
                        $report_unit->free_unit_name2 = null;
                    }
                    if (substr($request['unit_cd3_' . $subCode], -2) != AppConst::REPORT_OTHER_TEXT_UNIT_CODE) {
                        $report_unit->free_unit_name3 = null;
                    }
                    $report_unit->save();
                }
            }
        });

        return;
    }

    /**
     * 編集画面
     *
     * @param int $reportId 授業報告書ID
     * @return view
     */
    public function edit($id)
    {
        // IDのバリデーション
        $this->validateIds($id);

        // データを取得
        $report = $this->getReport($id);

        // 集団授業の場合受講生徒名取得
        if ($report->course_kind == AppConst::CODE_MASTER_42_2) {
            $class_member_names = $this->getClassMember($report->schedule_id);
        } else {
            $class_member_names = [];
        }

        $editdata = [
            'id' => $report->schedule_id,
            'report_id' => $report->report_id,
            'monthly_goal' => $report->monthly_goal,
            'test_contents' => $report->test_contents,
            'test_score' => $report->test_score,
            'test_full_score' => $report->test_full_score,
            'achievement' => intval($report->achievement),
            'goodbad_point' => $report->goodbad_point,
            'solution' => $report->solution,
            'others_comment' => $report->others_comment
        ];

        // 教材単元情報を取得（サブコード毎に取得する）
        foreach (AppConst::REPORT_SUBCODES as $subCode) {
            $exists = ReportUnit::where('report_units.report_id', '=', $report->report_id)
                ->where('report_units.sub_cd', '=', $subCode)
                ->exists();
            if ($exists) {
                // データがある場合のみeditdataにセット
                $report_unit = $this->getReportUnit($report->report_id, $subCode);
                $editdata += [
                    'bef_text_cd_' . $subCode => $report_unit->text_cd,
                    'text_name_' . $subCode => $report_unit->free_text_name,
                    'text_page_' . $subCode => $report_unit->text_page1,
                    'bef_unit_category_cd1_' . $subCode => $report_unit->unit_category_cd1,
                    'bef_unit_category_cd2_' . $subCode => $report_unit->unit_category_cd2,
                    'bef_unit_category_cd3_' . $subCode => $report_unit->unit_category_cd3,
                    'bef_unit_cd1_' . $subCode => $report_unit->unit_cd1,
                    'bef_unit_cd2_' . $subCode => $report_unit->unit_cd2,
                    'bef_unit_cd3_' . $subCode => $report_unit->unit_cd3,
                    'category_name1_' . $subCode => $report_unit->free_category_name1,
                    'category_name2_' . $subCode => $report_unit->free_category_name2,
                    'category_name3_' . $subCode => $report_unit->free_category_name3,
                    'unit_name1_' . $subCode => $report_unit->free_unit_name1,
                    'unit_name2_' . $subCode => $report_unit->free_unit_name2,
                    'unit_name3_' . $subCode => $report_unit->free_unit_name3,
                ];
            }
        }

        return view('pages.tutor.report_regist-input', [
            'editData' => $editdata,
            'rules' => $this->rulesForInput(null),
            'campus_name' => $report->campus_name,
            'regist_date' => $report->regist_date,
            'lesson_date' => $report->lesson_date,
            'period_no' => $report->period_no,
            'course_name' => $report->course_name,
            'course_kind' => $report->course_kind,
            'student_name' => $report->student_name,
            'class_member_names' => $class_member_names,
            'subject_name' => $report->subject_name,
            'status' => $report->status,
            'admin_comment' => $report->admin_comment,
        ]);
    }

    /**
     * 編集処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function update(Request $request)
    {
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {
            // 授業報告書情報取得
            $report = Report::query()
                ->where('report_id', $request->input('report_id'))
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            $form = $request->only(
                'monthly_goal',
                'test_contents',
                'test_score',
                'test_full_score',
                'achievement',
                'goodbad_point',
                'solution',
                'others_comment',
            );

            $report->regist_date = now();
            // 承認ステータスが差戻の場合、承認待ちに変更
            if ($report->approval_status == AppConst::CODE_MASTER_2_2) {
                $report->approval_status = AppConst::CODE_MASTER_2_0;
            }
            // 授業報告書情報更新
            $report->fill($form)->save();

            // 授業報告書教材単元情報
            foreach (AppConst::REPORT_SUBCODES as $subCode) {
                // 授業報告書単元情報が存在するか
                $exists = ReportUnit::where('report_units.report_id', '=', $report->report_id)
                    ->where('report_units.sub_cd', '=', $subCode)
                    ->exists();
                // 存在する場合削除
                if ($exists) {
                    $report_unit = ReportUnit::query()
                        ->where('report_units.report_id', '=', $report->report_id)
                        ->where('report_units.sub_cd', '=', $subCode)
                        ->firstOrFail();
                    // 削除
                    $report_unit->forceDelete();
                }
                if ($request['text_cd_' . $subCode] != null) {
                    $report_unit = new ReportUnit;
                    $report_unit->report_id = $report->report_id;
                    $report_unit->sub_cd = $subCode;
                    $report_unit->text_cd = $request['text_cd_' . $subCode];
                    $report_unit->free_text_name = $request['text_name_' . $subCode];
                    $report_unit->text_page = $request['text_page_' . $subCode];
                    $report_unit->unit_category_cd1 = $request['unit_category_cd1_' . $subCode];
                    $report_unit->free_category_name1 = $request['category_name1_' . $subCode];
                    $report_unit->unit_cd1 = $request['unit_cd1_' . $subCode];
                    $report_unit->free_unit_name1 = $request['unit_name1_' . $subCode];
                    $report_unit->unit_category_cd2 = $request['unit_category_cd2_' . $subCode];
                    $report_unit->free_category_name2 = $request['category_name2_' . $subCode];
                    $report_unit->unit_cd2 = $request['unit_cd2_' . $subCode];
                    $report_unit->free_unit_name2 = $request['unit_name2_' . $subCode];
                    $report_unit->unit_category_cd3 = $request['unit_category_cd3_' . $subCode];
                    $report_unit->free_category_name3 = $request['category_name3_' . $subCode];
                    $report_unit->unit_cd3 = $request['unit_cd3_' . $subCode];
                    $report_unit->free_unit_name3 = $request['unit_name3_' . $subCode];
                    // フリー入力の項目チェック
                    if (substr($request['text_cd_' . $subCode], -2) != 99) {
                        $report_unit->free_text_name = null;
                    }
                    if (substr($request['unit_category_cd1_' . $subCode], -2) != 99) {
                        $report_unit->free_category_name1 = null;
                    }
                    if (substr($request['unit_category_cd2_' . $subCode], -2) != 99) {
                        $report_unit->free_category_name2 = null;
                    }
                    if (substr($request['unit_category_cd3_' . $subCode], -2) != 99) {
                        $report_unit->free_category_name3 = null;
                    }
                    if (substr($request['unit_cd1_' . $subCode], -2) != 99) {
                        $report_unit->free_unit_name1 = null;
                    }
                    if (substr($request['unit_cd2_' . $subCode], -2) != 99) {
                        $report_unit->free_unit_name2 = null;
                    }
                    if (substr($request['unit_cd3_' . $subCode], -2) != 99) {
                        $report_unit->free_unit_name3 = null;
                    }
                    $report_unit->save();
                }
            }
        });

        return;
    }

    /**
     * 削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function delete(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'report_id');

        // 対象データを取得(PKでユニークに取る)
        $report = Report::query()
            ->where('report_id', $request->input('report_id'))
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // Reportテーブルのdelete
        $report->delete();

        // 授業教材情報を削除
        foreach (AppConst::REPORT_SUBCODES as $subCode) {
            if (ReportUnit::where('report_units.sub_cd', '=', $subCode)
                ->where('report_units.report_id', '=', $report->report_id)
                ->exists()
            ) {
                $lesson_unit = ReportUnit::query()
                    ->where('report_units.report_id', '=', $report->report_id)
                    ->where('report_units.sub_cd', '=', $subCode)
                    ->firstOrFail();
                // ReportUnitテーブルのdelete
                $lesson_unit->delete();
            }
        }

        return;
    }

    /**
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInput(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInput($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 教材
        $text_rule =  function ($attribute, $value, $fail) use ($request) {
            // 授業報告書情報登録
            $lesson = Schedule::query()
                ->where('schedule_id', '=', $request['id'])
                ->firstOrFail();

            // リストを取得し存在チェック
            // 教材リストを取得（授業科目コード指定）
            $texts = $this->mdlGetTextList($lesson->subject_cd, null, null);
            if (!isset($texts[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 単元分類
        $category_rule =  function ($attribute, $value, $fail) use ($request) {
            // リストを取得し存在チェック
            // 単元分類リストを取得
            $categores = $this->mdlGetUnitCategoryList(null, null);
            if (!isset($categores[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 単元
        $unit_rule =  function ($attribute, $value, $fail) use ($request) {
            // リストを取得し存在チェック
            // 単元リストを取得
            $units = $this->mdlGetUnitList(null);
            if (!isset($units[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += ['id' => ['required']];
        $rules += Report::fieldRules('monthly_goal');
        $rules += Report::fieldRules('test_contents');
        $rules += Report::fieldRules('test_score');
        $rules += Report::fieldRules('test_full_score');
        $rules += Report::fieldRules('achievement');
        $rules += Report::fieldRules('goodbad_point');
        $rules += Report::fieldRules('solution');
        $rules += Report::fieldRules('others_comment');

        // 授業内容・宿題のバリデーション
        foreach (AppConst::REPORT_SUBCODES as $subCode) {
            // ID名を可変変数をセット
            $text_cd = 'text_cd_' . $subCode;

            // 教材１のみ必須
            if ($subCode ==  AppConst::REPORT_SUBCODE_1) {
                $rules += [$text_cd => ['required']];
            }

            // required_withの可変変数をセット
            $required_with_text = 'required_with:' . $text_cd;

            // ページのバリデーション
            $rules += ['text_page_' . $subCode => ['string', 'max:50']];

            // 教材フリー入力のバリデーション
            $rules += ['text_name_' . $subCode => ['string', 'max:50']];

            for ($i = 1; $i <= 3; $i++) {
                // ID名を可変変数をセット
                $category_cd = 'unit_category_cd' . $i . '_' . $subCode;
                $unit_cd = 'unit_cd' . $i . '_' . $subCode;

                // required_withの可変変数をセット
                $required_with_category = 'required_with:' . $category_cd;

                $rules += [$text_cd => [$required_with_text, $required_with_category, $text_rule]];
                $rules += [$category_cd => [$required_with_category, $category_rule]];
                $rules += [$unit_cd => [$unit_rule]];
                $rules += ['category_name' . $i . '_' . $subCode => ['string', 'max:50']];
                $rules += ['unit_name' . $i . '_' . $subCode => ['string', 'max:50']];
            }
        }

        return $rules;
    }
}
