<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\MstSystem;
use App\Models\MstGrade;
use App\Models\Student;
use App\Models\BatchMng;
use App\Http\Controllers\Traits\CtrlDateTrait;
use Carbon\Carbon;
use App\Consts\AppConst;

/**
 * 学年更新 - バッチ処理
 */
class GradeUpdate extends Command
{

    // 年度取得用
    use CtrlDateTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:gradeUpdate';

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
            $batchMng->batch_type = AppConst::BATCH_TYPE_11;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = null;
            $batchMng->save();

            // バッヂID
            $batch_id = $batchMng->batch_id;

            // システムマスタから現年度を取得
            $system = MstSystem::where('key_id', AppConst::SYSTEM_KEY_ID_1)
                ->firstOrFail();

            // 生徒情報を取得
            $students = Student::where('grade_cd', '<=', AppConst::GRADE_CD_12)
                ->select(
                    'student_id',
                    'birth_date',
                    'grade_cd',
                    'grade_year'
                )
                ->get();

            // 年度はじめを取得
            $next_year_start_date = date('Y', strtotime('+10 month')) . '0401';

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($system, $students, $next_year_start_date, $batch_id) {

                // システムマスタの現年度更新
                $system->value_num = $system->value_num + 1;
                $system->save();

                // 新年度の年齢での学年を取得
                $grade_age = MstGrade::whereNot('age', 0)
                    ->select('grade_cd', 'age')
                    ->get()
                    ->keyBy('age');

                // 生徒の学年更新
                foreach ($students as $student) {
                    // 誕生日
                    $birthday = $student->birth_date->format('Ymd');

                    // 年齢
                    $age = floor(($next_year_start_date - $birthday) / 10000);

                    // 次年度が19歳以上で現高3の生徒は大学生
                    if ($student->grade_cd == AppConst::GRADE_CD_12 && $age > AppConst::GRADE_CD_12_AGE) {
                        $next_grade_cd = AppConst::GRADE_CD_16;
                    }
                    else {
                        // 次年度の年齢
                        $next_grade_cd = $grade_age[$age]['grade_cd'];
                    }

                    // 学年コード更新
                    $student->grade_cd = $next_grade_cd;
                    // 学年設定年度更新
                    $student->grade_year = $system->value_num;
                    // 更新
                    $student->save();
                }

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
