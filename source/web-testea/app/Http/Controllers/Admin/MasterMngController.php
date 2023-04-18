<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ExtGenericMaster;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ReadDataValidateException;
use Illuminate\Support\Facades\Lang;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Log;

/**
 * マスタ管理 - コントローラ
 */
class MasterMngController extends Controller
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
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        // コード区分のプルダウンを取得
        // コード区分=0より、汎用マスタを参照し、コード 昇順で名称1を表示
        $extGenericMasters = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_000);

        return view('pages.admin.master_mng', [
            'extGenericMasters' => $extGenericMasters,
            'rules' => $this->rulesForSearch()
        ]);
    }

    // 校舎マスタ表示
    public function indexSchool()
    {
        return view('pages.admin.master_mng_school');
    }

    /**
     * 校舎マスタ登録画面
     *
     * @return view
     */
    public function newSchool()
    {
        return view('pages.admin.master_mng_school-input', [
            'editData' => null,
            'rules' => $this->rulesForInput(null)
        ]);
    }

    /**
     * 校舎マスタ登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function createSchool(Request $request)
    {
        return;
    }

    /**
     * 校舎マスタ編集画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function editSchool()
    {
        $editData = [
            'code' => 110,
            'name_school' => "久我山校",
            'name_school_display' => "久我山",
            'name_school_abbreviation' => "久",
            'display_order' => 20,
        ];

        return view('pages.admin.master_mng_school-input', [
            'editData' => $editData,
            'rules' => $this->rulesForInput(null)
        ]);
    }

    /**
     * 校舎マスタ編集処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function updateSchool(Request $request)
    {
        return;
    }

    // 教科マスタ表示
    public function indexSubject()
    {
        return view('pages.admin.master_mng_grade');
    }

    /**
     * 教科マスタ登録画面
     *
     * @return view
     */
    public function newSubject()
    {
        return view('pages.admin.master_mng_subject-input', [
            'editData' => null,
            'rules' => $this->rulesForInput(null)
        ]);
    }

    /**
     * 教科マスタ登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function createSubject(Request $request)
    {
        return;
    }

    /**
     * 教科マスタ編集画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function editSubject()
    {
        $editData = [
            'code' => 110,
            'classification_school' => "小",
            'name_subject' => "国語",
            'display_order' => 1,
        ];

        return view('pages.admin.master_mng_subject-input', [
            'editData' => $editData,
            'rules' => $this->rulesForInput(null)
        ]);
    }

    /**
     * 教科マスタ編集処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function updateSubject(Request $request)
    {
        return;
    }

    // 学年マスタ表示
    public function indexGrade()
    {
        return view('pages.admin.master_mng_grade');
    }

    /**
     * 学年マスタ登録画面
     *
     * @return view
     */
    public function newGrade()
    {
        return view('pages.admin.master_mng_grade-input', [
            'editData' => null,
            'rules' => $this->rulesForInput(null)
        ]);
    }

    /**
     * 学年マスタ登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function createGrade(Request $request)
    {
        return;
    }

    /**
     * 学年マスタ編集画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function editGrade()
    {
        $editData = [
            'code' => 01,
            'classification_school' => "小",
            'name_grade' => "小学1年",
            'name_grade_abbreviation' => "小1",
            'display_order' => 36,
        ];

        return view('pages.admin.master_mng_grade-input', [
            'editData' => $editData,
            'rules' => $this->rulesForInput(null)
        ]);
    }

    /**
     * 学年マスタ編集処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function updateGrade(Request $request)
    {
        return;
    }

    /**
     * バリデーション(検索用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForSearch(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForSearch());
        return $validator->errors();
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch())->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = ExtGenericMaster::query();

        // 検索条件を指定
        $query->SearchCodecls($form);

        // データを取得
        $extGenericMasters = $query
            ->select(
                'codecls',
                'code',
                'value1',
                'value2',
                'value3',
                'value4',
                'value5',
                'name1',
                'name2',
                'name3',
                'name4',
                'name5',
                'disp_order'
            )
            // コード区分が000のものは表示しない
            ->where('codecls', '!=', AppConst::EXT_GENERIC_MASTER_000)
            ->orderBy('codecls')
            ->orderBy('code');

        // ページネータで返却
        return $this->getListAndPaginator($request, $extGenericMasters);
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック イベント
        $validationExtGenericMasterList =  function ($attribute, $value, $fail) {

            // コード区分=0より、汎用マスタを参照し、コード 昇順で名称1を表示
            $extGenericMasters = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_000);

            if (!isset($extGenericMasters[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        $rules += ExtGenericMaster::fieldRules('codecls', [$validationExtGenericMasterList]);

        return $rules;
    }

    //==========================
    // 取込
    //==========================

    /**
     * 取込画面
     *
     * @return view
     */
    public function import()
    {
        return view('pages.admin.master_mng-import', [
            'rules' => $this->rulesForSearch()
        ]);
    }

    // 校舎マスタ取り込み
    public function importSchool()
    {
        return view('pages.admin.master_mng_school-import');
    }

    /**
     * 新規登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function create(Request $request)
    {

        // アップロードされたかチェック(アップロードされた場合は該当の項目にファイル名をセットする)
        $this->fileUploadSetVal($request, 'upload_file');

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // アップロード先(アップ先は用途ごとに分ける)
        $uploadDir = config('appconf.upload_dir_master_mng') . date("YmdHis");

        // アップロードファイルの保存
        $path = $this->fileUploadSave($request, $uploadDir, 'upload_file');

        $datas = [];
        try {
            // Zipを解凍し、ファイルパス一覧を取得
            $opPathList = $this->unzip($path);
            // 今回は1件しか無いので、1件目を取得
            if (count($opPathList) != 1) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file'));
            }
            $csvPath = $opPathList[0];

            // CSVデータの読み込み
            $datas = $this->readData($csvPath);

            // Zip解凍ファイルのクリーンアップ
            $this->unzipCleanUp($opPathList);
        } catch (ReadDataValidateException  $e) {
            // 通常は事前にバリデーションするのでここはありえないのでエラーとする
            return $this->responseErr();
        }

        try {

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($datas) {
                // テーブル名取得
                $table = (new ExtGenericMaster)->getTable();
                // 一旦テーブルをクリア(トランザクションのため、truncateではなくdeleteにした)
                DB::table($table)->delete();
                // まとめて登録
                DB::table($table)->insert($datas);
            });
        } catch (\Exception  $e) {
            // この時点では補足できないエラーとして、詳細は返さずエラーとする
            Log::error($e);
            return $this->responseErr();
        }

        return;
    }

    /**
     * バリデーション(登録用)
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

        // パスを取得(upload直後のtmpのパス)
        $path = $this->fileUploadRealPath($request, 'upload_file');

        try {
            // Zipを解凍し、ファイルパス一覧を取得
            $opPathList = $this->unzip($path);
            // 今回は1件しか無いので、1件目を取得
            if (count($opPathList) != 1) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file'));
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

        // 取り込み対象となるコード
        $targetCodes = [101, 102, 109, 110, 111, 112, 114];

        // CSVのヘッダ項目
        $csvHeaders = [
            "codecls", "code", "value1", "value2", "value3", "value4", "value5",
            "name1", "name2", "name3", "name4", "name5", "disp_order", "updtime", "upduser"
        ];

        // CSVのデータをリストで保持
        $datas = [];
        $headers = [];

        // 現在日時を取得
        $now = Carbon::now();

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
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(ヘッダ行不正)");
                }

                continue;
            }

            //-------------
            // データ行
            //-------------

            // 列数チェックより前に取込不要なcodeclsについてスキップする
            // 対象データかどうかのチェック
            if ($line[0] === '000') {
                // 000 の場合、codeがターゲットのもののみ
                if (!in_array($line[1], $targetCodes)) {
                    continue;
                }
            } else {
                // それ以外は、codeclsがターゲットのもののみ
                if (!in_array($line[0], $targetCodes)) {
                    continue;
                }
            }

            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(データ列数不正)");
            }

            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make(
                // 対象
                $values,
                // バリデーションルール
                ExtGenericMaster::fieldRules('codecls', ['required'])
                    + ExtGenericMaster::fieldRules('code', ['required'])
                    + ExtGenericMaster::fieldRules('value1')
                    + ExtGenericMaster::fieldRules('value2')
                    + ExtGenericMaster::fieldRules('value3')
                    + ExtGenericMaster::fieldRules('value4')
                    + ExtGenericMaster::fieldRules('value5')
                    + ExtGenericMaster::fieldRules('name1')
                    + ExtGenericMaster::fieldRules('name2')
                    + ExtGenericMaster::fieldRules('name3')
                    + ExtGenericMaster::fieldRules('name4')
                    + ExtGenericMaster::fieldRules('name5')
                    + ExtGenericMaster::fieldRules('disp_order')
                    + ExtGenericMaster::fieldRules('updtime', ['required'], '_csv')
            );
            if ($validator->fails()) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(データ項目不正)");
            }

            // 不要カラムの除外
            unset($values['upduser']);

            // 日時をセットする
            $values['created_at'] = $now;
            $values['updated_at'] = $now;
            $values['deleted_at'] = null;

            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // リストに保持しておく
            $datas[] = $values;
        }

        return $datas;
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput()
    {
        $rules = array();

        // 独自バリデーション: ファイル名のチェック
        $validationFileName = function ($attribute, $value, $fail) {

            // ファイル名の先頭をチェック
            // 想定：汎用マスタ_20201124114040.zip
            $fileName = config('appconf.upload_file_name_master_mng');
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
