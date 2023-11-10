<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Report;
use App\Models\ReportUnit;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\MstCourse;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncReportTrait;

/**
 * 授業報告 - コントローラ
 */
class ReportCheckController extends Controller
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

        // 学年リストを取得
        $grades = $this->mdlGetGradeList();

        // 講師リストを取得
        $tutors = $this->mdlGetTutorList();

        // コースリストを取得
        $courses = $this->mdlGetCourseList();

        // 報告書承認リストを取得（サブコード指定で絞り込み）
        $subCodes = [1];
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_4, $subCodes);

        return view('pages.admin.report_check', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'grades' => $grades,
            'tutors' => $tutors,
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

        // 生徒リスト取得
        if ($campus_cd == -1 || !filled($campus_cd)) {
            // -1 または 空白の場合、自分の受け持ちの生徒だけに絞り込み
            // 生徒リストを取得
            $students = $this->mdlGetStudentList();
        } else {
            $students = $this->mdlGetStudentList($campus_cd);
        }

        return [
            'selectItems' => $this->objToArray($students),
        ];
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
        $query = Report::query();

        // 校舎コード選択による絞り込み条件
        // -1 は未選択状態のため、-1以外の場合に校舎コードの絞り込みを行う
        if (isset($form['campus_cd']) && filled($form['campus_cd']) && $form['campus_cd'] != -1) {
            // 検索フォームから取得（スコープ）
            $query->SearchCampusCd($form);
        }

        // 学年コード選択により絞り込み条件
        if (isset($form['grade_cd']) && filled($form['grade_cd'])) {
            // 検索フォームから取得（スコープ）
            $query->SearchGradeCd($form);
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

        // 講師IDの検索（スコープで指定する）
        $query->SearchTid($form);

        // 認定日の絞り込み条件
        $query->SearchLessonDateFrom($form);
        $query->SearchLessonDateTo($form);

        // 校舎名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // データを取得
        $reports = $query
            ->select(
                'reports.report_id as id',
                'reports.lesson_date',
                'reports.period_no',
                'reports.tutor_id',
                'reports.approval_status',
                'reports.regist_date',
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
            ->distinct()
            ->orderby('lesson_date', 'desc')
            ->orderby('period_no', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $reports);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
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

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return mixed ルール
     */
    private function rulesForSearch()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 初期表示の時はエラーを発生させないようにする
                if ($value == -1) return;

                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 学年
        $validationClassesList =  function ($attribute, $value, $fail) {

            // 学年リストを取得
            $classes = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_44_4);
            if (!isset($classes[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒ID
        $validationStudentList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $students = $this->mdlGetStudentList();
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 教師名
        $validationTeacherList =  function ($attribute, $value, $fail) {

            // 講師リストを取得
            $tutors = $this->mdlGetTutorList();
            if (!isset($tutors[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 承認ステータス
        $validationStatus =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += Report::fieldRules('campus_cd', [$validationRoomList]);
        $rules += Report::fieldRules('student_id', [$validationStudentList]);
        $rules += Report::fieldRules('tutor_id', [$validationTeacherList]);
        $rules += ['grade_cd' => [$validationClassesList]];
        $rules += Report::fieldRules('approval_status', [$validationStatus]);
        
        return $rules;
    }

    //==========================
    // 編集
    //==========================

    /**
     * 編集画面
     *
     * @param int reportId 授業報告書ID
     * @return view
     */
    public function edit($reportId)
    {
        // IDのバリデーション
        $this->validateIds($reportId);

        // データを取得
        $report = $this->getReport($reportId);

        // 集団授業の場合受講生徒名取得
        if ($report->course_kind == AppConst::CODE_MASTER_42_2) {
            $class_member_names = $this->getClassMember($report->schedule_id);
        } else {
            $class_member_names = [];
        }

        $editdata = [
            'id' => $report->schedule_id,
            'report_id' => $report->report_id,
            'approval_status' => $report->approval_status,
            'admin_comment' => $report->admin_comment
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
                    'text_name_' . $subCode => $report_unit->text_name1,
                    'free_text_name_' . $subCode => $report_unit->free_text_name,
                    'text_page_' . $subCode => $report_unit->text_page1,
                    'category_name1_' . $subCode => $report_unit->unit_category_name1,
                    'category_name2_' . $subCode => $report_unit->unit_category_name2,
                    'category_name3_' . $subCode => $report_unit->unit_category_name3,
                    'free_category_name1_' . $subCode => $report_unit->free_category_name1,
                    'free_category_name2_' . $subCode => $report_unit->free_category_name2,
                    'free_category_name3_' . $subCode => $report_unit->free_category_name3,
                    'unit_name1_' . $subCode => $report_unit->unit_name1,
                    'unit_name2_' . $subCode => $report_unit->unit_name2,
                    'unit_name3_' . $subCode => $report_unit->unit_name3,
                    'free_unit_name1_' . $subCode => $report_unit->free_unit_name1,
                    'free_unit_name2_' . $subCode => $report_unit->free_unit_name2,
                    'free_unit_name3_' . $subCode => $report_unit->free_unit_name3,
                ];
            }
            else {
                $editdata += [
                    'text_name_' . $subCode => null,
                    'free_text_name_' . $subCode => null,
                    'text_page_' . $subCode => null,
                    'category_name1_' . $subCode => null,
                    'category_name2_' . $subCode => null,
                    'category_name3_' . $subCode => null,
                    'free_category_name1_' . $subCode => null,
                    'free_category_name2_' . $subCode => null,
                    'free_category_name3_' . $subCode => null,
                    'unit_name1_' . $subCode => null,
                    'unit_name2_' . $subCode => null,
                    'unit_name3_' . $subCode => null,
                    'free_unit_name1_' . $subCode => null,
                    'free_unit_name2_' . $subCode => null,
                    'free_unit_name3_' . $subCode => null
                ];
            }
        }

        // 報告書承認リストを取得（サブコード指定で絞り込み）
        $subCodes = [1];
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_4, $subCodes);

        return view('pages.admin.report_check-edit', [
            'editData' => $editdata,
            'rules' => $this->rulesForInput(null),
            'report' => $report,
            'class_member_names' => $class_member_names,
            'statusList' => $statusList
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

        // 対象データを取得(PKでユニークに取る)
        $query = Report::query();

        // 校舎管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        $report = $query
            ->where('report_id', $request->input('report_id'))
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // フォームから受け取った値を格納
        $form = $request->only(
            'approval_status',
            'admin_comment'
        );

        // 更新
        $report->fill($form)->save();

        return;
    }

    /**
     * 承認処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function approval(Request $request)
    {
        $this->debug($request);

        return redirect('/report_check/');
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
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInput()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 承認ステータス
        $validationStatus =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += Report::fieldRules('approval_status', [$validationStatus]);
        $rules += Report::fieldRules('admin_comment');

        return $rules;
    }
}
