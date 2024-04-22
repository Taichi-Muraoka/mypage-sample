<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use App\Models\Account;
use App\Models\Tutor;
use App\Models\BatchMng;
use Carbon\Carbon;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Traits\CtrlCsvTrait;
use App\Http\Controllers\Traits\CtrlFileTrait;
use App\Http\Controllers\Traits\CtrlModelTrait;
use App\Exceptions\ReadDataValidateException;
use App\Models\MstSystem;

/**
 * 講師情報取込処理（データ移行用） - バッチ処理
 */
class TutorDataImport extends Command
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
    protected $signature = 'command:tutorDataImport {path}';

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

            Log::info("Batch tutorDataImport Start, PATH: {$path}");

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

                // --------------
                // 既存データ削除
                // --------------
                // 取込対象の講師IDを取得
                $tidList = [];
                foreach ($datas as $data) {
                    array_push($tidList,  $data['tutor_id']);
                }
                $uniqueTidList = array_unique($tidList);

                Tutor::whereIn('tutor_id', $uniqueTidList)
                    ->forceDelete();

                Account::whereIn('account_id', $uniqueTidList)
                    ->where('account_type', AppConst::CODE_MASTER_7_2)
                    ->forceDelete();

                // --------------
                // 新規データ作成
                // --------------
                // インポート講師数カウント用
                $tidCount = 0;

                // 1行ずつ取り込んだデータごとに処理
                foreach ($datas as $data) {

                    // 講師情報の作成
                    $tutor = new Tutor;
                    $tutor->tutor_id = $data['tutor_id'];
                    $tutor->fill($data)->save();

                    // アカウント情報の作成
                    $account = new Account;
                    $account->account_id = $data['tutor_id'];
                    $account->account_type = AppConst::CODE_MASTER_7_2;
                    $account->email = $data['email'];
                    $account->password = Hash::make($data['email']);
                    $account->password_reset = AppConst::ACCOUNT_PWRESET_0;
                    $account->plan_type = AppConst::CODE_MASTER_10_0;
                    // 退職済はログイン不可
                    if ($data['tutor_status'] == AppConst::CODE_MASTER_29_3) {
                        $account->login_flg = AppConst::CODE_MASTER_9_1;
                    } else {
                        $account->login_flg = AppConst::CODE_MASTER_9_0;
                    }
                    $account->save();

                    $tidCount++;
                }

                // バッチ管理テーブルのレコードを更新：正常終了
                $end = Carbon::now();
                BatchMng::where('batch_id', '=', $batch_id)
                    ->update([
                        'end_time' => $end,
                        'batch_state' => AppConst::CODE_MASTER_22_0,
                        'updated_at' => $end
                    ]);

                Log::info("Insert {$tidCount} tutors. tutorDataImport Succeeded.");
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
            'tutor_id',
            'name',
            'name_kana',
            'tel',
            'email',
            'address',
            'birth_date',
            'gender_cd',
            'grade_cd',
            'grade_year',
            'school_cd_j',
            'school_cd_h',
            'school_cd_u',
            'hourly_base_wage',
            'tutor_status',
            'enter_date',
            'leave_date',
            'memo'
        ];

        // CSVのデータをリストで保持
        $datas = [];
        $headers = [];

        // 「学年設定年度」用に現年度を取得
        $getCurrentYear = MstSystem::select('value_num')
            ->where('key_id', AppConst::SYSTEM_KEY_ID_1)
            ->whereNotNull('value_num')
            ->firstOrFail();

        $currentYear = $getCurrentYear->value_num;

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
                if ($validator->errors()->has('tutor_id')) {
                    $errCol = "tutor_id=" . $values['tutor_id'];
                } else if ($validator->errors()->has('name')) {
                    $errCol = "name=" . $values['name'];
                } else if ($validator->errors()->has('name_kana')) {
                    $errCol = "name_kana=" . $values['name_kana'];
                } else if ($validator->errors()->has('tel')) {
                    $errCol = "tel=" . $values['tel'];
                } else if ($validator->errors()->has('email')) {
                    $errCol = "email=" . $values['email'];
                } else if ($validator->errors()->has('address')) {
                    $errCol = "address=" . $values['address'];
                } else if ($validator->errors()->has('birth_date')) {
                    $errCol = "birth_date=" . $values['birth_date'];
                } else if ($validator->errors()->has('gender_cd')) {
                    $errCol = "gender_cd=" . $values['gender_cd'];
                } else if ($validator->errors()->has('grade_cd')) {
                    $errCol = "grade_cd=" . $values['grade_cd'];
                } else if ($validator->errors()->has('grade_year')) {
                    $errCol = "grade_year=" . $values['grade_year'];
                } else if ($validator->errors()->has('school_cd_j')) {
                    $errCol = "school_cd_j=" . $values['school_cd_j'];
                } else if ($validator->errors()->has('school_cd_h')) {
                    $errCol = "school_cd_h=" . $values['school_cd_h'];
                } else if ($validator->errors()->has('school_cd_u')) {
                    $errCol = "school_cd_u=" . $values['school_cd_u'];
                } else if ($validator->errors()->has('hourly_base_wage')) {
                    $errCol = "hourly_base_wage=" . $values['hourly_base_wage'];
                } else if ($validator->errors()->has('tutor_status')) {
                    $errCol = "tutor_status=" . $values['tutor_status'];
                } else if ($validator->errors()->has('enter_date')) {
                    $errCol = "enter_date=" . $values['enter_date'];
                } else if ($validator->errors()->has('leave_date')) {
                    $errCol = "leave_date=" . $values['leave_date'];
                } else if ($validator->errors()->has('memo')) {
                    $errCol = "memo=" . $values['memo'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "データ項目不正( " . $i + 1 . "行目 tutor_id=" . $values['tutor_id'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // 講師IDの重複チェック
            // 重複チェック用配列$dupTidCheckを用意し、同じ講師IDが存在するか判定する
            $tid = $values['tutor_id'];
            if (isset($dupTidCheck[$tid])) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "講師ID重複( 講師ID=" . $values['tutor_id'] . " )");
            } else {
                // 存在しなければ適当な値をセットし配列に追加する
                $dupTidCheck[$tid] = 1;
            }

            // ログイン用講師メールアドレスの重複チェック
            $email = $values['email'];
            if (isset($dupEmailCheck[$email])) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "ログイン用講師メールアドレス重複( 講師メールアドレス=" . $values['email'] . " )");
            } else {
                $dupEmailCheck[$email] = 1;
            }

            //-- ステータスによって不要部分をnullに変換 --//
            // 在籍
            if ($values['tutor_status'] == AppConst::CODE_MASTER_29_1) {
                $values['leave_date'] = null;
            }

            //-- NOT NULLエラー回避 --//
            // 学年設定年度が空白の場合は今年度を設定
            if ($values['grade_year'] === '') {
                $values['grade_year'] = $currentYear;
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

        // 独自バリデーション: リストのチェック 性別
        $validationGenderList =  function ($attribute, $value, $fail) {
            // 性別リストを取得
            $genderList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_30);
            if (!isset($genderList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 学年
        $validationGradeList =  function ($attribute, $value, $fail) {
            // 学年リストを取得
            $grades = $this->mdlGetTutorGradeList(false);
            if (!isset($grades[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 学校コード
        $validationSchoolList =  function ($attribute, $value, $fail) {
            // 学校コードリストを取得
            $schoolList = $this->mdlGetSchoolList();
            if (!isset($schoolList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 講師ステータス
        $validationStatusList =  function ($attribute, $value, $fail) {
            // ステータスリストを取得
            $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_29);
            if (!isset($statusList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: メールアドレス重複チェック
        $validationEmail = function ($attribute, $value, $fail) use ($values) {

            // 対象データを取得
            $exists = Account::where('email', $values['email'])
                // チェック中の講師IDを除外（バッチやり直しに対応）
                ->where('account_id', '!=', $values['tutor_id'])
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_email'));
            }
        };

        // 独自バリデーション: 「退職処理中」の場合、退職日はシステム日付より未来日
        $validationLeaveDateProspect = function ($attribute, $value, $fail) use ($values) {
            // 退職日の数値が現在日時の数値を下回っていないかチェック
            if (strtotime($values['leave_date']) < strtotime('now')) {
                // 下回っていた（未来日でない）場合エラー
                return $fail(Lang::get('validation.after_tomorrow'));
            }
        };

        // 独自バリデーション: 「退職済」の場合、退職日はシステム日付以前（当日含む）
        $validationLeaveDateExecution = function ($attribute, $value, $fail) use ($values) {
            // 現在日時の数値が退職日の数値を下回っていないかチェック
            if (strtotime('now') <= strtotime($values['leave_date'])) {
                // 下回っていた（システム日付以前でない）場合エラー
                return $fail(Lang::get('validation.before_or_equal_today',));
            }
        };

        // 独自バリデーション: 退職日は勤務開始日以降（当日含む）
        $validationLeaveDateAfterEnterDate = function ($attribute, $value, $fail) use ($values) {
            // 退職日の数値が勤務開始日の数値を下回っていないかチェック
            if (strtotime($values['leave_date']) < strtotime($values['enter_date'])) {
                // 下回っていた（勤務開始日以降でない）場合エラー
                return $fail(Lang::get('validation.tutor_leave_after_or_equal_enter_date'));
            }
        };

        $rules += Tutor::fieldRules('tutor_id', ['required']);
        $rules += Tutor::fieldRules('name', ['required']);
        $rules += Tutor::fieldRules('name_kana', ['required']);
        $rules += Tutor::fieldRules('tel', ['required']);
        $rules += Tutor::fieldRules('email', ['required', $validationEmail]);
        $rules += Tutor::fieldRules('address');
        // 日付はモデルではなく以下のように指定する（スラッシュ区切り・0埋め・0なし許容）
        $rules += ['birth_date' => ['required', 'date_format:Y/m/d,Y/n/j']];
        $rules += Tutor::fieldRules('gender_cd', ['required', $validationGenderList]);
        $rules += Tutor::fieldRules('grade_cd', ['required', $validationGradeList]);
        $rules += Tutor::fieldRules('grade_year');
        $rules += Tutor::fieldRules('school_cd_j', [$validationSchoolList]);
        $rules += Tutor::fieldRules('school_cd_h', [$validationSchoolList]);
        $rules += Tutor::fieldRules('school_cd_u', [$validationSchoolList]);
        $rules += Tutor::fieldRules('hourly_base_wage', ['required']);
        $rules += Tutor::fieldRules('tutor_status', ['required', $validationStatusList]);
        $rules += ['enter_date' => ['required', 'date_format:Y/m/d,Y/n/j']];

        // 退職日ルールはステータスによって分岐
        // MEMO:在籍は後ほどnull変換するので文字列でもOK
        if ($values['tutor_status'] == AppConst::CODE_MASTER_29_2) {
            $rules += ['leave_date' => ['required', 'date_format:Y/m/d,Y/n/j', $validationLeaveDateProspect, $validationLeaveDateAfterEnterDate]];
        } else if ($values['tutor_status'] == AppConst::CODE_MASTER_29_3) {
            $rules += ['leave_date' => ['required', 'date_format:Y/m/d,Y/n/j', $validationLeaveDateExecution, $validationLeaveDateAfterEnterDate]];
        }

        $rules += Tutor::fieldRules('memo');

        return $rules;
    }
}
