<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Lang;
use App\Models\MstSubject;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\Schedule;
use App\Models\BatchMng;
use App\Http\Controllers\Traits\CtrlModelTrait;
use App\Http\Controllers\Traits\CtrlDateTrait;
use Carbon\Carbon;
use App\Consts\AppConst;

/**
 * 振替残数リセット - バッチ処理
 */
class TransferReset extends Command
{
    // モデル共通処理
    use CtrlModelTrait;

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
            Log::info("Batch transferReset Start.");

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

            // 校舎名取得のサブクエリ
            $campus_names = $this->mdlGetRoomQuery();

            // 未振替授業を取得
            $schedules = Schedule::where('absent_status', AppConst::CODE_MASTER_35_3)
                ->whereBetween('target_date', [$year_start_date, $year_end_date])
                ->select(
                    'schedule_id',
                    'target_date',
                    'period_no',
                    'campus_names.room_name as campus_name',
                    'students.name as student_name',
                    'tutors.name as tutor_name',
                    'mst_subjects.name as subject_name',
                    'absent_status'
                )
                // 校舎名の取得
                ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                    $join->on('schedules.campus_cd', '=', 'campus_names.code');
                })
                // 生徒名を取得
                ->sdLeftJoin(Student::class, 'schedules.student_id', '=', 'students.student_id')
                // 講師名を取得
                ->sdLeftJoin(Tutor::class, 'schedules.tutor_id', '=', 'tutors.tutor_id')
                // 教科名取得
                ->sdLeftJoin(MstSubject::class, 'schedules.subject_cd', '=', 'mst_subjects.subject_cd')
                ->get();

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($schedules, $now, $batch_id) {

                // CSV出力するデータの格納用配列
                $array_data = [];

                // 振替残数リセット処理
                foreach ($schedules as $schedule) {
                    // 出欠ステータスをリセット済みに更新
                    $schedule->absent_status = AppConst::CODE_MASTER_35_7;
                    // 更新
                    $schedule->save();

                    // csv出力用に必要データをまとめる
                    $data = [
                        $schedule->target_date->format('Y/m/d'),
                        $schedule->period_no . '限',
                        $schedule->campus_name,
                        $schedule->student_name,
                        $schedule->tutor_name,
                        $schedule->subject_name
                    ];

                    // csv
                    array_push($array_data, $data);
                }

                //---------------------
                // CSV出力
                //---------------------
                // 保存用のディレクトリ作成
                // バッチ処理開始日時を14桁の数値に変換 $dir_name例：20230301000000
                $dir_name = preg_replace('/[^0-9]/', '', $now);

                // $dir_path例：transfer_reset_data_backup/20230301000000
                $dir_path = config('appconf.transfer_reset_backup_dir') . $dir_name;
                Storage::makeDirectory($dir_path);
                $dir_path = Storage::path($dir_path);

                // ヘッダを取得
                $header[] = Lang::get('message.file.transfer_reset_output.header');

                // ヘッダ配列とデータ配列を結合
                $arrayCsv = array_merge($header, $array_data);

                //---------------------
                // ファイル名の取得と出力
                //---------------------
                $filename = Lang::get(
                    'message.file.transfer_reset_output.name',
                    [
                        'outputDate' => date("Ymd")
                    ]
                );

                // 保存先ファイルパス生成
                $file_path = $dir_path . '/' . $filename;

                // 書き込むファイルを開く（新規作成する）
                $fp = fopen($file_path, "w");

                // 一行ずつ書き込む
                foreach ($arrayCsv as $data) {
                    // 文字化け対策
                    mb_convert_variables('SJIS-win', 'UTF-8', $data);
                    fputcsv($fp, $data);
                }

                // ファイルを閉じる
                fclose($fp);

                // バッチ管理テーブルのレコードを更新：正常終了
                $end = $now;
                BatchMng::where('batch_id', '=', $batch_id)
                    ->update([
                        'end_time' => $end,
                        'batch_state' => AppConst::CODE_MASTER_22_0,
                        'updated_at' => $end
                    ]);

                Log::info("transferReset Succeeded.");
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
