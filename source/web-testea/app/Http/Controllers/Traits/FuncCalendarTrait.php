<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use App\Models\ExtSchedule;
use App\Models\ExtStudentKihon;
use App\Models\CodeMaster;
use App\Models\ExtRirekisho;
use App\Models\ExtGenericMaster;
use App\Models\ExtTrialMaster;
use App\Models\ExtRoom;
use App\Models\Event;
use App\Models\EventApply;
use App\Models\RoomHoliday;
use App\Models\TutorSchedule;
use App\Models\TutorRelate;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Auth;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Log;

/**
 * カレンダー - 機能共通処理
 */
trait FuncCalendarTrait
{

    /**
     * バリデーションルールを取得(カレンダー用)
     *
     * @return array
     */
    private function rulesForCalendar()
    {

        // タイムスタンプで来る
        // 'start' => 1598713200000,
        // 'end' => 1602342000000,
        return [
            'start' => ['integer', 'required', 'date_format:U'],
            'end' => ['integer', 'required', 'date_format:U']
        ];
    }

    /**
     * 生徒のカレンダーを取得
     *
     * @param int $sid 生徒No.
     */
    private function getStudentCalendar(Request $request, $sid)
    {

        // リクエストから日付を取得(カレンダーの表示範囲)
        // MEMO: Y-m-dで比較するので、条件絞り込み対象の項目が「Date型」であることに注意(DateTimeの場合はうまく行かない)
        $start_date = date('Y-m-d', $request->input('start') / 1000);
        $end_date = date('Y-m-d', $request->input('end') / 1000 - 1);

        // 翌日日付取得
        $tomorrow = date("Y-m-d", strtotime('+1 day'));
        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // コードマスタからスケジュール種別を取得する
        $query = CodeMaster::query();
        $schedule_type_names = $query
            ->select(
                'code',
                'name'
            )
            ->where('data_type', '=', AppConst::CODE_MASTER_21)
            ->orderBy('code', 'asc')
            ->get()
            ->keyBy('code');

        // 個別授業の取得
        $query = ExtSchedule::query();
        $regular_schedules = $query
            ->select(
                'id',
                'ext_student_kihon.name AS name',
                'lesson_type',
                'lesson_date',
                'ext_schedule.start_time',
                'ext_schedule.end_time',
                'atd_status_cd',
                'create_kind_cd',
                'transefer_kind_cd',
                'room_name AS mdClassName',
                'room_name_symbol AS symbol',
                'ext_rirekisho.name AS mdTitleVal',
                'ext_generic_master.name1 AS mdSubject'
            )
            // 生徒名の取得
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('ext_student_kihon.sid', '=', 'ext_schedule.sid');
            })
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('ext_schedule.roomcd', '=', 'room_names.code');
            })
            // 履歴書の取得
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('ext_rirekisho.tid', '=', 'ext_schedule.tid');
            })
            // 教科の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'ext_schedule.curriculumcd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_000_114);
            })
            ->where('ext_schedule.sid', '=', $sid)
            ->where('ext_schedule.lesson_type', '=', AppConst::EXT_GENERIC_MASTER_109_0)
            // カレンダーの表示範囲で絞る
            ->whereBetween('ext_schedule.lesson_date', [$start_date, $end_date])
            ->orderBy('ext_schedule.lesson_date', 'asc')
            ->orderBy('ext_schedule.start_time', 'asc')
            ->get();

        foreach ($regular_schedules as $schedule) {

            $schedule_type = $this->getScheduleType($schedule);
            $schedule['title'] = $schedule['symbol'] . ' ' . $schedule['mdSubject'];
            if ($schedule_type['mdFurikae'] != '') {
                $schedule['title'] = $schedule['title'] . ' ' . $schedule_type['mdFurikae'];
            }
            $schedule['start'] = $schedule['lesson_date']->format('Y-m-d') . ' ' . $schedule['start_time']->format('H:i');
            $schedule['end'] = $schedule['lesson_date']->format('Y-m-d') . ' ' . $schedule['end_time']->format('H:i');
            $schedule['classNames'] = $schedule_type['className'];

            // モーダル表示用
            $schedule['mdType'] = $schedule_type['type'];
            $schedule['mdTypeName'] = $schedule_type_names[(string) $schedule_type['type']]['name'];
            $schedule['mdDt'] = $schedule['lesson_date'];
            $schedule['mdStartTime'] = $schedule['start_time'];
            $schedule['mdEndTime'] = $schedule['end_time'];
            $schedule['mdTitle'] = '教師名';
            $schedule['mdFurikae'] = $schedule_type['mdFurikae'];
            $schedule['mdBtn'] = false;
            if ($schedule['lesson_date'] >= $tomorrow && $schedule['atd_status_cd'] != AppConst::ATD_STATUS_CD_2) {
                // 欠席申請のボタンを表示する
                $schedule['mdBtn'] = true;
            }

            // 不要な要素の削除
            unset($schedule['name']);
            unset($schedule['lesson_type']);
            unset($schedule['symbol']);
            unset($schedule['lesson_date']);
            unset($schedule['start_time']);
            unset($schedule['end_time']);
            unset($schedule['atd_status_cd']);
            unset($schedule['create_kind_cd']);
        }

        // 短期講習の取得
        $query = ExtSchedule::query();
        $tanki_schedules = $query
            ->select(
                'id',
                'lesson_type',
                'lesson_date',
                'ext_schedule.start_time',
                'ext_schedule.end_time',
                'atd_status_cd',
                'create_kind_cd',
                'transefer_kind_cd',
                'room_name AS mdClassName',
                'room_name_symbol AS symbol',
                'ext_rirekisho.name AS mdTitleVal',
                'ext_generic_master.name1 AS mdSubject'

            )
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('ext_schedule.roomcd', '=', 'room_names.code');
            })
            // 教科の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'ext_schedule.curriculumcd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_000_114);
            })
            // 履歴書の取得
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('ext_rirekisho.tid', '=', 'ext_schedule.tid');
            })
            ->where('ext_schedule.sid', '=', $sid)
            ->where('ext_schedule.lesson_type', '=', AppConst::EXT_GENERIC_MASTER_109_1)
            // カレンダーの表示範囲で絞る
            ->whereBetween('ext_schedule.lesson_date', [$start_date, $end_date])
            ->orderBy('ext_schedule.lesson_date', 'asc')
            ->orderBy('ext_schedule.start_time', 'asc')
            ->get();

        foreach ($tanki_schedules as $schedule) {

            $schedule_type = $this->getScheduleType($schedule);
            $schedule['title'] = $schedule['symbol'] . ' ' . $schedule['mdTitleVal'];
            if ($schedule_type['mdFurikae'] != '') {
                $schedule['title'] = $schedule['title'] . ' ' . $schedule_type['mdFurikae'];
            }
            $schedule['start'] = $schedule['lesson_date']->format('Y-m-d') . ' ' . $schedule['start_time']->format('H:i');
            $schedule['end'] = $schedule['lesson_date']->format('Y-m-d') . ' ' . $schedule['end_time']->format('H:i');
            $schedule['classNames'] = $schedule_type['className'];

            // モーダル表示用
            $schedule['mdType'] = $schedule_type['type'];
            $schedule['mdTypeName'] = $schedule_type_names[(string) $schedule_type['type']]['name'];
            $schedule['mdDt'] = $schedule['lesson_date'];
            $schedule['mdStartTime'] = $schedule['start_time'];
            $schedule['mdEndTime'] = $schedule['end_time'];
            $schedule['mdTitle'] = '教師名';
            $schedule['mdFurikae'] = $schedule_type['mdFurikae'];
            $schedule['mdBtn'] = false;
            if ($schedule['lesson_date'] >= $tomorrow && $schedule['atd_status_cd'] != AppConst::ATD_STATUS_CD_2) {
                // 欠席申請のボタンを表示する
                $schedule['mdBtn'] = true;
            }

            // 不要な要素の削除
            unset($schedule['name']);
            unset($schedule['lesson_type']);
            unset($schedule['symbol']);
            unset($schedule['lesson_date']);
            unset($schedule['start_time']);
            unset($schedule['end_time']);
            unset($schedule['atd_status_cd']);
            unset($schedule['create_kind_cd']);
        }

        $schedules = collect($regular_schedules)->merge($tanki_schedules);

        // 模擬試験の取得
        $query = ExtSchedule::query();
        $trial_schedules = $query
            ->select(
                'id',
                'lesson_date',
                'ext_schedule.start_time',
                'ext_schedule.end_time',
                'room_name AS mdClassName',
                'room_name_symbol AS symbol',
                'ext_trial_master.name AS mdTitleVal'
            )
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('ext_schedule.roomcd', '=', 'room_names.code');
            })
            // 模試マスタ
            ->sdLeftJoin(ExtTrialMaster::class, function ($join) {
                $join->on('ext_trial_master.tmid', '=', 'ext_schedule.tmid');
            })
            ->where('ext_schedule.sid', '=', $sid)
            ->where('ext_schedule.lesson_type', '=', AppConst::EXT_GENERIC_MASTER_109_3)
            // カレンダーの表示範囲で絞る
            ->whereBetween('ext_schedule.lesson_date', [$start_date, $end_date])
            ->orderBy('ext_schedule.lesson_date', 'asc')
            ->orderBy('ext_schedule.start_time', 'asc')
            ->get();

        foreach ($trial_schedules as $schedule) {

            $schedule_type = ['type' => AppConst::CODE_MASTER_21_3, 'className' => 'cal_tanki_moshi', 'mdFurikae' => ''];
            $schedule['title'] = $schedule['symbol'] . ' ' . $schedule['mdTitleVal'];
            if (empty($schedule['start_time'])) {
                $schedule['start_time'] = "00:00";
            }
            $schedule['start'] = $schedule['lesson_date']->format('Y-m-d') . ' ' . $schedule['start_time']->format('H:i');
            if (empty($schedule['end_time'])) {
                $schedule['end_time'] = "00:00";
            }
            $schedule['end'] = $schedule['lesson_date']->format('Y-m-d') . ' ' . $schedule['end_time']->format('H:i');
            $schedule['classNames'] = $schedule_type['className'];

            // モーダル表示用
            $schedule['mdType'] = $schedule_type['type'];
            $schedule['mdTypeName'] = $schedule_type_names[(string) $schedule_type['type']]['name'];
            $schedule['mdDt'] = $schedule['lesson_date'];
            $schedule['mdStartTime'] = $schedule['start_time'];
            $schedule['mdEndTime'] = $schedule['end_time'];
            $schedule['mdTitle'] = '模試名';
            $schedule['mdSubject'] = '';
            $schedule['mdFurikae'] = $schedule_type['mdFurikae'];

            // 不要な要素の削除
            unset($schedule['symbol']);
            unset($schedule['lesson_date']);
            unset($schedule['start_time']);
            unset($schedule['end_time']);
        }

        $schedules = collect($schedules)->merge($trial_schedules);

        // 生徒の所属教室の事前取得
        $query = ExtRoom::query();
        $rooms = $query
            ->select(
                'sid',
                'roomcd',
                'room_name',
                'room_name_symbol'
            )
            ->where('ext_room.sid', '=', $sid)
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('ext_room.roomcd', '=', 'room_names.code');
            })
            ->orderByRaw('CAST(ext_room.roomcd AS signed) asc')
            ->get();

        // イベントの取得
        $query = EventApply::query();
        $event_schedules = $query
            ->select(
                'event_apply_id AS id',
                'name AS mdTitleVal',
                'event_date',
                'start_time',
                'end_time'
            )
            // イベント名取得
            ->sdLeftJoin(Event::class, function ($join) {
                $join->on('event_apply.event_id', '=', 'event.event_id');
            })
            ->where('event_apply.sid', '=', $sid)
            ->where('event_apply.changes_state', '=', AppConst::CODE_MASTER_2_2)
            // カレンダーの表示範囲で絞る
            ->whereBetween('event.event_date', [$start_date, $end_date])
            ->orderBy('event.event_date', 'asc')
            ->orderBy('event.start_time', 'asc')
            ->get();

        foreach ($event_schedules as $schedule) {

            $schedule_type = ['type' => AppConst::CODE_MASTER_21_4, 'className' => 'cal_event', 'mdFurikae' => ''];
            $schedule['title'] = $schedule['mdTitleVal'];
            $schedule['start'] = $schedule['event_date']->format('Y-m-d') . ' ' . $schedule['start_time']->format('H:i');
            $schedule['end'] = $schedule['event_date']->format('Y-m-d') . ' ' . $schedule['end_time']->format('H:i');
            $schedule['classNames'] = $schedule_type['className'];

            // モーダル表示用
            $schedule['mdType'] = $schedule_type['type'];
            $schedule['mdTypeName'] = $schedule_type_names[(string) $schedule_type['type']]['name'];
            $schedule['mdClassName'] = '';
            $schedule['mdDt'] = $schedule['event_date'];
            $schedule['mdStartTime'] = $schedule['start_time'];
            $schedule['mdEndTime'] = $schedule['end_time'];
            $schedule['mdTitle'] = 'イベント名';
            $schedule['mdSubject'] = '';
            $schedule['mdFurikae'] = $schedule_type['mdFurikae'];

            // 不要な要素の削除
            unset($schedule['event_date']);
            unset($schedule['start_time']);
            unset($schedule['end_time']);
        }

        $schedules = collect($schedules)->merge($event_schedules);

        // 複数教室所属の場合を考慮し、whereIn用に配列化
        $roomcds = [];
        foreach ($rooms as $room) {
            array_push($roomcds, $room->roomcd);
        };
        $roomcds = array_unique($roomcds);

        // 休業日の取得
        $query = RoomHoliday::query();
        $room_holiday_schedules = $query
            ->select(
                'room_holiday_id AS id',
                'holiday_date',
                'room_name AS mdClassName',
                'room_name_symbol AS symbol'
            )
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('room_holiday.roomcd', '=', 'room_names.code');
            })
            // 自分の教室のみ取得
            ->whereIn('room_holiday.roomcd', $roomcds)
            // カレンダーの表示範囲で絞る
            ->whereBetween('room_holiday.holiday_date', [$start_date, $end_date])
            ->orderBy('room_holiday.holiday_date', 'asc')
            ->get();

        foreach ($room_holiday_schedules as $schedule) {

            $schedule_type = ['type' => AppConst::CODE_MASTER_21_5, 'className' => 'cal_closed', 'mdFurikae' => ''];
            $schedule['title'] = $schedule['symbol'] . ' ' . "休業日";
            $schedule['start'] = $schedule['holiday_date']->format('Y-m-d');
            $schedule['classNames'] = $schedule_type['className'];

            // モーダル表示用
            $schedule['mdType'] = AppConst::CODE_MASTER_21_5;
            $schedule['mdTypeName'] = $schedule_type_names[(string) $schedule_type['type']]['name'];
            $schedule['mdDt'] = $schedule['holiday_date'];
            $schedule['mdStartTime'] = '';
            $schedule['mdEndTime'] = '';
            $schedule['mdTitle'] = '';
            $schedule['mdTitleVal'] = '';
            $schedule['mdSubject'] = '';
            $schedule['mdFurikae'] = $schedule_type['mdFurikae'];

            // 不要な要素の削除
            unset($schedule['holiday_date']);
        }

        $schedules = collect($schedules)->merge($room_holiday_schedules);

        return $schedules;
    }

    /**
     * 教師のカレンダーを取得
     *
     * @param int $tid 教師No.
     */
    private function getTutorCalendar(Request $request, $tid)
    {

        // リクエストから日付を取得
        $start_date = date('Y-m-d', $request->input('start') / 1000);
        $end_date = date('Y-m-d', $request->input('end') / 1000 - 1);

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // コードマスタからスケジュール種別を取得する
        $query = CodeMaster::query();
        $schedule_type_names = $query
            ->select(
                'code',
                'name'
            )
            ->where('data_type', '=', AppConst::CODE_MASTER_21)
            ->orderBy('code', 'asc')
            ->get()
            ->keyBy('code');

        // 個別授業の取得
        $query = ExtSchedule::query();
        $regular_schedules = $query
            ->select(
                'id',
                'ext_student_kihon.name AS mdTitleVal',
                'lesson_type',
                'lesson_date',
                'ext_schedule.start_time',
                'ext_schedule.end_time',
                'atd_status_cd',
                'create_kind_cd',
                'transefer_kind_cd',
                'room_name AS mdClassName',
                'room_name_symbol AS symbol',
                'ext_generic_master.name1 AS mdSubject'

            )
            // 生徒名の取得
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('ext_student_kihon.sid', '=', 'ext_schedule.sid');
            })
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('ext_schedule.roomcd', '=', 'room_names.code');
            })
            // 教科名の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'ext_schedule.curriculumcd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_000_114);
            })
            ->where('ext_schedule.tid', '=', $tid)
            ->where('ext_schedule.lesson_type', '=', AppConst::EXT_GENERIC_MASTER_109_0)
            // カレンダーの表示範囲で絞る
            ->whereBetween('ext_schedule.lesson_date', [$start_date, $end_date])
            ->orderBy('ext_schedule.lesson_date', 'asc')
            ->orderBy('ext_schedule.start_time', 'asc')
            ->get();

        foreach ($regular_schedules as $schedule) {

            $schedule_type = $this->getScheduleType($schedule);
            $schedule['title'] = $schedule['symbol'] . ' ' . $schedule['mdTitleVal'];
            if ($schedule_type['mdFurikae'] != '') {
                $schedule['title'] = $schedule['title'] . ' ' . $schedule_type['mdFurikae'];
            }
            $schedule['start'] = $schedule['lesson_date']->format('Y-m-d') . ' ' . $schedule['start_time']->format('H:i');
            $schedule['end'] = $schedule['lesson_date']->format('Y-m-d') . ' ' . $schedule['end_time']->format('H:i');
            $schedule['classNames'] = $schedule_type['className'];

            // モーダル表示用
            $schedule['mdType'] = $schedule_type['type'];
            $schedule['mdTypeName'] = $schedule_type_names[(string) $schedule_type['type']]['name'];
            $schedule['mdDt'] = $schedule['lesson_date'];
            $schedule['mdStartTime'] = $schedule['start_time'];
            $schedule['mdEndTime'] = $schedule['end_time'];
            $schedule['mdTitle'] = '生徒名';
            $schedule['mdFurikae'] = $schedule_type['mdFurikae'];

            // 不要な要素の削除
            unset($schedule['lesson_type']);
            unset($schedule['symbol']);
            unset($schedule['lesson_date']);
            unset($schedule['start_time']);
            unset($schedule['end_time']);
            unset($schedule['atd_status_cd']);
            unset($schedule['create_kind_cd']);
        }

        // 短期講習の取得
        $query = ExtSchedule::query();
        $tanki_schedules = $query
            ->select(
                'id',
                'ext_student_kihon.name AS mdTitleVal',
                'lesson_type',
                'lesson_date',
                'ext_schedule.start_time',
                'ext_schedule.end_time',
                'atd_status_cd',
                'create_kind_cd',
                'transefer_kind_cd',
                'room_name AS mdClassName',
                'room_name_symbol AS symbol',
                'ext_generic_master.name1 AS mdSubject'
            )
            // 生徒名の取得
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('ext_student_kihon.sid', '=', 'ext_schedule.sid');
            })
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('ext_schedule.roomcd', '=', 'room_names.code');
            })
            // 教科名の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'ext_schedule.curriculumcd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_000_114);
            })
            ->where('ext_schedule.tid', '=', $tid)
            ->where('ext_schedule.lesson_type', '=', AppConst::EXT_GENERIC_MASTER_109_1)
            // カレンダーの表示範囲で絞る
            ->whereBetween('ext_schedule.lesson_date', [$start_date, $end_date])
            ->orderBy('ext_schedule.lesson_date', 'asc')
            ->orderBy('ext_schedule.start_time', 'asc')
            ->get();

        foreach ($tanki_schedules as $schedule) {

            $schedule_type = $this->getScheduleType($schedule);
            $schedule['title'] = $schedule['symbol'] . ' ' . $schedule['mdTitleVal'];
            if ($schedule_type['mdFurikae'] != '') {
                $schedule['title'] = $schedule['title'] . ' ' . $schedule_type['mdFurikae'];
            }
            $schedule['start'] = $schedule['lesson_date']->format('Y-m-d') . ' ' . $schedule['start_time']->format('H:i');
            $schedule['end'] = $schedule['lesson_date']->format('Y-m-d') . ' ' . $schedule['end_time']->format('H:i');
            $schedule['classNames'] = $schedule_type['className'];

            // モーダル表示用
            $schedule['mdType'] = $schedule_type['type'];
            $schedule['mdTypeName'] = $schedule_type_names[(string) $schedule_type['type']]['name'];
            $schedule['mdDt'] = $schedule['lesson_date'];
            $schedule['mdStartTime'] = $schedule['start_time'];
            $schedule['mdEndTime'] = $schedule['end_time'];
            $schedule['mdTitle'] = '生徒名';
            $schedule['mdFurikae'] = $schedule_type['mdFurikae'];

            // 不要な要素の削除
            unset($schedule['lesson_type']);
            unset($schedule['symbol']);
            unset($schedule['lesson_date']);
            unset($schedule['start_time']);
            unset($schedule['end_time']);
            unset($schedule['atd_status_cd']);
            unset($schedule['create_kind_cd']);
        }

        $schedules = collect($regular_schedules)->merge($tanki_schedules);

        // 打ち合わせの取得
        $query = TutorSchedule::query();
        $tutor_schedules = $query
            ->select(
                'tutor_schedule_id AS id',
                'title AS mdTitleVal',
                'start_date',
                'start_time',
                'end_time',
                'room_name AS mdClassName',
                'room_name_symbol AS symbol',
                'roomcd'
            )
            // 教科名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('tutor_schedule.roomcd', '=', 'room_names.code');
            })
            ->where('tutor_schedule.tid', '=', $tid)
            // カレンダーの表示範囲で絞る
            ->whereBetween('tutor_schedule.start_date', [$start_date, $end_date])
            ->orderBy('tutor_schedule.start_date', 'asc')
            ->orderBy('tutor_schedule.start_time', 'asc')
            ->get();

        // 教室管理者の場合、更新ボタンの有無をチェックするため
        if (AuthEx::isRoomAdmin()) {
            $account = Auth::user();
        }

        foreach ($tutor_schedules as $schedule) {

            $schedule_type = ['type' => AppConst::CODE_MASTER_21_6, 'className' => 'cal_meeting', 'mdFurikae' => ''];
            $schedule['title'] = $schedule['symbol'] . ' ' . $schedule['mdTitleVal'];
            $schedule['start'] = $schedule['start_date']->format('Y-m-d') . ' ' . $schedule['start_time']->format('H:i');
            $schedule['end'] = $schedule['start_date']->format('Y-m-d') . ' ' . $schedule['end_time']->format('H:i');
            $schedule['classNames'] = $schedule_type['className'];

            // モーダル表示用
            $schedule['mdType'] = AppConst::CODE_MASTER_21_6;
            $schedule['mdTypeName'] = $schedule_type_names[(string) $schedule_type['type']]['name'];
            $schedule['mdDt'] = $schedule['start_date'];
            $schedule['mdStartTime'] = $schedule['start_time'];
            $schedule['mdEndTime'] = $schedule['end_time'];
            $schedule['mdTitle'] = '打ち合わせ名';
            $schedule['mdSubject'] = '';
            $schedule['mdFurikae'] = $schedule_type['mdFurikae'];
            $schedule['mdEditBtn'] = true;
            if (AuthEx::isRoomAdmin()) {
                if ($account->roomcd != $schedule->roomcd) {
                    // 教室管理者の場合、更新ボタンの有無をチェックするため
                    $schedule['mdEditBtn'] = false;
                }
            }

            // 不要な要素の削除
            unset($schedule['start_date']);
            unset($schedule['start_time']);
            unset($schedule['end_time']);
            unset($schedule['symbol']);
            unset($schedule['roomcd']);
        }

        $schedules = collect($schedules)->merge($tutor_schedules);

        // 教師の所属教室の事前取得
        $query = TutorRelate::query();
        $rooms = $query
            ->select(
                'roomcd',
                'room_name',
                'room_name_symbol'
            )
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('tutor_relate.roomcd', '=', 'room_names.code');
            })
            ->where('tutor_relate.tid', '=', $tid)
            ->orderByRaw('CAST(tutor_relate.roomcd AS signed) asc')
            ->get();

        // 複数教室所属の場合を考慮し、whereIn用に配列化
        $roomcds = [];
        foreach ($rooms as $room) {
            array_push($roomcds, $room->roomcd);
        };
        $roomcds = array_unique($roomcds);

        // 休業日の取得
        $query = RoomHoliday::query();
        $room_holiday_schedules = $query
            ->select(
                'room_holiday_id AS id',
                'holiday_date',
                'room_name AS mdClassName',
                'room_name_symbol AS symbol'
            )
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('room_holiday.roomcd', '=', 'room_names.code');
            })
            // 自分の教室データを取得
            ->whereIn('room_holiday.roomcd', $roomcds)
            // カレンダーの表示範囲で絞る
            ->whereBetween('room_holiday.holiday_date', [$start_date, $end_date])
            ->orderBy('room_holiday.holiday_date', 'asc')
            ->get();

        foreach ($room_holiday_schedules as $schedule) {

            $schedule_type = ['type' => AppConst::CODE_MASTER_21_5, 'className' => 'cal_closed', 'mdFurikae' => ''];
            $schedule['title'] = $schedule['symbol'] . ' ' . "休業日";
            $schedule['start'] = $schedule['holiday_date']->format('Y-m-d');
            $schedule['classNames'] = $schedule_type['className'];

            // モーダル表示用
            $schedule['mdType'] = AppConst::CODE_MASTER_21_5;
            $schedule['mdTypeName'] = $schedule_type_names[(string) $schedule_type['type']]['name'];
            $schedule['mdDt'] = $schedule['holiday_date'];
            $schedule['mdStartTime'] = '';
            $schedule['mdEndTime'] = '';
            $schedule['mdTitle'] = '';
            $schedule['mdTitleVal'] = '';
            $schedule['mdSubject'] = '';
            $schedule['mdFurikae'] = $schedule_type['mdFurikae'];

            // 不要な要素の削除
            unset($schedule['holiday_date']);
        }

        $schedules = collect($schedules)->merge($room_holiday_schedules);

        return $schedules;
    }

    /**
     * 教室のカレンダーを取得
     *
     * @param int $roomcd 教室cd
     */
    private function getRoomCalendar(Request $request, $roomcd, $flg)
    {

        // リクエストから日付を取得(カレンダーの表示範囲)
        // MEMO: Y-m-dで比較するので、条件絞り込み対象の項目が「Date型」であることに注意(DateTimeの場合はうまく行かない)
        $start_date = date('Y-m-d', $request->input('start') / 1000);
        $end_date = date('Y-m-d', $request->input('end') / 1000 - 1);

        // 翌日日付取得
        $tomorrow = date("Y-m-d", strtotime('+1 day'));
        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // コードマスタからスケジュール種別を取得する
        $query = CodeMaster::query();
        $schedule_type_names = $query
            ->select(
                'code',
                'name'
            )
            ->where('data_type', '=', AppConst::CODE_MASTER_21)
            ->orderBy('code', 'asc')
            ->get()
            ->keyBy('code');

        // 個別授業の取得
        $query = ExtSchedule::query();
        $regular_schedules = $query
            ->select(
                'id',
                'ext_student_kihon.name AS name',
                'lesson_type',
                'lesson_date',
                'ext_schedule.start_time',
                'ext_schedule.end_time',
                'atd_status_cd',
                'create_kind_cd',
                'transefer_kind_cd',
                'room_name AS mdClassName',
                'room_name_symbol AS symbol',
                'ext_rirekisho.name AS mdTitleVal',
                'ext_generic_master.name1 AS mdSubject'
            )
            // 生徒名の取得
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('ext_student_kihon.sid', '=', 'ext_schedule.sid');
            })
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('ext_schedule.roomcd', '=', 'room_names.code');
            })
            // 履歴書の取得
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('ext_rirekisho.tid', '=', 'ext_schedule.tid');
            })
            // 教科の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'ext_schedule.curriculumcd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_000_114);
            })
            ->where('ext_schedule.roomcd', '=', $roomcd)
            ->where('ext_schedule.lesson_type', '=', AppConst::EXT_GENERIC_MASTER_109_0)
            // カレンダーの表示範囲で絞る
            ->whereBetween('ext_schedule.lesson_date', [$start_date, $end_date])
            ->orderBy('ext_schedule.lesson_date', 'asc')
            ->orderBy('ext_schedule.start_time', 'asc')
            ->get();

        foreach ($regular_schedules as $schedule) {

            $schedule_type = $this->getScheduleType($schedule);
            $schedule['title'] = $schedule['start_time']->format('H:i') . '-'
             . $schedule['end_time']->format('H:i') . '<br>個別指導コース<br>' . $schedule['mdSubject']
             . '<br>stu：' . $schedule['name'] . '<br>tea：' . $schedule['mdTitleVal'];
             if ($schedule_type['mdFurikae'] != '') {
                $schedule['title'] = $schedule['title'] . '<br>' . $schedule_type['mdFurikae'];
            }
            $schedule['start'] = $schedule['lesson_date']->format('Y-m-d') . ' ' . $schedule['start_time']->format('H:i');
            $schedule['end'] = $schedule['lesson_date']->format('Y-m-d') . ' ' . $schedule['end_time']->format('H:i');
            $schedule['classNames'] = $schedule_type['className'];
            if ($schedule_type['mdFurikae'] === '後日振替・未' || $schedule_type['mdFurikae'] === '後日振替・済') {
                $schedule['resourceId'] = "999";
            } else {
                $schedule['resourceId'] = "001";
            }

            // モーダル表示用
            $schedule['mdType'] = $schedule_type['type'];
            $schedule['mdTypeName'] = $schedule_type_names[(string) $schedule_type['type']]['name'];
            $schedule['mdDt'] = $schedule['lesson_date'];
            $schedule['mdStartTime'] = $schedule['start_time'];
            $schedule['mdEndTime'] = $schedule['end_time'];
            $schedule['mdTitle'] = '教師名';
            $schedule['mdFurikae'] = $schedule_type['mdFurikae'];
            $schedule['mdBtn'] = false;
            if ($schedule['lesson_date'] >= $tomorrow && $schedule['atd_status_cd'] != AppConst::ATD_STATUS_CD_2) {
                // 欠席申請のボタンを表示する
                $schedule['mdBtn'] = true;
            }

            // 不要な要素の削除
            unset($schedule['name']);
            unset($schedule['lesson_type']);
            unset($schedule['symbol']);
            unset($schedule['lesson_date']);
            unset($schedule['start_time']);
            unset($schedule['end_time']);
            unset($schedule['atd_status_cd']);
            unset($schedule['create_kind_cd']);
        }

        if ( $flg ) {
            $schedules = $regular_schedules->filter(function ($schedule) {
                return $schedule->mdFurikae == '';
            });
        } else {
            $schedules = $regular_schedules;
        }

        // 休業日の取得
        $query = RoomHoliday::query();
        $room_holiday_schedules = $query
            ->select(
                'room_holiday_id AS id',
                'holiday_date',
                'room_name AS mdClassName',
                'room_name_symbol AS symbol'
            )
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('room_holiday.roomcd', '=', 'room_names.code');
            })
            // 自分の教室のみ取得
            //->whereIn('room_holiday.roomcd', $roomcds)
            ->where('room_holiday.roomcd', $roomcd)
            // カレンダーの表示範囲で絞る
            ->whereBetween('room_holiday.holiday_date', [$start_date, $end_date])
            ->orderBy('room_holiday.holiday_date', 'asc')
            ->get();

        foreach ($room_holiday_schedules as $schedule) {

            $schedule_type = ['type' => AppConst::CODE_MASTER_21_5, 'className' => 'cal_closed', 'mdFurikae' => ''];
            $schedule['title'] = "休業日";
            $schedule['start'] = $schedule['holiday_date']->format('Y-m-d') . ' 00:00';
            $schedule['end'] = $schedule['holiday_date']->format('Y-m-d') . ' 24:00';
            $schedule['classNames'] = $schedule_type['className'];
            $schedule['resourceId'] = "000";

            // モーダル表示用
            $schedule['mdType'] = AppConst::CODE_MASTER_21_5;
            $schedule['mdTypeName'] = $schedule_type_names[(string) $schedule_type['type']]['name'];
            $schedule['mdDt'] = $schedule['holiday_date'];
            $schedule['mdStartTime'] = '';
            $schedule['mdEndTime'] = '';
            $schedule['mdTitle'] = '';
            $schedule['mdTitleVal'] = '';
            $schedule['mdSubject'] = '';
            $schedule['mdFurikae'] = $schedule_type['mdFurikae'];

            // 不要な要素の削除
            unset($schedule['holiday_date']);
        }

        $schedules = collect($schedules)->merge($room_holiday_schedules);

        if ($room_holiday_schedules->count() === 0) {
        // 時間割データ
        $pd_schedules =[
            ['title' => '<br>0時限目<br>08:00-09:00',
             'start' => $start_date . ' 08:00',
             'end' => $start_date . ' 09:00',
             'classNames' => 'cal_period',
             'textColor' => 'white',
             'resourceId' => '000'],
            ['title' => '<br>1時限目<br>09:00-10:30',
             'start' => $start_date . ' 09:00',
             'end' => $start_date . ' 10:30',
             'textColor' => 'white',
             'classNames' => 'cal_period',
             'textColor' => 'white',
             'resourceId' => '000'],
             ['title' => '<br>2時限目<br>10:45-12:15',
             'start' => $start_date . ' 10:45',
             'end' => $start_date . ' 12:15',
             'classNames' => 'cal_period',
             'textColor' => 'white',
             'resourceId' => '000'],
             ['title' => '<br>3時限目<br>13:15-14:45',
             'start' => $start_date . ' 13:15',
             'end' => $start_date . ' 14:45',
             'classNames' => 'cal_period',
             'textColor' => 'white',
             'resourceId' => '000'],
             ['title' => '<br>4時限目<br>15:00-16:30',
             'start' => $start_date . ' 15:00',
             'end' => $start_date . ' 16:30',
             'classNames' => 'cal_period',
             'textColor' => 'white',
             'resourceId' => '000'],
             ['title' => '<br>5時限目<br>16:45-18:15',
             'start' => $start_date . ' 16:45',
             'end' => $start_date . ' 18:15',
             'classNames' => 'cal_period',
             'textColor' => 'white',
             'resourceId' => '000'],
             ['title' => '<br>6時限目<br>18:30-20:00',
             'start' => $start_date . ' 18:30',
             'end' => $start_date . ' 20:00',
             'classNames' => 'cal_period',
             'textColor' => 'white',
             'resourceId' => '000'],
             ['title' => '<br>7時限目<br>20:15-21:45',
             'start' => $start_date . ' 20:15',
             'end' => $start_date . ' 21:45',
             'classNames' => 'cal_period',
             'textColor' => 'white',
             'resourceId' => '000'],
        ];
        $schedules = collect($schedules)->merge($pd_schedules);
            
        }
        return $schedules;
    }

    /**
     * スケジュール種別の取得
     *
     * @param int $schedule スケジュール
     * @return array スケジュール種別・詳細
     */
    private function getScheduleType($schedule)
    {
        $lesson_type = $schedule['lesson_type'];

        if ($lesson_type == AppConst::EXT_GENERIC_MASTER_109_0) {
            // レギュラー
            if ($schedule['create_kind_cd'] == AppConst::CREATE_KIND_CD_1 && $schedule['atd_status_cd'] != AppConst::ATD_STATUS_CD_2) {
                // 個別授業
                return [
                    'type' => AppConst::CODE_MASTER_21_1,
                    'className' => 'cal_class',
                    'mdFurikae' => ''
                ];
            } elseif ($schedule['create_kind_cd'] == AppConst::CREATE_KIND_CD_2 && $schedule['atd_status_cd'] != AppConst::ATD_STATUS_CD_2) {
                // 振替日
                return [
                    'type' => AppConst::CODE_MASTER_21_1,
                    'className' => 'cal_class_furikae',
                    'mdFurikae' => '振替日'
                ];
            } elseif ($schedule['create_kind_cd'] == AppConst::CREATE_KIND_CD_3 && $schedule['atd_status_cd'] != AppConst::ATD_STATUS_CD_2) {
                // 増コマまたは分割振替は、通常の授業と同じ表示とする
                return [
                    'type' => AppConst::CODE_MASTER_21_1,
                    'className' => 'cal_class',
                    'mdFurikae' => ''
                ];
            } elseif ($schedule['atd_status_cd'] == AppConst::ATD_STATUS_CD_2) {
                // 後日振替は共通の表示
                // 表示色を振替日と異なる色（灰色）とする
                if ($schedule['transefer_kind_cd'] == AppConst::TRANSEFER_KIND_CD_1) {
                    // 振替区分（transefer_kind_cd）= 振替日設定済の場合
                    return [
                        'type' => AppConst::CODE_MASTER_21_1,
                        'className' => 'cal_class_gojitsu_furikae',
                        'mdFurikae' => '後日振替・済'
                    ];
                } else {
                    return [
                        'type' => AppConst::CODE_MASTER_21_1,
                        'className' => 'cal_class_gojitsu_furikae',
                        'mdFurikae' => '後日振替・未'
                    ];
                }
            } else {
                // 条件に当てはまらない場合でもエラーにはしない
                return [
                    'type' => AppConst::CODE_MASTER_21_1,
                    'className' => 'cal_class',
                    'mdFurikae' => ''
                ];
            }
        } elseif ($lesson_type == AppConst::EXT_GENERIC_MASTER_109_1) {
            // 個別講習
            if ($schedule['create_kind_cd'] == AppConst::CREATE_KIND_CD_1 && $schedule['atd_status_cd'] != AppConst::ATD_STATUS_CD_2) {
                // 短期講習
                return [
                    'type' => AppConst::CODE_MASTER_21_2,
                    'className' => 'cal_tanki_koshu',
                    'mdFurikae' => ''
                ];
            } elseif ($schedule['create_kind_cd'] == AppConst::CREATE_KIND_CD_2 && $schedule['atd_status_cd'] != AppConst::ATD_STATUS_CD_2) {
                // 振替日
                return [
                    'type' => AppConst::CODE_MASTER_21_2,
                    'className' => 'cal_tanki_koshu_furikae',
                    'mdFurikae' => '振替日'
                ];
            } elseif ($schedule['create_kind_cd'] == AppConst::CREATE_KIND_CD_3 && $schedule['atd_status_cd'] != AppConst::ATD_STATUS_CD_2) {
                // 増コマまたは分割振替は、通常の授業と同じ表示とする
                return [
                    'type' => AppConst::CODE_MASTER_21_2,
                    'className' => 'cal_tanki_koshu',
                    'mdFurikae' => ''
                ];
            } elseif ($schedule['atd_status_cd'] == AppConst::ATD_STATUS_CD_2) {
                // 後日振替は共通の表示
                // 表示色を振替日と異なる色（灰色）とする
                if ($schedule['transefer_kind_cd'] == AppConst::TRANSEFER_KIND_CD_1) {
                    // 振替区分（transefer_kind_cd）= 振替日設定済の場合
                    return [
                        'type' => AppConst::CODE_MASTER_21_2,
                        'className' => 'cal_class_gojitsu_furikae',
                        'mdFurikae' => '後日振替・済'
                    ];
                } else {
                    // 振替区分（transefer_kind_cd）= 振替日未設定の場合
                    return [
                        'type' => AppConst::CODE_MASTER_21_2,
                        'className' => 'cal_class_gojitsu_furikae',
                        'mdFurikae' => '後日振替・未'
                    ];
                }
            } else {
                // 条件に当てはまらない場合でもエラーにはしない
                return [
                    'type' => AppConst::CODE_MASTER_21_2,
                    'className' => 'cal_tanki_koshu',
                    'mdFurikae' => ''
                ];
            }
        } else {
            // 個別授業と短期講習以外では呼ばれない関数だが、エラーにしないよう個別授業（通常）の形式を返す
            return [
                'type' => AppConst::CODE_MASTER_21_1,
                'className' => 'cal_class',
                'mdFurikae' => ''
            ];
        }
    }
}
