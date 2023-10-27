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
use App\Models\MstUnitCategory;
use App\Models\MstUnit;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FuncReportTrait;
use Carbon\Carbon;

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
     * 生徒情報取得（教室リスト選択時）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 生徒情報
     */
    public function getDataSelectSearch(Request $request)
    {
        // $requestからidを取得し、検索結果を返却する
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // campus_cdを取得
        $campus_cd = $request->input('id');

        // $requestのcampus_cdから、生徒IDリストを取得し、検索結果を返却する。
        // 生徒リスト取得
        if ($campus_cd == -1 || !filled($campus_cd)) {
            // -1 または 空白の場合、自分の受け持ちの生徒だけに絞り込み
            $students = $this->mdlGetStudentListForT(null, $account_id);
        } else {
            $students = $this->mdlGetStudentListForT($campus_cd, $account_id);
        }

        return [
            'selectItems' => $this->objToArray($students)
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

        // 生徒IDの検索（スコープで指定する）
        $query->SearchSid($form);

        // 教室名取得のサブクエリ
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
            ->where(function ($query) use ($myStudents){
                // スケジュール情報から絞り込み（１対１授業）
                $query->whereIn('schedules.student_id', $myStudents)
                // または受講生徒情報から絞り込み（１対多授業）
                    ->orWhereIn('class_members.student_id', $myStudents);
            })
            // 自分の報告書かどうかで取得条件切り分け
            // 以下の条件はクロージャで記述(orを含むため)
            ->where(function ($query) use ($account_id){
                // 自分の報告書は承認ステータス全て取得
                $query->where('reports.tutor_id', $account_id)
                    // 自分以外の報告書は承認済みのもののみ
                    ->OrWhere(function ($query) use ($account_id){
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

        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 初期表示の時はエラーを発生させないようにする
            if ($value == -1) return;

            // 教室リストを取得
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
        // ==========================
        // 本番用処理
        // ==========================
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $id = $request->input('id');

        // データを取得
        $report = $this->getReport($id);
        
        // 集団授業の場合受講生徒名取得
        if ($report->course_kind == AppConst::CODE_MASTER_42_2) {
            $class_member_names = $this->getClassMember($report->schedule_id);
        }
        else {
            $class_member_names = [];
        }

        // 授業教材１の情報取得
        $lesson_text1 = $this->getReportText($report->report_id, AppConst::REPORT_SUBCODE_1);
        $lesson_category1_1 = $this->getReportCategory($report->report_id, AppConst::REPORT_SUBCODE_1, 1);
        $lesson_category1_2 = $this->getReportCategory($report->report_id, AppConst::REPORT_SUBCODE_1, 2);
        $lesson_category1_3 = $this->getReportCategory($report->report_id, AppConst::REPORT_SUBCODE_1, 3);

        // 授業教材２があれば取得
        if (ReportUnit::where('report_units.sub_cd', '=', AppConst::REPORT_SUBCODE_2)->exists()) {
            $lesson_text2 = $this->getReportText($report->report_id, AppConst::REPORT_SUBCODE_2);
            $lesson_category2_1 = $this->getReportCategory($report->report_id, AppConst::REPORT_SUBCODE_2, 1);
            $lesson_category2_2 = $this->getReportCategory($report->report_id, AppConst::REPORT_SUBCODE_2, 2);
            $lesson_category2_3 = $this->getReportCategory($report->report_id, AppConst::REPORT_SUBCODE_2, 3);
        }
        else {
            $lesson_text2 = null;
            $lesson_category2_1 = null;
            $lesson_category2_2 = null;
            $lesson_category2_3 = null;
        }
        
        // 宿題教材１があれば取得
        if (ReportUnit::where('report_units.sub_cd', '=', AppConst::REPORT_SUBCODE_3)->exists()) {
            $homework_text1 = $this->getReportText($report->report_id, AppConst::REPORT_SUBCODE_3);
            $homework_category1_1 = $this->getReportCategory($report->report_id, AppConst::REPORT_SUBCODE_3, 1);
            $homework_category1_2 = $this->getReportCategory($report->report_id, AppConst::REPORT_SUBCODE_3, 2);
            $homework_category1_3 = $this->getReportCategory($report->report_id, AppConst::REPORT_SUBCODE_3, 3);
        }
        else {
            $homework_text1 = null;
            $homework_category1_1 = null;
            $homework_category1_2 = null;
            $homework_category1_3 = null;
        }
        // 宿題教材２があれば取得
        if (ReportUnit::where('report_units.sub_cd', '=', AppConst::REPORT_SUBCODE_4)->exists()) {
            $homework_text2 = $this->getReportText($report->report_id, AppConst::REPORT_SUBCODE_3);
            $homework_category2_1 = $this->getReportCategory($report->report_id, AppConst::REPORT_SUBCODE_4, 1);
            $homework_category2_2 = $this->getReportCategory($report->report_id, AppConst::REPORT_SUBCODE_4, 2);
            $homework_category2_3 = $this->getReportCategory($report->report_id, AppConst::REPORT_SUBCODE_4, 3);
        }
        else {
            $homework_text2 = null;
            $homework_category2_1 = null;
            $homework_category2_2 = null;
            $homework_category2_3 = null;
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
            'lesson_text1' => $lesson_text1,
            'lesson_category1_1' => $lesson_category1_1,
            'lesson_category1_2' => $lesson_category1_2,
            'lesson_category1_3' => $lesson_category1_3,
            // 授業教材２
            'lesson_text2' => $lesson_text2,
            'lesson_category2_1' => $lesson_category2_1,
            'lesson_category2_2' => $lesson_category2_2,
            'lesson_category2_3' => $lesson_category2_3,
            // 宿題教材１
            'homework_text1' => $homework_text1,
            'homework_category1_1' => $homework_category1_1,
            'homework_category1_2' => $homework_category1_2,
            'homework_category1_3' => $homework_category1_3,
            // 宿題教材２
            'homework_text2' => $homework_text2,
            'homework_category2_1' => $homework_category2_1,
            'homework_category2_2' => $homework_category2_2,
            'homework_category2_3' => $homework_category2_3,
        ];
    }

    //==========================
    // 登録・編集・削除
    //==========================

    /**
     * 授業情報取得（スケジュールより）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 教室、生徒、コース、科目情報
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
        
        // 教材リストを取得（授業科目コード指定）
        $texts = $this->mdlGetTextList($lesson->subject_cd, null, null);

        // 集団授業の場合受講生徒名取得
        if ($lesson->course_kind == AppConst::CODE_MASTER_42_2) {
            $class_member_names = $this->getClassMember($lesson->schedule_id);
        }
        else {
            $class_member_names = [];
        }

        return [
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
            // 授業教材1
            $report_unit1 = new ReportUnit;
            $report_unit1->report_id = $report->report_id;
            $report_unit1->sub_cd = AppConst::REPORT_SUBCODE_1;
            $report_unit1->text_cd = $request['lesson_text1'];
            $report_unit1->free_text_name = $request['lesson_text_name1'];
            $report_unit1->text_page = $request['lesson_page1'];
            $report_unit1->unit_category_cd1 = $request['lesson_category1_1'];
            $report_unit1->free_category_name1 = $request['lesson_category_name1_1'];
            $report_unit1->unit_cd1 = $request['lesson_unit1_1'];
            $report_unit1->free_unit_name1 = $request['lesson_unit_name1_1'];
            $report_unit1->unit_category_cd2 = $request['lesson_category1_2'];
            $report_unit1->free_category_name2 = $request['lesson_category_name1_2'];
            $report_unit1->unit_cd2 = $request['lesson_unit1_2'];
            $report_unit1->free_unit_name2 = $request['lesson_unit_name1_2'];
            $report_unit1->unit_category_cd3 = $request['lesson_category1_3'];
            $report_unit1->free_category_name3 = $request['lesson_category_name1_3'];
            $report_unit1->unit_cd3 = $request['lesson_unit1_3'];
            $report_unit1->free_unit_name3 = $request['lesson_unit_name1_3'];
            $report_unit1->save();

            // 授業教材2
            if ($request['lesson_text2'] != null) {
                $report_unit2 = new ReportUnit;
                $report_unit2->report_id = $report->report_id;
                $report_unit2->sub_cd = AppConst::REPORT_SUBCODE_2;
                $report_unit2->text_cd = $request['lesson_text2'];
                $report_unit2->free_text_name = $request['lesson_text_name2'];
                $report_unit2->text_page = $request['lesson_page2'];
                $report_unit2->unit_category_cd1 = $request['lesson_category2_1'];
                $report_unit2->free_category_name1 = $request['lesson_category_name2_1'];
                $report_unit2->unit_cd1 = $request['lesson_unit2_1'];
                $report_unit2->free_unit_name1 = $request['lesson_unit_name2_1'];
                $report_unit2->unit_category_cd2 = $request['lesson_category2_2'];
                $report_unit2->free_category_name2 = $request['lesson_category_name2_2'];
                $report_unit2->unit_cd2 = $request['lesson_unit2_2'];
                $report_unit2->free_unit_name2 = $request['lesson_unit_name2_2'];
                $report_unit2->unit_category_cd3 = $request['lesson_category2_3'];
                $report_unit2->free_category_name3 = $request['lesson_category_name2_3'];
                $report_unit2->unit_cd3 = $request['lesson_unit2_3'];
                $report_unit2->free_unit_name3 = $request['lesson_unit_name2_3'];
                $report_unit2->save();
            }

            // 宿題教材1
            if ($request['homework_text1'] != null) {
                $report_unit3 = new ReportUnit;
                $report_unit3->report_id = $report->report_id;
                $report_unit3->sub_cd = AppConst::REPORT_SUBCODE_3;
                $report_unit3->text_cd = $request['homework_text1'];
                $report_unit3->free_text_name = $request['homework_text_name1'];
                $report_unit3->text_page = $request['homework_page1'];
                $report_unit3->unit_category_cd1 = $request['homework_category1_1'];
                $report_unit3->free_category_name1 = $request['homework_category_name1_1'];
                $report_unit3->unit_cd1 = $request['homework_unit1_1'];
                $report_unit3->free_unit_name1 = $request['homework_unit_name1_1'];
                $report_unit3->unit_category_cd2 = $request['homework_category1_2'];
                $report_unit3->free_category_name2 = $request['homework_category_name1_2'];
                $report_unit3->unit_cd2 = $request['homework_unit1_2'];
                $report_unit3->free_unit_name2 = $request['homework_unit_name1_2'];
                $report_unit3->unit_category_cd3 = $request['homework_category1_3'];
                $report_unit3->free_category_name3 = $request['homework_category_name1_3'];
                $report_unit3->unit_cd3 = $request['homework_unit1_3'];
                $report_unit3->free_unit_name3 = $request['homework_unit_name1_3'];
                $report_unit3->save();
            }

            // 宿題教材2
            if ($request['homework_text2'] != null) {
                $report_unit4 = new ReportUnit;
                $report_unit4->report_id = $report->report_id;
                $report_unit4->sub_cd = AppConst::REPORT_SUBCODE_4;
                $report_unit4->text_cd = $request['homework_text2'];
                $report_unit4->free_text_name = $request['homework_text_name2'];
                $report_unit4->text_page = $request['homework_page2'];
                $report_unit4->unit_category_cd1 = $request['homework_category2_1'];
                $report_unit4->free_category_name1 = $request['homework_category_name2_1'];
                $report_unit4->unit_cd1 = $request['homework_unit2_1'];
                $report_unit4->free_unit_name1 = $request['homework_unit_name2_1'];
                $report_unit4->unit_category_cd2 = $request['homework_category2_2'];
                $report_unit4->free_category_name2 = $request['homework_category_name2_2'];
                $report_unit4->unit_cd2 = $request['homework_unit2_2'];
                $report_unit4->free_unit_name2 = $request['homework_unit_name2_2'];
                $report_unit4->unit_category_cd3 = $request['homework_category2_3'];
                $report_unit4->free_category_name3 = $request['homework_category_name2_3'];
                $report_unit4->unit_cd3 = $request['homework_unit2_3'];
                $report_unit4->free_unit_name3 = $request['homework_unit_name2_3'];
                $report_unit4->save();
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

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // クエリを作成
        $query = Report::query();

        // データを取得
        $report = $this->getReport($id);

        // 教材リストを取得（授業科目コード指定）
        $texts = $this->mdlGetTextList($report->subject_cd, null, null);

        // 集団授業の場合受講生徒名取得
        if ($report->course_kind == AppConst::CODE_MASTER_42_2) {
            $class_member_names = $this->getClassMember($report->schedule_id);
        }
        else {
            $class_member_names = [];
        }

        $editdata = [
            'id' => $report->schedule_id,
            'monthly_goal' => $report->monthly_goal,
            'test_contents' => $report->test_contents,
            'test_score' => $report->test_score,
            'test_full_score' => $report->test_full_score,
            'achievement' => intval($report->achievement),
            'goodbad_point' => $report->goodbad_point,
            'solution' => $report->solution,
            'others_comment' => $report->others_comment
        ];

        // 授業教材１を取得
        $lesson_unit1 = $this->getReportUnit($report->report_id, AppConst::REPORT_SUBCODE_1);
        $editdata += [
            'lesson_text1' => $lesson_unit1->text_name1,
            'lesson_text_name1' => $lesson_unit1->free_text_name1,
            'lesson_page1' => $lesson_unit1->text_page1,
            'lesson_category1_1' => $lesson_unit1->unit_category_name1,
            'lesson_category1_2' => $lesson_unit1->unit_category_name2,
            'lesson_category1_3' => $lesson_unit1->unit_category_name3,
            'lesson_unit1_1' => $lesson_unit1->unit_name1,
            'lesson_unit1_2' => $lesson_unit1->unit_name2,
            'lesson_unit1_3' => $lesson_unit1->unit_name3,
            'lesson_category_name1_1' => $lesson_unit1->free_category_name1,
            'lesson_category_name1_2' => $lesson_unit1->free_category_name2,
            'lesson_category_name1_3' => $lesson_unit1->free_category_name2,
            'lesson_unit_name1_1' => $lesson_unit1->free_unit_name1,
            'lesson_unit_name1_2' => $lesson_unit1->free_unit_name2,
            'lesson_unit_name1_3' => $lesson_unit1->free_unit_name2
        ];
        // 授業教材２があれば取得
        if (ReportUnit::where('report_units.sub_cd', '=', AppConst::REPORT_SUBCODE_2)->exists()) {
            $lesson_unit2 = $this->getReportUnit($report->report_id, AppConst::REPORT_SUBCODE_2);
            $editdata += [
                'lesson_text2' => $lesson_unit2->text_name1,
                'lesson_text_name2' => $lesson_unit2->free_text_name1,
                'lesson_page2' => $lesson_unit2->text_page1,
                'lesson_category2_1' => $lesson_unit2->unit_category_name1,
                'lesson_category2_2' => $lesson_unit2->unit_category_name2,
                'lesson_category2_3' => $lesson_unit2->unit_category_name3,
                'lesson_unit2_1' => $lesson_unit2->unit_name1,
                'lesson_unit2_2' => $lesson_unit2->unit_name2,
                'lesson_unit2_3' => $lesson_unit2->unit_name3,
                'lesson_category_name2_1' => $lesson_unit2->free_category_name1,
                'lesson_category_name2_2' => $lesson_unit2->free_category_name2,
                'lesson_category_name2_3' => $lesson_unit2->free_category_name2,
                'lesson_unit_name2_1' => $lesson_unit2->free_unit_name1,
                'lesson_unit_name2_2' => $lesson_unit2->free_unit_name2,
                'lesson_unit_name2_3' => $lesson_unit2->free_unit_name2
            ];
        }
        // 宿題教材１があれば取得
        if (ReportUnit::where('report_units.sub_cd', '=', AppConst::REPORT_SUBCODE_3)->exists()) {
            $homework_unit1 = $this->getReportUnit($report->report_id, AppConst::REPORT_SUBCODE_3);
            $editdata += [
                'homework_text1' => $homework_unit1->text_name1,
                'homework_text_name1' => $homework_unit1->free_text_name1,
                'homework_page1' => $homework_unit1->text_page1,
                'homework_category1_1' => $homework_unit1->unit_category_name1,
                'homework_category1_2' => $homework_unit1->unit_category_name2,
                'homework_category1_3' => $homework_unit1->unit_category_name3,
                'homework_unit1_1' => $homework_unit1->unit_name1,
                'homework_unit1_2' => $homework_unit1->unit_name2,
                'homework_unit1_3' => $homework_unit1->unit_name3,
                'homework_category_name1_1' => $homework_unit1->free_category_name1,
                'homework_category_name1_2' => $homework_unit1->free_category_name2,
                'homework_category_name1_3' => $homework_unit1->free_category_name2,
                'homework_unit_name1_1' => $homework_unit1->free_unit_name1,
                'homework_unit_name1_2' => $homework_unit1->free_unit_name2,
                'homework_unit_name1_3' => $homework_unit1->free_unit_name2
            ];
        }
        // 宿題教材２があれば取得
        if (ReportUnit::where('report_units.sub_cd', '=', AppConst::REPORT_SUBCODE_4)->exists()) {
            $homework_unit2 = $this->getReportUnit($report->report_id, AppConst::REPORT_SUBCODE_4);
            $editdata += [
                'homework_text2' => $homework_unit2->text_name1,
                'homework_text_name2' => $homework_unit2->free_text_name1,
                'homework_page2' => $homework_unit2->text_page1,
                'homework_category2_1' => $homework_unit2->unit_category_name1,
                'homework_category2_2' => $homework_unit2->unit_category_name2,
                'homework_category2_3' => $homework_unit2->unit_category_name3,
                'homework_unit2_1' => $homework_unit2->unit_name1,
                'homework_unit2_2' => $homework_unit2->unit_name2,
                'homework_unit2_3' => $homework_unit2->unit_name3,
                'homework_category_name2_1' => $homework_unit2->free_category_name1,
                'homework_category_name2_2' => $homework_unit2->free_category_name2,
                'homework_category_name2_3' => $homework_unit2->free_category_name2,
                'homework_unit_name2_1' => $homework_unit2->free_unit_name1,
                'homework_unit_name2_2' => $homework_unit2->free_unit_name2,
                'homework_unit_name2_3' => $homework_unit2->free_unit_name2
            ];
        }

        // $this->debug($lesson_unit1);
        $this->debug($texts);

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
            'texts' => $texts,
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
        // $this->debug($request);

        // // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        // Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // // 対象データを取得(IDでユニークに取る)
        // $query = Report::query();

        // // 対象データを取得(PKでユニークに取る)
        // $report = $query
        //     ->where('report_id', $request->input('report_id'))
        //     // 受け持ち生徒に限定するガードを掛ける
        //     ->where($this->guardTutorTableWithSid())
        //     // 自分のアカウントIDでガードを掛ける（tid）
        //     ->where($this->guardTutorTableWithTid())
        //     // 該当データがない場合はエラーを返す
        //     ->firstOrFail();

        // // フォームから受け取った値を格納
        // $form = $request->only(
        //     'r_minutes',
        //     'content',
        //     'homework',
        //     'teacher_comment'
        // );

        // // 保存
        // $report->fill($form)->save();

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

        // 対象データを取得(IDでユニークに取る)
        $query = Report::query();

        // 対象データを取得(PKでユニークに取る)
        $report = $query
            ->where('report_id', $request->input('report_id'))
            // 受け持ち生徒に限定するガードを掛ける
            ->where($this->guardTutorTableWithSid())
            // 自分のアカウントIDでガードを掛ける（tid）
            ->where($this->guardTutorTableWithTid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // Reportテーブルのdelete
        $report->delete();

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

        $page_rule = ReportUnit::fieldRules('text_page');
        $text_name_rule = ReportUnit::fieldRules('free_text_name');
        $category_name_rule = ReportUnit::fieldRules('free_category_name1');
        $unit_name_rule = ReportUnit::fieldRules('free_unit_name1');

        $rules += Report::fieldRules('monthly_goal');
        $rules += Report::fieldRules('test_contents');
        $rules += Report::fieldRules('test_score');
        $rules += Report::fieldRules('test_full_score');
        $rules += Report::fieldRules('achievement');
        $rules += Report::fieldRules('goodbad_point');
        $rules += Report::fieldRules('solution');
        $rules += Report::fieldRules('others_comment');
        // $rules += ['lesson_text1' => ['required', $text_rule]];
        // $rules += ['lesson_text2' => $text_rule];
        // $rules += ['homework_text1' => $text_rule];
        // $rules += ['homework_text2' => $text_rule];
        // $rules += ['lesson_category1_1' => $category_rule];
        // $rules += ['lesson_category1_2' => $category_rule];
        // $rules += ['lesson_category1_3' => $category_rule];
        // $rules += ['lesson_category2_1' => $category_rule];
        // $rules += ['lesson_category2_2' => $category_rule];
        // $rules += ['lesson_category2_3' => $category_rule];
        // $rules += ['homework_category1_1' => $category_rule];
        // $rules += ['homework_category1_2' => $category_rule];
        // $rules += ['homework_category1_3' => $category_rule];
        // $rules += ['homework_category2_1' => $category_rule];
        // $rules += ['homework_category2_2' => $category_rule];
        // $rules += ['homework_category2_3' => $category_rule];
        // $rules += ['lesson_unit1_1' => $unit_rule];
        // $rules += ['lesson_unit1_2' => $unit_rule];
        // $rules += ['lesson_unit1_3' => $unit_rule];
        // $rules += ['lesson_unit2_1' => $unit_rule];
        // $rules += ['lesson_unit2_2' => $unit_rule];
        // $rules += ['lesson_unit2_3' => $unit_rule];
        // $rules += ['homework_unit1_1' => $unit_rule];
        // $rules += ['homework_unit1_2' => $unit_rule];
        // $rules += ['homework_unit1_3' => $unit_rule];
        // $rules += ['homework_unit2_1' => $unit_rule];
        // $rules += ['homework_unit2_2' => $unit_rule];
        // $rules += ['homework_unit2_3' => $unit_rule];
        // $rules += ['lesson_page1' => $page_rule];
        // $rules += ['lesson_page2' => $page_rule];
        // $rules += ['homework_page1' => $page_rule];
        // $rules += ['homework_page2' => $page_rule];
        // $rules += ['lesson_text_name1' => $text_name_rule];
        // $rules += ['lesson_text_name2' => $text_name_rule];
        // $rules += ['homework_text_name1' => $text_name_rule];
        // $rules += ['homework_text_name2' => $text_name_rule];
        // $rules += ['lesson_category_name1_1' => $category_name_rule];
        // $rules += ['lesson_category_name1_2' => $category_name_rule];
        // $rules += ['lesson_category_name1_3' => $category_name_rule];
        // $rules += ['lesson_category_name2_1' => $category_name_rule];
        // $rules += ['lesson_category_name2_2' => $category_name_rule];
        // $rules += ['lesson_category_name2_3' => $category_name_rule];
        // $rules += ['homework_category_name1_1' => $category_name_rule];
        // $rules += ['homework_category_name1_2' => $category_name_rule];
        // $rules += ['homework_category_name1_3' => $category_name_rule];
        // $rules += ['homework_category_name2_1' => $category_name_rule];
        // $rules += ['homework_category_name2_2' => $category_name_rule];
        // $rules += ['homework_category_name2_3' => $category_name_rule];
        // $rules += ['lesson_unit_name1_1' => $unit_name_rule];
        // $rules += ['lesson_unit_name1_2' => $unit_name_rule];
        // $rules += ['lesson_unit_name1_3' => $unit_name_rule];
        // $rules += ['lesson_unit_name2_1' => $unit_name_rule];
        // $rules += ['lesson_unit_name2_2' => $unit_name_rule];
        // $rules += ['lesson_unit_name2_3' => $unit_name_rule];
        // $rules += ['homework_unit_name1_1' => $unit_name_rule];
        // $rules += ['homework_unit_name1_2' => $unit_name_rule];
        // $rules += ['homework_unit_name1_3' => $unit_name_rule];
        // $rules += ['homework_unit_name2_1' => $unit_name_rule];
        // $rules += ['homework_unit_name2_2' => $unit_name_rule];
        // $rules += ['homework_unit_name2_3' => $unit_name_rule];

        return $rules;
    }
    //==========================
    // 授業報告書教材情報の取得
    //==========================
    protected function getReport($id)
    {
        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        // クエリを作成
        $query = Report::query();

        // データを取得
        $report = $query
            // IDを指定
            ->where('reports.report_id', $id)
            ->select(
                'reports.report_id',
                'reports.schedule_id',
                'reports.regist_date',
                'reports.lesson_date',
                'reports.period_no',
                'reports.campus_cd',
                // 校舎名
                'campus_names.room_name as campus_name',
                'reports.course_cd',
                // コース名
                'mst_courses.name as course_name',
                'reports.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'reports.monthly_goal',
                'reports.test_contents',
                'reports.test_score',
                'reports.test_full_score',
                'reports.achievement',
                'reports.goodbad_point',
                'reports.solution',
                'reports.others_comment',
                'reports.approval_status',
                // コードマスタの名称（ステータス）
                'mst_codes.name as status',
                // コース種別
                'mst_courses.course_kind as course_kind',
                'reports.admin_comment',
                'schedules.subject_cd as subject_cd',
                // 科目名
                'mst_subjects.name as subject_name',
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('reports.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'reports.student_id', '=', 'students.student_id')
            // スケジュール情報とJOIN
            ->sdLeftJoin(Schedule::class, 'reports.schedule_id', '=', 'schedules.schedule_id')
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd');
            })
            // 科目の取得
            ->sdLeftJoin(MstSubject::class, 'schedules.subject_cd', '=', 'mst_subjects.subject_cd')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('reports.approval_status', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_2);
            })
            ->first();
        
        return $report;
    }
    //==========================
    // 授業報告書教材情報の取得
    //==========================
    protected function getClassMember($schedule_id)
    {
        $class_members = ClassMember::query()
                ->where('class_members.schedule_id', '=', $schedule_id)
                ->select('class_members.student_id')
                ->get();
            
        // 受講人数カウント
        $number_people = count($class_members);
            
        // 受講生徒名を配列に格納
        $class_member_names = [];
        for ($i = 0; $i < $number_people; $i++) {
            $class_member_names[$i] = $this->mdlGetStudentName($class_members[$i]['student_id']);
        }

        return $class_member_names;
    }
    //==========================
    // 授業報告書教材情報の取得 詳細モーダル表示用
    //==========================
    // 教材情報取得関数
    protected function getReportText($report_id,$sub_code)
    {
        $report_text = ReportUnit::query()
            ->where('report_units.report_id', '=', $report_id)
            ->where('report_units.sub_cd', '=', $sub_code)
            ->select(
                'mst_texts.name as text_name1',// 教材名
                'report_units.free_text_name as free_text_name1',// 教材名フリー
                'report_units.text_page as text_page1',
            )
            // 教材名の取得
            ->sdLeftJoin(MstText::class, function ($join) {
                $join->on('report_units.text_cd', '=', 'mst_texts.text_cd');
            })
            ->first();

        return $report_text;
    }
    // 単元分類・単元情報取得
    protected function getReportCategory($report_id, $sub_code, $no)
    {
        if($no == 1) {
            $report_category = ReportUnit::query()
                ->where('report_units.report_id', '=', $report_id)
                ->where('report_units.sub_cd', '=', $sub_code)
                ->select(
                    'mst_unit_categories.name as unit_category_name',// 単元分類名
                    'report_units.free_category_name1 as free_category_name',// 単元分類名フリー
                    'mst_units.name as unit_name',// 単元名
                    'report_units.free_unit_name1 as free_unit_name',// 単元名フリー
                )
                // 単元分類名の取得
                ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                    $join->on('report_units.unit_category_cd1', '=', 'mst_unit_categories.unit_category_cd');
                })
                // 単元名取得
                ->sdLeftJoin(MstUnit::class, function ($join) {
                    $join->on('report_units.unit_cd1', '=', 'mst_units.unit_cd');
                })
                ->first();
        }
        if($no == 2) {
            $report_category = ReportUnit::query()
                ->where('report_units.report_id', '=', $report_id)
                ->where('report_units.sub_cd', '=', $sub_code)
                ->select(
                    'mst_unit_categories.name as unit_category_name',// 単元分類名
                    'report_units.free_category_name2 as free_category_name',// 単元分類名フリー
                    'mst_units.name as unit_name',// 単元名
                    'report_units.free_unit_name2 as free_unit_name',// 単元名フリー
                )
                // 単元分類名の取得
                ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                    $join->on('report_units.unit_category_cd2', '=', 'mst_unit_categories.unit_category_cd');
                })
                // 単元名取得
                ->sdLeftJoin(MstUnit::class, function ($join) {
                    $join->on('report_units.unit_cd2', '=', 'mst_units.unit_cd');
                })
                ->first();
        }
        if($no == 3) {
            $report_category = ReportUnit::query()
                ->where('report_units.report_id', '=', $report_id)
                ->where('report_units.sub_cd', '=', $sub_code)
                ->select(
                    'mst_unit_categories.name as unit_category_name',// 単元分類名
                    'report_units.free_category_name3 as free_category_name',// 単元分類名フリー
                    'mst_units.name as unit_name',// 単元名
                    'report_units.free_unit_name3 as free_unit_name',// 単元名フリー
                )
                // 単元分類名の取得
                ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                    $join->on('report_units.unit_category_cd3', '=', 'mst_unit_categories.unit_category_cd');
                })
                // 単元名取得
                ->sdLeftJoin(MstUnit::class, function ($join) {
                    $join->on('report_units.unit_cd3', '=', 'mst_units.unit_cd');
                })
                ->first();
        }
        return $report_category;
    }
    //==========================
    // 授業報告書教材情報の取得 編集画面用
    //==========================
    protected function getReportUnit($report_id, $sub_code)
    {
        $report_unit = ReportUnit::query()
            ->where('report_units.report_id', '=', $report_id)
            ->where('report_units.sub_cd', '=', $sub_code)
            ->select(
                'report_units.report_id',
                'report_units.sub_cd',
                'report_units.text_cd',
                'mst_texts1.name as text_name1',// 教材名
                'report_units.free_text_name as free_text_name1',// 教材名フリー
                'report_units.text_page as text_page1',
                'report_units.unit_category_cd1',
                'mst_unit_categories1.name as unit_category_name1',// 単元分類名
                'report_units.free_category_name1 as free_category_name1',// 単元分類名フリー
                'report_units.unit_cd1',
                'mst_units1.name as unit_name1',// 単元名
                'report_units.free_unit_name1 as free_unit_name1',// 単元名フリー
                'report_units.unit_category_cd2',
                'mst_unit_categories2.name as unit_category_name2',// 単元分類名
                'report_units.free_category_name2 as free_category_name2',// 単元分類名フリー
                'report_units.unit_cd2',
                'mst_units2.name as unit_name2',// 単元名
                'report_units.free_unit_name2 as free_unit_name2',// 単元名フリー
                'report_units.unit_category_cd3',
                'mst_unit_categories3.name as unit_category_name3',// 単元分類名
                'report_units.free_category_name3 as free_category_name3',// 単元分類名フリー
                'report_units.unit_cd3',
                'mst_units3.name as unit_name3',// 単元名
                'report_units.free_unit_name3 as free_unit_name3',// 単元名フリー
            )
            // 教材名の取得
            ->sdLeftJoin(MstText::class, function ($join) {
                $join->on('report_units.text_cd', '=', 'mst_texts1.text_cd');
            }, 'mst_texts1')
            // 単元分類名の取得
            ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                $join->on('report_units.unit_category_cd1', '=', 'mst_unit_categories1.unit_category_cd');
            }, 'mst_unit_categories1')
            ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                $join->on('report_units.unit_category_cd2', '=', 'mst_unit_categories2.unit_category_cd');
            }, 'mst_unit_categories2')
            ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                $join->on('report_units.unit_category_cd3', '=', 'mst_unit_categories3.unit_category_cd');
            }, 'mst_unit_categories3')
            // 単元名取得
            ->sdLeftJoin(MstUnit::class, function ($join) {
                $join->on('report_units.unit_cd1', '=', 'mst_units1.unit_cd');
            }, 'mst_units1')
            ->sdLeftJoin(MstUnit::class, function ($join) {
                $join->on('report_units.unit_cd2', '=', 'mst_units2.unit_cd');
            }, 'mst_units2')
            ->sdLeftJoin(MstUnit::class, function ($join) {
                $join->on('report_units.unit_cd3', '=', 'mst_units3.unit_cd');
            }, 'mst_units3')
            ->first();

        return $report_unit;
    }
}
