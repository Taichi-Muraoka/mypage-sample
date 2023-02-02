<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\BatchMng;
use App\Models\InvoiceImport;
use App\Models\SalaryImport;
use App\Consts\AppConst;
use Carbon\Carbon;

/**
 * 年次初期データ作成 - バッチ処理
 */
class YearInitialDataInsert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:yearInitialDataInsert';

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
     * @return int
     */
    public function handle()
    {

        try {

            Log::info("Batch yearInitialDataInsert Start.");

            $now = Carbon::now();

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_5;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = null;
            $batchMng->save();

            $batch_id = $batchMng->batch_id;

            // 実行が4月のため当年と翌年を取得する。
            $current_year = date('Y');
            $next_year = date('Y', strtotime('+1 year'));

            // キーとなる配列を作成する
            $insert_keys = [
                "{$current_year}/04/01",
                "{$current_year}/05/01",
                "{$current_year}/06/01",
                "{$current_year}/07/01",
                "{$current_year}/08/01",
                "{$current_year}/09/01",
                "{$current_year}/10/01",
                "{$current_year}/11/01",
                "{$current_year}/12/01",
                "{$next_year}/01/01",
                "{$next_year}/02/01",
                "{$next_year}/03/01",
            ];

            // データ作成
            $invoice_datas = [];
            $salary_datas = [];
            foreach ($insert_keys as $key) {
                $invoice = [
                    'invoice_date' => $key,
                    'import_state' => AppConst::CODE_MASTER_20_0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
                array_push($invoice_datas, $invoice);
                $salary = [
                    'salary_date' => $key,
                    'import_state' => AppConst::CODE_MASTER_20_0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
                array_push($salary_datas, $salary);
            }

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($invoice_datas, $salary_datas, $batch_id) {

                InvoiceImport::insert($invoice_datas);
                SalaryImport::insert($salary_datas);

                // バッチ管理テーブルのレコードを更新：正常終了
                $end = Carbon::now();
                BatchMng::where('batch_id', '=', $batch_id)
                    ->update([
                        'end_time' => $end,
                        'batch_state' => AppConst::CODE_MASTER_22_0,
                        'updated_at' => $end
                    ]);

                Log::info("Insert Records. yearInitialDataInsert Succeeded.");
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

        return 0;
    }
}
