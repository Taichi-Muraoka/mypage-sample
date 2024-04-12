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
            DB::transaction(function () use ($batch_id) {

                // 登録処理


                // バッチ管理テーブルのレコードを更新：正常終了
                $end = Carbon::now();
                BatchMng::where('batch_id', '=', $batch_id)
                    ->update([
                        'end_time' => $end,
                        'batch_state' => AppConst::CODE_MASTER_22_0,
                        'updated_at' => $end
                    ]);

                //$sidCount = (string) count($studentIds);
                $sidCount = 0;
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
        //$ids = null;
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

        // CSV読み込み
        $file = $this->readCsv($path, "sjis");

        // 1行ずつ取得
        foreach ($file as $i => $line) {
            if ($i === 0) {
                //-------------
                // ヘッダ行
                //-------------
                $headers = $line;
                Log::info($headers);

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
                } else if ($validator->errors()->has('grade_cd')) {
                    $errCol = "grade_cd=" . $values['grade_cd'];
                    // 以降の項目中略
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "データ項目不正( " . $i + 1 . "行目 student_id=" . $values['student_id'] . ", "
                    . "エラー項目：" . $errCol . " )");
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
    private function rulesForInput(array $value)
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
        // 中略

        $rules += Student::fieldRules('student_id', ['required']);
        $rules += Student::fieldRules('name', ['required']);
        $rules += Student::fieldRules('name_kana', ['required']);
        $rules += Student::fieldRules('grade_cd', ['required', $validationGradeList]);
        $rules += Student::fieldRules('grade_year');
        // 日付はモデルではなく以下のように指定する（スラッシュ区切り・0埋め・0なし許容）
        $rules += ['birth_date' => ['required', 'date_format:Y/m/d,Y/n/j']];
        // 中略
        $rules += Student::fieldRules('memo');

        return $rules;
    }
}
