<?php

namespace App\Http\Controllers\Traits;

use App\Models\ExtSchedule;
use App\Consts\AppConst;

/**
 * 振替申請 - 機能共通処理
 */
trait FuncTransferTrait
{

    /**
     * 教師のスケジュールを取得
     *
     * @param integer $tid
     * @param string $roomcd 教室コード（指定しない場合はnull）
     * @param integer $sid （指定しない場合はnull）
     * @param bool $tutor 教師向け画面からの遷移の場合true
     * @return array
     */
    private function getTeacherScheduleList($tid, $roomcd = null, $sid = null, $tutor = false)
    {
        // レギュラー＋個別講習の抽出条件
        $lesson_types = [AppConst::EXT_GENERIC_MASTER_109_0, AppConst::EXT_GENERIC_MASTER_109_1];

        // 教師No.に紐づくスケジュール（レギュラー・個別講習）を取得する。
        $query = ExtSchedule::query();
        $lessons = $query
            ->select(
                'id',
                'lesson_date',
                'start_time'
            )
            ->where('ext_schedule.tid', '=', $tid)
            ->whereIn('ext_schedule.lesson_type',  $lesson_types)
            // $tutorがtrueの場合（教師向け画面からの遷移）のみ絞り込み
            ->when($tutor, function ($query) {
                // 後日振替のレコードを除外 -> 後日振替かつ振替日設定済みのレコードを除外とする
                return $query->where(function ($orQuery) {
                    // 出欠・振替コードが2（振替）かつ 振替区分1 のレコードを除外  ※NULLのものを含む
                    $orQuery->whereNotIn('ext_schedule.atd_status_cd', [AppConst::ATD_STATUS_CD_2])
                        ->orWhere('ext_schedule.transefer_kind_cd', '!=', AppConst::TRANSEFER_KIND_CD_1)
                        ->orWhereNull('ext_schedule.atd_status_cd');
                });
            })
            // 教室が指定された場合のみ絞り込み
            ->when($roomcd, function ($query) use ($roomcd) {
                return $query->where('.roomcd', $roomcd);
            })
            // 生徒IDが指定された倍のみ絞り込み
            ->when($sid, function ($query) use ($sid) {
                return $query->where('.sid', $sid);
            })
            ->orderBy('ext_schedule.lesson_date', 'asc')
            ->orderBy('ext_schedule.start_time', 'asc')
            ->get();

        return $lessons;
    }
}
