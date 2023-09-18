<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Auth;
use App\Models\Schedule;
use App\Models\YearlySchedule;
use App\Models\MstTimetable;
use App\Models\MstCampus;
use App\Models\CodeMaster;
use App\Models\Account;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use Illuminate\Support\Facades\Lang;

/**
 * 教室カレンダー - コントローラ
 */
class RoomCalendarController extends Controller
{

    // 機能共通処理：カレンダー
    use FuncCalendarTrait;

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
        //$roomName = $this->getRoomName($roomcd);

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
     * レギュラースケジュールカレンダー
     *
     * @return view
     */
    public function defaultWeek()
    {

        //        // IDのバリデーション
        //        $this->validateIds($roomcd);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 当日日付を取得
        $today = null;
        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        //$this->guardRoomAdminRoomcd($roomcd);
        //$roomcd = $rooms[0]->roomcd;
        $roomcd = 110;
        // 教室名を取得する
        $roomName = $this->getRoomName($roomcd);

        return view('pages.admin.regular_schedule', [
            'rooms' => $rooms,
            'name' => $roomName,
            // カレンダー用にIDを渡す
            'editData' => [
                'roomcd' => $roomcd,
                'curDate' => $today
            ]
        ]);
    }

    /**
     * カレンダー
     *
     * @param int $sid 生徒Id
     * @return view
     */
    public function eventCalendar()
    {

        //        // IDのバリデーション
        //        $this->validateIds($roomcd);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 当日日付を取得
        $today = null;
        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        //$this->guardRoomAdminRoomcd($roomcd);
        //$roomcd = $rooms[0]->roomcd;
        $roomcd = 110;
        // 教室名を取得する
        $roomName = $this->getRoomName($roomcd);

        return view('pages.admin.event_calendar', [
            'rooms' => $rooms,
            'name' => $roomName,
            // カレンダー用にIDを渡す
            'editData' => [
                'roomcd' => $roomcd,
                'curDate' => $today
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

    /**
     * カレンダー取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return int 生徒Id
     */
    public function getCalendarRegular(Request $request)
    {

        // バリデーション。NGの場合はレスポンスコード422を返却
        //Validator::make($request->all(), $this->rulesForCalendar())->validate();

        // IDのバリデーション
        //$this->validateIdsFromRequest($request, 'sid');

        $roomcd = $request->input('roomcd');

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        //$this->guardRoomAdminRoomcd($roomcd);

        return $this->getRoomCalendar($request, $roomcd, true);
    }

    /**
     * カレンダー取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return int 生徒Id
     */
    public function getCalendarEvent(Request $request)
    {

        // バリデーション。NGの場合はレスポンスコード422を返却
        //Validator::make($request->all(), $this->rulesForCalendar())->validate();

        // IDのバリデーション
        //$this->validateIdsFromRequest($request, 'sid');

        $roomcd = $request->input('roomcd');

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        //$this->guardRoomAdminRoomcd($roomcd);

        return $this->getEventCalendar($request, $roomcd, false);
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
    //public function new(Request $request)
    {

        //$roomcd = $request->query('roomcd');
        //$date = $request->query('date');
        //$start_time = $request->query('start_time');
        //$end_time = $request->query('end_time');
        $date = substr($datetimeStr, 0, 4) . '-' . substr($datetimeStr, 4, 2) . '-' . substr($datetimeStr, 6, 2);
        $time = substr($datetimeStr, 8, 2) . ':' . substr($datetimeStr, 10, 2);

        $param = [
            'campus_cd' => $campusCd,
            'target_date' => $date,
            'start_time' => $time,
            'booth_cd' => $boothCd,
        ];

        // パラメータのバリデーション
        $this->validateFromParam($param, $this->rulesForNew());

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $this->guardRoomAdminRoomcd($campusCd);

        // 教室名を取得する
        $roomName = $this->getRoomName($campusCd);

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
        $timetableKind = $this->getTimeTableKind($campusCd, $date);

        // 時限リストを取得（校舎・時間割区分から）
        $periods = $this->mdlGetPeriodList($campusCd, $timetableKind);

        // 指定時刻から、対応する時限の情報を取得
        $periodInfo = $this->getPeriodTime($campusCd, $timetableKind, $time);
        if (isset($periodInfo)) {
            $periodNo = $periodInfo->timetable_id;
            $startTime = $periodInfo->start_time;
            $endTime = $periodInfo->end_time;
        } else {
            $periodNo = null;
            $startTime = null;
            $endTime = null;
        }

        // 初期表示データをセット
        $editData = [
            'campus_cd' => $campusCd,
            'name' => $roomName,
            'target_date' => $date,
            'booth_cd' => $boothCd,
            'period_no' => $periodNo,
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
            'editData' => $editData
        ]);
    }

    /**
     * バリデーションルールを取得(登録画面パラメータ用)
     *
     * @return array ルール
     */
    private function rulesForNew()
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

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += Schedule::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += Schedule::fieldRules('target_date', ['required']);
        $rules += Schedule::fieldRules('start_time', ['required']);
        $rules += Schedule::fieldRules('booth_cd', ['required']);

        return $rules;
    }

    /**
     * 登録画面（レギュラー）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return view
     */
    public function weekNew(Request $request)
    {

        $roomcd = $request->query('roomcd');
        $day = $request->query('day');
        $start_time = $request->query('start_time');
        $end_time = $request->query('end_time');

        // IDのバリデーション
        //$this->validateIds($roomcd);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $this->guardRoomAdminRoomcd($roomcd);

        // 生徒のidを渡しておく
        $editData = [
            'roomcd' => $roomcd,
            'day_no' => $day,
            'start_time' => substr($start_time, 0, 2) . ':' . substr($start_time, 2, 2),
            'end_time' => substr($end_time, 0, 2) . ':' . substr($end_time, 2, 2)
        ];

        return view('pages.admin.regular_schedule-input', [
            'rooms' => $rooms,
            'rules' => null,
            'editData' => $editData
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

        return;
    }

    /**
     * 編集画面
     *
     * @param int $scheduleId スケジュールID
     * @return view
     */
    public function edit($kind, $scheduleId)
    {

        // IDのバリデーション
        $this->validateIds($scheduleId);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList();

        // スケジュールを取得
        $extSchedule = ExtSchedule::select(
            'lesson_date',
            'start_time',
            'end_time',
            'sid',
            'tid',
            'roomcd'
        )
            ->where('id', $scheduleId)
            ->firstOrFail();

        $editData = [
            'roomcd' => $extSchedule['roomcd'],
            'curDate' => $extSchedule['lesson_date'],
            'start_time' => $extSchedule['start_time'],
            'end_time' => $extSchedule['end_time'],
            'kind' => $kind,
            'course_cd' => "4",
        ];

        return view('pages.admin.room_calendar-input', [
            'rooms' => $rooms,
            'editData' => $editData,
            'rules' => $this->rulesForInput(null)
        ]);
    }

    /**
     * コピー登録画面
     *
     * @param int $scheduleId スケジュールID
     * @return view
     */
    public function copy($kind, $scheduleId)
    {

        // IDのバリデーション
        $this->validateIds($scheduleId);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList();

        // スケジュールを取得
        $extSchedule = ExtSchedule::select(
            'lesson_date',
            'start_time',
            'end_time',
            'sid',
            'tid',
            'roomcd'
        )
            ->where('id', $scheduleId)
            ->firstOrFail();

        $editData = [
            'roomcd' => $extSchedule['roomcd'],
            'curDate' => $extSchedule['lesson_date'],
            'start_time' => $extSchedule['start_time'],
            'end_time' => $extSchedule['end_time'],
            'kind' => $kind,
            'course_cd' => "4",
        ];

        return view('pages.admin.room_calendar-input', [
            'rooms' => $rooms,
            'editData' => $editData,
            'rules' => $this->rulesForInput(null)
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
        $extSchedule = ExtSchedule::select(
            'lesson_date',
            'start_time',
            'end_time',
            'sid',
            'tid',
            'roomcd'
        )
            ->where('id', $scheduleId)
            ->firstOrFail();

        $editData = [
            'roomcd' => $extSchedule['roomcd'],
            'curDate' => $extSchedule['lesson_date'],
            'start_time' => $extSchedule['start_time'],
            'end_time' => $extSchedule['end_time'],
        ];

        return view('pages.admin.room_calendar-absent', [
            'rooms' => $rooms,
            'editData' => $editData,
            'rules' => $this->rulesForInput(null)
        ]);
    }

    /**
     * 編集画面（レギュラー）
     *
     * @param int $scheduleId スケジュールID
     * @return view
     */
    public function weekEdit($kind, $scheduleId)

    {

        // IDのバリデーション
        $this->validateIds($scheduleId);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList();

        // スケジュールを取得
        $extSchedule = ExtSchedule::select(
            'lesson_date',
            'start_time',
            'end_time',
            'sid',
            'tid',
            'roomcd'
        )
            ->where('id', $scheduleId)
            ->firstOrFail();

        $editData = [
            'roomcd' => $extSchedule['roomcd'],
            'curDate' => $extSchedule['lesson_date'],
            'start_time' => $extSchedule['start_time'],
            'end_time' => $extSchedule['end_time'],
            'kind' => $kind,
            'course_cd' => $kind,
        ];

        return view('pages.admin.regular_schedule-input', [
            'rooms' => $rooms,
            'editData' => $editData,
            'rules' => $this->rulesForInput(null)
        ]);
    }

    /**
     * 編集画面（レギュラー）
     *
     * @param int $scheduleId スケジュールID
     * @return view
     */
    public function weekCopy($kind, $scheduleId)
    {

        // IDのバリデーション
        $this->validateIds($scheduleId);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList();

        // スケジュールを取得
        $extSchedule = ExtSchedule::select(
            'lesson_date',
            'start_time',
            'end_time',
            'sid',
            'tid',
            'roomcd'
        )
            ->where('id', $scheduleId)
            ->firstOrFail();

        $editData = [
            'roomcd' => $extSchedule['roomcd'],
            'curDate' => $extSchedule['lesson_date'],
            'start_time' => $extSchedule['start_time'],
            'end_time' => $extSchedule['end_time'],
            'kind' => $kind,
            'course_cd' => $kind,
        ];

        return view('pages.admin.regular_schedule-input', [
            'rooms' => $rooms,
            'editData' => $editData,
            'rules' => $this->rulesForInput(null)
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

        return;
    }

    //==========================
    // クラス内共通処理
    //==========================

    /**
     * 校舎名の取得
     *
     * @param string $campusCd 校舎コード
     * @return object
     */
    private function getRoomName($campusCd)
    {
        // 校舎名を取得
        $query = MstCampus::query();
        $room = $query
            ->select('name as room_name')
            ->where('campus_cd', $campusCd)
            ->firstOrFail();

        return $room->room_name;
    }

    /**
     * 校舎・日付から時間割区分の取得
     *
     * @param string $campusCd 校舎コード
     * @param date $date 対象日
     * @return object
     */
    private function getTimeTableKind($campusCd, $date)
    {
        $query = MstTimetable::query();

        $account = Auth::user();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }
        // 年間予定情報とJOIN
        $query->sdJoin(YearlySchedule::class, function ($join) use ($date) {
            $join->on('mst_timetables.campus_cd', 'yearly_schedules.campus_cd')
                ->where('yearly_schedules.lesson_date', $date);
        })
            // 期間区分
            ->sdJoin(CodeMaster::class, function ($join) {
                $join->on('yearly_schedules.date_kind', '=', 'mst_codes.code')
                    ->on('mst_timetables.timetable_kind', '=', 'mst_codes.sub_code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_38);
            })
            // 校舎は指定されている前提として絞り込み
            ->where('mst_timetables.campus_cd', $campusCd);

        $timeTable = $query
            ->select('timetable_kind')
            ->distinct()
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        return $timeTable->timetable_kind;
    }

    /**
     * 校舎・時間割区分から時間割情報の取得
     *
     * @param string $campusCd 校舎コード
     * @param int $timetableKind 時間割区分
     * @return object
     */
    private function getPeriodTime($campusCd, $timetableKind, $time)
    {
        $query = MstTimetable::query();

        $account = Auth::user();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }

        $timeTable = $query
            // 指定校舎で絞り込み
            ->where('campus_cd', $campusCd)
            // 時間割区分で絞り込み
            ->where('timetable_kind', $timetableKind)
            // 時間から対象時限を絞り込み
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->first();

        return $timeTable;
    }
}
