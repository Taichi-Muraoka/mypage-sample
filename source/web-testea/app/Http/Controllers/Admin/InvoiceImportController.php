<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ReadDataValidateException;
use App\Consts\AppConst;
use Carbon\Carbon;
use App\Models\InvoiceImport;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\CodeMaster;
use App\Models\ExtGenericMaster;
use Illuminate\Support\Facades\Log;

/**
 * 請求情報取込 - コントローラ
 */
class InvoiceImportController extends Controller
{
    /**
     * 一括Insert chunk数
     */
    const INSERT_CHUNK = 100;

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

        return view('pages.admin.invoice_import');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {

        // 当月を取得
        $present_month = date('Y-m') . '-01';

        // 請求情報取込を取得
        $query = InvoiceImport::query();
        $invoice_imports = $query
            ->select(
                'invoice_date',
                'import_state',
                'name AS state_name'
            )
            // 取り込み状態
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('invoice_import.import_state', '=', 'code_master.code')
                    ->where('code_master.data_type', AppConst::CODE_MASTER_20);
            })
            ->where('invoice_date', '<=', $present_month)
            ->orderBy('invoice_date', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $invoice_imports, function ($items) {
            // IDは年月
            foreach ($items as $item) {
                $item['id'] = $item->invoice_date->format('Ym');
            }
            return $items;
        });
    }

    //==========================
    // 取込
    //==========================

    /**
     * 取込画面
     *
     * @param date $invoiceDate 年月（YYYYMM）
     * @return view
     */
    public function import($invoiceDate)
    {

        // IDのバリデーション
        $this->validateIds($invoiceDate);

        // dateの形式のバリデーションと変換
        $idDate = $this->fmYmToDate($invoiceDate);

        // 当月を取得
        $present_month = date('Y-m') . '-01';

        // 取込可能な年月か確認する
        if ($present_month < $idDate) {
            $this->illegalResponseErr();
        }

        // 請求情報取込を取得
        $invoice_import = InvoiceImport::select('invoice_date')
            ->where('invoice_date', '=', $idDate)
            ->firstOrFail();

        return view('pages.admin.invoice_import-import', [
            'rules' => $this->rulesForInput(),
            'invoice_import' => $invoice_import,
            'editData' => [
                'invoiceDate' => $invoiceDate
            ]
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
        $this->fileUploadSetVal($request, 'upload_file_kobetsu');
        $this->fileUploadSetVal($request, 'upload_file_katei');

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // アップロード先(アップ先は用途ごとに分ける)
        $uploadDir = config('appconf.upload_dir_invoice_import') . date("YmdHis");

        // アップロードファイルの保存
        $path_kobetsu = $this->fileUploadSave($request, $uploadDir, 'upload_file_kobetsu');
        $path_katei = $this->fileUploadSave($request, $uploadDir, 'upload_file_katei');

        $invoiceDate = $request->input('invoiceDate');

        // dateの形式のバリデーションと変換
        $idDate = $this->fmYmToDate($invoiceDate);

        // 当月を取得
        $present_month = date('Y-m') . '-01';

        // 取込可能な年月か確認する
        if ($present_month < $idDate) {
            $this->illegalResponseErr();
        }

        $datas_kobetsu = [];
        $datas_katei = [];
        try {
            // CSVデータの読み込み 保存用データを返すのでtrue
            $datas_kobetsu = $this->readData($path_kobetsu, $invoiceDate, 'kobetsu', true);
            $datas_katei = $this->readData($path_katei, $invoiceDate, 'katei', true);
        } catch (ReadDataValidateException  $e) {
            // 通常は事前にバリデーションするのでここはありえないのでエラーとする
            return $this->responseErr();
        }

        try {

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($datas_kobetsu, $datas_katei, $idDate) {

                // 請求情報明細から対象月のレコードを物理削除する
                InvoiceDetail::where('invoice_date', '=', $idDate)
                    ->forceDelete();

                // 請求情報から対象月のレコードを物理削除する
                Invoice::where('invoice_date', '=', $idDate)
                    ->forceDelete();

                // 請求情報に取込データを挿入する
                // MEMO: 一括Insertは、INSERT_CHUNK数 ずつ分割して行う
                $invoice = new Invoice;
                // 個別教室情報
                $kobetsuChunks = array_chunk($datas_kobetsu['invoices'], self::INSERT_CHUNK);
                foreach ($kobetsuChunks as $kobetsuChunk) {
                    $invoice->insert($kobetsuChunk);
                }
                // 家庭教師情報
                $kateiChunks = array_chunk($datas_katei['invoices'], self::INSERT_CHUNK);
                foreach ($kateiChunks as $kateiChunk) {
                    $invoice->insert($kateiChunk);
                }

                // 請求情報明細に取込データを挿入する
                // MEMO: 一括Insertは、INSERT_CHUNK * 明細数 ずつ分割して行う
                $invoiceDatail = new InvoiceDetail;
                // 個別教室情報
                // 請求情報明細数をカウント（個別教室）
                $detailCnt = CodeMaster::where('data_type', '=', AppConst::CODE_MASTER_18)
                    ->where('sub_code', '=', AppConst::CODE_MASTER_18_SUB_1)
                    ->count();

                $kobetsuChunks = array_chunk($datas_kobetsu['invoice_details'], self::INSERT_CHUNK * $detailCnt);
                foreach ($kobetsuChunks as $kobetsuChunk) {
                    $invoiceDatail->insert($kobetsuChunk);
                }
                // 家庭教師情報
                // 請求情報明細数をカウント（家庭教師）
                $detailCnt = CodeMaster::where('data_type', '=', AppConst::CODE_MASTER_18)
                    ->where('sub_code', '=', AppConst::CODE_MASTER_18_SUB_2)
                    ->count();

                $kateiChunks = array_chunk($datas_katei['invoice_details'], self::INSERT_CHUNK * $detailCnt);
                foreach ($kateiChunks as $kateiChunk) {
                    $invoiceDatail->insert($kateiChunk);
                }

                // 該当する請求情報取込を更新する
                $salaryImport = InvoiceImport::where('invoice_date', '=', $idDate)->firstOrFail();
                $salaryImport->import_state = AppConst::CODE_MASTER_20_1;
                $salaryImport->import_date = $datas_kobetsu['import_date'];
                $salaryImport->save();
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
        $this->fileUploadSetVal($request, 'upload_file_kobetsu');
        $this->fileUploadSetVal($request, 'upload_file_katei');

        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInput());

        // エラーがあれば返却
        if ($validator->fails()) {
            return $validator->errors();
        }

        // invoiceDate(取込月)も取得しておく
        $invoiceDate = $request->input('invoiceDate');

        $errors = [];

        // 個別教室
        // CSVのパスを取得(upload直後のtmpのパス)
        $path_kobetsu = $this->fileUploadRealPath($request, 'upload_file_kobetsu');

        try {
            // CSVの中身の読み込みとバリデーション
            $this->readData($path_kobetsu, $invoiceDate, 'kobetsu');
        } catch (ReadDataValidateException  $e) {
            // ファイルのバリデーションエラーをキャッチしておく
            $errors += ['upload_file_kobetsu' => [$e->getMessage()]];
        }

        // 家庭教師
        // CSVのパスを取得(upload直後のtmpのパス)
        $path_katei = $this->fileUploadRealPath($request, 'upload_file_katei');

        try {
            // CSVの中身の読み込みとバリデーション
            $this->readData($path_katei, $invoiceDate, 'katei');
        } catch (ReadDataValidateException  $e) {
            // ファイルのバリデーションエラーをキャッチしておく
            $errors += ['upload_file_katei' => [$e->getMessage()]];
        }

        // 両方の中身をチェックしてから返却
        if ($errors) {
            return $errors;
        }

        return;
    }

    /**
     * アップロードされたファイルを読み込む
     * バリデーションも行う
     *
     * @param $path
     * @param $invoiceDate (YYYYMM) 取込月
     * @param $type 'kobetsu' or 'katei'
     * @param $create バリデーションだけの時はfalse
     * @return array
     */
    private function readData($path, $invoiceDate, $type, $create = false)
    {

        // 取込対象月
        $date = $this->fmYmToDate($invoiceDate);

        // return
        $datas = [];

        // DB保存用配列
        $invoices = [];
        $invoice_details = [];

        // 現在日時を取得
        $now = Carbon::now();

        // 請求方法のチェック用
        $payType = ExtGenericMaster::select('code', 'name2')
            ->where('codecls', '=', AppConst::EXT_GENERIC_MASTER_102)
            ->get()
            ->keyBy('name2');

        // 個別教室の場合
        if ($type === 'kobetsu') {

            // バリデーション項目名用リスト
            $invoice_lists = [];

            // コードマスタから項目名を取得
            $invoiceCodes = CodeMaster::select(
                'name',
                'order_code',
                'gen_item1'
            )
                ->where('data_type', '=', AppConst::CODE_MASTER_18)
                ->where('sub_code', '=', AppConst::CODE_MASTER_18_SUB_1)
                ->orderBy('order_code', 'asc')
                ->get()
                ->keyBy('gen_item1');

            foreach ($invoiceCodes as $invoice) {
                $invoice_list = [
                    'order_code' => $invoice['order_code'],
                    'name' => $invoice['name']
                ];
                array_push($invoice_lists, $invoice_list);
            }

            // array_combine用 new
            $dataHeaders = [
                '引落年月',
                '確定',
                '教室',
                '生徒名',
                '規定',
                $invoiceCodes['5']['name'],
                $invoiceCodes['6']['name'],
                $invoiceCodes['7']['name'],
                $invoiceCodes['8']['name'],
                $invoiceCodes['9']['name'],
                $invoiceCodes['10']['name'],
                $invoiceCodes['11']['name'],
                '合計',
                '補足説明',
                '請求方法',
                '入金日',
                '生徒No',
                '支払期日',
                '発行日',
                '備考内容'
            ];

            // CSV読み込み
            $file = $this->readCsv($path, "sjis");

            // 1行ずつ取得
            foreach ($file as $i => $line) {

                // 最初の2行は読み飛ばす
                if ($i < 2) {
                    continue;
                }

                // [バリデーション] データ行の列の数のチェック
                // データ行の末尾に余計なカンマが入っていても許容する
                if (count($line) < count($dataHeaders)) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(データ列数不正)");
                }

                // 項目名を編集したdataHeadersをもとに、値をセットしたオブジェクトを生成
                array_splice($line, count($dataHeaders));
                $values = array_combine($dataHeaders, $line);

                $rules = [
                    '引落年月' => 'date_format:Y-m|required',
                    '規定' => 'string',
                    '補足説明' => 'string',
                    // 日付0埋めなし
                    '支払期日' => 'date',
                    '生徒No' => 'integer|min:1|max:99999999|required',
                    '発行日' => 'date|required',
                    '備考内容' => 'string'
                ];

                // 各項目の金額のバリデーションを追加
                foreach ($invoice_lists as $invoice_list) {
                    $rules += [
                        $invoice_list['name'] => 'required|vdPrice|vdPriceDigits'
                    ];
                }

                $validator = Validator::make($values, $rules);
                if ($validator->fails()) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(データ項目不正)");
                }

                // 引落年月と指定された請求月が一致しているかチェックする
                if (strcmp($date, $values['引落年月'] . "-01") !== 0) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(請求月不一致)");
                }

                // 請求方法は文字列からコード逆引き 存在しなかったらエラー
                $payTypeKey = $values['請求方法'];
                if (!isset($payType[$payTypeKey])) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(請求方法不正)");
                }

                // 請求方法が「郵貯」「七十七」「他行」の時のみ支払期日は必須
                // 請求方法
                $pay_type_code = $payType[$payTypeKey]->code;
                if ($pay_type_code == AppConst::EXT_GENERIC_MASTER_102_4 || $pay_type_code == AppConst::EXT_GENERIC_MASTER_102_5 || $pay_type_code == AppConst::EXT_GENERIC_MASTER_102_6) {
                    if ($values['支払期日'] == "") {
                        throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(支払期日)");
                    }
                }

                // 日付の独自バリデーション
                $ymdRules = ['custom_date' => 'date_format:Y/m/d'];
                $ynjRules = ['custom_date' => 'date_format:Y/n/j'];

                // 支払期限
                $bill_date = ['custom_date' => $values['支払期日']];
                $validationYmd = Validator::make($bill_date, $ymdRules);
                $validationYnj = Validator::make($bill_date, $ynjRules);

                if ($validationYmd->fails() && $validationYnj->fails()) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(不正な形式：支払期限)");
                }

                // 発行日
                $issue_date = ['custom_date' => $values['発行日']];
                $validationYmd = Validator::make($issue_date, $ymdRules);
                $validationYnj = Validator::make($issue_date, $ynjRules);

                if ($validationYmd->fails() && $validationYnj->fails()) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(不正な形式：発行日)");
                }

                // 登録前バリデーションの場合のみ、データ成型を行う
                if (!$create) {
                    continue;
                }

                // 請求情報
                $invoice = [
                    'sid' => $values['生徒No'],
                    'invoice_date' => $date,
                    'lesson_type' => AppConst::CODE_MASTER_8_1,
                    'pay_type' => (int) $pay_type_code,
                    'agreement' => $values['規定'],
                    'issue_date' => $values['発行日'],
                    'bill_date' => $values['支払期日'],
                    'note' => $values['備考内容'],
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null
                ];
                // 支払期日が空の場合の対応
                foreach ($invoice as $key => $val) {
                    // 空白はnullに変換
                    if ($invoice[$key] === '') {
                        $invoice[$key] = null;
                    }
                }
                array_push($invoices, $invoice);

                //-------------
                // invoice_detailsを作る
                //-------------

                // 請求情報通番を連番にする
                $seq = 1;

                // 請求情報明細
                foreach ($invoice_lists as $invoice_list) {
                    $invoice_detail = [];
                    $invoice_detail['sid'] = $values['生徒No'];
                    $invoice_detail['invoice_date'] = $date;
                    $invoice_detail['lesson_type'] = AppConst::CODE_MASTER_8_1;
                    $invoice_detail['invoice_seq'] = $seq;
                    $invoice_detail['order_code'] = (int) $invoice_list['order_code'];
                    $invoice_detail['cost_name'] = $invoice_list['name'];
                    $invoice_detail['cost'] = (int) str_replace(',', '', $values[$invoice_list['name']]);
                    $invoice_detail['created_at'] = $now;
                    $invoice_detail['updated_at'] = $now;
                    $invoice_detail['deleted_at'] = null;

                    foreach ($invoice_detail as $key => $val) {
                        // 空白はnullに変換
                        if ($invoice_detail[$key] === '') {
                            $invoice_detail[$key] = null;
                        }
                    }

                    array_push($invoice_details, $invoice_detail);
                    $seq++;
                }
            }
        }
        // 家庭教師の場合
        elseif ($type === 'katei') {

            // バリデーション項目名用リスト
            $invoice_lists = [];

            // コードマスタから項目名を取得
            $invoiceCodes = CodeMaster::select(
                'name',
                'order_code',
                'gen_item1'
            )
                ->where('data_type', '=', AppConst::CODE_MASTER_18)
                ->where('sub_code', '=', AppConst::CODE_MASTER_18_SUB_2)
                ->orderBy('order_code', 'asc')
                ->get()
                ->keyBy('gen_item1');

            foreach ($invoiceCodes as $invoice) {
                $invoice_list = [
                    'order_code' => $invoice['order_code'],
                    'name' => $invoice['name']
                ];
                array_push($invoice_lists, $invoice_list);
            }

            // array_combine用
            $dataHeaders = [
                '引落年月',
                '確定',
                '生徒名',
                '家庭教師標準',
                $invoiceCodes['4']['name'],
                $invoiceCodes['5']['name'],
                $invoiceCodes['6']['name'],
                $invoiceCodes['7']['name'],
                $invoiceCodes['8']['name'],
                $invoiceCodes['9']['name'],
                $invoiceCodes['10']['name'],
                '家庭教師 合計',
                $invoiceCodes['12']['name'],
                '合計',
                '補足説明',
                '請求方法',
                '入金日',
                '生徒No',
                '支払期日',
                '発行日',
                '備考内容'
            ];

            // CSV読み込み
            $file = $this->readCsv($path, "sjis");

            // 1行ずつ取得
            foreach ($file as $i => $line) {

                // 最初の2行は読み飛ばす
                if ($i < 2) {
                    continue;
                }

                // [バリデーション] データ行の列の数のチェック
                // データ行の末尾に余計なカンマが入っていても許容する
                if (count($line) < count($dataHeaders)) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(データ列数不正)");
                }

                // 項目名を編集したdataHeadersをもとに、値をセットしたオブジェクトを生成
                array_splice($line, count($dataHeaders));
                $values = array_combine($dataHeaders, $line);

                $rules = [
                    '引落年月' => 'date_format:Y-m|required',
                    '家庭教師標準' => 'string',
                    '補足説明' => 'string',
                    // 日付0埋めなし
                    '支払期日' => 'date',
                    '生徒No' => 'integer|min:1|max:99999999|required',
                    '発行日' => 'date|required',
                    '備考内容' => 'string'
                ];

                // 各項目の金額のバリデーションを追加
                foreach ($invoice_lists as $invoice_list) {
                    $rules += [
                        $invoice_list['name'] => 'required|vdPrice|vdPriceDigits'
                    ];
                }

                $validator = Validator::make($values, $rules);
                if ($validator->fails()) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(データ項目不正)");
                }

                // 引落年月と指定された請求月が一致しているかチェックする
                if (strcmp($date, $values['引落年月'] . "-01") !== 0) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(請求月不一致)");
                }

                // 請求方法は文字列からコード逆引き 存在しなかったらエラー
                $payTypeKey = $values['請求方法'];
                if (!isset($payType[$payTypeKey])) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(請求方法不正)");
                }

                // 請求方法が「郵貯」「七十七」「他行」の時のみ支払期日は必須
                // 請求方法
                $pay_type_code = $payType[$payTypeKey]->code;
                if ($pay_type_code == AppConst::EXT_GENERIC_MASTER_102_4 || $pay_type_code == AppConst::EXT_GENERIC_MASTER_102_5 || $pay_type_code == AppConst::EXT_GENERIC_MASTER_102_6) {
                    if ($values['支払期日'] == "") {
                        throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(支払期日不明)");
                    }
                }

                // 日付の独自バリデーション
                $ymdRules = ['custom_date' => 'date_format:Y/m/d'];
                $ynjRules = ['custom_date' => 'date_format:Y/n/j'];

                // 支払期限
                $bill_date = ['custom_date' => $values['支払期日']];
                $validationYmd = Validator::make($bill_date, $ymdRules);
                $validationYnj = Validator::make($bill_date, $ynjRules);

                if ($validationYmd->fails() && $validationYnj->fails()) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(不正な日付形式：支払期限)");
                }

                // 発行日
                $issue_date = ['custom_date' => $values['発行日']];
                $validationYmd = Validator::make($issue_date, $ymdRules);
                $validationYnj = Validator::make($issue_date, $ynjRules);

                if ($validationYmd->fails() && $validationYnj->fails()) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(不正な日付形式：発行日)");
                }

                // 登録前バリデーションの場合のみ、データ成型を行う
                if (!$create) {
                    continue;
                }

                // 請求情報
                $invoice = [
                    'sid' => $values['生徒No'],
                    'invoice_date' => $date,
                    'lesson_type' => AppConst::CODE_MASTER_8_2,
                    'pay_type' => (int) $pay_type_code,
                    'agreement' => $values['家庭教師標準'],
                    'issue_date' => $values['発行日'],
                    'bill_date' => $values['支払期日'],
                    'note' => $values['備考内容'],
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null
                ];
                // 支払期日が空の場合の対応
                foreach ($invoice as $key => $val) {
                    // 空白はnullに変換
                    if ($invoice[$key] === '') {
                        $invoice[$key] = null;
                    }
                }
                array_push($invoices, $invoice);

                //-------------
                // invoice_detailsを作る
                //-------------

                // 請求情報通番を連番にする（表示順と通番は異なる）
                $seq = 1;

                // 請求情報明細
                foreach ($invoice_lists as $invoice_list) {
                    $invoice_detail = [];
                    $invoice_detail['sid'] = $values['生徒No'];
                    $invoice_detail['invoice_date'] = $date;
                    $invoice_detail['lesson_type'] = AppConst::CODE_MASTER_8_2;
                    $invoice_detail['invoice_seq'] = $seq;
                    $invoice_detail['order_code'] = (int) $invoice_list['order_code'];
                    $invoice_detail['cost_name'] = $invoice_list['name'];
                    $invoice_detail['cost'] = (int) str_replace(',', '', $values[$invoice_list['name']]);
                    $invoice_detail['created_at'] = $now;
                    $invoice_detail['updated_at'] = $now;
                    $invoice_detail['deleted_at'] = null;
                    array_push($invoice_details, $invoice_detail);
                    $seq++;
                }
            }
        } else {
            // あり得ないのでイレギュラー
            $this->illegalResponseErr();
        }

        // 取込のタイムスタンプを揃えるため、処理開始時点のタイムスタンプも渡しておく
        $datas = [
            'invoices' => $invoices,
            'invoice_details' => $invoice_details,
            'import_date' => $now
        ];

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

        $rules += ['invoiceDate' => ['integer', 'required']];

        // ファイルアップロードの必須チェック
        $rules += ['upload_file_kobetsu' => ['required']];
        $rules += ['upload_file_katei' => ['required']];

        // ファイルのタイプのチェック(「file_項目名」の用にチェックする)
        $rules += ['file_upload_file_kobetsu' => [
            // ファイル
            'file',
            // mimes CSVのMIMEタイプリストと一致するか（laravel8と少し挙動が異なる）
            'mimes:csv',
        ]];

        $rules += ['file_upload_file_katei' => [
            // ファイル
            'file',
            // mimes CSVのMIMEタイプリストと一致するか（laravel8と少し挙動が異なる）
            'mimes:csv',
        ]];

        return $rules;
    }
}
