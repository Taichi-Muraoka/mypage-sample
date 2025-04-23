<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Models\MstCourse;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\Tutor;
use App\Models\MstCampus;
use App\Models\MstSubject;
use App\Models\Student;
use App\Models\CodeMaster;
use App\Http\Controllers\Traits\CtrlDateTrait;

/**
 * 欠席申請 - 機能共通処理
 */
trait FuncAbsentTrait
{
    // 欠席連絡可能スケジュール取得用
    use CtrlDateTrait;

    /**
     * 生徒のスケジュールを取得する
     *
     * @param integer $sid 生徒ID
     */
    private function getStudentSchedule($sid)
    {
        // 画面表示時の時間を基準に、欠席連絡可能な授業を判定する
        $targetPeriod = $this->dtGetTargetDateFromTo();

        // 生徒IDに紐づくスケジュール（1対多授業）を取得する。
        $query = Schedule::query();
        $lessons = $query
            ->select(
                'schedules.schedule_id',
                'schedules.campus_cd',
                'schedules.target_date',
                'schedules.period_no',
                'schedules.tutor_id',
            )
            // コースマスタとJOIN
            ->sdLeftJoin(MstCourse::class, 'schedules.course_cd', '=', 'mst_courses.course_cd')
            // 受講生徒情報とJOIN
            ->sdLeftJoin(ClassMember::class, 'schedules.schedule_id', '=', 'class_members.schedule_id')
            // 抽出条件：
            // コースマスタ->コース種別 = 授業複
            // 受講生徒情報->受講生徒ID = ログイン中の生徒ID
            // 受講生徒情報->出欠ステータス = 実施前・出席
            ->where('mst_courses.course_kind', AppConst::CODE_MASTER_42_2)
            ->where('class_members.student_id', '=', $sid)
            ->where('class_members.absent_status', '=', AppConst::CODE_MASTER_35_0)
            // 上記で取得した対象期間で絞る
            ->whereBetween('schedules.target_date', [$targetPeriod['from_date'], $targetPeriod['to_date']])
            ->orderBy('schedules.target_date', 'asc')
            ->orderBy('schedules.period_no', 'asc')
            ->get();

        return $lessons;
    }

    /**
     * スケジュール詳細を取得
     * テーブルに表示する情報、メール送信に必要な情報を返却する
     *
     * @param integer $scheduleId スケジュールID
     */
    private function getScheduleDetail($scheduleId)
    {
        $query = Schedule::query();
        $lesson = $query
            ->select(
                'schedules.target_date',
                'schedules.period_no',
                'mst_campuses.name as campus_name',
                'mst_campuses.email_campus',
                'mst_campuses.tel_campus',
                'mst_courses.name as course_name',
                'mst_subjects.name as subject_name',
                'tutors.name as tutor_name'
            )
            // 校舎マスタとJOIN
            ->sdLeftJoin(MstCampus::class, function ($join) {
                $join->on('schedules.campus_cd', '=', 'mst_campuses.campus_cd');
            })
            // コースマスタとJOIN
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd');
            })
            // 教科マスタとJOIN
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('schedules.subject_cd', '=', 'mst_subjects.subject_cd');
            })
            // 講師情報とJOIN
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('schedules.tutor_id', '=', 'tutors.tutor_id');
            })
            ->where('schedules.schedule_id', '=', $scheduleId)
            ->firstOrFail();

        return $lesson;
    }

    /**
     * 欠席申請情報を取得
     * 一覧・詳細モーダル・受付モーダル共通
     * 管理者用
     */
    private function getAbsentApplyDetail($query)
    {
        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        // AbsentApplicationは校舎コードを持っていないので、Scheduleのモデルを指定する
        $model = new Schedule;
        $query->where($this->guardRoomAdminTableWithRoomCd($model));

        $absentApply = $query
            ->select(
                'absent_applications.absent_apply_id',
                'absent_applications.student_id',
                'absent_applications.absent_reason',
                'absent_applications.status',
                'absent_applications.apply_date',
                'schedules.schedule_id',
                'schedules.target_date',
                'schedules.period_no',
                'schedules.campus_cd',
                // 校舎名
                'mst_campuses.name as campus_name',
                // コース名
                'mst_courses.name as course_name',
                // 教科名
                'mst_subjects.name as subject_name',
                // 生徒名
                'students.name as student_name',
                // 講師名
                'tutors.tutor_id',
                'tutors.name as tutor_name',
                // コードマスタの名称（ステータス）
                'mst_codes.name as status_name'
            )
            // スケジュール情報とJOIN
            ->sdLeftJoin(Schedule::class, function ($join) {
                $join->on('absent_applications.schedule_id', '=', 'schedules.schedule_id');
            })
            // 校舎マスタとJOIN
            ->sdLeftJoin(MstCampus::class, function ($join) {
                $join->on('schedules.campus_cd', '=', 'mst_campuses.campus_cd');
            })
            // コースマスタとJOIN
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd');
            })
            // 教科マスタとJOIN
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('schedules.subject_cd', '=', 'mst_subjects.subject_cd');
            })
            // 生徒情報とJOIN
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('absent_applications.student_id', '=', 'students.student_id');
            })
            // 講師情報とJOIN
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('schedules.tutor_id', '=', 'tutors.tutor_id');
            })
            // コードマスタとJOIN ステータス
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('absent_applications.status', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_1);
            });

        return $absentApply;
    }
}
