<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ReadDataValidateException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use App\Models\CodeMaster;
use App\Models\YearlySchedule;
use App\Models\YearlySchedulesImport;
use App\Consts\AppConst;
use App\Http\Controllers\Traits\CtrlFileTrait;
use App\Http\Controllers\Traits\CtrlCsvTrait;
use App\Libs\AuthEx;

/**
 * 年度スケジュール取込 - コントローラ
 */
class YearScheduleImportController extends Controller
{
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
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // 校舎名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        $query = YearlySchedulesImport::query();
        $yeary_schedules_import = $query
            ->select(
                'yearly_schedules_import_id as id',
                'school_year',
                'import_date',
                'import_state',
                'room_names.room_name as room_name',
                'mst_codes.name as import_state_name',
            )
            // 校舎名の取得
            ->joinSub($room_names, 'room_names', function ($join) {
                $join->on('campus_cd', '=', 'room_names.code');
            })
            // 取込状態取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('import_state', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_20);
            })
            ->orderBy('school_year', 'desc')
            ->orderBy('campus_cd', 'asc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $yeary_schedules_import);
    }

    //==========================
    // 取込
    //==========================

    /**
     * 初期画面(一覧)
     *
     * @return view
     */
    public function index()
    {
        // 教室管理者の場合は画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        return view('pages.admin.year_schedule_import', [
            'rules' => $this->rulesForInput()
        ]);
    }

    // 取り込み画面
    public function import($id)
    {
        // 教室管理者の場合は画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // IDのバリデーション
        $this->validateIds($id);

        // 校舎名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        $query = YearlySchedulesImport::query();
        $yeary_schedules_import = $query
            ->where('yearly_schedules_import_id', $id)
            ->select(
                'yearly_schedules_import_id as id',
                'school_year',
                'campus_cd',
                'import_date',
                'room_names.room_name as room_name',
            )
            // 校舎名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('campus_cd', '=', 'room_names.code');
            })
            ->firstOrFail();

        $editData = [
            'yearly_schedules_import_id' => $yeary_schedules_import->id,
            'campus_cd' => $yeary_schedules_import->campus_cd,
            'school_year' => $yeary_schedules_import->school_year,
        ];

        return view('pages.admin.year_schedule_import-import', [
            'rules' => $this->rulesForInput(),
            'school_year' => $yeary_schedules_import->school_year,
            'campus_cd' => $yeary_schedules_import->campus_cd,
            'editData' => $editData,
            'room_name' => $yeary_schedules_import->room_name,
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
        // 教室管理者の場合、処理を行わない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // アップロードされたかチェック(アップロードされた場合は該当の項目にファイル名をセットする)
        $this->fileUploadSetVal($request, 'upload_file');

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // アップロード先(アップ先は用途ごとに分ける)
        $uploadDir = config('appconf.upload_dir_year_schedule_import') . date("YmdHis");

        // アップロードファイルの保存
        $path = $this->fileUploadSave($request, $uploadDir, 'upload_file');

        $school_year = $request['school_year'];
        $campus_cd = $request['campus_cd'];
        $yearly_schedules_import_id = $request['yearly_schedules_import_id'];
        $datas = [];

        try {
            // CSVデータの読み込み
            $datas = $this->readData($path);
            $datas = $datas;

            if (empty($datas)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(データ件数不正)");
            }
        } catch (ReadDataValidateException  $e) {
            // 通常は事前にバリデーションするのでここはありえないのでエラーとする
            throw $e;
        }

        try {
            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($datas, $school_year, $campus_cd, $yearly_schedules_import_id) {

                // 元々のデータを削除
                YearlySchedule::query()
                    ->where('school_year', $school_year)
                    ->where('campus_cd', $campus_cd)
                    ->forceDelete();

                // コードマスタから曜日を取得
                $week = CodeMaster::select('code', 'name')
                    ->where('data_type', AppConst::CODE_MASTER_16)
                    ->orderby('code')
                    ->get()
                    ->keyBy('name');

                // スケジュール情報テーブルの登録（Insert）
                foreach ($datas as $data) {

                    $yearlySchedule = new YearlySchedule;
                    $yearlySchedule['school_year'] = $school_year;
                    $yearlySchedule['campus_cd'] = $campus_cd;
                    $yearlySchedule['lesson_date'] = $data['年月日'];
                    $yearlySchedule['day_cd'] = $week[$data['曜日']]->code;
                    $yearlySchedule['date_kind'] = $data['期間区分コード'];
                    $yearlySchedule['school_month'] = $data['月度'];
                    $yearlySchedule['week_count'] = $data['週数'];

                    $yearlySchedule->save();
                }

                $query = YearlySchedulesImport::query();
                $yearlyScheduleImport = $query
                    ->where('yearly_schedules_import_id', $yearly_schedules_import_id)
                    ->firstOrFail();

                // 年間予定取込
                $yearlyScheduleImport->import_state = AppConst::CODE_MASTER_20_1;
                $yearlyScheduleImport->import_date = now();
                $yearlyScheduleImport->save();
            });
        } catch (\Exception  $e) {
            // この時点では補足できないエラーとして、詳細は返さずエラーとする
            Log::error($e);
            return $this->illegalResponseErr();
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
            // CSVの中身の読み込みとバリデーション
            $this->readData($path);
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
        $csvHeaders = [
            '年月日',
            '曜日',
            '期間区分',
            '期間区分コード',
            '月度',
            '週数'
        ];
        $headers = [];

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
                        . "(ヘッダ行不正)");
                }
                continue;
            }

            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(データ列数不正)");
            }

            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $rules = [];
            $rules += ['年月日' => ['required', 'date_format:Y/m/d,Y/n/j']];
            $rules += ['曜日' => ['required', 'max:1', 'regex:/^(月|火|水|木|金|土|日\d*)$/']];
            $rules += ['期間区分' => ['string', 'max:50']];
            $rules += ['期間区分コード' => ['required', 'max:1', 'regex:/^([0-3]|[9]\d*)$/']];
            $rules += ['月度' => ['required', 'max:2', 'regex:/^([0-9]|[1][0-2]\d*)$/']];
            $rules += ['週数' => ['required', 'max:1', 'regex:/^(0|[0-4]\d*)$/']];

            // バリデーションルールチェック
            $validator = Validator::make($values, $rules);
            if ($validator->fails()) {
                $errCol = "";
                if ($validator->errors()->has('年月日')) {
                    $errCol = "年月日=" . $values['年月日'];
                } else if ($validator->errors()->has('曜日')) {
                    $errCol = "曜日=" . $values['曜日'];
                } else if ($validator->errors()->has('期間区分')) {
                    $errCol =  "期間区分=" . $values['期間区分'];
                } else if ($validator->errors()->has('期間区分コード')) {
                    $errCol =  "期間区分コード=" . $values['期間区分コード'];
                } else if ($validator->errors()->has('月度')) {
                    $errCol =  "月度=" . $values['月度'];
                } else if ($validator->errors()->has('週数')) {
                    $errCol =  "週数=" . $values['週数'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "データ項目不正( " . $i + 1 . "行目 年月日=" . $values['年月日'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // リストに保持
            $datas[] = $values;
        }

        return $datas;
    }

    /**
     * バリデーションルールを取得(取込用)
     *
     * @return array ルール
     */
    private function rulesForInput()
    {
        $rules = array();

        //-----------------------------
        // ファイルアップロード
        //-----------------------------

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
