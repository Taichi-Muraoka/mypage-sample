<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ReadDataValidateException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Models\BatchMng;
use App\Models\AdminUser;
use App\Models\CodeMaster;
use App\Models\YearlySchedule;
use App\Models\YearlySchedulesImport;
use App\Consts\AppConst;
use App\Http\Controllers\Traits\CtrlFileTrait;
use App\Http\Controllers\Traits\CtrlCsvTrait;

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
                'room_names.room_name as room_name',
                'mst_codes.name as import_state',
            )
            // 校舎名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
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
        return view('pages.admin.year_schedule_import', [
            'rules' => $this->rulesForInput()
        ]);
    }

    // 取り込み画面
    public function import($id)
    {
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
                'import_date',
                'room_names.room_name as room_name',
            )
            // 校舎名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('campus_cd', '=', 'room_names.code');
            })
            ->firstOrFail();

        return view('pages.admin.year_schedule_import-import', [
            'rules' => $this->rulesForInput(),
            'school_year' => $yeary_schedules_import->school_year,
            'room_name' => $yeary_schedules_import->room_name
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

        // アップロードされたかチェック(アップロードされた場合は該当の項目にファイル名をセットする)
        $this->fileUploadSetVal($request, 'upload_file');

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // アップロード先(アップ先は用途ごとに分ける)
        $uploadDir = config('appconf.upload_dir_year_schedule_import') . date("YmdHis");

        // アップロードファイルの保存
        $path = $this->fileUploadSave($request, $uploadDir, 'upload_file');
        $base_path = base_path();

        // 実行者のアカウントIDを取得
        $account_id = Auth::user()->account_id;

        // 非同期実行する
        if (strpos(PHP_OS, 'WIN') !== false) {
            // Windows
            $command = 'start /b /d ' . base_path() . ' php artisan command:YearScheduleImport ' . $path . ' ' . $account_id;
            $fp = popen($command, 'r');
            pclose($fp);
        } else {
            // Linux
            $command = "cd {$base_path} && php artisan command:YearScheduleImport {$path} {$account_id} > /dev/null &";
            exec($command);
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
        // CSVのヘッダ項目
        $csvHeaders = [
            'lesson_date',
            'day_cd',
            'date_kind_name',
            'date_kind',
            'school_month',
            'week_count'
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
                // if ($headers !== $csvHeaders) {
                //     throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                //         . "(" . config('appconf.upload_file_csv_name_T01') . "：ヘッダ行不正)");
                // }
                continue;
            }

            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_T01') . "：データ列数不正)");
            }

            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make(
                // 対象
                $values,
                // バリデーションルール
                YearlySchedule::fieldRules('school_year', ['required'])
                    + YearlySchedule::fieldRules('campus_cd', ['required'])
                    + YearlySchedule::fieldRules('lesson_date', ['required'])
                    + YearlySchedule::fieldRules('day_cd', ['required'])
                    + YearlySchedule::fieldRules('date_kind', ['required'])
                    + YearlySchedule::fieldRules('school_month', ['required'])
                    + YearlySchedule::fieldRules('week_count', ['required'])
            );

            if ($validator->fails()) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(" . config('appconf.upload_file_csv_name_T01') . "：データ項目不正)");
            }

            // MEMO: スケジュール情報は期間が絞られている前提とし授業日のチェックを行わない

            // 10行目までチェックする
            if ($i === 10) {
                break;
            }
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
            // 想定：年次スケジュール情報_20201124114040.zip
            $fileName = config('appconf.upload_file_name_year_schedule_import');
            if (!preg_match('/^' . $fileName . '[0-9]{14}.zip$/', $value)) {
                return $fail(Lang::get('validation.invalid_file'));
            }
        };

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
