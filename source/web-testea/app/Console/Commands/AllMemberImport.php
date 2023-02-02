<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ReadDataValidateException;
use App\Models\ExtStudentKihon;
use App\Models\Account;
use App\Models\BatchMng;
use App\Http\Controllers\Traits\CtrlCsvTrait;
use App\Http\Controllers\Traits\CtrlFileTrait;
use Carbon\Carbon;
use App\Consts\AppConst;

/**
 * 学年情報取込 - バッチ処理
 */
class AllMemberImport extends Command
{
    // CSV共通処理
    use CtrlCsvTrait;
    use CtrlFileTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:allMemberImport {path} {account_id}';

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
     * @param path
     * @param account_id
     * @return void
     */
    public function handle()
    {
        try {

            // CSVファイルのパスと実行者のアカウントIDを受け取る
            $path = $this->argument("path");
            $account_id = $this->argument("account_id");
            $datas = [];

            Log::info("Batch allMemberImport Start, PATH: {$path}, ACCOUNT_ID: {$account_id}");

            $now = Carbon::now();

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_1;
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
            DB::transaction(function () use ($datas, $batch_id) {

                $updateCount = 0;
                // スケジュールテーブルを登録・更新
                foreach ($datas as $data) {

                    // レコードが存在するかチェック(キーを指定)
                    $exists = ExtStudentKihon::where('sid', "=", $data['sid'])->exists();

                    if ($exists) {
                        $updateCount++;
                        $extStudentKihon = ExtStudentKihon::where(['sid' => $data['sid']])
                            ->firstOrFail();

                        $extStudentKihon['name'] = $data['name'];
                        $extStudentKihon['cls_cd'] = $data['cls_cd'];
                        $extStudentKihon['mailaddress1'] = $data['mailaddress1'];
                        $extStudentKihon['enter_date'] = $data['enter_date'];
                        $extStudentKihon['disp_flg'] = $data['disp_flg'];
                        $extStudentKihon['updtime'] = $data['updtime'];
                        $extStudentKihon->save();

                        // 既存のアカウントがある場合はアカウントのメールアドレスも更新する。
                        $accountExists = Account::where('account_id', '=', $data['sid'])
                            ->where('account_type', '=', AppConst::CODE_MASTER_7_1)
                            ->exists();

                        if ($accountExists) {

                            $account = Account::where('account_id', '=', $data['sid'])
                                ->where('account_type', '=', AppConst::CODE_MASTER_7_1)
                                ->firstOrFail();

                            $account['email'] = $data['mailaddress1'];
                            $account->save();
                        }
                    }
                }

                // バッチ管理テーブルのレコードを更新：正常終了
                $end = Carbon::now();
                BatchMng::where('batch_id', '=', $batch_id)
                    ->update([
                        'end_time' => $end,
                        'batch_state' => AppConst::CODE_MASTER_22_0,
                        'updated_at' => $end
                    ]);

                $updateCount = (string) $updateCount;
                Log::info("Update {$updateCount} Records. allMemberImport Succeeded.");
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
        // レスポンス
        $datas = [];

        // ヘッダ行
        $csvHeaders = ["sid", "name", "cls_cd", "mailaddress1", "disp_flg", "updtime", "upduser", "enter_date"];

        $headers = [];

        // CSV読み込み
        $file = $this->readCsv($path, "sjis");

        // 1行ずつ取得
        foreach ($file as $i => $line) {

            // 1行目がヘッダ行
            if ($i === 0) {
                //-------------
                // ヘッダ行
                //-------------
                $headers = $line;

                // [バリデーション] ヘッダが想定通りかチェック
                if ($headers !== $csvHeaders) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(" . config('appconf.upload_file_csv_name_A05') . "：ヘッダ行不正)");
                }

                continue;
            }

            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_A05') . "：データ列数不正)");
            }

            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $rules = [];
            $rules += ExtStudentKihon::fieldRules('sid', ['required']);
            $rules += ExtStudentKihon::fieldRules('name', ['required']);
            $rules += ExtStudentKihon::fieldRules('cls_cd', ['required']);
            $rules += ExtStudentKihon::fieldRules('mailaddress1', ['required']);
            $rules += ExtStudentKihon::fieldRules('enter_date', [], '_csv');
            $rules += ExtStudentKihon::fieldRules('disp_flg', ['required']);
            $rules += ExtStudentKihon::fieldRules('updtime', ['required'], '_csv');

            $validator = Validator::make($values, $rules);
            if ($validator->fails()) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                . config('appconf.upload_file_csv_name_A05')
                . "：データ項目不正( 生徒No=" . $values['sid'] . " )");
            }

            $values['sid'] = (int) $values['sid'];
            $values['disp_flg'] = (int) $values['disp_flg'];
            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // 不要な項目を削除
            unset($values['upduser']);

            array_push($datas, $values);
        }

        return $datas;
    }
}
