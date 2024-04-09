<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\MstCourse;
use App\Models\MstBooth;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use App\Http\Controllers\Traits\FuncScheduleTrait;
use Illuminate\Support\Facades\Lang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Libs\AuthEx;

/**
 * 教室カレンダー - コントローラ
 */
class RoomCalendarController extends Controller
{

    // 機能共通処理：カレンダー
    use FuncCalendarTrait;
    // 機能共通処理：スケジュール関連
    use FuncScheduleTrait;

    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
    }

    //==========================
    // カレンダー
    //==========================

    /**
     * カレンダー初期画面
     *
     * @return view
     */
    public function calendar()
    {

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // セッションからデータを取得（取得後キー削除）
        $sessionCampus = session()->pull('session_campus_cd', null);
        $sessionDate = session()->pull('session_date', null);

        // セッションデータのチェック
        if (!is_null($sessionCampus)) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $this->guardRoomAdminRoomcd($sessionCampus);
        }
        if (!is_null($sessionDate)) {
            // 日付のバリデーション
            $this->validateDates($sessionDate);
        }

        return view('pages.admin.room_calendar', [
            'rooms' => $rooms,
            'editData' => [
                'campus_cd' => $sessionCampus,
                'target_date' => $sessionDate
            ]
        ]);
    }

    /**
     * カレンダー
     *
     * @param string $campusCd 校舎コード
     * @param string $dateStr 日付文字列(年月日)
     * @return view
     */
    public function calendarBack($campusCd, $dateStr)
    {
        // パラメータ取得・日時切り分け
        $date = substr($dateStr, 0, 4) . '-' . substr($dateStr, 4, 2) . '-' . substr($dateStr, 6, 2);

        // パラメータのバリデーション
        $this->validateDates($date);

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $this->guardRoomAdminRoomcd($campusCd);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(false);

        return view('pages.admin.room_calendar', [
            'rooms' => $rooms,
            'editData' => [
                'campus_cd' => $campusCd,
                'target_date' => $date
            ]
        ]);
    }

    /**
     * ブース情報取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return object ブース情報
     */
    public function getBooth(Request $request)
    {

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $campusCd = $request->input('campus_cd');
        $this->guardRoomAdminRoomcd($campusCd);

        // クエリを作成
        $query = MstBooth::query();

        // 校舎の絞り込み条件
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        }
        $query->where('campus_cd', $campusCd);

        // データを取得
        $mstBooth = $query
            ->select(
                'mst_booths.booth_cd as id',
                'mst_booths.name as title',
                'mst_booths.disp_order',
            )
            ->orderby('disp_order')
            ->get();

        // 固定の仮ブース情報を付加
        $mstBooth->prepend(config('appconf.timetable_booth'));
        $mstBooth->push(config('appconf.transfer_booth'));

        return $mstBooth;
    }

    /**
     * カレンダー情報取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return object カレンダー情報
     */
    public function getCalendar(Request $request)
    {

        // タイムスタンプのバリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForCalendar())->validate();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $campusCd = $request->input('campus_cd');
        $this->guardRoomAdminRoomcd($campusCd);

        return $this->getRoomCalendar($request);
    }

    //==========================
    // 授業スケジュール登録
    //==========================

    /**
     * 新規登録画面
     *
     * @param string $campusCd 校舎コード
     * @param string $datetimeStr 日付時刻文字列(年月日時分)
     * @param string $boothCd ブースコード
     * @return view
     */
    public function new($campusCd, $datetimeStr, $boothCd)
    {
        // パラメータ取得・日時切り分け
        $date = substr($datetimeStr, 0, 4) . '-' . substr($datetimeStr, 4, 2) . '-' . substr($datetimeStr, 6, 2);
        $time = substr($datetimeStr, 8, 2) . ':' . substr($datetimeStr, 10, 2);

        $param = [
            'campus_cd' => $campusCd,
            'target_date' => $date,
            'start_time' => $time,
            'booth_cd' => $boothCd,
        ];

        // パラメータのバリデーション
        $this->validateFromParam($param, $this->rulesForNew($param));
        // 日付型に変換
        $targetDate = new Carbon($date);

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $this->guardRoomAdminRoomcd($campusCd);

        // 教室名を取得する
        $roomName = $this->mdlGetRoomName($campusCd);

        // ブースリストを取得
        $booths = $this->mdlGetBoothList($campusCd);

        // コースリストを取得
        $courses = $this->mdlGetCourseList();

        // 科目リストを取得
        $subjects = $this->mdlGetSubjectList();

        // 生徒リストを取得
        $students = $this->mdlGetStudentList($campusCd);

        // 講師リストを取得
        $tutors = $this->mdlGetTutorList($campusCd);

        // 日付から時間割区分を取得
        $timetableKind = $this->fncScheGetTimeTableKind($campusCd, $targetDate);

        // 指定時刻から、対応する時限の情報を取得
        // MEMO:パラメータにはカレンダーのセルの開始時刻が設定されているが、
        // 時限の終了時刻とすぐ下のセルの開始時刻が等しくなるため、1min加算して判定する
        $targetTime = date('H:i', strtotime($time . "+1 min"));
        $periodInfo = $this->fncScheGetPeriodTime($campusCd, $timetableKind, $targetTime);

        if (isset($periodInfo)) {
            $periodNo = $periodInfo->period_no;
            $startTime = $periodInfo->start_time;
            $endTime = $periodInfo->end_time;
        } else {
            $periodNo = null;
            $startTime = null;
            $endTime = null;
        }

        // 授業区分リストを取得
        $lessonKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_31);

        // 通塾種別リストを取得
        $howToKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_33);

        // 仮登録フラグリストを取得
        $tentativeStatusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_36);

        // 繰り返し回数リスト
        $times = config('appconf.repeat_times');

        // 初期表示データをセット
        $editData = [
            'campus_cd' => $campusCd,
            'name' => $roomName,
            'target_date' => $targetDate,
            'booth_cd' => $boothCd,
            'course_kind' => AppConst::CODE_MASTER_42_1,
            'period_no' => $periodNo,
            'period_no_bef' => $periodNo,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'timetable_kind' => $timetableKind,
            // スケジュール更新区分=NEW
            'kind' => AppConst::SCHEDULE_KIND_NEW
        ];

        // 登録画面表示時の校舎コード・日付をセッションに保存
        session(['session_campus_cd' => $campusCd]);
        session(['session_date' => $date]);

        return view('pages.admin.room_calendar-input', [
            'rules' => $this->rulesForInput(null),
            'booths' => $booths,
            'courses' => $courses,
            'periods' => null,
            'tutors' => $tutors,
            'students' => $students,
            'subjects' => $subjects,
            'lessonKindList' => $lessonKindList,
            'howToKindList' => $howToKindList,
            'tentativeStatusList' => $tentativeStatusList,
            'substituteKindList' => null,
            'todayabsentList' => null,
            'times' => $times,
            'editData' => $editData
        ]);
    }

    /**
     * バリデーションルールを取得(登録画面パラメータ用)
     *
     * @param array $param パラメータ
     * @return array ルール
     */
    private function rulesForNew($param)
    {
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

        // 独自バリデーション: リストのチェック ブース
        $validationBoothList =  function ($attribute, $value, $fail) use ($param) {

            // ブースリストを取得
            $booths = $this->mdlGetBoothList($param['campus_cd']);
            if (!isset($booths[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += Schedule::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += Schedule::fieldRules('target_date', ['required']);
        $rules += Schedule::fieldRules('start_time', ['required']);
        $rules += Schedule::fieldRules('booth_cd', ['required', $validationBoothList]);

        return $rules;
    }

    /**
     * 編集画面
     *
     * @param int $scheduleId スケジュールID
     * @return view
     */
    public function edit($scheduleId)
    {
        // IDのバリデーション
        $this->validateIds($scheduleId);

        // クエリを作成
        $query = Schedule::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        // データを取得
        $schedule = $query
            ->select(
                'schedule_id',
                'campus_cd',
                'room_names.room_name as name',
                'target_date',
                'period_no',
                'period_no as period_no_bef', // hiddenに退避
                'start_time',
                'end_time',
                'booth_cd',
                'schedules.course_cd',
                'mst_courses.course_kind',
                'mst_courses.name as course_name',
                'student_id',
                'schedules.tutor_id',
                'subject_cd',
                'lesson_kind',
                'how_to_kind',
                'substitute_kind',
                'substitute_kind as substitute_kind_bef', // hiddenに退避
                'absent_tutor_id',
                'tutors.name as tutor_name',
                'absent_status',
                'tentative_status',
                'schedules.memo'
            )
            // 校舎名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'room_names.code');
            })
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd');
            })
            // 欠席講師名の取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('schedules.absent_tutor_id', '=', 'tutors.tutor_id');
            })
            // IDを指定
            ->where('schedule_id', $scheduleId)
            ->firstOrFail();

        // データを取得（受講生徒情報）
        $classMembers = ClassMember::query()
            ->select(
                'student_id',
            )
            // スケジュールIDを指定
            ->where('schedule_id', $scheduleId)
            ->get();

        // 取得データを配列->カンマ区切り文字列に変換しセット
        $arrClassMembers = [];
        if (count($classMembers) > 0) {
            foreach ($classMembers as $classMember) {
                array_push($arrClassMembers, $classMember['student_id']);
            }
        }
        $schedule['class_member_id'] = implode(',', $arrClassMembers);

        $campusCd = $schedule['campus_cd'];
        $targetDate = $schedule['target_date'];

        // ブースリストを取得
        $booths = $this->mdlGetBoothList($campusCd);

        // 科目リストを取得
        $subjects = $this->mdlGetSubjectList();

        // 生徒リストを取得
        $students = $this->mdlGetStudentList($campusCd);

        // 講師リストを取得
        $tutors = $this->mdlGetTutorList($campusCd);

        // 時限リストを取得（校舎・日付から）
        $periods = $this->mdlGetPeriodListByDate($campusCd, $targetDate);

        // 授業区分リストを取得
        $lessonKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_31);

        // 通塾種別リストを取得
        $howToKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_33);

        // 仮登録フラグリストを取得
        $tentativeStatusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_36);

        // 授業代講種別リストを取得
        $substituteKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_34);

        // 出欠ステータスリストを取得
        $todayabsentList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_35, [AppConst::CODE_MASTER_35_SUB_0, AppConst::CODE_MASTER_35_SUB_1, AppConst::CODE_MASTER_35_SUB_3]);

        // 日付から時間割区分を取得
        $timetableKind = $this->fncScheGetTimeTableKind($campusCd, $targetDate);
        $schedule['timetable_kind'] = $timetableKind;
        // スケジュール更新区分=UPDATE
        $schedule['kind'] = AppConst::SCHEDULE_KIND_UPD;

        // 編集画面表示時の校舎コード・日付をセッションに保存
        session(['session_campus_cd' => $campusCd]);
        session(['session_date' => $targetDate->format('Y-m-d')]);

        return view('pages.admin.room_calendar-input', [
            'rules' => $this->rulesForInput(null),
            'booths' => $booths,
            'courses' => null,
            'periods' => $periods,
            'tutors' => $tutors,
            'students' => $students,
            'subjects' => $subjects,
            'lessonKindList' => $lessonKindList,
            'howToKindList' => $howToKindList,
            'tentativeStatusList' => $tentativeStatusList,
            'substituteKindList' => $substituteKindList,
            'todayabsentList' => $todayabsentList,
            'editData' => $schedule
        ]);
    }

    /**
     * コピー登録画面
     *
     * @param int $scheduleId スケジュールID
     * @return view
     */
    public function copy($scheduleId)
    {
        // IDのバリデーション
        $this->validateIds($scheduleId);

        // クエリを作成
        $query = Schedule::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        // データを取得
        $schedule = $query
            ->select(
                'schedule_id',
                'campus_cd',
                'room_names.room_name as name',
                'target_date',
                'period_no',
                'period_no as period_no_bef',
                'start_time',
                'end_time',
                'booth_cd',
                'schedules.course_cd',
                'mst_courses.course_kind',
                'mst_courses.name as course_name',
                'student_id',
                'tutor_id',
                'subject_cd',
                'create_kind',
                'lesson_kind',
                'how_to_kind',
                'tentative_status',
                'memo'
            )
            // 校舎名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'room_names.code');
            })
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd');
            })
            // IDを指定
            ->where('schedule_id', $scheduleId)
            ->firstOrFail();

        // データを取得（受講生徒情報）
        $classMembers = ClassMember::query()
            ->select(
                'student_id',
            )
            // スケジュールIDを指定
            ->where('schedule_id', $scheduleId)
            ->get();

        // 受講生徒情報を配列->カンマ区切り文字列に変換しセット
        $arrClassMembers = [];
        if (count($classMembers) > 0) {
            foreach ($classMembers as $classMember) {
                array_push($arrClassMembers, $classMember['student_id']);
            }
        }
        $schedule['class_member_id'] = implode(',', $arrClassMembers);

        $campusCd = $schedule['campus_cd'];
        $targetDate = $schedule['target_date'];

        // ブースリストを取得
        $booths = $this->mdlGetBoothList($campusCd);

        // 科目リストを取得
        $subjects = $this->mdlGetSubjectList();

        // 生徒リストを取得
        $students = $this->mdlGetStudentList($campusCd);

        // 講師リストを取得
        $tutors = $this->mdlGetTutorList($campusCd);

        // 時限リストを取得（校舎・日付から）
        $periods = $this->mdlGetPeriodListByDate($campusCd, $targetDate);

        // 授業区分リストを取得
        $lessonKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_31);

        // 通塾種別リストを取得
        $howToKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_33);

        // 仮登録フラグリストを取得
        $tentativeStatusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_36);

        // 日付から時間割区分を取得
        $timetableKind = $this->fncScheGetTimeTableKind($campusCd, $targetDate);
        $schedule['timetable_kind'] = $timetableKind;
        // スケジュール更新区分=COPY
        $schedule['kind'] = AppConst::SCHEDULE_KIND_CPY;

        // コピー登録画面表示時の校舎コード・日付をセッションに保存
        session(['session_campus_cd' => $campusCd]);
        session(['session_date' => $targetDate->format('Y-m-d')]);

        return view('pages.admin.room_calendar-input', [
            'rules' => $this->rulesForInput(null),
            'booths' => $booths,
            'courses' => null,
            'periods' => $periods,
            'tutors' => $tutors,
            'students' => $students,
            'subjects' => $subjects,
            'lessonKindList' => $lessonKindList,
            'howToKindList' => $howToKindList,
            'tentativeStatusList' => $tentativeStatusList,
            'substituteKindList' => null,
            'todayabsentList' => null,
            'editData' => $schedule
        ]);
    }

    /**
     * コース情報取得（コースプルダウン選択）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array コース情報
     */
    public function getDataSelectCourse(Request $request)
    {
        // コースコードは文字列型なのでidチェックはしない

        // コースコードを取得
        $courseCd = $request->input('id');

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------

        // コースプルダウンを取得
        $courses = $this->mdlGetCourseList();

        // [ガード] コースコードがプルダウンの中にあるかチェック
        $this->guardListValue($courses, $courseCd);

        //---------------------------
        // コース情報（コース種別）を返却する
        //---------------------------
        // コース情報を取得
        $course = $this->fncScheGetCourseInfo($courseCd);

        return [
            'course_kind' => $course->course_kind
        ];
    }

    /**
     * 時限情報取得（日付ピッカー変更）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 時限情報
     */
    public function getDataSelect(Request $request)
    {
        // 日付のバリデーション
        if ($request->input('target_date')) {
            $this->validateDatesFromRequest($request, 'target_date');
        }

        // 校舎コード・日付を取得
        $campusCd = $request->input('campus_cd');
        $targetDate = $request->input('target_date');

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $this->guardRoomAdminRoomcd($campusCd);

        //---------------------------
        // 時限リストを返却する
        //---------------------------
        // 時限リストを取得（校舎・日付から）
        $periods = $this->mdlGetPeriodListByDate($campusCd, $targetDate);
        // 日付から時間割区分を取得
        $timetableKind = null;
        if (count($periods) > 0) {
            $timetableKind = $this->fncScheGetTimeTableKind($campusCd, $targetDate);
        }

        return [
            'selectItems' => $this->objToArray($periods),
            'timetable_kind' => $timetableKind
        ];
    }

    /**
     * 時間割情報取得（時限プルダウン選択時）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 時間割情報
     */
    public function getDataSelectTimetable(Request $request)
    {
        // 時限のバリデーション
        $this->validateIdsFromRequest($request, 'period_no');
        // 日付のバリデーション
        $this->validateDatesFromRequest($request, 'target_date');

        // 校舎コード・日付・時限を取得
        $campusCd = $request->input('campus_cd');
        $targetDate = $request->input('target_date');
        $periodNo = $request->input('period_no');

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $this->guardRoomAdminRoomcd($campusCd);

        // 時限リストを取得
        $periods = $this->mdlGetPeriodListByDate($campusCd, $targetDate);

        // [ガード] 時限がプルダウンの中にあるかチェック
        $this->guardListValue($periods, $periodNo);

        //---------------------------
        // 時間割情報（開始時刻・終了時刻）を返却する
        //---------------------------

        // 日付から時間割区分を取得
        $timetableKind = $this->fncScheGetTimeTableKind($campusCd, $targetDate);

        // 時間割区分・指定時限から、対応する時間割情報を取得
        $periodInfo = $this->fncScheGetTimetableByPeriod($campusCd, $timetableKind, $periodNo);
        $startTime = $periodInfo->start_time;
        $endTime = $periodInfo->end_time;

        return [
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'timetable_kind' => $timetableKind
        ];
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
        // 登録前バリデーション（関連チェック）。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInputRelated($request))->validate();

        if ($request['kind'] == AppConst::SCHEDULE_KIND_NEW) {
            // 新規登録処理
            $this->createNew($request);
        } else {
            // コピー登録処理
            $this->createCopy($request);
        }
        return;
    }

    /**
     * 新規登録処理（create()から呼ばれる）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    private function createNew(Request $request)
    {
        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            $form = $request->only(
                'campus_cd',
                'target_date',
                'period_no',
                'start_time',
                'end_time',
                'booth_cd',
                'course_cd',
                'course_kind',
                'student_id',
                'class_member_id',
                'tutor_id',
                'subject_cd',
                'lesson_kind',
                'how_to_kind',
                'tentative_status',
                'memo',
                'repeat_chk',
                'repeat_times',
            );

            // 繰り返し登録時の設定
            $targetDates = [];
            if ($form['course_kind'] == AppConst::CODE_MASTER_42_3 || $form['repeat_chk'] != 'true') {
                // 繰り返し登録なしの場合
                array_push($targetDates, $form['target_date']);
            } else {
                // 繰り返し登録ありの場合
                $repeatTimes = intval($form['repeat_times']) + 1;
                // 対象日と同一曜日の授業日リストを取得
                $targetDates = $this->fncScheGetScheduleDate($form['campus_cd'], $form['target_date'], $repeatTimes, null);
            }

            foreach ($targetDates as $targetDate) {
                // ブースのチェック・空きブース取得
                if ($form['course_kind'] == AppConst::CODE_MASTER_42_3) {
                    // コースが面談の場合
                    $booth = $this->fncScheSearchBoothForConference(
                        $form['campus_cd'],
                        $form['booth_cd'],
                        $targetDate,
                        $form['start_time'],
                        $form['end_time'],
                        null,
                        false
                    );
                } else {
                    // コースが面談以外の場合
                    $booth = $this->fncScheSearchBooth(
                        $form['campus_cd'],
                        $form['booth_cd'],
                        $targetDate,
                        $form['period_no'],
                        $form['how_to_kind'],
                        null,
                        false
                    );
                }
                if (!$booth) {
                    // 空きなし時は不正な値としてエラーレスポンスを返却（事前にバリデーションを行っているため）
                    $this->illegalResponseErr();
                }

                // スケジュール情報登録
                $this->fncScheCreateSchedule($form, $targetDate, $booth, AppConst::CODE_MASTER_32_1);
            }
        });
        return;
    }

    /**
     * コピー登録処理（create()から呼ばれる）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    private function createCopy(Request $request)
    {
        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            $form = $request->only(
                'schedule_id',
                'campus_cd',
                'target_date',
                'period_no',
                'start_time',
                'end_time',
                'booth_cd',
                'course_cd',
                'course_kind',
                'student_id',
                'class_member_id',
                'tutor_id',
                'subject_cd',
                //'create_kind',
                'lesson_kind',
                'how_to_kind',
                'tentative_status',
                'memo',
            );

            // ブースのチェック・空きブース取得
            if ($form['course_kind'] == AppConst::CODE_MASTER_42_3) {
                // コースが面談の場合
                $booth = $this->fncScheSearchBoothForConference(
                    $form['campus_cd'],
                    $form['booth_cd'],
                    $form['target_date'],
                    $form['start_time'],
                    $form['end_time'],
                    null,
                    false
                );
            } else {
                // コースが面談以外の場合
                $booth = $this->fncScheSearchBooth(
                    $form['campus_cd'],
                    $form['booth_cd'],
                    $form['target_date'],
                    $form['period_no'],
                    $form['how_to_kind'],
                    null,
                    false
                );
            }
            if (!$booth) {
                // エラー時。エラー時は不正な値としてエラーレスポンスを返却
                $this->illegalResponseErr();
            }

            // スケジュール情報登録
            $this->fncScheCreateSchedule($form, $form['target_date'], $booth, AppConst::CODE_MASTER_32_1);
        });

        return;
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
        // 登録前バリデーション（関連チェック）。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInputRelated($request))->validate();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            $form = $request->only(
                'schedule_id',
                'campus_cd',
                'target_date',
                'period_no',
                'start_time',
                'end_time',
                'booth_cd',
                'course_cd',
                'course_kind',
                'student_id',
                'class_member_id',
                'tutor_id',
                'subject_cd',
                'lesson_kind',
                'how_to_kind',
                'substitute_kind',
                'substitute_kind_bef',
                'substitute_tid',
                'absent_tutor_id',
                'absent_status',
                'tentative_status',
                'memo',
            );

            // 時間（分）の算出
            $start = Carbon::createFromTimeString($form['start_time']);
            $end = Carbon::createFromTimeString($form['end_time']);
            $minutes = $start->diffInMinutes($end);

            // 代講の有無により、講師ID・欠席講師IDの設定
            if ($form['substitute_kind_bef'] == AppConst::CODE_MASTER_34_0) {
                if ($form['substitute_kind'] == AppConst::CODE_MASTER_34_0) {
                    // 代講なし -> 代講なし
                    $tutorId = $form['tutor_id'];
                    $absentTutorId = "";
                } else {
                    // 代講なし -> 代講あり（代講講師を講師に、講師を欠席講師に）
                    $tutorId = $form['substitute_tid'];
                    $absentTutorId = $form['tutor_id'];
                }
            } else {
                if ($form['substitute_kind'] == AppConst::CODE_MASTER_34_0) {
                    // 代講あり -> 代講なし（欠席講師を戻す）
                    $tutorId = $form['absent_tutor_id'];
                    $absentTutorId = "";
                } else {
                    // 代講あり -> 代講あり
                    $tutorId = $form['tutor_id'];
                    $absentTutorId = $form['absent_tutor_id'];
                }
            }

            // スケジュール情報更新（UPDATE）
            // 対象データを取得(IDでユニークに取る)
            $schedule = Schedule::where('schedule_id', $form['schedule_id'])
                // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            $schedule->target_date = $form['target_date'];
            if ($form['course_kind'] != AppConst::CODE_MASTER_42_3) {
                // 時限は面談以外の場合のみ設定
                $schedule->period_no = $form['period_no'];
            }
            $schedule->start_time = $form['start_time'];
            $schedule->end_time = $form['end_time'];
            $schedule->minutes = $minutes;
            $schedule->booth_cd = $form['booth_cd'];
            $schedule->student_id = $form['student_id'];
            $schedule->tutor_id = $tutorId;
            $schedule->subject_cd = $form['subject_cd'];
            if ($form['course_kind'] == AppConst::CODE_MASTER_42_1 || $form['course_kind'] == AppConst::CODE_MASTER_42_2) {
                // 授業区分は１対１授業・１対多授業のみ設定
                $schedule->lesson_kind = $form['lesson_kind'];
            }
            $schedule->how_to_kind = $form['how_to_kind'];
            $schedule->substitute_kind = $form['substitute_kind'];
            $schedule->absent_tutor_id = $absentTutorId;
            $schedule->absent_status = $form['absent_status'];
            if ($form['lesson_kind'] == AppConst::CODE_MASTER_31_2) {
                // 仮登録フラグは特別期間講習の場合のみ設定
                $schedule->tentative_status = $form['tentative_status'];
            } else {
                $schedule->tentative_status = AppConst::CODE_MASTER_36_0;
            }
            $schedule->memo = $form['memo'];
            // 更新
            $schedule->save();

            // 受講生徒情報更新（コース種別が授業複の場合のみ）
            if ($form['course_kind'] == AppConst::CODE_MASTER_42_2) {

                // 既存データを取得
                $classMembers = ClassMember::query()
                    ->select(
                        'student_id',
                    )
                    // スケジュールIDを指定
                    ->where('schedule_id', $form['schedule_id'])
                    ->get();

                // 変更前の生徒リスト（テーブルから取得）
                $arrMembersBef = [];
                if (count($classMembers) > 0) {
                    foreach ($classMembers as $classMember) {
                        array_push($arrMembersBef, $classMember['student_id']);
                    }
                }

                // 変更後の生徒リスト（requestから取得）
                // カンマ区切りで入ってくるので配列に格納
                $arrMembersAft = explode(",", $form['class_member_id']);

                // 受講生徒情報削除処理
                // 変更前の生徒 - 変更後の生徒 で差分を取得
                $arrMembersDel = array_diff($arrMembersBef, $arrMembersAft);

                if (count($arrMembersDel) > 0) {
                    // 対象データを削除
                    ClassMember::where('schedule_id', $form['schedule_id'])
                        // 削除対象の生徒リストを指定
                        ->whereIn('student_id', $arrMembersDel)
                        // 物理削除
                        ->forceDelete();
                }

                // 受講生徒情報追加処理
                // 変更後の生徒 - 変更前の生徒 で差分を取得
                $arrMembersAdd = array_diff($arrMembersAft, $arrMembersBef);

                foreach ($arrMembersAdd as $member) {
                    // class_membersテーブルへのinsert
                    $classmember = new ClassMember;
                    $classmember->schedule_id = $form['schedule_id'];
                    $classmember->student_id = $member;
                    $classmember->absent_status = AppConst::CODE_MASTER_35_0;
                    // 登録
                    $classmember->save();
                }
            }
        });
        return;
    }

    /**
     * 編集処理（欠席登録）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function updateAbsent(Request $request)
    {
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInputAbsent($request))->validate();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            // 対象データを取得(IDでユニークに取る)
            Schedule::select(
                'schedule_id',
            )
                ->where('schedule_id', $request['schedule_id'])
                // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            // 受講生徒情報更新
            // 件数取得
            $count = intval($request['studentCnt']);

            for ($i = 0; $i < $count; $i++) {

                // 対象データを取得(IDでユニークに取る)
                $classMember = ClassMember::query()
                    // 受講生徒情報IDを指定
                    ->where('class_member_id', $request['class_member_id_' . $i])
                    // スケジュールIDを指定
                    ->where('schedule_id', $request['schedule_id'])
                    // 生徒IDを指定
                    ->where('student_id', $request['student_id_' . $i])
                    // 該当データがない場合はエラーを返す
                    ->firstOrFail();

                // 更新
                $classMember->absent_status = $request['absent_status_' . $i];
                $classMember->save();
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
    public function validationForInput(Request $request)
    {
        // リクエストデータチェック（項目チェック）
        $validator = Validator::make($request->all(), $this->rulesForInput($request));
        if (count($validator->errors()) != 0) {
            // 項目チェックエラーがある場合はここでエラー情報を返す
            return $validator->errors();
        }
        // リクエストデータチェック（関連チェック追加）
        $validatorRerated = Validator::make($request->all(), $this->rulesForInputRelated($request));
        // 項目チェックエラー無し時は関連チェックを行い、結果を返す
        return $validatorRerated->errors();
    }

    /**
     * バリデーションルールを取得(登録用・項目チェック)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
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

        // 独自バリデーション: リストのチェック コース
        $validationCourseList =  function ($attribute, $value, $fail) {
            // コースリストを取得
            $list = $this->mdlGetCourseList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ブース
        $validationBoothList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('campus_cd')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            // ブースリストを取得
            $list = $this->mdlGetBoothList($request['campus_cd']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 時限
        $validationPeriodList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('course_kind')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            if ($request['course_kind'] == AppConst::CODE_MASTER_42_3) {
                // コースが面談の場合はチェックしない
                return;
            }
            // 時限リストを取得
            $list = $this->mdlGetPeriodListByDate($request['campus_cd'], $request['target_date']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 講師
        $validationTutorList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('campus_cd')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            // 講師リストを取得
            $list = $this->mdlGetTutorList($request['campus_cd']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒
        $validationStudentList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('campus_cd')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            // 生徒リストを取得
            $list = $this->mdlGetStudentList($request['campus_cd']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒（複数選択）
        $validationStudentListMulti =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('campus_cd')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            $list = $this->mdlGetStudentList($request['campus_cd']);
            // カンマ区切りで入ってくるので分割してチェック
            foreach (explode(",", $value) as $member) {
                // 生徒リストを取得
                if (!isset($list[$member])) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 独自バリデーション: リストのチェック 科目
        $validationSubjectList =  function ($attribute, $value, $fail) {

            // 科目リストを取得
            $list = $this->mdlGetSubjectList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 授業区分
        $validationLessonKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_31);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 通塾種別
        $validationHowToKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_33);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 仮登録フラグ
        $validationTentativeStatusList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_36);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 授業代講区分
        $validationSubstituteKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_34);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 出欠ステータス
        $validationAbsentStatusList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_35);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 繰り返し回数
        $validationRepeatTimes =  function ($attribute, $value, $fail) use ($request) {

            if ($request['repeat_chk'] != "true") {
                // 繰り返し登録が選択されていない場合はチェックしない
                return;
            }
            // リストを取得し存在チェック
            $list = config('appconf.repeat_times');
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += Schedule::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += Schedule::fieldRules('course_cd', ['required', $validationCourseList]);
        $rules += ['course_kind' => ['required']];
        $rules += Schedule::fieldRules('target_date', ['required']);
        $rules += Schedule::fieldRules('start_time', ['required']);
        $rules += Schedule::fieldRules('end_time', ['required']);
        $rules += Schedule::fieldRules('memo');
        $rules += Schedule::fieldRules('booth_cd', ['required', $validationBoothList]);

        if ($request && $request->filled('course_kind') && $request['course_kind'] != AppConst::CODE_MASTER_42_3) {
            // コース種別が面談以外の場合のみチェック
            $rules += Schedule::fieldRules('period_no', ['required', $validationPeriodList]);
        }
        if (
            $request && $request->filled('course_kind') &&
            ($request['course_kind'] == AppConst::CODE_MASTER_42_1 || $request['course_kind'] == AppConst::CODE_MASTER_42_2)
        ) {
            // コース種別が授業の場合のみチェック
            $rules += Schedule::fieldRules('lesson_kind', ['required', $validationLessonKindList]);
            $rules += Schedule::fieldRules('how_to_kind', ['required', $validationHowToKindList]);
            $rules += Schedule::fieldRules('tutor_id', ['required', $validationTutorList]);

            if ($request->filled('course_kind') && $request['lesson_kind'] == AppConst::CODE_MASTER_31_2) {
                // 特別期間講習の場合のみ、仮登録フラグをチェック
                $rules += Schedule::fieldRules('tentative_status', ['required', $validationTentativeStatusList]);
            }
        }
        if (
            $request && $request->filled('course_kind') &&
            ($request['course_kind'] == AppConst::CODE_MASTER_42_1 || $request['course_kind'] == AppConst::CODE_MASTER_42_2)
        ) {
            // コース種別が授業の場合、教科をチェック（必須あり）
            $rules += Schedule::fieldRules('subject_cd', ['required', $validationSubjectList]);
        } else if ($request && $request->filled('course_kind') && $request['course_kind'] == AppConst::CODE_MASTER_42_4) {
            // コース種別が自習の場合、教科をチェック（必須なし）
            $rules += Schedule::fieldRules('subject_cd', [$validationSubjectList]);
        }
        if ($request && $request->filled('course_kind') && $request['course_kind'] == AppConst::CODE_MASTER_42_1) {
            // コース種別が授業単の場合、生徒（単数指定）をチェック（必須あり）
            $rules += Schedule::fieldRules('student_id', ['required', $validationStudentList]);
        } else if ($request && $request->filled('course_kind') && $request['course_kind'] == AppConst::CODE_MASTER_42_2) {
            // コース種別が授業複の場合、受講生徒（複数指定）をチェック
            $rules += ['class_member_id' => ['required', $validationStudentListMulti]];
        } else {
            // コース種別が面談・自習の場合、生徒（単数指定）をチェック（必須なし）
            $rules += Schedule::fieldRules('student_id', [$validationStudentList]);
        }
        if ($request && $request->filled('kind') && $request['kind'] == AppConst::SCHEDULE_KIND_NEW) {
            // 新規登録の場合
            if ($request && $request->filled('repeat_chk') && $request['repeat_chk'] == "true") {
                // 繰り返し登録有りの場合、繰り返し回数のチェック
                $rules += ['repeat_times' => [$validationRepeatTimes]];
            }
        }
        if ($request && $request->filled('kind') && $request['kind'] == AppConst::SCHEDULE_KIND_UPD) {
            // 更新登録の場合
            $rules += Schedule::fieldRules('substitute_kind', ['required', $validationSubstituteKindList]);
            if (
                $request && $request->filled('substitute_kind')
                && $request['substitute_kind'] != AppConst::CODE_MASTER_34_0
            ) {
                // 代講種別が「なし」以外の場合
                $rules += ['substitute_tid' => ['required_without:absent_tutor_id', 'different:tutor_id', $validationTutorList]];
            }
            $rules += Schedule::fieldRules('absent_status', ['required', $validationAbsentStatusList]);
        }
        return $rules;
    }

    /**
     * バリデーションルールを取得(登録用・関連チェック)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInputRelated(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: ブース重複チェック（面談以外）
        $validationDupBooth =  function ($attribute, $value, $fail) use ($request) {

            if (
                !$request->filled('campus_cd') || !$request->filled('booth_cd')
                || !$request->filled('target_date') || !$request->filled('period_no')
                || !$request->filled('how_to_kind')
            ) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }

            $scheduleId = null;
            if ($request['kind'] == AppConst::SCHEDULE_KIND_UPD && $request->filled('schedule_id')) {
                // 更新の場合のみ、スケジュールIDをセット（除外用）
                $scheduleId = $request['schedule_id'];
            }
            if ($request['kind'] == AppConst::SCHEDULE_KIND_CPY) {
                // コピー登録の場合、空きブース検索ありとする
                $checkOnly = false;
            } else {
                // 新規登録・更新の場合、空きブース検索なしとする
                $checkOnly = true;
            }
            // ブース重複チェック（空きブース検索なし）
            $booth = $this->fncScheSearchBooth(
                $request['campus_cd'],
                $request['booth_cd'],
                $request['target_date'],
                $request['period_no'],
                $request['how_to_kind'],
                $scheduleId,
                $checkOnly
            );
            if (!$booth) {
                // ブース空きなしエラー
                return $fail(Lang::get('validation.duplicate_booth'));
            }

            // 繰り返し登録有無チェック
            if (!$request->filled('repeat_chk') || !$request->filled('repeat_times')) {
                // 繰り返し登録対象外時はチェックしない
                return;
            }
            if ($request['repeat_chk'] != 'true' || intval($request['repeat_times']) == 0) {
                // 繰り返し登録対象外時はチェックしない
                return;
            }
            // 繰り返し登録有りの場合
            $targetDates = [];
            $repeatTimes = intval($request['repeat_times']) + 1;
            // 対象日と同一曜日の授業日を取得
            $targetDates = $this->fncScheGetScheduleDate($request['campus_cd'], $request['target_date'], $repeatTimes, null);
            // チェック済みの日付を除外
            $targetDatesAfter = array_diff($targetDates, [$request['target_date']]);

            foreach ($targetDatesAfter as $targetDate) {
                // ブース重複チェック（空きブース検索あり）
                $booth = $this->fncScheSearchBooth(
                    $request['campus_cd'],
                    $request['booth_cd'],
                    $targetDate,
                    $request['period_no'],
                    $request['how_to_kind'],
                    $scheduleId,
                    false
                );
                if (!$booth) {
                    // ブース空きなしエラー
                    $validateMsg = Lang::get('validation.duplicate_booth');
                    if ($targetDate != $request['target_date']) {
                        // 繰り返し登録データの場合、対象日も合わせて表示する
                        $validateMsg = $validateMsg . "(" . $targetDate . ")";
                    }
                    return $fail($validateMsg);
                }
            }
        };

        // 独自バリデーション: ブース重複チェック（面談）
        $validationDupBoothConference =  function ($attribute, $value, $fail) use ($request) {

            if (
                !$request->filled('campus_cd') || !$request->filled('booth_cd')
                || !$request->filled('target_date') || !$request->filled('start_time')
                || !$request->filled('end_time')
            ) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }

            $scheduleId = null;
            if ($request['kind'] == AppConst::SCHEDULE_KIND_UPD && $request->filled('schedule_id')) {
                // 更新の場合のみ、スケジュールIDをセット（除外用）
                $scheduleId = $request['schedule_id'];
            }
            if ($request['kind'] == AppConst::SCHEDULE_KIND_CPY) {
                // コピー登録の場合、空きブース検索ありとする
                $checkOnly = false;
            } else {
                // 新規登録・更新の場合、空きブース検索なしとする
                $checkOnly = true;
            }
            // ブースの重複チェック
            $booth = $this->fncScheSearchBoothForConference(
                $request['campus_cd'],
                $request['booth_cd'],
                $request['target_date'],
                $request['start_time'],
                $request['end_time'],
                $scheduleId,
                $checkOnly
            );
            if (!$booth) {
                // ブース空きなしエラー
                return $fail(Lang::get('validation.duplicate_booth'));
            }
        };

        // 独自バリデーション: 時限と開始時刻の相関チェック
        $validationPeriodStartTime =  function ($attribute, $value, $fail) use ($request) {
            return $this->fncScheValidatePeriodStartTime($request, $attribute, $value, $fail);
        };

        // 独自バリデーション: 面談開始時刻のチェック
        $validationConferenceStartTime =  function ($attribute, $value, $fail) use ($request) {
            return $this->fncScheValidateConferenceStartTime($request, $attribute, $value, $fail);
        };

        // 独自バリデーション: 生徒スケジュール重複チェック
        $validationDupStudent =  function ($attribute, $value, $fail) use ($request) {
            $kind = $request['kind'];
            return $this->fncScheValidateStudent($request, $kind, $attribute, $value, $fail);
        };

        // 独自バリデーション: 生徒スケジュール重複チェック（複数指定）
        $validationDupStudentMulti =  function ($attribute, $value, $fail) use ($request) {
            $kind = $request['kind'];
            return $this->fncScheValidateStudent($request, $kind, $attribute, $value, $fail);
        };

        // 独自バリデーション: 講師スケジュール重複チェック
        $validationDupTutor =  function ($attribute, $value, $fail) use ($request) {
            $kind = $request['kind'];
            return $this->fncScheValidateTutor($request, $kind, $attribute, $value, $fail);
        };

        // 独自バリデーション: 授業区分（見込客）
        $validationLessonKindTrial =  function ($attribute, $value, $fail) use ($request) {
            return $this->fncScheValidateLessonKindTrial($request, $attribute, $value, $fail);
        };

        // 関連チェックは項目チェックと分けて行う
        // ブース重複チェック
        if ($request->filled('course_kind') && $request['course_kind'] == AppConst::CODE_MASTER_42_3) {
            // コース種別が面談の場合
            $rules += ['booth_cd' => [$validationDupBoothConference]];
        } else if ($request->filled('course_kind') && $request['course_kind'] != AppConst::CODE_MASTER_42_3) {
            // コース種別が面談以外の場合
            $rules += ['booth_cd' => [$validationDupBooth]];
        }
        // 時限と開始時刻の相関チェック
        if ($request->filled('course_kind') && $request['course_kind'] != AppConst::CODE_MASTER_42_3) {
            // コース種別が面談以外の場合のみチェック
            $rules += ['start_time' => [$validationPeriodStartTime]];
        }
        // 面談開始時刻チェック
        if ($request->filled('course_kind') && $request['course_kind'] == AppConst::CODE_MASTER_42_3) {
            // コース種別が面談の場合のみチェック
            $rules += ['start_time' => [$validationConferenceStartTime]];
        }
        // 授業区分（見込客）チェック
        if (
            $request->filled('course_kind') &&
            $request['course_kind'] != AppConst::CODE_MASTER_42_3 && $request['course_kind'] != AppConst::CODE_MASTER_42_4
        ) {
            // コース種別が面談・自習以外の場合のみチェック
            $rules += ['lesson_kind' => [$validationLessonKindTrial]];
        }
        // 講師のスケジュール重複チェック
        if (
            $request->filled('course_kind') &&
            $request['course_kind'] != AppConst::CODE_MASTER_42_3 && $request['course_kind'] != AppConst::CODE_MASTER_42_4
        ) {
            // コース種別が面談・自習以外の場合のみチェック
            $rules += ['tutor_id' => [$validationDupTutor]];
        }
        // 代講講師のスケジュール重複チェック
        if ($request && $request->filled('substitute_tid')) {
            // 代講講師が選択されている場合
            $rules += ['substitute_tid' => [$validationDupTutor]];
        }
        // 生徒のスケジュール重複チェック
        if ($request->filled('course_kind') && $request['course_kind'] != AppConst::CODE_MASTER_42_2) {
            // コース種別が授業複以外の場合のみ、生徒（単数指定）をチェック
            $rules += ['student_id' => [$validationDupStudent]];
        }
        if ($request->filled('course_kind') && $request['course_kind'] == AppConst::CODE_MASTER_42_2) {
            // コース種別が授業複の場合のみ、受講生徒（複数指定）をチェック
            $rules += ['class_member_id' => [$validationDupStudentMulti]];
        }

        return $rules;
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
        $this->validateIdsFromRequest($request, 'schedule_id');

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            // Formを取得
            $form = $request->only(
                'schedule_id',
                'course_kind'
            );

            // 対象データを取得(IDでユニークに取る)
            $schedule = Schedule::where('schedule_id', $form['schedule_id'])
                // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            // 受講生徒情報削除（コース種別が授業複の場合のみ）
            if ($form['course_kind'] == AppConst::CODE_MASTER_42_2) {
                // 削除
                ClassMember::where('schedule_id', $form['schedule_id'])
                    ->delete();
            }

            // スケジュール情報削除
            $schedule->delete();
        });

        return;
    }

    //==========================
    // 授業欠席登録（１対他授業用）
    //==========================

    /**
     * 欠席登録画面
     *
     * @param int $scheduleId スケジュールID
     * @return view
     */
    public function absent($scheduleId)
    {

        // IDのバリデーション
        $this->validateIds($scheduleId);

        // クエリを作成
        $query = Schedule::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        // データを取得
        $schedule = $query
            ->select(
                'schedules.schedule_id',
                'schedules.campus_cd',
                'room_names.room_name as campus_name',
                'schedules.target_date',
                'schedules.period_no',
                'mst_booths.name as booth_name',
                'mst_courses.name as course_name',
                'schedules.tutor_id',
                'tutors.name as tutor_name',
            )
            // 校舎名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'room_names.code');
            })
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd');
            })
            // ブース名の取得
            ->sdLeftJoin(MstBooth::class, function ($join) {
                $join->on('schedules.campus_cd', 'mst_booths.campus_cd');
                $join->on('schedules.booth_cd', 'mst_booths.booth_cd');
            })
            // 講師名の取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('schedules.tutor_id', 'tutors.tutor_id');
            })
            // IDを指定
            ->where('schedule_id', $scheduleId)
            // コース種別のガードを掛ける（授業複のみ）
            ->where('mst_courses.course_kind', AppConst::CODE_MASTER_42_2)
            ->firstOrFail();

        // データを取得（受講生徒情報）
        $classMembers = ClassMember::query()
            ->select(
                'class_members.class_member_id',
                'class_members.student_id',
                'class_members.absent_status',
                'students.name as student_name',
                'students.name_kana'
            )
            // 生徒名の取得
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('class_members.student_id', 'students.student_id');
            })
            // スケジュールIDを指定
            ->where('schedule_id', $scheduleId)
            ->orderBy('name_kana')
            ->get();

        // 受講生徒件数を取得
        $schedule['studentCnt'] = $classMembers->count();

        // 画面表示用にキー名を変更
        for ($i = 0; $i < $schedule['studentCnt']; $i++) {
            $classMembers[$i]['class_member_id_' . $i] = $classMembers[$i]['class_member_id'];
            $classMembers[$i]['student_id_' . $i] = $classMembers[$i]['student_id'];
            $classMembers[$i]['absent_status_' . $i] = $classMembers[$i]['absent_status'];
            $classMembers[$i]['student_name_' . $i] = $classMembers[$i]['student_name'];
            unset($classMembers[$i]['class_member_id']);
            unset($classMembers[$i]['student_id']);
            unset($classMembers[$i]['absent_status']);
            unset($classMembers[$i]['student_name']);
        }

        // 出欠ステータスリストを取得（１対多）
        $todayabsentList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_35, [AppConst::CODE_MASTER_35_SUB_0, AppConst::CODE_MASTER_35_SUB_2]);

        // 欠席登録画面表示時の校舎コード・日付をセッションに保存
        session(['session_campus_cd' => $schedule['campus_cd']]);
        session(['session_date' => $schedule['target_date']->format('Y-m-d')]);

        return view('pages.admin.room_calendar-absent', [
            'schedule' => $schedule,
            'classMembers' => $classMembers,
            'todayabsentList' => $todayabsentList,
            'rules' => $this->rulesForInputAbsent(null)
        ]);
    }

    /**
     * バリデーション(欠席登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInputAbsent(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInputAbsent($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(欠席登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInputAbsent(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 生徒ID
        $validationStudentList =  function ($attribute, $value, $fail) {

            // 生徒リストを取得
            $list = $this->mdlGetStudentList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 出欠ステータス
        $validationAbsentStatusList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_35);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += Schedule::fieldRules('schedule_id', ['required']);
        $rules += ['studentCnt' => ['required', 'integer']];
        $ruleClassMemberId = ClassMember::getFieldRule('class_member_id');
        $ruleStudentId = ClassMember::getFieldRule('student_id');
        $ruleAbsentStatus = ClassMember::getFieldRule('absent_status');
        if ($request) {
            $count = intval($request['studentCnt']);
            for ($i = 0; $i < $count; $i++) {
                // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
                // その他を第二引数で指定する
                // 受講生徒情報ID
                if ($request->filled('class_member_id_' . $i)) {
                    $rules += ['class_member_id_' . $i => array_merge($ruleClassMemberId, ['required'])];
                }
                // 生徒ID
                if ($request->filled('student_id_' . $i)) {
                    $rules += ['student_id_' . $i => array_merge($ruleStudentId, ['required', $validationStudentList])];
                }
                // 出欠ステータス
                if ($request->filled('absent_status_' . $i)) {
                    $rules += ['absent_status_' . $i =>  array_merge($ruleAbsentStatus, ['required', $validationAbsentStatusList])];
                }
            }
        }
        return $rules;
    }
}
