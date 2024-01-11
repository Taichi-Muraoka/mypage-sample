<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Exceptions\ReadDataValidateException;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\CodeMaster;
use App\Models\MstSchool;

/**
 * 学校コード取込 - コントローラ
 */
class ImportSchoolCodeController extends Controller
{
    /**
     * Zipファイル内CSVファイル数
     */
    const CSVFILE_MAX = 3;

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
     * 取込画面
     *
     * @return view
     */
    public function index()
    {
        // 全体管理者でない場合は画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        return view('pages.admin.import_school_code');
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
        $uploadDir = config('appconf.upload_dir_school_code_import') . date("YmdHis");

        // アップロードファイルの保存
        $path = $this->fileUploadSave($request, $uploadDir, 'upload_file');

        try {
            // Zipを解凍し、ファイルパス一覧を取得
            $opPathList = $this->unzip($path);

            // 解凍したcsvファイルの読み込み
            $datas = $this->readData($opPathList);

            // Zip解凍ファイルのクリーンアップ
            $this->unzipCleanUp($opPathList);
        } catch (ReadDataValidateException  $e) {
            // 通常は事前にバリデーションするのでここはありえないのでエラーとする
            return $this->responseErr();
        }

        try {
            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($datas) {
                //==========================
                // 既存データ削除
                //==========================
                MstSchool::query()->forceDelete();

                //==========================
                // 新規データ作成
                //==========================
                // 1行ずつ取り込んだデータごとに処理
                foreach ($datas as $data) {
                    $mstSchool = new MstSchool;
                    $mstSchool->school_cd = $data['学校コード'];
                    $mstSchool->school_kind = $data['学校種'];
                    $mstSchool->school_kind_cd = $data['学校種コード'];
                    $mstSchool->pref_cd = $data['都道府県番号'];
                    $mstSchool->establish_kind = $data['設置区分'];
                    $mstSchool->branch_kind = $data['本分校'];
                    $mstSchool->name = $data['学校名'];
                    $mstSchool->address = $data['学校所在地'];
                    $mstSchool->post_code = $data['郵便番号'];
                    $mstSchool->setting_date = $data['属性情報設定年月日'];
                    $mstSchool->abolition_date = $data['属性情報廃止年月日'];
                    $mstSchool->old_shool_cd = $data['旧学校調査番号'];
                    $mstSchool->change_flg = $data['移行後の学校コード'];

                    // 保存
                    $mstSchool->save();
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

            // 解凍したcsvファイルの読み込み・バリデーション
            $this->readData($opPathList);

            // Zip解凍ファイルのクリーンアップ
            $this->unzipCleanUp($opPathList);
        } catch (ReadDataValidateException $e) {
            // ファイルのバリデーションエラーとして返却
            return ['upload_file' => [$e->getMessage()]];
        }

        return;
    }

    /**
     * 解凍されたcsvファイルの読み込み・バリデーション
     *
     * @param mixed $opPathList
     * @return mixed CSV取込データ
     */
    private function readData($opPathList)
    {
        // csvファイル数のチェック
        if (count($opPathList) <> self::CSVFILE_MAX) {
            throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                . "(csvファイル数不正)");
        }

        //-----------------------------------
        // CSVの中身の読み込みとバリデーション
        //-----------------------------------
        // return用配列
        $datas = [];

        // 学校種のチェック用に汎用項目1を取得
        $schoolKindList = CodeMaster::select('code', 'gen_item1')
            ->where('data_type', '=', AppConst::CODE_MASTER_49)
            ->get()
            ->keyBy('gen_item1');

        // 設置区分のチェック用コード取得
        $establishKindList = CodeMaster::select('code')
            ->where('data_type', '=', AppConst::CODE_MASTER_50)
            ->get()
            ->keyBy('code');

        // 本分校のチェック用コード取得
        $branchKindList = CodeMaster::select('code')
            ->where('data_type', '=', AppConst::CODE_MASTER_51)
            ->get()
            ->keyBy('code');

        // CSVのヘッダ項目
        $csvHeaders = [
            "学校コード",
            "学校種",
            "都道府県番号",
            "設置区分",
            "本分校",
            "学校名",
            "学校所在地",
            "郵便番号",
            "属性情報設定年月日",
            "属性情報廃止年月日",
            "旧学校調査番号",
            "移行後の学校コード"
        ];

        // 以下のループ処理中にタイムアウトしないよう設定
        ini_set("max_execution_time", 0);

        // CSVファイルごとにループ処理
        foreach ($opPathList as $path) {
            // CSV読み込み
            // MEMO:文科省提示のCSVファイルはUTF8
            $file = $this->readCsv($path, "utf8");

            // 1行ずつ取得
            foreach ($file as $i => $line) {

                // 最初の1行は読み飛ばす
                if ($i < 1) {
                    continue;
                }

                // [バリデーション] データ行の列の数のチェック
                // データ行の末尾に余計なカンマが入っていても許容する
                if (count($line) < count($csvHeaders)) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(" . basename($path) . "：データ列数不正)");
                }

                // csvHeadersをもとに、値をセットしたオブジェクトを生成
                // MEMO:バリデーションに引っ掛かるため不要なスペースはtrimで削除する
                $values = array_combine($csvHeaders, $line);
                $values = array_map('trim', $values);

                // バリデーションルールセット
                $rules = [
                    '学校コード' => 'string|max:13|required',
                    '学校種' => 'string|max:2|required',
                    '都道府県番号' => 'string|max:2|required',
                    '設置区分' => 'integer|required',
                    '本分校' => 'integer|required',
                    '学校名' => 'string|max:50|required',
                    '学校所在地' => 'string|max:100|required',
                    '郵便番号' => 'string|max:7|required',
                    '属性情報設定年月日' => 'date',
                    '属性情報廃止年月日' => 'date',
                    '旧学校調査番号' => 'string|max:6',
                    '移行後の学校コード' => 'string|max:100',
                ];

                // バリデーションルールチェック
                $validator = Validator::make($values, $rules);
                if ($validator->fails()) {
                    $errCol = "";
                    if ($validator->errors()->has('学校コード')) {
                        $errCol = "学校コード=" . $values['学校コード'];
                    } else if ($validator->errors()->has('学校種')) {
                        $errCol = "学校種=" . $values['学校種'];
                    } else if ($validator->errors()->has('都道府県番号')) {
                        $errCol = "都道府県番号=" . $values['都道府県番号'];
                    } else if ($validator->errors()->has('設置区分')) {
                        $errCol = "設置区分=" . $values['設置区分'];
                    } else if ($validator->errors()->has('本分校')) {
                        $errCol = "本分校=" . $values['本分校'];
                    } else if ($validator->errors()->has('学校名')) {
                        $errCol = "学校名=" . $values['学校名'];
                    } else if ($validator->errors()->has('学校所在地')) {
                        $errCol = "学校所在地=" . $values['学校所在地'];
                    } else if ($validator->errors()->has('郵便番号')) {
                        $errCol = "郵便番号=" . $values['郵便番号'];
                    } else if ($validator->errors()->has('属性情報設定年月日')) {
                        $errCol = "属性情報設定年月日=" . $values['属性情報設定年月日'];
                    } else if ($validator->errors()->has('属性情報廃止年月日')) {
                        $errCol = "属性情報廃止年月日=" . $values['属性情報廃止年月日'];
                    } else if ($validator->errors()->has('旧学校調査番号')) {
                        $errCol = "旧学校調査番号=" . $values['旧学校調査番号'];
                    } else if ($validator->errors()->has('移行後の学校コード')) {
                        $errCol = "移行後の学校コード=" . $values['移行後の学校コード'];
                    }
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . basename($path)
                        . "：データ項目不正( 学校コード=" . $values['学校コード'] . ", "
                        . "エラー項目：" . $errCol . " )");
                }

                // 学校種のコードが存在しなかったらエラー
                $schoolKindKey = $values['学校種'];
                if (!isset($schoolKindList[$schoolKindKey])) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . basename($path)
                        . "：学校種不正( 学校コード=" . $values['学校コード'] . ", "
                        . "学校種=" . $values['学校種'] . " )");
                }

                // 設置区分のコードが存在しなかったらエラー
                $establishKindKey = $values['設置区分'];
                if (!isset($establishKindList[$establishKindKey])) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . basename($path)
                        . "：設置区分不正( 学校コード=" . $values['学校コード'] . ", "
                        . "設置区分=" . $values['設置区分'] . " )");
                }

                // 本分校のコードが存在しなかったらエラー
                $branchKindKey = $values['本分校'];
                if (!isset($branchKindList[$branchKindKey])) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . basename($path)
                        . "：本分校不正( 学校コード=" . $values['学校コード'] . ", "
                        . "本分校=" . $values['本分校'] . " )");
                }

                // 学校コードの重複チェック
                // 重複チェック用配列$duplicateCheckを用意し、同じ学校コードが存在するか判定する
                $schoolCode = $values['学校コード'];
                if (isset($duplicateCheck[$schoolCode])) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . basename($path)
                        . "：学校コード重複( 学校コード=" . $values['学校コード'] . " )");
                } else {
                    // 存在しなければ適当な値をセットし配列に追加する
                    $duplicateCheck[$schoolCode] = 1;
                }

                // 学校種に該当するコードマスタ学校種コードをセットする
                // 例：'A1' → 1, 'F1' → 11
                foreach ($schoolKindList as $schoolKind) {
                    if ($values['学校種'] == $schoolKind['gen_item1']) {
                        $values['学校種コード'] = $schoolKind['code'];
                        break;
                    }
                }

                // return用の$datas配列に1行分のデータを格納する
                array_push($datas, $values);
            }
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

        // ファイルアップロードの必須チェック
        $rules += ['upload_file' => ['required']];

        // ファイルのタイプのチェック(「file_項目名」のようにチェックする)
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
