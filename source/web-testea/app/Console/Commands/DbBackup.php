<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use App\Models\BatchMng;
use App\Consts\AppConst;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * データベースバックアップ - バッチ処理
 */
class DbBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:dbBackup';

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

            Log::info("Batch dbBackup Start.");

            $now = Carbon::now();
            $file_date = date('Ymd');

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_5;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = null;
            $batchMng->save();

            $batch_id = $batchMng->batch_id;

            // dumpコマンドを作成
            $host = env('DB_HOST');
            $user = env('DB_USERNAME');
            $pass = env('DB_PASSWORD');
            $db_name = env('DB_DATABASE');
            $dump_name = $file_date . '_' . $db_name . '.dump';
            $zip_name = $file_date . '_' . $db_name . '.zip';

            // 保存先を設定から取得
            $backup_dir = config('appconf.db_backup_dir');
            // appフォルダのフルパスを取得
            $backup_path = Storage::path($backup_dir);

            // コマンドは正常に実行されるがディレクトリがないということは考えづらいため、この時点でディレクトリ存在確認を行う
            if (!File::isDirectory($backup_path)) {
                throw new \Exception("ディレクトリが存在しません：" . $backup_path);
            }

            // コマンド実行する
            $command = "mysqldump --host {$host} --single-transaction --no-tablespaces -u{$user} -p{$pass} {$db_name} -r {$backup_path}{$dump_name} 2>&1";
            $rtnStr = exec($command, $output, $rtnSts);

            // コマンド実行のエラーがあったらthrowする
            if ($rtnSts !== 0) {
                throw new \Exception($rtnStr);
            }

            // zipで保存する
            $zip = new \ZipArchive();
            $zip->open($backup_path . $zip_name, \ZipArchive::CREATE);
            $zip->addFile($backup_path . $dump_name, $dump_name);
            $zip->close();

            // dumpファイルの削除
            File::delete($backup_path . $dump_name);

            $file_dates = [];
            $files = glob($backup_path . '*.zip');

            // 取得したファイル名の日付部分を配列化
            foreach ($files as $file) {
                $file_info = pathinfo($file);
                $file_name = explode('_', $file_info['filename']);
                array_push($file_dates, $file_name[0]);
            }

            // 日付配列を参照し、古いファイルを削除する
            rsort($file_dates);
            $file_count = 0;
            foreach ($file_dates as $file_date) {
                if ($file_count >= config('appconf.db_backup_generation')) {
                    File::delete($backup_path . $file_date . '_' . $db_name . '.zip');
                }
                $file_count++;
            }

            // バッチ管理テーブルのレコードを更新：正常終了
            $end = Carbon::now();
            BatchMng::where('batch_id', '=', $batch_id)
                ->update([
                    'end_time' => $end,
                    'batch_state' => AppConst::CODE_MASTER_22_0,
                    'updated_at' => $end
                ]);
            Log::info("dbBackup Succeeded.");
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
