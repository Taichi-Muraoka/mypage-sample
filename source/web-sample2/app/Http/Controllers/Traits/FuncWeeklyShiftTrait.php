<?php

namespace App\Http\Controllers\Traits;

use App\Models\TutorFreePeriod;
use App\Models\RegularClass;

/**
 * 空き時間 - 機能共通処理
 */
trait FuncWeeklyShiftTrait
{

    //==========================
    // 関数名を区別するために
    // fncWksfを先頭につける
    //==========================

    /**
     * 講師の空き時間の取得
     *
     * @param int $tutorId 講師ID
     * @return array
     */
    private function fncWksfGetFreePeriod($tutorId)
    {
        // 講師の空き時間を取得する
        $freePeriods = tutorFreePeriod::select(
            'day_cd',
            'period_no',
        )
            // 講師IDを指定
            ->where('tutor_id', $tutorId)
            ->orderBy('day_cd')
            ->orderBy('period_no')
            ->get();

        // チェックボックスをセットするための値を生成
        // 曜日コード_時限No 例：['1_1', '2_1']
        $chkData = [];
        foreach ($freePeriods as $freePeriod) {
            // 配列に追加
            array_push($chkData, $freePeriod->day_cd . '_' . $freePeriod->period_no);
        }

        return $chkData;
    }

    /**
     * 講師のレギュラー授業情報の取得
     *
     * @param int $tutorId 講師ID
     * @return array
     */
    private function fncWksfGetRegularClass($tutorId)
    {
        // レギュラー授業情報を取得し、黒色表示するための値を生成
        // データを取得（レギュラースケジュール情報）
        $regulars = RegularClass::select(
            'day_cd',
            'period_no',
        )
            // 講師IDを指定
            ->where('tutor_id', $tutorId)
            ->orderBy('day_cd')
            ->orderBy('period_no')
            ->distinct()
            ->get();

        // チェックボックスをセットするための値を生成
        // 曜日コード_時限No 例：['1_1', '2_1']
        $regularData = [];
        foreach ($regulars as $regular) {
            // 配列に追加
            array_push($regularData, $regular->day_cd . '_' . $regular->period_no);
        }

        return $regularData;
    }

    /**
     * チェックボックスの値を分割する
     * ある程度フォーマットのチェックは行っている
     *
     * @param string $value チェックボックスの値
     * @return array 配列
     */
    private function fncWksfSplitValue($value)
    {
        // パラメータ：
        // カンマ区切りで曜日_時限 のように飛んでくる。
        // 1_1,2_3
        //
        // 戻り値：
        // array (
        //   0 =>
        //   array (
        //     'dayCd' => '1',
        //     'periodNo' => '1',
        //   ),
        //   1 =>
        //   array (
        //     'dayCd' => '2',
        //     'periodNo' => '33',
        //   ),
        // )
        $rtn = [];

        // 空の場合は処理なし
        if (!filled($value)) {
            return $rtn;
        }

        // カンマ区切りで分割
        $commaList = explode(",", $value);

        foreach ($commaList as $commaVal) {

            // アンダーバー区切りで分割
            $weekDayPeriod = explode("_", $commaVal);

            // 必ず2つになる
            if (count($weekDayPeriod) != 2) {
                // 不正なエラー
                $this->illegalResponseErr();
            }

            array_push($rtn, [
                'dayCd' => $weekDayPeriod[0],
                'periodNo' => $weekDayPeriod[1],
            ]);
        }

        return $rtn;
    }
}
