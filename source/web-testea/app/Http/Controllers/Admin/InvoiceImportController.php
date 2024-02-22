<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CtrlFileTrait;
use App\Http\Controllers\Traits\CtrlCsvTrait;
use App\Exceptions\ReadDataValidateException;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\InvoiceImport;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\CodeMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Carbon\Carbon;

/**
 * 請求情報取込 - コントローラ
 */
class InvoiceImportController extends Controller
{
    // 機能共通処理：ファイル操作
    use CtrlFileTrait;
    use CtrlCsvTrait;

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
        // 全体管理者でない場合は画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

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
        // 翌月の月初を取得
        $nextMonth = date('Y-m-d', strtotime('first day of next month '));

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
                $join->on('invoice_import.import_state', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_20);
            })
            ->where('invoice_date', '<=', $nextMonth)
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
        // 全体管理者でない場合は画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // IDのバリデーション
        $this->validateIds($invoiceDate);

        // dateの形式のバリデーションと変換
        $idDate = $this->fmYmToDate($invoiceDate);
        // 請求年月の月末を取得
        $idEnd = date('Y/m/t', strtotime($idDate));

        // 翌月の月初を取得
        $nextMonth = date('Y-m-d', strtotime('first day of next month '));

        // 取込可能な年月か確認する
        if ($nextMonth < $idDate) {
            $this->illegalResponseErr();
        }

        // 請求情報取込を取得
        $invoice_import = InvoiceImport::select(
            'invoice_date',
            'bill_date',
            'start_date',
            'end_date',
            'term_text1',
            'term_text2',
        )
            ->where('invoice_date', '=', $idDate)
            ->firstOrFail();

        // $editDataに値をセット
        // 発行日は常にシステム日付
        $editData['issue_date'] = date('Y/m/d');
        // hidden用にセット
        $editData['invoiceDate'] = $invoiceDate;
        // 請求書年月
        $editData['invoice_date'] = $invoice_import->invoice_date;

        // 各項目の有無によってセットする値を分岐
        if ($invoice_import->bill_date != null) {
            $editData['bill_date'] = $invoice_import->bill_date;
        } else {
            $editData['bill_date'] = $idDate;
        }

        if ($invoice_import->start_date != null) {
            $editData['start_date'] = $invoice_import->start_date;
        } else {
            $editData['start_date'] = $idDate;
        }

        if ($invoice_import->end_date != null) {
            $editData['end_date'] = $invoice_import->end_date;
        } else {
            $editData['end_date'] = $idEnd;
        }

        if ($invoice_import->term_text1 != null) {
            $editData['term_text1'] = $invoice_import->term_text1;
        }

        if ($invoice_import->term_text2 != null) {
            $editData['term_text2'] = $invoice_import->term_text2;
        }

        return view('pages.admin.invoice_import-import', [
            'rules' => $this->rulesForInput(null),
            'editData' => $editData,
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

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // アップロード先(アップ先は用途ごとに分ける)
        $uploadDir = config('appconf.upload_dir_invoice_import') . date("YmdHis");

        // アップロードファイルの保存
        $path = $this->fileUploadSave($request, $uploadDir, 'upload_file');

        // 請求年月のdateの形式のバリデーションと変換
        $invoiceDate = $request->input('invoiceDate');
        $idDate = $this->fmYmToDate($invoiceDate);

        // 翌月の月初を取得
        $nextMonth = date('Y-m-d', strtotime('first day of next month '));

        // 取込可能な年月か確認する
        if ($nextMonth < $idDate) {
            $this->illegalResponseErr();
        }

        $datas = [];
        try {
            // CSVデータの読み込み
            $datas = $this->readData($path);
        } catch (ReadDataValidateException  $e) {
            // 通常は事前にバリデーションするのでここはありえないのでエラーとする
            return $this->responseErr();
        }

        try {
            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($request, $datas, $idDate) {
                //==========================
                // 既存データ削除
                //==========================
                // 請求情報テーブルから対象月の請求IDを取得する
                $invoices = Invoice::select('invoice_id')
                    ->where('invoice_date', '=', $idDate)
                    ->get();

                // 取得した請求IDリストを作る
                $invoiceIdList = [];
                foreach ($invoices as $invoice) {
                    $invoiceIdList[] = $invoice['invoice_id'];
                }

                // 請求明細情報テーブルから取得した請求IDに該当するレコードを物理削除する
                InvoiceDetail::whereIn('invoice_id', $invoiceIdList)
                    ->forceDelete();

                // 請求情報テーブルから対象月のレコードを物理削除する
                Invoice::where('invoice_date', '=', $idDate)
                    ->forceDelete();

                //==========================
                // 新規データ作成
                //==========================
                // 1行ずつ取り込んだデータごとに処理
                foreach ($datas as $data) {
                    //-------------
                    // invoicesの新規登録
                    //-------------
                    $invoice = new Invoice;
                    $invoice->student_id = $data['生徒ID'];
                    $invoice->invoice_date = $idDate;
                    $invoice->campus_cd = $data['校舎コード'];
                    $invoice->pay_type = $data['支払方法'];
                    $invoice->total_amount = $data['請求額'];
                    // 保存
                    $invoice->save();

                    //-------------
                    // invoice_detailsの新規登録
                    //-------------
                    // 明細連番用
                    $seq = 1;

                    // 摘要～小計の20セット分を処理
                    for ($i = 0; $i < 20; $i++) {
                        // 摘要が空欄なら飛ばす
                        if (empty($data['摘要' . $i])) {
                            continue;
                        }

                        $detail = [];
                        $detail['invoice_id'] = $invoice->invoice_id;
                        $detail['invoice_seq'] = $seq;
                        $detail['description'] = $data['摘要' . $i];
                        $detail['unit_price'] = $data['コマ単価' . $i];
                        $detail['times'] = $data['コマ数' . $i];
                        $detail['amount'] = $data['小計' . $i];

                        foreach ($detail as $key => $val) {
                            // 空白はnullに変換 金額などは数値型のため
                            if ($detail[$key] === '') {
                                $invoice_detail[$key] = null;
                            }
                        }

                        // 保存
                        $invoice_detail = new InvoiceDetail;
                        $invoice_detail->fill($detail)->save();

                        // 連番加算
                        $seq++;
                    }
                }

                //==========================
                // 請求取込情報の更新
                //==========================
                // 対象月の請求取込情報を取得
                $salaryImport = InvoiceImport::where('invoice_date', '=', $idDate)
                    ->firstOrFail();

                // 取込画面で入力した内容をセット
                $salaryImport->issue_date = $request['issue_date'];
                $salaryImport->bill_date = $request['bill_date'];
                $salaryImport->start_date = $request['start_date'];
                $salaryImport->end_date = $request['end_date'];
                $salaryImport->term_text1 = $request['term_text1'];
                $salaryImport->term_text2 = $request['term_text2'];
                $salaryImport->import_state = AppConst::CODE_MASTER_20_1;
                $salaryImport->import_date = Carbon::now();

                // 更新
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
        $this->fileUploadSetVal($request, 'upload_file');

        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInput($request));

        // エラーがあれば返却
        if ($validator->fails()) {
            return $validator->errors();
        }

        // CSVのパスを取得(upload直後のtmpのパス)
        $path = $this->fileUploadRealPath($request, 'upload_file');

        try {
            // CSVの中身の読み込みとバリデーション
            $this->readData($path);
        } catch (ReadDataValidateException  $e) {
            // エラーがあれば返却
            return ['upload_file' => [$e->getMessage()]];
        }

        return;
    }

    /**
     * アップロードされたファイルを読み込む
     * バリデーションも行う
     *
     * @param $path
     * @return array
     */
    private function readData($path)
    {
        // return用配列
        $datas = [];

        // 生徒重複チェック用配列
        $student = [];

        // 支払方法のチェック用コード取得
        $payType = CodeMaster::select('code')
            ->where('data_type', '=', AppConst::CODE_MASTER_21)
            ->get()
            ->keyBy('code');

        // バリデーション項目名用・ヘッダー用リスト 摘要～小計を20セット作成
        $details_lists = [];
        for ($i = 0; $i < 20; $i++) {
            $details_list = [
                '摘要' . $i => '摘要' . $i,
                'コマ単価' . $i => 'コマ単価' . $i,
                'コマ数' . $i => 'コマ数' . $i,
                '小計' . $i => '小計' . $i,
            ];
            array_push($details_lists, $details_list);
        }

        // ヘッダーリスト
        $arrayHeaders = [
            '生徒ID',
            '姓',
            '名',
            '支払方法',
            '校舎コード',
            '請求額',
        ];

        // 摘要～小計の多次元配列を一次元配列に変換
        $arrayDetails = Arr::flatten($details_lists);
        // ヘッダー配列の結合 生徒ID～小計 array_combineで使用
        $dataHeaders = array_merge($arrayHeaders, $arrayDetails);

        // CSV読み込み
        $file = $this->readCsv($path, "sjis");

        // 1行ずつ取得
        foreach ($file as $i => $line) {

            // 最初の10行は読み飛ばす
            if ($i < 10) {
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

            // バリデーションルールセット
            $rules = [
                '生徒ID' => 'integer|min:1|max:99999999|required',
                '姓' => 'string|max:50',
                '名' => 'string|max:50',
                '支払方法' => 'integer|required',
                '校舎コード' => 'string|max:2|digits:2|required',
                '請求額' => 'integer|max:99999999|required',
            ];

            // detailsの各項目のバリデーションルールを追加
            for ($i = 0; $i < 20; $i++) {
                $rules += [
                    $details_lists[$i]['摘要' . $i] => 'string|max:50',
                    $details_lists[$i]['コマ単価' . $i] => 'integer|max:99999999',
                    $details_lists[$i]['コマ数' . $i] => 'integer|max:99999999',
                    $details_lists[$i]['小計' . $i] => 'required_with:摘要' . $i . '|integer|max:99999999',
                ];
            }

            // バリデーションルールチェック
            $validator = Validator::make($values, $rules);
            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('生徒ID')) {
                    $errCol = "生徒ID=" . $values['生徒ID'];
                } else if ($validator->errors()->has('姓')) {
                    $errCol = "姓=" . $values['姓'];
                } else if ($validator->errors()->has('名')) {
                    $errCol =  "名=" . $values['名'];
                } else if ($validator->errors()->has('支払方法')) {
                    $errCol =  "支払方法=" . $values['支払方法'];
                } else if ($validator->errors()->has('校舎コード')) {
                    $errCol =  "校舎コード=" . $values['校舎コード'];
                } else if ($validator->errors()->has('請求額')) {
                    $errCol =  "請求額=" . $values['請求額'];
                }
                for ($i = 0; $i < 20; $i++) {
                    if ($validator->errors()->has('摘要' . $i)) {
                        $errCol =  "摘要=" . $values['摘要' . $i];
                    } else if ($validator->errors()->has('コマ単価' . $i)) {
                        $errCol =  "コマ単価=" . $values['コマ単価' . $i];
                    } else if ($validator->errors()->has('コマ数' . $i)) {
                        $errCol =  "コマ数=" . $values['コマ数' . $i];
                    } else if ($validator->errors()->has('小計' . $i)) {
                        $errCol =  "小計=" . $values['小計' . $i];
                    }
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "データ項目不正( 生徒ID=" . $values['生徒ID'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // 支払方法のコードが存在しなかったらエラー
            $payTypeKey = $values['支払方法'];
            if (!isset($payType[$payTypeKey])) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "支払方法不正( 生徒ID=" . $values['生徒ID'] . ", "
                    . "支払方法=" . $values['支払方法'] . " )");
            }

            // 1生徒につき1請求書のチェック
            // $student配列に生徒IDを格納
            array_push($student, $values['生徒ID']);
            // 各生徒IDの出現回数を数える
            $value_count = array_count_values($student);
            // 最大の出現回数を取得する
            $max = max($value_count);
            // 最大出現回数が1でない場合（既に存在する場合）はエラー
            if ($max != 1) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "生徒重複( 生徒ID=" . $values['生徒ID'] . " )");
            }

            // $datas配列に1行分のデータを格納する
            array_push($datas, $values);
        }

        return $datas;
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        // 独自バリデーション: 月謝期間終了日は月謝期間開始日より未来日とする
        $validationEndDate = function ($attribute, $value, $fail) use ($request) {
            // 月謝期間終了日の数値が月謝期間開始日の数値を下回っていないかチェック
            if (strtotime($request['end_date']) <= strtotime($request['start_date'])) {
                // 下回っていた（月謝期間開始日より未来日でない）場合エラー
                return $fail(Lang::get('validation.after',));
            }
        };

        $rules = array();

        $rules += InvoiceImport::fieldRules('issue_date', ['required']);
        $rules += InvoiceImport::fieldRules('start_date', ['required']);
        $rules += InvoiceImport::fieldRules('end_date', ['required', $validationEndDate]);
        $rules += InvoiceImport::fieldRules('term_text1');
        $rules += InvoiceImport::fieldRules('term_text2');
        $rules += InvoiceImport::fieldRules('bill_date', ['required']);

        // hiddenの年月チェック
        $rules += ['invoiceDate' => ['integer', 'required']];

        // ファイルアップロードの必須チェック
        $rules += ['upload_file' => ['required']];

        // ファイルのタイプのチェック(「file_項目名」の用にチェックする)
        $rules += ['file_upload_file' => [
            // ファイル
            'file',
            // mimes CSVのMIMEタイプリストと一致するか（laravel8と少し挙動が異なる）
            'mimes:csv',
        ]];

        return $rules;
    }
}
