<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use App\Models\MstSystem;
use App\Models\BatchMng;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\MstCourse;
use Illuminate\Support\Facades\Mail;
use App\Mail\LessonReminder;
use Carbon\Carbon;
use App\Consts\AppConst;
use App\Http\Controllers\Traits\CtrlModelTrait;

/**
 * 授業リマインドメール配信 - バッチ処理
 */
class LessonReminderMail extends Command
{

    // モデル共通処理
    use CtrlModelTrait;

    /**
     * メール送信数チェック
     */
    const MAIL_COUNT_MAX = 250;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:lessonReminderMail';

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

            Log::info("Batch lessonReminderMail Start.");

            $now = Carbon::now();

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_4;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = null;
            $batchMng->save();

            $batch_id = $batchMng->batch_id;

            //-------------------------
            // メール配信可否判定
            //-------------------------
            // システムマスタから配信可否を取得
            $sendFlg = MstSystem::where('key_id', AppConst::SYSTEM_KEY_ID_5)
                ->whereNotNull('value_num')
                ->firstOrFail();

            if ($sendFlg->value_num != AppConst::CODE_MASTER_9_0) {
                // メール配信可否＝可でない場合、以下の処理をスキップする
                // バッチ管理テーブルのレコードを更新：正常終了
                $end = Carbon::now();
                BatchMng::where('batch_id', '=', $batch_id)
                    ->update([
                        'end_time' => $end,
                        'batch_state' => AppConst::CODE_MASTER_22_0,
                        'updated_at' => $end
                    ]);
                Log::info("Batch lessonReminderMail Skipped.");
                return 0;
            }

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($batch_id) {

                $tomorrow = Carbon::tomorrow()->format('y-m-d');
                //-------------------------
                // 対象生徒抽出
                //-------------------------
                // １対１授業の対象生徒取得クエリ
                $queryScheduleStudent = Schedule::select(
                    'student_id',
                )
                    // コースマスタをJOIN
                    ->sdJoin(MstCourse::class, function ($join) {
                        $join->on('schedules.course_cd', 'mst_courses.course_cd');
                    })
                    // 授業日＝翌日
                    ->where('schedules.target_date', $tomorrow)
                    // コース種別＝１対１授業
                    ->where('mst_courses.course_kind', AppConst::CODE_MASTER_42_1)
                    // 振替済・リセット済スケジュールを除外
                    ->whereNotIn('schedules.absent_status', [AppConst::CODE_MASTER_35_5, AppConst::CODE_MASTER_35_7])
                    ->whereNotNull('schedules.student_id');

                // １対他授業の対象生徒取得クエリ
                $queryClassMember = Schedule::select(
                    'class_members.student_id',
                )
                    // 受講生徒情報とJOIN
                    ->sdJoin(ClassMember::class, function ($join) {
                        $join->on('schedules.schedule_id', 'class_members.schedule_id');
                    })
                    // コースマスタをJOIN
                    ->sdJoin(MstCourse::class, function ($join) {
                        $join->on('schedules.course_cd', 'mst_courses.course_cd');
                    })
                    // 授業日＝翌日
                    ->where('schedules.target_date', $tomorrow)
                    // コース種別＝１対他授業
                    ->where('mst_courses.course_kind', AppConst::CODE_MASTER_42_2)
                    // 欠席者（予定）を除外
                    ->where('class_members.absent_status', '!=', AppConst::CODE_MASTER_35_6);

                // 2つのqueryをUNIONし、対象生徒リストを取得
                $students = $queryScheduleStudent
                    ->union($queryClassMember)
                    ->get();

                // 対象生徒リスト
                $studentIds = [];
                foreach ($students as $student) {
                    array_push($studentIds, $student->student_id);
                }
                //-------------------------
                // 対象講師抽出
                //-------------------------
                $tutors = Schedule::select(
                    'schedules.tutor_id',
                )
                    // コースマスタをJOIN
                    ->sdJoin(MstCourse::class, function ($join) {
                        $join->on('schedules.course_cd', 'mst_courses.course_cd');
                    })
                    // 授業日＝翌日
                    ->where('schedules.target_date', $tomorrow)
                    // 振替済・リセット済スケジュールを除外
                    ->whereNotIn('schedules.absent_status', [AppConst::CODE_MASTER_35_5, AppConst::CODE_MASTER_35_7])
                    // コース種別＝１対１授業または１対他授業
                    ->whereIn('mst_courses.course_kind', [AppConst::CODE_MASTER_42_1, AppConst::CODE_MASTER_42_2])
                    ->whereNotNull('tutor_id')
                    ->distinct()
                    ->get();

                // 対象講師リスト
                $tutorIds = [];
                foreach ($tutors as $tutor) {
                    array_push($tutorIds, $tutor->tutor_id);
                }

                $sendCount = 0;
                // 生徒毎の処理
                foreach ($studentIds as $sid) {
                    //-------------------------
                    // メール送信(生徒宛)
                    //-------------------------
                    // 送信先メールアドレス取得
                    $studentEmail = $this->mdlGetAccountMail($sid, AppConst::CODE_MASTER_7_1);
                    // メール本文固定
                    // メール送信
                    Mail::to($studentEmail)->send(new LessonReminder());
                    $sendCount++;

                    // メール送信数チェック
                    if ($sendCount >= self::MAIL_COUNT_MAX) {
                        // メール送信数がサーバーの15分毎の送信数上限を超える場合
                        // 15分sleep
                        Log::info("Send {$sendCount} mail. Sendmail wait...");
                        Sleep::for(15)->minutes();
                        $sendCount = 0;
                    }
                }

                // 講師毎の処理
                foreach ($tutorIds as $tid) {
                    //-------------------------
                    // メール送信(講師宛)
                    //-------------------------
                    // 送信先メールアドレス取得
                    $tutorEmail = $this->mdlGetAccountMail($tid, AppConst::CODE_MASTER_7_2);
                    // メール本文固定
                    // メール送信
                    Mail::to($tutorEmail)->send(new LessonReminder());
                    $sendCount++;

                    // メール送信数チェック
                    if ($sendCount >= self::MAIL_COUNT_MAX) {
                        // メール送信数がサーバーの15分毎の送信数上限を超える場合
                        // 15分sleep
                        Log::info("Send {$sendCount} mail. Sendmail wait...");
                        Sleep::for(15)->minutes();
                        $sendCount = 0;
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

                Log::info("lessonReminderMail Succeeded.");
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
