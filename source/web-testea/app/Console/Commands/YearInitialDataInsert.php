<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\BatchMng;
use App\Models\InvoiceImport;
use App\Models\SalaryImport;
use App\Consts\AppConst;
use App\Models\MstCampus;
use App\Models\MstSystem;
use App\Models\SalaryMng;
use App\Models\SeasonMng;
use App\Models\SeasonStudentRequest;
use App\Models\StudentCampus;
use App\Models\YearlySchedulesImport;
use App\Http\Controllers\Traits\CtrlModelTrait;
use App\Models\Student;
use Carbon\Carbon;

/**
 * 年次初期データ作成 - バッチ処理
 */
class YearInitialDataInsert extends Command
{
    // モデル共通処理
    use CtrlModelTrait;

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
            $batchMng->batch_type = AppConst::BATCH_TYPE_14;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = null;
            $batchMng->save();

            $batch_id = $batchMng->batch_id;

            // 新年度と翌年度を取得する。
            $mstSystem = MstSystem::select('value_num')
                ->where('key_id', AppConst::SYSTEM_KEY_ID_1)
                ->whereNotNull('value_num')
                ->firstOrFail();

            $current_year = $mstSystem->value_num;
            $next_year = $mstSystem->value_num + 1;

            // --------------------------
            // 請求取込情報
            // --------------------------
            // キーとなるdate配列を作成する
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
            // 新年度4月～翌年度3月の12ヶ月分
            $invoice_datas = [];
            foreach ($insert_keys as $key) {
                $invoice = [
                    'invoice_date' => $key,
                    'import_state' => AppConst::CODE_MASTER_20_0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
                array_push($invoice_datas, $invoice);
            }

            // --------------------------
            // 給与取込情報
            // 給与算出管理情報
            // --------------------------
            // キーとなるdate配列を作成する
            $insert_keys = [
                "{$current_year}/03/01",
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
            ];

            // データ作成
            // 新年度12ヶ月分
            $salary_import_datas = [];
            $salary_mng_datas = [];
            foreach ($insert_keys as $key) {
                $salary_import = [
                    'salary_date' => $key,
                    'import_state' => AppConst::CODE_MASTER_20_0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
                array_push($salary_import_datas, $salary_import);
                $salary_mng = [
                    'salary_date' => $key,
                    'state' => AppConst::CODE_MASTER_24_0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
                array_push($salary_mng_datas, $salary_mng);
            }

            // --------------------------
            // 年間予定取込情報
            // --------------------------
            // 校舎コード取得
            $campuses = MstCampus::select('campus_cd')->get();

            // データ作成
            // 翌年度 × 校舎数
            $yearly_schedules_import_datas = [];
            foreach ($campuses as $campus) {
                $yearly_schedules_import = [
                    'school_year' => $next_year,
                    'campus_cd' => $campus->campus_cd,
                    'import_state' => AppConst::CODE_MASTER_20_0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
                array_push($yearly_schedules_import_datas, $yearly_schedules_import);
            }

            // --------------------------
            // 特別期間講習管理
            // --------------------------
            // 特別期間コードの配列取得
            $season_cd_keys = $this->mdlFormatSeasonCd();

            // データ作成
            // 新年度：夏冬、翌年度：春 × 校舎数
            $season_mng_datas = [];
            foreach ($season_cd_keys as $season_cd) {
                // 新年度春期は作成済みのためスキップする
                if ($season_cd == "{$current_year}01") {
                    continue;
                }

                foreach ($campuses as $campus) {
                    $season_mng = [
                        'season_cd' => $season_cd,
                        'campus_cd' => $campus->campus_cd,
                        'status' => AppConst::CODE_MASTER_48_0,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                    array_push($season_mng_datas, $season_mng);
                }
            }

            // --------------------------
            // 特別期間講習 生徒連絡情報
            // --------------------------
            // 生徒所属情報取得
            $student_campuses = StudentCampus::select('student_campuses.student_id', 'student_campuses.campus_cd')
                // 生徒情報とJOIN
                ->sdLeftJoin(Student::class, 'students.student_id', '=', 'student_campuses.student_id')
                // 退会済の生徒は除外
                ->where('students.stu_status', '<>', AppConst::CODE_MASTER_28_5)
                ->orderBy('student_campuses.student_id', 'asc')
                ->orderBy('student_campuses.campus_cd', 'asc')
                ->get();

            // データ作成
            // 新年度：夏冬、翌年度：春 × 所属校舎
            $season_student_requests_datas = [];
            foreach ($student_campuses as $student_campus) {
                foreach ($season_cd_keys as $season_cd) {
                    // 新年度春期は作成済みのためスキップする
                    if ($season_cd == "{$current_year}01") {
                        continue;
                    }

                    $season_student_request = [
                        'student_id' => $student_campus->student_id,
                        'season_cd' => $season_cd,
                        'campus_cd' => $student_campus->campus_cd,
                        'regist_status' => AppConst::CODE_MASTER_5_0,
                        'plan_status' => AppConst::CODE_MASTER_47_0,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                    array_push($season_student_requests_datas, $season_student_request);
                }
            }

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($invoice_datas, $salary_import_datas, $salary_mng_datas, $yearly_schedules_import_datas, $season_mng_datas, $season_student_requests_datas, $batch_id) {

                InvoiceImport::insert($invoice_datas);
                SalaryImport::insert($salary_import_datas);
                SalaryMng::insert($salary_mng_datas);
                YearlySchedulesImport::insert($yearly_schedules_import_datas);
                SeasonMng::insert($season_mng_datas);
                SeasonStudentRequest::insert($season_student_requests_datas);

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
