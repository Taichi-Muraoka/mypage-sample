<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use App\Models\BatchMng;
use App\Models\Invoice;
use App\Models\InvoiceImport;
use App\Models\Student;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceIssue;
use Carbon\Carbon;
use App\Consts\AppConst;
use App\Http\Controllers\Traits\CtrlModelTrait;

/**
 * 授業リマインドメール配信 - バッチ処理
 */
class InvoiceIssueMail extends Command
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
    protected $signature = 'command:invoiceIssueMail {invoice_ym} {account_id?}';

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

            // 請求年月（Ym）と実行者のアカウントIDを受け取る
            $invoiceYm = $this->argument("invoice_ym");
            $account_id = $this->argument("account_id");

            // dateの形式のバリデーションと変換
            if (!preg_match('/^\d{6}$/', $invoiceYm)) {
                Log::error("Batch invoiceIssueMail param err, invoice_ym: {$invoiceYm}");
                return 1;
            } else {
                $year = (int)substr($invoiceYm, 0, 4);
                $month = (int)substr($invoiceYm, -2);
                // 有効な月かどうかをチェック
                if ($month >= 1 && $month <= 12) {
                    // Ym->Y-m-d に変換
                    $invoiceDate = substr($invoiceYm, 0, 4) . '-' . substr($invoiceYm, -2) . '-01';
                    // Ym->Y年m月 に変換
                    $invoiceYmStr = $year . '年' . $month . '月';
                } else {
                    // エラー
                    Log::error("Batch invoiceIssueMail param err, invoice_ym: {$invoiceYm}");
                    return 1;
                }
            }

            //-------------------------
            // メール配信可否判定
            //-------------------------
            // 請求取込情報のチェック
            // 対象年月の請求情報が取込済かつメール未送信かどうか
            // システムから実行される場合は、呼び出し元でもチェックする
            $importChk = InvoiceImport::where('invoice_date', $invoiceDate)
                ->where('import_state', AppConst::CODE_MASTER_20_1)
                ->where('mail_state', AppConst::CODE_MASTER_56_0)
                ->exists();

            if (!$importChk) {
                // 請求取込情報のチェックNGである場合
                // 以下の処理をスキップする
                Log::error("Batch invoiceIssueMail Skipped. (InvoiceImport Status Error)");
                return 1;
            }

            // メール送信バッチが実行中かチェック
            // システムから実行される場合は、呼び出し元でもチェックする
            // バッチ管理テーブルから実行中のメール送信処理を取得
            $batchMng = BatchMng::select('batch_state')
                ->whereIn('batch_type', [AppConst::BATCH_TYPE_4, AppConst::BATCH_TYPE_6])
                ->orderBy('start_time', 'DESC')
                ->first();

            if ($batchMng->batch_state == AppConst::CODE_MASTER_22_99) {
                // メール送信バッチが実行中である場合
                // 以下の処理をスキップする
                Log::error("Batch invoiceIssueMail Skipped. (SendMail batch in progress)");
                return 1;
            }

            Log::info("Batch invoiceIssueMail Start, invoice_ym: {$invoiceYm}, account_id: {$account_id}");

            $now = Carbon::now();

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_6;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = $account_id;
            $batchMng->save();

            $batch_id = $batchMng->batch_id;

            // 請求取込情報のメール送信ステータスを処理中に更新
            $invoiceImp = InvoiceImport::where('invoice_date', $invoiceDate)
                ->firstOrFail();
            // 更新
            $invoiceImp->mail_state = AppConst::CODE_MASTER_56_2;
            $invoiceImp->save();

            $res = DB::select("show variables like 'wait_timeout';");
            Log::info($res);
            DB::statement('SET wait_timeout=1200');
            $res = DB::select("show variables like 'wait_timeout';");
            Log::info($res);

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($batch_id, $invoiceDate, $invoiceYmStr) {
                //-------------------------
                // 対象請求情報の生徒ID抽出
                //-------------------------
                // 請求情報テーブルから対象月の生徒IDを取得する
                $students = Invoice::select(
                    'invoices.student_id',
                    'students.name',
                )
                    // 生徒名の取得
                    ->sdJoin(Student::class, function ($join) {
                        $join->on('invoices.student_id', 'students.student_id');
                    })
                    ->where('invoice_date', '=', $invoiceDate)
                    ->distinct()
                    ->get();

                $sendCount = 0;
                // 生徒毎の処理
                foreach ($students as $student) {
                    //-------------------------
                    // メール送信(生徒宛)
                    //-------------------------
                    // 送信先メールアドレス取得
                    $studentEmail = $this->mdlGetParentMail($student->student_id);
                    // メールアドレス取得できる場合のみ、メール送信を行う
                    if ($studentEmail) {

                        // メール送信数チェック
                        if ($sendCount + 1 > self::MAIL_COUNT_MAX) {
                            // メール送信数がサーバーの15分毎の送信数上限を超える場合
                            // 15分sleep
                            Log::info("Send {$sendCount} mail. Sendmail wait...");
                            Sleep::for(15)->minutes();
                            $sendCount = 0;
                        }

                        // メール本文に記載する情報をセット
                        $name =  $student->name;
                        $mail_body = [
                            'name' => $name,
                            'invoice_ym' => $invoiceYmStr,
                        ];
                        // メール送信
                        Mail::to($studentEmail)->send(new InvoiceIssue($mail_body));
                        Log::channel('dailyMail')->info("InvoiceIssueMail student_id: " . $student->student_id . ", to: [" . $studentEmail . "]");
                        $sendCount++;
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

                // 請求取込情報のメール送信ステータスを更新：送信済
                $invoiceImp = InvoiceImport::where('invoice_date', $invoiceDate)
                    ->firstOrFail();
                $invoiceImp->mail_state = AppConst::CODE_MASTER_56_1;
                $invoiceImp->save();

                Log::info("Send " . $sendCount . " mail.");
                Log::info("invoiceIssueMail Succeeded.");

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

            // 請求取込情報のメール送信ステータスを更新：送信エラー
            $invoiceImp = InvoiceImport::where('invoice_date', $invoiceDate)
                ->firstOrFail();
            $invoiceImp->mail_state = AppConst::CODE_MASTER_56_3;
            $invoiceImp->save();

            // この時点では補足できないエラーとして、詳細は返さずエラーとする
            Log::error($e);
        }
        return 0;
    }
}
