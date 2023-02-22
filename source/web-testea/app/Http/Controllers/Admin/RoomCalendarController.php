<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\ExtSchedule;
use App\Models\ExtGenericMaster;
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
        $rooms = $this->mdlGetRoomList(false);

        // 当日日付を取得
        $today = null;
        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        //$this->guardRoomAdminRoomcd($roomcd);
        //$roomcd = $rooms[0]->roomcd;
        $roomcd = 110;
        // 教室名を取得する
        $roomName = $this->getRoomName($roomcd);

        return view('pages.admin.room_calendar', [
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

    //==========================
    // 授業スケジュール登録
    //==========================

    /**
     * 登録画面
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return view
     */
    public function new(Request $request)
    {

        $roomcd = $request->query('roomcd');
        $date = $request->query('date');
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
            'curDate' => substr($date, 0, 4) . '/' . substr($date, 4, 2) . '/' . substr($date, 6, 2),
            'start_time' => substr($start_time, 0, 2) . ':' . substr($start_time, 2, 2),
            'end_time' => substr($end_time, 0, 2) . ':' . substr($end_time, 2, 2)
        ];

        return view('pages.admin.room_calendar-input', [
            'rooms' => $rooms,
            'rules' => null,
            'editData' => $editData
        ]);
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
    public function edit($scheduleId)
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

        return view('pages.admin.room_calendar-input', [
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
    public function weekEdit($scheduleId)
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
     * 教室名の取得
     *
     * @param int $sid 生徒Id
     * @return object
     */
    private function getRoomName($roomcd)
    {

        // コードマスタより教室情報を取得
        $codemasters = CodeMaster::select('gen_item1', 'gen_item2')
            ->where('data_type', AppConst::CODE_MASTER_6)
            ->first();

        // 教室名を取得
        $query = ExtGenericMaster::query();
        $room = $query
            ->select('name2 as room_name')
            ->where('codecls', $codemasters->gen_item1)
            ->where('code', $roomcd)
            ->firstOrFail();

        return $room->room_name;
    }
}
