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
use App\Models\Student;
use App\Models\Tutor;
use App\Models\MstCourse;
use Illuminate\Support\Facades\Mail;
use App\Mail\LessonReminder;
use Carbon\Carbon;
use App\Consts\AppConst;
use App\Libs\CommonDateFormat;
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

            $res = DB::select("show variables like 'wait_timeout';");
            Log::info($res);
            DB::statement('SET wait_timeout=1200');
            $res = DB::select("show variables like 'wait_timeout';");
            Log::info($res);

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
                // 対象スケジュール情報抽出
                //-------------------------
                $schedules = $this->getSchedule($tomorrow);
                Log::info($schedules);

                //-------------------------
                // 対象生徒ID抽出
                //-------------------------
                $studentIds = $schedules->whereNotNull('student_id')
                    ->unique('student_id')->pluck('student_id');
                Log::info($studentIds);

                //-------------------------
                // 対象講師ID抽出
                //-------------------------
                // 対象講師リスト
                $tutorIds = $schedules->whereNotNull('tutor_id')
                    ->unique('tutor_id')->pluck('tutor_id');
                Log::info($tutorIds);

                $sendCount = 0;
                // 生徒毎の処理
                foreach ($studentIds as $sid) {
                    //-------------------------
                    // メール送信(生徒宛)
                    //-------------------------
                    // 送信先メールアドレス取得
                    $studentEmail = $this->mdlGetStudentMailAll($sid);
                    Log::debug($studentEmail);
                    Log::debug(count($studentEmail));
                    // メールアドレス取得できる場合のみ、メール送信を行う
                    if (count($studentEmail) > 0) {

                        // メール送信数チェック
                        if ($sendCount + count($studentEmail) > self::MAIL_COUNT_MAX) {
                            // メール送信数がサーバーの15分毎の送信数上限を超える場合
                            // 15分sleep
                            Log::info("Send {$sendCount} mail. Sendmail wait...");
                            Sleep::for(15)->minutes();
                            $sendCount = 0;
                        }

                        // 対象生徒のスケジュール情報を取得
                        //（取得済みのcollectionより）
                        $stuSchedules = $schedules->where('student_id', $sid)
                            ->sortBy('target_date')->sortBy('start_time')
                            ->unique('schedule_id');

                        // メール本文に記載するスケジュール情報をセット
                        $name =  $stuSchedules->first()->student_name;
                        $lessons = [];
                        foreach ($stuSchedules as $schedule) {
                            $lessons[] = [
                                'date_time' => CommonDateFormat::formatYmdDay($schedule->target_date) . ' ' . $schedule->start_time->format('H:i'),
                                'campus_name' => $schedule->room_name,
                            ];
                        }
                        $mail_body = [
                            'name' => $name,
                            'lessons' => $lessons,
                        ];
                        // メール送信
                        Mail::to($studentEmail)->send(new LessonReminder($mail_body));
                        Log::channel('dailyMail')->info("LessonReminderMail student_id: " . $sid . ", to: " . $studentEmail);
                        $sendCount = $sendCount + count($studentEmail);
                        Log::debug("sendCount=" . $sendCount);
                    }
                }

                // 講師毎の処理
                foreach ($tutorIds as $tid) {
                    //-------------------------
                    // メール送信(講師宛)
                    //-------------------------
                    // 送信先メールアドレス取得
                    $tutorEmail = $this->mdlGetAccountMail($tid, AppConst::CODE_MASTER_7_2);
                    Log::debug($tutorEmail);
                    // メール送信数チェック
                    if ($sendCount + 1 > self::MAIL_COUNT_MAX) {
                        // メール送信数がサーバーの15分毎の送信数上限を超える場合
                        // 15分sleep
                        Log::info("Send {$sendCount} mail. Sendmail wait...");
                        Sleep::for(15)->minutes();
                        $sendCount = 0;
                    }
                    // 対象講師のスケジュール情報を取得
                    //（取得済みのcollectionより）
                    $teaSchedules = $schedules->where('tutor_id', $tid)
                        ->sortBy('target_date')->sortBy('start_time')
                        ->unique('schedule_id');

                    // メール本文に記載するスケジュール情報をセット
                    $name =  $teaSchedules->first()->tutor_name;
                    $lessons = [];
                    foreach ($teaSchedules as $schedule) {
                        $lessons[] = [
                            'date_time' => CommonDateFormat::formatYmdDay($schedule->target_date) . ' ' . $schedule->start_time->format('H:i'),
                            'campus_name' => $schedule->room_name,
                        ];
                    }
                    $mail_body = [
                        'name' => $name,
                        'lessons' => $lessons,
                    ];

                    // メール送信
                    Mail::to($tutorEmail)->send(new LessonReminder($mail_body));
                    Log::channel('dailyMail')->info("LessonReminderMail tutor_id: " . $tid . ", to: [\"" . $tutorEmail . "\"]");
                    $sendCount++;
                    Log::debug("sendCount=" . $sendCount);
                }

                // バッチ管理テーブルのレコードを更新：正常終了
                $end = Carbon::now();
                BatchMng::where('batch_id', '=', $batch_id)
                    ->update([
                        'end_time' => $end,
                        'batch_state' => AppConst::CODE_MASTER_22_0,
                        'updated_at' => $end
                    ]);

                Log::info("Send " . $sendCount . " mail.");
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

    /**
     * スケジュール情報の取得
     *
     * @param date $targetDate 対象日
     * @return mixed
     */
    private function getSchedule($targetDate)
    {
        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        $query = Schedule::query();

        $schedules = $query
            ->select(
                'schedules.schedule_id',
                'schedules.campus_cd',
                'room_names.room_name as room_name',
                'schedules.target_date',
                'schedules.period_no',
                'schedules.start_time',
                'schedules.end_time',
                'schedules.tutor_id',
                'tutors.name as tutor_name',
            )
            // 生徒ID取得
            ->selectRaw(
                "CASE
                    WHEN mst_courses.course_kind = ? THEN schedules.student_id
                    WHEN mst_courses.course_kind = ? THEN class_members.student_id
                    ELSE null
                END AS student_id",
                [AppConst::CODE_MASTER_42_1, AppConst::CODE_MASTER_42_2]
            )
            // 生徒名取得
            ->selectRaw(
                "CASE
                    WHEN mst_courses.course_kind = ? THEN students.name
                    WHEN mst_courses.course_kind = ? THEN class_students.name
                    ELSE null
                END AS student_name",
                [AppConst::CODE_MASTER_42_1, AppConst::CODE_MASTER_42_2]
            )
            // 受講生徒情報とJOIN
            ->sdLeftJoin(ClassMember::class, function ($join) {
                $join->on('schedules.schedule_id', 'class_members.schedule_id')
                    // 欠席者（予定）を除外
                    ->where('class_members.absent_status', '!=', AppConst::CODE_MASTER_35_6);
            })
            // 校舎名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('schedules.campus_cd', 'room_names.code');
            })
            // 生徒名の取得
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('schedules.student_id', 'students.student_id');
            })
            // 生徒名の取得（受講生徒情報）
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('class_members.student_id', 'class_students.student_id');
            }, 'class_students')
            // 講師名の取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('schedules.tutor_id', '=', 'tutors.tutor_id');
            })
            // コースマスタをJOIN
            ->sdJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', 'mst_courses.course_cd');
            })
            // 授業日＝対象日
            ->where('schedules.target_date', $targetDate)
            // 未振替・振替中・振替済・リセット済スケジュールを除外
            ->whereNotIn('schedules.absent_status', [AppConst::CODE_MASTER_35_3, AppConst::CODE_MASTER_35_4, AppConst::CODE_MASTER_35_5, AppConst::CODE_MASTER_35_7])
            // 仮登録スケジュールを除外
            ->where('schedules.tentative_status', '!=', AppConst::CODE_MASTER_36_1)
            // コース種別＝１対１授業または１対他授業
            ->whereIn('mst_courses.course_kind', [AppConst::CODE_MASTER_42_1, AppConst::CODE_MASTER_42_2])
            ->orderBy('schedules.target_date', 'asc')
            ->orderBy('schedules.start_time', 'asc')
            ->get();

        return $schedules;
    }
}
