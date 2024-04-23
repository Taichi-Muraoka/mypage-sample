<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use App\Models\Account;
use App\Models\BatchMng;
use Carbon\Carbon;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Traits\CtrlCsvTrait;
use App\Http\Controllers\Traits\CtrlFileTrait;
use App\Http\Controllers\Traits\CtrlModelTrait;
use App\Exceptions\ReadDataValidateException;
use App\Models\AdminUser;
use App\Models\CodeMaster;
use App\Models\MstCampus;

/**
 * 管理者情報取込処理（データ移行用） - バッチ処理
 */
class AdminDataImport extends Command
{
    // CSV共通処理
    use CtrlCsvTrait;
    use CtrlFileTrait;
    // モデル共通処理
    use CtrlModelTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:adminDataImport {path}';

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

            // CSVファイルのパスを受け取る
            $path = $this->argument("path");
            $datas = [];

            Log::info("Batch adminDataImport Start, PATH: {$path}");

            $now = Carbon::now();

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_21;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = null;
            $batchMng->save();

            $batch_id = $batchMng->batch_id;

            try {
                // 入力ファイル名のチェック
                // .csvファイルではない場合にエラーとする
                if (strrchr($path, '.') != '.csv') {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(ファイル名不正)");
                }
                // ファイルが存在しない場合にエラーとする
                if (!file_exists($path)) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(ファイルパス・ファイル名不正)");
                }
                // CSVデータの読み込み
                $datas = $this->readData($path);

                if (empty($datas)) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(データ件数不正)");
                }
            } catch (ReadDataValidateException  $e) {
                // 通常は事前にバリデーションするのでここはありえないのでエラーとする
                throw $e;
            }

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($batch_id, $datas) {

                // インポート管理者数カウント用
                $aidCount = 0;

                // 1行ずつ取り込んだデータごとに処理
                foreach ($datas as $data) {

                    // MEMO:管理者情報の既存データ削除は行なわない

                    // 管理者情報の作成
                    $admin = new AdminUser();
                    $admin->adm_id = $data['adm_id'];
                    $admin->fill($data)->save();

                    // アカウント情報の作成
                    $account = new Account;
                    $account->account_id = $data['adm_id'];
                    $account->account_type = AppConst::CODE_MASTER_7_3;
                    $account->email = $data['email'];
                    $account->password = Hash::make($data['email']);
                    $account->password_reset = AppConst::ACCOUNT_PWRESET_0;
                    $account->plan_type = AppConst::CODE_MASTER_10_0;
                    $account->login_flg = AppConst::CODE_MASTER_9_0;
                    $account->save();

                    $aidCount++;
                }

                // バッチ管理テーブルのレコードを更新：正常終了
                $end = Carbon::now();
                BatchMng::where('batch_id', '=', $batch_id)
                    ->update([
                        'end_time' => $end,
                        'batch_state' => AppConst::CODE_MASTER_22_0,
                        'updated_at' => $end
                    ]);

                Log::info("Insert {$aidCount} admin_users. adminDataImport Succeeded.");
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
        // 念のため明示的に捨てる
        $datas = null;

        return 0;
    }

    /**
     * アップロードされたファイルを読み込む
     * バリデーションも行う
     *
     * @param $path
     * @return array データ
     */
    private function readData($path)
    {
        $csvHeaders = [
            'adm_id',
            'name',
            'email',
            'campus_cd'
        ];

        // CSVのデータをリストで保持
        $datas = [];
        $headers = [];

        // CSV読み込み
        $file = $this->readCsv($path, "sjis");

        // 1行ずつ取得
        foreach ($file as $i => $line) {
            if ($i === 0) {
                //-------------
                // ヘッダ行
                //-------------
                $headers = $line;

                // [バリデーション] ヘッダが想定通りかチェック
                if ($headers !== $csvHeaders) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(ヘッダ行不正)");
                }
                continue;
            }
            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(データ列数不正)");
            }

            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make($values, $this->rulesForInput($values));
            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('adm_id')) {
                    $errCol = "adm_id=" . $values['adm_id'];
                } else if ($validator->errors()->has('name')) {
                    $errCol = "name=" . $values['name'];
                } else if ($validator->errors()->has('email')) {
                    $errCol = "email=" . $values['email'];
                } else if ($validator->errors()->has('campus_cd')) {
                    $errCol = "campus_cd=" . $values['campus_cd'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "データ項目不正( " . $i + 1 . "行目 adm_id=" . $values['adm_id'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // 管理者IDの重複チェック
            // 重複チェック用配列$dupAidCheckを用意し、同じ管理者IDが存在するか判定する
            $aid = $values['adm_id'];
            if (isset($dupAidCheck[$aid])) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "管理者ID重複( 管理者ID=" . $values['adm_id'] . " )");
            } else {
                // 存在しなければ適当な値をセットし配列に追加する
                $dupAidCheck[$aid] = 1;
            }

            // ログイン用管理者メールアドレスの重複チェック
            $email = $values['email'];
            if (isset($dupEmailCheck[$email])) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "ログイン用管理者メールアドレス重複( 管理者メールアドレス=" . $values['email'] . " )");
            } else {
                $dupEmailCheck[$email] = 1;
            }

            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // リストに保持
            $datas[] = $values;
        }

        return $datas;
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput(array $values)
    {
        $rules = array();

        // MEMO:バッチ処理ではログイン情報がないため、mdlGetRoomList()は使わない
        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) use ($values) {

            // コードマスタより「本部(00)」の校舎コードを取得
            $queryHonbu = CodeMaster::select('gen_item1')
                ->where('data_type', AppConst::CODE_MASTER_6);

            $exists = MstCampus::where('campus_cd', $values['campus_cd'])
                // 非表示フラグの条件を付加
                ->where('is_hidden', AppConst::CODE_MASTER_11_1)
                // UNIONで本部を加える
                ->union($queryHonbu)
                ->exists();

            // 存在しなければエラー
            if (!$exists) {
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: メールアドレス重複チェック
        $validationEmail = function ($attribute, $value, $fail) use ($values) {

            // 対象データを取得
            $exists = Account::where('email', $values['email'])
                ->where(function ($query) use ($values) {
                    // 管理者のチェック中ID以外を検索
                    $query->where('account_type', AppConst::CODE_MASTER_7_3)
                        ->where('account_id', '!=', $values['adm_id'])
                        // または、生徒・講師で検索
                        ->orWhere('account_type', '!=', AppConst::CODE_MASTER_7_3);
                })
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_email'));
            }
        };

        // emailルールはAccountより適用
        $rules += Account::fieldRules('email', ['required', $validationEmail]);
        $rules += AdminUser::fieldRules('adm_id', ['required']);
        $rules += AdminUser::fieldRules('name', ['required']);
        $rules += AdminUser::fieldRules('campus_cd', ['required', $validationRoomList]);

        return $rules;
    }
}
