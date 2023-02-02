<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ExtStudentKihon;
use App\Models\ExtRoom;
use App\Models\ExtRegular;
use App\Models\ExtRegularDetail;
use App\Models\ExtExtraIndividual;
use App\Models\ExtExtraIndDetail;
use App\Models\ExtHomeTeacherStd;
use App\Models\ExtHomeTeacherStdDetail;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ReadDataValidateException;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use App\Consts\AppConst;
use App\Models\TutorRelate;
use App\Http\Controllers\Traits\FuncScheduleTrait;
use App\Models\ExtSchedule;

/**
 * 会員情報取込 - コントローラ
 */
class MemberImportController extends Controller
{
    // 機能共通処理：スケジュール取込
    use FuncScheduleTrait;

    // MEMO: 教室管理者であっても取り込み可能なのでガードは不要

    /**
     * Zipファイルタイプ
     */
    const FILETYPE_ENTR = 1;
    const FILETYPE_CRSE = 2;
    const FILETYPE_INDI = 3;

    /**
     * Zipファイル内CSVファイル数
     */
    const CSVFILE_MAX_ENTR = 9;
    const CSVFILE_MAX_CRSE = 8;
    const CSVFILE_MAX_INDI = 3;

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

        return view('pages.admin.member_import', [
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
        $this->fileUploadSetVal($request, 'upload_file_member');

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // アップロード先(アップ先は用途ごとに分ける)
        $uploadDir = config('appconf.upload_dir_member_import') . date("YmdHis");

        // アップロードファイルの保存
        $path = $this->fileUploadSave($request, $uploadDir, 'upload_file_member');

        try {
            // Zipを解凍し、ファイルパス一覧を取得
            $opPathList = $this->unzip($path);

            //-----------------------------------
            // 解凍したcsvファイルの読み込み
            //-----------------------------------
            $datas = $this->readDataAll($opPathList, $request['upload_file_member']);

            // Zip解凍ファイルのクリーンアップ
            $this->unzipCleanUp($opPathList);
        } catch (ReadDataValidateException  $e) {
            // 通常は事前にバリデーションするのでここはありえないのでエラーとする
            return $this->responseErr();
        }

        try {
            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($datas, $request) {

                //-----------------------------------
                // テーブル登録処理
                //-----------------------------------
                $delSkip = false;
                if ($datas['A05'] !== []) {
                    // 生徒基本情報テーブルの登録・更新処理（アカウントテーブル登録含む）
                    $this->registA05($datas['A05']);
                    // 入会者情報の場合
                    if (preg_match(
                        '/^' . config('appconf.upload_file_name_member_import_enter') . '/',
                        $request['upload_file_member']
                    )) {
                        // 生徒基本情報csvのsidに紐づく情報を削除(A04/A10/A11/A30/A31/A60/A61/T01)
                        $this->deleteStudentData($datas['A05']);
                        $delSkip = true;
                    }
                }
                if ($datas['A04'] !== []) {
                    // 教室情報テーブルの登録・更新処理
                    $this->registA04($datas['A04'], $delSkip);
                }
                if ($datas['A10'] !== []) {
                    // 規定情報テーブルの登録・更新処理
                    $this->registA10($datas['A10'], $delSkip);
                }
                if ($datas['A11'] !== []) {
                    // 規定情報情報明細テーブルの登録・更新処理
                    $this->registA11($datas['A11'], $delSkip);
                }
                if ($datas['A30'] !== [] && $datas['A31'] !== []) {
                    // 個別講習情報テーブルの登録・更新処理
                    $this->registA30($datas['A30'], $datas['A31'], $delSkip);
                }
                if ($datas['A31'] !== []) {
                    // 個別講習情報明細テーブルの登録・更新処理
                    $this->registA31($datas['A31'], $delSkip);
                }
                if ($datas['A60'] !== []) {
                    // 家庭教師標準情報テーブルの登録・更新処理
                    $this->registA60($datas['A60'], $delSkip);
                }
                if ($datas['A61'] !== []) {
                    // 家庭教師標準情報詳細テーブルの登録・更新処理
                    $this->registA61($datas['A61'], $delSkip);
                }
                //-----------------------------------
                // 教師関連情報テーブルデータ作成処理
                //-----------------------------------
                if ($datas['A11'] !== [] || $datas['A31'] !== [] || $datas['A61'] !== []) {
                    // 規定情報明細・個別講習情報明細・家庭教師標準情報詳細のいずれかに更新があるときのみ
                    $this->registTutorRelate($datas);
                }
                // ピンチヒッター（授業代行）の教師情報登録対応
                // registT01内で教師関連情報を追加登録するため、registTutorRelate処理後に行う
                if ($datas['T01'] !== []) {
                    // スケジュール情報テーブルの登録・更新処理
                    // MEMO: registT01は外出しし、スケジュール取込処理と共通化
                    $this->registT01($datas['T01'], false, $delSkip);
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
        $this->fileUploadSetVal($request, 'upload_file_member');

        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInput());

        // エラーがあれば返却
        if ($validator->fails()) {
            return $validator->errors();
        }

        // パスを取得(upload直後のtmpのパス)
        $path = $this->fileUploadRealPath($request, 'upload_file_member');
        try {
            // Zipを解凍し、ファイルパス一覧を取得
            $opPathList = $this->unzip($path);

            //-----------------------------------
            // 解凍したcsvファイルの読み込み・バリデーション
            //-----------------------------------
            $this->readDataAll($opPathList, $request['upload_file_member']);

            // Zip解凍ファイルのクリーンアップ
            $this->unzipCleanUp($opPathList);
        } catch (ReadDataValidateException $e) {
            // ファイルのバリデーションエラーとして返却
            return ['upload_file_member' => [$e->getMessage()]];
        }

        return;
    }

    /**
     * 解凍されたcsvファイルの読み込み・バリデーション
     *
     * @param mixed $opPathList
     * @param string $uploadFileName
     * @return mixed CSV取込データ
     */
    private function readDataAll($opPathList, $uploadFileName)
    {

        $fileNameEntr = config('appconf.upload_file_name_member_import_enter');
        $fileNameCrse = config('appconf.upload_file_name_member_import_course');
        $fileNameIndi = config('appconf.upload_file_name_member_import_individual');

        // csvファイル数のチェック（入会者情報）
        // MEMO: zipファイル種別毎にcsvファイル数は固定となる
        if (preg_match('/^' . $fileNameEntr . '/', $uploadFileName)) {
            $fileType = self::FILETYPE_ENTR;
            if (count($opPathList) <> self::CSVFILE_MAX_ENTR) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(csvファイル数不正)");
            }
        }
        // csvファイル数のチェック（コース追加変更情報）
        // MEMO: zipファイル種別毎にcsvファイル数は固定となる
        if (preg_match('/^' . $fileNameCrse . '/', $uploadFileName)) {
            $fileType = self::FILETYPE_CRSE;
            if (count($opPathList) <> self::CSVFILE_MAX_CRSE) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(csvファイル数不正)");
            }
        }
        // csvファイル数のチェック（短期講習申込情報）
        // MEMO: zipファイル種別毎にcsvファイル数は固定となる
        if (preg_match('/^' . $fileNameIndi . '/', $uploadFileName)) {
            $fileType = self::FILETYPE_INDI;
            if (count($opPathList) <> self::CSVFILE_MAX_INDI) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(csvファイル数不正)");
            }
        }

        //-----------------------------------
        // CSVの中身の読み込みとバリデーション
        //-----------------------------------
        $csvNames['A05'] = config('appconf.upload_file_csv_name_A05');
        $csvNames['A04'] = config('appconf.upload_file_csv_name_A04');
        $csvNames['A10'] = config('appconf.upload_file_csv_name_A10');
        $csvNames['A11'] = config('appconf.upload_file_csv_name_A11');
        $csvNames['A30'] = config('appconf.upload_file_csv_name_A30');
        $csvNames['A31'] = config('appconf.upload_file_csv_name_A31');
        $csvNames['A60'] = config('appconf.upload_file_csv_name_A60');
        $csvNames['A61'] = config('appconf.upload_file_csv_name_A61');
        $csvNames['T01'] = config('appconf.upload_file_csv_name_T01');

        // 配列キーの定義（csvファイルがない場合もエラーにしない）
        $datas['A05'] = [];
        $datas['A04'] = [];
        $datas['A10'] = [];
        $datas['A11'] = [];
        $datas['A30'] = [];
        $datas['A31'] = [];
        $datas['A60'] = [];
        $datas['A61'] = [];
        $datas['T01'] = [];

        foreach ($csvNames as $key => $val) {
            $csvPath = array_values(preg_grep('/' . $csvNames[$key] . '\.csv$/', $opPathList));
            if (!empty($csvPath)) {
                switch ($key) {
                    case 'A05':
                        // ファイルパス一覧に生徒基本情報ファイルが含まれる場合
                        // 生徒基本情報ファイル読み込み処理
                        $datas['A05'] = $this->readDataA05($csvPath[0]);
                        break;
                    case 'A04':
                        // ファイルパス一覧に教室情報ファイルが含まれる場合
                        // 教室情報ファイル読み込み処理
                        $datas['A04'] = $this->readDataA04($csvPath[0]);
                        if ($fileType != self::FILETYPE_ENTR) {
                            //  [バリデーション] sid登録チェック
                            // 入会者情報以外の場合に、登録済み生徒のデータであるか
                            $this->validationExistsSid($datas['A04']);
                        }
                        break;
                    case 'A10':
                        // ファイルパス一覧に規定情報ファイルが含まれる場合
                        // 規定情報ファイル読み込み処理
                        $datas['A10'] = $this->readDataA10($csvPath[0]);
                        if ($fileType != self::FILETYPE_ENTR) {
                            //  [バリデーション] sid登録チェック
                            // 入会者情報以外の場合に、登録済み生徒のデータであるか
                            $this->validationExistsSid($datas['A10']);
                        }
                        break;
                    case 'A11':
                        // ファイルパス一覧に規定情報明細ファイルが含まれる場合
                        // 規定情報明細ファイル読み込み処理
                        $datas['A11'] = $this->readDataA11($csvPath[0]);
                        if ($fileType != self::FILETYPE_ENTR) {
                            //  [バリデーション] sid登録チェック
                            $this->validationExistsSid($datas['A11']);
                        }
                        break;
                    case 'A30':
                        // ファイルパス一覧に個別講習情報ファイルが含まれる場合
                        // 個別講習情報ファイル読み込み処理
                        $datas['A30'] = $this->readDataA30($csvPath[0]);
                        if ($fileType != self::FILETYPE_ENTR) {
                            //  [バリデーション] sid登録チェック
                            $this->validationExistsSid($datas['A30']);
                        }
                        break;
                    case 'A31':
                        // ファイルパス一覧に個別講習情報明細ファイルが含まれる場合
                        // 個別講習明細ファイル読み込み処理
                        $datas['A31'] = $this->readDataA31($csvPath[0]);
                        if ($fileType != self::FILETYPE_ENTR) {
                            //  [バリデーション] sid登録チェック
                            $this->validationExistsSid($datas['A31']);
                        }
                        break;
                    case 'A60':
                        // ファイルパス一覧に家庭教師標準情報ファイルが含まれる場合
                        // 家庭教師標準情報ファイル読み込み処理
                        $datas['A60'] = $this->readDataA60($csvPath[0]);
                        if ($fileType != self::FILETYPE_ENTR) {
                            // 独自バリデーション：sid登録チェック
                            $this->validationExistsSid($datas['A60']);
                        }
                        break;
                    case 'A61':
                        // ファイルパス一覧に家庭教師標準情報詳細ファイルが含まれる場合
                        // 家庭教師標準情報詳細ファイル読み込み処理
                        $datas['A61'] = $this->readDataA61($csvPath[0]);
                        if ($fileType != self::FILETYPE_ENTR) {
                            //  [バリデーション] sid登録チェック
                            $this->validationExistsSid($datas['A61']);
                        }
                        break;
                    case 'T01':
                        // ファイルパス一覧にスケジュール情報ファイルが含まれる場合
                        // スケジュール情報ファイル読み込み処理
                        // MEMO: readDataT01は外出しし、スケジュール取込処理と共通化
                        $datas['T01'] = $this->readDataT01($csvPath[0]);
                        if ($fileType != self::FILETYPE_ENTR) {
                            //  [バリデーション] sid登録チェック
                            $this->validationExistsSid($datas['T01']);
                        }
                        break;
                }
            }
        }
        return $datas;
    }

    /**
     * アップロードされたファイルを読み込む（生徒基本情報）
     * バリデーションも行う
     *
     * @param string $path ファイルパス
     * @return array CSV取込データ
     */
    private function readDataA05($path)
    {

        // CSVのヘッダ項目
        $csvHeaders = [
            "sid", "name", "cls_cd", "mailaddress1", "disp_flg", "updtime", "upduser", "enter_date"
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
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(" . config('appconf.upload_file_csv_name_A05') . "：ヘッダ行不正)");
                }
                continue;
            }
            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_A05') . "：データ列数不正)");
            }
            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make(
                // 対象
                $values,
                // バリデーションルール
                ExtStudentKihon::fieldRules('sid', ['required'])
                    + ExtStudentKihon::fieldRules('name', ['required'])
                    + ExtStudentKihon::fieldRules('cls_cd', ['required'])
                    + ExtStudentKihon::fieldRules('mailaddress1', ['required'])
                    + ExtStudentKihon::fieldRules('enter_date', [], '_csv')
                    + ExtStudentKihon::fieldRules('disp_flg', ['required'])
                    + ExtStudentKihon::fieldRules('updtime', ['required'], '_csv')
            );

            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('sid')) {
                    $errCol = "生徒No=" . $values['sid'];
                } else if ($validator->errors()->has('name')) {
                    $errCol = "名前=" . $values['name'];
                } else if ($validator->errors()->has('cls_cd')) {
                    $errCol =  "学年=" . $values['cls_cd'];
                } else if ($validator->errors()->has('mailaddress1')) {
                    $errCol =  "メールアドレス1=" . $values['mailaddress1'];
                } else if ($validator->errors()->has('enter_date')) {
                    $errCol =  "入会日=" . $values['enter_date'];
                } else if ($validator->errors()->has('disp_flg')) {
                    $errCol =  "表示フラグ=" . $values['disp_flg'];
                } else if ($validator->errors()->has('updtime')) {
                    $errCol =  "更新日時=" . $values['updtime'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . config('appconf.upload_file_csv_name_A05')
                    . "：データ項目不正( 生徒No=" . $values['sid'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // アカウント情報に対象メールアドレスが登録されているかチェック
            $exists = Account::where('email', $values['mailaddress1'])
                ->where('account_id', "!=", $values['sid'])
                ->exists();

            if ($exists) {
                // 登録済みエラー
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . config('appconf.upload_file_csv_name_A05')
                    . "：メールアドレス重複( 生徒No=" . $values['sid'] . ", "
                    . "メールアドレス1=" . $values['mailaddress1'] . " )");
            }

            // MEMO: saveで登録するため、ここでの日時セット処理は不要
            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // 不要な項目を削除
            unset($values['upduser']);

            // リストに保持しておく
            $datas[] = $values;
        }
        return $datas;
    }

    /**
     * アップロードされたファイルを読み込む（教室情報）
     * バリデーションも行う
     *
     * @param string $path ファイルパス
     * @return array csv取込データ
     */
    private function readDataA04($path)
    {

        // CSVのヘッダ項目
        $csvHeaders = [
            "sid", "roomcd", "updtime", "upduser"
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
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(" . config('appconf.upload_file_csv_name_A04') . "：ヘッダ行不正)");
                }
                continue;
            }
            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_A04') . "：データ列数不正)");
            }
            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make(
                // 対象
                $values,
                // バリデーションルール
                ExtRoom::fieldRules('sid', ['required'])
                    + ExtRoom::fieldRules('roomcd', ['required'])
                    + ExtRoom::fieldRules('updtime', ['required'], '_csv')
            );

            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('sid')) {
                    $errCol = "生徒No=" . $values['sid'];
                } else if ($validator->errors()->has('roomcd')) {
                    $errCol = "教室コード=" . $values['roomcd'];
                } else if ($validator->errors()->has('updtime')) {
                    $errCol =  "更新日時=" . $values['updtime'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . config('appconf.upload_file_csv_name_A04')
                    . "：データ項目不正( 生徒No=" . $values['sid'] . ", "
                    . "教室コード=" . $values['roomcd'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // MEMO: saveで登録するため、ここでの日時セット処理は不要
            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // 不要な項目を削除
            unset($values['upduser']);

            // リストに保持しておく
            $datas[] = $values;
        }
        return $datas;
    }

    /**
     * アップロードされたファイルを読み込む（規定情報）
     * バリデーションも行う
     *
     * @param string $path ファイルパス
     * @return array csv取込データ
     */
    private function readDataA10($path)
    {

        // CSVのヘッダ項目
        $csvHeaders = [
            "roomcd", "sid", "r_seq", "startdate", "enddate", "regular_summary", "tuition", "base_tuition",
            "base_time", "updtime", "upduser", "rkcd", "kskb", "kaisu", "jkkb", "jikan",
            "listfee", "listfeeit", "kskb_ov", "kaisu_ov", "jkkb_ov", "jikan_ov", "listfee_ov", "listfeeit_ov",
            "opkb", "uchishitei", "ukaisu", "ujikan", "uopkb", "listfee_kitei", "listfeeit_kitei"
        ];
        // CSVのデータをリストで保持
        $datas = [];
        $headers = [];
        // 現在日時を取得
        $now = Carbon::now();
        // 前年度の開始日を取得
        $prevStart = new Carbon($this->dtGetFiscalDate('prev', 'start'));
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
                        . "(" . config('appconf.upload_file_csv_name_A10') . "：ヘッダ行不正)");
                }
                continue;
            }
            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_A10') . "：データ列数不正)");
            }
            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make(
                // 対象
                $values,
                // バリデーションルール
                ExtRegular::fieldRules('roomcd', ['required'])
                    + ExtRegular::fieldRules('sid', ['required'])
                    + ExtRegular::fieldRules('r_seq', ['required'])
                    + ExtRegular::fieldRules('startdate', ['required'], '_csv')
                    + ExtRegular::fieldRules('enddate', ['required'], '_csv')
                    + ExtRegular::fieldRules('regular_summary', ['required'])
                    + ExtRegular::fieldRules('tuition', ['required'])
                    + ExtRegular::fieldRules('base_tuition', ['required'])
                    + ExtRegular::fieldRules('base_time', ['required'])
                    + ExtRegular::fieldRules('updtime', ['required'], '_csv')
            );

            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('sid')) {
                    $errCol = "生徒No=" . $values['sid'];
                } else if ($validator->errors()->has('roomcd')) {
                    $errCol = "教室コード=" . $values['roomcd'];
                } else if ($validator->errors()->has('r_seq')) {
                    $errCol = "規定情報連番=" . $values['r_seq'];
                } else if ($validator->errors()->has('startdate')) {
                    $errCol = "開始日=" . $values['startdate'];
                } else if ($validator->errors()->has('enddate')) {
                    $errCol = "終了日=" . $values['enddate'];
                } else if ($validator->errors()->has('regular_summary')) {
                    $errCol = "規定内容概略=" . $values['regular_summary'];
                } else if ($validator->errors()->has('tuition')) {
                    $errCol = "月額規定授業料=" . $values['tuition'];
                } else if ($validator->errors()->has('base_tuition')) {
                    $errCol = "料金表金額=" . $values['base_tuition'];
                } else if ($validator->errors()->has('base_time')) {
                    $errCol = "料金表時間=" . $values['base_time'];
                } else if ($validator->errors()->has('updtime')) {
                    $errCol =  "更新日時=" . $values['updtime'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . config('appconf.upload_file_csv_name_A10')
                    . "：データ項目不正( 生徒No=" . $values['sid'] . ", "
                    . "教室コード=" . $values['roomcd'] . ", "
                    . "規定情報連番=" . $values['r_seq'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // 開始日・終了日（契約期間）のチェック
            if (Carbon::parse($values['startdate']) < $prevStart && Carbon::parse($values['enddate']) < $prevStart) {
                // 前年度より前のデータは取込対象外とする
                continue;
            }

            // MEMO: saveで登録するため、ここでの日時セット処理は不要
            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // 不要な項目を削除
            unset($values['upduser']);
            unset($values['rkcd']);
            unset($values['kskb']);
            unset($values['kaisu']);
            unset($values['jkkb']);
            unset($values['jikan']);
            unset($values['listfee']);
            unset($values['listfeeit']);
            unset($values['kskb_ov']);
            unset($values['kaisu_ov']);
            unset($values['jkkb_ov']);
            unset($values['jikan_ov']);
            unset($values['listfee_ov']);
            unset($values['listfeeit_ov']);
            unset($values['opkb']);
            unset($values['uchishitei']);
            unset($values['ukaisu']);
            unset($values['ujikan']);
            unset($values['uopkb']);
            unset($values['listfee_kitei']);
            unset($values['listfeeit_kitei']);

            // リストに保持しておく
            $datas[] = $values;
        }
        return $datas;
    }

    /**
     * アップロードされたファイルを読み込む（規定情報明細）
     * バリデーションも行う
     *
     * @param string $path ファイルパス
     * @return array csv取込データ
     */
    private function readDataA11($path)
    {

        // CSVのヘッダ項目
        $csvHeaders = [
            "roomcd", "sid", "r_seq", "rd_seq", "tid", "weekdaycd", "start_time", "r_minutes",
            "end_time", "r_count", "curriculumcd", "updtime", "upduser"
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
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(" . config('appconf.upload_file_csv_name_A11') . "：ヘッダ行不正)");
                }
                continue;
            }
            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_A11') . "：データ列数不正)");
            }
            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make(
                // 対象
                $values,
                // バリデーションルール
                ExtRegularDetail::fieldRules('roomcd', ['required'])
                    + ExtRegularDetail::fieldRules('sid', ['required'])
                    + ExtRegularDetail::fieldRules('r_seq', ['required'])
                    + ExtRegularDetail::fieldRules('rd_seq', ['required'])
                    + ExtRegularDetail::fieldRules('tid', ['required'])
                    + ExtRegularDetail::fieldRules('weekdaycd', ['required'])
                    + ExtRegularDetail::fieldRules('start_time', ['required'], '_csv')
                    + ExtRegularDetail::fieldRules('r_minutes', ['required'])
                    + ExtRegularDetail::fieldRules('end_time', ['required'], '_csv')
                    + ExtRegularDetail::fieldRules('r_count', ['required'])
                    + ExtRegularDetail::fieldRules('curriculumcd', ['required'])
                    + ExtRegularDetail::fieldRules('updtime', ['required'], '_csv')
            );

            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('sid')) {
                    $errCol = "生徒No=" . $values['sid'];
                } else if ($validator->errors()->has('roomcd')) {
                    $errCol = "教室コード=" . $values['roomcd'];
                } else if ($validator->errors()->has('r_seq')) {
                    $errCol = "規定情報連番=" . $values['r_seq'];
                } else if ($validator->errors()->has('rd_seq')) {
                    $errCol = "規定情報明細連番=" . $values['rd_seq'];
                } else if ($validator->errors()->has('tid')) {
                    $errCol = "教師No=" . $values['tid'];
                } else if ($validator->errors()->has('weekdaycd')) {
                    $errCol = "曜日コード=" . $values['weekdaycd'];
                } else if ($validator->errors()->has('start_time')) {
                    $errCol = "授業開始時刻=" . $values['start_time'];
                } else if ($validator->errors()->has('r_minutes')) {
                    $errCol = "授業時間数=" . $values['r_minutes'];
                } else if ($validator->errors()->has('end_time')) {
                    $errCol = "授業終了時刻=" . $values['end_time'];
                } else if ($validator->errors()->has('r_count')) {
                    $errCol = "回数=" . $values['r_count'];
                } else if ($validator->errors()->has('curriculumcd')) {
                    $errCol = "教科コード=" . $values['curriculumcd'];
                } else if ($validator->errors()->has('updtime')) {
                    $errCol =  "更新日時=" . $values['updtime'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . config('appconf.upload_file_csv_name_A11')
                    . "：データ項目不正( 生徒No=" . $values['sid'] . ", "
                    . "教室コード=" . $values['roomcd'] . ", "
                    . "規定情報連番=" . $values['r_seq'] . ", "
                    . "規定情報明細連番=" . $values['rd_seq'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }
            // MEMO: saveで登録するため、ここでの日時セット処理は不要
            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // 不要な項目を削除
            unset($values['upduser']);

            // リストに保持しておく
            $datas[] = $values;
        }
        return $datas;
    }

    /**
     * アップロードされたファイルを読み込む（個別講習情報）
     * バリデーションも行う
     *
     * @param string $path ファイルパス
     * @return array csv取込データ
     */
    private function readDataA30($path)
    {

        // CSVのヘッダ項目
        $csvHeaders = [
            "roomcd", "sid", "i_seq", "name", "symbol", "price", "bill_plan", "bill_date", "updtime", "upduser"
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
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(" . config('appconf.upload_file_csv_name_A30') . "：ヘッダ行不正)");
                }
                continue;
            }
            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_A30') . "：データ列数不正)");
            }
            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make(
                // 対象
                $values,
                // バリデーションルール
                ExtExtraIndividual::fieldRules('roomcd', ['required'])
                    + ExtExtraIndividual::fieldRules('sid', ['required'])
                    + ExtExtraIndividual::fieldRules('i_seq', ['required'])
                    + ExtExtraIndividual::fieldRules('name')
                    + ExtExtraIndividual::fieldRules('symbol')
                    + ExtExtraIndividual::fieldRules('price')
                    + ExtExtraIndividual::fieldRules('bill_plan', [], '_csv')
                    + ExtExtraIndividual::fieldRules('bill_date', [], '_csv')
                    + ExtExtraIndividual::fieldRules('updtime', ['required'], '_csv')
            );

            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('sid')) {
                    $errCol = "生徒No=" . $values['sid'];
                } else if ($validator->errors()->has('roomcd')) {
                    $errCol = "教室コード=" . $values['roomcd'];
                } else if ($validator->errors()->has('i_seq')) {
                    $errCol = "個別講習連番=" . $values['i_seq'];
                } else if ($validator->errors()->has('name')) {
                    $errCol = "講習名=" . $values['name'];
                } else if ($validator->errors()->has('symbol')) {
                    $errCol = "表示用シンボル=" . $values['symbol'];
                } else if ($validator->errors()->has('price')) {
                    $errCol = "講習料=" . $values['price'];
                } else if ($validator->errors()->has('bill_plan')) {
                    $errCol = "請求予定日=" . $values['bill_plan'];
                } else if ($validator->errors()->has('bill_date')) {
                    $errCol = "請求日=" . $values['bill_date'];
                } else if ($validator->errors()->has('updtime')) {
                    $errCol =  "更新日時=" . $values['updtime'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . config('appconf.upload_file_csv_name_A30')
                    . "：データ項目不正( 生徒No=" . $values['sid'] . ", "
                    . "教室コード=" . $values['roomcd'] . ", "
                    . "個別講習連番=" . $values['i_seq'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // MEMO: saveで登録するため、ここでの日時セット処理は不要
            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // 不要な項目を削除
            unset($values['upduser']);

            // リストに保持しておく
            $datas[] = $values;
        }
        return $datas;
    }

    /**
     * アップロードされたファイルを読み込む（個別講習情報明細）
     * バリデーションも行う
     *
     * @param string $path ファイルパス
     * @return array csv取込データ
     */
    private function readDataA31($path)
    {

        // CSVのヘッダ項目
        $csvHeaders = [
            "roomcd", "sid", "i_seq", "period_no", "extra_date", "curriculumcd", "start_time", "r_minutes",
            "end_time", "tid", "updtime", "upduser"
        ];
        // CSVのデータをリストで保持
        $datas = [];
        $headers = [];
        // 現在日時を取得
        $now = Carbon::now();
        // 前年度の開始日を取得
        $prevStart = new Carbon($this->dtGetFiscalDate('prev', 'start'));
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
                        . "(" . config('appconf.upload_file_csv_name_A31') . "：ヘッダ行不正)");
                }
                continue;
            }
            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_A31') . "：データ列数不正)");
            }
            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make(
                // 対象
                $values,
                // バリデーションルール
                ExtExtraIndDetail::fieldRules('roomcd', ['required'])
                    + ExtExtraIndDetail::fieldRules('sid', ['required'])
                    + ExtExtraIndDetail::fieldRules('i_seq', ['required'])
                    + ExtExtraIndDetail::fieldRules('period_no', ['required'])
                    + ExtExtraIndDetail::fieldRules('extra_date', ['required'], '_csv')
                    + ExtExtraIndDetail::fieldRules('curriculumcd', ['required'])
                    + ExtExtraIndDetail::fieldRules('start_time', ['required'], '_csv')
                    + ExtExtraIndDetail::fieldRules('r_minutes', ['required'])
                    + ExtExtraIndDetail::fieldRules('end_time', ['required'], '_csv')
                    + ExtExtraIndDetail::fieldRules('tid', ['required'])
                    + ExtExtraIndDetail::fieldRules('updtime', ['required'], '_csv')
            );

            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('sid')) {
                    $errCol = "生徒No=" . $values['sid'];
                } else if ($validator->errors()->has('roomcd')) {
                    $errCol = "教室コード=" . $values['roomcd'];
                } else if ($validator->errors()->has('i_seq')) {
                    $errCol = "個別講習連番=" . $values['i_seq'];
                } else if ($validator->errors()->has('period_no')) {
                    $errCol = "個別講習コマ連番=" . $values['period_no'];
                } else if ($validator->errors()->has('extra_date')) {
                    $errCol = "講習日=" . $values['extra_date'];
                } else if ($validator->errors()->has('curriculumcd')) {
                    $errCol = "教科コード=" . $values['curriculumcd'];
                } else if ($validator->errors()->has('start_time')) {
                    $errCol = "授業開始時刻=" . $values['start_time'];
                } else if ($validator->errors()->has('r_minutes')) {
                    $errCol = "授業時間数=" . $values['r_minutes'];
                } else if ($validator->errors()->has('end_time')) {
                    $errCol = "授業終了時刻=" . $values['end_time'];
                } else if ($validator->errors()->has('tid')) {
                    $errCol = "教師No=" . $values['tid'];
                } else if ($validator->errors()->has('updtime')) {
                    $errCol =  "更新日時=" . $values['updtime'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . config('appconf.upload_file_csv_name_A31')
                    . "：データ項目不正( 生徒No=" . $values['sid'] . ", "
                    . "教室コード=" . $values['roomcd'] . ", "
                    . "個別講習連番=" . $values['i_seq'] . ", "
                    . "個別講習コマ連番=" . $values['period_no'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // 講習日の期間チェック
            if (Carbon::parse($values['extra_date']) < $prevStart) {
                // 前年度より前のデータは取込対象外とする
                continue;
            }

            // MEMO: saveで登録するため、ここでの日時セット処理は不要
            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // 不要な項目を削除
            unset($values['upduser']);

            // リストに保持しておく
            $datas[] = $values;
        }
        return $datas;
    }

    /**
     * アップロードされたファイルを読み込む（家庭教師標準情報）
     * バリデーションも行う
     *
     * @param string $path ファイルパス
     * @return array csv取込データ
     */
    private function readDataA60($path)
    {

        // CSVのヘッダ項目
        $csvHeaders = [
            "roomcd", "sid", "std_seq", "startdate", "enddate", "std_summary", "tuition", "expenses",
            "updtime", "upduser", "rkcd", "kskb", "kaisu", "jkkb", "jikan",
            "listfee", "listfeeit", "kskb_ov", "kaisu_ov", "jkkb_ov", "jikan_ov", "listfee_ov", "listfeeit_ov",
            "opkb", "uchishitei", "ukaisu", "ujikan", "uopkb", "listfee_kitei", "listfeeit_kitei", "additional_flg"
        ];
        // CSVのデータをリストで保持
        $datas = [];
        $headers = [];
        // 現在日時を取得
        $now = Carbon::now();
        // 前年度の開始日を取得
        $prevStart = new Carbon($this->dtGetFiscalDate('prev', 'start'));
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
                        . "(" . config('appconf.upload_file_csv_name_A60') . "：ヘッダ行不正)");
                }
                continue;
            }
            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_A60') . "：データ列数不正)");
            }
            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make(
                // 対象
                $values,
                // バリデーションルール
                ExtHomeTeacherStd::fieldRules('roomcd', ['required'])
                    + ExtHomeTeacherStd::fieldRules('sid', ['required'])
                    + ExtHomeTeacherStd::fieldRules('std_seq', ['required'])
                    + ExtHomeTeacherStd::fieldRules('startdate', ['required'], '_csv')
                    + ExtHomeTeacherStd::fieldRules('enddate', ['required'], '_csv')
                    + ExtHomeTeacherStd::fieldRules('std_summary', ['required'])
                    + ExtHomeTeacherStd::fieldRules('tuition', ['required'])
                    + ExtHomeTeacherStd::fieldRules('expenses', ['required'])
                    + ExtHomeTeacherStd::fieldRules('updtime', ['required'], '_csv')
            );

            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('sid')) {
                    $errCol = "生徒No=" . $values['sid'];
                } else if ($validator->errors()->has('roomcd')) {
                    $errCol = "教室コード=" . $values['roomcd'];
                } else if ($validator->errors()->has('std_seq')) {
                    $errCol = "家庭教師標準連番=" . $values['std_seq'];
                } else if ($validator->errors()->has('startdate')) {
                    $errCol = "開始日=" . $values['startdate'];
                } else if ($validator->errors()->has('enddate')) {
                    $errCol = "終了日=" . $values['enddate'];
                } else if ($validator->errors()->has('std_summary')) {
                    $errCol = "標準内容概略=" . $values['std_summary'];
                } else if ($validator->errors()->has('tuition')) {
                    $errCol = "月額授業料=" . $values['tuition'];
                } else if ($validator->errors()->has('expenses')) {
                    $errCol = "月額交通費=" . $values['expenses'];
                } else if ($validator->errors()->has('updtime')) {
                    $errCol =  "更新日時=" . $values['updtime'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . config('appconf.upload_file_csv_name_A60')
                    . "：データ項目不正( 生徒No=" . $values['sid'] . ", "
                    . "教室コード=" . $values['roomcd'] . ", "
                    . "家庭教師標準連番=" . $values['std_seq'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // 開始日・終了日（契約期間）のチェック
            if (Carbon::parse($values['startdate']) < $prevStart && Carbon::parse($values['enddate']) < $prevStart) {
                // 前年度より前のデータは取込対象外とする
                continue;
            }

            // MEMO: saveで登録するため、ここでの日時セット処理は不要
            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // 不要な項目を削除
            unset($values['upduser']);
            unset($values['rkcd']);
            unset($values['kskb']);
            unset($values['kaisu']);
            unset($values['jkkb']);
            unset($values['jikan']);
            unset($values['listfee']);
            unset($values['listfeeit']);
            unset($values['kskb_ov']);
            unset($values['kaisu_ov']);
            unset($values['jkkb_ov']);
            unset($values['jikan_ov']);
            unset($values['listfee_ov']);
            unset($values['listfeeit_ov']);
            unset($values['opkb']);
            unset($values['uchishitei']);
            unset($values['ukaisu']);
            unset($values['ujikan']);
            unset($values['uopkb']);
            unset($values['listfee_kitei']);
            unset($values['listfeeit_kitei']);
            unset($values['additional_flg']);

            // リストに保持しておく
            $datas[] = $values;
        }
        return $datas;
    }

    /**
     * アップロードされたファイルを読み込む（家庭教師標準情報詳細）
     * バリデーションも行う
     *
     * @param string $path ファイルパス
     * @return array csv取込データ
     */
    private function readDataA61($path)
    {

        // CSVのヘッダ項目
        $csvHeaders = [
            "roomcd", "sid", "std_seq", "std_dtl_seq", "tid", "std_minutes", "std_count", "hour_payment",
            "roundtrip_expenses", "rem_count", "updtime", "upduser"
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
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(" . config('appconf.upload_file_csv_name_A61') . "：ヘッダ行不正)");
                }
                continue;
            }
            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_A61') . "：データ列数不正)");
            }
            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make(
                // 対象
                $values,
                // バリデーションルール
                ExtHomeTeacherStdDetail::fieldRules('roomcd', ['required'])
                    + ExtHomeTeacherStdDetail::fieldRules('sid', ['required'])
                    + ExtHomeTeacherStdDetail::fieldRules('std_seq', ['required'])
                    + ExtHomeTeacherStdDetail::fieldRules('std_dtl_seq', ['required'])
                    + ExtHomeTeacherStdDetail::fieldRules('tid', ['required'])
                    + ExtHomeTeacherStdDetail::fieldRules('std_minutes', ['required'])
                    + ExtHomeTeacherStdDetail::fieldRules('std_count', ['required'])
                    + ExtHomeTeacherStdDetail::fieldRules('hour_payment', ['required'])
                    + ExtHomeTeacherStdDetail::fieldRules('roundtrip_expenses', ['required'])
                    + ExtHomeTeacherStdDetail::fieldRules('rem_count', ['required'])
                    + ExtHomeTeacherStdDetail::fieldRules('updtime', ['required'], '_csv')
            );

            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('sid')) {
                    $errCol = "生徒No=" . $values['sid'];
                } else if ($validator->errors()->has('roomcd')) {
                    $errCol = "教室コード=" . $values['roomcd'];
                } else if ($validator->errors()->has('std_seq')) {
                    $errCol = "家庭教師標準連番=" . $values['std_seq'];
                } else if ($validator->errors()->has('std_dtl_seq')) {
                    $errCol = "家庭教師標準明細枝番=" . $values['std_dtl_seq'];
                } else if ($validator->errors()->has('tid')) {
                    $errCol = "教師No=" . $values['tid'];
                } else if ($validator->errors()->has('std_minutes')) {
                    $errCol = "授業時間=" . $values['std_minutes'];
                } else if ($validator->errors()->has('std_count')) {
                    $errCol = "回数=" . $values['std_count'];
                } else if ($validator->errors()->has('hour_payment')) {
                    $errCol = "時給=" . $values['hour_payment'];
                } else if ($validator->errors()->has('roundtrip_expenses')) {
                    $errCol = "往復交通費=" . $values['roundtrip_expenses'];
                } else if ($validator->errors()->has('rem_count')) {
                    $errCol = "残回数=" . $values['rem_count'];
                } else if ($validator->errors()->has('updtime')) {
                    $errCol =  "更新日時=" . $values['updtime'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . config('appconf.upload_file_csv_name_A61')
                    . "：データ項目不正( 生徒No=" . $values['sid'] . ", "
                    . "教室コード=" . $values['roomcd'] . ", "
                    . "家庭教師標準連番=" . $values['std_seq'] . ", "
                    . "家庭教師標準明細枝番=" . $values['std_dtl_seq'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // MEMO: saveで登録するため、ここでの日時セット処理は不要
            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // 不要な項目を削除
            unset($values['upduser']);

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

            // ファイル名の先頭をチェック（以下のいずれか）
            // 想定１：入会者情報_20201124114040.zip
            // 想定２：コース変更情報_20201124114040.zip
            // 想定３：短期講習申込_20201124114040.zip
            $fileNameEntr = config('appconf.upload_file_name_member_import_enter');
            $fileNameCrse = config('appconf.upload_file_name_member_import_course');
            $fileNameIndi = config('appconf.upload_file_name_member_import_individual');
            if (!preg_match('/^(' . $fileNameEntr . '|'  . $fileNameCrse . '|' . $fileNameIndi . ')[0-9]{14}.zip$/', $value)) {
                return $fail(Lang::get('validation.invalid_file'));
            }
        };

        //-----------------------------
        // ファイルアップロード
        //-----------------------------

        // ファイルアップロードの必須チェック
        $rules += ['upload_file_member' => ['required', $validationFileName]];

        // ファイルのタイプのチェック(「file_項目名」の用にチェックする)
        $rules += ['file_upload_file_member' => [
            // ファイル
            'file',
            // 拡張子
            'mimes:zip',
            // Laravelが判定したmimetypes
            'mimetypes:application/zip',
        ]];

        return $rules;
    }

    /**
     * 生徒情報削除(A04/A10/A11/A30/A31/A60/A61/T01)
     * （物理削除）
     *
     * @param array $datas
     * @return void
     */
    private function deleteStudentData($datas)
    {
        // 生徒基本情報csvより取込対象のsidを取得
        $sidList = [];
        foreach ($datas as $data) {
            array_push($sidList,  $data['sid']);
        }
        $sidList = array_unique($sidList);

        //------------------------------------
        // 教室情報テーブルの削除処理（A04）
        //------------------------------------
        // 取込対象sidのデータをDelete（物理削除）
        ExtRoom::whereIn('sid', $sidList)
            ->forceDelete();

        //------------------------------------
        // 規定情報テーブルの削除処理（A10）
        //------------------------------------
        // 取込対象sidのデータをDelete（物理削除）
        ExtRegular::whereIn('sid', $sidList)
            ->forceDelete();

        //------------------------------------
        // 規定情報明細テーブルの削除処理（A11）
        //------------------------------------
        // 取込対象sidのデータをDelete（物理削除）
        ExtRegularDetail::whereIn('sid', $sidList)
            ->forceDelete();

        //------------------------------------
        // 個別講習情報テーブルの削除処理（A30）
        //------------------------------------
        // 取込対象sidのデータをDelete（物理削除）
        ExtExtraIndividual::whereIn('sid', $sidList)
            ->forceDelete();

        //------------------------------------
        // 個別講習情報明細テーブルの削除処理（A31）
        //------------------------------------
        // 取込対象sidのデータをDelete（物理削除）
        ExtExtraIndDetail::whereIn('sid', $sidList)
            ->forceDelete();

        //------------------------------------
        // 家庭教師標準情報テーブルの削除処理（A60）
        //------------------------------------
        // 取込対象sidのデータをDelete（物理削除）
        ExtHomeTeacherStd::whereIn('sid', $sidList)
            ->forceDelete();

        //------------------------------------
        // 家庭教師標準詳細テーブルの削除処理（A61）
        //------------------------------------
        // 取込対象sidのデータをDelete（物理削除）
        ExtHomeTeacherStdDetail::whereIn('sid', $sidList)
            ->forceDelete();

        //------------------------------------
        // スケジュール情報テーブルの削除処理（T01）
        //------------------------------------
        // 取込対象sidのデータをDelete（物理削除）
        ExtSchedule::whereIn('sid', $sidList)
            ->forceDelete();

        return;
    }

    /**
     * 生徒基本情報テーブルデータ登録・アカウントテーブル登録
     * （新規登録または更新）
     *
     * @param array $datas
     * @return void
     */
    private function registA05($datas)
    {
        // 1行ずつ取得
        foreach ($datas as $data) {
            //------------------------------------
            // 生徒基本情報テーブルの登録・更新処理
            //------------------------------------
            // レコードが存在するかチェック(キーを指定)
            $extStudent = ExtStudentKihon::firstOrNew(['sid' => $data['sid']]);
            $extStudent->sid = $data['sid'];
            $extStudent->name = $data['name'];
            $extStudent->cls_cd = $data['cls_cd'];
            $extStudent->mailaddress1 = $data['mailaddress1'];
            $extStudent->enter_date = $data['enter_date'];
            $extStudent->disp_flg = $data['disp_flg'];
            $extStudent->updtime = $data['updtime'];
            $extStudent->save();

            //------------------------------------
            // アカウントテーブルの登録・更新処理
            //------------------------------------
            // 論理削除されたアカウントに対象sidが存在するかチェック
            $reAccount = Account::onlyTrashed()
                ->where('account_id', $data['sid'])
                ->where('account_type', AppConst::CODE_MASTER_7_1)
                ->first();

            if ($reAccount !== null) {
                // 対象sidが存在する場合は復元する（再入会対応）
                // TODO: パスワードはリセットでよいか？
                $reAccount->restore();
                $reAccount->account_id = $data['sid'];
                $reAccount->account_type = AppConst::CODE_MASTER_7_1;
                $reAccount->email = $data['mailaddress1'];
                // 初期パスワードのハッシュ化(適当な文字列で生成)
                // 宣言する→use Illuminate\Support\Facades\Hash;
                $reAccount->password = Hash::make(md5(time() . rand()));
                $reAccount->password_reset = AppConst::ACCOUNT_PWRESET_0;
                $reAccount->save();
            } else {
                // レコードが存在するかチェック(キーを指定)
                $account = Account::firstOrNew(['account_id' => $data['sid'], 'account_type' => AppConst::CODE_MASTER_7_1]);
                $account->account_id = $data['sid'];
                $account->account_type = AppConst::CODE_MASTER_7_1;
                $account->email = $data['mailaddress1'];
                if (!$account->exists) {
                    // 登録時のみ
                    // 初期パスワードのハッシュ化(適当な文字列で生成)
                    // 宣言する→use Illuminate\Support\Facades\Hash;
                    $account->password = Hash::make(md5(time() . rand()));
                    $account->password_reset = AppConst::ACCOUNT_PWRESET_0;
                }
                $account->save();
            }
        }

        return;
    }

    /**
     * 教室情報テーブルデータ登録
     * （物理削除/登録）
     *
     * @param array $datas
     * @param bool $delSkip 削除処理スキップ時はtrue
     * @return void
     */
    private function registA04($datas, $delSkip = false)
    {
        //------------------------------------
        // 教室情報テーブルの登録処理（Delete/Insert）
        //------------------------------------
        if (!$delSkip) {
            // 取込対象のsidを取得
            $sidList = [];
            foreach ($datas as $data) {
                array_push($sidList,  $data['sid']);
            }
            $sidList = array_unique($sidList);

            // 取込対象sidのデータをDelete(物理削除）
            ExtRoom::whereIn('sid', $sidList)
                ->forceDelete();
        }

        // 1行ずつ取得
        foreach ($datas as $data) {

            // 生徒基本情報に対象sidが存在しなければエラーとする
            ExtStudentKihon::where('sid', $data['sid'])
                ->firstOrFail();

            // 教室情報テーブルの登録（Insert）
            $extRoom = new ExtRoom;
            $extRoom->sid = $data['sid'];
            $extRoom->roomcd = $data['roomcd'];
            $extRoom->updtime = $data['updtime'];
            $extRoom->save();
        }

        return;
    }

    /**
     * 規定情報テーブルデータ登録
     * （物理削除/登録）
     *
     * @param array $datas
     * @param bool $delSkip 削除処理スキップ時はtrue
     * @return void
     */
    private function registA10($datas, $delSkip = false)
    {
        //------------------------------------
        // 規定情報テーブルの登録処理（Delete/Insert）
        //------------------------------------
        if (!$delSkip) {
            // 取込対象のsidを取得
            $sidList = [];
            foreach ($datas as $data) {
                array_push($sidList,  $data['sid']);
            }
            $sidList = array_unique($sidList);

            // 取込対象sidのデータをDelete(物理削除）
            ExtRegular::whereIn('sid', $sidList)
                ->forceDelete();
        }

        // 1行ずつ取得
        foreach ($datas as $data) {

            // 生徒基本情報に対象sidが存在しなければエラーとする
            ExtStudentKihon::where('sid', $data['sid'])
                ->firstOrFail();

            // MEMO: 教室情報との整合性チェックは行わないものとする

            // 規定情報テーブルの登録（Insert）
            $extRegular = new ExtRegular;
            $extRegular->roomcd = $data['roomcd'];
            $extRegular->sid = $data['sid'];
            $extRegular->r_seq = $data['r_seq'];
            $extRegular->startdate = $data['startdate'];
            $extRegular->enddate = $data['enddate'];
            $extRegular->regular_summary = $data['regular_summary'];
            $extRegular->tuition = $data['tuition'];
            $extRegular->base_tuition = $data['base_tuition'];
            $extRegular->base_time = $data['base_time'];
            $extRegular->updtime = $data['updtime'];
            $extRegular->save();
        }

        return;
    }

    /**
     * 規定情報明細テーブルデータ登録
     * （物理削除/登録）
     *
     * @param array $datas
     * @param bool $delSkip 削除処理スキップ時はtrue
     * @return void
     */
    private function registA11($datas, $delSkip = false)
    {
        //------------------------------------
        // 規定情報明細テーブルの登録処理（Delete/Insert）
        //------------------------------------
        if (!$delSkip) {
            // 取込対象のsidを取得
            $sidList = [];
            foreach ($datas as $data) {
                array_push($sidList,  $data['sid']);
            }
            $sidList = array_unique($sidList);

            // 取込対象sidのデータをDelete(物理削除）
            ExtRegularDetail::whereIn('sid', $sidList)
                ->forceDelete();
        }

        // 1行ずつ取得
        foreach ($datas as $data) {

            // 規定情報に明細の親となるデータが存在しなければ取込対象外とする
            $exists = ExtRegular::where('roomcd', $data['roomcd'])
                ->where('sid',  $data['sid'])
                ->where('r_seq',  $data['r_seq'])
                ->exists();

            if (!$exists) continue;

            // 規定情報明細テーブルの登録（Insert）
            $extExtRegDatail = new ExtRegularDetail;
            $extExtRegDatail->roomcd = $data['roomcd'];
            $extExtRegDatail->sid = $data['sid'];
            $extExtRegDatail->r_seq = $data['r_seq'];
            $extExtRegDatail->rd_seq = $data['rd_seq'];
            $extExtRegDatail->tid = $data['tid'];
            $extExtRegDatail->weekdaycd = $data['weekdaycd'];
            $extExtRegDatail->start_time = $data['start_time'];
            $extExtRegDatail->r_minutes = $data['r_minutes'];
            $extExtRegDatail->end_time = $data['end_time'];
            $extExtRegDatail->r_count = $data['r_count'];
            $extExtRegDatail->curriculumcd = $data['curriculumcd'];
            $extExtRegDatail->updtime = $data['updtime'];
            $extExtRegDatail->save();
        }

        return;
    }

    /**
     * 個別講習情報テーブルデータ登録
     * （物理削除/登録）
     *
     * @param array $datasA30 個別講習情報
     * @param array $datasA31 個別講習情報明細
     * @param bool $delSkip 削除処理スキップ時はtrue
     * @return void
     */
    private function registA30($datasA30, $datasA31, $delSkip = false)
    {
        //------------------------------------
        // 個別講習情報テーブルの登録処理（Delete/Insert）
        //------------------------------------
        if (!$delSkip) {
            // 取込対象のsidを取得
            $sidList = [];
            foreach ($datasA30 as $data) {
                array_push($sidList,  $data['sid']);
            }
            $sidList = array_unique($sidList);

            // 取込対象sidのデータをDelete(物理削除）
            ExtExtraIndividual::whereIn('sid', $sidList)
                ->forceDelete();
        }

        // 1行ずつ取得
        foreach ($datasA30 as $data) {

            // 生徒基本情報に対象sidが存在しなければエラーとする
            ExtStudentKihon::where('sid', $data['sid'])
                ->firstOrFail();

            // MEMO: 教室情報との整合性チェックは行わないものとする

            // 個別講習情報明細（取込データ）とのマッチング
            // 個別講習情報明細に紐づくデータがなければ取込対象外とする
            // （個別講習情報明細の「講習日」で取込対象外データの除外を行っているため）
            $regFlg = false;
            foreach ($datasA31 as $dataA31) {
                if (
                    $data['roomcd'] === $dataA31['roomcd']
                    && $data['sid'] === $dataA31['sid']
                    && $data['i_seq'] === $dataA31['i_seq']
                ) {
                    $regFlg = true;
                    break;
                }
            }
            if (!$regFlg) continue;

            // 個別講習情報テーブルの登録（Insert）
            $extExtInvidual = new ExtExtraIndividual;
            $extExtInvidual->roomcd = $data['roomcd'];
            $extExtInvidual->sid = $data['sid'];
            $extExtInvidual->i_seq = $data['i_seq'];
            $extExtInvidual->name = $data['name'];
            $extExtInvidual->symbol = $data['symbol'];
            $extExtInvidual->price = $data['price'];
            $extExtInvidual->bill_plan = $data['bill_plan'];
            $extExtInvidual->bill_date = $data['bill_date'];
            $extExtInvidual->updtime = $data['updtime'];
            $extExtInvidual->save();
        }

        return;
    }

    /**
     * 個別講習情報明細テーブルデータ登録
     * （物理削除/登録）
     *
     * @param array $datas
     * @param bool $delSkip 削除処理スキップ時はtrue
     * @return void
     */
    private function registA31($datas, $delSkip = false)
    {
        //------------------------------------
        // 個別講習情報明細テーブルの登録処理（Delete/Insert）
        //------------------------------------
        if (!$delSkip) {
            // 取込対象のsidを取得
            $sidList = [];
            foreach ($datas as $data) {
                array_push($sidList,  $data['sid']);
            }
            $sidList = array_unique($sidList);

            // 取込対象sidのデータをDelete(物理削除）
            ExtExtraIndDetail::whereIn('sid', $sidList)
                ->forceDelete();
        }

        // 1行ずつ取得
        foreach ($datas as $data) {

            // 個別講習情報に明細の親となるデータが存在しなければエラーとする
            ExtExtraIndividual::where('roomcd', $data['roomcd'])
                ->where('sid',  $data['sid'])
                ->where('i_seq',  $data['i_seq'])
                ->firstOrFail();

            // 個別講習情報テーブルの登録（Insert）
            $extExtIndiDatail = new ExtExtraIndDetail;
            $extExtIndiDatail->roomcd = $data['roomcd'];
            $extExtIndiDatail->sid = $data['sid'];
            $extExtIndiDatail->i_seq = $data['i_seq'];
            $extExtIndiDatail->period_no = $data['period_no'];
            $extExtIndiDatail->extra_date = $data['extra_date'];
            $extExtIndiDatail->curriculumcd = $data['curriculumcd'];
            $extExtIndiDatail->start_time = $data['start_time'];
            $extExtIndiDatail->r_minutes = $data['r_minutes'];
            $extExtIndiDatail->end_time = $data['end_time'];
            $extExtIndiDatail->tid = $data['tid'];
            $extExtIndiDatail->updtime = $data['updtime'];
            $extExtIndiDatail->save();
        }

        return;
    }

    /**
     * 家庭教師標準情報テーブルデータ登録
     * （物理削除/登録）
     *
     * @param array $datas
     * @param bool $delSkip 削除処理スキップ時はtrue
     * @return void
     */
    private function registA60($datas, $delSkip = false)
    {
        //------------------------------------
        // 家庭教師標準情報テーブルの登録処理（Delete/Insert）
        //------------------------------------
        if (!$delSkip) {
            // 取込対象のsidを取得
            $sidList = [];
            foreach ($datas as $data) {
                array_push($sidList,  $data['sid']);
            }
            $sidList = array_unique($sidList);

            // 取込対象sidのデータをDelete(物理削除）
            ExtHomeTeacherStd::whereIn('sid', $sidList)
                ->forceDelete();
        }

        // 1行ずつ取得
        foreach ($datas as $data) {

            // 生徒基本情報に対象sidが存在しなければエラーとする
            ExtStudentKihon::where('sid', $data['sid'])
                ->firstOrFail();

            // MEMO: 教室情報との整合性チェックは行わないものとする

            // 個別講習情報テーブルの登録（Insert）
            $extHomeTeacher = new ExtHomeTeacherStd;
            $extHomeTeacher->roomcd = $data['roomcd'];
            $extHomeTeacher->sid = $data['sid'];
            $extHomeTeacher->std_seq = $data['std_seq'];
            $extHomeTeacher->startdate = $data['startdate'];
            $extHomeTeacher->enddate = $data['enddate'];
            $extHomeTeacher->std_summary = $data['std_summary'];
            $extHomeTeacher->tuition = $data['tuition'];
            $extHomeTeacher->expenses = $data['expenses'];
            $extHomeTeacher->updtime = $data['updtime'];
            $extHomeTeacher->save();
        }

        return;
    }

    /**
     * 家庭教師標準詳細テーブルデータ登録
     * （物理削除/登録）
     *
     * @param array $datas
     * @param bool $delSkip 削除処理スキップ時はtrue
     * @return void
     */
    private function registA61($datas, $delSkip = false)
    {
        //------------------------------------
        // 家庭教師標準詳細テーブルの登録処理（Delete/Insert）
        //------------------------------------
        if (!$delSkip) {
            // 取込対象のsidを取得
            $sidList = [];
            foreach ($datas as $data) {
                array_push($sidList,  $data['sid']);
            }
            $sidList = array_unique($sidList);

            // 取込対象sidのデータをDelete(物理削除）
            ExtHomeTeacherStdDetail::whereIn('sid', $sidList)
                ->forceDelete();
        }

        // 1行ずつ取得
        foreach ($datas as $data) {

            // 家庭教師標準情報に明細の親となるデータが存在しなければ取込対象外とする
            $exists = ExtHomeTeacherStd::where('roomcd', $data['roomcd'])
                ->where('sid',  $data['sid'])
                ->where('std_seq',  $data['std_seq'])
                ->exists();

            if (!$exists) continue;

            // 家庭教師標準明細テーブルの登録（Insert）
            $extHomeDatail = new ExtHomeTeacherStdDetail;
            $extHomeDatail->roomcd = $data['roomcd'];
            $extHomeDatail->sid = $data['sid'];
            $extHomeDatail->std_seq = $data['std_seq'];
            $extHomeDatail->std_dtl_seq = $data['std_dtl_seq'];
            $extHomeDatail->tid = $data['tid'];
            $extHomeDatail->std_minutes = $data['std_minutes'];
            $extHomeDatail->std_count = $data['std_count'];
            $extHomeDatail->hour_payment = $data['hour_payment'];
            $extHomeDatail->roundtrip_expenses = $data['roundtrip_expenses'];
            $extHomeDatail->rem_count = $data['rem_count'];
            $extHomeDatail->updtime = $data['updtime'];
            $extHomeDatail->save();
        }

        return;
    }

    /**
     * 教師関連情報テーブルデータ登録
     * （物理削除/登録）
     *
     * @param array $datas
     * @return void
     */
    private function registTutorRelate($datas)
    {

        $sidList = [];
        if ($datas['A11'] !== []) {
            // 規定情報明細データからsidを抽出する
            foreach ($datas['A11'] as $data) {
                array_push($sidList,  $data['sid']);
            }
        }
        if ($datas['A31'] !== []) {
            // 個別講習情報明細データからsidを抽出する
            foreach ($datas['A31'] as $data) {
                array_push($sidList,  $data['sid']);
            }
        }
        if ($datas['A61'] !== []) {
            // 家庭教師標準情報詳細データからsidを抽出する
            foreach ($datas['A61'] as $data) {
                array_push($sidList,  $data['sid']);
            }
        }
        $sidList = array_unique($sidList);

        // 規定情報明細テーブルから更新対象sidのデータを取得する
        $queryRegular = ExtRegularDetail::query()
            ->select('roomcd', 'sid', 'tid')
            ->whereIn('sid', $sidList)
            // アカウントテーブルとJOIN（削除教師非表示対応）
            ->sdJoin(Account::class, function ($join) {
                $join->on('ext_regular_detail.tid', '=', 'account.account_id')
                    ->where('account.account_type', AppConst::CODE_MASTER_7_2);
            });

        // 個別講習情報明細テーブルから更新対象sidのデータを取得する
        $queryExtIndi = ExtExtraIndDetail::select('roomcd', 'sid', 'tid')
            ->whereIn('sid', $sidList)
            // アカウントテーブルとJOIN（削除教師非表示対応）
            ->sdJoin(Account::class, function ($join) {
                $join->on('ext_extra_ind_detail.tid', '=', 'account.account_id')
                    ->where('account.account_type', AppConst::CODE_MASTER_7_2);
            });

        // 家庭教師情報詳細テーブルから更新対象sidのデータを取得する
        $queryExtHome = ExtHomeTeacherStdDetail::select('roomcd', 'sid', 'tid')
            ->whereIn('sid', $sidList)
            // アカウントテーブルとJOIN（削除教師非表示対応）
            ->sdJoin(Account::class, function ($join) {
                $join->on('ext_home_teacher_std_detail.tid', '=', 'account.account_id')
                    ->where('account.account_type', AppConst::CODE_MASTER_7_2);
            });

        // 3つのqueryをUNION
        $tutorRelates = $queryRegular
            ->union($queryExtIndi)
            ->union($queryExtHome)
            ->get();

        //------------------------------------
        // 教師関連情報テーブルの登録処理(Delete/Insert)
        //------------------------------------
        // 教師関連情報テーブルより、更新対象sidのデータを物理削除する
        TutorRelate::whereIn('sid', $sidList)
            ->forceDelete();

        // 1レコードずつ登録
        foreach ($tutorRelates as $data) {
            $tutorRelate = new TutorRelate;
            $tutorRelate->roomcd = $data['roomcd'];
            $tutorRelate->sid = $data['sid'];
            $tutorRelate->tid = $data['tid'];
            $tutorRelate->save();
        }

        return;
    }
}
