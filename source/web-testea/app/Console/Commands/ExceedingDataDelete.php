<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\AbsentApply;
use App\Models\BatchMng;
use App\Models\Card;
use App\Models\Contact;
use App\Models\CourseApply;
use App\Models\Report;
use App\Models\RoomHoliday;
use App\Models\TimesReport;
use App\Models\TransferApply;
use App\Models\TutorRelate;
use App\Models\TutorSchedule;
use App\Models\Event;
use App\Models\EventApply;
use App\Models\ExtExtraIndividual;
use App\Models\ExtExtraIndDetail;
use App\Models\ExtHomeTeacherStd;
use App\Models\ExtHomeTeacherStdDetail;
use App\Models\ExtRegular;
use App\Models\ExtRegularDetail;
use App\Models\Grades;
use App\Models\GradesDetail;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\InvoiceImport;
use App\Models\Notice;
use App\Models\NoticeDestination;
use App\Models\Salary;
use App\Models\SalaryDetail;
use App\Models\SalaryImport;
use App\Models\TrainingContents;
use App\Models\TrainingBrowse;
use App\Models\ExtSchedule;
use App\Models\ExtTrialMaster;
use App\Models\TrialApply;
use App\Models\ExtRoom;
use App\Models\Account;
use App\Models\LeaveApply;
use App\Models\WeeklyShift;
use App\Models\Office;
use App\Models\ExtRirekisho;
use App\Models\ExtStudentKihon;
use App\Models\PasswordResets;
use App\Http\Controllers\Traits\CtrlDateTrait;
use App\Consts\AppConst;
use Carbon\Carbon;

/**
 * 保存期間超過データ削除 - バッチ処理
 */
class ExceedingDataDelete extends Command
{

    // 年度取得用
    use CtrlDateTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:exceedingDataDelete';

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

            Log::info("Batch exceedingDataDelete Start.");

            $now = Carbon::now();

            // 前年度の開始日を取得
            $prev_fiscal_start_date = $this->dtGetFiscalDate('prev', 'start');
            $prev_fiscal_start_timestamp = $prev_fiscal_start_date . ' 00:00:00';
            $prev_fiscal_start_dir_name = str_replace("/", "", $prev_fiscal_start_date) . "000000";

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_3;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = null;
            $batchMng->save();

            $batch_id = $batchMng->batch_id;

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($batch_id, $prev_fiscal_start_date, $prev_fiscal_start_timestamp) {

                //--------------------------
                // 単体で削除
                //--------------------------

                // 欠席申請削除（条件：授業日）
                $absent_apply_count = AbsentApply::where('lesson_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();

                // バッチ管理削除（条件：更新日時）
                $batch_mng_count = BatchMng::where('updated_at', '<', $prev_fiscal_start_timestamp)
                    ->forceDelete();

                // ギフトカード削除（条件：使用期間終了日）
                $card_count = Card::where('term_end', '<', $prev_fiscal_start_date)
                    ->forceDelete();

                // 問い合わせ情報削除（条件：更新日時）
                $contact_count = Contact::where('updated_at', '<', $prev_fiscal_start_timestamp)
                    ->forceDelete();

                // コース変更・授業追加申請削除（条件：更新日時）
                $course_apply_count = CourseApply::where('updated_at', '<', $prev_fiscal_start_timestamp)
                    ->forceDelete();

                // 授業報告書削除（条件：授業日）
                $report_count = Report::where('lesson_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();

                // 教室休業日削除（条件：休業日）
                $room_holiday_count = RoomHoliday::where('holiday_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();

                // 回数報告書削除（条件：報告月）
                $times_report_count = TimesReport::where('report_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();

                // 振替連絡削除（条件：振替日）
                $transfer_apply_count = TransferApply::where('transfer_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();

                // 教師関連情報削除（条件：削除日時）
                $tutor_relate_count = TutorRelate::where('deleted_at', '<', $prev_fiscal_start_timestamp)
                    ->forceDelete();

                // 教師スケジュール削除（条件：開催日）
                $tutor_schedule_count = TutorSchedule::where('start_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();


                //--------------------------
                // 詳細・親で削除
                //--------------------------

                // イベント----------------------------------------------------

                // イベントから削除対象を抽出（条件：開催日）
                $events_query = Event::query()
                    ->where('event_date', '<', $prev_fiscal_start_date);
                $events = $events_query->select('event_id')
                    ->withTrashed()
                    ->get();

                $event_ids = [];
                foreach ($events as $event) {
                    array_push($event_ids, $event->event_id);
                }

                // イベント申込から削除
                $event_apply_count = EventApply::whereIn('event_id', $event_ids)
                    ->forceDelete();

                // イベントから削除
                $event_count = $events_query->forceDelete();

                // getデータ破棄
                $events = null;


                // 個別講習情報----------------------------------------------------

                // 個別講習情報明細から削除（条件：講習日）
                $ext_extra_ind_detail_count = ExtExtraIndDetail::where('extra_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();

                // 個別講習情報から明細が1つもないレコードを抽出・削除
                $ext_extra_individuals = ExtExtraIndividual::select(
                    'ext_extra_individual.roomcd',
                    'ext_extra_individual.sid',
                    'ext_extra_individual.i_seq',
                    'ext_extra_ind_detail.extra_date'
                )
                    ->sdLeftJoin(ExtExtraIndDetail::class, function ($join) {
                        $join->on('ext_extra_individual.roomcd', '=', 'ext_extra_ind_detail.roomcd');
                        $join->on('ext_extra_individual.sid', '=', 'ext_extra_ind_detail.sid');
                        $join->on('ext_extra_individual.i_seq', '=', 'ext_extra_ind_detail.i_seq');
                    })
                    ->where('ext_extra_ind_detail.extra_date', '=', null)
                    ->withTrashed()
                    ->get();

                $ext_extra_individual_count = 0;
                foreach ($ext_extra_individuals as $data) {
                    $count = ExtExtraIndividual::where('roomcd', '=', $data->roomcd)
                        ->where('sid', '=', $data->sid)
                        ->where('i_seq', '=', $data->i_seq)
                        ->forceDelete();
                    $ext_extra_individual_count += $count;
                }

                // getデータ破棄
                $ext_extra_individuals = null;


                // 家庭教師標準----------------------------------------------------

                // 家庭教師標準から削除対象を抽出（条件：終了日）
                $ext_home_teacher_std_query = ExtHomeTeacherStd::query()
                    ->where('enddate', '<', $prev_fiscal_start_date);
                $ext_home_teacher_stds = $ext_home_teacher_std_query
                    ->select(
                        'roomcd',
                        'sid',
                        'std_seq'
                    )
                    ->withTrashed()
                    ->get();

                // 家庭教師標準詳細から削除
                $ext_home_teacher_std_detail_count = 0;
                foreach ($ext_home_teacher_stds as $data) {
                    $count = ExtHomeTeacherStdDetail::where('roomcd', '=', $data->roomcd)
                        ->where('sid', '=', $data->sid)
                        ->where('std_seq', '=', $data->std_seq)
                        ->forceDelete();
                    $ext_home_teacher_std_detail_count += $count;
                }

                // 家庭教師標準から削除
                $ext_home_teacher_std_count = $ext_home_teacher_std_query->forceDelete();

                // getデータ破棄
                $ext_home_teacher_stds = null;


                // 規定情報----------------------------------------------------

                // 規定情報から削除対象を抽出（条件：終了日）
                $ext_regular_query = ExtRegular::query()
                    ->where('enddate', '<', $prev_fiscal_start_date);
                $ext_regulars = $ext_regular_query
                    ->select(
                        'roomcd',
                        'sid',
                        'r_seq'
                    )
                    ->withTrashed()
                    ->get();

                // 規定情報明細から削除
                $ext_regular_detail_count = 0;
                foreach ($ext_regulars as $data) {
                    $count = ExtRegularDetail::where('roomcd', '=', $data->roomcd)
                        ->where('sid', '=', $data->sid)
                        ->where('r_seq', '=', $data->r_seq)
                        ->forceDelete();
                    $ext_regular_detail_count += $count;
                }

                // 規定情報から削除
                $ext_regular_count = $ext_regular_query->forceDelete();

                // getデータ破棄
                $ext_regulars = null;


                // 生徒成績情報----------------------------------------------------

                // 生徒成績情報から削除対象を抽出（条件：更新日時）
                $grades_query = Grades::query()
                    ->where('updated_at', '<', $prev_fiscal_start_timestamp);
                $grades = $grades_query->select('grades_id')
                    ->withTrashed()
                    ->get();

                // 生徒成績詳細情報から削除
                $grades_detail_count = 0;
                foreach ($grades as $data) {
                    $count = GradesDetail::where('grades_id', '=', $data->grades_id)
                        ->forceDelete();
                    $grades_detail_count += $count;
                }

                // 生徒成績情報から削除
                $grades_count = $grades_query->forceDelete();

                // getデータ破棄
                $grades = null;


                // 請求情報----------------------------------------------------

                // 請求情報明細から削除（条件：請求月）
                $invoice_detail_count = InvoiceDetail::where('invoice_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();

                // 請求情報から削除（条件：請求月）
                $invoice_count = Invoice::where('invoice_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();

                // 請求情報取込から削除（条件：請求月）
                $invoice_import_count = InvoiceImport::where('invoice_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();


                // お知らせ情報----------------------------------------------------

                // お知らせ情報から削除対象を抽出（条件：登録日時）
                $notice_query = Notice::query()
                    ->where('regist_time', '<', $prev_fiscal_start_timestamp);
                $notices = $notice_query->select('notice_id')
                    ->withTrashed()
                    ->get();

                // お知らせ宛先情報から削除
                $notice_destination_count = 0;
                foreach ($notices as $data) {
                    $count = NoticeDestination::where('notice_id', '=', $data->notice_id)
                        ->forceDelete();
                    $notice_destination_count += $count;
                }

                // お知らせ情報から削除
                $notice_count = $notice_query->forceDelete();

                // getデータ破棄
                $notices = null;


                // 給与情報----------------------------------------------------

                // 給与情報明細から削除（条件：給与月）
                $salary_detail_count = SalaryDetail::where('salary_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();

                // 給与情報から削除（条件：給与月）
                $salary_count = Salary::where('salary_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();

                // 給与情報取込から削除（条件：給与月）
                $salary_import_count = SalaryImport::where('salary_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();


                // 研修資料----------------------------------------------------

                // 研修資料から削除対象を抽出（条件：研修期限日）
                $training_contents_query = TrainingContents::query()
                    ->where('limit_date', '<', $prev_fiscal_start_date)
                    ->whereNotNull('limit_date');
                $training_contents = $training_contents_query->select('trn_id')
                    ->withTrashed()
                    ->get();

                // 研修閲覧から削除
                $training_browse_count = 0;
                foreach ($training_contents as $data) {
                    $count = TrainingBrowse::where('trn_id', '=', $data->trn_id)
                        ->forceDelete();
                    $training_browse_count += $count;
                }

                // 研修資料ファイル（ディレクトリごと）の削除
                $traning_files = $training_contents_query->select('trn_id')
                    ->where('trn_type', AppConst::CODE_MASTER_12_1)
                    ->withTrashed()
                    ->get();

                $traning_files_count = 0;
                $traning_files_dir = config('appconf.upload_dir_training');
                $traning_files_path = Storage::path($traning_files_dir);
                foreach ($traning_files as $data) {
                    $directory_path = $traning_files_path . (string) $data->trn_id . '/';
                    if (File::isDirectory($directory_path)) {
                        File::deleteDirectory($directory_path);
                        $traning_files_count++;
                    }
                }

                // 研修資料から削除
                $training_contents_count = TrainingContents::where('limit_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();

                // getデータ破棄
                $training_contents = null;
                $traning_files = null;


                //--------------------------
                // スケジュール・模試の削除
                //--------------------------

                // スケジュール情報----------------------------------------------------

                // スケジュール情報から削除（条件：授業日）
                $ext_schedule_count = ExtSchedule::where('lesson_date', '<', $prev_fiscal_start_date)
                    ->forceDelete();


                // 模試----------------------------------------------------

                // 模試マスタから削除対象を抽出（条件：受験日）
                $ext_trial_master_query = ExtTrialMaster::query()
                    ->where('trial_date', '<', $prev_fiscal_start_date);
                $trials = $ext_trial_master_query->select('tmid')
                    ->withTrashed()
                    ->get();

                // 模試申込から削除
                $trial_apply_count = 0;
                foreach ($trials as $data) {
                    $count = TrialApply::where('tmid', '=', $data->tmid)
                        ->forceDelete();
                    $trial_apply_count += $count;
                }

                // 模試マスタから削除
                $ext_trial_master_count = $ext_trial_master_query->forceDelete();

                // getデータ破棄
                $trials = null;


                //--------------------------
                // アカウントに連動しての削除
                //--------------------------

                // アカウントから削除対象を抽出（条件：削除日時）
                $account_query = Account::query()
                    ->where('deleted_at', '<', $prev_fiscal_start_timestamp);
                $accounts = $account_query->withTrashed()->get();

                // 生徒アカウントの削除対象
                $students = Account::where('account_type', '=', AppConst::CODE_MASTER_7_1)
                    ->where('deleted_at', '<', $prev_fiscal_start_timestamp)
                    ->withTrashed()
                    ->get();

                // 教師アカウントの削除対象
                $teachers = Account::where('account_type', '=', AppConst::CODE_MASTER_7_2)
                    ->where('deleted_at', '<', $prev_fiscal_start_timestamp)
                    ->withTrashed()
                    ->get();

                // 事務局アカウントの削除対象
                $officers = Account::where('account_type', '=', AppConst::CODE_MASTER_7_3)
                    ->where('deleted_at', '<', $prev_fiscal_start_timestamp)
                    ->withTrashed()
                    ->get();

                // 教室情報から削除
                $ext_room_count = 0;
                foreach ($students as $data) {
                    $count = ExtRoom::where('sid', '=', $data->account_id)
                        ->forceDelete();
                    $ext_room_count += $count;
                }

                // 退会申請から削除
                $leave_apply_count = 0;
                foreach ($students as $data) {
                    $count = LeaveApply::where('sid', '=', $data->account_id)
                        ->forceDelete();
                    $leave_apply_count += $count;
                }

                // 空き時間から削除
                $weekly_shift_count = 0;
                foreach ($teachers as $data) {
                    $count = WeeklyShift::where('tid', '=', $data->account_id)
                        ->forceDelete();
                    $weekly_shift_count += $count;
                }

                // 事務局アカウントから削除
                $office_count = 0;
                foreach ($officers as $data) {
                    $count = Office::where('adm_id', '=', $data->account_id)
                        ->forceDelete();
                    $office_count += $count;
                }

                // 履歴書から削除
                $ext_rirekisho_count = 0;
                foreach ($teachers as $data) {
                    $count = ExtRirekisho::where('tid', '=', $data->account_id)
                        ->forceDelete();
                    $ext_rirekisho_count += $count;
                }

                // 生徒基本情報から削除
                $ext_student_kihon_count = 0;
                foreach ($students as $data) {
                    $count = ExtStudentKihon::where('sid', '=', $data->account_id)
                        ->forceDelete();
                    $ext_student_kihon_count += $count;
                }

                // 保存期間超過した削除済みアカウントのメールアドレスを復元する
                $deleted_account_emails = [];
                foreach ($accounts as $data) {
                    $del_email = $data->email;
                    if (preg_match(config("appconf.delete_email_rule"), $del_email, $match)) {
                        $deleted_account_email = str_replace($match[1], "", $del_email);
                        array_push($deleted_account_emails, $deleted_account_email);
                    }
                }
                array_unique($deleted_account_emails);

                // パスワードリセットから削除
                $password_resets_count = 0;
                foreach ($deleted_account_emails as $email) {
                    $count = PasswordResets::where('email', '=', $email)
                        // 現在同じメールアドレスが使われている可能性があるため、削除対象を絞る
                        ->where('created_at', '<', $prev_fiscal_start_timestamp)
                        ->forceDelete();
                    $password_resets_count += $count;
                }

                // アカウント情報から削除
                $account_count = $account_query->forceDelete();

                // getデータ破棄
                $accounts = null;
                $students = null;
                $teachers = null;
                $officers = null;

                Log::info("
Delete {$absent_apply_count} Records From absent_apply.
Delete {$batch_mng_count} Records From batch_mng.
Delete {$card_count} Records From card.
Delete {$contact_count} Records From contact.
Delete {$course_apply_count} Records From course_apply.
Delete {$report_count} Records From report.
Delete {$room_holiday_count} Records From room_holiday.
Delete {$times_report_count} Records From times_report.
Delete {$transfer_apply_count} Records From transfer_apply.
Delete {$tutor_relate_count} Records From tutor_relate.
Delete {$tutor_schedule_count} Records From tutor_schedule.
Delete {$event_apply_count} Records From event_apply.
Delete {$event_count} Records From event.
Delete {$ext_extra_ind_detail_count} Records From ext_extra_ind_detail.
Delete {$ext_extra_individual_count} Records From ext_extra_individual.
Delete {$ext_home_teacher_std_detail_count} Records From ext_home_teacher_std_detail.
Delete {$ext_home_teacher_std_count} Records From ext_home_teacher_std.
Delete {$ext_regular_detail_count} Records From ext_regular_detail.
Delete {$ext_regular_count} Records From ext_regular.
Delete {$grades_detail_count} Records From grades_detail.
Delete {$grades_count} Records From grades.
Delete {$invoice_detail_count} Records From invoice_detail.
Delete {$invoice_count} Records From invoice.
Delete {$invoice_import_count} Records From invoice_import.
Delete {$notice_destination_count} Records From notice_destination.
Delete {$notice_count} Records From notice.
Delete {$salary_detail_count} Records From salary_detail.
Delete {$salary_count} Records From salary.
Delete {$salary_import_count} Records From salary_import.
Delete {$training_browse_count} Records From training_browse.
Delete {$training_contents_count} Records From training_contents And {$traning_files_count} Folders From {$traning_files_dir}.
Delete {$ext_schedule_count} Records From ext_schedule.
Delete {$trial_apply_count} Records From trial_apply.
Delete {$ext_trial_master_count} Records From ext_trial_master.
Delete {$ext_room_count} Records From ext_room.
Delete {$leave_apply_count} Records From leave_apply.
Delete {$weekly_shift_count} Records From weekly_shift.
Delete {$office_count} Records From office.
Delete {$ext_rirekisho_count} Records From ext_rirekisho.
Delete {$ext_student_kihon_count} Records From ext_student_kihon.
Delete {$password_resets_count} Records From password_resets.
Delete {$account_count} Records From account."
                );
            });

            //--------------------------------
            // 期限の過ぎた取込ファイルを削除する
            //--------------------------------

            // 削除するのは $prev_fiscal_start_dir_name より前のディレクトリ

            // 汎用マスタ取込ファイル削除
            $master_mng_dir = config('appconf.upload_dir_master_mng');
            $master_mng_dir_count = $this->deleteExceedingUploadDir($master_mng_dir, $prev_fiscal_start_dir_name);

            // 模試情報取込ファイル削除
            $trial_mng_dir = config('appconf.upload_dir_trial_mng');
            $trial_mng_dir_count = $this->deleteExceedingUploadDir($trial_mng_dir, $prev_fiscal_start_dir_name);

            // 教師情報取込ファイル削除
            $tutor_regist_dir = config('appconf.upload_dir_tutor_regist');
            $tutor_regist_dir_count = $this->deleteExceedingUploadDir($tutor_regist_dir, $prev_fiscal_start_dir_name);

            // 生徒情報取込ファイル削除
            $member_import_dir = config('appconf.upload_dir_member_import');
            $member_import_dir_count = $this->deleteExceedingUploadDir($member_import_dir, $prev_fiscal_start_dir_name);

            // スケジュール情報取込ファイル削除
            $schedule_import_dir = config('appconf.upload_dir_schedule_import');
            $schedule_import_dir_count = $this->deleteExceedingUploadDir($schedule_import_dir, $prev_fiscal_start_dir_name);

            // 給与情報取込ファイル削除
            $salary_import_dir = config('appconf.upload_dir_salary_import');
            $salary_import_dir_count = $this->deleteExceedingUploadDir($salary_import_dir, $prev_fiscal_start_dir_name);

            // 請求情報取込ファイル削除
            $invoice_import_dir = config('appconf.upload_dir_invoice_import');
            $invoice_import_dir_count = $this->deleteExceedingUploadDir($invoice_import_dir, $prev_fiscal_start_dir_name);

            // 年次学年情報取込ファイル削除
            $all_member_import_dir = config('appconf.upload_dir_all_member_import');
            $all_member_import_dir_count = $this->deleteExceedingUploadDir($all_member_import_dir, $prev_fiscal_start_dir_name);

            // 年度スケジュール情報取込ファイル削除
            $year_schedule_import_dir = config('appconf.upload_dir_year_schedule_import');
            $year_schedule_import_dir_count = $this->deleteExceedingUploadDir($year_schedule_import_dir, $prev_fiscal_start_dir_name);

            Log::info("
Delete {$master_mng_dir_count} Folders From {$master_mng_dir}.
Delete {$trial_mng_dir_count} Folders From {$trial_mng_dir}.
Delete {$tutor_regist_dir_count} Folders From {$tutor_regist_dir}.
Delete {$member_import_dir_count} Folders From {$member_import_dir}.
Delete {$schedule_import_dir_count} Folders From {$schedule_import_dir}.
Delete {$salary_import_dir_count} Folders From {$salary_import_dir}.
Delete {$invoice_import_dir_count} Folders From {$invoice_import_dir}.
Delete {$all_member_import_dir_count} Folders From {$all_member_import_dir}.
Delete {$year_schedule_import_dir_count} Folders From {$year_schedule_import_dir}."
            );

            // バッチ管理テーブルのレコードを更新：正常終了
            $end = Carbon::now();
            BatchMng::where('batch_id', '=', $batch_id)
                ->update([
                    'end_time' => $end,
                    'batch_state' => AppConst::CODE_MASTER_22_0,
                    'updated_at' => $end
                ]);

            Log::info("Delete Records And Folders. exceedingDataDelete Succeeded.");

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
     * 保存期間を過ぎたアップロードファイルをディレクトリごと削除する
     * @param $dir 検索対象となる親ディレクトリ $date より前のディレクトリを削除
     * @return int $delete_count 削除したディレクトリ数
     */
    public function deleteExceedingUploadDir($dir, $date)
    {
        $delete_count = 0;
        $path = Storage::path($dir);
        if (File::isDirectory($path)) {
            $directories = Storage::directories($dir);
            // 取得したファイル名の日付部分を配列化
            foreach ($directories as $directory) {
                $directory_date = explode('/', $directory);
                if ((int) $directory_date[array_key_last($directory_date)] < (int) $date) {
                    File::deleteDirectory($path . $directory_date[array_key_last($directory_date)]);
                    $delete_count++;
                }
            }
        }
        return $delete_count;
    }
}
