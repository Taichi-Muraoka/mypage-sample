<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Models\MstCourse;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\Tutor;
use App\Models\MstCampus;
use App\Models\MstSubject;

/**
 * 欠席申請 - 機能共通処理
 */
trait FuncAbsentTrait
{
    /**
     * 生徒のスケジュールを取得する
     *
     * @param integer $sid 生徒ID
     */
    private function getStudentSchedule($sid)
    {
        // 画面表示時の時間を基準に、欠席連絡可能な授業を判定する
        $nowTime = date('H:i');
        $fromDate = null;
        $toDate = null;
        if ($nowTime < '22:00') {
            // 現在時刻が22時までは、翌日～翌日より1ヶ月先
            $fromDate = date('Y/m/d', strtotime('+1 day'));
            $toDate = date('Y/m/d', strtotime('+1 day +1 month'));
        } else {
            // 現在時刻が22時以降は、翌々日～翌々日より1ヶ月先
            $fromDate = date('Y/m/d', strtotime('+2 day'));
            $toDate = date('Y/m/d', strtotime('+2 day +1 month'));
        }

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
            ->whereBetween('schedules.target_date', [$fromDate, $toDate])
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
                'mst_subjects.name as subject_name',
                'tutors.name as tutor_name'
            )
            // 校舎マスタとJOIN
            ->sdLeftJoin(MstCampus::class, function ($join) {
                $join->on('schedules.campus_cd', '=', 'mst_campuses.campus_cd');
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
}
