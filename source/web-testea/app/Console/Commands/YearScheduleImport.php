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

            try {
                // CSVデータの読み込み
                $datas = $this->readData($path);

                $datas = $datas["datas"];

                if (empty($datas)) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(データ件数不正)");
                }
                
            } catch (ReadDataValidateException  $e) {
                // 通常は事前にバリデーションするのでここはありえないのでエラーとする
                throw $e;
            }

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($datas) {

                $insertCount = 0;

                // スケジュール情報テーブルの登録（Insert）
                foreach ($datas as $data) {

                    $yearlySchedule = new YearlySchedule;
                    $yearlySchedule['school_year'] = $data['school_year'];
                    $yearlySchedule['campus_cd'] = $data['campus_cd'];
                    $yearlySchedule['lesson_date'] = $data['lesson_date'];
                    $yearlySchedule['day_cd'] = $data['day_cd'];
                    $yearlySchedule['date_kind'] = $data['date_kind'];
                    $yearlySchedule['school_month'] = $data['school_month'];
                    $yearlySchedule['week_count'] = $data['week_count'];
                    
                    $yearlySchedule->save();
                    $insertCount++;
                }

                $insertCount = (string) $insertCount;

                Log::info("Insert {$insertCount} Records. yearScheduleImport Succeeded.");
            });
        } catch (\Exception  $e) {
            // この時点では補足できないエラーとして、詳細は返さずエラーとする
            Log::error($e);
        }
        // 念のため明示的に捨てる
        $datas = null;
        $ids = null;
    }

    // /**
    //  * アップロードされたファイルを読み込む
    //  * バリデーションも行う
    //  *
    //  * @param $path
    //  * @return array データ
    //  */
    // private function readData($path)
    // {

    //     // CSVのヘッダ項目
    //     $csvHeaders = [
    //         'lesson_date',
    //         'day_cd',
    //         'date_kind_name',
    //         'date_kind',
    //         'school_month',
    //         'week_count'
    //     ];

    //     // CSVのデータをリストで保持
    //     $datas = [
    //         "datas" => [],
    //         "ids" => [],
    //     ];
    //     $headers = [];

    //     // CSV読み込み
    //     $file = $this->readCsv($path, "sjis");
    //     // 1行ずつ取得
    //     foreach ($file as $i => $line) {
    //         if ($i === 0) {
    //             //-------------
    //             // ヘッダ行
    //             //-------------
    //             $headers = $line;

    //             // [バリデーション] ヘッダが想定通りかチェック
    //             if ($headers !== $csvHeaders) {
    //                 throw new ReadDataValidateException(Lang::get('validation.invalid_file')
    //                     . "(" . config('appconf.upload_file_csv_name_T01') . "：ヘッダ行不正)");
    //             }
    //             continue;
    //         }

    //         //-------------
    //         // データ行
    //         //-------------
    //         // [バリデーション] データ行の列の数のチェック
    //         if (count($line) !== count($csvHeaders)) {
    //             throw new ReadDataValidateException(Lang::get('validation.invalid_file')
    //                 . "(" . config('appconf.upload_file_csv_name_T01') . "：データ列数不正)");
    //         }

    //         // headerをもとに、値をセットしたオブジェクトを生成
    //         $values = array_combine($headers, $line);

    //         // [バリデーション] データ行の値のチェック

    //         $validator = Validator::make(
    //             // 対象
    //             $values,
    //             // バリデーションルール
    //             YearlySchedule::fieldRules('school_year', ['required'], '_csv')
    //                 + YearlySchedule::fieldRules('campus_cd', ['required'], '_csv')
    //                 + YearlySchedule::fieldRules('lesson_date', ['required'], '_csv')
    //                 + YearlySchedule::fieldRules('day_cd', ['required'], '_csv')
    //                 + YearlySchedule::fieldRules('date_kind', ['required'], '_csv')
    //                 + YearlySchedule::fieldRules('school_month', ['required'], '_csv')
    //                 + YearlySchedule::fieldRules('week_count', ['required'], '_csv')
    //         );

    //         if ($validator->fails()) {
    //             throw new ReadDataValidateException(Lang::get('validation.invalid_file')
    //                 . "(" . config('appconf.upload_file_csv_name_T01') . "：データ項目不正)");
    //         }

    //         // MEMO: スケジュール情報は期間が絞られている前提とし授業日のチェックを行わない

    //         foreach ($values as $key => $val) {
    //             // 空白はnullに変換
    //             if ($values[$key] === '') {
    //                 $values[$key] = null;
    //             }
    //         }

    //         // 不要な項目を削除
    //         unset($values['upduser']);

    //         // リストに保持しておく
    //         $datas["datas"][] = $values;
    //         $datas["ids"][] = $values["sid"];
    //     }

    //     // sidをユニークにする
    //     $datas["ids"] = array_unique($datas["ids"]);
    //     $datas["ids"] = array_values($datas["ids"]);

    //     return $datas;
    // }
}
