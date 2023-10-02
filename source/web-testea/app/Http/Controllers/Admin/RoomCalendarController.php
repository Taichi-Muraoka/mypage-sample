<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
//use App\Libs\AuthEx;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;
use App\Models\ClassMember;
//use App\Models\YearlySchedule;
//use App\Models\MstTimetable;
use App\Models\Tutor;
use App\Models\MstCourse;
use App\Models\CodeMaster;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use App\Http\Controllers\Traits\FuncScheduleTrait;
use Illuminate\Support\Facades\Lang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
     * カレンダー
     *
     * @param int $sid 生徒Id
     * @return view
     */
    public function calendar()
    {

        //        // IDのバリデーション
        //        $this->validateIds($roomcd);

        // 教室リストを取得
        //$rooms = $this->mdlGetRoomList(false);

        // 当日日付を取得
        //$today = null;
        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        //$this->guardRoomAdminRoomcd($roomcd);
        //$roomcd = $rooms[0]->roomcd;
        $roomcd = 110;
        // 教室名を取得する
        //$roomName = $this->($roomcd);

        return view('pages.admin.room_calendar', [
            //'rooms' => $rooms,
            //'name' => $roomName,
            'editData' => [
                'roomcd' => $roomcd,
                'curDate' => null
            ]
        ]);
    }

    /**
     * カレンダー取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return int 生徒Id
     */
    public function getCalendar(Request $request)
    {

        // バリデーション。NGの場合はレスポンスコード422を返却
        //Validator::make($request->all(), $this->rulesForCalendar())->validate();

        // IDのバリデーション
        //$this->validateIdsFromRequest($request, 'sid');

        $roomcd = $request->input('roomcd');

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        //$this->guardRoomAdminRoomcd($roomcd);

        return $this->getRoomCalendar($request, $roomcd, false);
    }

    //==========================
    // 授業スケジュール登録
    //==========================

    /**
     * 登録画面
     *
     * @param \Illuminate\Http\Request $request リクエスト
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

        // 時限リストを取得（校舎・時間割区分から）
        $periods = $this->mdlGetPeriodListByKind($campusCd, $timetableKind);

        // 指定時刻から、対応する時限の情報を取得
        $periodInfo = $this->fncScheGetPeriodTime($campusCd, $timetableKind, $time);

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
            'end_time' => $endTime
        ];

        return view('pages.admin.room_calendar-input', [
            'rules' => null,
            'booths' => $booths,
            'courses' => $courses,
            'periods' => $periods,
            'tutors' => $tutors,
            'students' => $students,
            'subjects' => $subjects,
            'lessonKindList' => $lessonKindList,
            'howToKindList' => $howToKindList,
            'tentativeStatusList' => $tentativeStatusList,
            'substituteKindList' => null,
            'todayabsentList' => null,
            'editData' => $editData
        ]);
    }

    /**
     * コース情報取得（コース種別）
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
            'end_time' => $endTime->format('H:i')
        ];
    }

    /**
     * 時限情報取得（実装中）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 時限情報
     */
    public function getDataSelect(Request $request)
    {
        // 日付のバリデーション
        //$this->validateDatesFromRequest($request, 'target_date');

        // 校舎コード・日付を取得
        $campusCd = $request->input('campus_cd');
        $targetDate = $request->input('target_date');
        $periodNo = $request->input('period_no');

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $this->guardRoomAdminRoomcd($campusCd);

        //---------------------------
        // 時限リストを返却する
        //---------------------------
        $this->debug($campusCd);
        $this->debug($targetDate);
        $this->debug($periodNo);
        // 時限リストを取得（校舎・時間割区分から）
        $periods = $this->mdlGetPeriodListByDate($campusCd, $targetDate);
        $this->debug($periods);

        return [
            'selectItems' => $this->objToArray($periods),
            'period_no' => $periodNo
        ];
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
                'create_kind',
                'mst_codes.name as create_kind_name',
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
            // データ作成区分
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.create_kind', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_32);
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
        $this->debug($classMembers);
        $schedule['class_member_id'] = implode(',', $arrClassMembers);
        $this->debug($schedule['class_member_id']);

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

        // 日付から時間割区分を取得
        $timetableKind = $this->fncScheGetTimeTableKind($campusCd, $targetDate);

        // 時限リストを取得（校舎・時間割区分から）
        $periods = $this->mdlGetPeriodListByKind($campusCd, $timetableKind);

        // 授業区分リストを取得
        $lessonKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_31);

        // 通塾種別リストを取得
        $howToKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_33);

        // 仮登録フラグリストを取得
        $tentativeStatusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_36);

        // 授業代講種別リストを取得
        $substituteKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_34);

        // 出欠ステータスリストを取得
        $todayabsentList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_35);

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

        // 日付から時間割区分を取得
        $timetableKind = $this->fncScheGetTimeTableKind($campusCd, $targetDate);

        // 時限リストを取得（校舎・時間割区分から）
        $periods = $this->mdlGetPeriodListByKind($campusCd, $timetableKind);

        // 授業区分リストを取得
        $lessonKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_31);

        // 通塾種別リストを取得
        $howToKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_33);

        // 仮登録フラグリストを取得
        $tentativeStatusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_36);

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
     * 欠席登録画面
     *
     * @param int $scheduleId スケジュールID
     * @return view
     */
    public function absent($scheduleId)
    {

        // IDのバリデーション
        $this->validateIds($scheduleId);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList();

        // スケジュールを取得
        //$extSchedule = ExtSchedule::select(
        //    'lesson_date',
        //    'start_time',
        //    'end_time',
        //    'sid',
        //    'tid',
        //    'roomcd'
        //)
        //    ->where('id', $scheduleId)
        //    ->firstOrFail();

        //$editData = [
        //    'roomcd' => $extSchedule['roomcd'],
        //    'curDate' => $extSchedule['lesson_date'],
        //    'start_time' => $extSchedule['start_time'],
        //    'end_time' => $extSchedule['end_time'],
        //];

        return view('pages.admin.room_calendar-absent', [
            'rooms' => $rooms,
            //    'editData' => $editData,
            'editData' => null,
            'rules' => $this->rulesForInput(null)
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

        if (!$request['schedule_id']) {
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
                // 教室管理者の場合の校舎コードのチェックはバリデーション(validationRoomList)で行っている
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
                //'substitute_kind',
                //'substitute_tid',
                //'absent_tutor_id',
                //'absent_status',
                'tentative_status',
                //'transfer_id',
                //'transfer_class_id',
                //'report_id',
                'memo',
                'repeat_chk',
                'kaisu',
            );

            // 時間（分）の算出
            $start = Carbon::createFromTimeString($form['start_time']);
            $end = Carbon::createFromTimeString($form['end_time']);
            $minites = $start->diffInMinutes($end);

            // 登録者の設定
            $account = Auth::user();
            // SQLの表示（デバッグ用。削除してからcommit/pushすること）
            \DB::enableQueryLog();

            // 繰り返し登録時の設定
            $targetDates = [];
            if ($form['repeat_chk'] != 'true') {
                array_push($targetDates, $form['target_date']);
            } else {
                $kaisu = intval($form['kaisu']) + 1;
                // 対象日と同一曜日の授業日を取得
                $targetDates = $this->fncScheGetScheduleDate($form['campus_cd'], $form['target_date'], $kaisu, null);
                $this->debug($targetDates);
            }

            foreach ($targetDates as $targetDate) {

                // ブースのチェック・取得
                //$booth = $this->fncScheSearchBooth($form['campus_cd'], $form['booth_cd'], $targetDate);

                // スケジュール情報登録
                // schedulesテーブルへのinsert
                $schedule = new Schedule;
                $schedule->campus_cd = $form['campus_cd'];
                $schedule->target_date = $targetDate;
                $schedule->period_no = $form['period_no'];
                $schedule->start_time = $form['start_time'];
                $schedule->end_time = $form['end_time'];
                $schedule->minites = $minites;
                $schedule->booth_cd = $form['booth_cd'];
                $schedule->course_cd = $form['course_cd'];
                $schedule->student_id = $form['student_id'];
                $schedule->tutor_id = $form['tutor_id'];
                $schedule->subject_cd = $form['subject_cd'];
                $schedule->create_kind = AppConst::CODE_MASTER_32_1;
                $schedule->lesson_kind = $form['lesson_kind'];
                $schedule->how_to_kind = $form['how_to_kind'];
                $schedule->tentative_status = $form['tentative_status'];
                $schedule->memo = $form['memo'];
                $schedule->adm_id = $account->account_id;
                // 登録
                $schedule->save();

                // 受講生徒情報登録（コース種別が授業複の場合のみ）
                if ($form['course_kind'] == AppConst::CODE_MASTER_42_2) {
                    // schedulesテーブル登録時のスケジュールIDをセット
                    $scheduleId = $schedule->schedule_id;

                    foreach (explode(",", $form['class_member_id']) as $member) {
                        // 受講生徒情報テーブルへのinsert
                        $classmember = new ClassMember;
                        $classmember->schedule_id = $scheduleId;
                        $classmember->student_id = $member;
                        $classmember->absent_status = AppConst::CODE_MASTER_35_0;
                        // 登録
                        $classmember->save();
                    }
                }
            }
            // クエリ出力（デバッグ用。削除してからcommit/pushすること）
            $this->debug(\DB::getQueryLog());
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
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        //Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            $form = $request->only(
                'schedule_id',
                // 教室管理者の場合の校舎コードのチェックはバリデーション(validationRoomList)で行っている
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
                'create_kind',
                'lesson_kind',
                'how_to_kind',
                'tentative_status',
                'memo',
            );

            // 時間（分）の算出
            $start = Carbon::createFromTimeString($form['start_time']);
            $end = Carbon::createFromTimeString($form['end_time']);
            $minites = $start->diffInMinutes($end);

            // 登録者の設定
            $account = Auth::user();

            // ブースのチェック・取得

            // SQLの表示（デバッグ用。削除してからcommit/pushすること）
            \DB::enableQueryLog();

            // スケジュール情報
            // schedulesテーブルへのinsert
            $schedule = new Schedule;
            $schedule->campus_cd = $form['campus_cd'];
            $schedule->target_date = $form['target_date'];
            $schedule->period_no = $form['period_no'];
            $schedule->start_time = $form['start_time'];
            $schedule->end_time = $form['end_time'];
            $schedule->minites = $minites;
            $schedule->booth_cd = $form['booth_cd'];
            $schedule->course_cd = $form['course_cd'];
            $schedule->student_id = $form['student_id'];
            $schedule->tutor_id = $form['tutor_id'];
            $schedule->subject_cd = $form['subject_cd'];
            $schedule->create_kind = AppConst::CODE_MASTER_32_1;
            $schedule->lesson_kind = $form['lesson_kind'];
            $schedule->how_to_kind = $form['how_to_kind'];
            $schedule->tentative_status = $form['tentative_status'];
            $schedule->memo = $form['memo'];
            $schedule->adm_id = $account->account_id;
            // 登録
            $schedule->save();

            // 受講生徒情報
            if ($form['course_kind'] == AppConst::CODE_MASTER_42_2) {
                // schedulesテーブル登録時のスケジュールIDをセット
                $scheduleId = $schedule->schedule_id;

                foreach (explode(",", $form['class_member_id']) as $member) {
                    // class_membersテーブルへのinsert
                    $classmember = new ClassMember;
                    $classmember->schedule_id = $scheduleId;
                    $classmember->student_id = $member;
                    $classmember->absent_status = AppConst::CODE_MASTER_35_0;
                    // 登録
                    $classmember->save();
                }
            }
            // クエリ出力（デバッグ用。削除してからcommit/pushすること）
            $this->debug(\DB::getQueryLog());
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

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            $form = $request->only(
                'schedule_id',
                // 教室管理者の場合の校舎コードのチェックはバリデーション(validationRoomList)で行っている
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
                //'transfer_id',
                //'transfer_class_id',
                //'report_id',
                'memo',
            );

            // 時間（分）の算出
            $start = Carbon::createFromTimeString($form['start_time']);
            $end = Carbon::createFromTimeString($form['end_time']);
            $minites = $start->diffInMinutes($end);

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

            // SQLの表示（デバッグ用。削除してからcommit/pushすること）
            \DB::enableQueryLog();

            // スケジュール情報更新（UPDATE）
            // 対象データを取得(IDでユニークに取る)
            $schedule = Schedule::where('schedule_id', $form['schedule_id'])
                // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            //$schedule->campus_cd = $form['campus_cd'];
            $schedule->target_date = $form['target_date'];
            $schedule->period_no = $form['period_no'];
            $schedule->start_time = $form['start_time'];
            $schedule->end_time = $form['end_time'];
            $schedule->minites = $minites;
            $schedule->booth_cd = $form['booth_cd'];
            //$schedule->course_cd = $form['course_cd'];
            $schedule->student_id = $form['student_id'];
            $schedule->tutor_id = $tutorId;
            $schedule->subject_cd = $form['subject_cd'];
            //$schedule->create_kind = AppConst::CODE_MASTER_32_1;
            $schedule->lesson_kind = $form['lesson_kind'];
            $schedule->how_to_kind = $form['how_to_kind'];
            $schedule->tentative_status = $form['tentative_status'];
            $schedule->substitute_kind = $form['substitute_kind'];
            $schedule->absent_tutor_id = $absentTutorId;
            $schedule->tentative_status = $form['tentative_status'];
            $schedule->absent_status = $form['absent_status'];
            $schedule->memo = $form['memo'];
            //$schedule->adm_id = $account->account_id;
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
                $arrMembersAft = explode(",", $form['class_member_id']);

                $this->debug($arrMembersBef);
                $this->debug($arrMembersAft);
                // 受講生徒情報削除処理
                // 変更前の生徒 - 変更後の生徒 で差分を取得
                $arrMembersDel = array_diff($arrMembersBef, $arrMembersAft);
                $this->debug($arrMembersDel);

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
                $this->debug($arrMembersAdd);

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

            // クエリ出力（デバッグ用。削除してからcommit/pushすること）
            $this->debug(\DB::getQueryLog());
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

            // 校舎リストを取得
            $courses = $this->mdlGetCourseList(false);
            if (!isset($courses[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 用途種別
        $validationKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_41);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += Schedule::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += Schedule::fieldRules('course_cd', ['required', $validationCourseList]);
        $rules += Schedule::fieldRules('booth_cd', ['required']);

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
    // クラス内共通処理
    //==========================

}
