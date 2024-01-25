<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Exceptions\ReadDataValidateException;
use App\Models\ExtStudentKihon;
use App\Models\BatchMng;
use App\Models\AdminUser;
use App\Models\CodeMaster;
use App\Consts\AppConst;

/**
 * 学年情報取込 - コントローラ
 */
class AllMemberImportController extends Controller
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
        // クエリ作成
        $query = BatchMng::query();
        $students = $query
            ->select(
                'start_time',
                'end_time',
                'batch_state',
                'mst_codes.name AS batch_state_name', 
            )
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('batch_state', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_22);
            })
            ->where('batch_type', '=', AppConst::BATCH_TYPE_1)
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

        return view('pages.admin.all_member_import', [
            'rules' => $this->rulesForInput(),
            'editData' => ["this_year"=>"2022","next_year"=>"2023"]
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
        $uploadDir = config('appconf.upload_dir_all_member_import') . date("YmdHis");

        // アップロードファイルの保存
        $path = $this->fileUploadSave($request, $uploadDir, 'upload_file');
        $base_path = base_path();

        // 実行者のアカウントIDを取得
        $account_id = Auth::user()->account_id;

        // 非同期実行する
        if (strpos(PHP_OS, 'WIN') !== false) {
            // Windows
            $command = 'start /b /d ' . base_path() . ' php artisan command:allMemberImport ' . $path . ' ' . $account_id;
            $fp = popen($command, 'r');
            pclose($fp);
        } else {
            // Linux
            $command = "cd {$base_path} && php artisan command:allMemberImport {$path} {$account_id} > /dev/null &";
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
            $exists = BatchMng::where('batch_type', '=', AppConst::BATCH_TYPE_1)
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

        return [];
    }

    /**
     * アップロードされたファイルを読み込む
     * バリデーションも行う
     *
     * @param string $path ファイルパス
     * @return void
     */
    private function readData($path)
    {

        // ヘッダ行
        $csvHeaders = ["sid", "name", "cls_cd", "mailaddress1", "disp_flg", "updtime", "upduser", "enter_date"];

        $headers = [];

        // CSV読み込み
        $file = $this->readCsv($path, "sjis");

        // 1行ずつ取得
        foreach ($file as $i => $line) {

            // 1行目がヘッダ行
            if ($i === 0) {
                //-------------
                // ヘッダ行
                //-------------
                $headers = $line;

                // [バリデーション] ヘッダが想定通りかチェック
                if ($headers !== $csvHeaders) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(" . config('appconf.upload_file_csv_name_A05') . "：ヘッダ行不正)");
                }

                continue;
            }

            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_A05') . "：データ列数不正)");
            }

            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $rules = [];
            $rules += ExtStudentKihon::fieldRules('sid', ['required']);
            $rules += ExtStudentKihon::fieldRules('name', ['required']);
            $rules += ExtStudentKihon::fieldRules('cls_cd', ['required']);
            $rules += ExtStudentKihon::fieldRules('mailaddress1', ['required']);
            $rules += ExtStudentKihon::fieldRules('enter_date', [], '_csv');
            $rules += ExtStudentKihon::fieldRules('disp_flg', ['required']);
            $rules += ExtStudentKihon::fieldRules('updtime', ['required'], '_csv');

            $validator = Validator::make($values, $rules);
            if ($validator->fails()) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                . config('appconf.upload_file_csv_name_A05')
                . "：データ項目不正( 生徒No=" . $values['sid'] . " )");
            }

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
            // 想定：年次学年情報_20201124114040.zip
            $fileName = config('appconf.upload_file_name_all_member_import_enter');
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
