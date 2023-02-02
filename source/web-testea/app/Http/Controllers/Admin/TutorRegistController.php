<?php

namespace App\Http\Controllers\Admin;

use App\Consts\AppConst;
use App\Exceptions\ReadDataValidateException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ExtRirekisho;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * 教師登録 - コントローラ
 */
class TutorRegistController extends Controller
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
    // 登録
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {

        return view('pages.admin.tutor_regist', [
            'rules' => $this->rulesForInput()
        ]);
    }

    /**
     * 登録処理
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
        $uploadDir = config('appconf.upload_dir_tutor_regist') . date("YmdHis");

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

                // 履歴書テーブルの登録・更新処理
                foreach ($datas as $data) {
                    // レコードが存在するかチェック(キーを指定)
                    $extRirekisho = ExtRirekisho::firstOrNew(['tid' => $data['tid']]);

                    $extRirekisho->tid = $data['tid'];
                    $extRirekisho->name = $data['name'];
                    $extRirekisho->mailaddress1 = $data['mailaddress1'];
                    $extRirekisho->updtime = $data['updtime'];
                    // 以下の日時はここで設定不要
                    //$extRirekisho->updated_at = $data['updated_at'];
                    //$extRirekisho->deleted_at = $data['deleted_at'];

                    //// 登録時のみ
                    //if (!$extRirekisho->exists) $extRirekisho->created_at = $data['created_at'];

                    $extRirekisho->save();
                }

                // アカウントテーブルの登録・更新処理
                foreach ($datas as $data) {
                    // 論理削除されたアカウントに対象tidが存在するかチェック
                    $reAccount = Account::onlyTrashed()
                        ->where('account_id', $data['tid'])
                        ->where('account_type', AppConst::CODE_MASTER_7_2)
                        ->first();

                    if ($reAccount !== null) {
                        // 対象tidが存在する場合は復元する（再入会対応）
                        $reAccount->restore();
                        $reAccount->account_id = $data['tid'];
                        $reAccount->account_type = AppConst::CODE_MASTER_7_2;
                        $reAccount->email = $data['mailaddress1'];
                        // 初期パスワードのハッシュ化(適当な文字列で生成)
                        // 宣言する→use Illuminate\Support\Facades\Hash;
                        $reAccount->password = Hash::make(md5(time() . rand()));
                        $reAccount->password_reset = AppConst::ACCOUNT_PWRESET_0;
                        $reAccount->save();
                    } else {
                        // レコードが存在するかチェック(キーを指定)
                        $account = Account::firstOrNew(['account_id' => $data['tid'], 'account_type' => AppConst::CODE_MASTER_7_2]);
                        if (!$account->exists) {
                            //---------
                            // 新規登録
                            //---------
                            $account->account_id = $data['tid'];
                            $account->account_type = AppConst::CODE_MASTER_7_2;
                            $account->email = $data['mailaddress1'];
                            // 初期パスワードのハッシュ化(適当な文字列で生成)
                            $account->password = Hash::make(md5(time() . rand()));
                            $account->password_reset = AppConst::ACCOUNT_PWRESET_0;
                            // 以下の日時はここで設定不要
                            //$account->created_at = $data['created_at'];
                            //$account->updated_at = $data['updated_at'];
                            //$account->deleted_at = $data['deleted_at'];
                            $account->save();
                        } else {
                            //---------
                            // 更新
                            //---------
                            $account->account_id = $data['tid'];
                            $account->account_type = AppConst::CODE_MASTER_7_2;
                            $account->email = $data['mailaddress1'];
                            $account->password_reset = AppConst::ACCOUNT_PWRESET_0;
                            // 以下の日時はここで設定不要
                            //$account->updated_at = $data['updated_at'];
                            //$account->deleted_at = $data['deleted_at'];
                            $account->save();
                        }
                    }
                }
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

        // CSVのヘッダ項目
        $csvHeaders = [
            "tid", "name", "mailaddress1", "disp_flg", "updtime", "upduser"
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
                ExtRirekisho::fieldRules('tid', ['required'])
                    + ExtRirekisho::fieldRules('name', ['required'])
                    + ExtRirekisho::fieldRules('mailaddress1', ['required'])
                    + ExtRirekisho::fieldRules('updtime', ['required'], '_csv')
            );

            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('tid')) {
                    $errCol = "教師No=" . $values['tid'];
                } else if ($validator->errors()->has('name')) {
                    $errCol = "名前=" . $values['name'];
                } else if ($validator->errors()->has('mailaddress1')) {
                    $errCol =  "メールアドレス1=" . $values['mailaddress1'];
                } else if ($validator->errors()->has('updtime')) {
                    $errCol =  "更新日時=" . $values['updtime'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "データ項目不正( 教師No=" . $values['tid'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // アカウント情報に対象メールアドレスが登録されているかチェック
            $exists = Account::where('email', $values['mailaddress1'])
                ->where('account_id', "!=", $values['tid'])
                ->exists();

                if ($exists) {
                    // 登録済みエラー
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "：メールアドレス重複( 教師No=" . $values['tid'] . ", "
                        . "メールアドレス1=" . $values['mailaddress1'] . " )");
                }
    

            // CSV列に対して、挿入先テーブルのオブジェクトに変換
            // 今回は読み込んだファイルの列項目がほぼテーブルと同じなので、不足項目を追加
            // 以下の日時はセット不要
            //$values['updtime'] = $now;
            //// 日時をセットする
            //$values['created_at'] = $now;
            //$values['updated_at'] = $now;
            //$values['deleted_at'] = null;
            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // 不要な項目を削除
            unset($values['upduser']);
            unset($values['disp_flg']);

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
            // 想定：教師情報_20201124114040.zip
            $fileName = config('appconf.upload_file_name_tutor_regist');
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
