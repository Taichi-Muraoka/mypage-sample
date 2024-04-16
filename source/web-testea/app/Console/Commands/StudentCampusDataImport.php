<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use App\Models\Student;
use App\Models\BatchMng;
use Carbon\Carbon;
use App\Consts\AppConst;
use App\Http\Controllers\Traits\CtrlCsvTrait;
use App\Http\Controllers\Traits\CtrlFileTrait;
use App\Http\Controllers\Traits\CtrlModelTrait;
use App\Exceptions\ReadDataValidateException;
use App\Models\SeasonStudentRequest;
use App\Models\StudentCampus;

/**
 * 生徒所属情報取込処理（データ移行用） - バッチ処理
 */
class StudentCampusDataImport extends Command
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
    protected $signature = 'command:studentCampusDataImport {path}';

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

            Log::info("Batch studentCampusDataImport Start, PATH: {$path}");

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

                // インポート生徒所属数カウント用
                $studentCampusCount = 0;
                // 洗い替え後のデータか確認用
                $AfterRefresh = [];

                // 1行ずつ取り込んだデータごとに処理
                foreach ($datas as $data) {
                    // --------------
                    // 既存データ削除
                    // --------------
                    // MEMO:洗い替え前の既存データを削除する
                    $sid = $data['student_id'];
                    if (!isset($AfterRefresh[$sid])) {
                        StudentCampus::where('student_id', $data['student_id'])
                            ->forceDelete();

                        SeasonStudentRequest::where('student_id', $data['student_id'])
                            ->forceDelete();

                        // 2ループ目以降に同生徒IDのデータが消されないようチェック配列に追加する
                        $AfterRefresh[$sid] = 1;
                    }

                    // --------------
                    // 新規データ作成
                    // --------------
                    // 生徒所属情報の作成
                    $studentCampus = new StudentCampus;
                    $studentCampus->fill($data)->save();

                    // 特別期間講習生徒連絡情報の作成
                    // 特別期間コードリスト取得
                    $seasonCodes = $this->mdlFormatSeasonCd();
                    foreach ($seasonCodes as $seasonCd) {
                        $seasonStuReq = new SeasonStudentRequest;
                        $seasonStuReq->season_cd = $seasonCd;
                        $seasonStuReq->fill($data)->save();
                    }

                    $studentCampusCount++;
                }

                // バッチ管理テーブルのレコードを更新：正常終了
                $end = Carbon::now();
                BatchMng::where('batch_id', '=', $batch_id)
                    ->update([
                        'end_time' => $end,
                        'batch_state' => AppConst::CODE_MASTER_22_0,
                        'updated_at' => $end
                    ]);

                Log::info("Insert {$studentCampusCount} student_campuses. studentCampusDataImport Succeeded.");
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
                if ($validator->errors()->has('student_id')) {
                    $errCol = "student_id=" . $values['student_id'];
                } else if ($validator->errors()->has('campus_cd')) {
                    $errCol = "campus_cd=" . $values['campus_cd'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "データ項目不正( " . $i + 1 . "行目 student_id=" . $values['student_id'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // 生徒ID・校舎コードの重複チェック
            // 重複チェック用配列$dupCheckを用意し、同じ組み合わせが存在するか判定する
            $sid = $values['student_id'];
            $campusCd = $values['campus_cd'];
            if (isset($dupCheck[$sid][$campusCd])) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "生徒ID・校舎コード組み合わせ重複( 生徒ID=" . $values['student_id'] . ",校舎コード=" . $values['campus_cd'] . " )");
            } else {
                // 存在しなければ適当な値をセットし配列に追加する
                $dupCheck[$sid][$campusCd] = 1;
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

        // MEMO:退会済生徒も移行するためmdlGetStudentList()は使わない
        // 独自バリデーション: リストのチェック 生徒
        $validationStudentList =  function ($attribute, $value, $fail) use ($values) {

            $exists = Student::where('student_id', $values['student_id'])
                ->exists();

            // 存在しなければエラー
            if (!$exists) {
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {
            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += StudentCampus::fieldRules('student_id', ['required', $validationStudentList]);
        $rules += StudentCampus::fieldRules('campus_cd', ['required', $validationRoomList]);

        return $rules;
    }
}
