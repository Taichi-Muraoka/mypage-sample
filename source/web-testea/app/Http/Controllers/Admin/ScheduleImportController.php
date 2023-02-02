<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ReadDataValidateException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Traits\FuncScheduleTrait;

/**
 * スケジュール取込 - コントローラ
 */
class ScheduleImportController extends Controller
{
    // 機能共通処理：スケジュール取込
    use FuncScheduleTrait;

    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
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

        return view('pages.admin.schedule_import');
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
        $uploadDir = config('appconf.upload_dir_schedule_import') . date("YmdHis");

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
            // MEMO: スケジュール情報読込処理共通化・外出し
            $datas = $this->readDataT01($csvPath);

            //  [バリデーション] sid登録チェック
            $this->validationExistsSid($datas);

            // Zip解凍ファイルのクリーンアップ
            $this->unzipCleanUp($opPathList);
        } catch (ReadDataValidateException  $e) {
            // 通常は事前にバリデーションするのでここはありえないのでエラーとする
            return $this->responseErr();
        }

        // ファイルタイプ設定（スケジュール情報 or 模試申込）
        $fileNameTrial = config('appconf.upload_file_name_schedule_import_trial');
        if (preg_match('/^' . $fileNameTrial . '/', $request['upload_file'])) {
            // 模試申込の場合true
            $trial = true;
        } else {
            $trial = false;
        }

        try {
            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($datas, $trial) {
                // スケジュール情報テーブルの登録・更新処理
                // MEMO: registT01は外出しし、会員情報取込処理と共通化
                $this->registT01($datas, $trial, false);
            });
        } catch (\Exception  $e) {
            // この時点では補足できないエラーとして、詳細は返さずエラーとする
            Log::error($e);
            return $this->responseErr();
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
            // MEMO: スケジュール情報読込処理共通化・外出し
            $datas = $this->readDataT01($csvPath);

            //  [バリデーション] sid登録チェック
            $this->validationExistsSid($datas);

            // Zip解凍ファイルのクリーンアップ
            $this->unzipCleanUp($opPathList);
        } catch (ReadDataValidateException $e) {
            // ファイルのバリデーションエラーとして返却
            return ['upload_file' => [$e->getMessage()]];
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
            // 想定１：スケジュール情報_20201124114040.zip
            // 想定２：模試情報_20201124114040.zip
            $fileName = config('appconf.upload_file_name_schedule_import');
            $fileNameTrial = config('appconf.upload_file_name_schedule_import_trial');
            if (!preg_match('/^(' . $fileName . '|'  . $fileNameTrial . ')[0-9]{14}.zip$/', $value)) {
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
