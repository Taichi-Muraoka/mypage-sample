<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\BatchMng;
use App\Models\Student;
use Carbon\Carbon;
use App\Consts\AppConst;

/**
 * 生徒退会処理 - バッチ処理
 */
class StudentRecessSetting extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:studentRecessSetting';

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
     * @return int
     */
    public function handle()
    {
        try {

            Log::info("Batch studentRecessSetting Start.");

            $now = Carbon::now();

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_3;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = null;
            $batchMng->save();

            $batch_id = $batchMng->batch_id;

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($batch_id) {

                $today = Carbon::today()->format('y-m-d');
                //-------------------------
                // 対象生徒抽出
                //-------------------------
                $students = Student::select(
                    'student_id',
                )
                    // 生徒ステータス＝休塾予定
                    ->where('stu_status', AppConst::CODE_MASTER_28_2)
                    // 休塾開始日が当日以前
                    ->where('recess_start_date', '<=', $today)
                    ->get();

                // 対象生徒リスト
                $studentIds = [];
                foreach ($students as $student) {
                    array_push($studentIds, $student->student_id);
                }

                // 対象生徒ありの場合のみ以下の処理を行う
                if (count($studentIds) > 0) {
                    // 生徒毎の処理
                    foreach ($students as $student) {
                        //-------------------------
                        // 会員ステータス更新
                        //-------------------------
                        // 生徒情報
                        $updStudent = Student::where('student_id', $student->student_id)
                            ->firstOrFail();

                        // 会員ステータスを休塾に更新
                        $updStudent->stu_status = AppConst::CODE_MASTER_28_3;
                        // 保存
                        $updStudent->save();
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

                $updateCount = (string) count($studentIds);
                Log::info("Update {$updateCount} students. studentRecessSetting Succeeded.");
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
