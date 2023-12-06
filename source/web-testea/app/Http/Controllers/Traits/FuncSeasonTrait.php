<?php

namespace App\Http\Controllers\Traits;

use App\Models\YearlySchedule;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\TutorCampus;
use App\Models\SeasonMng;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 特別期間講習 - 機能共通処理
 */
trait FuncSeasonTrait
{

    //==========================
    // 関数名を区別するために
    // fncSasnを先頭につける
    //==========================

    /**
     * 特別期間プルダウンメニューのリストを取得
     * 管理者向け（教室管理者の場合は自分の校舎のみ）
     * 教室管理者以外は、指定されたcampusCdで検索
     *
     * @param string $campusCd 校舎コード 指定なしの場合null
     * @return array
     */
    protected function fncSasnGetGetSeasonList($campusCd = null)
    {
        // 現在日を取得
        $today = date("Y-m-d");

        // クエリを作成（特別期間講習管理）
        $query = SeasonMng::query();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、校舎コードで絞る
            $account = Auth::user();
            $query->where('campus_cd', $account->campus_cd);
        }
        // 校舎が指定された場合絞り込み
        $query->when($campusCd, function ($query) use ($campusCd) {
            return $query->where('campus_cd', $campusCd);
        });

        // プルダウンリストを取得する
        return $query->select(
            'season_cd as code',
            DB::raw('CONCAT(LEFT(season_cd, 4), "年", mst_codes.gen_item2) AS value')
        )
            // コードマスターとJOIN（期間区分）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on(DB::raw('RIGHT(season_cd, 2)'), '=', 'mst_codes.gen_item1')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_38);
            })
            // 講師受付開始日を過ぎたもの
            ->where('t_start_date', '<=', $today)
            ->orderby('season_cd', 'desc')
            ->distinct()
            ->get()
            ->keyBy('code');
    }

    /**
     * 年間予定から特別期間日付の取得（校舎・特別期間コード指定）
     *
     * @param string $campusCd 校舎コード
     * @param string $seasonCd 特別期間コード
     * @return array
     */
    private function fncSasnGetSeasonDate($campusCd, $seasonCd)
    {
        $account = Auth::user();

        // 年間予定情報から対象の特別期間の日付を取得
        $query = YearlySchedule::query();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('yearly_schedules.campus_cd', $account->campus_cd);
        }

        $lessonDates = $query
            ->select(
                'yearly_schedules.lesson_date',
                'mst_codes.name as dayname'
            )
            // コードマスターとJOIN（曜日）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('yearly_schedules.day_cd', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_16);
            })
            ->where('campus_cd', $campusCd)
            // 年度＝特別期間コードの上4桁
            ->where('school_year',  substr($seasonCd, 0, 4))
            // 期間区分＝特別期間コードの下2桁を数値変換
            ->where('date_kind', intval(substr($seasonCd, 4, 2)))
            ->orderBy('lesson_date')
            ->get();

        // 配列に格納
        $dateList = [];
        foreach ($lessonDates as $lessonDate) {
            array_push($dateList, [
                // 日付（区切り文字無し）をIDとして扱う
                'dateId' => $lessonDate->lesson_date->format('Ymd'),
                // 「月/日(曜日)」の形式に編集
                'dateLabel' => $lessonDate->lesson_date->format('m/d') . "(" . $lessonDate->dayname . ")",
                // 日付（ハイフン区切り）
                'dateYmd' => $lessonDate->lesson_date->format('Y-m-d'),
            ]);
        }

        return $dateList;
    }

    /**
     * 年間予定から特別期間日付の取得（講師ID・特別期間コード指定）
     *
     * @param int $tutorId 講師ID
     * @param string $seasonCd 特別期間コード
     * @return array
     */
    private function fncSasnGetSeasonDateForTutor($tutorId, $seasonCd)
    {
        // 年間予定情報から対象の特別期間の日付を取得
        $query = YearlySchedule::query();

        $lessonDates = $query
            ->select(
                'yearly_schedules.lesson_date',
                'mst_codes.name as dayname'
            )
            // コードマスターとJOIN（曜日）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('yearly_schedules.day_cd', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_16);
            })
            // 講師所属情報とJOIN
            ->sdJoin(TutorCampus::class, function ($join) use ($tutorId) {
                $join->on('yearly_schedules.campus_cd', '=', 'tutor_campuses.campus_cd')
                    ->where('tutor_campuses.tutor_id', $tutorId);
            })
            // 年度＝特別期間コードの上4桁
            ->where('school_year',  substr($seasonCd, 0, 4))
            // 期間区分＝特別期間コードの下2桁を数値変換
            ->where('date_kind', intval(substr($seasonCd, 4, 2)))
            ->orderBy('lesson_date')
            ->distinct()
            ->get();

        // 配列に格納
        $dateList = [];
        foreach ($lessonDates as $lessonDate) {
            array_push($dateList, [
                // 日付（区切り文字無し）をIDとして扱う
                'dateId' => $lessonDate->lesson_date->format('Ymd'),
                // 「月/日(曜日)」の形式に編集
                'dateLabel' => $lessonDate->lesson_date->format('m/d') . "(" . $lessonDate->dayname . ")",
                // 日付（ハイフン区切り）
                'dateYmd' => $lessonDate->lesson_date->format('Y-m-d'),
            ]);
        }

        return $dateList;
    }

    /**
     * チェックボックスの値を分割する
     * ある程度フォーマットのチェックは行っている
     *
     * @param string $value チェックボックスの値
     * @return array 配列
     */
    private function fncSasnSplitValue($value)
    {
        // パラメータ：
        // カンマ区切りで日付_時限 のように飛んでくる。
        // 20231225_1,20231226_2
        //
        // 戻り値：
        // array (
        //   0 =>
        //   array (
        //     'dateId' => '20231225',
        //     'lesson_date' => '2023-12-25',
        //     'period_no' => '1',
        //   ),
        //   1 =>
        //   array (
        //     'dateId' => '20231226',
        //     'lesson_date' => '2023-12-26',
        //     'period_no' => '2',
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

            $datePeriod = $this->fncSasnSplitDatePeriodKey($commaVal);

            array_push($rtn, $datePeriod);
        }

        return $rtn;
    }

    /**
     * 日付時限キー（日付_時限）を分割・整形する
     * ある程度フォーマットのチェックは行っている
     *
     * @param string $value 日付時限キー
     * @return object
     */
    private function fncSasnSplitDatePeriodKey($value)
    {
        // パラメータ：日付_時限
        // 20231225_1
        //
        // 戻り値：
        //   array (
        //     'dateId' => '20231225',
        //     'lesson_date' => '2023-12-25',
        //     'period_no' => '1',
        //   )

        // 空の場合は処理なし
        if (!filled($value)) {
            return null;
        }

        // アンダーバー区切りで分割
        $datePeriod = explode("_", $value);

        // 必ず2つになる
        if (count($datePeriod) != 2) {
            // 不正なエラー
            $this->illegalResponseErr();
        }

        // 20231011 -> 2023-10-11
        $dateId = $datePeriod[0];
        if (strlen($dateId) != 8) {
            // 不正なエラー
            $this->illegalResponseErr();
        }

        $rtnObj['dateId'] = $datePeriod[0];
        // ハイフン区切りの日付にする
        $rtnObj['lesson_date'] = substr($dateId, 0, 4) . '-' . substr($dateId, 4, 2) . '-' . substr($dateId, 6, 2);
        $rtnObj['period_no'] = $datePeriod[1];

        return $rtnObj;
    }

    /**
     * 特別期間講習管理 一覧画面表示対象の特別期間コード取得
     * システム日付の月により設定
     *
     * @return string
     */
    private function fncSasnGetDispSeasonCd()
    {
        // 現在月を取得
        $month = date("n");

        switch ($month) {
            case 2:
            case 3:
            case 4:
            case 5:
                // 2～5月の場合、当年の春期特別期間コードとする
                return date("Y") . AppConst::CODE_MASTER_38_GEN1_1;
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
                // 6～10月の場合、当年の夏期特別期間コードとする
                return date("Y") . AppConst::CODE_MASTER_38_GEN1_2;
            case 11:
            case 12:
                // 11～12月の場合、当年の冬期特別期間コードとする
                return date("Y") . AppConst::CODE_MASTER_38_GEN1_3;
            case 1:
                // 1月の場合、年が変わるため前年の冬期特別期間コードとする
                return date('Y', strtotime('-1 year')) . AppConst::CODE_MASTER_38_GEN1_3;
            default:
                // 不正なエラー
                $this->illegalResponseErr();
        }
    }
}
