<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ReadDataValidateException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Models\ExtSchedule;
use App\Models\BatchMng;
use App\Models\Office;
use App\Models\CodeMaster;
use App\Consts\AppConst;

/**
 * 年度スケジュール取込 - コントローラ
 */
class YearScheduleImportController extends Controller
{
    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
    }

    //==========================
    // 一覧
    //==========================

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        $query = BatchMng::query();
        $students = $query
            ->select(
                'start_time',
                'end_time',
                'batch_state',
                'code_master.name AS batch_state_name',
                'room_names.room_name_full AS room_name',
                'office.name AS executor'
            )
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('batch_state', '=', 'code_master.code')
                    ->where('code_master.data_type', AppConst::CODE_MASTER_22);
            })
            ->sdLeftJoin(Office::class, function ($join) {
                $join->on('batch_mng.adm_id', '=', 'office.adm_id');
            })
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('office.roomcd', '=', 'room_names.code');
            })
            ->where('batch_type', '=', AppConst::BATCH_TYPE_2)
            ->orderBy('start_time', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $students);
    }

    //==========================
    // 取込
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {

        return view('pages.admin.year_schedule_import', [
            'rules' => $this->rulesForInput()
        ]);
    }

    /**
     * 取込処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function create(Request $request)
    {

        // アップロードされたかチェック(アップロードされた場合は該当の項目にファイル名をセットする)
        $this->fileUploadSetVal($request, 'upload_file');

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // アップロード先(アップ先は用途ごとに分ける)
        $uploadDir = config('appconf.upload_dir_year_schedule_import') . date("YmdHis");

        // アップロードファイルの保存
        $path = $this->fileUploadSave($request, $uploadDir, 'upload_file');
        $base_path = base_path();

        // 実行者のアカウントIDを取得
        $account_id = Auth::user()->account_id;

        // 非同期実行する
        if (strpos(PHP_OS, 'WIN') !== false) {
            // Windows
            $command = 'start /b /d ' . base_path() . ' php artisan command:YearScheduleImport ' . $path . ' ' . $account_id;
            $fp = popen($command, 'r');
            pclose($fp);
        } else {
            // Linux
            $command = "cd {$base_path} && php artisan command:YearScheduleImport {$path} {$account_id} > /dev/null &";
            exec($command);
        }

        return;
    }

    /**
     * バリデーション(取込用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInput(Request $request)
    {

        // アップロードされたかチェック(アップロードされた場合は該当の項目にファイル名をセットする)
        $this->fileUploadSetVal($request, 'upload_file');

        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInput());

        // エラーがあれば返却
        if ($validator->fails()) {
            return $validator->errors();
        }

        // バッチ実行中の場合はエラー
        try {
            $exists = BatchMng::where('batch_type', '=', AppConst::BATCH_TYPE_2)
                ->where('batch_state', '=', AppConst::CODE_MASTER_22_99)
                ->exists();

            if ($exists) {
                throw new ReadDataValidateException(Lang::get('validation.already_running'));
            }
        } catch (ReadDataValidateException $e) {
            // ファイルのバリデーションエラーとして返却
            return ['upload_file' => [$e->getMessage()]];
        }

        // パスを取得(upload直後のtmpのパス)
        $path = $this->fileUploadRealPath($request, 'upload_file');
        try {
            // Zipを解凍し、ファイルパス一覧を取得
            $opPathList = $this->unzip($path);
            // 今回は1件しか無いので、1件目を取得
            if (count($opPathList) != 1) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(csvファイル数不正)");
            }
            $csvPath = $opPathList[0];

            // CSVの中身の読み込みとバリデーション
            $this->readData($csvPath);

            // Zip解凍ファイルのクリーンアップ
            $this->unzipCleanUp($opPathList);
        } catch (ReadDataValidateException $e) {
            // ファイルのバリデーションエラーとして返却
            return ['upload_file' => [$e->getMessage()]];
        }

        return;
    }

    /**
     * アップロードされたファイルを読み込む
     * バリデーションも行う
     *
     * @param string $path ファイルパス
     * @return array csv取込データ
     */
    private function readData($path)
    {

        // CSVのヘッダ項目
        $csvHeaders = [
            "id",
            "roomcd",
            "sid",
            "lesson_type",
            "symbol",
            "curriculumcd",
            "rglr_minutes",
            "gmid",
            "period_no",
            "tmid",
            "tid",
            "lesson_date",
            "start_time",
            "r_minutes",
            "end_time",
            "pre_tid",
            "pre_lesson_date",
            "pre_start_time",
            "pre_r_minutes",
            "pre_end_time",
            "chg_status_cd",
            "diff_time",
            "substitute_flg",
            "atd_status_cd",
            "status_info",
            "create_kind_cd",
            "transefer_kind_cd",
            "trn_lesson_date",
            "trn_start_time",
            "trn_r_minutes",
            "trn_end_time",
            "updtime",
            "upduser"
        ];

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
                        . "(" . config('appconf.upload_file_csv_name_T01') . "：ヘッダ行不正)");
                }
                continue;
            }

            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_T01') . "：データ列数不正)");
            }

            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make(
                // 対象
                $values,
                // バリデーションルール
                ExtSchedule::fieldRules('id', ['required'])
                    + ExtSchedule::fieldRules('roomcd', ['required'])
                    + ExtSchedule::fieldRules('sid', ['required'])
                    + ExtSchedule::fieldRules('lesson_type', ['required'])
                    + ExtSchedule::fieldRules('symbol', ['required'])
                    + ExtSchedule::fieldRules('curriculumcd')
                    + ExtSchedule::fieldRules('rglr_minutes')
                    + ExtSchedule::fieldRules('gmid')
                    + ExtSchedule::fieldRules('period_no')
                    + ExtSchedule::fieldRules('tmid')
                    + ExtSchedule::fieldRules('tid')
                    + ExtSchedule::fieldRules('lesson_date', ['required'], '_csv')
                    + ExtSchedule::fieldRules('start_time', [], '_csv')
                    + ExtSchedule::fieldRules('r_minutes')
                    + ExtSchedule::fieldRules('end_time', [], '_csv')
                    + ExtSchedule::fieldRules('pre_tid')
                    + ExtSchedule::fieldRules('pre_lesson_date', [], '_csv')
                    + ExtSchedule::fieldRules('pre_start_time', [], '_csv')
                    + ExtSchedule::fieldRules('pre_r_minutes')
                    + ExtSchedule::fieldRules('pre_end_time', [], '_csv')
                    + ExtSchedule::fieldRules('chg_status_cd')
                    + ExtSchedule::fieldRules('diff_time')
                    + ExtSchedule::fieldRules('substitute_flg')
                    + ExtSchedule::fieldRules('atd_status_cd')
                    + ExtSchedule::fieldRules('status_info')
                    + ExtSchedule::fieldRules('create_kind_cd', ['required'])
                    + ExtSchedule::fieldRules('transefer_kind_cd', ['required'])
                    + ExtSchedule::fieldRules('trn_lesson_date', [], '_csv')
                    + ExtSchedule::fieldRules('trn_start_time', [], '_csv')
                    + ExtSchedule::fieldRules('trn_r_minutes')
                    + ExtSchedule::fieldRules('trn_end_time', [], '_csv')
                    + ExtSchedule::fieldRules('updtime', ['required'], '_csv')
            );

            if ($validator->fails()) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_T01') . "：データ項目不正)");
            }

            // MEMO: スケジュール情報は期間が絞られている前提とし授業日のチェックを行わない

            // 10行目までチェックする
            if ($i === 10) {
                break;
            }
        }
        return;
    }

    /**
     * バリデーションルールを取得(取込用)
     *
     * @return array ルール
     */
    private function rulesForInput()
    {

        $rules = array();

        // 独自バリデーション: ファイル名のチェック
        $validationFileName = function ($attribute, $value, $fail) {

            // ファイル名の先頭をチェック
            // 想定：年次スケジュール情報_20201124114040.zip
            $fileName = config('appconf.upload_file_name_year_schedule_import');
            if (!preg_match('/^' . $fileName . '[0-9]{14}.zip$/', $value)) {
                return $fail(Lang::get('validation.invalid_file'));
            }
        };

        //-----------------------------
        // ファイルアップロード
        //-----------------------------

        // ファイルアップロードの必須チェック
        $rules += ['upload_file' => ['required', $validationFileName]];

        // ファイルのタイプのチェック(「file_項目名」の用にチェックする)
        $rules += ['file_upload_file' => [
            // ファイル
            'file',
            // 拡張子
            'mimes:zip',
            // Laravelが判定したmimetypes
            'mimetypes:application/zip',
        ]];

        return $rules;
    }
}
