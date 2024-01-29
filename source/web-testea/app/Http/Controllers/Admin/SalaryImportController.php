<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use Carbon\Carbon;
use App\Models\Salary;
use App\Models\SalaryDetail;
use App\Models\SalaryImport;
use App\Models\CodeMaster;
use App\Exceptions\ReadDataValidateException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

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
        // 全体管理者でない場合は画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

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
                $join->on('salary_import.import_state', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_20);
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
        // 全体管理者でない場合は画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

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
        // 全体管理者でない場合は画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

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

                //==========================
                // 既存データ削除
                //==========================
                // 給与情報テーブルから対象月の給与IDを取得する
                $salaryies = Salary::select('salary_id')
                    ->where('salary_date', '=', $idDate)
                    ->get();

                // 取得した請求IDリストを作る
                $salaryIdList = [];
                foreach ($salaryies as $salary) {
                    $salaryIdList[] = $salary['salary_id'];
                }

                // 給与情報から対象月のレコードを物理削除する
                Salary::where('salary_date', '=', $idDate)
                    ->forceDelete();

                // 給与情報明細から対象月のレコードを物理削除する
                SalaryDetail::whereIn('salary_id', $salaryIdList)
                    ->forceDelete();

                // 給与情報明細数
                $detailCnt = AppConst::COUNT_SALARY;

                $seq = 0;
                foreach ($datas['salarys'] as $salaryData) {

                    // 給与情報に取込データを挿入する
                    $salary = new Salary;
                    $salary->tutor_id = $salaryData['tutor_id'];
                    $salary->salary_date = $salaryData['salary_date'];
                    $salary->total_amount = $salaryData['total_amount'];
                    $salary->memo = $salaryData['memo'];
                    // 保存
                    $salary->save();

                    for ($i = 0; $i < $detailCnt; $i++) {
                        // 給与明細情報に取込データを挿入する
                        $salaryDetail = new SalaryDetail;
                        $salaryDetail->salary_id = $salary->salary_id;
                        $salaryDetail->salary_seq = $datas['salary_details'][$seq]['salary_seq'];
                        $salaryDetail->salary_group = $datas['salary_details'][$seq]['salary_group'];
                        $salaryDetail->item_name = $datas['salary_details'][$seq]['item_name'];
                        $salaryDetail->hour_payment = $datas['salary_details'][$seq]['hour_payment'];
                        $salaryDetail->hour = $datas['salary_details'][$seq]['hour'];
                        $salaryDetail->amount = $datas['salary_details'][$seq]['amount'];
                        // 保存
                        $salaryDetail->save();
                        $seq++;
                    }
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

        // 各表示グループ
        $salary_group_1 = [];
        $salary_group_2 = [];
        $salary_group_3 = [];
        $salary_group_4 = [];
        $salary_group_0 = [];

        // コードマスタからCSV汎用項目１を取得
        $salary_headers = CodeMaster::select(
            'sub_code',
            'gen_item1'
        )
            ->where('data_type', '=', AppConst::CODE_MASTER_19)
            ->orderBy('code', 'asc')
            ->get();

        //-------------
        // グループ分け
        //-------------
        foreach ($salary_headers as $salary_header) {
            array_push($csvHeaderNames, $salary_header->gen_item1);
            switch ($salary_header->sub_code) {
                case AppConst::SALARY_GROUP_1:
                    array_push($salary_group_1, $salary_header->gen_item1);
                    break;
                case AppConst::SALARY_GROUP_2:
                    array_push($salary_group_2, $salary_header->gen_item1);
                    break;
                case AppConst::SALARY_GROUP_3:
                    array_push($salary_group_3, $salary_header->gen_item1);
                    break;
                case AppConst::SALARY_GROUP_4:
                    array_push($salary_group_4, $salary_header->gen_item1);
                    break;
                case AppConst::SALARY_GROUP_0:
                    array_push($salary_group_0, $salary_header->gen_item1);
                    break;
            }
        }

        // CSVのデータをリストで保持
        $datas = [];
        $headers = [];

        // DB保存用配列
        $salarys = [];
        $salary_details = [];

        // CSV読み込み
        $file = $this->readCsv($path, "sjis");

        // 1行ずつ取得
        foreach ($file as $line_i => $line) {

            // 0行目がヘッダ行
            if ($line_i === 0) {
                //-------------
                // ヘッダ行
                //-------------
                $headers = $line;

                // [バリデーション] ヘッダが想定通りかチェック
                if ($headers !== $csvHeaderNames) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(ヘッダ行不正)");
                }
                continue;
            }

            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaderNames)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(データ列数不正)");
            }

            $csvHeaderPref = [
                "講師番号",
                "講師名",
                "出社回数"
            ];

            $dataHeaderPref = [
                "個別コース",
                "１対２",
                "１対３",
                "集団",
                "家庭教師",
                "演習",
                "ハイプラン",
                "インターン",
                "作業時間"
            ];

            $dataHeaderSuf = [
                "交通費",
                "特別報酬",
                "ペナルティ",
                "源泉計算用小計",
                "交通費等",
                "扶養者",
                "種別",
                "源泉徴収月額",
                "住民税徴収",
                "経費精算",
                "年末調整",
                "支払",
                "備考"
            ];

            $details_lists = [];
            $i = 1;

            foreach ($dataHeaderPref as $content) {
                $details_list = [
                    $content => $content,
                    "時給" . $i => "時給" . $i
                ];
                array_push($details_lists, $details_list);
                $i++;
            }

            // array_combine用
            $arrayDetails = Arr::flatten($details_lists);
            $dataHeaders = array_merge($csvHeaderPref, $arrayDetails, $dataHeaderSuf);

            // headerをもとに、値をセットしたオブジェクトを生成
            array_splice($line, count($dataHeaders));
            $values = array_combine($dataHeaders, $line);

            // [バリデーション] データ行の値のチェック
            $rules = [
                "講師番号" => 'integer|min:1|max:9999999999|required',
                "講師名" => 'string|max:50|required',
                "出社回数" => 'integer|max:99999999|required',
                "交通費" => 'integer|max:99999999',
                "特別報酬" => 'integer|max:99999999',
                "ペナルティ" => 'integer|max:99999999',
                "源泉計算用小計" => 'max:99999999',
                "扶養者" => 'integer|max:999',
                "種別" => 'string|max:1',
                "源泉徴収月額" => 'max:99999999',
                "交通費等" => 'integer|max:99999999',
                "住民税徴収" => 'integer|min:0|max:99999999',
                "経費精算" => 'integer|min:0|max:99999999',
                "年末調整" => 'integer|min:0|max:99999999',
                "支払" => 'required|integer|min:0|max:99999999',
                "備考" => 'string|max:1000'
            ];

            $i = 1;
            foreach ($dataHeaderPref as $dataHeader) {
                $rules += [
                    $dataHeader => 'required|numeric|max:999',
                    "時給" . $i => 'required|integer|max:99999999'
                ];
                $i++;
            }

            $validator = Validator::make($values, $rules);
            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('講師番号')) {
                    $errCol = "講師番号=" . $values['講師番号'];
                } else if ($validator->errors()->has('講師名')) {
                    $errCol = "講師名=" . $values['講師名'];
                } else if ($validator->errors()->has('出社回数')) {
                    $errCol =  "出社回数=" . $values['出社回数'];
                } else if ($validator->errors()->has('交通費')) {
                    $errCol =  "交通費=" . $values['交通費'];
                } else if ($validator->errors()->has('特別報酬')) {
                    $errCol =  "特別報酬=" . $values['特別報酬'];
                } else if ($validator->errors()->has('ペナルティ')) {
                    $errCol =  "ペナルティ=" . $values['ペナルティ'];
                } else if ($validator->errors()->has('源泉計算用小計')) {
                    $errCol = "源泉計算用小計=" . $values['源泉計算用小計'];
                } else if ($validator->errors()->has('交通費等')) {
                    $errCol =  "交通費等=" . $values['交通費等'];
                } else if ($validator->errors()->has('扶養者数')) {
                    $errCol =  "扶養者数=" . $values['扶養者数'];
                } else if ($validator->errors()->has('種別')) {
                    $errCol =  "種別=" . $values['種別'];
                } else if ($validator->errors()->has('源泉徴収月額')) {
                    $errCol =  "源泉徴収月額=" . $values['源泉徴収月額'];
                } else if ($validator->errors()->has('住民税徴収')) {
                    $errCol = "住民税徴収=" . $values['住民税徴収'];
                } else if ($validator->errors()->has('経費精算')) {
                    $errCol =  "経費精算=" . $values['経費精算'];
                } else if ($validator->errors()->has('年末調整')) {
                    $errCol =  "年末調整=" . $values['年末調整'];
                } else if ($validator->errors()->has('支払')) {
                    $errCol =  "支払=" . $values['支払'];
                } else if ($validator->errors()->has('備考')) {
                    $errCol =  "備考=" . $values['備考'];
                }
                $i = 1;
                foreach ($dataHeaderPref as $dataHeader) {
                    if ($validator->errors()->has('時給' . $i)) {
                        $errCol =  "時給=" . $values['時給' . $i];
                    } else if ($validator->errors()->has($dataHeader)) {
                        $errCol = $dataHeader . "=" . $values[$dataHeader];
                    }
                    $i++;
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "データ項目不正( " . $line_i + 1 . "行目 講師番号=" . $values['講師番号']
                    . " エラー項目：" . $errCol . " )");
            }

            // 登録前バリデーションの場合のみ、データ成型を行う
            if (!$create) {
                continue;
            }

            // 給与情報
            $salary = [
                'tutor_id' => $values['講師番号'],
                'salary_date' => $date,
                'total_amount' => $values['支払'],
                'memo' => $values['備考']
            ];
            array_push($salarys, $salary);

            //-------------
            // グループごとにsalary_detailsを作る
            //-------------

            // 給与情報通番と表示順を連番にする
            $seq = 1;
            $count_group_1 = 0;

            // 支給
            foreach ($salary_group_1 as $salary_group) {
                if ($salary_group != "時給") {
                    $count_group_1++;
                    $hp = '時給' . $count_group_1;
                    $salary_detail = [];
                    $salary_detail['salary_seq'] = $seq;
                    $salary_detail['salary_group'] = AppConst::SALARY_GROUP_1;
                    $salary_detail['item_name'] = $salary_group;
                    if ($count_group_1 < AppConst::COUNT_HOUR_SALARY) {
                        $salary_detail['hour'] = $values[$salary_group];
                    } else {
                        $salary_detail['hour'] = null;
                    }
                    if ($count_group_1 < AppConst::COUNT_HOUR_SALARY) {
                        $salary_detail['hour_payment'] = $values[$hp];
                    } else {
                        $salary_detail['hour_payment'] = null;
                    }
                    if ($count_group_1 < AppConst::COUNT_HOUR_SALARY) {
                        $salary_detail['amount'] = (float)$values[$salary_group] * (int)$values[$hp];
                    } else {
                        $salary_detail['amount'] = (int)$values[$salary_group];
                    }
                    array_push($salary_details, $salary_detail);
                    $seq++;
                }
            }

            // 控除
            foreach ($salary_group_2 as $salary_group) {
                $salary_detail = [];
                $salary_detail['salary_seq'] = $seq;
                $salary_detail['salary_group'] = AppConst::SALARY_GROUP_2;
                $salary_detail['item_name'] = $salary_group;
                $salary_detail['hour'] = null;
                $salary_detail['hour_payment'] = null;
                $salary_detail['amount'] = (int)$values[$salary_group];
                array_push($salary_details, $salary_detail);
                $seq++;
            }

            // その他
            foreach ($salary_group_3 as $salary_group) {
                $salary_detail = [];
                $salary_detail['salary_seq'] = $seq;
                $salary_detail['salary_group'] = AppConst::SALARY_GROUP_3;
                $salary_detail['item_name'] = $salary_group;
                $salary_detail['hour'] = null;
                $salary_detail['hour_payment'] = null;
                $salary_detail['amount'] = (int)$values[$salary_group];
                array_push($salary_details, $salary_detail);
                $seq++;
            }

            // 合計
            $salary_detail = [];
            $salary_detail['salary_seq'] = $seq;
            $salary_detail['salary_group'] = AppConst::SALARY_GROUP_4;
            $salary_detail['item_name'] = $salary_group_4[0];
            $salary_detail['hour'] = null;
            $salary_detail['hour_payment'] = null;
            $salary_detail['amount'] = (int)$values[$salary_group_4[0]];
            array_push($salary_details, $salary_detail);
        }

        // 現在日時を取得
        $now = Carbon::now();

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
