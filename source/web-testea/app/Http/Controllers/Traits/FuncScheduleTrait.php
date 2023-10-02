<?php

namespace App\Http\Controllers\Traits;

use App\Models\YearlySchedule;
use App\Models\MstTimetable;
use App\Models\MstCourse;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * スケジュール関連 - 共通処理
 */
trait FuncScheduleTrait
{

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
     * 年間予定から日付の取得（スケジュール生成用）
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
            ->where('day_cd', $targetDate->day_cd)
            // $startDateを含む
            ->where('lesson_date', ">=", $startDate)
            // 休日は除外する
            ->where('date_kind', "<>", AppConst::CODE_MASTER_38_9)
            ->orderBy('lesson_date')
            ->get();

        // 配列に格納
        $arrLessenDate = [];
        foreach ($lessonDates as $lessonDate) {
            array_push($arrLessenDate, $lessonDate->lesson_date);
        }

        return $arrLessenDate;
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
}
