<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use App\Models\BatchMng;
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

            // 6年前の年度開始日を取得 例：今年度が2023なら2017/03/01
            $six_years_ago_fiscal_start_date = $this->dtGetFiscalDate('6yearsAgo', 'start');
            // 5年前
            $five_years_ago_fiscal_start_date = $this->dtGetFiscalDate('5yearsAgo', 'start');
            // 4年前
            $four_years_ago_fiscal_start_date = $this->dtGetFiscalDate('4yearsAgo', 'start');
            // 取込ファイル削除用 例：20230301000000
            $five_years_ago_fiscal_start_dir_name = str_replace("/", "", $five_years_ago_fiscal_start_date) . "000000";

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_13;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = null;
            $batchMng->save();

            $batch_id = $batchMng->batch_id;

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($now, $six_years_ago_fiscal_start_date, $five_years_ago_fiscal_start_date, $four_years_ago_fiscal_start_date) {

                // CSV出力する各テーブルデータの格納用配列
                $array_table_data = [];
                // CSV出力するアカウント情報テーブルデータの格納用配列
                $array_accounts_data = [];

                //--------------------------
                // 6年経過後に削除
                //--------------------------
                // 生徒----------------------------------------------------
                // --生徒所属・生徒受験・生徒入退会履歴・連絡記録・バッジ付与・パスワードリセット・アカウント情報（生徒）・生徒情報
                // --8テーブル

                //---------------------
                // CSV出力用のデータ抽出
                //---------------------
                // 生徒情報から削除対象を抽出（条件：退会日）
                // テーブル名を指定
                $table_data['table_name'] = 'students';
                // 対象データ取得用クエリ
                $students_query = DB::table($table_data['table_name'])
                    ->where('leave_date', '<', $six_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $students_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の生徒IDを取得
                $students = $students_query->select('student_id')->get();

                // 削除対象の生徒IDを配列化
                $student_ids = [];
                foreach ($students as $student) {
                    array_push($student_ids, $student->student_id);
                }

                // 削除対象の生徒IDを基に、アカウント情報から削除対象を取得
                // テーブル名を指定
                $table_data['table_name'] = 'accounts';
                // 対象データ取得用クエリ
                $student_accounts_query = DB::table($table_data['table_name'])
                    ->where('account_type', '=', AppConst::CODE_MASTER_7_1)
                    ->whereIn('account_id', $student_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $student_accounts_query->get()->toArray();
                // CSV出力用配列に追加
                // ※アカウント情報は別配列にまとめて格納
                $array_accounts_data = $table_data['data_list'];

                // 削除対象の生徒メールアドレスを取得
                $student_accounts = $student_accounts_query->select('email')->get();

                // 削除対象の生徒メールアドレスを配列化
                $student_emails = [];
                foreach ($student_accounts as $student_account) {
                    array_push($student_emails, $student_account->email);
                }

                // 生徒所属情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'student_campuses';
                // 対象データ取得用クエリ
                $student_campuses_query = DB::table($table_data['table_name'])
                    ->whereIn('student_id', $student_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $student_campuses_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 生徒受験情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'student_entrance_exams';
                // 対象データ取得用クエリ
                $student_entrance_exams_query = DB::table($table_data['table_name'])
                    ->whereIn('student_id', $student_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $student_entrance_exams_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 生徒入退会履歴情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'student_enter_histories';
                // 対象データ取得用クエリ
                $student_enter_histories_query = DB::table($table_data['table_name'])
                    ->whereIn('student_id', $student_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $student_enter_histories_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 連絡記録情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'records';
                // 対象データ取得用クエリ
                $records_query = DB::table($table_data['table_name'])
                    ->whereIn('student_id', $student_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $records_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // バッジ付与情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'badges';
                // 対象データ取得用クエリ
                $badges_query = DB::table($table_data['table_name'])
                    ->whereIn('student_id', $student_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $badges_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                //---------------------
                // データ削除
                //---------------------
                // 生徒所属情報から削除
                $student_campuses_count = $student_campuses_query->delete();

                // 生徒受験情報から削除
                $student_entrance_exams_count = $student_entrance_exams_query->delete();

                // 生徒入退会履歴情報から削除
                $student_enter_histories_count = $student_enter_histories_query->delete();

                // 連絡記録情報から削除
                $records_count = $records_query->delete();

                // バッジ付与情報から削除
                $badges_count = $badges_query->delete();

                // パスワードリセットから削除（CSV不要）
                $student_password_resets_count = PasswordResets::whereIn('email', $student_emails)
                    // 現在同じメールアドレスが使われている可能性があるため、削除対象を絞る
                    ->where('created_at', '<', $six_years_ago_fiscal_start_date)
                    ->forceDelete();

                // アカウント情報から削除
                $student_accounts_count = $student_accounts_query->delete();

                // 生徒情報から削除
                $students_count = $students_query->delete();

                // getデータ破棄
                $students = null;
                $student_accounts = null;

                //--------------------------
                // 5年経過後に削除
                //--------------------------
                // 授業----------------------------------------------------
                // --スケジュール情報・受講生徒情報
                // --欠席申請
                // --振替依頼情報・振替依頼日程情報
                // --追加授業依頼情報
                // --授業報告書・授業報告書教材単元情報
                // --8テーブル

                //---------------------
                // CSV出力用のデータ抽出
                //---------------------
                // --スケジュール情報・受講生徒情報----------------------------------------------------
                // スケジュール情報から削除対象を抽出（条件：日付）
                // テーブル名を指定
                $table_data['table_name'] = 'schedules';
                // 対象データ取得用クエリ
                $schedules_query = DB::table($table_data['table_name'])
                    ->where('target_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $schedules_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象のスケジュールIDを取得
                $schedules = $schedules_query->select('schedule_id')->get();

                // 削除対象のスケジュールIDを配列化
                $schedule_ids = [];
                foreach ($schedules as $schedule) {
                    array_push($schedule_ids, $schedule->schedule_id);
                }

                // 受講生徒情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'class_members';
                // 対象データ取得用クエリ
                $class_members_query = DB::table($table_data['table_name'])
                    ->whereIn('schedule_id', $schedule_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $class_members_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --欠席申請----------------------------------------------------
                // 欠席申請情報から削除対象を抽出（条件：申請日）
                // テーブル名を指定
                $table_data['table_name'] = 'absent_applications';
                // 対象データ取得用クエリ
                $absent_applications_query = DB::table($table_data['table_name'])
                    ->where('apply_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $absent_applications_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --振替依頼情報・振替依頼日程情報----------------------------------------------------
                // 振替依頼情報から削除対象を抽出（条件：依頼日）
                // テーブル名を指定
                $table_data['table_name'] = 'transfer_applications';
                // 対象データ取得用クエリ
                $transfer_applications_query = DB::table($table_data['table_name'])
                    ->where('apply_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $transfer_applications_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の振替依頼IDを取得
                $transfer_applications = $transfer_applications_query->select('transfer_apply_id')->get();

                // 削除対象の振替依頼IDを配列化
                $transfer_apply_ids = [];
                foreach ($transfer_applications as $t_application) {
                    array_push($transfer_apply_ids, $t_application->transfer_apply_id);
                }

                // 振替依頼日程情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'transfer_application_dates';
                // 対象データ取得用クエリ
                $transfer_application_dates_query = DB::table($table_data['table_name'])
                    ->whereIn('transfer_apply_id', $transfer_apply_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $transfer_application_dates_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --追加授業依頼情報----------------------------------------------------
                // 追加授業依頼情報から削除対象を抽出（条件：依頼日）
                // テーブル名を指定
                $table_data['table_name'] = 'extra_class_applications';
                // 対象データ取得用クエリ
                $extra_class_applications_query = DB::table($table_data['table_name'])
                    ->where('apply_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $extra_class_applications_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --授業報告書・授業報告書教材単元情報----------------------------------------------------
                // 授業報告書情報から削除対象を抽出（条件：授業日）
                // テーブル名を指定
                $table_data['table_name'] = 'reports';
                // 対象データ取得用クエリ
                $reports_query = DB::table($table_data['table_name'])
                    ->where('lesson_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $reports_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の授業報告書IDを取得
                $reports = $reports_query->select('report_id')->get();

                // 削除対象の授業報告書IDを配列化
                $report_ids = [];
                foreach ($reports as $report) {
                    array_push($report_ids, $report->report_id);
                }

                // 授業報告書教材単元情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'report_units';
                // 対象データ取得用クエリ
                $report_units_query = DB::table($table_data['table_name'])
                    ->whereIn('report_id', $report_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $report_units_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                //---------------------
                // データ削除
                //---------------------
                // 受講生徒情報から削除
                $class_members_count = $class_members_query->delete();

                // スケジュール情報から削除
                $schedules_count = $schedules_query->delete();

                // 欠席申請情報から削除
                $absent_applications_count = $absent_applications_query->delete();

                // 振替依頼日程情報から削除
                $transfer_application_dates_count = $transfer_application_dates_query->delete();

                // 振替依頼情報から削除
                $transfer_applications_count = $transfer_applications_query->delete();

                // 追加授業依頼情報から削除
                $extra_class_applications_count = $extra_class_applications_query->delete();

                // 授業報告書教材単元情報から削除
                $report_units_count = $report_units_query->delete();

                // 授業報告書情報から削除
                $reports_count = $reports_query->delete();

                // getデータ破棄
                $schedules = null;
                $transfer_applications = null;
                $reports = null;

                // 特別期間講習----------------------------------------------------
                // --特別期間講習管理・特別期間講習 生徒連絡情報・特別期間講習 生徒連絡コマ情報・特別期間講習 生徒実施回数情報・特別期間講習 講師連絡情報・特別期間講習 講師連絡コマ情報
                // --6テーブル

                //---------------------
                // CSV出力用のデータ抽出
                //---------------------
                // 特別期間講習管理から削除対象を抽出（条件：生徒受付終了日）
                // テーブル名を指定
                $table_data['table_name'] = 'season_mng';
                // 対象データ取得用クエリ
                $season_mng_query = DB::table($table_data['table_name'])
                    ->where('s_end_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $season_mng_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の特別期間コードを取得
                $season_mng = $season_mng_query->select('season_cd')
                    // 重複排除で取得する
                    ->distinct()
                    ->get();

                // 削除対象の特別期間コードを配列化
                $season_mng_cds = [];
                foreach ($season_mng as $s_mng) {
                    array_push($season_mng_cds, $s_mng->season_cd);
                }

                // 削除対象の特別期間コードを基に、特別期間講習 生徒連絡情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'season_student_requests';
                // 対象データ取得用クエリ
                $season_student_requests_query = DB::table($table_data['table_name'])
                    ->whereIn('season_cd', $season_mng_cds);
                // 対象データを配列で取得
                $table_data['data_list'] = $season_student_requests_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の生徒連絡IDを取得
                $season_student_requests = $season_student_requests_query->select('season_student_id')
                    ->get();

                // 削除対象の生徒連絡IDを配列化
                $season_student_ids = [];
                foreach ($season_student_requests as $season_student_request) {
                    array_push($season_student_ids, $season_student_request->season_student_id);
                }

                // 削除対象の特別期間コードを基に、特別期間講習 講師連絡情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'season_tutor_requests';
                // 対象データ取得用クエリ
                $season_tutor_requests_query = DB::table($table_data['table_name'])
                    ->whereIn('season_cd', $season_mng_cds);
                // 対象データを配列で取得
                $table_data['data_list'] = $season_tutor_requests_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の講師連絡IDを取得
                $season_tutor_requests = $season_tutor_requests_query->select('season_tutor_id')
                    ->get();

                // 削除対象の講師連絡IDを配列化
                $season_tutor_ids = [];
                foreach ($season_tutor_requests as $season_tutor_request) {
                    array_push($season_tutor_ids, $season_tutor_request->season_tutor_id);
                }

                // 特別期間講習 生徒連絡コマ情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'season_student_periods';
                // 対象データ取得用クエリ
                $season_student_periods_query = DB::table($table_data['table_name'])
                    ->whereIn('season_student_id', $season_student_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $season_student_periods_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 特別期間講習 生徒実施回数情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'season_student_times';
                // 対象データ取得用クエリ
                $season_student_times_query = DB::table($table_data['table_name'])
                    ->whereIn('season_student_id', $season_student_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $season_student_times_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 特別期間講習 講師連絡コマ情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'season_tutor_periods';
                // 対象データ取得用クエリ
                $season_tutor_periods_query = DB::table($table_data['table_name'])
                    ->whereIn('season_tutor_id', $season_tutor_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $season_tutor_periods_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                //---------------------
                // データ削除
                //---------------------
                // 特別期間講習 生徒連絡コマ情報から削除
                $season_student_periods_count = $season_student_periods_query->delete();

                // 特別期間講習 生徒実施回数情報から削除
                $season_student_times_count = $season_student_times_query->delete();

                // 特別期間講習 講師連絡コマ情報から削除
                $season_tutor_periods_count = $season_tutor_periods_query->delete();

                // 特別期間講習 生徒連絡情報から削除
                $season_student_requests_count = $season_student_requests_query->delete();

                // 特別期間講習 講師連絡情報から削除
                $season_tutor_requests_count = $season_tutor_requests_query->delete();

                // 特別期間講習管理から削除
                $season_mng_count = $season_mng_query->delete();

                // getデータ破棄
                $season_mng = null;
                $season_student_requests = null;
                $season_tutor_requests = null;

                // 給与----------------------------------------------------
                // --追加請求情報
                // --給与算出管理情報
                // --給与算出情報
                // --給与算出交通費情報
                // --給与取込情報
                // --給与情報・給与明細情報
                // --7テーブル

                //---------------------
                // CSV出力用のデータ抽出
                //---------------------
                // --追加請求情報----------------------------------------------------
                // 追加請求情報から削除対象を抽出（条件：申請日）
                // テーブル名を指定
                $table_data['table_name'] = 'surcharges';
                // 対象データ取得用クエリ
                $surcharges_query = DB::table($table_data['table_name'])
                    ->where('apply_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $surcharges_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --給与算出管理情報----------------------------------------------------
                // 給与算出管理情報から削除対象を抽出（条件：給与年月）
                // テーブル名を指定
                $table_data['table_name'] = 'salary_mng';
                // 対象データ取得用クエリ
                $salary_mng_query = DB::table($table_data['table_name'])
                    ->where('salary_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $salary_mng_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --給与算出情報----------------------------------------------------
                // 給与算出情報から削除対象を抽出（条件：給与年月）
                // テーブル名を指定
                $table_data['table_name'] = 'salary_summarys';
                // 対象データ取得用クエリ
                $salary_summarys_query = DB::table($table_data['table_name'])
                    ->where('salary_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $salary_summarys_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --給与算出交通費情報----------------------------------------------------
                // 給与算出交通費情報から削除対象を抽出（条件：給与年月）
                // テーブル名を指定
                $table_data['table_name'] = 'salary_travel_costs';
                // 対象データ取得用クエリ
                $salary_travel_costs_query = DB::table($table_data['table_name'])
                    ->where('salary_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $salary_travel_costs_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --給与取込情報----------------------------------------------------
                // 給与取込情報から削除対象を抽出（条件：給与年月）
                // テーブル名を指定
                $table_data['table_name'] = 'salary_import';
                // 対象データ取得用クエリ
                $salary_import_query = DB::table($table_data['table_name'])
                    ->where('salary_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $salary_import_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --給与情報・給与明細情報----------------------------------------------------
                // 給与情報から削除対象を抽出（条件：給与年月）
                // テーブル名を指定
                $table_data['table_name'] = 'salaries';
                // 対象データ取得用クエリ
                $salaries_query = DB::table($table_data['table_name'])
                    ->where('salary_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $salaries_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の給与IDを取得
                $salaries = $salaries_query->select('salary_id')->get();

                // 削除対象の給与IDを配列化
                $salary_ids = [];
                foreach ($salaries as $salary) {
                    array_push($salary_ids, $salary->salary_id);
                }

                // 削除対象の給与IDを基に、給与明細情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'salary_details';
                // 対象データ取得用クエリ
                $salary_details_query = DB::table($table_data['table_name'])
                    ->whereIn('salary_id', $salary_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $salary_details_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                //---------------------
                // データ削除
                //---------------------
                // 追加請求情報から削除
                $surcharges_count = $surcharges_query->delete();

                // 給与算出管理情報から削除
                $salary_mng_count = $salary_mng_query->delete();

                // 給与算出情報から削除
                $salary_summarys_count = $salary_summarys_query->delete();

                // 給与算出交通費情報から削除
                $salary_travel_costs_count = $salary_travel_costs_query->delete();

                // 給与取込情報から削除
                $salary_import_count = $salary_import_query->delete();

                // 給与明細情報から削除
                $salary_details_count = $salary_details_query->delete();

                // 給与情報から削除
                $salaries_count = $salaries_query->delete();

                // getデータ破棄
                $salaries = null;

                // 請求----------------------------------------------------
                // --請求取込情報
                // --請求情報・請求明細情報
                // --3テーブル

                //---------------------
                // CSV出力用のデータ抽出
                //---------------------
                // --請求取込情報----------------------------------------------------
                // 請求取込情報から削除対象を抽出（条件：請求書年月）
                // テーブル名を指定
                $table_data['table_name'] = 'invoice_import';
                // 対象データ取得用クエリ
                $invoice_import_query = DB::table($table_data['table_name'])
                    ->where('invoice_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $invoice_import_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --請求情報・請求明細情報----------------------------------------------------
                // 請求情報から削除対象を抽出（条件：請求書年月）
                // テーブル名を指定
                $table_data['table_name'] = 'invoices';
                // 対象データ取得用クエリ
                $invoices_query = DB::table($table_data['table_name'])
                    ->where('invoice_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $invoices_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の請求IDを取得
                $invoices = $invoices_query->select('invoice_id')->get();

                // 削除対象の請求IDを配列化
                $invoice_ids = [];
                foreach ($invoices as $invoice) {
                    array_push($invoice_ids, $invoice->invoice_id);
                }

                // 削除対象の請求IDを基に、請求明細情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'invoice_details';
                // 対象データ取得用クエリ
                $invoice_details_query = DB::table($table_data['table_name'])
                    ->whereIn('invoice_id', $invoice_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $invoice_details_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                //---------------------
                // データ削除
                //---------------------
                // 請求取込情報から削除
                $invoice_import_count = $invoice_import_query->delete();

                // 請求明細情報から削除
                $invoice_details_count = $invoice_details_query->delete();

                // 請求情報から削除
                $invoices_count = $invoices_query->delete();

                // getデータ破棄
                $invoices = null;

                // その他----------------------------------------------------
                // --年間予定情報・年間予定取込情報
                // --管理者アカウント情報・アカウント情報（管理者）・パスワードリセット（管理者）
                // --面談連絡情報・面談日程情報
                // --生徒成績情報・生徒成績詳細情報
                // --問い合わせ情報
                // --お知らせ情報・お知らせ宛先情報
                // --研修資料・研修閲覧
                // --バッチ管理
                // --15テーブル

                //---------------------
                // CSV出力用のデータ抽出
                //---------------------
                // --年間予定情報・年間予定取込情報----------------------------------------------------
                // 年間予定情報から削除対象を抽出（条件：作成日時）
                // テーブル名を指定
                $table_data['table_name'] = 'yearly_schedules';
                // 対象データ取得用クエリ
                $yearly_schedules_query = DB::table($table_data['table_name'])
                    ->where('created_at', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $yearly_schedules_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 年間予定取込情報から削除対象を抽出（条件：取込日時）
                // テーブル名を指定
                $table_data['table_name'] = 'yearly_schedules_import';
                // 対象データ取得用クエリ
                $yearly_schedules_import_query = DB::table($table_data['table_name'])
                    ->where('import_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $yearly_schedules_import_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --管理者アカウント情報・アカウント情報（管理者）・パスワードリセット（管理者）----------------------------------------------------
                // 管理者アカウント情報から削除対象を抽出（条件：削除日時）
                // テーブル名を指定
                $table_data['table_name'] = 'admin_users';
                // 対象データ取得用クエリ
                $admin_users_query = DB::table($table_data['table_name'])
                    ->where('deleted_at', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $admin_users_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の管理者IDを取得
                $admin_users = $admin_users_query->select('adm_id')->get();

                // 削除対象の管理者IDを配列化
                $admin_user_ids = [];
                foreach ($admin_users as $admin_user) {
                    array_push($admin_user_ids, $admin_user->adm_id);
                }

                // 削除対象の管理者IDを基に、アカウント情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'accounts';
                // 対象データ取得用クエリ
                $admin_user_accounts_query = DB::table($table_data['table_name'])
                    ->where('account_type', '=', AppConst::CODE_MASTER_7_3)
                    ->whereIn('account_id', $admin_user_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $admin_user_accounts_query->get()->toArray();
                // CSV出力用配列に追加
                // ※アカウント情報は別配列にまとめて格納
                $array_accounts_data = array_merge($array_accounts_data, $table_data['data_list']);

                // 削除対象の管理者メールアドレスを取得
                $admin_user_accounts = $admin_user_accounts_query->select('email')->get();

                // 削除対象の管理者メールアドレスを配列化
                $admin_user_emails = [];
                foreach ($admin_user_accounts as $admin_user_account) {
                    array_push($admin_user_emails, $admin_user_account->email);
                }

                // --面談連絡情報・面談日程情報----------------------------------------------------
                // 面談連絡情報から削除対象を抽出（条件：連絡日）
                // テーブル名を指定
                $table_data['table_name'] = 'conferences';
                // 対象データ取得用クエリ
                $conferences_query = DB::table($table_data['table_name'])
                    ->where('apply_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $conferences_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の面談連絡IDを取得
                $conferences = $conferences_query->select('conference_id')->get();

                // 削除対象の面談連絡IDを配列化
                $conference_ids = [];
                foreach ($conferences as $conference) {
                    array_push($conference_ids, $conference->conference_id);
                }

                // 削除対象の面談連絡IDを基に、面談日程情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'conference_dates';
                // 対象データ取得用クエリ
                $conference_dates_query = DB::table($table_data['table_name'])
                    ->whereIn('conference_id', $conference_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $conference_dates_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --生徒成績情報・生徒成績詳細情報----------------------------------------------------
                // 生徒成績情報から削除対象を抽出（条件：更新日時）
                // テーブル名を指定
                $table_data['table_name'] = 'scores';
                // 対象データ取得用クエリ
                $scores_query = DB::table($table_data['table_name'])
                    ->where('updated_at', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $scores_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の生徒成績IDを取得
                $scores = $scores_query->select('score_id')->get();

                // 削除対象の生徒成績IDを配列化
                $score_ids = [];
                foreach ($scores as $score) {
                    array_push($score_ids, $score->score_id);
                }

                // 削除対象の生徒成績IDを基に、生徒成績詳細情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'score_details';
                // 対象データ取得用クエリ
                $score_details_query = DB::table($table_data['table_name'])
                    ->whereIn('score_id', $score_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $score_details_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --問い合わせ情報----------------------------------------------------
                // 問い合わせ情報から削除対象を抽出（条件：更新日時）
                // テーブル名を指定
                $table_data['table_name'] = 'contacts';
                // 対象データ取得用クエリ
                $contacts_query = DB::table($table_data['table_name'])
                    ->where('updated_at', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $contacts_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --お知らせ情報・お知らせ宛先情報----------------------------------------------------
                // お知らせ情報から削除対象を抽出（条件：登録日時）
                // テーブル名を指定
                $table_data['table_name'] = 'notices';
                // 対象データ取得用クエリ
                $notices_query = DB::table($table_data['table_name'])
                    ->where('regist_time', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $notices_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象のお知らせIDを取得
                $notices = $notices_query->select('notice_id')->get();

                // 削除対象のお知らせIDを配列化
                $notice_ids = [];
                foreach ($notices as $notice) {
                    array_push($notice_ids, $notice->notice_id);
                }

                // 削除対象のお知らせIDを基に、お知らせ宛先情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'notice_destinations';
                // 対象データ取得用クエリ
                $notice_destinations_query = DB::table($table_data['table_name'])
                    ->whereIn('notice_id', $notice_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $notice_destinations_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --研修資料・研修閲覧----------------------------------------------------
                // 研修資料から削除対象を抽出（条件：研修期限日）
                // テーブル名を指定
                $table_data['table_name'] = 'training_contents';
                // 対象データ取得用クエリ
                $training_contents_query = DB::table($table_data['table_name'])
                    ->where('limit_date', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $training_contents_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の研修IDを取得
                $training_contents = $training_contents_query->select('trn_id')->get();

                // 削除対象の研修IDを配列化
                $trn_ids = [];
                foreach ($training_contents as $training_content) {
                    array_push($trn_ids, $training_content->trn_id);
                }

                // 削除対象の研修IDを基に、研修閲覧から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'training_browses';
                // 対象データ取得用クエリ
                $training_browses_query = DB::table($table_data['table_name'])
                    ->whereIn('trn_id', $trn_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $training_browses_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // --バッチ実行管理----------------------------------------------------
                // バッチ実行管理から削除対象を抽出（条件：更新日時）
                // テーブル名を指定
                $table_data['table_name'] = 'batch_mng';
                // 対象データ取得用クエリ
                $batch_mng_query = DB::table($table_data['table_name'])
                    ->where('updated_at', '<', $five_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $batch_mng_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                //---------------------
                // データ削除
                //---------------------
                // 年間予定情報から削除
                $yearly_schedules_count = $yearly_schedules_query->delete();

                // 年間予定取込情報から削除
                $yearly_schedules_import_count = $yearly_schedules_import_query->delete();

                // パスワードリセットから削除（CSV不要）
                $admin_user_password_resets_count = PasswordResets::whereIn('email', $admin_user_emails)
                    // 現在同じメールアドレスが使われている可能性があるため、削除対象を絞る
                    ->where('created_at', '<', $five_years_ago_fiscal_start_date)
                    ->forceDelete();

                // アカウント情報から削除
                $admin_user_accounts_count = $admin_user_accounts_query->delete();

                // 管理者アカウント情報から削除
                $admin_users_count = $admin_users_query->delete();

                // 面談日程情報から削除
                $conference_dates_count = $conference_dates_query->delete();

                // 面談連絡情報から削除
                $conferences_count = $conferences_query->delete();

                // 生徒成績詳細情報から削除
                $score_details_count = $score_details_query->delete();

                // 生徒成績情報から削除
                $scores_count = $scores_query->delete();

                // 問い合わせ情報削除（条件：更新日時）
                $contacts_count = $contacts_query->delete();

                // お知らせ宛先情報から削除
                $notice_destinations_count = $notice_destinations_query->delete();

                // お知らせ情報から削除
                $notices_count = $notices_query->delete();

                // 研修閲覧から削除
                $training_browses_count = $training_browses_query->delete();

                // 研修資料から削除
                $training_contents_count = $training_contents_query->delete();

                // バッチ実行管理から削除
                $batch_mng_count = $batch_mng_query->delete();

                // getデータ破棄
                $admin_users = null;
                $admin_user_accounts = null;
                $conferences = null;
                $scores = null;
                $notices = null;
                $training_contents = null;

                //--------------------------
                // 4年経過後に削除
                //--------------------------
                // 講師----------------------------------------------------
                // --講師情報・講師所属情報・講師担当科目情報・講師空き時間情報・アカウント情報（講師）・パスワードリセット（講師）
                // --6テーブル

                //---------------------
                // CSV出力用のデータ抽出
                //---------------------
                // 講師情報から削除対象を抽出（条件：退職日）
                // テーブル名を指定
                $table_data['table_name'] = 'tutors';
                // 対象データ取得用クエリ
                $tutors_query = DB::table($table_data['table_name'])
                    ->where('leave_date', '<', $four_years_ago_fiscal_start_date);
                // 対象データを配列で取得
                $table_data['data_list'] = $tutors_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の講師IDを取得
                $tutors = $tutors_query->select('tutor_id')->get();

                // 削除対象の講師IDを配列化
                $tutor_ids = [];
                foreach ($tutors as $tutor) {
                    array_push($tutor_ids, $tutor->tutor_id);
                }

                // 削除対象の講師IDを基に、アカウント情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'accounts';
                // 対象データ取得用クエリ
                $tutor_accounts_query = DB::table($table_data['table_name'])
                    ->where('account_type', '=', AppConst::CODE_MASTER_7_2)
                    ->whereIn('account_id', $tutor_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $tutor_accounts_query->get()->toArray();
                // CSV出力用配列に追加
                // ※アカウント情報は別配列にまとめて格納
                $array_accounts_data = array_merge($array_accounts_data, $table_data['data_list']);

                // 削除対象の講師IDを基に、アカウント情報からメールアドレスを取得
                $tutor_accounts = $tutor_accounts_query->select('email')->get();

                // 削除対象の講師メールアドレスを配列化
                $tutor_emails = [];
                foreach ($tutor_accounts as $tutor_account) {
                    array_push($tutor_emails, $tutor_account->email);
                }

                // 削除対象の講師IDを基に、講師所属情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'tutor_campuses';
                // 対象データ取得用クエリ
                $tutor_campuses_query = DB::table($table_data['table_name'])
                    ->whereIn('tutor_id', $tutor_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $tutor_campuses_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の講師IDを基に、講師担当科目情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'tutor_subjects';
                // 対象データ取得用クエリ
                $tutor_subjects_query = DB::table($table_data['table_name'])
                    ->whereIn('tutor_id', $tutor_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $tutor_subjects_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                // 削除対象の講師IDを基に、講師空き時間情報から削除対象を抽出
                // テーブル名を指定
                $table_data['table_name'] = 'tutor_free_periods';
                // 対象データ取得用クエリ
                $tutor_free_periods_query = DB::table($table_data['table_name'])
                    ->whereIn('tutor_id', $tutor_ids);
                // 対象データを配列で取得
                $table_data['data_list'] = $tutor_free_periods_query->get()->toArray();
                // CSV出力用配列に追加
                array_push($array_table_data, $table_data);

                //---------------------
                // データ削除
                //---------------------
                // 講師所属情報から削除
                $tutor_campuses_count = $tutor_campuses_query->delete();

                // 講師担当科目情報から削除
                $tutor_subjects_count = $tutor_subjects_query->delete();

                // 講師空き時間情報から削除
                $tutor_free_periods_count = $tutor_free_periods_query->delete();

                // パスワードリセットから削除（CSV不要）
                $tutor_password_resets_count = PasswordResets::whereIn('email', $tutor_emails)
                    // 現在同じメールアドレスが使われている可能性があるため、削除対象を絞る
                    ->where('created_at', '<', $four_years_ago_fiscal_start_date)
                    ->forceDelete();

                // アカウント情報から削除
                $tutor_accounts_count = $tutor_accounts_query->delete();

                // 講師情報から削除
                $tutors_count = $tutors_query->delete();

                // getデータ破棄
                $tutors = null;
                $tutor_accounts = null;

                //---------------------
                // CSV出力
                //---------------------
                // 保存用のディレクトリ作成
                // バッチ処理開始日時を14桁の数値に変換 $dir_name例：20230301000000
                $dir_name = preg_replace('/[^0-9]/', '', $now);

                // $dir_path例：exceeding_data_backup/20230301000000
                $dir_path = config('appconf.exceeding_data_backup_dir') . $dir_name;
                Storage::makeDirectory($dir_path);
                $dir_path = Storage::path($dir_path);

                // テーブルごとにCSV出力
                foreach ($array_table_data as $data) {
                    $this->saveCsv($data['table_name'], $data['data_list'], $dir_path);
                }

                // アカウント情報のCSV出力（生徒・講師・運用管理まとめて）
                $this->saveCsv('accounts', $array_accounts_data, $dir_path);

                //---------------------
                // zip保存
                //---------------------
                // $zip_name例：保持期限超過データ削除バックアップ_20230301000000.zip
                $zip_name = config('appconf.exceeding_data_backup_zip_filename') . $dir_name . '.zip';

                $zip = new \ZipArchive();
                $zip->open($dir_path . '/' . $zip_name, \ZipArchive::CREATE);

                // ディレクトリ内のCSVファイルパスを全て取得し、1ファイルずつzip保存
                $files = glob($dir_path . '/*');
                foreach ($files as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();

                //---------------------
                // ログ出力
                //---------------------
                // 削除したパスワードリセットの合計算出
                $password_resets_count = $student_password_resets_count + $admin_user_password_resets_count + $tutor_password_resets_count;

                // 削除したアカウント情報の合計算出
                $accounts_count = $student_accounts_count + $admin_user_accounts_count + $tutor_accounts_count;

                // 削除したデータ数のログ出力
                Log::info(
                    "Delete {$student_campuses_count} Records From student_campuses.
                    Delete {$student_entrance_exams_count} Records From student_entrance_exams.
                    Delete {$student_enter_histories_count} Records From student_enter_histories.
                    Delete {$records_count} Records From records.
                    Delete {$badges_count} Records From badges.
                    Delete {$students_count} Records From students.
                    Delete {$class_members_count} Records From class_members.
                    Delete {$schedules_count} Records From schedules.
                    Delete {$absent_applications_count} Records From absent_applications.
                    Delete {$transfer_application_dates_count} Records From transfer_application_dates.
                    Delete {$transfer_applications_count} Records From transfer_applications.
                    Delete {$extra_class_applications_count} Records From extra_class_applications.
                    Delete {$report_units_count} Records From report_units.
                    Delete {$reports_count} Records From reports.
                    Delete {$season_student_periods_count} Records From season_student_periods.
                    Delete {$season_student_times_count} Records From season_student_times.
                    Delete {$season_tutor_periods_count} Records From season_tutor_periods.
                    Delete {$season_student_requests_count} Records From season_student_requests.
                    Delete {$season_tutor_requests_count} Records From season_tutor_requests.
                    Delete {$season_mng_count} Records From season_mng.
                    Delete {$surcharges_count} Records From surcharges.
                    Delete {$salary_mng_count} Records From salary_mng.
                    Delete {$salary_summarys_count} Records From salary_summarys.
                    Delete {$salary_travel_costs_count} Records From salary_travel_costs.
                    Delete {$salary_import_count} Records From salary_import.
                    Delete {$salary_details_count} Records From salary_details.
                    Delete {$salaries_count} Records From salaries.
                    Delete {$invoice_import_count} Records From invoice_import.
                    Delete {$invoice_details_count} Records From invoice_details.
                    Delete {$invoices_count} Records From invoices.
                    Delete {$yearly_schedules_count} Records From yearly_schedules.
                    Delete {$yearly_schedules_import_count} Records From yearly_schedules_import.
                    Delete {$admin_users_count} Records From admin_users.
                    Delete {$conference_dates_count} Records From conference_dates.
                    Delete {$conferences_count} Records From conferences.
                    Delete {$score_details_count} Records From score_details.
                    Delete {$scores_count} Records From scores.
                    Delete {$contacts_count} Records From contacts.
                    Delete {$notice_destinations_count} Records From notice_destinations.
                    Delete {$notices_count} Records From notices.
                    Delete {$training_browses_count} Records From training_browses.
                    Delete {$training_contents_count} Records From training_contents.
                    Delete {$batch_mng_count} Records From batch_mng.
                    Delete {$tutor_campuses_count} Records From tutor_campuses.
                    Delete {$tutor_subjects_count} Records From tutor_subjects.
                    Delete {$tutor_free_periods_count} Records From tutor_free_periods.
                    Delete {$tutors_count} Records From tutors.
                    Delete {$password_resets_count} Records From password_resets.
                    Delete {$accounts_count} Records From accounts."
                );
            });

            //--------------------------------
            // 期限の過ぎた取込ファイルを削除する（保持期限超過データバックアップも含む）
            //--------------------------------
            // 削除するのは5年経過（$five_years_ago_fiscal_start_dir_name）のディレクトリ

            // 研修資料ファイル削除
            $training_dir = config('appconf.upload_dir_training');
            $training_dir_count = $this->deleteExceedingUploadDir($training_dir, $five_years_ago_fiscal_start_dir_name);

            // 給与情報取込ファイル削除
            $salary_import_dir = config('appconf.upload_dir_salary_import');
            $salary_import_dir_count = $this->deleteExceedingUploadDir($salary_import_dir, $five_years_ago_fiscal_start_dir_name);

            // 請求情報取込ファイル削除
            $invoice_import_dir = config('appconf.upload_dir_invoice_import');
            $invoice_import_dir_count = $this->deleteExceedingUploadDir($invoice_import_dir, $five_years_ago_fiscal_start_dir_name);

            // 年度スケジュール情報取込ファイル削除
            $year_schedule_import_dir = config('appconf.upload_dir_year_schedule_import');
            $year_schedule_import_dir_count = $this->deleteExceedingUploadDir($year_schedule_import_dir, $five_years_ago_fiscal_start_dir_name);

            // 学校コード取込ファイル削除
            $school_code_import_dir = config('appconf.upload_dir_school_code_import');
            $school_code_import_dir_count = $this->deleteExceedingUploadDir($school_code_import_dir, $five_years_ago_fiscal_start_dir_name);

            // 保持期限超過データバックアップファイル削除
            $exceeding_data_backup_dir = config('appconf.exceeding_data_backup_dir');
            $exceeding_data_backup_dir_count = $this->deleteExceedingUploadDir($exceeding_data_backup_dir, $five_years_ago_fiscal_start_dir_name);

            Log::info(
                "Delete {$training_dir_count} Folders From {$training_dir}.
                Delete {$salary_import_dir_count} Folders From {$salary_import_dir}.
                Delete {$invoice_import_dir_count} Folders From {$invoice_import_dir}.
                Delete {$year_schedule_import_dir_count} Folders From {$year_schedule_import_dir}.
                Delete {$school_code_import_dir_count} Folders From {$school_code_import_dir}.
                Delete {$exceeding_data_backup_dir_count} Folders From {$exceeding_data_backup_dir}."
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
     * バックアップCSVの保存を行う
     * @param mixed $table_name 出力するテーブル名
     * @param mixed $data_list 出力するデータ
     * @param mixed $dir_path 保存先のディレクトリパス
     */
    private function saveCsv($table_name, $data_list, $dir_path)
    {
        // ヘッダを取得
        $header[] = Schema::getColumnListing($table_name);

        // $data_listはstdClass Objectで入ってくるため、Array形式に変換する
        $decode_data_list = json_decode(json_encode($data_list), true);

        // ヘッダ配列とデータ配列を結合
        $arrayCsv = array_merge($header, $decode_data_list);

        // ファイル名の取得
        $filename = Lang::get(
            'message.file.' . $table_name . '_output.name',
            [
                'tableName' => $table_name,
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

        return;
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
