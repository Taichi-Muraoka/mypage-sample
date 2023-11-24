<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\RegularClass;
use App\Models\RegularClassMember;
use App\Models\CodeMaster;
use App\Models\MstBooth;
use App\Models\MstCourse;
use App\Models\MstSubject;
use App\Models\MstTimetable;
use App\Models\YearlySchedule;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\AdminUser;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Auth;
use App\Libs\AuthEx;

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
                    $schedule['class_student_names'] = $this->getClassMembers($schedule['schedule_id']);
                } else {
                    // 管理者以外（生徒想定）の場合、対象生徒の出欠ステータスを取得
                    $schedule['absent_name'] = $this->getClassMemberStatus($schedule['schedule_id'], $sid);
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
                $schedule['class_student_names'] = $this->getClassMembers($schedule['schedule_id']);
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
        $dateKind = $this->getYearlyDateKind($campusCd, $startDate);
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
        $timeTables = $this->getTimetableByDate($campusCd, $startDate);

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
                $schedule['class_student_names'] = $this->getClassMembers($schedule['schedule_id']);
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
        $timeTables = $this->getTimetableByKind($campusCd, AppConst::CODE_MASTER_37_0);

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
        $schedules = $this->getRegularSchedule($dayCd, $campusCd);

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
                $schedule['class_student_names'] = $this->getRegularClassMembers($schedule['regular_class_id']);
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
     * 校舎・日付から期間区分の取得
     *
     * @param string $campusCd 校舎コード
     * @param date $targetDate 対象日
     * @return int
     */
    private function getYearlyDateKind($campusCd, $targetDate)
    {
        $query = YearlySchedule::query();

        $account = Auth::user();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('yearly_schedules.campus_cd', $account->campus_cd);
        }

        $yearlySchedule = $query
            ->select(
                'date_kind'
            )
            // 指定校舎で絞り込み
            ->where('yearly_schedules.campus_cd', $campusCd)
            // 指定日付で絞り込み
            ->where('yearly_schedules.lesson_date', $targetDate)
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        return $yearlySchedule->date_kind;
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
     * 校舎・日付から時間割情報の取得
     *
     * @param string $campusCd 校舎コード
     * @param date $targetDate 対象日
     * @return object
     */
    private function getTimetableByDate($campusCd, $targetDate)
    {
        $query = MstTimetable::query();

        $account = Auth::user();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('mst_timetables.campus_cd', $account->campus_cd);
        }

        $timeTables = $query
            ->select(
                'period_no',
                'start_time',
                'end_time',
            )
            // 年間予定情報とJOIN
            ->sdJoin(YearlySchedule::class, function ($join) use ($targetDate) {
                $join->on('mst_timetables.campus_cd', 'yearly_schedules.campus_cd')
                    ->where('yearly_schedules.lesson_date', $targetDate);
            })
            // 期間区分
            ->sdJoin(CodeMaster::class, function ($join) {
                $join->on('yearly_schedules.date_kind', '=', 'mst_codes.code')
                    ->on('mst_timetables.timetable_kind', '=', 'mst_codes.sub_code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_38);
            })
            // 指定校舎で絞り込み
            ->where('mst_timetables.campus_cd', $campusCd)
            ->orderby('period_no')
            ->get();

        return $timeTables;
    }

    /**
     * 校舎・時間割区分から時間割情報の取得
     *
     * @param string $campusCd 校舎コード
     * @param int $timetableKind 時間割区分
     * @return object
     */
    private function getTimetableByKind($campusCd, $timetableKind)
    {
        $query = MstTimetable::query();

        $account = Auth::user();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('mst_timetables.campus_cd', $account->campus_cd);
        }

        $timeTables = $query
            ->select(
                'period_no',
                'start_time',
                'end_time',
            )
            // 指定校舎で絞り込み
            ->where('mst_timetables.campus_cd', $campusCd)
            // 時間割区分で絞り込み
            ->where('mst_timetables.timetable_kind', $timetableKind)
            ->orderby('period_no')
            ->get();

        return $timeTables;
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
                            ->where('class_members.student_id', $studentId);
                    });
            });
        }
        if ($tutorId) {
            // 講師ID指定の場合、講師IDで絞り込み
            $query->where('schedules.tutor_id', $tutorId);
        }

        if (AuthEx::isStudent() || AuthEx::isTutor()) {
            // アカウントが生徒・講師の場合、仮登録のデータを除外
            $this->debug("not admin!!");
            $query->where('schedules.tentative_status', "!=", AppConst::CODE_MASTER_36_1);
        }

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // スケジュール情報の取得
        $schedules = $query
            ->select(
                'schedules.schedule_id',
                'schedules.campus_cd',
                'room_names.room_name as room_name',
                'room_names.room_name_symbol as room_symbol',
                'schedules.target_date',
                'schedules.period_no',
                'schedules.start_time',
                'schedules.end_time',
                'schedules.booth_cd',
                'mst_booths.name as booth_name',
                'schedules.course_cd',
                'mst_courses.course_kind',
                'mst_courses.summary_kind',
                'mst_courses.name as course_name',
                'mst_courses.short_name as course_sname',
                'schedules.student_id',
                'schedules.tutor_id',
                'schedules.subject_cd',
                'mst_subjects.name as subject_name',
                'mst_subjects.short_name as subject_sname',
                'schedules.lesson_kind',
                'mst_codes_31.name as lesson_kind_name',
                'schedules.create_kind',
                'mst_codes_32.name as create_kind_name',
                'schedules.how_to_kind',
                'mst_codes_33.name as how_to_kind_name',
                'schedules.substitute_kind',
                'mst_codes_34.name as substitute_kind_name',
                'schedules.absent_tutor_id',
                'org_tutors.name as tutor_name',
                'absent_tutors.name as absent_tutor_name',
                'students.name as student_name',
                'schedules.absent_status',
                'mst_codes_35.name as absent_name',
                'schedules.tentative_status',
                'mst_codes_36.name as tentative_name',
                'transfer_schedules.target_date as transfer_date',
                'transfer_schedules.period_no as transfer_period_no',
                'admin_users.name as admin_name',
                'schedules.memo'
            )
            // 校舎名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('schedules.campus_cd', 'room_names.code');
            })
            // 生徒名の取得
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('schedules.student_id', 'students.student_id');
            })
            // 講師名取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('schedules.tutor_id', '=', 'org_tutors.tutor_id');
            }, 'org_tutors')
            // 欠席講師名取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('schedules.absent_tutor_id', '=', 'absent_tutors.tutor_id');
            }, 'absent_tutors')
            // 科目名の取得
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('schedules.subject_cd', 'mst_subjects.subject_cd');
            })
            // ブース名の取得
            ->sdLeftJoin(MstBooth::class, function ($join) {
                $join->on('schedules.booth_cd', 'mst_booths.booth_cd');
            })
            // コース情報の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', 'mst_courses.course_cd');
            })
            // 授業区分名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.lesson_kind', '=', 'mst_codes_31.code')
                    ->where('mst_codes_31.data_type', AppConst::CODE_MASTER_31);
            }, 'mst_codes_31')
            // データ作成区分名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.create_kind', '=', 'mst_codes_32.code')
                    ->where('mst_codes_32.data_type', AppConst::CODE_MASTER_32);
            }, 'mst_codes_32')
            // 通塾種別名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.how_to_kind', '=', 'mst_codes_33.code')
                    ->where('mst_codes_33.data_type', AppConst::CODE_MASTER_33);
            }, 'mst_codes_33')
            // 代講種別名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.substitute_kind', '=', 'mst_codes_34.code')
                    ->where('mst_codes_34.data_type', AppConst::CODE_MASTER_34);
            }, 'mst_codes_34')
            // 出欠ステータス名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.absent_status', '=', 'mst_codes_35.code')
                    ->where('mst_codes_35.data_type', AppConst::CODE_MASTER_35);
            }, 'mst_codes_35')
            // 仮登録フラグ名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.tentative_status', '=', 'mst_codes_36.code')
                    ->where('mst_codes_36.data_type', AppConst::CODE_MASTER_36);
            }, 'mst_codes_36')
            // 管理者名の取得
            ->sdLeftJoin(AdminUser::class, function ($join) {
                $join->on('schedules.adm_id', 'admin_users.adm_id');
            })
            // 振替情報の取得
            ->sdLeftJoin(Schedule::class, function ($join) {
                $join->on('schedules.transfer_class_id', '=', 'transfer_schedules.schedule_id');
            }, 'transfer_schedules')
            // 振替済・リセット済スケジュールを除外
            ->whereNotIn('schedules.absent_status', [AppConst::CODE_MASTER_35_5, AppConst::CODE_MASTER_35_7])
            // カレンダーの表示範囲で絞り込み
            ->whereBetween('schedules.target_date', [$startDate, $endDate])
            ->orderBy('schedules.target_date', 'asc')
            ->orderBy('schedules.start_time', 'asc')
            ->get();

        return $schedules;
    }

    /**
     * 受講生徒情報の取得
     *
     * @param int $scheduleId スケジュールID
     * @return string
     */
    private function getClassMembers($scheduleId)
    {
        $query = ClassMember::query();

        // データを取得（受講生徒情報）
        $classMembers = $query
            ->select(
                'students.name as student_name',
                'class_members.absent_status',
                'mst_codes.gen_item1 as absent_name',
                'students.name_kana'
            )
            // 生徒名の取得
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('class_members.student_id', 'students.student_id');
            })
            // 出欠ステータス名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('class_members.absent_status', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_35);
            })
            // スケジュールIDを指定
            ->where('schedule_id', $scheduleId)
            ->orderBy('absent_status')->orderBy('name_kana')
            ->get();

        // 取得データを配列->改行区切りの文字列に変換しセット
        $arrClassMembers = [];
        if (count($classMembers) > 0) {
            foreach ($classMembers as $classMember) {
                if ($classMember['absent_status'] == AppConst::CODE_MASTER_35_6) {
                    // 出席ステータスが欠席の場合、名前の後ろにステータス略称を付加
                    array_push($arrClassMembers, $classMember['student_name'] . " (" . $classMember['absent_name'] . ")");
                } else {
                    array_push($arrClassMembers, $classMember['student_name']);
                }
            }
        }
        $strClassMembers = implode("\n", $arrClassMembers);

        return $strClassMembers;
    }

    /**
     * 受講生徒出欠情報の取得
     *
     * @param int $scheduleId スケジュールID
     * @param int $sid 生徒ID
     * @return string
     */
    private function getClassMemberStatus($scheduleId, $sid)
    {
        $query = ClassMember::query();

        // データを取得（受講生徒情報）
        $classMember = $query
            ->select(
                'mst_codes.name as absent_name'
            )
            // 出欠ステータス名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('class_members.absent_status', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_35);
            })
            // スケジュールIDを指定
            ->where('schedule_id', $scheduleId)
            // 生徒IDを指定
            ->where('student_id', $sid)
            ->firstOrFail();

        return $classMember->absent_name;
    }

    /**
     * レギュラースケジュール情報の取得
     *
     * @param int $dayCd 曜日コード
     * @param string $campusCd 校舎コード
     * @param int $studentId 生徒ID
     * @param int $tutorId 講師ID
     * @return object
     */
    private function getRegularSchedule($dayCd, $campusCd = null, $studentId = null, $tutorId = null)
    {

        $query = RegularClass::query();

        if ($campusCd) {
            // 校舎コード指定の場合、指定の校舎コードで絞り込み
            $query->where('regular_classes.campus_cd', $campusCd);
        }
        if ($studentId) {
            // 生徒ID指定の場合、生徒IDで絞り込み
            // スケジュール情報に存在するかチェックする。existsを使用した
            $query->where('regular_classes.student_id', $studentId)
                ->orWhereExists(function ($query) use ($studentId) {
                    $query->from('regular_class_members')->whereColumn('regular_class_members.schedule_id', 'regular_classes.schedule_id')
                        ->where('regular_class_members.student_id', $studentId);
                });
        }
        if ($tutorId) {
            // 講師ID指定の場合、講師IDで絞り込み
            $query->where('regular_classes.tutor_id', $tutorId);
        }

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // スケジュール情報の取得
        $schedules = $query
            ->select(
                'regular_classes.regular_class_id',
                'regular_classes.campus_cd',
                'room_names.room_name as room_name',
                'regular_classes.day_cd',
                'mst_codes_16.name as day_name',
                'regular_classes.period_no',
                'regular_classes.start_time',
                'regular_classes.end_time',
                'regular_classes.booth_cd',
                'mst_booths.name as booth_name',
                'regular_classes.course_cd',
                'mst_courses.course_kind',
                'mst_courses.summary_kind',
                'mst_courses.name as course_name',
                'mst_courses.short_name as course_sname',
                'regular_classes.student_id',
                'regular_classes.tutor_id',
                'regular_classes.subject_cd',
                'mst_subjects.name as subject_name',
                'mst_subjects.short_name as subject_sname',
                'regular_classes.how_to_kind',
                'mst_codes_33.name as how_to_kind_name',
                'tutors.name as tutor_name',
                'students.name as student_name'
            )
            // 校舎名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('regular_classes.campus_cd', 'room_names.code');
            })
            // 生徒名の取得
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('regular_classes.student_id', 'students.student_id');
            })
            // 講師名取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('regular_classes.tutor_id', '=', 'tutors.tutor_id');
            })
            // 科目名の取得
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('regular_classes.subject_cd', 'mst_subjects.subject_cd');
            })
            // ブース名の取得
            ->sdLeftJoin(MstBooth::class, function ($join) {
                $join->on('regular_classes.booth_cd', 'mst_booths.booth_cd');
            })
            // コース情報の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('regular_classes.course_cd', 'mst_courses.course_cd');
            })
            // 通塾種別名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('regular_classes.how_to_kind', '=', 'mst_codes_33.code')
                    ->where('mst_codes_33.data_type', AppConst::CODE_MASTER_33);
            }, 'mst_codes_33')
            // 曜日名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('regular_classes.day_cd', '=', 'mst_codes_16.code')
                    ->where('mst_codes_16.data_type', AppConst::CODE_MASTER_16);
            }, 'mst_codes_16')
            // 曜日コードで絞り込み
            ->where('regular_classes.day_cd', $dayCd)
            ->orderBy('regular_classes.start_time', 'asc')
            ->get();

        return $schedules;
    }

    /**
     * レギュラー受講生徒情報の取得
     *
     * @param int $regularClassId レギュラークラスID
     * @return string
     */
    private function getRegularClassMembers($regularClassId)
    {
        $query = RegularClassMember::query();

        // データを取得（レギュラー受講生徒情報）
        $classMembers = $query
            ->select(
                'students.name as student_name',
                'students.name_kana'
            )
            // 生徒名の取得
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('regular_class_members.student_id', 'students.student_id');
            })
            // レギュラークラスIDを指定
            ->where('regular_class_id', $regularClassId)
            ->orderBy('name_kana')
            ->get();

        // 取得データを配列->改行区切りの文字列に変換しセット
        $arrClassMembers = [];
        if (count($classMembers) > 0) {
            foreach ($classMembers as $classMember) {
                array_push($arrClassMembers, $classMember['student_name']);
            }
        }
        $strClassMembers = implode("\n", $arrClassMembers);

        return $strClassMembers;
    }
}
