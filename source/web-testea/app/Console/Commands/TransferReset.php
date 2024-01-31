<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MstSystem;
use App\Models\MstGrade;
use App\Models\Schedule;
use App\Models\BatchMng;
use App\Http\Controllers\Traits\CtrlDateTrait;
use Carbon\Carbon;
use App\Consts\AppConst;

/**
 * 振替残数リセット - バッチ処理
 */
class TransferReset extends Command
{

    // 年度取得用
    use CtrlDateTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:transferReset';

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
            Log::info("Batch gradeUpdate Start.");

            $now = Carbon::now();

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_12;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = null;
            $batchMng->save();

            // バッヂID
            $batch_id = $batchMng->batch_id;
            
            // 前年度開始日を取得
            $year_start_date = $this->dtGetFiscalDate('prev', 'start');
            
            // 前年度終了日を取得
            $year_end_date = $this->dtGetFiscalDate('prev', 'end');

            // 未振替授業を取得
            $schedules = Schedule::where('absent_status', AppConst::CODE_MASTER_35_3)
                ->whereBetween('target_date', [$year_start_date, $year_end_date])
                ->get();

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($schedules, $batch_id) {

                

                

                // バッチ管理テーブルのレコードを更新：正常終了
                $end = Carbon::now();
                BatchMng::where('batch_id', '=', $batch_id)
                    ->update([
                        'end_time' => $end,
                        'batch_state' => AppConst::CODE_MASTER_22_0,
                        'updated_at' => $end
                    ]);

                Log::info("gradeUpdate Succeeded.");
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
    }
}
