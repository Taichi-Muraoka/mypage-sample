<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Consts\AppConst;
use Carbon\Carbon;
use App\Models\Salary;
use App\Models\SalaryDetail;
use App\Models\SalaryImport;
use App\Models\CodeMaster;
use App\Exceptions\ReadDataValidateException;
use Illuminate\Support\Facades\Log;

/**
 * 給与情報取込 - コントローラ
 */
class SalaryImportController extends Controller
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

        return view('pages.admin.salary_import');
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

        // 給与情報取込を取得
        $query = SalaryImport::query();
        $salary_imports = $query
            ->select(
                'salary_date',
                'import_state',
                'name AS state_name'
            )
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('salary_import.import_state', '=', 'code_master.code')
                    ->where('code_master.data_type', AppConst::CODE_MASTER_20);
            })
            ->where('salary_date', '<=', $present_month)
            ->orderBy('salary_date', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $salary_imports, function ($items) {
            // IDは年月
            foreach ($items as $item) {
                $item['id'] = $item->salary_date->format('Ym');
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
     * @param date $salaryDate 年月（YYYYMM）
     * @return view
     */
    public function import($salaryDate)
    {

        // IDのバリデーション
        $this->validateIds($salaryDate);

        // dateの形式のバリデーションと変換
        $idDate = $this->fmYmToDate($salaryDate);

        // 当月を取得
        $present_month = date('Y-m') . '-01';

        // 取込可能な年月か確認する
        if ($present_month < $idDate) {
            $this->illegalResponseErr();
        }

        // 給与情報取込を取得
        $salary_import = SalaryImport::select('salary_date')
            ->where('salary_date', '=', $idDate)
            ->firstOrFail();

        return view('pages.admin.salary_import-import', [
            'rules' => $this->rulesForInput(),
            'salary_import' => $salary_import,
            'editData' => [
                'salaryDate' => $salaryDate
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
        $this->fileUploadSetVal($request, 'upload_file');

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // アップロード先(アップ先は用途ごとに分ける)
        $uploadDir = config('appconf.upload_dir_salary_import') . date("YmdHis");

        // アップロードファイルの保存
        $path = $this->fileUploadSave($request, $uploadDir, 'upload_file');

        $salaryDate = $request->input('salaryDate');

        // dateの形式のバリデーションと変換
        $idDate = $this->fmYmToDate($salaryDate);

        // 当月を取得
        $present_month = date('Y-m') . '-01';

        // 取込可能な年月か確認する
        if ($present_month < $idDate) {
            $this->illegalResponseErr();
        }

        $datas = [];
        try {
            // CSVデータの読み込み 保存用データを返すのでtrue
            $datas = $this->readData($path, $salaryDate, true);
        } catch (ReadDataValidateException  $e) {
            // 通常は事前にバリデーションするのでここはありえないのでエラーとする
            return $this->responseErr();
        }

        try {

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($datas, $idDate) {

                // 給与情報明細から対象月のレコードを物理削除する
                SalaryDetail::where('salary_date', '=', $idDate)
                    ->forceDelete();

                // 給与情報から対象月のレコードを物理削除する
                Salary::where('salary_date', '=', $idDate)
                    ->forceDelete();

                // 給与情報に取込データを挿入する
                // MEMO: 一括Insertは、INSERT_CHUNK数 ずつ分割して行う
                $salary = new Salary;
                $salarysChunks = array_chunk($datas['salarys'], self::INSERT_CHUNK);
                foreach ($salarysChunks as $salaryChunk) {
                    $salary->insert($salaryChunk);
                }

                // 給与情報明細数をカウント
                $detailCnt = CodeMaster::where('data_type', '=', AppConst::CODE_MASTER_19)
                    ->count();

                // 給与明細情報に取込データを挿入する
                // MEMO: 一括Insertは、INSERT_CHUNK * 明細数 ずつ分割して行う
                $salaryDetail = new SalaryDetail;
                $salarysChunks = array_chunk($datas['salary_details'], self::INSERT_CHUNK * $detailCnt);
                foreach ($salarysChunks as $salaryChunk) {
                    $salaryDetail->insert($salaryChunk);
                }

                // 該当する給与情報取込を更新する
                $salaryImport = SalaryImport::where('salary_date', '=', $idDate)->firstOrFail();
                $salaryImport->import_state = AppConst::CODE_MASTER_20_1;
                $salaryImport->import_date = $datas['import_date'];
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
        $validator = Validator::make($request->all(), $this->rulesForInput());

        // エラーがあれば返却
        if ($validator->fails()) {
            return $validator->errors();
        }

        // salaryDate(取込月)も取得しておく
        $salaryDate = $request->input('salaryDate');

        // CSVのパスを取得(upload直後のtmpのパス)
        $path = $this->fileUploadRealPath($request, 'upload_file');

        try {
            // CSVの中身の読み込みとバリデーション
            $this->readData($path, $salaryDate, true);
        } catch (ReadDataValidateException  $e) {
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
     * @param date $salaryDate (YYYYMM) 取込月
     * @param bool $create バリデーションだけの時はfalse
     * @return array 取り込んだcsvデータ
     */
    private function readData($path, $salaryDate, $create = false)
    {

        // 取込対象月
        $date = $this->fmYmToDate($salaryDate);

        // ヘッダ行成型用
        $csvHeaderNames = [];
        $csvHeaderPref = ["", "項目名"];
        $csvHeaderSuf = ["扶養人数", "税額表"];
        $dataHeaderPref = ["tid", "name"];
        $dataHeaderSuf = ["dependents", "tax_table"];

        // 各表示グループ
        $salary_group_1 = [];
        $salary_group_2 = [];
        $salary_group_3 = [];
        $salary_group_4 = [];

        // コードマスタから項目名を取得
        $salary_headers = CodeMaster::select(
            'sub_code',
            'name'
        )
            ->where('data_type', '=', AppConst::CODE_MASTER_19)
            ->orderBy('order_code', 'asc')
            ->get();

        foreach ($salary_headers as $salary_header) {
            array_push($csvHeaderNames, $salary_header->name);
            switch ($salary_header->sub_code) {
                case AppConst::SALARY_GROUP_1:
                    array_push($salary_group_1, $salary_header->name);
                    break;
                case AppConst::SALARY_GROUP_2:
                    array_push($salary_group_2, $salary_header->name);
                    break;
                case AppConst::SALARY_GROUP_3:
                    array_push($salary_group_3, $salary_header->name);
                    break;
                case AppConst::SALARY_GROUP_4:
                    array_push($salary_group_4, $salary_header->name);
                    break;
            }
        }

        $csvHeaders = array_merge($csvHeaderPref, $csvHeaderNames);
        $csvHeaders = array_merge($csvHeaders, $csvHeaderSuf);

        // array_combine用
        $dataHeaders = array_merge($dataHeaderPref, $csvHeaderNames);
        $dataHeaders = array_merge($dataHeaders, $dataHeaderSuf);

        //-------------
        // グループ分け
        //-------------

        // CSVのデータをリストで保持
        $datas = [];
        $headers = [];

        // DB保存用配列
        $salarys = [];
        $salary_details = [];

        // 現在日時を取得
        $now = Carbon::now();

        // CSV読み込み
        $file = $this->readCsv($path, "sjis");

        // 1行ずつ取得
        foreach ($file as $i => $line) {

            // 8行目までは読み飛ばす
            if ($i < 7) {
                continue;
            }

            // 8行目がヘッダ行
            if ($i === 7) {
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

            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(データ列数不正)");
            }

            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($dataHeaders, $line);

            // 合計行で終了 ただし10行目以降の場合
            if (empty($values['tid']) && $i > 8) {
                break;
            }

            // [バリデーション] データ行の値のチェック
            $rules = [
                'name' => 'string|required',
            ];
            $rules += Salary::fieldRules('tid', ['required'], '_csv');
            $rules += Salary::fieldRules('tax_table', ['required']);
            $rules += Salary::fieldRules('dependents', ['required']);

            foreach ($csvHeaderNames as $csvHeaderName) {
                $rules += [
                    $csvHeaderName => 'required|vdPrice|vdPriceDigits'
                ];
            }

            $validator = Validator::make($values, $rules);
            if ($validator->fails()) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(データ項目不正)");
            }

            // 登録前バリデーションの場合のみ、データ成型を行う
            if (!$create) {
                continue;
            }

            // 給与情報
            $salary = [
                'tid' => $values['tid'],
                'salary_date' => $date,
                'dependents' => $values['dependents'],
                'tax_table' => $values['tax_table'],
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null
            ];
            array_push($salarys, $salary);

            //-------------
            // グループごとにsalary_detailsを作る
            //-------------

            // 給与情報通番と表示順を連番にする
            $seq = 1;
            $count_group_1 = 0;
            $count_group_2 = 0;

            // 支給
            foreach ($salary_group_1 as $salary_detail) {
                $salary_detail = [];
                $salary_detail['tid'] = (int) $values['tid'];
                $salary_detail['salary_date'] = $date;
                $salary_detail['salary_seq'] = $seq;
                $salary_detail['order_code'] = $seq;
                $salary_detail['salary_group'] = 1;
                $salary_detail['item_name'] = $salary_group_1[$count_group_1];
                $salary_detail['amount'] = (int) str_replace(',', '', $values[$salary_group_1[$count_group_1]]);
                $salary_detail['created_at'] = $now;
                $salary_detail['updated_at'] = $now;
                $salary_detail['deleted_at'] = null;
                array_push($salary_details, $salary_detail);
                $seq++;
                $count_group_1++;
            }

            // 控除
            foreach ($salary_group_2 as $salary_detail) {
                $salary_detail = [];
                $salary_detail['tid'] = (int) $values['tid'];
                $salary_detail['salary_date'] = $date;
                $salary_detail['salary_seq'] = $seq;
                $salary_detail['order_code'] = $seq;
                $salary_detail['salary_group'] = 2;
                $salary_detail['item_name'] = $salary_group_2[$count_group_2];
                $salary_detail['amount'] = (int) str_replace(',', '', $values[$salary_group_2[$count_group_2]]);
                $salary_detail['created_at'] = $now;
                $salary_detail['updated_at'] = $now;
                $salary_detail['deleted_at'] = null;
                array_push($salary_details, $salary_detail);
                $seq++;
                $count_group_2++;
            }

            // その他
            $salary_detail = [];
            $salary_detail['tid'] = (int) $values['tid'];
            $salary_detail['salary_date'] = $date;
            $salary_detail['salary_seq'] = $seq;
            $salary_detail['order_code'] = $seq;
            $salary_detail['salary_group'] = 3;
            $salary_detail['item_name'] = $salary_group_3[0];
            $salary_detail['amount'] = (int) str_replace(',', '', $values[$salary_group_3[0]]);
            $salary_detail['created_at'] = $now;
            $salary_detail['updated_at'] = $now;
            $salary_detail['deleted_at'] = null;
            array_push($salary_details, $salary_detail);
            $seq++;

            // 合計
            $salary_detail = [];
            $salary_detail['tid'] = (int) $values['tid'];
            $salary_detail['salary_date'] = $date;
            $salary_detail['salary_seq'] = $seq;
            $salary_detail['order_code'] = $seq;
            $salary_detail['salary_group'] = 4;
            $salary_detail['item_name'] = $salary_group_4[0];
            $salary_detail['amount'] = (int) str_replace(',', '', $values[$salary_group_4[0]]);
            $salary_detail['created_at'] = $now;
            $salary_detail['updated_at'] = $now;
            $salary_detail['deleted_at'] = null;
            array_push($salary_details, $salary_detail);
        }

        // 取込のタイムスタンプを揃えるため、処理開始時点のタイムスタンプも渡しておく
        $datas = [
            'salarys' => $salarys,
            'salary_details' => $salary_details,
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

        $rules += ['salaryDate' => ['integer', 'required']];

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
