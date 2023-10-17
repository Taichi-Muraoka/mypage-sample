<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\MstCourse;
use App\Models\MstSubject;
use App\Models\CodeMaster;
use App\Models\Student;

use App\Models\Tutor;
use App\Models\MstTimetable;
use App\Models\YearlySchedule;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Auth;
use App\Libs\AuthEx;
use App\Models\ClassMember;
use App\Models\MstBooth;
use App\Models\AdminUser;

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
        $startDate = date('Y-m-d', $request->input('start') / 1000);
        $endDate = date('Y-m-d', $request->input('end') / 1000 - 1);

        // 期間区分の取得（年間授業予定） ※後で取得・設定
        // 休業日の場合、休日表示のみ返す

        // スケジュール情報の取得（生徒IDで絞り込み）
        $schedules = $this->getSchedule($startDate, $endDate, null, $sid, null);

        foreach ($schedules as $schedule) {
            $schedule['title'] = $schedule['subject_name'] . ' ' . $schedule['tutor_name'];
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
                //$schedule['transfer_before'] = $schedule['transfer_date']->format('Y-m-d') . $schedule['transfer_period_no'] . "限";
            }

            // 不要な要素の削除 ※後で見直し・設定する
            unset($schedule['campus_cd']);
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

        $this->debug($schedules);
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
        $startDate = date('Y-m-d', $request->input('start') / 1000);
        $endDate = date('Y-m-d', $request->input('end') / 1000 - 1);

        // 期間区分の取得（年間授業予定） ※後で取得・設定
        // 休業日の場合、休日表示のみ返す


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
                $schedule['title'] = $schedule['title'] . ' ' . $schedule['subject_name'];
            } else {
                // コース種別が１対多授業以外の場合 生徒名を表示
                $schedule['title'] = $schedule['title'] . ' ' . $schedule['student_name'];
            }

            // 表示色クラス・リソースID判定
            $classInfo = $this->getClassByCourse($schedule);
            // クラス名（表示色設定）
            $schedule['classNames'] = $classInfo['className'];
            // リソースID（ブースコード）
            //$schedule['resourceId'] = $classInfo['resourceId'];

            // モーダル表示用
            $schedule['hurikae_name'] = "";
            // 振替の場合、授業区分に付加する文字列を設定
            if ($schedule['create_kind'] == AppConst::CODE_MASTER_32_2) {
                $schedule['hurikae_name'] = $schedule['create_kind_name'];
                //$schedule['transfer_before'] = $schedule['transfer_date']->format('Y-m-d') . $schedule['transfer_period_no'] . "限";
            }

            // １対多授業の場合、受講生徒名を取得
            if ($schedule['course_kind'] == AppConst::CODE_MASTER_42_2) {
                $schedule['class_student_names'] = $this->getClassMembers($schedule['schedule_id']);
            }

            // 不要な要素の削除 ※後で見直し・設定する
            unset($schedule['campus_cd']);
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

        $this->debug($schedules);
        $scheduleData = collect($schedules);

        return $scheduleData;
    }

    /**
     * 教室のカレンダーを取得
     * @return object
     *
     * @param \Illuminate\Http\Request $request リクエスト
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
            $holiday =[
                ['title' => '休業日',
                 'start' => $startDate . ' 00:00',
                 'end' => $endDate . ' 23:59',
                 'classNames' => 'cal_closed',
                 'resourceId' => '000'
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
            if ($schedule['lesson_kind'] != AppConst::CODE_MASTER_31_1
                && $schedule['lesson_kind'] != AppConst::CODE_MASTER_31_2 ) {
                // 授業種別が初回・体験・追加の場合
                $schedule['title'] = $schedule['title'] . '<br><span class="class_special">' . $schedule['lesson_kind_name'] . '</span>';
                if ($schedule['create_kind'] == AppConst::CODE_MASTER_32_2) {
                    // かつ 振替の場合
                    $schedule['title'] = $schedule['title'] . ' <span class="class_special">' . $schedule['create_kind_name'] . '</span>';
                }
            } else {
                // 授業種別が通常・特別で振替の場合
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
                if ($schedule['how_to_kind'] == AppConst::CODE_MASTER_33_2
                || $schedule['how_to_kind'] == AppConst::CODE_MASTER_33_3) {
                    // 講師オンラインまたは両者オンラインの場合、アンダーライン表示
                    $schedule['title'] = $schedule['title'] . '<br>tea：' . '<span class="class_marker">' . $schedule['tutor_name'] . '</span>';
                } else {
                    $schedule['title'] = $schedule['title'] . '<br>tea：' . $schedule['tutor_name'];
                }
            }
            // タイトル_生徒名
            if ($schedule['student_name'] != "") {
                if ($schedule['how_to_kind'] == AppConst::CODE_MASTER_33_1
                || $schedule['how_to_kind'] == AppConst::CODE_MASTER_33_2) {
                    // 生徒オンラインまたは両者オンラインの場合、アンダーライン表示
                    $schedule['title'] = $schedule['title'] . '<br>stu：' . '<span class="class_marker">' . $schedule['student_name'] . '</span>';
                } else {
                    $schedule['title'] = $schedule['title'] . '<br>stu：' . $schedule['student_name'];
                }
            }
            // モーダル表示用
            $schedule['hurikae_name'] = "";
            // 振替の場合、授業区分に付加する文字列を設定
            if ($schedule['create_kind'] == AppConst::CODE_MASTER_32_2) {
                $schedule['hurikae_name'] = $schedule['create_kind_name'];
                //$schedule['transfer_before'] = $schedule['transfer_date']->format('Y-m-d') . $schedule['transfer_period_no'] . "限";
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
        $this->debug($schedules);
        $scheduleData = collect($timeTables)->merge($schedules);

        return $scheduleData;
    }

    /**
     * 校舎・日付から期間区分の取得
     *
     * @param string $campusCd 校舎コード
     * @param date $targetDate 対象日
     * @return object
     */
    private function getYearlyDateKind($campusCd, $targetDate)
    {
        $query = YearlySchedule::query();

        $account = Auth::user();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
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
     * スケジュール種別の取得
     *
     * @param int $schedule スケジュール
     * @return array スケジュール種別・詳細
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
            case AppConst::CODE_MASTER_25_6:
                $class = 'cal_ensyu';
                break;
            case AppConst::CODE_MASTER_25_7:
                $class = 'cal_highplan';
                break;
            case AppConst::CODE_MASTER_25_4:
                $class = 'cal_group';
                break;
            case AppConst::CODE_MASTER_25_0:
                $schedule['course_kind'] == AppConst::CODE_MASTER_42_3 ? 
                    $class = 'cal_meeting' : $class = 'cal_jisyu';
                break;
            default:
                $class = 'cal_class';
        }

        // 振替中・未振替の場合は退避エリア表示とする
        if ($schedule['absent_status'] == AppConst::CODE_MASTER_35_3
        || $schedule['absent_status'] == AppConst::CODE_MASTER_35_4 ) {
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
            $query->where('campus_cd', $account->campus_cd);
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
     * スケジュール情報の取得
     *
     * @param date $startDate 対象日（開始日）
     * @param date $endDate 対象日（終了日）
     * @param string $campusCd 校舎コード
     * @param string $studentId 生徒ID
     * @param string $tutorId 講師ID
     * @return object
     */
    private function getSchedule($startDate, $endDate, $campusCd = null, $studentId = null, $tutorId = null)
    {
        // SQLの表示（デバッグ用。削除してからcommit/pushすること）
        \DB::enableQueryLog();
        $query = Schedule::query();

        $account = Auth::user();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('schedules.ampus_cd', $account->campus_cd);
        }
        if ($campusCd) {
            // 校舎コードで絞り込み
            $query->where('schedules.campus_cd', $campusCd);
        }
        if ($studentId) {
            // 生徒IDで絞り込み
            // スケジュール情報に存在するかチェックする。existsを使用した
            $query->where('schedules.student_id', $studentId)
                ->orWhereExists(function ($query) use ($studentId) {
                $query->from('class_members')->whereColumn('class_members.schedule_id', 'schedules.schedule_id')
                    ->where('class_members.student_id', $studentId);
            });
        }
        if ($tutorId) {
            // 講師IDで絞り込み
            $query->where('schedules.tutor_id', $tutorId);
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
                'schedules.student_id',
                'schedules.tutor_id',
                'schedules.subject_cd',
                'mst_subjects.name as subject_name',
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
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('schedules.tutor_id', 'tutors.tutor_id');
            })
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
            // 管理者名の取得
            ->sdLeftJoin(AdminUser::class, function ($join) {
                $join->on('schedules.adm_id', 'admin_users.adm_id');
            })
            // 振替情報の取得
            ->sdLeftJoin(Schedule::class, function ($join) {
                $join->on('schedules.transfer_class_id', '=', 'transfer_schedules.schedule_id');
            }, 'transfer_schedules')
            // 振替済みスケジュールを除外
            ->where('schedules.absent_status', '!=', AppConst::CODE_MASTER_35_5)
            // カレンダーの表示範囲で絞り込み
            ->whereBetween('schedules.target_date', [$startDate, $endDate])
            ->orderBy('schedules.target_date', 'asc')
            ->orderBy('schedules.start_time', 'asc')
            ->get();
        // クエリ出力（デバッグ用。削除してからcommit/pushすること）
        $this->debug(\DB::getQueryLog());

        return $schedules;
    }

    /**
     * 受講生徒情報の取得
     *
     * @param string $scheduleId スケジュールID
     * @return object
     */
    private function getClassMembers($scheduleId)
    {
        $query = ClassMember::query();

        $account = Auth::user();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }

        // データを取得（受講生徒情報）
        $classMembers = $query
            ->select(
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

        // 取得データを配列->改行区切りの文字列に変換しセット
        $arrClassMembers = [];
        if (count($classMembers) > 0) {
            foreach ($classMembers as $classMember) {
                array_push($arrClassMembers, $classMember['student_name']);
            }
        }
        $classMembers = implode("\n", $arrClassMembers);

        return $classMembers;
    }
}
