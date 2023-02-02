<?php

namespace App\Http\Controllers\Traits;

use App\Models\ExtSchedule;
use App\Models\TutorRelate;
use App\Models\ExtRirekisho;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Log;

/**
 * 欠席申請 - 機能共通処理
 */
trait FuncAbsentTrait
{

    /**
     * 生徒のスケジュールを取得する
     *
     * @param integer $sid 生徒ID
     * @param string $roomcd 教室コード（指定しない場合はnull）
     */
    private function getStudentSchedule($sid, $roomcd)
    {

        // 翌日を取得（明日以降のスケジュール取得を分かりやすくするため）
        $tomorrow = date("Y/m/d", strtotime('+1 day'));

        // レギュラー＋個別講習の抽出条件
        $lesson_types = [AppConst::EXT_GENERIC_MASTER_109_0, AppConst::EXT_GENERIC_MASTER_109_1];

        // 生徒No.に紐づくスケジュール（レギュラー＋個別講習）を取得する。
        $query = ExtSchedule::query();
        $lessonsQuery = $query
            ->select(
                'id',
                'lesson_date',
                'start_time'
            )
            ->where('ext_schedule.sid', '=', $sid)
            ->whereIn('ext_schedule.lesson_type', $lesson_types)
            ->where(function ($orQuery) {
                // 出欠・振替コードが2（振替）以外 ※NULLのものを含む
                $orQuery->whereNotIn('ext_schedule.atd_status_cd', [AppConst::ATD_STATUS_CD_2])
                    ->orWhereNull('ext_schedule.atd_status_cd');
            })
            // 教室が指定された場合のみ絞り込み
            ->when($roomcd, function ($query) use ($roomcd) {
                return $query->where('.roomcd', $roomcd);
            })
            ->orderBy('ext_schedule.lesson_date', 'asc')
            ->orderBy('ext_schedule.start_time', 'asc');

        // 生徒のみ翌日以降のスケジュール表示
        if (AuthEx::isAdmin()) {
            $lessons = $lessonsQuery->get();
        } else {
            $lessons = $lessonsQuery
                ->where('ext_schedule.lesson_date', '>=', $tomorrow)
                ->get();
        }
        return $lessons;
    }

    /**
     * 生徒が所属する教室の教師名を取得する
     * プルダウン用
     *
     * @param integer $sid 生徒ID
     */
    private function getTeacherList($sid)
    {
        // 教師名のプルダウンメニューを作成
        $query = TutorRelate::query();
        $home_teachers = $query
            ->distinct()
            ->select(
                'ext_rirekisho.tid',
                'ext_rirekisho.name AS value'
            )
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('ext_rirekisho.tid', '=', 'tutor_relate.tid');
            })
            ->where('tutor_relate.sid', '=', $sid)
            ->where('tutor_relate.roomcd', '=', AppConst::EXT_GENERIC_MASTER_101_900)
            ->orderBy('ext_rirekisho.tid', 'asc')
            ->get()
            ->keyBy('tid');

        return $home_teachers;
    }

    /**
     * スケジュール詳細を取得
     * 教師名と教室名を返却する
     *
     * @param integer $scheduleId スケジュールID
     */
    private function getScheduleDetail($scheduleId)
    {
        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // $requestからidを取得し、検索結果を返却する。idはスケジュールID
        $query = ExtSchedule::query();
        $lesson = $query
            ->select(
                'room_name_full',
                'name'
            )
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('ext_schedule.roomcd', '=', 'room_names.code');
            })
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('ext_rirekisho.tid', '=', 'ext_schedule.tid');
            })
            ->where('ext_schedule.id', '=', $scheduleId)
            ->firstOrFail();

        return $lesson;
    }
}
