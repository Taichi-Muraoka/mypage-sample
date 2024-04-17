<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use App\Models\Account;
use App\Models\Student;
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
 * 生徒情報取込処理（データ移行用） - バッチ処理
 */
class StudentDataImport extends Command
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
    protected $signature = 'command:studentDataImport {path}';

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

            Log::info("Batch studentDataImport Start, PATH: {$path}");

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
                $datas = $datas;

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

                // インポート生徒数カウント用
                $sidCount = 0;

                // 1行ずつ取り込んだデータごとに処理
                foreach ($datas as $data) {
                    // --------------
                    // 既存データ削除
                    // --------------
                    Student::where('student_id', $data['student_id'])
                        ->forceDelete();

                    Account::where('account_id', $data['student_id'])
                        ->where('account_type', AppConst::CODE_MASTER_7_1)
                        ->forceDelete();

                    // --------------
                    // 新規データ作成
                    // --------------
                    // 生徒情報の作成
                    $student = new Student;
                    $student->student_id = $data['student_id'];
                    $student->fill($data)->save();

                    // MEMO:見込客はアカウント作成しない
                    // アカウント情報の作成
                    if ($data['stu_status'] != AppConst::CODE_MASTER_28_0) {

                        // ログイン種別が生徒メールか保護者メールか判別
                        $email = null;
                        if ($data['login_kind'] == AppConst::CODE_MASTER_8_1) {
                            $email = $data['email_stu'];
                        }
                        if ($data['login_kind'] == AppConst::CODE_MASTER_8_2) {
                            $email = $data['email_par'];
                        }

                        $account = new Account;
                        $account->account_id = $data['student_id'];
                        $account->account_type = AppConst::CODE_MASTER_7_1;
                        $account->email = $email;
                        $account->password = Hash::make($email);
                        $account->password_reset = AppConst::ACCOUNT_PWRESET_0;
                        $account->plan_type = AppConst::CODE_MASTER_10_0;

                        // 退会済はログイン不可
                        if ($data['stu_status'] == AppConst::CODE_MASTER_28_5) {
                            $account->login_flg = AppConst::CODE_MASTER_9_1;
                        } else {
                            $account->login_flg = AppConst::CODE_MASTER_9_0;
                        }

                        $account->save();
                    }

                    $sidCount ++;
                }

                // バッチ管理テーブルのレコードを更新：正常終了
                $end = Carbon::now();
                BatchMng::where('batch_id', '=', $batch_id)
                    ->update([
                        'end_time' => $end,
                        'batch_state' => AppConst::CODE_MASTER_22_0,
                        'updated_at' => $end
                    ]);

                Log::info("Insert {$sidCount} students. studentDataImport Succeeded.");
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
            'student_id',
            'name',
            'name_kana',
            'grade_cd',
            'grade_year',
            'birth_date',
            'school_cd_e',
            'school_cd_j',
            'school_cd_h',
            'is_jukensei',
            'tel_stu',
            'tel_par',
            'email_stu',
            'email_par',
            'login_kind',
            'stu_status',
            'enter_date',
            'leave_date',
            'recess_start_date',
            'recess_end_date',
            'past_enter_term',
            'lead_id',
            'storage_link',
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
                if ($validator->errors()->has('student_id')) {
                    $errCol = "student_id=" . $values['student_id'];
                } else if ($validator->errors()->has('name')) {
                    $errCol = "name=" . $values['name'];
                } else if ($validator->errors()->has('name_kana')) {
                    $errCol = "name_kana=" . $values['name_kana'];
                } else if ($validator->errors()->has('grade_cd')) {
                    $errCol = "grade_cd=" . $values['grade_cd'];
                } else if ($validator->errors()->has('grade_year')) {
                    $errCol = "grade_year=" . $values['grade_year'];
                } else if ($validator->errors()->has('birth_date')) {
                    $errCol = "birth_date=" . $values['birth_date'];
                } else if ($validator->errors()->has('school_cd_e')) {
                    $errCol = "school_cd_e=" . $values['school_cd_e'];
                } else if ($validator->errors()->has('school_cd_j')) {
                    $errCol = "school_cd_j=" . $values['school_cd_j'];
                } else if ($validator->errors()->has('school_cd_h')) {
                    $errCol = "school_cd_h=" . $values['school_cd_h'];
                } else if ($validator->errors()->has('is_jukensei')) {
                    $errCol = "is_jukensei=" . $values['is_jukensei'];
                } else if ($validator->errors()->has('tel_stu')) {
                    $errCol = "tel_stu=" . $values['tel_stu'];
                } else if ($validator->errors()->has('tel_par')) {
                    $errCol = "tel_par=" . $values['tel_par'];
                } else if ($validator->errors()->has('email_stu')) {
                    $errCol = "email_stu=" . $values['email_stu'];
                } else if ($validator->errors()->has('email_par')) {
                    $errCol = "email_par=" . $values['email_par'];
                } else if ($validator->errors()->has('login_kind')) {
                    $errCol = "login_kind=" . $values['login_kind'];
                } else if ($validator->errors()->has('stu_status')) {
                    $errCol = "stu_status=" . $values['stu_status'];
                } else if ($validator->errors()->has('enter_date')) {
                    $errCol = "enter_date=" . $values['enter_date'];
                } else if ($validator->errors()->has('leave_date')) {
                    $errCol = "leave_date=" . $values['leave_date'];
                } else if ($validator->errors()->has('recess_start_date')) {
                    $errCol = "recess_start_date=" . $values['recess_start_date'];
                } else if ($validator->errors()->has('recess_end_date')) {
                    $errCol = "recess_end_date=" . $values['recess_end_date'];
                } else if ($validator->errors()->has('past_enter_term')) {
                    $errCol = "past_enter_term=" . $values['past_enter_term'];
                } else if ($validator->errors()->has('lead_id')) {
                    $errCol = "lead_id=" . $values['lead_id'];
                } else if ($validator->errors()->has('storage_link')) {
                    $errCol = "storage_link=" . $values['storage_link'];
                } else if ($validator->errors()->has('memo')) {
                    $errCol = "memo=" . $values['memo'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "データ項目不正( " . $i + 1 . "行目 student_id=" . $values['student_id'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // 生徒IDの重複チェック
            // 重複チェック用配列$dupSidCheckを用意し、同じ生徒IDが存在するか判定する
            $sid = $values['student_id'];
            if (isset($dupSidCheck[$sid])) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "生徒ID重複( 生徒ID=" . $values['student_id'] . " )");
            } else {
                // 存在しなければ適当な値をセットし配列に追加する
                $dupSidCheck[$sid] = 1;
            }

            // ログイン用生徒メールアドレスの重複チェック
            if ($values['login_kind'] == AppConst::CODE_MASTER_8_1) {
                $emailStu = $values['email_stu'];
                if (isset($dupEmailStuCheck[$emailStu])) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "ログイン用生徒メールアドレス重複( 生徒メールアドレス=" . $values['email_stu'] . " )");
                } else {
                    $dupEmailStuCheck[$emailStu] = 1;
                }
            }

            // ログイン用保護者メールアドレスの重複チェック
            if ($values['login_kind'] == AppConst::CODE_MASTER_8_2) {
                $emailPar = $values['email_par'];
                if (isset($dupEmailParCheck[$emailPar])) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "ログイン用保護者メールアドレス重複( 保護者メールアドレス=" . $values['email_par'] . " )");
                } else {
                    $dupEmailParCheck[$emailPar] = 1;
                }
            }

            //-- 会員ステータスによって不要部分をnullに変換 --//
            // 見込客
            if ($values['stu_status'] == AppConst::CODE_MASTER_28_0) {
                $values['login_kind'] = null;
                $values['enter_date'] = null;
                $values['leave_date'] = null;
                $values['recess_start_date'] = null;
                $values['recess_end_date'] = null;
                $values['past_enter_term'] = 0;
            }

            // 在籍
            if ($values['stu_status'] == AppConst::CODE_MASTER_28_1) {
                $values['leave_date'] = null;
                $values['recess_start_date'] = null;
                $values['recess_end_date'] = null;
            }

            // 休塾予定・休塾
            if ($values['stu_status'] == AppConst::CODE_MASTER_28_2 || $values['stu_status'] == AppConst::CODE_MASTER_28_3) {
                $values['leave_date'] = null;
            }

            //-- NOT NULLエラー回避 --//
            // 学年設定年度が空白の場合は今年度を設定
            if ($values['grade_year'] === '') {
                $values['grade_year'] = $currentYear;
            }

            // 過去通塾期間が空白の場合は0を設定
            if ($values['past_enter_term'] === '') {
                $values['past_enter_term'] = 0;
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

        // 独自バリデーション: リストのチェック 学年
        $validationGradeList =  function ($attribute, $value, $fail) {
            // 学年リストを取得
            $grades = $this->mdlGetGradeList(false);
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

        // 独自バリデーション: リストのチェック 受験生フラグ
        $validationJukenFlagList =  function ($attribute, $value, $fail) {
            // 受験生フラグリストを取得
            $jukenFlagList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_13);
            if (!isset($jukenFlagList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ログインID種別
        $validationLoginKindList =  function ($attribute, $value, $fail) {
            // ログインID種別リストを取得
            $loginKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_8);
            if (!isset($loginKindList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 会員ステータス
        $validationStatusList =  function ($attribute, $value, $fail) {
            // 会員ステータスリストを取得
            $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_28);
            if (!isset($statusList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 会員ステータス「休塾予定」の場合、休塾開始日はシステム日付より未来日
        $validationRecessStartDateCaseProspect = function ($attribute, $value, $fail) use ($values) {
            if ($values['stu_status'] == AppConst::CODE_MASTER_28_2) {
                // 休塾開始日の数値が現在日時の数値を下回っていないかチェック
                if (strtotime($values['recess_start_date']) < strtotime('now')) {
                    // 下回っていた（未来日でない）場合エラー
                    return $fail(Lang::get('validation.after_tomorrow'));
                }
            }
        };

        // 独自バリデーション: 会員ステータス「休塾」の場合、休塾開始日はシステム日付以前（当日含む）
        $validationRecessStartDateCaseExecution = function ($attribute, $value, $fail) use ($values) {
            if ($values['stu_status'] == AppConst::CODE_MASTER_28_3) {
                // 現在日時の数値が休塾開始日の数値を下回っていないかチェック
                if (strtotime('now') <= strtotime($values['recess_start_date'])) {
                    // 下回っていた（システム日付以前でない）場合エラー
                    return $fail(Lang::get('validation.before_or_equal_today',));
                }
            }
        };

        // 独自バリデーション: 休塾終了日は休塾開始日より未来日とする
        $validationRecessDate = function ($attribute, $value, $fail) use ($values) {
            // 休塾終了日の数値が休塾開始日の数値を下回っていないかチェック
            if (strtotime($values['recess_end_date']) <= strtotime($values['recess_start_date'])) {
                // 下回っていた（休塾開始日より未来日でない）場合エラー
                return $fail(Lang::get('validation.after',));
            }
        };

        // 独自バリデーション: 会員ステータス「退会処理中」の場合、退会日はシステム日付より未来日
        $validationLeaveDateProspect = function ($attribute, $value, $fail) use ($values) {
            if ($values['stu_status'] == AppConst::CODE_MASTER_28_4) {
                // 退会日の数値が現在日時の数値を下回っていないかチェック
                if (strtotime($values['leave_date']) < strtotime('now')) {
                    // 下回っていた（未来日でない）場合エラー
                    return $fail(Lang::get('validation.after_tomorrow'));
                }
            }
        };

        // 独自バリデーション: 会員ステータス「退会済」の場合、退会日はシステム日付以前（当日含む）
        $validationLeaveDateExecution = function ($attribute, $value, $fail) use ($values) {
            if ($values['stu_status'] == AppConst::CODE_MASTER_28_5) {
                // 現在日時の数値が退会日の数値を下回っていないかチェック
                if (strtotime('now') <= strtotime($values['leave_date'])) {
                    // 下回っていた（システム日付以前でない）場合エラー
                    return $fail(Lang::get('validation.before_or_equal_today',));
                }
            }
        };

        // 独自バリデーション: 退会日は入会日以降
        $validationLeaveDateAfterEnterDate = function ($attribute, $value, $fail) use ($values) {
            // 退会日の数値が入会日の数値を下回っていないかチェック
            if (strtotime($values['leave_date']) < strtotime($values['enter_date'])) {
                // 下回っていた（入会日以降でない）場合エラー
                return $fail(Lang::get('validation.student_leave_after_or_equal_enter_date'));
            }
        };

        $rules += Student::fieldRules('student_id', ['required']);
        $rules += Student::fieldRules('name', ['required']);
        $rules += Student::fieldRules('name_kana', ['required']);
        $rules += Student::fieldRules('grade_cd', ['required', $validationGradeList]);
        $rules += Student::fieldRules('grade_year');
        // 日付はモデルではなく以下のように指定する（スラッシュ区切り・0埋め・0なし許容）
        $rules += ['birth_date' => ['required', 'date_format:Y/m/d,Y/n/j']];
        $rules += Student::fieldRules('school_cd_e', [$validationSchoolList]);
        $rules += Student::fieldRules('school_cd_j', [$validationSchoolList]);
        $rules += Student::fieldRules('school_cd_h', [$validationSchoolList]);
        $rules += Student::fieldRules('is_jukensei', ['required', $validationJukenFlagList]);
        $rules += Student::fieldRules('tel_stu');
        $rules += Student::fieldRules('tel_par', ['required']);

        // MEMO:見込客はログインアカウントを持たないためメールアドレスは任意登録だが、形式チェックは行なうため分岐する
        if ($values['stu_status'] == AppConst::CODE_MASTER_28_0) {
            $rules += Student::fieldRules('email_stu');
            $rules += Student::fieldRules('email_par');
        } else {
            $rules += Student::fieldRules('email_stu', ['required_if:login_kind,' . AppConst::CODE_MASTER_8_1]);
            $rules += Student::fieldRules('email_par', ['required_if:login_kind,' . AppConst::CODE_MASTER_8_2]);
        }

        $rules += Student::fieldRules('stu_status', ['required', $validationStatusList]);
        $rules += Student::fieldRules('login_kind', ['required_unless:stu_status,' . AppConst::CODE_MASTER_28_0, $validationLoginKindList]);
        $rules += ['enter_date' => ['required_unless:stu_status,' . AppConst::CODE_MASTER_28_0, 'date_format:Y/m/d,Y/n/j']];
        $rules += ['leave_date' => ['required_if:stu_status,' . AppConst::CODE_MASTER_28_4 . ',' . AppConst::CODE_MASTER_28_5, 'date_format:Y/m/d,Y/n/j', $validationLeaveDateProspect, $validationLeaveDateExecution, $validationLeaveDateAfterEnterDate]];
        $rules += ['recess_start_date' => ['required_if:stu_status,' . AppConst::CODE_MASTER_28_2 . ',' . AppConst::CODE_MASTER_28_3, 'date_format:Y/m/d,Y/n/j', $validationRecessStartDateCaseProspect, $validationRecessStartDateCaseExecution]];
        $rules += ['recess_end_date' => ['required_if:stu_status,' . AppConst::CODE_MASTER_28_2 . ',' . AppConst::CODE_MASTER_28_3, 'date_format:Y/m/d,Y/n/j', $validationRecessDate]];
        $rules += Student::fieldRules('past_enter_term');
        $rules += Student::fieldRules('lead_id');
        $rules += Student::fieldRules('storage_link', ['nullable', 'url']);
        $rules += Student::fieldRules('memo');

        return $rules;
    }
}
