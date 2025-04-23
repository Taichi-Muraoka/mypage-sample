<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\YearlySchedule;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Http\Controllers\Traits\FuncScheduleTrait;

/**
 * カレンダー - 機能共通処理
 */
trait FuncCalendarTrait
{
    // 機能共通処理：スケジュール関連
    use FuncScheduleTrait;

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
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $sid 生徒ID
     * @return object
     */
    private function getStudentCalendar(Request $request, $sid)
    {

        // リクエストから日付を取得(カレンダーの表示範囲)
        // MEMO: Y-m-dで比較するので、条件絞り込み対象の項目が「Date型」であることに注意(DateTimeの場合はうまく行かない)
        $startDate = date('Y-m-d', $request->input('start') / 1000);
        $endDate = date('Y-m-d', $request->input('end') / 1000 - 1);

        // 休業日の取得（年間授業予定）
        $holidays = $this->getYearlySchedule($startDate, $endDate, null, $sid, null);
        foreach ($holidays as $holiday) {
            // 開始日時
            $holiday['start'] = $holiday['target_date'];
            // タイトル
            $holiday['title'] = $holiday['room_symbol'] . ' ' . '休業日';
            // クラス名（表示色設定）
            $holiday['classNames'] = 'cal_closed';
            // モーダル表示用
            $holiday['holiday_name'] = '休業日';
        }

        // スケジュール情報の取得（生徒IDで絞り込み）
        $schedules = $this->getSchedule($startDate, $endDate, null, $sid, null);

        foreach ($schedules as $schedule) {
            if (
                $schedule['course_kind'] == AppConst::CODE_MASTER_42_1
                || $schedule['course_kind'] == AppConst::CODE_MASTER_42_2
            ) {
                // コース種別が授業の場合 科目名を表示
                $schedule['title'] = $schedule['subject_sname'];
            } else {
                // コース種別が授業以外の場合 コース名を表示
                $schedule['title'] = $schedule['course_sname'];
            }
            $schedule['title'] = $schedule['title'] . ' ' . $schedule['tutor_name'];
            $schedule['start'] = $schedule['target_date']->format('Y-m-d') . ' ' . $schedule['start_time']->format('H:i');
            $schedule['end'] = $schedule['target_date']->format('Y-m-d') . ' ' . $schedule['end_time']->format('H:i');
            // 表示色クラス・リソースID判定
            $classInfo = $this->getClassByCourse($schedule);
            // クラス名（表示色設定）
            $schedule['classNames'] = $classInfo['className'];

            // モーダル表示用
            $schedule['hurikae_name'] = "";
            // 振替の場合、授業区分に付加する文字列を設定
            if ($schedule['create_kind'] == AppConst::CODE_MASTER_32_2) {
                $schedule['hurikae_name'] = $schedule['create_kind_name'];
            }

            // １対多授業の場合
            if ($schedule['course_kind'] == AppConst::CODE_MASTER_42_2) {
                if (AuthEx::isAdmin()) {
                    // 管理者の場合、受講生徒名を取得
                    $schedule['class_student_names'] = $this->fncScheGetClassMembers($schedule['schedule_id']);
                } else {
                    // 管理者以外（生徒想定）の場合、対象生徒の出欠ステータスを取得
                    $schedule['absent_name'] = $this->fncScheGetClassMemberStatus($schedule['schedule_id'], $sid);
                }
            }

            // 不要な要素の削除
            unset($schedule['campus_cd']);
            unset($schedule['booth_cd']);
            unset($schedule['course_cd']);
            unset($schedule['student_id']);
            unset($schedule['tutor_id']);
            unset($schedule['subject_cd']);
            unset($schedule['summary_kind']);
            unset($schedule['absent_tutor_id']);
            unset($schedule['absent_status']);
            unset($schedule['report_id']);

            if (!AuthEx::isAdmin()) {
                // 管理者以外（生徒想定）の場合に表示しない項目をunset
                unset($schedule['absent_tutor_name']);
                unset($schedule['tentative_status']);
                unset($schedule['tentative_name']);
                unset($schedule['admin_name']);
                unset($schedule['memo']);
            }
        }

        $scheduleData = collect($holidays)->merge($schedules);

        return $scheduleData;
    }

    /**
     * 講師のカレンダーを取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $tid 講師ID
     * @return object
     */
    private function getTutorCalendar(Request $request, $tid)
    {

        // リクエストから日付を取得
        $startDate = date('Y-m-d', $request->input('start') / 1000);
        $endDate = date('Y-m-d', $request->input('end') / 1000 - 1);

        // 休業日の取得（年間授業予定）
        $holidays = $this->getYearlySchedule($startDate, $endDate, null, null, $tid);
        foreach ($holidays as $holiday) {
            // 開始日時
            $holiday['start'] = $holiday['target_date'];
            // タイトル
            $holiday['title'] = $holiday['room_symbol'] . ' ' . '休業日';
            // クラス名（表示色設定）
            $holiday['classNames'] = 'cal_closed';
            // モーダル表示用
            $holiday['holiday_name'] = '休業日';
        }

        // スケジュール情報の取得（講師IDで絞り込み）
        $schedules = $this->getSchedule($startDate, $endDate, null, null, $tid);

        foreach ($schedules as $schedule) {
            // 開始日時
            $schedule['start'] = $schedule['target_date']->format('Y-m-d') . ' ' . $schedule['start_time']->format('H:i');
            // 終了日時
            $schedule['end'] = $schedule['target_date']->format('Y-m-d') . ' ' . $schedule['end_time']->format('H:i');
            // タイトル
            $schedule['title'] = $schedule['room_symbol'];
            if ($schedule['course_kind'] == AppConst::CODE_MASTER_42_2) {
                // コース種別が１対多授業の場合 科目名を表示
                $schedule['title'] = $schedule['title'] . ' ' . $schedule['subject_sname'];
            } else {
                // コース種別が１対多授業以外の場合 生徒名を表示
                $schedule['title'] = $schedule['title'] . ' ' . $schedule['student_name'];
            }

            // 表示色クラス・リソースID判定
            $classInfo = $this->getClassByCourse($schedule);
            // クラス名（表示色設定）
            $schedule['classNames'] = $classInfo['className'];

            // モーダル表示用
            $schedule['hurikae_name'] = "";
            // 振替の場合、授業区分に付加する文字列を設定
            if ($schedule['create_kind'] == AppConst::CODE_MASTER_32_2) {
                $schedule['hurikae_name'] = $schedule['create_kind_name'];
            }

            // １対多授業の場合、受講生徒名を取得
            if ($schedule['course_kind'] == AppConst::CODE_MASTER_42_2) {
                $schedule['class_student_names'] = $this->fncScheGetClassMembers($schedule['schedule_id']);
            }

            // 不要な要素の削除
            unset($schedule['campus_cd']);
            unset($schedule['booth_cd']);
            unset($schedule['course_cd']);
            unset($schedule['student_id']);
            unset($schedule['tutor_id']);
            unset($schedule['subject_cd']);
            unset($schedule['summary_kind']);
            unset($schedule['absent_tutor_id']);
            unset($schedule['absent_status']);
            unset($schedule['report_id']);

            if (!AuthEx::isAdmin()) {
                // 管理者以外（講師想定）の場合に表示しない項目をunset
                unset($schedule['absent_tutor_name']);
                unset($schedule['tentative_status']);
                unset($schedule['tentative_name']);
                unset($schedule['admin_name']);
                unset($schedule['memo']);
            }
        }

        $scheduleData = collect($holidays)->merge($schedules);

        return $scheduleData;
    }

    /**
     * 教室のカレンダーを取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return object
     */
    private function getRoomCalendar(Request $request)
    {
        // リクエストから日付を取得(カレンダーの表示範囲)
        // MEMO: Y-m-dで比較するので、条件絞り込み対象の項目が「Date型」であることに注意(DateTimeの場合はうまく行かない)
        $startDate = date('Y-m-d', $request->input('start') / 1000);
        $endDate = date('Y-m-d', $request->input('end') / 1000 - 1);
        $campusCd = $request->input('campus_cd');

        // 期間区分の取得（年間授業予定）
        $dateKind = $this->fncScheGetYearlyDateKind($campusCd, $startDate);
        if ($dateKind == AppConst::CODE_MASTER_38_9) {
            // 休業日の場合、休日表示のみ返す
            $holiday = [
                [
                    'title' => '休業日',
                    'start' => $startDate . ' 00:00',
                    'end' => $endDate . ' 23:59',
                    'classNames' => 'cal_closed_room',
                    'resourceId' => config('appconf.timetable_boothId')
                ],
            ];
            return $holiday;
        }

        // 時間割情報の取得
        $timeTables = $this->fncScheGetTimetableByDate($campusCd, $startDate);

        foreach ($timeTables as $timetable) {
            $timetable['title'] = "<br>" . $timetable['period_no'] . "時限目<br>"
                . $timetable['start_time']->format('H:i') . "-" . $timetable['end_time']->format('H:i');
            $timetable['start'] = $startDate . " " . $timetable['start_time']->format('H:i');
            $timetable['end'] = $startDate . " " . $timetable['end_time']->format('H:i');
            $timetable['classNames'] = "cal_period";
            $timetable['resourceId'] = config('appconf.timetable_boothId');
            // 不要な要素の削除
            unset($timetable['period_no']);
            unset($timetable['start_time']);
            unset($timetable['end_time']);
        }

        // スケジュール情報の取得
        $schedules = $this->getSchedule($startDate, $endDate, $campusCd);

        foreach ($schedules as $schedule) {
            // 開始日時
            $schedule['start'] = $schedule['target_date']->format('Y-m-d') . ' ' . $schedule['start_time']->format('H:i');
            // 終了日時
            $schedule['end'] = $schedule['target_date']->format('Y-m-d') . ' ' . $schedule['end_time']->format('H:i');

            // 表示色クラス・リソースID判定
            $classInfo = $this->getClassByCourse($schedule);
            // クラス名（表示色設定）
            $schedule['classNames'] = $classInfo['className'];
            // リソースID（ブースコード）
            $schedule['resourceId'] = $classInfo['resourceId'];

            // タイトル_開始終了時刻
            $schedule['title'] = $schedule['start_time']->format('H:i') . '-' . $schedule['end_time']->format('H:i');
            // タイトル_強調表示
            if (
                $schedule['lesson_kind'] == AppConst::CODE_MASTER_31_3
                || $schedule['lesson_kind'] == AppConst::CODE_MASTER_31_4
                || $schedule['lesson_kind'] == AppConst::CODE_MASTER_31_5
                || $schedule['lesson_kind'] == AppConst::CODE_MASTER_31_6
                || $schedule['lesson_kind'] == AppConst::CODE_MASTER_31_7
            ) {
                // 授業種別が初回・体験・追加の場合
                $schedule['title'] = $schedule['title'] . '<br><span class="class_special">' . $schedule['lesson_kind_name'] . '</span>';
                if ($schedule['create_kind'] == AppConst::CODE_MASTER_32_2) {
                    // かつ 振替の場合
                    $schedule['title'] = $schedule['title'] . ' <span class="class_special">' . $schedule['create_kind_name'] . '</span>';
                }
            } else {
                // 授業種別が初回・体験・追加以外で振替の場合
                if ($schedule['create_kind'] == AppConst::CODE_MASTER_32_2) {
                    $schedule['title'] = $schedule['title'] . '<br><span class="class_special">' . $schedule['create_kind_name'] . '</span>';
                }
            }
            // タイトル_コース名
            $schedule['title'] = $schedule['title'] . "<br>" . $schedule['course_name'];
            // タイトル_科目名
            if ($schedule['subject_name'] != "") {
                $schedule['title'] = $schedule['title'] . "<br>" . $schedule['subject_name'];
            }
            // タイトル_講師名
            if ($schedule['tutor_name'] != "") {
                if (
                    $schedule['how_to_kind'] == AppConst::CODE_MASTER_33_2
                    || $schedule['how_to_kind'] == AppConst::CODE_MASTER_33_3
                ) {
                    // 講師オンラインまたは両者オンラインの場合、アンダーライン表示
                    $schedule['title'] = $schedule['title'] . '<br>tea：' . '<span class="class_marker">' . $schedule['tutor_name'] . '</span>';
                } else {
                    $schedule['title'] = $schedule['title'] . '<br>tea：' . $schedule['tutor_name'];
                }
            }
            // タイトル_生徒名
            if ($schedule['student_name'] != "") {
                if (
                    $schedule['how_to_kind'] == AppConst::CODE_MASTER_33_1
                    || $schedule['how_to_kind'] == AppConst::CODE_MASTER_33_2
                ) {
                    // 生徒オンラインまたは両者オンラインの場合、アンダーライン表示
                    $schedule['title'] = $schedule['title'] . '<br>stu：' . '<span class="class_marker">' . $schedule['student_name'] . '</span>';
                } else {
                    $schedule['title'] = $schedule['title'] . '<br>stu：' . $schedule['student_name'];
                }
            }
            // タイトル_仮登録フラグ（仮登録の場合のみ）
            if ($schedule['tentative_status'] == AppConst::CODE_MASTER_36_1) {
                $schedule['title'] = $schedule['title'] . '<br><span class="class_special">' . $schedule['tentative_name'] . '</span>';
            }
            // モーダル表示用
            $schedule['hurikae_name'] = "";
            // 振替の場合、授業区分に付加する文字列を設定
            if ($schedule['create_kind'] == AppConst::CODE_MASTER_32_2) {
                $schedule['hurikae_name'] = $schedule['create_kind_name'];
            }

            // １対多授業の場合、受講生徒名を取得
            if ($schedule['course_kind'] == AppConst::CODE_MASTER_42_2) {
                $schedule['class_student_names'] = $this->fncScheGetClassMembers($schedule['schedule_id']);
            }

            // 不要な要素の削除
            unset($schedule['campus_cd']);
            unset($schedule['room_symbol']);
            unset($schedule['booth_cd']);
            unset($schedule['course_cd']);
            unset($schedule['student_id']);
            unset($schedule['tutor_id']);
            unset($schedule['subject_cd']);
            unset($schedule['summary_kind']);
            unset($schedule['absent_tutor_id']);
            unset($schedule['absent_status']);
            unset($schedule['tentative_status']);
            unset($schedule['report_id']);
        }
        $scheduleData = collect($timeTables)->merge($schedules);

        return $scheduleData;
    }

    /**
     * レギュラースケジュールを取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return object
     */
    private function getRegularCalendar(Request $request)
    {
        // リクエストから日付を取得(カレンダーの表示範囲)
        // MEMO: Y-m-dで比較するので、条件絞り込み対象の項目が「Date型」であることに注意(DateTimeの場合はうまく行かない)
        $startDate = date('Y-m-d', $request->input('start') / 1000);
        $endDate = date('Y-m-d', $request->input('end') / 1000 - 1);
        $campusCd = $request->input('campus_cd');
        $dayCd = $request->input('day');

        // 時間割情報の取得（通常期間）
        $timeTables = $this->fncScheGetTimetableByKind($campusCd, AppConst::CODE_MASTER_37_0);

        foreach ($timeTables as $timetable) {
            $timetable['title'] = "<br>" . $timetable['period_no'] . "時限目<br>"
                . $timetable['start_time']->format('H:i') . "-" . $timetable['end_time']->format('H:i');
            $timetable['start'] = $startDate . " " . $timetable['start_time']->format('H:i');
            $timetable['end'] = $startDate . " " . $timetable['end_time']->format('H:i');
            $timetable['classNames'] = "cal_period";
            $timetable['resourceId'] = config('appconf.timetable_boothId');
            // 不要な要素の削除
            unset($timetable['period_no']);
            unset($timetable['start_time']);
            unset($timetable['end_time']);
        }

        // レギュラースケジュール情報の取得
        $schedules = $this->fncScheGetRegularSchedule($dayCd, $campusCd);

        foreach ($schedules as $schedule) {
            // 開始日時
            $schedule['start'] = $startDate . ' ' . $schedule['start_time']->format('H:i');
            // 終了日時
            $schedule['end'] = $endDate . ' ' . $schedule['end_time']->format('H:i');

            // 表示色クラス・リソースID判定
            $classInfo = $this->getClassByCourse($schedule);
            // クラス名（表示色設定）
            $schedule['classNames'] = $classInfo['className'];
            // リソースID（ブースコード）
            $schedule['resourceId'] = $classInfo['resourceId'];

            // タイトル_開始終了時刻
            $schedule['title'] = $schedule['start_time']->format('H:i') . '-' . $schedule['end_time']->format('H:i');
            // タイトル_コース名
            $schedule['title'] = $schedule['title'] . "<br>" . $schedule['course_name'];
            // タイトル_科目名
            if ($schedule['subject_name'] != "") {
                $schedule['title'] = $schedule['title'] . "<br>" . $schedule['subject_name'];
            }
            // タイトル_講師名
            if ($schedule['tutor_name'] != "") {
                if (
                    $schedule['how_to_kind'] == AppConst::CODE_MASTER_33_2
                    || $schedule['how_to_kind'] == AppConst::CODE_MASTER_33_3
                ) {
                    // 講師オンラインまたは両者オンラインの場合、アンダーライン表示
                    $schedule['title'] = $schedule['title'] . '<br>tea：' . '<span class="class_marker">' . $schedule['tutor_name'] . '</span>';
                } else {
                    $schedule['title'] = $schedule['title'] . '<br>tea：' . $schedule['tutor_name'];
                }
            }
            // タイトル_生徒名
            if ($schedule['student_name'] != "") {
                if (
                    $schedule['how_to_kind'] == AppConst::CODE_MASTER_33_1
                    || $schedule['how_to_kind'] == AppConst::CODE_MASTER_33_2
                ) {
                    // 生徒オンラインまたは両者オンラインの場合、アンダーライン表示
                    $schedule['title'] = $schedule['title'] . '<br>stu：' . '<span class="class_marker">' . $schedule['student_name'] . '</span>';
                } else {
                    $schedule['title'] = $schedule['title'] . '<br>stu：' . $schedule['student_name'];
                }
            }
            // モーダル表示用
            // １対多授業の場合、受講生徒名を取得
            if ($schedule['course_kind'] == AppConst::CODE_MASTER_42_2) {
                $schedule['class_student_names'] = $this->fncScheGetRegularClassMembers($schedule['regular_class_id']);
            }

            // 不要な要素の削除
            unset($schedule['campus_cd']);
            unset($schedule['room_symbol']);
            unset($schedule['booth_cd']);
            unset($schedule['course_cd']);
            unset($schedule['student_id']);
            unset($schedule['tutor_id']);
            unset($schedule['subject_cd']);
            unset($schedule['summary_kind']);
        }
        $scheduleData = collect($timeTables)->merge($schedules);

        return $scheduleData;
    }

    /**
     * スケジュール種別の取得
     *
     * @param object $schedule スケジュール
     * @return object スケジュール種別・詳細
     */
    private function getClassByCourse($schedule)
    {

        switch ($schedule['summary_kind']) {
            case AppConst::CODE_MASTER_25_1:
                $class = 'cal_class';
                break;
            case AppConst::CODE_MASTER_25_2:
                $class = 'cal_two';
                break;
            case AppConst::CODE_MASTER_25_3:
                $class = 'cal_three';
                break;
            case AppConst::CODE_MASTER_25_4:
                $class = 'cal_group';
                break;
            case AppConst::CODE_MASTER_25_5:
                $class = 'cal_home';
                break;
            case AppConst::CODE_MASTER_25_6:
                $class = 'cal_ensyu';
                break;
            case AppConst::CODE_MASTER_25_7:
                $class = 'cal_highplan';
                break;
            case AppConst::CODE_MASTER_25_0:
                $schedule['course_kind'] == AppConst::CODE_MASTER_42_3 ?
                    $class = 'cal_meeting' : $class = 'cal_jisyu';
                break;
            default:
                $class = 'cal_class';
        }

        // 振替中・未振替の場合は退避エリア表示とする
        if (
            $schedule['absent_status'] == AppConst::CODE_MASTER_35_3
            || $schedule['absent_status'] == AppConst::CODE_MASTER_35_4
        ) {
            // クラス名（表示色設定）
            $class = "cal_class_furikae";
            // リソースID固定
            $resourceId = config('appconf.transfer_boothId');;
        } else {
            // リソースID（ブースコード）
            $resourceId = $schedule['booth_cd'];
        }

        return [
            'className' => $class,
            'resourceId' => $resourceId,
        ];
    }

    /**
     * 年間授業予定（休業日情報）の取得
     *
     * @param date $startDate 対象日（開始日）
     * @param date $endDate 対象日（終了日）
     * @param string $campusCd 校舎コード
     * @param int $studentId 生徒ID
     * @param int $tutorId 講師ID
     * @return object
     */
    private function getYearlySchedule($startDate, $endDate, $campusCd = null, $studentId = null, $tutorId = null)
    {
        $query = YearlySchedule::query();

        if ($campusCd) {
            // 校舎コードで絞り込み
            $query->where('yearly_schedules.campus_cd', $campusCd);
        }
        if ($studentId) {
            // 生徒の所属校舎で絞り込み
            $this->mdlWhereRoomBySidQuery($query, YearlySchedule::class, $studentId);
        }
        if ($tutorId) {
            // 講師の所属校舎で絞り込み
            $this->mdlWhereRoomByTidQuery($query, YearlySchedule::class, $tutorId);
        }

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // 年間授業予定情報の取得
        $yearlySchedule = $query
            ->select(
                'yearly_schedules.lesson_date as target_date',
                'yearly_schedules.campus_cd',
                'room_names.room_name as room_name',
                'room_names.room_name_symbol as room_symbol',
            )
            // 校舎名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('yearly_schedules.campus_cd', 'room_names.code');
            })
            // 期間区分で絞り込み
            ->where('yearly_schedules.date_kind', AppConst::CODE_MASTER_38_9)
            // カレンダーの表示範囲で絞り込み
            ->whereBetween('yearly_schedules.lesson_date', [$startDate, $endDate])
            ->orderBy('yearly_schedules.lesson_date', 'asc')
            ->get();

        return $yearlySchedule;
    }

    /**
     * スケジュール情報の取得
     *
     * @param date $startDate 対象日（開始日）
     * @param date $endDate 対象日（終了日）
     * @param string $campusCd 校舎コード
     * @param int $studentId 生徒ID
     * @param int $tutorId 講師ID
     * @return object
     */
    private function getSchedule($startDate, $endDate, $campusCd = null, $studentId = null, $tutorId = null)
    {

        $query = Schedule::query();

        if ($campusCd) {
            // 校舎コード指定の場合、指定の校舎コードで絞り込み
            $query->where('schedules.campus_cd', $campusCd);
        }
        if ($studentId) {
            // 生徒ID指定の場合、生徒IDで絞り込み
            // スケジュール情報に存在するかチェックする。existsを使用した
            $query->where(function ($orQuery) use ($studentId) {
                $orQuery->where('schedules.student_id', $studentId)
                    ->orWhereExists(function ($query) use ($studentId) {
                        $query->from('class_members')->whereColumn('class_members.schedule_id', 'schedules.schedule_id')
                            ->where('class_members.student_id', $studentId)
                            // delete_dt条件の追加
                            ->whereNull('class_members.deleted_at');
                    });
            });
        }
        if ($tutorId) {
            // 講師ID指定の場合、講師IDで絞り込み
            $query->where('schedules.tutor_id', $tutorId);
        }

        if (AuthEx::isStudent() || AuthEx::isTutor()) {
            // アカウントが生徒・講師の場合、仮登録のデータを除外
            $query->where('schedules.tentative_status', "!=", AppConst::CODE_MASTER_36_1);
        }

        // スケジュール情報表示用のquery作成（select句・join句）
        $schedules = $this->fncScheGetScheduleQuery($query)
            // カレンダーの表示範囲で絞り込み
            ->whereBetween('schedules.target_date', [$startDate, $endDate])
            ->orderBy('schedules.target_date', 'asc')
            ->orderBy('schedules.start_time', 'asc')
            ->get();

        return $schedules;
    }
}
