<?php

namespace App\Http\Controllers\Traits;

use App\Models\YearlySchedule;
use App\Models\MstTimetable;
use App\Models\MstCourse;
use App\Models\MstBooth;
use App\Models\MstSubject;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\RegularClass;
use App\Models\RegularClassMember;
use App\Models\Student;
use App\Models\StudentCampus;
use App\Models\Tutor;
use App\Models\TutorCampus;
use App\Models\TutorFreePeriod;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;

/**
 * スケジュール関連 - 共通処理
 */
trait FuncScheduleTrait
{

    //==========================
    // 関数名を区別するために
    // fncScheを先頭につける
    //==========================

    //------------------------------
    // データ取得系(query)
    // select句・join句を設定する
    //------------------------------
    /**
     * スケジュール情報表示用のquery作成
     * 個別のwhere条件・ガードは呼び元で行うこと
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    private function fncScheGetScheduleQuery($query)
    {
        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // スケジュール情報の取得
        return $query
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
                'schedules.report_id',
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
                $join->on('schedules.campus_cd', 'mst_booths.campus_cd');
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
            ->whereNotIn('schedules.absent_status', [AppConst::CODE_MASTER_35_5, AppConst::CODE_MASTER_35_7]);
    }

    //------------------------------
    // データ取得系
    //------------------------------
    /**
     * 受講生徒情報の取得
     *
     * @param int $scheduleId スケジュールID
     * @return string
     */
    private function fncScheGetClassMembers($scheduleId)
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
    private function fncScheGetClassMemberStatus($scheduleId, $sid)
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
    private function fncScheGetRegularSchedule($dayCd, $campusCd = null, $studentId = null, $tutorId = null)
    {

        $query = RegularClass::query();

        if ($campusCd) {
            // 校舎コード指定の場合、指定の校舎コードで絞り込み
            $query->where('regular_classes.campus_cd', $campusCd);
        }
        if ($studentId) {
            // 生徒ID指定の場合、生徒IDで絞り込み
            // レギュラースケジュール情報に存在するかチェックする。existsを使用した
            $query->where(function ($orQuery) use ($studentId) {
                $orQuery->where('regular_classes.student_id', $studentId)
                    ->orWhereExists(function ($query) use ($studentId) {
                        $query->from('regular_class_members')->whereColumn('regular_class_members.schedule_id', 'regular_classes.schedule_id')
                            ->where('regular_class_members.student_id', $studentId)
                            // delete_dt条件の追加
                            ->whereNull('regular_class_members.deleted_at');
                    });
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
                $join->on('regular_classes.campus_cd', 'mst_booths.campus_cd');
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
    private function fncScheGetRegularClassMembers($regularClassId)
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

    /**
     * コース情報の取得
     *
     * @param string $courseCd コースコード
     * @return object
     */
    private function fncScheGetCourseInfo($courseCd)
    {
        // コース情報を取得
        $query = MstCourse::query();
        $course = $query
            ->select('name as course_name', 'course_kind')
            ->where('course_cd', $courseCd)
            ->firstOrFail();

        return $course;
    }

    /**
     * コース情報の取得（コース種別・給与算出種別から）
     *
     * @param string $courseCd コースコード
     * @return object
     */
    private function fncScheGetCourseInfoByKind($courseKind, $summaryKind)
    {
        // コース情報を取得
        $query = MstCourse::query();
        $course = $query
            ->select('course_cd')
            ->where('course_kind', $courseKind)
            ->where('summary_kind', $summaryKind)
            ->firstOrFail();

        return $course;
    }

    /**
     * 生徒情報の取得
     *
     * @param string $courseCd コースコード
     * @return object
     */
    private function fncScheGetStudentInfo($studentId)
    {
        // 生徒情報を取得
        $query = Student::query();
        $student = $query
            ->where('student_id', $studentId)
            ->firstOrFail();

        return $student;
    }

    /**
     * 年間予定から日付の取得（スケジュール生成用・開始日から同一曜日）
     *
     * @param string $campusCd 校舎コード
     * @param string $startDate 取得範囲開始日付
     * @param string $kaisu 取得件数
     * @param string $endDate 取得範囲終了日付
     * @return array
     */
    private function fncScheGetScheduleDate($campusCd, $startDate, $kaisu, $endDate = null)
    {
        // 年間予定情報から曜日コードを取得
        $query = YearlySchedule::query();

        $account = Auth::user();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('yearly_schedules.campus_cd', $account->campus_cd);
        }
        $targetDate = $query
            ->select('lesson_date', 'day_cd')
            ->where('campus_cd', $campusCd)
            ->where('lesson_date', $startDate)
            ->firstOrFail();

        // 年間予定情報から指定曜日の日付を取得
        $arrLessenDate = $this->fncScheGetScheduleDateByDayCd($campusCd, $targetDate->day_cd, $startDate, $kaisu, $endDate);

        return $arrLessenDate;
    }

    /**
     * 年間予定から日付の取得（スケジュール生成用・曜日指定）
     *
     * @param string $campusCd 校舎コード
     * @param string $dayCd 曜日コード
     * @param string $startDate 取得範囲開始日付
     * @param string $kaisu 取得件数
     * @param string $endDate 取得範囲終了日付
     * @return array
     */
    private function fncScheGetScheduleDateByDayCd($campusCd, $dayCd, $startDate, $kaisu, $endDate = null)
    {
        $account = Auth::user();

        // 年間予定情報から同一曜日の日付を指定件数分取得
        $query = YearlySchedule::query();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('yearly_schedules.campus_cd', $account->campus_cd);
        }
        // 取得範囲終了日が指定された場合絞り込み
        $query->when($endDate, function ($query) use ($endDate) {
            return $query->where('lesson_date', "<=", $endDate);
        });
        // 取得件数が指定された場合
        $query->when($kaisu, function ($query) use ($kaisu) {
            return $query->limit($kaisu);
        });

        $lessonDates = $query
            ->select('lesson_date')
            ->where('campus_cd', $campusCd)
            ->where('day_cd', $dayCd)
            // $startDateを含む
            ->where('lesson_date', ">=", $startDate)
            // 休日は除外する
            ->where('date_kind', "<>", AppConst::CODE_MASTER_38_9)
            ->orderBy('lesson_date')
            ->get();

        // 配列に格納
        $arrLessenDate = [];
        foreach ($lessonDates as $lessonDate) {
            array_push($arrLessenDate, $lessonDate->lesson_date->format('Y-m-d'));
        }

        return $arrLessenDate;
    }

    /**
     * 校舎・日付から期間区分の取得
     *
     * @param string $campusCd 校舎コード
     * @param date $targetDate 対象日
     * @return int
     */
    private function fncScheGetYearlyDateKind($campusCd, $targetDate)
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
     * 校舎・日付から時間割区分の取得
     *
     * @param string $campusCd 校舎コード
     * @param date $targetDate 対象日
     * @return object
     */
    private function fncScheGetTimeTableKind($campusCd, $targetDate)
    {
        $query = MstTimetable::query();

        $account = Auth::user();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('mst_timetables.campus_cd', $account->campus_cd);
        }
        // 年間予定情報とJOIN
        $query->sdJoin(YearlySchedule::class, function ($join) use ($targetDate) {
            $join->on('mst_timetables.campus_cd', 'yearly_schedules.campus_cd')
                ->where('yearly_schedules.lesson_date', $targetDate);
        })
            // 期間区分
            ->sdJoin(CodeMaster::class, function ($join) {
                $join->on('yearly_schedules.date_kind', '=', 'mst_codes.code')
                    ->on('mst_timetables.timetable_kind', '=', 'mst_codes.sub_code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_38);
            })
            // 校舎は指定されている前提として絞り込み
            ->where('mst_timetables.campus_cd', '=', $campusCd);

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
    private function fncScheGetTimetableByKind($campusCd, $timetableKind)
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
     * 校舎・時間割区分・指定時刻から時間割情報の取得
     *
     * @param string $campusCd 校舎コード
     * @param int $timetableKind 時間割区分
     * @param int $time 指定時刻
     * @return object
     */
    private function fncScheGetPeriodTime($campusCd, $timetableKind, $time)
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

    /**
     * 校舎・時間割区分・指定時刻から時限の取得（移行バッチ用）
     *
     * @param string $campusCd 校舎コード
     * @param int $timetableKind 時間割区分
     * @param int $time 指定時刻
     * @return integer 時限
     */
    private function fncScheGetPeriodTimeForBatch($campusCd, $timetableKind, $time)
    {
        $timeTable = MstTimetable::query()
            // 指定校舎で絞り込み
            ->where('campus_cd', $campusCd)
            // 時間割区分で絞り込み
            ->where('timetable_kind', $timetableKind)
            // 時間から対象時限を絞り込み
            ->where('start_time', '<=', $time)
            ->orderBy('period_no', 'desc')
            ->first();

        if (isset($timeTable)) {
            $periodNo = $timeTable->period_no;
        } else {
            // 時間割マスタに対象校舎の時間割が登録されていない場合
            // 校舎コード先頭（久我山校）の時間割情報を取得する
            $timeTable2 = MstTimetable::query()
            // 指定校舎絞り込みなし
            // 時間割区分で絞り込み
            ->where('timetable_kind', $timetableKind)
            // 時間から対象時限を絞り込み
            ->where('start_time', '<=', $time)
            // 校舎コードの昇順
            ->orderBy('campus_cd', 'asc')->orderBy('period_no', 'desc')
            ->first();

            if (isset($timeTable2)) {
                $periodNo = $timeTable2->period_no;
            } else {
                $periodNo = 1;
            }
        }

        return $periodNo;
    }

    /**
     * 校舎・時間割区分・時限から時間割情報の取得
     *
     * @param string $campusCd 校舎コード
     * @param int $timetableKind 時間割区分
     * @param int $periodNo 時限
     * @return object
     */
    private function fncScheGetTimetableByPeriod($campusCd, $timetableKind, $periodNo)
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
            // 時限で絞り込み
            ->where('period_no', $periodNo)
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        return $timeTable;
    }

    /**
     * 校舎・日付から時間割情報の取得
     *
     * @param string $campusCd 校舎コード
     * @param date $targetDate 対象日
     * @return object
     */
    private function fncScheGetTimetableByDate($campusCd, $targetDate)
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
     * 校舎・日付・時限から時間割時刻の取得
     *
     * @param string $campusCd   校舎コード
     * @param date   $targetDate 対象日
     * @param int    $periodNo  時限
     * @return object   開始時刻、終了時刻
     */
    private function fncScheGetTimetableByDatePeriod($campusCd, $targetDate, $periodNo)
    {
        $query = MstTimetable::query();

        $account = Auth::user();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('mst_timetables.campus_cd', $account->campus_cd);
        }

        $timeTables = $query
            ->select(
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
            ->where('mst_timetables.period_no', $periodNo)
            ->firstOrFail();

        return $timeTables;
    }

    //------------------------------
    // チェック処理系
    //------------------------------

    /**
     * 生徒のスケジュール重複チェック
     *
     * @param string $targetDate 対象日付
     * @param string $startTime 開始時刻
     * @param string $endTime 終了時刻
     * @param string $studentId 生徒ID
     * @param string $scheduleId スケジュールID
     * @param bool $exceptRegular レギュラー授業除外（一括登録用・省略可）
     * @return array
     */
    private function fncScheChkDuplidateSid($targetDate, $startTime, $endTime, $studentId, $scheduleId = null, $exceptRegular = false)
    {
        // スケジュール情報から対象生徒のスケジュールが登録されているか検索
        $query = Schedule::query();

        // 変更時は更新中のキー以外を検索
        if ($scheduleId) {
            $query->where('schedules.schedule_id', '!=', $scheduleId);
        }
        // レギュラー授業除外時はデータ作成区分「一括」以外を検索
        if ($exceptRegular) {
            $query->where('create_kind', '!=', AppConst::CODE_MASTER_32_0);
        }

        $exists = $query
            // 受講生徒情報とJOIN
            ->sdLeftJoin(ClassMember::class, function ($join) {
                $join->on('schedules.schedule_id', '=', 'class_members.schedule_id');
            })
            // 生徒所属情報とJOIN
            ->sdJoin(StudentCampus::class, function ($join) use ($studentId) {
                $join->on('schedules.campus_cd', '=', 'student_campuses.campus_cd')
                    ->where('student_campuses.student_id', $studentId);
            })
            // 日付・開始時刻・終了時刻・生徒IDで絞り込み
            ->where('target_date', $targetDate)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->where(function ($orQuery) use ($studentId) {
                $orQuery
                    ->where('schedules.student_id', $studentId)
                    ->orWhere('class_members.student_id', $studentId);
            })
            // 未振替・振替中・振替済・リセット済スケジュールを除外
            ->whereNotIn('schedules.absent_status', [AppConst::CODE_MASTER_35_3, AppConst::CODE_MASTER_35_4, AppConst::CODE_MASTER_35_5, AppConst::CODE_MASTER_35_7])
            ->exists();

        if ($exists) {
            // 重複ありの場合
            return false;
        }
        return true;
    }

    /**
     * 生徒のスケジュール重複チェック（レギュラー授業）
     *
     * @param string $dayCd 曜日コード
     * @param string $startTime 開始時刻
     * @param string $endTime 終了時刻
     * @param string $studentId 生徒ID
     * @param int $regularClassId レギュラー授業ID
     * @return array
     */
    private function fncScheChkDuplidateSidRegular($dayCd, $startTime, $endTime, $studentId, $regularClassId = null)
    {
        // スケジュール情報から対象生徒のスケジュールが登録されているか検索
        $query = RegularClass::query();

        // 変更時は更新中のキー以外を検索
        if ($regularClassId) {
            $query->where('regular_classes.regular_class_id', '!=', $regularClassId);
        }

        $exists = $query
            // 受講生徒情報とJOIN
            ->sdLeftJoin(RegularClassMember::class, function ($join) {
                $join->on('regular_classes.regular_class_id', '=', 'regular_class_members.regular_class_id');
            })
            // 生徒所属情報とJOIN
            ->sdJoin(StudentCampus::class, function ($join) use ($studentId) {
                $join->on('regular_classes.campus_cd', '=', 'student_campuses.campus_cd')
                    ->where('student_campuses.student_id', $studentId);
            })
            // 曜日・開始時刻・終了時刻・生徒IDで絞り込み
            ->where('day_cd', $dayCd)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->where(function ($orQuery) use ($studentId) {
                $orQuery
                    ->where('regular_classes.student_id', $studentId)
                    ->orWhere('regular_class_members.student_id', $studentId);
            })
            ->exists();

        if ($exists) {
            // 重複ありの場合
            return false;
        }
        return true;
    }

    /**
     * 講師のスケジュール重複チェック（スケジュール情報）
     *
     * @param string $targetDate 対象日付
     * @param string $startTime 開始時刻
     * @param string $endTime 終了時刻
     * @param int $tutorId 講師ID
     * @param int $scheduleId スケジュールID
     * @param bool $exceptRegular レギュラー授業除外（一括登録用・省略可）
     * @return bool
     */
    private function fncScheChkDuplidateTid($targetDate, $startTime, $endTime, $tutorId, $scheduleId = null, $exceptRegular = false)
    {
        // スケジュール情報から対象講師のスケジュールが登録されているか検索
        $query = Schedule::query();

        // 変更時は更新中のキー以外を検索
        if ($scheduleId) {
            $query->where('schedule_id', '!=', $scheduleId);
        }
        // レギュラー授業除外時はデータ作成区分「一括」以外を検索
        if ($exceptRegular) {
            $query->where('create_kind', '!=', AppConst::CODE_MASTER_32_0);
        }

        $exists = $query
            // 講師所属情報とJOIN
            ->sdJoin(TutorCampus::class, function ($join) use ($tutorId) {
                $join->on('schedules.campus_cd', '=', 'tutor_campuses.campus_cd')
                    ->where('tutor_campuses.tutor_id', $tutorId);
            })
            // 日付・開始時刻・終了時刻・講師IDで絞り込み
            ->where('schedules.target_date', $targetDate)
            ->where('schedules.tutor_id', $tutorId)
            // 未振替・振替中・振替済・リセット済スケジュールを除外
            ->whereNotIn('schedules.absent_status', [AppConst::CODE_MASTER_35_3, AppConst::CODE_MASTER_35_4, AppConst::CODE_MASTER_35_5, AppConst::CODE_MASTER_35_7])
            ->where('schedules.start_time', '<', $endTime)
            ->where('schedules.end_time', '>', $startTime)
            ->exists();

        if ($exists) {
            // 重複ありの場合
            return false;
        }
        return true;
    }

    /**
     * 講師のスケジュール重複チェック（レギュラー授業）
     *
     * @param string $dayCd 曜日コード
     * @param string $startTime 開始時刻
     * @param string $endTime 終了時刻
     * @param int $tutorId 講師ID
     * @param int $regularClassId レギュラー授業ID
     * @return bool
     */
    private function fncScheChkDuplidateTidRegular($dayCd, $startTime, $endTime, $tutorId, $regularClassId = null)
    {
        // スケジュール情報から対象生徒のスケジュールが登録されているか検索
        $query = RegularClass::query();

        // 変更時は更新中のキー以外を検索
        if ($regularClassId) {
            $query->where('regular_classes.regular_class_id', '!=', $regularClassId);
        }

        $exists = $query
            // 講師所属情報とJOIN
            ->sdJoin(TutorCampus::class, function ($join) use ($tutorId) {
                $join->on('regular_classes.campus_cd', '=', 'tutor_campuses.campus_cd')
                    ->where('tutor_campuses.tutor_id', $tutorId);
            })
            // 曜日・開始時刻・終了時刻・講師IDで絞り込み
            ->where('regular_classes.day_cd', $dayCd)
            ->where('regular_classes.tutor_id', $tutorId)
            ->where('regular_classes.start_time', '<', $endTime)
            ->where('regular_classes.end_time', '>', $startTime)
            ->exists();

        if ($exists) {
            // 重複ありの場合
            return false;
        }
        return true;
    }

    /**
     * ブースのチェック・空きブース取得（面談以外）
     *
     * @param string $campusCd 校舎コード
     * @param string $boothCd ブースコード
     * @param string $targetDate 対象日付
     * @param int $periodNo 時限
     * @param int $howToKind 通塾種別
     * @param int $scheduleId スケジュールID
     * @param bool $checkOnly 重複チェックのみ
     * @return string ブースコード（空き無し時にはnull）
     */
    private function fncScheSearchBooth($campusCd, $boothCd, $targetDate, $periodNo, $howToKind, $scheduleId, $checkOnly = false)
    {
        if (!$boothCd) {
            // $boothCdがnullの場合、そのまま返す
            return $boothCd;
        }

        // ブースマスタから対象ブースの用途種別を取得
        $usage_kind = $this->fncScheGetBoothUsage($campusCd, $boothCd);
        if (
            $usage_kind == AppConst::CODE_MASTER_41_4
            || $usage_kind == AppConst::CODE_MASTER_41_5
        ) {
            // 用途種別が両者オンライン・家庭教師の場合はブースコードをそのまま返す
            return $boothCd;
        }

        // スケジュール情報から対象ブースが使用されているか検索
        $exists = $this->fncScheChkBoothFromSchedule($campusCd, $targetDate, $periodNo, $boothCd, $scheduleId, false);
        if (!$exists) {
            // 重複なしの場合、ブースコードをそのまま返す
            return $boothCd;
        }

        // ブースチェックのみの場合、空きブース取得処理なしで復帰
        if ($checkOnly) {
            // 重複ありの場合、nullを返す
            return null;
        }

        //---------------------------
        // 空きブース検索
        //---------------------------
        // ブースマスタから対象校舎のブースを取得
        $arrMstBooths = $this->fncScheGetBoothFromMst($campusCd, $howToKind);

        // スケジュール情報より、使用ブースを取得
        $arrUsedBooths = $this->fncScheGetUseBoothFromSchedule($campusCd, $targetDate, $periodNo);

        // マスタのブース - 使用ブース で差分を取得（空きブース）
        $arrFreeBooths = array_diff($arrMstBooths, $arrUsedBooths);
        if (count($arrFreeBooths) > 0) {
            // 空きブースありの場合
            // ソート順先頭のブースを返す
            $arrFreeBooths = array_values($arrFreeBooths);
            $freeBooth = $arrFreeBooths[0];
        } else {
            // 空きブースなしの場合、nullを返す
            $freeBooth = null;
        }

        return $freeBooth;
    }

    /**
     * ブースのチェック・空きブース取得（面談用）
     *
     * @param string $campusCd 校舎コード
     * @param string $boothCd ブースコード
     * @param string $targetDate 対象日付
     * @param string $startTime 開始時刻
     * @param string $endTime 終了時刻
     * @param int $scheduleId スケジュールID
     * @param bool $checkOnly 重複チェックのみ
     * @return string ブースコード（空き無し時にはnull）
     */
    private function fncScheSearchBoothForConference($campusCd, $boothCd, $targetDate, $startTime, $endTime, $scheduleId, $checkOnly = false)
    {
        // スケジュール情報から対象ブースが使用されているか検索
        $query = Schedule::query();

        // 変更時は更新中のキー以外を検索
        if ($scheduleId) {
            $query->where('schedule_id', '!=', $scheduleId);
        }

        $account = Auth::user();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }
        $exists = $query
            // 校舎・日付・開始時刻・終了時刻・ブースコードで検索
            ->where('campus_cd', $campusCd)
            ->where('target_date', $targetDate)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->where('booth_cd', $boothCd)
            // 未振替・振替中・振替済・リセット済スケジュールを除外
            ->whereNotIn('schedules.absent_status', [AppConst::CODE_MASTER_35_3, AppConst::CODE_MASTER_35_4, AppConst::CODE_MASTER_35_5, AppConst::CODE_MASTER_35_7])
            ->exists();

        if (!$exists) {
            // 重複なしの場合、ブースコードをそのまま返す
            return $boothCd;
        }

        // ブースチェックのみの場合、空きブース取得処理なしで復帰
        if ($checkOnly) {
            // 重複ありの場合、nullを返す
            return null;
        }

        //---------------------------
        // ブース重複ありの場合
        //---------------------------
        // ブースマスタから対象校舎のブースを取得
        $query = MstBooth::query();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }
        $mstBooths = $query
            ->select('booth_cd')
            // 校舎で絞り込み
            ->where('campus_cd', $campusCd)
            // 用途種別で絞り込み（面談用）
            ->where('usage_kind', AppConst::CODE_MASTER_41_3)
            ->orderBy('disp_order')
            ->get();

        // 配列に格納
        $arrMstBooths = [];
        foreach ($mstBooths as $mstBooth) {
            array_push($arrMstBooths, $mstBooth->booth_cd);
        }

        // スケジュール情報より、使用ブースを取得
        $query = Schedule::query();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }
        $usedBooths = $query
            ->select('booth_cd')
            // 校舎・日付・開始時刻・終了時刻で検索
            ->where('campus_cd', $campusCd)
            ->where('target_date', $targetDate)
            ->where('start_time', '<=', $endTime)
            ->where('end_time', '>=', $startTime)
            // 未振替・振替中・振替済・リセット済スケジュールを除外
            ->whereNotIn('schedules.absent_status', [AppConst::CODE_MASTER_35_3, AppConst::CODE_MASTER_35_4, AppConst::CODE_MASTER_35_5, AppConst::CODE_MASTER_35_7])
            ->get();

        // 配列に格納
        $arrUsedBooths = [];
        foreach ($usedBooths as $usedBooth) {
            array_push($arrUsedBooths, $usedBooth->booth_cd);
        }

        // マスタのブース - 使用ブース で差分を取得（空きブース）
        $arrFreeBooths = array_diff($arrMstBooths, $arrUsedBooths);
        if (count($arrFreeBooths) > 0) {
            $arrFreeBooths = array_values($arrFreeBooths);
            // 空きブースありの場合
            // ソート順先頭のブースを返す
            $freeBooth = $arrFreeBooths[0];
        } else {
            // 空きなしの場合、nullを返す
            $freeBooth = null;
        }

        return $freeBooth;
    }

    /**
     * ブースのチェック・空きブース取得（レギュラー授業）
     *
     * @param string $campusCd 校舎コード
     * @param string $boothCd ブースコード
     * @param string $dayCd 曜日コード
     * @param int $periodNo 時限
     * @param int $howToKind 通塾種別
     * @param int $regularClassId レギュラー授業ID
     * @param bool $checkOnly 重複チェックのみ
     * @return string ブースコード（空き無し時にはnull）
     */
    private function fncScheSearchBoothRegular($campusCd, $boothCd, $dayCd, $periodNo, $howToKind, $regularClassId, $checkOnly = false)
    {
        // ブースマスタから対象ブースの用途種別を取得
        $usage_kind = $this->fncScheGetBoothUsage($campusCd, $boothCd);
        if (
            $usage_kind == AppConst::CODE_MASTER_41_4
            || $usage_kind == AppConst::CODE_MASTER_41_5
        ) {
            // 用途種別が両者オンライン・家庭教師の場合はブースコードをそのまま返す
            return $boothCd;
        }

        // レギュラー授業情報から対象ブースが使用されているか検索
        $exists = $this->fncScheChkBoothFromRegular($campusCd, $dayCd, $periodNo, $boothCd, $regularClassId);
        if (!$exists) {
            // 重複なしの場合、ブースコードをそのまま返す
            return $boothCd;
        }

        // ブースチェックのみの場合、空きブース取得処理なしで復帰
        if ($checkOnly) {
            // 重複ありの場合、nullを返す
            return null;
        }

        //---------------------------
        // 空きブース検索
        //---------------------------
        // ブースマスタから対象校舎のブースを取得
        $arrMstBooths = $this->fncScheGetBoothFromMst($campusCd, $howToKind);

        // レギュラー授業情報より、使用ブースを取得
        $arrUsedBooths = $this->fncScheGetUseBoothFromRegular($campusCd, $dayCd, $periodNo, null);

        // マスタのブース - 使用ブース で差分を取得（空きブース）
        $arrFreeBooths = array_diff($arrMstBooths, $arrUsedBooths);
        if (count($arrFreeBooths) > 0) {
            // 空きブースありの場合
            // ソート順先頭のブースを返す
            $arrFreeBooths = array_values($arrFreeBooths);
            $freeBooth = $arrFreeBooths[0];
        } else {
            // 空きブースなしの場合、nullを返す
            $freeBooth = null;
        }

        return $freeBooth;
    }

    /**
     * ブースのチェック・空きブース取得（一括登録用）
     *
     * @param string $campusCd 校舎コード
     * @param string $boothCd ブースコード
     * @param string $targetDate 対象日付
     * @param string $dayCd 曜日コード
     * @param int $periodNo 時限
     * @param int $howToKind 通塾種別
     * @param int $regularClassId レギュラー授業ID
     * @return string ブースコード（空き無し時にはnull）
     */
    private function fncScheSearchBoothBulk($campusCd, $boothCd, $targetDate, $dayCd, $periodNo, $howToKind, $regularClassId)
    {
        // ブースマスタから対象ブースの用途種別を取得
        $usage_kind = $this->fncScheGetBoothUsage($campusCd, $boothCd);
        if (
            $usage_kind == AppConst::CODE_MASTER_41_4
            || $usage_kind == AppConst::CODE_MASTER_41_5
        ) {
            // 用途種別が両者オンライン・家庭教師の場合はブースコードをそのまま返す
            return $boothCd;
        }
        // スケジュール情報から対象ブースが使用されているか検索
        $exists = $this->fncScheChkBoothFromSchedule($campusCd, $targetDate, $periodNo, $boothCd, null, true);
        if (!$exists) {
            // 重複なしの場合、ブースコードをそのまま返す
            return $boothCd;
        }

        //---------------------------
        // 空きブース検索
        //---------------------------
        // ブースマスタから対象校舎のブースを取得
        $arrMstBooths = $this->fncScheGetBoothFromMst($campusCd, $howToKind);

        // スケジュール情報より、使用ブースを取得 ※レギュラー授業を除外
        $arrUsedBoothsSchedule = $this->fncScheGetUseBoothFromSchedule($campusCd, $targetDate, $periodNo, true);

        // レギュラー授業情報より、使用ブースを取得 ※自身のレギュラーを除外
        $arrUsedBoothsRegular = $this->fncScheGetUseBoothFromRegular($campusCd, $dayCd, $periodNo, $regularClassId);
        // 使用ブースをマージ
        $arrUsedBooths = array_unique(array_merge($arrUsedBoothsSchedule, $arrUsedBoothsRegular));

        // マスタのブース - 使用ブース で差分を取得（空きブース）
        $arrFreeBooths = array_diff($arrMstBooths, $arrUsedBooths);
        if (count($arrFreeBooths) > 0) {
            // 空きブースありの場合
            // ソート順先頭のブースを返す
            $arrFreeBooths = array_values($arrFreeBooths);
            $freeBooth = $arrFreeBooths[0];
        } else {
            // 空きブースなしの場合、nullを返す
            $freeBooth = null;
        }

        return $freeBooth;
    }

    /**
     * ブースの用途種別取得
     *
     * @param string $campusCd 校舎コード
     * @param string $boothCd ブースコード
     * @return string
     */
    private function fncScheGetBoothUsage($campusCd, $boothCd)
    {
        // ブースマスタから用途種別を取得
        $query = MstBooth::query();

        $account = Auth::user();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }
        $mstBooth = $query
            ->select('usage_kind')
            // 校舎・ブースで検索
            ->where('campus_cd', $campusCd)
            ->where('booth_cd', $boothCd)
            ->firstOrFail();

        return $mstBooth->usage_kind;
    }

    /**
     * スケジュール情報 ブース空きチェック
     *
     * @param string $campusCd 校舎コード
     * @param string $targetDate 対象日付
     * @param int $periodNo 時限
     * @param int $boothCd ブースコード
     * @param int $scheduleId スケジュールID
     * @param bool $bulkFlg 一括登録かどうか
     * @return bool
     */
    private function fncScheChkBoothFromSchedule($campusCd, $targetDate, $periodNo, $boothCd, $scheduleId = null, $bulkFlg = false)
    {
        // スケジュール情報より、ブース空きチェック
        $account = Auth::user();
        $query = Schedule::query();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }

        // 変更時は更新中のキー以外を検索
        if ($scheduleId) {
            $query->where('schedule_id', '!=', $scheduleId);
        }

        // 一括登録時は一括登録データ以外を検索
        if ($bulkFlg) {
            $query->where('create_kind', '!=', AppConst::CODE_MASTER_32_0);
        }

        $exists = $query
            // 校舎・日付・時限・ブースコードで検索
            ->where('campus_cd', $campusCd)
            ->where('target_date', $targetDate)
            ->where('period_no', $periodNo)
            ->where('booth_cd', $boothCd)
            // 未振替・振替中・振替済・リセット済スケジュールを除外
            ->whereNotIn('schedules.absent_status', [AppConst::CODE_MASTER_35_3, AppConst::CODE_MASTER_35_4, AppConst::CODE_MASTER_35_5, AppConst::CODE_MASTER_35_7])
            ->exists();

        return $exists;
    }

    /**
     * レギュラー授業情報 ブース空きチェック
     *
     * @param string $campusCd 校舎コード
     * @param string $dayCd 曜日コード
     * @param int $periodNo 時限
     * @param int $boothCd ブースコード
     * @param int $regularClassId レギュラー授業ID
     * @return bool
     */
    private function fncScheChkBoothFromRegular($campusCd, $dayCd, $periodNo, $boothCd, $regularClassId)
    {
        // スケジュール情報より、ブース空きチェック
        $account = Auth::user();
        $query = RegularClass::query();

        // 変更時は更新中のキー以外を検索
        if ($regularClassId) {
            $query->where('regular_class_id', '!=', $regularClassId);
        }

        $account = Auth::user();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }
        $exists = $query
            // 校舎・曜日・時限・ブースコードで検索
            ->where('campus_cd', $campusCd)
            ->where('day_cd', $dayCd)
            ->where('period_no', $periodNo)
            ->where('booth_cd', $boothCd)
            ->exists();

        return $exists;
    }

    /**
     * 通塾種別によるブース情報取得（ブースマスタより）
     *
     * @param string $campusCd 校舎コード
     * @param int $howToKind 通塾種別
     * @return array
     */
    private function fncScheGetBoothFromMst($campusCd, $howToKind)
    {
        $account = Auth::user();
        // ブースマスタから対象校舎のブースを取得
        $query = MstBooth::query();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }

        $mstBooths = $query
            ->select('booth_cd')
            // 用途種別で絞り込み
            ->sdJoin(CodeMaster::class, function ($join) use ($howToKind) {
                $join->on('mst_booths.usage_kind', '=', 'mst_codes.sub_code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_33)
                    ->where('mst_codes.code', $howToKind);
            })
            // 校舎で絞り込み
            ->where('campus_cd', $campusCd)
            ->orderBy('disp_order')
            ->get();

        // 配列に格納
        $arrMstBooths = [];
        foreach ($mstBooths as $mstBooth) {
            array_push($arrMstBooths, $mstBooth->booth_cd);
        }

        return $arrMstBooths;
    }

    /**
     * スケジュール情報より使用ブース情報取得
     *
     * @param string $campusCd 校舎コード
     * @param string $targetDate 対象日付
     * @param int $periodNo 時限
     * @param bool $exceptRegular レギュラー授業除外（一括登録用・省略可）
     * @return array
     */
    private function fncScheGetUseBoothFromSchedule($campusCd, $targetDate, $periodNo, $exceptRegular = false)
    {
        // スケジュール情報より、使用ブースを取得
        $account = Auth::user();
        $query = Schedule::query();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }

        // レギュラー授業除外時はデータ作成区分「一括」以外を検索
        if ($exceptRegular) {
            $query->where('create_kind', '!=', AppConst::CODE_MASTER_32_0);
        }

        $usedBooths = $query
            ->select('booth_cd')
            // 校舎・日付・時限で検索
            ->where('campus_cd', $campusCd)
            ->where('target_date', $targetDate)
            ->where('period_no', $periodNo)
            // 未振替・振替中・振替済・リセット済スケジュールを除外
            ->whereNotIn('schedules.absent_status', [AppConst::CODE_MASTER_35_3, AppConst::CODE_MASTER_35_4, AppConst::CODE_MASTER_35_5, AppConst::CODE_MASTER_35_7])
            ->get();

        // 配列に格納
        $arrUsedBooths = [];
        foreach ($usedBooths as $usedBooth) {
            array_push($arrUsedBooths, $usedBooth->booth_cd);
        }

        return $arrUsedBooths;
    }

    /**
     * レギュラー授業情報より使用ブース情報取得
     *
     * @param string $campusCd 校舎コード
     * @param string $dayCd 曜日コード
     * @param int $periodNo 時限
     * @param int $regularClassId レギュラー授業ID
     * @return array
     */
    private function fncScheGetUseBoothFromRegular($campusCd, $dayCd, $periodNo, $regularClassId)
    {
        // スケジュール情報より、使用ブースを取得
        $account = Auth::user();
        $query = RegularClass::query();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }

        // 対象のキー以外を検索
        if ($regularClassId) {
            $query->where('regular_class_id', '!=', $regularClassId);
        }

        $usedBooths = $query
            ->select('booth_cd')
            // 校舎・曜日・時限で検索
            ->where('campus_cd', $campusCd)
            ->where('day_cd', $dayCd)
            ->where('period_no', $periodNo)
            ->get();

        // 配列に格納
        $arrUsedBooths = [];
        foreach ($usedBooths as $usedBooth) {
            array_push($arrUsedBooths, $usedBooth->booth_cd);
        }

        return $arrUsedBooths;
    }

    /**
     * 時限・開始時刻の相関チェック
     *
     * @param string $campusCd 校舎コード
     * @param string $timetableKind 時間割区分
     * @param string $period 時限
     * @param string $startTime 開始時刻
     * @return bool
     */
    private function fncScheChkStartTime($campusCd, $timetableKind, $period, $startTime)
    {
        // 対象時限の時間割情報を取得
        $curTimeTable = $this->fncScheGetTimetableByPeriod($campusCd, $timetableKind, $period);

        // 前の時限の時間割情報を取得
        $query = MstTimetable::query();

        $account = Auth::user();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }

        $preTimeTable = $query
            // 指定校舎で絞り込み
            ->where('campus_cd', $campusCd)
            // 時間割区分で絞り込み
            ->where('timetable_kind', $timetableKind)
            // 時限で絞り込み
            ->where('period_no', '<', $period)
            ->orderBy('period_no', 'desc')
            // 先頭１件取得
            ->first();

        if ($preTimeTable) {
            // 前の時限がある場合、前の時限の終了時刻をセット
            $preEndTime = $preTimeTable->end_time;
        } else {
            // 前の時限がない場合、規定の時刻(8:00)をセット
            $preEndTime = Carbon::createFromTimeString(config('appconf.lesson_start_time_min'));
        }
        $startTime = Carbon::createFromTimeString($startTime);
        // 指定の開始時刻が前の時限の終了時刻～指定時限の終了時刻の範囲内か
        if ($startTime >= $preEndTime && $startTime <= $curTimeTable->end_time) {
            // チェックOK
            return true;
        } else {
            // チェックNG
            return false;
        }
    }

    /**
     * 指定期間が通常期間内かチェック
     *
     * @param string $campusCd 校舎コード
     * @param string $startDate 開始日付
     * @param string $endDate 終了日付
     * @return bool
     */
    private function fncScheChkScheduleTerm($campusCd, $startDate, $endDate)
    {
        // 通常期間が含まれるか
        $exists = YearlySchedule::where('campus_cd', $campusCd)
            ->whereBetween('lesson_date', [$startDate, $endDate])
            // 通常期間
            ->where('date_kind', AppConst::CODE_MASTER_38_0)
            ->exists();

        if (!$exists) {
            // 通常期間データなしの場合、falseを返す
            return false;
        }

        // 特別期間が含まれるか
        $exists = YearlySchedule::where('campus_cd', $campusCd)
            ->where('campus_cd', $campusCd)
            ->whereBetween('lesson_date', [$startDate, $endDate])
            // 特別期間（春期・夏期・冬期）
            ->whereIn('date_kind', [AppConst::CODE_MASTER_38_1, AppConst::CODE_MASTER_38_2, AppConst::CODE_MASTER_38_3])
            ->exists();

        if ($exists) {
            // 特別期間データありの場合、falseを返す
            return false;
        }

        return true;
    }

    //------------------------------
    // データ登録系
    //------------------------------
    /**
     * スケジュール情報登録
     * ※トランザクション管理は呼び元で行うこと
     *
     * @param array $data スケジュール情報
     * @param date $targetDate 対象日付
     * @param string $booth ブース
     * @param int $createKind データ作成区分
     * @return int
     */
    private function fncScheCreateSchedule($data, $targetDate, $booth, $createKind)
    {
        $account = Auth::user();

        // 時間（分）の算出
        $start = Carbon::createFromTimeString($data['start_time']);
        $end = Carbon::createFromTimeString($data['end_time']);
        $minutes = (int)$start->diffInMinutes($end, true);

        // スケジュール情報
        // schedulesテーブルへのinsert
        $schedule = new Schedule;
        $schedule->campus_cd = $data['campus_cd'];
        $schedule->target_date = $targetDate;
        if ($data['course_kind'] != AppConst::CODE_MASTER_42_3) {
            // 時限・教科は面談以外の場合のみ設定
            $schedule->period_no = $data['period_no'];
            $schedule->subject_cd = $data['subject_cd'];
        }
        $schedule->start_time = $data['start_time'];
        $schedule->end_time = $data['end_time'];
        $schedule->minutes = $minutes;
        $schedule->booth_cd = $booth;
        $schedule->course_cd = $data['course_cd'];
        if ($data['course_kind'] != AppConst::CODE_MASTER_42_2) {
            // １対多以外の場合 生徒IDを設定
            $schedule->student_id = $data['student_id'];
        }
        if ($data['course_kind'] == AppConst::CODE_MASTER_42_1 || $data['course_kind'] == AppConst::CODE_MASTER_42_2) {
            // 授業の場合 講師IDを設定
            $schedule->tutor_id = $data['tutor_id'];
        }
        $schedule->how_to_kind = $data['how_to_kind'];
        $schedule->create_kind = $createKind;
        // 授業区分の設定
        if ($createKind == AppConst::CODE_MASTER_32_1 || $createKind == AppConst::CODE_MASTER_32_2) {
            // 個別登録・振替の場合
            if ($data['course_kind'] == AppConst::CODE_MASTER_42_1 || $data['course_kind'] == AppConst::CODE_MASTER_42_2) {
                // 授業の場合 指定された授業区分を設定
                $schedule->lesson_kind = $data['lesson_kind'];
            } else {
                // その他の場合、授業区分は初期値とする
                $schedule->lesson_kind = AppConst::CODE_MASTER_31_0;
            }
            // 仮登録フラグ・メモは設定元情報をそのまま設定
            $schedule->tentative_status = $data['tentative_status'];
            $schedule->memo = $data['memo'];
        } else {
            // 一括登録の場合
            if ($data['course_kind'] == AppConst::CODE_MASTER_42_1 || $data['course_kind'] == AppConst::CODE_MASTER_42_2) {
                // 授業の場合 授業区分に「通常」を設定
                $schedule->lesson_kind = AppConst::CODE_MASTER_31_1;
            } else {
                // その他の場合、授業区分は初期値とする
                $schedule->lesson_kind = AppConst::CODE_MASTER_31_0;
            }
        }
        if ($createKind == AppConst::CODE_MASTER_32_0) {
            // 一括登録の場合のみ設定
            $schedule->regular_class_id = $data['regular_class_id'];
        }
        if ($createKind == AppConst::CODE_MASTER_32_2) {
            // 振替登録の場合のみ設定
            $schedule->transfer_class_id = $data['schedule_id'];
            $schedule->substitute_kind = $data['substitute_kind'];
            $schedule->absent_tutor_id = $data['absent_tutor_id'];
        }
        $schedule->adm_id = $account->account_id;
        // 登録
        $schedule->save();

        // 受講生徒情報
        if ($data['course_kind'] == AppConst::CODE_MASTER_42_2) {
            // schedulesテーブル登録時のスケジュールIDをセット
            $scheduleId = $schedule->schedule_id;

            // class_member_idはカンマ区切り文字列で入ってくる
            // 分割して１件ずつ登録
            foreach (explode(",", $data['class_member_id']) as $member) {
                // class_membersテーブルへのinsert
                $classmember = new ClassMember;
                $classmember->schedule_id = $scheduleId;
                $classmember->student_id = $member;
                $classmember->absent_status = AppConst::CODE_MASTER_35_0;
                // 登録
                $classmember->save();
            }
        }
        return $schedule->schedule_id;
    }

    /**
     * レギュラー授業情報登録
     * 講師空き時間情報登録も合わせて行う
     * ※トランザクション管理は呼び元で行うこと
     *
     * @param array $data レギュラー授業登録情報
     * @param string $booth ブース
     * @return void
     */
    private function fncScheCreateRegular($data, $booth)
    {
        // 時間（分）の算出
        $start = Carbon::createFromTimeString($data['start_time']);
        $end = Carbon::createFromTimeString($data['end_time']);
        $minutes = (int)$start->diffInMinutes($end, true);

        // レギュラー授業情報登録
        // regular_classesテーブルへのinsert
        $regularClass = new RegularClass();
        $regularClass->campus_cd = $data['campus_cd'];
        $regularClass->day_cd = $data['day_cd'];
        $regularClass->period_no = $data['period_no'];
        $regularClass->subject_cd = $data['subject_cd'];
        $regularClass->start_time = $data['start_time'];
        $regularClass->end_time = $data['end_time'];
        $regularClass->minutes = $minutes;
        $regularClass->booth_cd = $booth;
        $regularClass->course_cd = $data['course_cd'];
        if ($data['course_kind'] != AppConst::CODE_MASTER_42_2) {
            // １対多以外の場合 生徒IDを設定
            $regularClass->student_id = $data['student_id'];
        }
        if ($data['course_kind'] == AppConst::CODE_MASTER_42_1 || $data['course_kind'] == AppConst::CODE_MASTER_42_2) {
            // 授業の場合 講師IDを設定
            $regularClass->tutor_id = $data['tutor_id'];
        }
        $regularClass->how_to_kind = $data['how_to_kind'];
        // 登録
        $regularClass->save();

        // レギュラー受講生徒情報登録（コース種別が授業複の場合のみ）
        if ($data['course_kind'] == AppConst::CODE_MASTER_42_2) {
            // regular_classesテーブル登録時のレギュラー授業IDをセット
            $regularClassId = $regularClass->regular_class_id;

            // class_member_idはカンマ区切り文字列で入ってくる
            // 分割して１件ずつ登録
            foreach (explode(",", $data['class_member_id']) as $member) {
                // レギュラー受講生徒情報テーブルへのinsert
                $regularClassMember = new RegularClassMember;
                $regularClassMember->regular_class_id = $regularClassId;
                $regularClassMember->student_id = $member;
                // 登録
                $regularClassMember->save();
            }
        }

        // 講師空き時間情報登録（コース種別が授業の場合のみ）
        if ($data['course_kind'] == AppConst::CODE_MASTER_42_1 || $data['course_kind'] == AppConst::CODE_MASTER_42_2) {
            // 講師空き時間情報検索
            $exists = TutorFreePeriod::where('tutor_id', $data['tutor_id'])
                // 講師ID・曜日コード・時限で絞り込み
                ->where('day_cd', $data['day_cd'])
                ->where('period_no', $data['period_no'])
                ->exists();

            if (!$exists) {
                // データなしの場合、登録
                // 講師空き時間情報テーブルへのinsert
                $tutorPeriod = new TutorFreePeriod;
                $tutorPeriod->tutor_id = $data['tutor_id'];
                $tutorPeriod->day_cd = $data['day_cd'];
                $tutorPeriod->period_no = $data['period_no'];
                // 登録
                $tutorPeriod->save();
            }
        }
        return;
    }

    //------------------------------
    // バリデーション（共通処理）
    //------------------------------

    /**
     * スケジュール登録時の生徒スケジュール重複チェック
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param $kind
     * @param $attribute
     * @param $value
     * @param $fail
     */
    function fncScheValidateStudent($request, $kind, $attribute, $value, $fail)
    {
        if (
            !$request->filled('target_date') || !$request->filled('start_time')
            || !$request->filled('end_time') || !$request->filled($attribute)
            || !$request->filled('campus_cd')
        ) {
            // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
            return;
        }

        $scheduleId = null;
        if ($kind == AppConst::SCHEDULE_KIND_UPD && $request->filled('schedule_id')) {
            // 更新の場合のみ、スケジュールIDをセット（除外用）
            $scheduleId = $request['schedule_id'];
        }

        // 繰り返し登録有無チェック
        $targetDates = [];
        if ($request->filled('repeat_chk') && $request->filled('repeat_times')) {
            if ($request['repeat_chk'] == 'true' && intval($request['repeat_times']) > 0) {
                // 繰り返し登録有りの場合
                // 対象日と同一曜日の授業日を回数分取得
                $repeatTimes = intval($request['repeat_times']) + 1;
                $targetDates = $this->fncScheGetScheduleDate($request['campus_cd'], $request['target_date'], $repeatTimes, null);
            } else {
                // 繰り返し登録なしの場合
                array_push($targetDates, $request['target_date']);
            }
        } else {
            // 繰り返し登録対象外の場合
            array_push($targetDates, $request['target_date']);
        }
        foreach ($targetDates as $targetDate) {
            $members = [];
            // チェック対象の生徒IDを設定
            if ($attribute == 'class_member_id') {
                // 複数生徒指定の場合
                $members = explode(",", $value);
            } else {
                // 単一生徒指定の場合
                array_push($members, $value);
            }
            foreach ($members as $member) {
                // 生徒リストを取得
                // 生徒スケジュール重複チェック
                $chk = $this->fncScheChkDuplidateSid(
                    $targetDate,
                    $request['start_time'],
                    $request['end_time'],
                    $member,
                    $scheduleId,
                    false
                );
                if (!$chk) {
                    // 生徒スケジュール重複エラー
                    $validateMsg = Lang::get('validation.duplicate_student');
                    if ($targetDate != $request['target_date']) {
                        // 繰り返し登録データの場合、対象日も合わせて表示する
                        $validateMsg = $validateMsg . "(" . $targetDate;
                        if ($attribute == 'class_member_id') {
                            // 生徒複数指定の場合、生徒名も合わせて表示する
                            $studentName = $this->mdlGetStudentName($member);
                            $validateMsg = $validateMsg . " " . $studentName;
                        }
                        $validateMsg = $validateMsg . ")";
                    } else {
                        if ($attribute == 'class_member_id') {
                            // 生徒複数指定の場合、生徒名も合わせて表示する
                            $studentName = $this->mdlGetStudentName($member);
                            $validateMsg = $validateMsg . "(" . $studentName . ")";
                        }
                    }
                    return $fail($validateMsg);
                }
            }
        }
    }

    /**
     * スケジュール登録時の講師スケジュール重複チェック
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param $kind
     * @param $attribute
     * @param $value
     * @param $fail
     */
    function fncScheValidateTutor($request, $kind, $attribute, $value, $fail)
    {
        if (
            !$request->filled('target_date') || !$request->filled('start_time')
            || !$request->filled('end_time') || !$request->filled('tutor_id')
            || !$request->filled('campus_cd')
        ) {
            // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
            return;
        }

        $scheduleId = null;
        if ($kind == AppConst::SCHEDULE_KIND_UPD && $request->filled('schedule_id')) {
            // 更新の場合のみ、スケジュールIDをセット（除外用）
            $scheduleId = $request['schedule_id'];
        }

        // 繰り返し登録有無チェック
        $targetDates = [];
        if ($request->filled('repeat_chk') && $request->filled('repeat_times')) {
            if ($request['repeat_chk'] == 'true' && intval($request['repeat_times']) > 0) {
                // 繰り返し登録有りの場合
                // 対象日と同一曜日の授業日を回数分取得
                $repeatTimes = intval($request['repeat_times']) + 1;
                $targetDates = $this->fncScheGetScheduleDate($request['campus_cd'], $request['target_date'], $repeatTimes, null);
            } else {
                // 繰り返し登録なしの場合
                array_push($targetDates, $request['target_date']);
            }
        } else {
            // 繰り返し登録対象外の場合
            array_push($targetDates, $request['target_date']);
        }
        foreach ($targetDates as $targetDate) {
            // 講師スケジュール重複チェック
            $chk = $this->fncScheChkDuplidateTid(
                $targetDate,
                $request['start_time'],
                $request['end_time'],
                $value,
                $scheduleId,
                false
            );
            if (!$chk) {
                // 講師スケジュール重複エラー
                $validateMsg = Lang::get('validation.duplicate_tutor');
                if ($targetDate != $request['target_date']) {
                    // 繰り返し登録データの場合、対象日も合わせて表示する
                    $validateMsg = $validateMsg . "(" . $targetDate . ")";
                }
                return $fail($validateMsg);
            }
        }
    }

    /**
     * スケジュール登録時の時限と開始時刻の相関チェック
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param $attribute
     * @param $value
     * @param $fail
     */
    function fncScheValidatePeriodStartTime($request, $attribute, $value, $fail)
    {
        if (
            !$request->filled('campus_cd') || !$request->filled('target_date')
            || !$request->filled('period_no')
        ) {
            // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
            return;
        }
        // 対象日の時間割区分を取得
        $timetableKind = $this->fncScheGetTimeTableKind($request['campus_cd'], $request['target_date']);
        // 時限と開始時刻の相関チェック
        $chk = $this->fncScheChkStartTime(
            $request['campus_cd'],
            $timetableKind,
            $request['period_no'],
            $value
        );
        if (!$chk) {
            // 開始時刻範囲エラー
            return $fail(Lang::get('validation.out_of_range_period'));
        }
    }

    /**
     * スケジュール登録時の面談開始時刻チェック
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param $attribute
     * @param $value
     * @param $fail
     */
    function fncScheValidateConferenceStartTime($request, $attribute, $value, $fail)
    {
        $startTimeMin = Carbon::createFromTimeString(config('appconf.lesson_start_time_min'));
        $startTime = Carbon::createFromTimeString($value);

        // 開始時刻が8:00より前の場合エラーとする
        if ($startTime < $startTimeMin) {
            return $fail(Lang::get('validation.invalid_input'));
        }
    }

    /**
     * スケジュール登録時の授業区分チェック（見込客）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param $attribute
     * @param $value
     * @param $fail
     */
    function fncScheValidateLessonKindTrial($request, $attribute, $value, $fail)
    {
        if (!$request->filled('student_id')) {
            // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
            return;
        }
        // 生徒情報を取得
        $student = $this->fncScheGetStudentInfo($request['student_id']);
        if ($student->stu_status == AppConst::CODE_MASTER_28_0) {
            // 見込客の場合、体験授業以外ならエラー
            if (
                $value != AppConst::CODE_MASTER_31_5
                && $value != AppConst::CODE_MASTER_31_6
                && $value != AppConst::CODE_MASTER_31_7
            ) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        } else {
            // 見込客以外の場合、体験授業ならエラー
            if (
                $value == AppConst::CODE_MASTER_31_5
                || $value == AppConst::CODE_MASTER_31_6
                || $value == AppConst::CODE_MASTER_31_7
            ) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        }
    }
    /**
     * レギュラー授業スケジュール登録時の生徒スケジュール重複チェック
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param $kind
     * @param $attribute
     * @param $value
     * @param $fail
     */
    function fncScheValidateStudentRegular($request, $kind, $attribute, $value, $fail)
    {
        if (
            !$request->filled('day_cd') || !$request->filled('start_time')
            || !$request->filled('end_time') || !$request->filled($attribute)
            || !$request->filled('campus_cd')
        ) {
            // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
            return;
        }

        $regularClassId = null;
        if ($kind == AppConst::SCHEDULE_KIND_UPD && $request->filled('regular_class_id')) {
            // 更新の場合のみ、スケジュールIDをセット（除外用）
            $regularClassId = $request['regular_class_id'];
        }

        $members = [];
        // チェック対象の生徒IDを設定
        if ($attribute == 'class_member_id') {
            // 複数生徒指定の場合
            $members = explode(",", $value);
        } else {
            // 単一生徒指定の場合
            array_push($members, $value);
        }
        foreach ($members as $member) {
            // 生徒スケジュール重複チェック（生徒毎）
            $chk = $this->fncScheChkDuplidateSidRegular(
                $request['day_cd'],
                $request['start_time'],
                $request['end_time'],
                $member,
                $regularClassId
            );
            if (!$chk) {
                // 生徒スケジュール重複エラー
                $validateMsg = Lang::get('validation.duplicate_student');
                if ($attribute == 'class_member_id') {
                    // 生徒複数指定の場合、対象生徒名も合わせて表示する
                    $studentName = $this->mdlGetStudentName($member);
                    $validateMsg = $validateMsg . "(" . $studentName . ")";
                }
                return $fail($validateMsg);
            }
        }
    }

    /**
     * レギュラー授業スケジュール登録時の講師スケジュール重複チェック
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param $kind
     * @param $attribute
     * @param $value
     * @param $fail
     */
    function fncScheValidateTutorRegular($request, $kind, $attribute, $value, $fail)
    {
        if (
            !$request->filled('day_cd') || !$request->filled('start_time')
            || !$request->filled('end_time')
        ) {
            // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
            return;
        }

        $regularClassId = null;
        if ($kind == AppConst::SCHEDULE_KIND_UPD && $request->filled('regular_class_id')) {
            // 更新の場合のみ、スケジュールIDをセット（除外用）
            $regularClassId = $request['regular_class_id'];
        }

        // 講師スケジュール重複チェック
        $chk = $this->fncScheChkDuplidateTidRegular(
            $request['day_cd'],
            $request['start_time'],
            $request['end_time'],
            $value,
            $regularClassId
        );
        if (!$chk) {
            // 講師スケジュール重複エラー
            return $fail(Lang::get('validation.duplicate_tutor'));
        }
    }
}
