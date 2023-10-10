<?php

namespace App\Http\Controllers\Traits;

use App\Models\YearlySchedule;
use App\Models\MstTimetable;
use App\Models\MstCourse;
use App\Models\MstBooth;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\Student;
use App\Models\StudentCampus;
use App\Models\TutorCampus;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
    // データ取得系
    //------------------------------

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
     * @return array
     */
    private function fncScheChkDuplidateSid($targetDate, $startTime, $endTime, $studentId, $scheduleId = null)
    {
        // スケジュール情報から対象生徒のスケジュールが登録されているか検索
        $query = Schedule::query();

        // 変更時は更新中のキー以外を検索
        if ($scheduleId) {
            $query->where('schedules.schedule_id', '!=', $scheduleId);
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
            ->exists();

        if ($exists) {
            // 重複ありの場合
            return false;
        }
        return true;
    }

    /**
     * 講師のスケジュール重複チェック
     *
     * @param string $targetDate 対象日付
     * @param string $startTime 開始時刻
     * @param string $endTime 終了時刻
     * @param int $tutorId 講師ID
     * @param int $scheduleId スケジュールID
     * @return bool
     */
    private function fncScheChkDuplidateTid($targetDate, $startTime, $endTime, $tutorId, $scheduleId = null)
    {
        // スケジュール情報から対象生徒のスケジュールが登録されているか検索
        $query = Schedule::query();

        // 変更時は更新中のキー以外を検索
        if ($scheduleId) {
            $query->where('schedule_id', '!=', $scheduleId);
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
     * ブースのチェック・空きブース取得
     *
     * @param string $campusCd 校舎コード
     * @param string $boothCd ブースコード
     * @param string $targetDate 対象日付
     * @param int $periodNo 時限
     * @param int $howToKind 通塾種別
     * @param int $scheduleId スケジュールID
     * @param bool $checkOnly 重複チェックのみ
     * @return string
     */
    private function fncScheSearchBooth($campusCd, $boothCd, $targetDate, $periodNo, $howToKind, $scheduleId, $checkOnly = false)
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

        if (
            $mstBooth->usage_kind == AppConst::CODE_MASTER_41_4
            || $mstBooth->usage_kind == AppConst::CODE_MASTER_41_5
        ) {
            // 用途種別が両者オンライン・家庭教師の場合はそのまま設定
            return $boothCd;
        }

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
            // 校舎・日付・時限・ブースコードで検索
            ->where('campus_cd', $campusCd)
            ->where('target_date', $targetDate)
            ->where('period_no', $periodNo)
            ->where('booth_cd', $boothCd)
            ->exists();

        if (!$exists) {
            // 重複なしの場合
            return $boothCd;
        }

        //---------------------------
        // ブース重複ありの場合
        //---------------------------
        // ブースチェックのみの場合、空きブース取得処理なし
        if ($checkOnly) {
            // 重複ありの場合、nullを返す
            return null;
        }

        // スケジュール情報より、使用ブースを取得
        $query = Schedule::query();
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        }
        $usedBooths = $query
            ->select('booth_cd')
            // 校舎・日付・時限で検索
            ->where('campus_cd', $campusCd)
            ->where('target_date', $targetDate)
            ->where('period_no', $periodNo)
            ->get();

        // 配列に格納
        $arrUsedBooths = [];
        foreach ($usedBooths as $usedBooth) {
            array_push($arrUsedBooths, $usedBooth->booth_cd);
        }

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
     * @return string
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
            ->exists();

        if (!$exists) {
            // 重複なしの場合
            return $boothCd;
        }

        //---------------------------
        // ブース重複ありの場合
        //---------------------------

        // ブースチェックのみの場合、空きブース取得処理なし
        if ($checkOnly) {
            // 重複ありの場合、nullを返す
            return null;
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
            ->get();

        // 配列に格納
        $arrUsedBooths = [];
        foreach ($usedBooths as $usedBooth) {
            array_push($arrUsedBooths, $usedBooth->booth_cd);
        }

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
     * 時限・開始時刻の相関チェック
     *
     * @param string $campusCd 校舎コード
     * @param string $targetDate 対象日付
     * @param string $period 時限
     * @param string $startTime 開始時刻
     * @return bool
     */
    private function fncScheCheckStartTime($campusCd, $targetDate, $period, $startTime)
    {
        // 対象日の時間割区分を取得
        $timetableKind = $this->fncScheGetTimeTableKind($campusCd, $targetDate);

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
}
