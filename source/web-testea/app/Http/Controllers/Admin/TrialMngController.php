<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Models\ExtGenericMaster;
use App\Consts\AppConst;
use App\Exceptions\ReadDataValidateException;
use App\Models\Notice;
use App\Models\CodeMaster;
use App\Models\ExtStudentKihon;
use App\Models\ExtTrialMaster;
use App\Models\NoticeDestination;
use App\Models\TrialApply;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Traits\FuncEventTrialTrait;

/**
 * 模試管理 - コントローラ
 */
class TrialMngController extends Controller
{

    // 機能共通処理：模試・イベント
    use FuncEventTrialTrait;

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
        // 学年プルダウン
        $cls = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

        return view('pages.admin.trial_mng', [
            'rules' => $this->rulesForSearch(null),
            'cls' => $cls,
        ]);
    }

    /**
     * バリデーション(検索用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForSearch(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForSearch($request));
        return $validator->errors();
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {

        // MEMO: 模試マスタそのものは教室管理者でも全て見れるのでガードは不要

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = ExtTrialMaster::query();

        $query->SearchName($form);
        $query->SearchClsCd($form);
        $query->SearchTrialDateFrom($form);
        $query->SearchTrialDateTo($form);

        // データを取得
        $extTrialMasters = $query
            // MEMO: 重要：表示に使用する項目のみ取得
            // パスワードのような重要情報は返却しない。
            ->select(
                'ext_trial_master.tmid',
                'ext_trial_master.name',
                'ext_generic_master.name1 as cls',
                'ext_trial_master.trial_date',
                DB::raw('count(trial_apply.tmid) as count'),
                'ext_trial_master.created_at'
            )
            // 学年名称の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'ext_trial_master.cls_cd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_112);
            })
            // 模試申込者 未対応件数の取得 2021/09/08 追加
            ->sdLeftJoin(TrialApply::class, function ($join) {
                $join->on('trial_apply.tmid', '=', 'ext_trial_master.tmid')
                    ->where('trial_apply.apply_state', '=', AppConst::CODE_MASTER_1_0);
            })
            ->groupBy('ext_trial_master.tmid','ext_trial_master.name',
                'ext_trial_master.tmid','ext_generic_master.name1',
                'ext_trial_master.trial_date','ext_trial_master.created_at')
            ->orderBy('ext_trial_master.trial_date', 'desc')
            ->orderBy('ext_trial_master.created_at', 'desc')
            ->orderBy('ext_trial_master.tmid', 'desc');

        // MEMO: ページネータで返却
        return $this->getListAndPaginator($request, $extTrialMasters);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getData(Request $request)
    {

        // MEMO: 模試マスタそのものは教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $id = $request->input('id');

        // クエリを作成
        $query = ExtTrialMaster::query();

        $trialMngDtl = $query
            ->where('ext_trial_master.tmid', $id)
            ->select(
                'ext_trial_master.name',
                'ext_generic_master.name1 as cls',
                'ext_trial_master.trial_date',
                'ext_trial_master.start_time',
                'ext_trial_master.end_time'
            )
            // 学年名称の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'ext_trial_master.cls_cd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_112);
            })
            ->firstOrFail();

        return $trialMngDtl;
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch(?Request $request)
    {
        // 独自バリデーション: リストのチェック `学年`
        $validationClsList =  function ($attribute, $value, $fail) {

            // 学年プルダウン
            $cls = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

            if (!isset($cls[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        $rules = array();

        $rules += ExtTrialMaster::fieldRules('name');
        $rules += ExtTrialMaster::fieldRules('cls_cd', [$validationClsList]);

        // 開催日 項目のバリデーションルールをベースにする
        $ruleTrialDate = ExtTrialMaster::getFieldRule('trial_date');

        // FromとToの大小チェックバリデーションを追加(Fromが存在する場合のみ)
        $validateFromTo = [];
        $keyFrom = 'trial_date_from';
        if (isset($request[$keyFrom]) && filled($request[$keyFrom])) {
            $validateFromTo = ['after_or_equal:' . $keyFrom];
        }

        // 日付From・Toのバリデーションの設定
        $rules += ['trial_date_from' => $ruleTrialDate];
        $rules += ['trial_date_to' => array_merge($validateFromTo, $ruleTrialDate)];

        return $rules;
    }

    //==========================
    // 登録・編集
    //==========================

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        return view('pages.admin.trial_mng-new', [
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

        // MEMO: 模試マスタそのものは教室管理者でも全て見れるのでガードは不要

        // アップロードされたかチェック(アップロードされた場合は該当の項目にファイル名をセットする)
        $this->fileUploadSetVal($request, 'upload_file');

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // アップロード先(アップ先は用途ごとに分ける)
        $uploadDir = config('appconf.upload_dir_trial_mng') . date("YmdHis");

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
                // テーブル名取得
                $table = (new ExtTrialMaster())->getTable();
                // 一旦テーブルをクリア(トランザクションのため、truncateではなくdeleteにした)
                DB::table($table)->delete();
                // まとめて登録
                DB::table($table)->insert($datas);
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
            "tmid", "name", "symbol", "cls_cd", "price", "trial_date", "start_time",
            "end_time", "disp_flg", "updtime", "upduser"
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
                // バリデーションルール(一部、CSV向けのバリデーションを読み込む)
                ExtTrialMaster::fieldRules('tmid', ['required'])
                    + ExtTrialMaster::fieldRules('name', ['required'])
                    + ExtTrialMaster::fieldRules('symbol', ['required'])
                    + ExtTrialMaster::fieldRules('cls_cd', ['required'])
                    + ExtTrialMaster::fieldRules('price', ['required'])
                    + ExtTrialMaster::fieldRules('trial_date', ['required'], '_csv')
                    + ExtTrialMaster::fieldRules('start_time', ['required'], '_csv')
                    + ExtTrialMaster::fieldRules('end_time', ['required'], '_csv')
                    + ExtTrialMaster::fieldRules('disp_flg', ['required'])
                    + ExtTrialMaster::fieldRules('updtime', ['required'], '_csv')
            );

            if ($validator->fails()) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(データ項目不正)");
            }

            // CSV列に対して、挿入先テーブルのオブジェクトに変換
            // 今回は読み込んだファイルの列項目がほぼテーブルと同じなので、不足項目を追加
            // 日時をセットする
            $values['created_at'] = $now;
            $values['updated_at'] = $now;
            $values['deleted_at'] = null;

            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // 不要な項目を削除
            unset($values['upduser']);

            // 今年度の終了日と前年度の開始日を取得
            $prev = new Carbon($this->dtGetFiscalDate('prev', 'start'));
            $present = new Carbon($this->dtGetFiscalDate('present', 'end'));

            // 2年度分のデータを取り込む
            if (Carbon::parse($values['trial_date'])->between($prev, $present)) {
                // リストに保持しておく
                $datas[] = $values;
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

        // 独自バリデーション: ファイル名のチェック
        $validationFileName = function ($attribute, $value, $fail) {

            // ファイル名の先頭をチェック
            // 想定：模試マスタ_20201124114040.zip
            $fileName = config('appconf.upload_file_name_trial_mng');
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

    //==========================
    // 模試申込者一覧
    //==========================

    /**
     * 一覧画面
     *
     * @param int $tmid 模試ID
     * @return view
     */
    public function entry($tmid)
    {
        // IDのバリデーション
        $this->validateIds($tmid);

        // MEMO: 模試マスタそのものは教室管理者でも全て見れるのでガードは不要

        // 模試詳細の取得
        $query = ExtTrialMaster::query();
        $trial = $query
            ->select(
                'ext_trial_master.tmid',
                'ext_trial_master.name',
                'ext_generic_master.name1 AS cls',
                'ext_trial_master.trial_date'
            )
            // 学年名称の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'ext_trial_master.cls_cd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_112);
            })
            // 模試IDで絞り込み
            ->where('ext_trial_master.tmid', '=', $tmid)
            ->firstOrFail();

        return view(
            'pages.admin.trial_mng-entry',
            [
                'trial' => $trial
            ]
        );
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function searchEntry(Request $request)
    {

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'tmid');

        // 模試IDを取得
        $tmid = $request->input('tmid');

        $query = TrialApply::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithSid());

        // 一覧取得
        $trialApply = $query
            ->select(
                'trial_apply.tmid',
                'trial_apply.trial_apply_id',
                'trial_apply.apply_time',
                'ext_student_kihon.name',
                'code_master.name as apply_state',
                'trial_apply.created_at'
            )
            // 氏名
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('ext_student_kihon.sid', '=', 'trial_apply.sid');
            })
            // ステータスの条件
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('code_master.code', '=', 'trial_apply.apply_state')
                    ->where('code_master.data_type', '=', AppConst::CODE_MASTER_3);
            })
            // 模試IDで絞り込み
            ->where('trial_apply.tmid', '=', $tmid)
            ->orderBy('trial_apply.apply_time', 'desc')
            ->orderBy('trial_apply.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $trialApply);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return arrray 詳細データ
     */
    public function getDataEntry(Request $request)
    {

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl":
                //----------
                // 詳細
                //----------
                // IDのバリデーション
                $this->validateIdsFromRequest($request, 'trial_apply_id');

                // 模試申込ID
                $trialApplyId = $request->input('trial_apply_id');

                // 生徒情報
                $query = TrialApply::query();

                // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                $query->where($this->guardRoomAdminTableWithSid());

                $trialApply = $query
                    ->select(
                        'trial_apply.tmid',
                        'trial_apply.apply_time',
                        'ext_student_kihon.name',
                        'code_master.name as apply_state',
                        'ext_trial_master.name as trial_name'
                    )
                    // 氏名
                    ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                        $join->on('ext_student_kihon.sid', '=', 'trial_apply.sid');
                    })
                    // ステータスの条件
                    ->sdLeftJoin(CodeMaster::class, function ($join) {
                        $join->on('code_master.code', '=', 'trial_apply.apply_state')
                            ->where('code_master.data_type', '=', AppConst::CODE_MASTER_3);
                    })
                    // イベント名の取得
                    ->sdLeftJoin(ExtTrialMaster::class, 'ext_trial_master.tmid', '=', 'trial_apply.tmid')
                    // PKで1件取得
                    ->where('trial_apply.trial_apply_id', '=', $trialApplyId)
                    ->firstOrFail();

                return [
                    'apply_time' => $trialApply->apply_time,
                    'name' => $trialApply->name,
                    'trial_name' => $trialApply->trial_name,
                    'apply_state' => $trialApply->apply_state
                ];

            case "#modal-dtl-output":
                //----------
                // 一覧出力
                //----------

                return [];
            default:
                // 該当しない場合
                $this->illegalResponseErr();
        }
    }

    /**
     * モーダル処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function execModalEntry(Request $request)
    {

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'tmid');

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl-output":
                //--------------
                // 一覧出力
                //--------------

                // 模試IDを種痘
                $tmid = $request['tmid'];

                // 模試詳細の取得
                $trial = ExtTrialMaster::select(
                    'tmid',
                    'name',
                    'trial_date',
                    'ext_generic_master.name1 AS cls',
                )
                    // 学年名称の取得
                    ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                        $join->on('ext_generic_master.code', '=', 'ext_trial_master.cls_cd')
                            ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_112);
                    })
                    ->where('tmid', $tmid)
                    ->firstOrFail();

                // 一覧を取得(検索と同じ)
                $query = TrialApply::query();

                // 教室管理者の場合、自分の教室コードのみにガードを掛ける
                $query->where($this->guardRoomAdminTableWithSid());

                // 一覧取得
                $trialApply = $query
                    ->select(
                        'trial_apply.apply_time',
                        'trial_apply.sid',
                        'ext_student_kihon.name',
                        'code_master.name AS state',
                        'trial_apply.created_at'
                    )
                    // 氏名
                    ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                        $join->on('ext_student_kihon.sid', '=', 'trial_apply.sid');
                    })
                    // ステータス
                    ->sdLeftJoin(CodeMaster::class, function ($join) {
                        $join->on('trial_apply.apply_state', '=', 'code_master.code')
                            ->where('code_master.data_type', AppConst::CODE_MASTER_3);
                    })
                    // 模試IDで絞り込み
                    ->where('trial_apply.tmid', '=', $tmid)
                    ->orderBy('trial_apply.apply_time', 'desc')
                    ->orderBy('trial_apply.created_at', 'desc')
                    ->get();

                //---------------------
                // CSV出力内容を配列に保持
                //---------------------
                $arrayCsv = [];

                // 模試詳細
                $arrayCsv[] = [Lang::get('message.file.trial_entry_output.detail.tmid'), $trial->tmid];
                $arrayCsv[] = [Lang::get('message.file.trial_entry_output.detail.name'), $trial->name];
                $arrayCsv[] = [Lang::get('message.file.trial_entry_output.detail.cls'), $trial->cls];
                $arrayCsv[] = [Lang::get('message.file.trial_entry_output.detail.trialDate'), $trial->trial_date->format('Y/m/d')];

                // ヘッダ
                $arrayCsv[] = Lang::get(
                    'message.file.trial_entry_output.header'
                );

                // 生徒詳細
                foreach ($trialApply as $data) {
                    // 一行出力
                    $arrayCsv[] = [
                        $data->apply_time->format('Y/m/d'),
                        $data->sid,
                        $data->name,
                        $data->state
                    ];
                }

                //---------------------
                // ファイル名の取得
                //---------------------

                $filename = Lang::get(
                    'message.file.trial_entry_output.name',
                    [
                        'trialDate' => $trial->trial_date->format('Ymd'),
                        'trialName' => $trial->name,
                        'cls' => $trial->cls,
                        'outputDate' => date("Ymd")
                    ]
                );

                // ファイルダウンロードヘッダーの指定
                $this->fileDownloadHeader($filename, true);

                //-----------------------------------------------------------
                // ステータスが「未対応」のレコードを一括で「受付済み」に変更し、
                // お知らせ通知を行う。
                //-----------------------------------------------------------

                // 複数の更新のためトランザクション
                DB::transaction(function () use ($request) {

                    // 模試IDを取得
                    $tmid = $request['tmid'];

                    //--------------------------
                    // 申し込み状態が
                    // 未対応の情報を更新前に取得
                    //--------------------------

                    $query = TrialApply::query();

                    // 教室管理者の場合、自分の教室コードのみにガードを掛ける
                    $query->where($this->guardRoomAdminTableWithSid());

                    // 一覧を取得
                    $notCompatibles = $query->select(
                        'tmid',
                        'sid'
                    )
                        ->where('tmid', $tmid)
                        // 未対応を取得
                        ->where('apply_state', AppConst::CODE_MASTER_3_0)
                        ->get();

                    // 存在しない場合は処理終了
                    if (count($notCompatibles) <= 0) {
                        return;
                    }

                    //--------------------------
                    // 申し込み状態を対応済みに変更
                    //--------------------------

                    // ◆一覧で申込状態が0：未対応のレコードについて、申込状態を1：受付済にupdateする。
                    $query = TrialApply::query();

                    // 教室管理者の場合、自分の教室コードのみにガードを掛ける
                    $query->where($this->guardRoomAdminTableWithSid());

                    // 複数行なのでupdateで対応
                    $query->where('tmid', $tmid)
                        // 未対応が対象
                        ->where('apply_state', AppConst::CODE_MASTER_3_0)
                        ->update([
                            'apply_state' => AppConst::CODE_MASTER_3_1
                        ]);

                    //-------------------------
                    // お知らせメッセージの登録
                    //-------------------------

                    // 模試名と受験日の取得
                    $trial = ExtTrialMaster::select('name', 'trial_date')
                        ->where('tmid', $tmid)
                        ->firstOrFail();

                    // ◆お知らせ情報に、お知らせ種別=1、模試・イベントID=当該模試IDのお知らせをinsertする。
                    $notice = new Notice;

                    // タイトルと本文(Langから取得する)
                    $notice->title = Lang::get('message.notice.trial_entry_acceptance.title');
                    $notice->text = Lang::get(
                        'message.notice.trial_entry_acceptance.text',
                        // 動的に表示(模試名と開催日)
                        [
                            'trialName' => $trial->name,
                            'trialDate' => $trial->trial_date->format('Y/m/d')
                        ]
                    );

                    // お知らせ種別
                    $notice->notice_type = AppConst::CODE_MASTER_14_4;
                    // 模試ID
                    $notice->tmid_event_id = $tmid;
                    // 事務局ID
                    $account = Auth::user();
                    $notice->adm_id = $account->account_id;
                    $notice->roomcd = $account->roomcd;

                    // 保存
                    $notice->save();

                    //-------------------------
                    // お知らせ宛先の登録
                    //-------------------------

                    // ◆受付処理を行ったレコード毎に、お知らせ宛先情報に以下の条件でinsertする。
                    foreach ($notCompatibles as $index => $notCompatible) {

                        // ・お知らせID=上記で作成したお知らせのお知らせID
                        // ・宛先連番=1 からのインクリメント
                        // ・宛先種別=2
                        // ・生徒No.=各レコードの生徒No.

                        $noticeDestination = new NoticeDestination;

                        // 先に登録したお知らせIDをセット
                        $noticeDestination->notice_id = $notice->notice_id;
                        // 宛先連番
                        $noticeDestination->destination_seq = $index + 1;
                        // 宛先種別（生徒）
                        $noticeDestination->destination_type = AppConst::CODE_MASTER_15_2;
                        // 生徒No
                        $noticeDestination->sid = $notCompatible->sid;

                        // 保存
                        $noticeDestination->save();
                    }
                });

                // CSVを出力する
                $this->outputCsv($arrayCsv);

                return;

            default:
                // 該当しない場合
                $this->illegalResponseErr();
        }
    }

    //==========================
    // 編集
    //==========================

    /**
     * 編集画面
     *
     * @param int $tmid 模試ID
     * @param int $trialApplyId 模試申込ID
     * @return view
     */
    public function entryEdit($tmid, $trialApplyId)
    {

        // IDのバリデーション
        $this->validateIds($tmid, $trialApplyId);

        // ステータスリストを取得
        $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_3);

        // 申し込み情報を取得する(PKでユニークに取る・更新前情報分も項目取得)
        $trialApply = TrialApply::select(
            '*',
            // 生徒名の取得
            'ext_student_kihon.name',
            // 生徒の学年を取得
            'ext_student_kihon.cls_cd',
        )
            // 生徒基本情報とJOIN
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('trial_apply.sid', '=', 'ext_student_kihon.sid');
            })
            ->where('trial_apply.trial_apply_id', $trialApplyId)
            // キーは上記なので、上記だけで絞れるが、URLの都合上、tmidも条件として入れる
            // http://localhost:8000/trial_mng/entry/20/edit/5
            // このチェックをしないと20の部分が何でも良くなってしまうため
            ->where('trial_apply.tmid', $tmid)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 生徒の学年から模試名リストを取得
        $trials = $this->getMenuOfTrial($trialApply->cls_cd);

        return view(
            'pages.admin.trial_mng-entry-edit',
            [
                'tmid' => $tmid,
                'trials' => $trials,
                'states' => $states,
                'editData' => $trialApply,
                'rules' => $this->rulesForInputEntry(null),
            ]
        );
    }

    /**
     * 編集処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function updateEntry(Request $request)
    {
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInputEntry($request))->validate();

        // MEMO: 必ず登録する項目のみに絞る。
        $form = $request->only(
            'tmid',
            'apply_state',
            'apply_time'
        );

        // 対象データを取得(PKでユニークに取る)
        $trialApply = TrialApply::where('trial_apply_id', $request['trial_apply_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $trialApply->fill($form)->save();

        return;
    }

    /**
     * 削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function deleteEntry(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'trial_apply_id');

        // Formを取得
        $form = $request->all();

        // 対象データを取得(IDでユニークに取る)
        $trialApply = TrialApply::where('trial_apply_id', $form['trial_apply_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 削除
        $trialApply->delete();

        return;
    }

    /**
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInputEntry(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInputEntry($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInputEntry(?Request $request)
    {

        // 独自バリデーション: リストのチェック 模試名
        $validationTrialList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request || !(isset($request['trial_apply_id']))) {
                return;
            }

            // 選択した模試名が申込者の学年を対象としたものか確認する
            $id = $request['trial_apply_id'];
            $cls = $this->getClsByTrialApplyId($id);
            $trials = $this->getMenuOfTrial($cls);
            if (!isset($trials[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {

            // ステータスリストを取得
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_3);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        // 独自バリデーション: 変更後のキーが存在しないかチェック
        $validationKey = function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            // 対象データを取得(PKでユニークに取る)
            $trialApply = TrialApply::where('trial_apply_id', $request['trial_apply_id'])
                ->firstOrFail();

            // 別な模試に同じ生徒が存在するかチェック
            $exists = TrialApply::where('tmid', $request['tmid'])
                ->where('sid', $trialApply->sid)
                // 同じ模試だったらチェックはしない
                ->where('tmid', '!=', $trialApply->tmid)
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += TrialApply::fieldRules('tmid', ['required', $validationKey, $validationTrialList]);
        $rules += TrialApply::fieldRules('trial_apply_id', ['required']);
        $rules += TrialApply::fieldRules('apply_state', ['required', $validationStateList]);
        $rules += TrialApply::fieldRules('apply_time', ['required']);

        return $rules;
    }

    /**
     * 模試名リストの取得
     *
     * @param string $cls 学年コード
     * @return array 模試名リスト
     */
    private function getMenuOfTrial($cls = null)
    {

        $query = ExtTrialMaster::query();
        $query->select('tmid as code', 'name as value');
        if (!empty($cls)) {
            $query->where('cls_cd', '=', $cls);
        }
        $query->orderBy('trial_date', 'desc');

        return $query->get()->keyBy('code');
    }

    /**
     * 模試申込者の学年取得
     * 
     * @param integer $id trial_apply_id
     * @return string 学年コード
     */
    private function getClsByTrialApplyId($id)
    {

        $query = TrialApply::query();
        $trial_apply = $query->select('ext_student_kihon.cls_cd')
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('trial_apply.sid', '=', 'ext_student_kihon.sid');
            })
            ->where('trial_apply.trial_apply_id', '=', $id)
            ->firstOrFail();

        return $trial_apply->cls_cd;
    }
}
