<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
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

        // $this->debug($grades);

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

        // $this->debug($request);

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

        $this->debug($id);

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

        $rules += Report::fieldRules('campus_cd', [$validationRoomList]);
        $rules += Report::fieldRules('student_id', [$validationStudentsList]);
        
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

        // // IDのバリデーション
        // $this->validateIds($reportId);

        // // クエリを作成
        // $query = Report::query();

        // // 校舎管理者の場合、自分の校舎コードのみにガードを掛ける
        // $query->where($this->guardRoomAdminTableWithRoomCd());

        // // データを取得
        // $report = $query
        //     // IDを指定
        //     ->where('report.report_id', $reportId)
        //     ->select(
        //         'report_id',
        //         'regist_time',
        //         'lesson_type',
        //         'id',
        //         'id as _id', // hiddenに退避
        //         'lesson_date',
        //         'start_time',
        //         'report.sid',
        //         'report.tid',
        //         'ext_rirekisho.name as tname',
        //         'r_minutes',
        //         'content',
        //         'homework',
        //         'teacher_comment',
        //         'parents_comment'
        //     )
        //     // 教師名の取得
        //     ->sdLeftJoin(ExtRirekisho::class, 'report.tid', '=', 'ext_rirekisho.tid')
        //     ->firstOrFail();

        // if ($report->lesson_type == AppConst::CODE_MASTER_8_1) {
        //     // 個別校舎の場合、生徒IDをセットする
        //     $report['sidKobetsu'] = $report->sid;
        //     $report->sid = null;
        // }

        // // 教師の担当している生徒の一覧を取得(個別校舎)
        // // このプルダウン自体は登録には使わず、個別校舎のスケジュールのプルダウンを作成するために使用される
        // // 家庭教師以外
        // $studentsKobetsu = $this->mdlGetStudentListForT(null, $report->tid, AppConst::EXT_GENERIC_MASTER_101_900);

        // // 家庭教師の受け持ち生徒名プルダウンメニューを作成
        // $students = $this->mdlGetStudentListForT(AppConst::EXT_GENERIC_MASTER_101_900, $report->tid);

        // // 授業時間数のプルダウンメニューを作成
        // $minutes = $this->getMenuOfMinutes();

        return view('pages.admin.report_check-edit', [
            'editData' => null,
            'rules' => $this->rulesForInput(null),
            'student_kobetsu_list' => null,
            'student_list' => null,
            'minutes_list' => null
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

        // 登録者の教師ID取得
        $tid = $request->input('tid');

        // 対象データを取得(PKでユニークに取る)
        $query = Report::query();

        // 校舎管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        $report = $query
            ->where('report_id', $request->input('report_id'))
            // 登録者の教師IDでも絞り込み
            ->where('tid', $tid)
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        if ($request->input('lesson_type') == AppConst::CODE_MASTER_8_1) {
            //---------------
            // 個別校舎登録
            //---------------
            $id = $request->input('id');

            // スケジュールidから授業日・授業開始時間・校舎・生徒を取得する。
            $query = ExtSchedule::query();

            // 校舎管理者の場合、自分の校舎コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());

            $lesson = $query
                ->select(
                    'roomcd',
                    'lesson_date',
                    'start_time',
                    'sid'
                )
                ->where('id', '=', $id)
                // 登録者の教師IDでも絞り込み
                ->where('tid', '=', $tid)
                ->firstOrFail();

            $roomcd = $lesson->roomcd;
            $lesson_date = $lesson->lesson_date;
            $start_time = $lesson->start_time;
            $sid = $lesson->sid;
        } elseif ($request->input('lesson_type') == AppConst::CODE_MASTER_8_2) {
            //---------------
            // 家庭教師登録
            //---------------
            $id = null;
            $roomcd = AppConst::EXT_GENERIC_MASTER_101_900;
            $lesson_date = $request->input('lesson_date');
            $start_time = $request->input('start_time');
            $sid = $request->input('sid');
        } else {
            $this->illegalResponseErr();
        }

        // フォームから受け取った値を格納
        $form = $request->only(
            'regist_time',
            'lesson_type',
            'r_minutes',
            'content',
            'homework',
            'teacher_comment',
            'parents_comment'
        );

        // 保存
        $report->id = $id;
        $report->roomcd = $roomcd;
        $report->lesson_date = $lesson_date;
        $report->start_time = $start_time;
        $report->sid = $sid;
        $report->fill($form)->save();

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
        $report_id = $request->input('report_id');
        $tid = $request->input('tid');

        // 対象データを取得(PKでユニークに取る)
        $query = Report::query();

        // 校舎管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        $report = $query
            ->where('report_id', $report_id)
            // 登録者の教師IDでも絞り込み
            ->where('tid', $tid)
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // Reportテーブルのdelete
        $report->delete();

        return;
    }

    /**
     * 校舎・生徒情報取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 教師、生徒情報
     */
    public function getDataSelect(Request $request)
    {

        // IDのバリデーション
        // スケジュールIDは生徒IDの後に受け取れるのでsidのみ必須チェックする
        $this->validateIdsFromRequest($request, 'reportId', 'sid');

        // IDを取得
        $reportId =  $request->input('reportId');
        $schedule_id = $request->input('id');
        $sid = $request->input('sid');

        // reportIdを取得してtidを取得する
        $tid = $this->getTidFormReport($reportId);

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------

        // 教師の担当している生徒の一覧を取得(家庭教師は除く)
        $students = $this->mdlGetStudentListForT(null, $tid, AppConst::EXT_GENERIC_MASTER_101_900);

        // 生徒一覧にsidがあるかチェック
        $this->guardListValue($students, $sid);

        //---------------------------
        // スケジュールプルダウンの作成
        //---------------------------

        // 教師に紐づくスケジュールを取得
        // 校舎管理者の場合、自分の校舎コードのスケジュールのみにガードを掛ける
        if (AuthEx::isRoomAdmin()) {
            $account = Auth::user();
            $myRoomCd = $account->roomcd;
        } else {
            $myRoomCd = null;
        }

        // 個別校舎のスケジュールプルダウンメニューを作成
        $scheduleMaster = $this->getScheduleListReport($tid, $myRoomCd, $sid);

        //---------------------------
        // 校舎を返却する
        //---------------------------
        $room_name = null;
        if (filled($schedule_id)) {
            // idが指定されている場合のみ

            // [ガード] スケジュールIDがプルダウンの中にあるかチェック
            $this->guardListValue($scheduleMaster, $schedule_id);

            // スケジュールの取得(ガードはこの中でも掛ける)
            $lesson = $this->mdlGetScheduleDtl($schedule_id);

            // 変数にセット
            $room_name = $lesson->room_name;
        }

        return [
            'selectItems' => $this->objToArray($scheduleMaster),
            'class_name' => $room_name
        ];
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
    private function rulesForInput(?Request $request)
    {

        // 独自バリデーション: 重複チェック(個別校舎登録用)
        $validationDuplicateRegular = function ($attribute, $value, $fail) use ($request) {

            if (!isset($request['id'])) {
                // requiredでチェックするのでreturn
                return;
            }
            if ($request['lesson_type'] != AppConst::CODE_MASTER_8_1) {
                // 種別で判断
                return;
            }

            // 対象データを取得(PKでユニークに取る)
            // スケジュールID
            $exists = Report::where('id', $request['id'])
                // 授業種別
                ->where('lesson_type', AppConst::CODE_MASTER_8_1)
                // 更新中のキー以外を検索
                ->where('report_id', '!=', $request['report_id'])
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // 独自バリデーション: 重複チェック(家庭教師登録用)
        $validationDuplicateHomeTeacher = function ($attribute, $value, $fail) use ($request) {

            if (!isset($request['lesson_date'])) {
                // requiredでチェックするのでreturn
                return;
            }
            if ($request['lesson_type'] != AppConst::CODE_MASTER_8_2) {
                // 種別で判断
                return;
            }

            // 授業日・開始時刻が現在日付時刻以前の授業のみ登録可とする
            $lesson_datetime = $request['lesson_date'] . " " . $request['start_time'];
            $today = date("Y/m/d H:i");

            if (strtotime($lesson_datetime) > strtotime($today)) {
                // 日時チェックエラー
                return $fail(Lang::get('validation.before_today'));
            }

            // 対象データを取得(PKでユニークに取る)
            $exists = Report::where('tid', $request['tid'])
                ->where('lesson_date', $request['lesson_date'])
                ->where('start_time', $request['start_time'])
                // 授業種別
                ->where('lesson_type', AppConst::CODE_MASTER_8_2)
                // 更新中のキー以外を検索
                ->where('report_id', '!=', $request['report_id'])
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // 独自バリデーション: 授業種別（ラジオ）
        $validationRadioLessonType = function ($attribute, $value, $fail) use ($request) {

            // ラジオの値のチェック
            if (
                $request['lesson_type'] != AppConst::CODE_MASTER_8_1 &&
                $request['lesson_type'] != AppConst::CODE_MASTER_8_2
            ) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
            // 校舎管理者の場合、ラジオの値の変更は不可とする
            if (AuthEx::isRoomAdmin()) {
                $account = Auth::user();
                if (
                    $account->roomcd == AppConst::EXT_GENERIC_MASTER_101_900 &&
                    $request->input('lesson_type') == AppConst::CODE_MASTER_8_1
                ) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                } else if (
                    $account->roomcd != AppConst::EXT_GENERIC_MASTER_101_900 &&
                    $request->input('lesson_type') == AppConst::CODE_MASTER_8_2
                ) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 独自バリデーション: リストのチェック 授業時間
        $validationMinutesList =  function ($attribute, $value, $fail) {

            // 授業時間数のプルダウンメニューを作成
            $minutes = $this->getMenuOfMinutes();
            if (!isset($minutes[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 個別校舎のスケジュール
        $validationScheduleMasterList =  function ($attribute, $value, $fail) use ($request) {

            if (!isset($request)) return;

            // reportIdを取得してtidを取得する
            $tid = $this->getTidFormReport($request['report_id']);

            // 教師に紐づくスケジュールを取得
            // 校舎管理者の場合、自分の校舎コードのスケジュールのみにガードを掛ける
            if (AuthEx::isRoomAdmin()) {
                $account = Auth::user();
                $myRoomCd = $account->roomcd;
            } else {
                $myRoomCd = null;
            }

            // 個別校舎のスケジュールプルダウンメニューを作成
            $scheduleMaster = $this->getScheduleListReport($tid, $myRoomCd);

            if (!isset($scheduleMaster[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 家庭教師の受け持ち生徒名
        $validationStudentsList =  function ($attribute, $value, $fail) use ($request) {

            if (!isset($request)) return;

            // reportIdを取得してtidを取得する
            $tid = $this->getTidFormReport($request['report_id']);

            // 家庭教師の受け持ち生徒名プルダウンメニューを作成
            $students = $this->mdlGetStudentListForT(AppConst::EXT_GENERIC_MASTER_101_900, $tid);
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒ID(個別校舎)
        $validationSidList =  function ($attribute, $value, $fail) use ($request) {

            // reportIdを取得してtidを取得する
            $tid = $this->getTidFormReport($request['report_id']);

            // 教師の担当している生徒の一覧を取得
            $students = $this->mdlGetStudentListForT(null, $tid, AppConst::EXT_GENERIC_MASTER_101_900);

            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する

        // 個別校舎の生徒ID
        $ruleSid = ExtSchedule::getFieldRule('sid');
        $rules += ['sidKobetsu' =>  array_merge(
            $ruleSid,
            ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_1, $validationSidList]
        )];

        // 項目のバリデーションルールをベースにする
        $ruleId = Report::getFieldRule('id');
        $ruleLessonDate = Report::getFieldRule('lesson_date');
        $rules += Report::fieldRules('lesson_type', ['required', $validationRadioLessonType]);
        $rules += Report::fieldRules('regist_time', ['required']);
        $rules += Report::fieldRules('start_time', ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_2]);
        $rules += Report::fieldRules('sid', ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_2, $validationStudentsList]);

        // 授業種別が個別校舎の場合、スケジュールIDの必須チェックと重複チェック
        $rules += ['id' =>  array_merge(
            $ruleId,
            ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_1, $validationDuplicateRegular, $validationScheduleMasterList]
        )];

        // 授業種別が家庭教師の場合、必須チェックと重複チェック
        $rules += ['lesson_date' =>  array_merge(
            $ruleLessonDate,
            ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_2, $validationDuplicateHomeTeacher]
        )];

        // MEMO: 不正アクセス対策として、report_idもルールに追加する
        $rules += Report::fieldRules('report_id', ['required']);
        $rules += Report::fieldRules('r_minutes', ['required', $validationMinutesList]);
        $rules += Report::fieldRules('content', ['required']);
        $rules += Report::fieldRules('homework');
        $rules += Report::fieldRules('teacher_comment', ['required']);
        $rules += Report::fieldRules('parents_comment');

        return $rules;
    }

    /**
     * レポートIDから教師IDを取得
     * 
     * @param int $transferApplyId 振替ID
     */
    private function getTidFormReport($reportId)
    {
        // クエリを作成
        $query = Report::query();

        // 校舎管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // データを取得
        $report = $query
            // IDを指定
            ->where('report.report_id', $reportId)
            ->select(
                'report.tid',
            )
            ->firstOrFail();

        return $report->tid;
    }
    /**
     * 授業報告書教材情報の取得
     *
     * @param integer $id report_id
     * @return array
     */
    private function getReport($id)
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
    /**
     * 集団授業の生徒名取得
     *
     * @param integer $schedule_id スケジュールID
     * @return array
     */
    private function getClassMember($schedule_id)
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
    /**
     * 授業報告書教材情報の取得 詳細モーダル表示用
     *
     * @param integer $report_id 授業報告書ID
     * @param integer $sub_code AppConst 授業報告書サブコード
     * @return array
     */
    private function getReportText($report_id,$sub_code)
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
    /**
     * 単元分類・単元情報取得 詳細モーダル表示用
     *
     * @param integer $report_id 授業報告書ID
     * @param integer $sub_code AppConst 授業報告書サブコード
     * @param integer $i 1~3単元分類の数
     * @return array
     */
    private function getReportCategory($report_id, $sub_code, $no)
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
                    $join->on('report_units.unit_category_cd1', '=', 'mst_units.unit_category_cd');
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
                    $join->on('report_units.unit_category_cd2', '=', 'mst_units.unit_category_cd');
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
                    $join->on('report_units.unit_category_cd3', '=', 'mst_units.unit_category_cd');
                })
                ->first();
        }
        return $report_category;
    }
    /**
     * 授業報告書教材情報の取得 編集画面用
     *
     * @param integer $report_id 授業報告書ID
     * @param integer $sub_code AppConst 授業報告書サブコード
     * @return array
     */
    private function getReportUnit($report_id, $sub_code)
    {
        $report_unit = ReportUnit::query()
            ->where('report_units.report_id', '=', $report_id)
            ->where('report_units.sub_cd', '=', $sub_code)
            ->select(
                'report_units.report_id',
                'report_units.sub_cd',
                'report_units.text_cd',
                'mst_texts1.name as text_name1',// 教材名
                'report_units.free_text_name as free_text_name',// 教材名フリー
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
