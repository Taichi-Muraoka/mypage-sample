<?php

namespace App\Http\Controllers\Traits;

use App\Models\ExtSchedule;
use App\Models\ExtStudentKihon;
use App\Consts\AppConst;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\ReadDataValidateException;
use App\Models\ExtTrialMaster;
use App\Models\TutorRelate;
use Illuminate\Support\Facades\Lang;

/**
 * スケジュール取込 - 機能共通処理
 */
trait FuncScheduleTrait
{

    /**
     * アップロードされたファイルを読み込む（スケジュール情報）
     * バリデーションも行う
     *
     * @param $path
     * @return array データ
     */
    private function readDataT01($path)
    {

        // CSVのヘッダ項目
        $csvHeaders = [
            "id",
            "roomcd",
            "sid",
            "lesson_type",
            "symbol",
            "curriculumcd",
            "rglr_minutes",
            "gmid",
            "period_no",
            "tmid",
            "tid",
            "lesson_date",
            "start_time",
            "r_minutes",
            "end_time",
            "pre_tid",
            "pre_lesson_date",
            "pre_start_time",
            "pre_r_minutes",
            "pre_end_time",
            "chg_status_cd",
            "diff_time",
            "substitute_flg",
            "atd_status_cd",
            "status_info",
            "create_kind_cd",
            "transefer_kind_cd",
            "trn_lesson_date",
            "trn_start_time",
            "trn_r_minutes",
            "trn_end_time",
            "updtime",
            "upduser"
        ];
        // CSVのデータをリストで保持
        $datas = [];
        $headers = [];
        // 現在日時を取得
        $now = Carbon::now();
        // 前年度の開始日を取得
        $prevStart = new Carbon($this->dtGetFiscalDate('prev', 'start'));
        // CSV読み込み
        $file = $this->readCsv($path, "sjis");
        // 1行ずつ取得
        foreach ($file as $i => $line) {
            if ($i === 0) {
                //-------------
                // ヘッダ行
                //-------------
                $headers = $line;

                // [バリデーション] ヘッダが想定通りかチェック
                if ($headers !== $csvHeaders) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(" . config('appconf.upload_file_csv_name_T01') . "：ヘッダ行不正)");
                }
                continue;
            }

            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_T01') . "：データ列数不正)");
            }

            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make(
                // 対象
                $values,
                // バリデーションルール
                ExtSchedule::fieldRules('id', ['required'])
                    + ExtSchedule::fieldRules('roomcd', ['required'])
                    + ExtSchedule::fieldRules('sid', ['required'])
                    + ExtSchedule::fieldRules('lesson_type', ['required'])
                    + ExtSchedule::fieldRules('symbol', ['required'])
                    + ExtSchedule::fieldRules('curriculumcd')
                    + ExtSchedule::fieldRules('rglr_minutes')
                    + ExtSchedule::fieldRules('gmid')
                    + ExtSchedule::fieldRules('period_no')
                    + ExtSchedule::fieldRules('tmid')
                    + ExtSchedule::fieldRules('tid')
                    + ExtSchedule::fieldRules('lesson_date', ['required'], '_csv')
                    + ExtSchedule::fieldRules('start_time', [], '_csv')
                    + ExtSchedule::fieldRules('r_minutes')
                    + ExtSchedule::fieldRules('end_time', [], '_csv')
                    + ExtSchedule::fieldRules('pre_tid')
                    + ExtSchedule::fieldRules('pre_lesson_date', [], '_csv')
                    + ExtSchedule::fieldRules('pre_start_time', [], '_csv')
                    + ExtSchedule::fieldRules('pre_r_minutes')
                    + ExtSchedule::fieldRules('pre_end_time', [], '_csv')
                    + ExtSchedule::fieldRules('chg_status_cd')
                    + ExtSchedule::fieldRules('diff_time')
                    + ExtSchedule::fieldRules('substitute_flg')
                    + ExtSchedule::fieldRules('atd_status_cd')
                    + ExtSchedule::fieldRules('status_info')
                    + ExtSchedule::fieldRules('create_kind_cd', ['required'])
                    + ExtSchedule::fieldRules('transefer_kind_cd', ['required'])
                    + ExtSchedule::fieldRules('trn_lesson_date', [], '_csv')
                    + ExtSchedule::fieldRules('trn_start_time', [], '_csv')
                    + ExtSchedule::fieldRules('trn_r_minutes')
                    + ExtSchedule::fieldRules('trn_end_time', [], '_csv')
                    + ExtSchedule::fieldRules('updtime', ['required'], '_csv')
            );

            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('sid')) {
                    $errCol = "生徒No=" . $values['sid'];
                } else if ($validator->errors()->has('roomcd')) {
                    $errCol = "教室コード=" . $values['roomcd'];
                } else if ($validator->errors()->has('id')) {
                    $errCol = "スケジュールID=" . $values['id'];
                } else if ($validator->errors()->has('lesson_type')) {
                    $errCol = "授業分類コード=" . $values['lesson_type'];
                } else if ($validator->errors()->has('symbol')) {
                    $errCol = "スケジュール表示用シンボル=" . $values['symbol'];
                } else if ($validator->errors()->has('curriculumcd')) {
                    $errCol = "教科コード=" . $values['curriculumcd'];
                } else if ($validator->errors()->has('rglr_minutes')) {
                    $errCol = "規定時間数=" . $values['rglr_minutes'];
                } else if ($validator->errors()->has('gmid')) {
                    $errCol = "集団講習ID=" . $values['gmid'];
                } else if ($validator->errors()->has('period_no')) {
                    $errCol = "集団講習実施日連番=" . $values['period_no'];
                } else if ($validator->errors()->has('tmid')) {
                    $errCol = "模試ID=" . $values['tmid'];
                } else if ($validator->errors()->has('tid')) {
                    $errCol = "教師No=" . $values['tid'];
                } else if ($validator->errors()->has('lesson_date')) {
                    $errCol = "授業日=" . $values['lesson_date'];
                } else if ($validator->errors()->has('start_time')) {
                    $errCol = "授業開始時刻=" . $values['start_time'];
                } else if ($validator->errors()->has('r_minutes')) {
                    $errCol = "授業時間数=" . $values['r_minutes'];
                } else if ($validator->errors()->has('end_time')) {
                    $errCol = "終了時間=" . $values['end_time'];
                } else if ($validator->errors()->has('pre_tid')) {
                    $errCol = "初期教師No=" . $values['pre_tid'];
                } else if ($validator->errors()->has('pre_lesson_date')) {
                    $errCol = "初期授業日=" . $values['pre_lesson_date'];
                } else if ($validator->errors()->has('pre_start_time')) {
                    $errCol = "初期授業開始時刻=" . $values['pre_start_time'];
                } else if ($validator->errors()->has('pre_r_minutes')) {
                    $errCol = "初期授業時間数=" . $values['pre_r_minutes'];
                } else if ($validator->errors()->has('pre_end_time')) {
                    $errCol = "初期終了時間=" . $values['pre_end_time'];
                } else if ($validator->errors()->has('chg_status_cd')) {
                    $errCol = "時間変更区分=" . $values['chg_status_cd'];
                } else if ($validator->errors()->has('diff_time')) {
                    $errCol = "変更授業時間数=" . $values['diff_time'];
                } else if ($validator->errors()->has('substitute_flg')) {
                    $errCol = "代理フラグ=" . $values['substitute_flg'];
                } else if ($validator->errors()->has('atd_status_cd')) {
                    $errCol = "出欠・振替コード=" . $values['atd_status_cd'];
                } else if ($validator->errors()->has('status_info')) {
                    $errCol = "出欠補足情報=" . $values['status_info'];
                } else if ($validator->errors()->has('create_kind_cd')) {
                    $errCol = "作成区分コード=" . $values['create_kind_cd'];
                } else if ($validator->errors()->has('transefer_kind_cd')) {
                    $errCol = "振替区分コード=" . $values['transefer_kind_cd'];
                } else if ($validator->errors()->has('trn_lesson_date')) {
                    $errCol = "振替授業日=" . $values['trn_lesson_date'];
                } else if ($validator->errors()->has('trn_start_time')) {
                    $errCol = "振替授業開始時刻=" . $values['trn_start_time'];
                } else if ($validator->errors()->has('trn_r_minutes')) {
                    $errCol = "振替授業時間数=" . $values['trn_r_minutes'];
                } else if ($validator->errors()->has('trn_end_time')) {
                    $errCol = "振替終了時間=" . $values['trn_end_time'];
                } else if ($validator->errors()->has('updtime')) {
                    $errCol =  "更新日時=" . $values['updtime'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . config('appconf.upload_file_csv_name_T01')
                    . "：データ項目不正( 生徒No=" . $values['sid'] . ", "
                    . "教室コード=" . $values['roomcd'] . ", "
                    . "スケジュールID=" . $values['id'] . ", "
                    . "授業日=" . $values['lesson_date'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // MEMO: スケジュール情報は期間が絞られている前提とし授業日のチェックを行わない

            // MEMO: saveで登録するため、ここでの日時セット処理は不要
            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // 不要な項目を削除
            unset($values['upduser']);

            // リストに保持しておく
            $datas[] = $values;
        }
        return $datas;
    }

    /**
     * スケジュール情報テーブルデータ登録
     * （物理削除/登録）
     *
     * @param array $datas
     * @param bool $trial 模試申込の場合true
     * @param bool $delSkip 削除処理スキップ時はtrue
     * @return void
     */
    private function registT01($datas, $trial = false, $delSkip = false)
    {
        //------------------------------------
        // スケジュール情報テーブルの登録処理（Delete/Insert）
        //------------------------------------
        if (!$delSkip) {
            // 取込対象のsidを取得
            $sidList = [];
            foreach ($datas as $data) {
                array_push($sidList,  $data['sid']);
            }
            $sidList = array_unique($sidList);

            // 取込対象sidのデータをDelete(物理削除）
            ExtSchedule::whereIn('sid', $sidList)
                // 模試申込の場合、授業種別=模試のデータのみとする条件付加
                ->when($trial, function ($query) {
                    return $query->where('lesson_type', AppConst::EXT_GENERIC_MASTER_109_3);
                })
                ->forceDelete();
        }

        // 1行ずつ取得
        foreach ($datas as $data) {

            // 生徒基本情報に対象sidが存在しなければエラーとする
            ExtStudentKihon::where('sid', $data['sid'])
                ->firstOrFail();

            // MEMO: 教室情報との整合性チェックは行わないものとする

            // 模試申込の場合、模試マスタに対象tmidが存在しなければエラーとする
            if ($trial) {
                ExtTrialMaster::where('tmid', $data['tmid'])
                    ->firstOrFail();
            }

            // スケジュール情報テーブルの登録（Insert）
            $extSchedule = new ExtSchedule;
            $extSchedule['id'] = $data['id'];
            $extSchedule['roomcd'] = $data['roomcd'];
            $extSchedule['sid'] = $data['sid'];
            $extSchedule['lesson_type'] = $data['lesson_type'];
            $extSchedule['symbol'] = $data['symbol'];
            $extSchedule['curriculumcd'] = $data['curriculumcd'];
            $extSchedule['rglr_minutes'] = $data['rglr_minutes'];
            $extSchedule['gmid'] = $data['gmid'];
            $extSchedule['period_no'] = $data['period_no'];
            $extSchedule['tmid'] = $data['tmid'];
            $extSchedule['tid'] = $data['tid'];
            $extSchedule['lesson_date'] = $data['lesson_date'];
            $extSchedule['start_time'] = $data['start_time'];
            $extSchedule['r_minutes'] = $data['r_minutes'];
            $extSchedule['end_time'] = $data['end_time'];
            $extSchedule['pre_tid'] = $data['pre_tid'];
            $extSchedule['pre_lesson_date'] = $data['pre_lesson_date'];
            $extSchedule['pre_start_time'] = $data['pre_start_time'];
            $extSchedule['pre_r_minutes'] = $data['pre_r_minutes'];
            $extSchedule['pre_end_time'] = $data['pre_end_time'];
            $extSchedule['chg_status_cd'] = $data['chg_status_cd'];
            $extSchedule['diff_time'] = $data['diff_time'];
            $extSchedule['substitute_flg'] = $data['substitute_flg'];
            $extSchedule['atd_status_cd'] = $data['atd_status_cd'];
            $extSchedule['status_info'] = $data['status_info'];
            $extSchedule['create_kind_cd'] = $data['create_kind_cd'];
            $extSchedule['transefer_kind_cd'] = $data['transefer_kind_cd'];
            $extSchedule['trn_lesson_date'] = $data['trn_lesson_date'];
            $extSchedule['trn_start_time'] = $data['trn_start_time'];
            $extSchedule['trn_r_minutes'] = $data['trn_r_minutes'];
            $extSchedule['trn_end_time'] = $data['trn_end_time'];
            $extSchedule['updtime'] = $data['updtime'];
            $extSchedule->save();

            // ピンチヒッター（授業代行）教師情報を取得
            // （レギュラー授業 かつ 現在設定のtidが初期設定時と異なる場合）
            if ($data['lesson_type'] == AppConst::EXT_GENERIC_MASTER_109_0 && $data['tid'] != $data['pre_tid']) {
                // 対象データの有無チェック
                $exists = TutorRelate::where('roomcd', $data['roomcd'])
                    ->where('sid', $data['sid'])
                    ->where('tid', $data['tid'])
                    ->exists();

                if (!$exists) {
                    //------------------------------------
                    // 教師関連情報テーブルの登録処理(データがない場合のみInsert)
                    //------------------------------------
                    $tutorRelate = new TutorRelate;
                    $tutorRelate->roomcd = $data['roomcd'];
                    $tutorRelate->sid = $data['sid'];
                    $tutorRelate->tid = $data['tid'];
                    $tutorRelate->save();
                }
            }
        }
        return;
    }

    /**
     * sid登録済みチェック（バリデーション）
     * これから登録するデータのsidが生徒基本情報に登録されているか
     *
     * @param string $path ファイルパス
     * @return void
     */
    private function validationExistsSid($datas)
    {
        // 1行ずつ取得
        foreach ($datas as $data) {
            // 生徒基本情報に対象sidが存在するかチェック
            $exists = ExtStudentKihon::where('sid', $data['sid'])
                ->exists();

            if (!$exists) {
                // 未登録エラー
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(生徒No未登録)");
            }
        }
        return;
    }
}
