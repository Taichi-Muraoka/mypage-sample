<?php

namespace App\Console\Commands;

use App\Exceptions\ReadDataValidateException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\ExtSchedule;
use App\Models\BatchMng;
use App\Models\ExtStudentKihon;
use App\Models\ExtTrialMaster;
use App\Http\Controllers\Traits\CtrlCsvTrait;
use App\Http\Controllers\Traits\CtrlFileTrait;
use App\Consts\AppConst;

/**
 * 年度スケジュール取込 - バッチ処理
 */
class YearScheduleImport extends Command
{
    // CSV共通処理
    use CtrlCsvTrait;
    use CtrlFileTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:yearScheduleImport {path} {account_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {

            // CSVファイルのパスと実行者のアカウントIDを受け取る
            $path = $this->argument("path");
            $account_id = $this->argument("account_id");
            $datas = [];

            Log::info("Batch yearScheduleImport Start, PATH: {$path}, ACCOUNT_ID: {$account_id}");

            $now = Carbon::now();

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_2;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = $account_id;
            $batchMng->save();

            $batch_id = $batchMng->batch_id;

            try {
                // Zipを解凍し、ファイルパス一覧を取得
                $opPathList = $this->unzip($path);
                // 今回は1件しか無いので、1件目を取得
                if (count($opPathList) != 1) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(csvファイル数不正)");
                }

                $csvPath = $opPathList[0];

                // CSVデータの読み込み
                $datas = $this->readData($csvPath);

                $ids = $datas["ids"];
                $datas = $datas["datas"];

                if (empty($datas)) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(データ件数不正)");
                }

                // Zip解凍ファイルのクリーンアップ
                $this->unzipCleanUp($opPathList);
            } catch (ReadDataValidateException  $e) {
                // 通常は事前にバリデーションするのでここはありえないのでエラーとする
                throw $e;
            }

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($datas, $ids, $batch_id) {

                $insertCount = 0;

                // CSVから抽出したsidを持つレコードを物理削除
                ExtSchedule::whereIn("sid", $ids)->forceDelete();

                // スケジュール情報テーブルの登録（Insert）
                foreach ($datas as $data) {

                    // 生徒基本情報に対象sidが存在しなければエラーとする
                    ExtStudentKihon::where('sid', $data['sid'])
                        ->firstOrFail();

                    // MEMO: 教室情報との整合性チェックは行わないものとする

                    // 模試申込の場合、模試マスタに対象tmidが存在しなければエラーとする
                    if ($data['lesson_type'] == AppConst::EXT_GENERIC_MASTER_109_3) {
                        ExtTrialMaster::where('tmid', $data['tmid'])
                            ->firstOrFail();
                    }

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
                    $insertCount++;
                }

                // バッチ管理テーブルのレコードを更新：正常終了
                $end = Carbon::now();
                BatchMng::where('batch_id', '=', $batch_id)
                    ->update([
                        'end_time' => $end,
                        'batch_state' => AppConst::CODE_MASTER_22_0,
                        'updated_at' => $end
                    ]);

                $insertCount = (string) $insertCount;

                Log::info("Insert {$insertCount} Records. yearScheduleImport Succeeded.");
            });
        } catch (\Exception  $e) {
            // バッチ管理テーブルのレコードを更新：異常終了
            $end = Carbon::now();
            BatchMng::where('batch_id', '=', $batch_id)
                ->update([
                    'end_time' => $end,
                    'batch_state' => AppConst::CODE_MASTER_22_1,
                    'updated_at' => $end
                ]);
            // この時点では補足できないエラーとして、詳細は返さずエラーとする
            Log::error($e);
        }
        // 念のため明示的に捨てる
        $datas = null;
        $ids = null;
    }

    /**
     * アップロードされたファイルを読み込む
     * バリデーションも行う
     *
     * @param $path
     * @return array データ
     */
    private function readData($path)
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
        $datas = [
            "datas" => [],
            "ids" => [],
        ];
        $headers = [];

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
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_T01') . "：データ項目不正)");
            }

            // MEMO: スケジュール情報は期間が絞られている前提とし授業日のチェックを行わない

            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // 不要な項目を削除
            unset($values['upduser']);

            // リストに保持しておく
            $datas["datas"][] = $values;
            $datas["ids"][] = $values["sid"];
        }

        // sidをユニークにする
        $datas["ids"] = array_unique($datas["ids"]);
        $datas["ids"] = array_values($datas["ids"]);

        return $datas;
    }
}
