<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Carbon;
use App\Models\Report;
use App\Models\ExtStudentKihon;
use Illuminate\Support\Facades\DB;

/**
 * 回数報告 - 機能共通処理
 */
trait FuncTimesTrait
{

    /**
     * 回数報告向けの「実施月」プルダウンの取得
     * 当月から過去2年分の月を取得（顧客要望により、前月からではなく当月からとする）
     *
     * @return array
     */
    private function getTimesDateList()
    {

        // 対象の月数(過去二年分)
        $TARGET_MONTHS = 24;

        // 当月から過去2年分の月を取得
        $date = new Carbon;
        $date->startOfMonth();
        $reportDate = [];

        // リストに加えるのは前年度の4月分まで
        $prevStartMonthNum = date('Y', strtotime('-1 year -3 month')) . '04';

        for ($i = 0; $i <= $TARGET_MONTHS; $i++) {

            // $iの数前の月を取得
            $date->subMonths($i);

            // 前年度の4月より前の場合はリストへの追加を終了する。
            if ((int)$date->format('Ym') < (int)$prevStartMonthNum) {
                break;
            }

            $reportDate += [
                $date->format('Y-m-d') => [
                    'value' => $date->format('Y/m')
                ]
            ];

            // インスタンスの値を元に戻す
            $date->addMonths($i);
        }

        return $reportDate;
    }

    /**
     * 教師の担当の生徒の対象期間のレポートを取得
     *
     * @param int $tid 教師ID
     * @param string $startMonth 日付
     * @return array
     */
    private function getStudentReportList($tid, $startMonth, $roomcd = null)
    {

        // 選択された月の最終日時を取得
        $endMonth = new Carbon($startMonth);
        $endMonth->endOfMonth();

        // 登録済みの授業報告一覧の取得
        $query = Report::query();
        $query->select(
            'lesson_date',
            'start_time',
            'ext_student_kihon.name',
            'r_minutes'
        )
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('report.sid', 'ext_student_kihon.sid');
            })
            ->where('report.tid', $tid)
            ->whereBetween('lesson_date', [$startMonth, $endMonth])
            ->orderBy('report.lesson_date')
            ->orderBy('report.start_time');

        if (!empty($roomcd)) {
            $query->where('report.roomcd', '=', $roomcd);
        }

        $reportList = $query->get();

        // 実施回数表示の取得
        $query = Report::query();
        $query->select(
            'ext_student_kihon.name',
            DB::raw('count(*) as name_count')
        )
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('report.sid', 'ext_student_kihon.sid');
            })
            ->where('report.tid', $tid)
            ->whereBetween('lesson_date', [$startMonth, $endMonth])
            ->groupBy('ext_student_kihon.name')
            ->groupBy('report.sid')
            ->orderBy('report.sid');

        if (!empty($roomcd)) {
            $query->where('report.roomcd', '=', $roomcd);
        }

        $countList = $query->get();

        return [
            // 授業一覧
            'reportList' => $reportList,
            // 生徒一覧(回数)
            'countList' => $countList
        ];
    }
}
