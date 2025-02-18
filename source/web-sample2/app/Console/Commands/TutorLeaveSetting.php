<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Account;
use App\Models\BatchMng;
use App\Models\Tutor;
use App\Models\TutorFreePeriod;
use App\Models\TrainingBrowse;
use App\Models\Notice;
use App\Models\NoticeDestination;
use App\Models\SeasonTutorRequest;
use App\Models\SeasonTutorPeriod;
use Carbon\Carbon;
use App\Consts\AppConst;

/**
 * 講師退職処理 - バッチ処理
 */
class TutorLeaveSetting extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:tutorLeaveSetting';

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

            Log::info("Batch tutorLeaveSetting Start.");

            $now = Carbon::now();

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_2;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = null;
            $batchMng->save();

            $batch_id = $batchMng->batch_id;

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($batch_id) {

                $today = Carbon::today()->format('y-m-d');
                //-------------------------
                // 対象講師抽出
                //-------------------------
                $tutors = Tutor::select(
                    'tutor_id',
                )
                    // 講師ステータス＝退職処理中
                    ->where('tutor_status', AppConst::CODE_MASTER_29_2)
                    // 退職日が当日より前の日付（当日を含まない）
                    ->where('leave_date', '<', $today)
                    ->get();

                // 対象講師リスト
                $tutorIds = [];
                foreach ($tutors as $tutor) {
                    array_push($tutorIds, $tutor->tutor_id);
                }

                // 対象講師ありの場合のみ以下の処理を行う
                if (count($tutorIds) > 0) {
                    //-------------------------
                    // 申請データ削除（論理削除）
                    //-------------------------
                    // 講師空き時間情報 削除
                    TutorFreePeriod::whereIn('tutor_id', $tutorIds)
                        ->delete();

                    // 研修閲覧情報 削除
                    TrainingBrowse::whereIn('tutor_id', $tutorIds)
                        ->delete();

                    // お知らせ情報・お知らせ宛先情報
                    // お知らせ宛先情報から削除対象を抽出
                    $noticeQuery = NoticeDestination::query()
                        ->whereIn('tutor_id', $tutorIds);

                    $notices = $noticeQuery->select('notice_id')
                        ->get();

                    $noticeIds = [];
                    foreach ($notices as $notice) {
                        array_push($noticeIds, $notice->notice_id);
                    }

                    // お知らせ情報 削除
                    Notice::whereIn('notice_id', $noticeIds)
                        ->delete();

                    // お知らせ宛先情報 削除
                    $noticeQuery->delete();

                    // 特別期間講習 講師連絡情報・講師連絡コマ情報
                    // 講師連絡情報から削除対象を抽出
                    $seasonQuery = SeasonTutorRequest::query()
                        ->whereIn('tutor_id', $tutorIds);

                    $seasonTutors = $seasonQuery->select('season_tutor_id')
                        ->get();

                    $seasonTutorIds = [];
                    foreach ($seasonTutors as $seasonTutor) {
                        array_push($seasonTutorIds, $seasonTutor->season_tutor_id);
                    }

                    // 講師連絡コマ情報 削除
                    SeasonTutorPeriod::whereIn('season_tutor_id', $seasonTutorIds)
                        ->delete();

                    // 講師連絡情報 削除
                    $seasonQuery->delete();

                    // 講師毎の処理
                    foreach ($tutors as $tutor) {
                        //-------------------------
                        // 会員ステータス更新
                        //-------------------------
                        // 講師情報
                        $updTutor = Tutor::where('tutor_id', $tutor->tutor_id)
                            ->firstOrFail();

                        // 会員ステータスを退職済に更新
                        $updTutor->tutor_status = AppConst::CODE_MASTER_29_3;
                        // 保存
                        $updTutor->save();

                        //-------------------------
                        // ログイン可否の更新
                        //-------------------------
                        // アカウント情報
                        $account = Account::where('account_type', AppConst::CODE_MASTER_7_2)
                            ->where('account_id', $tutor->tutor_id)
                            ->firstOrFail();

                        // ログイン可否を不可に変更する
                        $account->login_flg = AppConst::CODE_MASTER_9_1;
                        // 保存
                        $account->save();
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

                $updateCount = (string) count($tutorIds);
                Log::info("Update {$updateCount} tutors. tutorLeaveSetting Succeeded.");
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
