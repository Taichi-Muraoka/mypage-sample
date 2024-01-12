<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Account;
use App\Models\BatchMng;
use App\Models\Student;
use App\Models\StudentEnterHistory;
use App\Models\AbsentApplication;
use App\Models\ExtraClassApplication;
use App\Models\Conference;
use App\Models\ConferenceDate;
use App\Models\SeasonStudentRequest;
use App\Models\SeasonStudentPeriod;
use App\Models\SeasonStudentTime;
use Carbon\Carbon;
use App\Consts\AppConst;

/**
 * 生徒退会処理 - バッチ処理
 */
class StudentLeaveSetting extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:studentLeaveSetting';

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

            Log::info("Batch studentLeaveSetting Start.");

            $now = Carbon::now();

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_1;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = null;
            $batchMng->save();

            $batch_id = $batchMng->batch_id;

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($batch_id) {

                //$today = date('Y-m-d');
                $today = Carbon::today()->format('y-m-d');
                //-------------------------
                // 対象生徒抽出
                //-------------------------
                $students = Student::select(
                    'student_id',
                    'enter_date',
                    'leave_date',
                    'past_enter_term',
                )
                    // 生徒ステータス＝退会処理中
                    ->where('stu_status', AppConst::CODE_MASTER_28_4)
                    // 退会日が当日以前
                    ->where('leave_date', '<=', $today)
                    ->get();

                // 対象生徒リスト
                $studentIds = [];
                foreach ($students as $student) {
                    array_push($studentIds, $student->student_id);
                }

                // 対象生徒ありの場合のみ以下の処理を行う
                if (count($studentIds) > 0) {
                    //-------------------------
                    // 申請データ削除（論理削除）
                    //-------------------------
                    // 欠席申請 削除
                    AbsentApplication::whereIn('student_id', $studentIds)
                        ->delete();

                    // 追加授業依頼情報 削除
                    ExtraClassApplication::whereIn('student_id', $studentIds)
                        ->delete();

                    // 面談連絡情報・面談日程情報
                    // 面談連絡情報から削除対象を抽出
                    $conferenceQuery = Conference::query()
                        ->whereIn('student_id', $studentIds);

                    $conferences = $conferenceQuery->select('conference_id')
                        ->get();

                    $conferrenceIds = [];
                    foreach ($conferences as $conference) {
                        array_push($conferrenceIds, $conference->conference_id);
                    }

                    // 面談日程情報 削除
                    ConferenceDate::whereIn('conference_id', $conferrenceIds)
                        ->delete();

                    // 面談連絡情報 削除
                    $conferenceQuery->delete();

                    // 特別期間講習 生徒連絡情報・生徒連絡コマ情報・生徒実施回数情報
                    // 生徒連絡情報から削除対象を抽出
                    $seasonQuery = SeasonStudentRequest::query()
                        ->whereIn('student_id', $studentIds);

                    $seasonStudents = $seasonQuery->select('season_student_id')
                        ->get();

                    $seasonStudentIds = [];
                    foreach ($seasonStudents as $seasonStudent) {
                        array_push($seasonStudentIds, $seasonStudent->season_student_id);
                    }

                    // 生徒連絡コマ情報 削除
                    SeasonStudentPeriod::whereIn('season_student_id', $seasonStudentIds)
                        ->delete();

                    // 生徒実施回数情報 削除
                    SeasonStudentTime::whereIn('season_student_id', $seasonStudentIds)
                        ->delete();

                    // 生徒連絡情報 削除
                    $seasonQuery->delete();

                    // 生徒毎の処理
                    foreach ($students as $student) {
                        //-------------------------
                        // 入退会履歴情報登録
                        //-------------------------
                        // 通塾期間算出
                        // 退会日 - 入会日 の月数
                        $startDate = $student->enter_date->startOfMonth();
                        $endDate = $student->leave_date->endOfMonth();
                        $enterTerm = $startDate->diffInMonths($endDate) + 1;

                        $enterHistory = new StudentEnterHistory;
                        $enterHistory->student_id = $student->student_id;
                        $enterHistory->enter_date = $student->enter_date;
                        $enterHistory->leave_date = $student->leave_date;
                        $enterHistory->enter_term = $enterTerm;
                        $enterHistory->save();

                        //-------------------------
                        // 会員ステータス更新
                        //-------------------------
                        // 生徒情報
                        $updStudent = Student::where('student_id', $student->student_id)
                            ->firstOrFail();

                        // 会員ステータスを退会済に更新
                        $updStudent->stu_status = AppConst::CODE_MASTER_28_5;
                        // 過去通塾期間に今回の通塾期間を加算
                        $updStudent->past_enter_term = $student->past_enter_term + $enterTerm;
                        // 保存
                        $updStudent->save();

                        //-------------------------
                        // ログイン可否の更新
                        //-------------------------
                        // アカウント情報
                        $account = Account::where('account_type', AppConst::CODE_MASTER_7_1)
                            ->where('account_id', $student->student_id)
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

                $updateCount = (string) count($studentIds);
                Log::info("Update {$updateCount} students. studentLeaveSetting Succeeded.");
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
