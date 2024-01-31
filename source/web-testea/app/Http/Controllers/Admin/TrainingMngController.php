<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Libs\AuthEx;
use App\Models\Tutor;
use App\Models\TrainingBrowse;
use App\Models\TrainingContent;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FuncTrainingTrait;
use Carbon\Carbon;

/**
 * 研修管理 - コントローラ
 */
class TrainingMngController extends Controller
{
    // 機能共通処理：研修
    use FuncTrainingTrait;

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
        // 形式プルダウン
        $trainingType = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_12);

        return view('pages.admin.training_mng', [
            'rules' => $this->rulesForSearch(),
            'training_type' => $trainingType,
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
        $validator = Validator::make($request->all(), $this->rulesForSearch());
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
        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch())->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = TrainingContent::query();

        // 検索条件の指定
        $query->SearchTrnType($form);
        $query->SearchRegistTime($form);
        $query->SearchText($form);

        // データを取得
        $trainings = $query
            ->select(
                'trn_id',
                'text',
                'regist_time',
                'release_date',
                'limit_date',
                'mst_codes.name as trn_type',
                'training_contents.created_at'
            )
            // 形式
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_codes.code', '=', 'training_contents.trn_type')
                    ->where('mst_codes.data_type', '=', AppConst::CODE_MASTER_12);
            })
            ->orderBy('training_contents.release_date', 'desc')
            ->orderBy('training_contents.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $trainings);
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {

        // 独自バリデーション: リストのチェック 形式
        $validationTrainingTypeList =  function ($attribute, $value, $fail) {

            // 形式プルダウン
            $trainingType = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_12);
            if (!isset($trainingType[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        $rules = array();

        $rules += TrainingContent::fieldRules('trn_type', [$validationTrainingTypeList]);
        $rules += TrainingContent::fieldRules('text');
        $rules += TrainingContent::fieldRules('regist_time');

        return $rules;
    }

    //==========================
    // 研修閲覧状況
    //==========================

    /**
     * 状況確認画面
     *
     * @param integer trnId 研修ID
     * @return view
     */
    public function state($trnId)
    {
        // IDのバリデーション
        $this->validateIds($trnId);

        $query = TrainingContent::query();
        $training = $query
            ->select(
                'trn_id',
                'text',
                'regist_time',
                'release_date',
                'limit_date',
                'mst_codes.name as trn_type'
            )
            // 形式
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_codes.code', '=', 'training_contents.trn_type')
                    ->where('mst_codes.data_type', '=', AppConst::CODE_MASTER_12);
            })
            // 条件
            ->where('training_contents.trn_id', $trnId)
            ->firstOrFail();

        return view(
            'pages.admin.training_mng-state',
            [
                'training' => $training
            ]
        );
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function searchState(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'trn_id');

        // 研修IDを取得
        $trnId = $request->input('trn_id');

        // クエリ取得
        $query = TrainingBrowse::query();

        // 講師の所属教室で絞り込み
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、強制的に校舎コードで検索する
            $account = Auth::user();
            $this->mdlWhereTidByRoomQuery($query, TrainingBrowse::class, $account->campus_cd);
        }

        // 一覧取得
        $trainingBrowse = $query
            ->select(
                'tutors.name',
                'training_browses.browse_time'
            )
            // 教師名の取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('tutors.tutor_id', '=', 'training_browses.tutor_id');
            })
            // 研修IDで絞り込み
            ->where('training_browses.trn_id', '=', $trnId)
            ->orderBy('training_browses.browse_time', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $trainingBrowse);
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
        // 形式プルダウン
        $trainingType = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_12);

        return view('pages.admin.training_mng-input', [
            'editData' => null,
            'rules' => $this->rulesForInput(null),
            'trainingType' => $trainingType,
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
        $this->fileUploadSetVal($request, 'file_doc');

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            // 現在日時を取得
            $now = Carbon::now();

            $form = $request->only(
                'trn_type',
                'text',
                'release_date',
                'limit_date'
            );

            // クエリ
            $traningContents = new TrainingContent;

            // 形式で判断
            $trnType = $request->input('trn_type');
            if (AppConst::CODE_MASTER_12_1 == $trnType) {

                // 資料(ファイル名)
                $traningContents->url = $request->input('file_doc');
            } else if (AppConst::CODE_MASTER_12_2 == $trnType) {

                // 動画URL
                $traningContents->url = $request->input('url');
            } else {
                return $this->illegalResponseErr();
            }

            // 登録
            $traningContents->regist_time = $now;
            $traningContents->fill($form)->save();

            // ファイルのアップロード
            if (AppConst::CODE_MASTER_12_1 == $trnType) {
                // アップロードファイルの保存(資料の場合)
                $uploadDir = config('appconf.upload_dir_training') . $traningContents->trn_id;
                $this->fileUploadSave($request, $uploadDir, 'file_doc');
            }
        });

        return;
    }

    /**
     * 編集画面
     *
     * @param int $trnId 研修ID
     * @return view
     */
    public function edit($trnId)
    {
        // IDのバリデーション
        $this->validateIds($trnId);

        // 形式プルダウン
        $trainingType = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_12);

        // IDから編集するデータを取得する
        $training = TrainingContent::select(
            'trn_id',
            'trn_type',
            'text',
            'url',
            'regist_time',
            'release_date',
            'limit_date'
        )
            ->where('trn_id', $trnId)
            ->firstOrFail();

        if (AppConst::CODE_MASTER_12_1 == $training->trn_type) {
            // 資料の場合、file_docに入れる
            $training['file_doc'] = $training->url;
            // URLは消す
            unset($training['url']);
        }

        return view('pages.admin.training_mng-input', [
            'editData' => $training,
            'rules' => $this->rulesForInput(null),
            'trainingType' => $trainingType,
        ]);
    }

    /**
     * 編集処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function update(Request $request)
    {
        // アップロードされたかチェック(アップロードされた場合は該当の項目にファイル名をセットする)
        $this->fileUploadSetVal($request, 'file_doc');

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            $form = $request->only(
                'trn_id',
                'trn_type',
                'text',
                'regist_time',
                'release_date',
                'limit_date'
            );

            // 対象データを取得(IDでユニークに取る)
            $traningContents = TrainingContent::where('trn_id', $form['trn_id'])
                ->firstOrFail();

            // 更新データの種別
            $trnType = $request->input('trn_type');

            // 資料用に保存先を設定から取得
            $trainingDir = config('appconf.upload_dir_training');
            // appフォルダのフルパスを取得
            $trainingPath = Storage::path($trainingDir);

            // 資料から動画へ変更になった場合、既存の資料ディレクトリを削除する。
            if ($traningContents->trn_type == AppConst::CODE_MASTER_12_1 && $trnType == AppConst::CODE_MASTER_12_2) {

                // 古い資料をディレクトリごと削除
                $deleteDir = $trainingPath . $traningContents->trn_id;
                if (File::isDirectory($deleteDir)) {
                    File::deleteDirectory($deleteDir);
                }
            }

            // 形式で判断
            if ($trnType == AppConst::CODE_MASTER_12_1) {

                // 資料(ファイル名)
                if ($this->fileUploadCheck($request, 'file_doc')) {
                    // ファイルがアップロードされた場合に更新
                    $traningContents->url = $request->input('file_doc');
                }
            } else if ($trnType == AppConst::CODE_MASTER_12_2) {

                // 動画URL
                $traningContents->url = $request->input('url');
            } else {
                return $this->illegalResponseErr();
            }

            // 登録
            $traningContents->fill($form)->save();

            // ファイルのアップロード
            if ($trnType == AppConst::CODE_MASTER_12_1 && $this->fileUploadCheck($request, 'file_doc')) {

                // 古い資料をディレクトリごと削除
                $deleteDir = $trainingPath . $traningContents->trn_id;
                if (File::isDirectory($deleteDir)) {
                    File::deleteDirectory($deleteDir);
                }

                // アップロードファイルの保存(資料の場合)
                $uploadDir = $trainingDir . $traningContents->trn_id;
                $this->fileUploadSave($request, $uploadDir, 'file_doc');
            }
        });

        return;
    }

    /**
     * 削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function delete(Request $request)
    {

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'trn_id');

        // Formを取得
        $trnId = $request->input('trn_id');

        // 対象データを取得(IDでユニークに取る)
        $training = TrainingContent::where('trn_id', $trnId)
            ->firstOrFail();

        DB::transaction(function () use ($trnId, $training) {
            // 削除
            $training->delete();
            // 閲覧履歴も削除
            TrainingBrowse::where('trn_id', '=', $trnId)->delete();
        });

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
        $this->fileUploadSetVal($request, 'file_doc');

        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInput($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        // 独自バリデーション: 研修種別
        $validationTrnType = function ($attribute, $value, $fail) use ($request) {

            if (
                $request->input('trn_type') != AppConst::CODE_MASTER_12_1 &&
                $request->input('trn_type') != AppConst::CODE_MASTER_12_2
            ) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 形式
        $validationTrainingTypeList =  function ($attribute, $value, $fail) {

            // 形式プルダウン
            $trainingType = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_12);
            if (!isset($trainingType[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 研修期限日は公開日以降であること
        $validationLimitDate = function ($attribute, $value, $fail) use ($request) {

            $release_date = $request->input('release_date');
            $limit_date = $request->input('limit_date');

            // 期限日はnullの場合があるので、その場合はスキップ
            if (!$limit_date) {
                return;
            }

            if (strtotime($limit_date) < strtotime($release_date)) {
                // 公開日以降でない場合エラー
                return $fail(Lang::get('validation.after_or_equal_release_date'));
            }
        };

        $rules = array();

        $rules += TrainingContent::fieldRules('trn_id');
        $rules += TrainingContent::fieldRules('trn_type', ['required', $validationTrnType, $validationTrainingTypeList]);
        $rules += TrainingContent::fieldRules('text', ['required']);
        $rules += TrainingContent::fieldRules('release_date', ['required']);
        $rules += TrainingContent::fieldRules('limit_date', [$validationLimitDate]);

        // 編集時のみ登録日を必須指定する
        if ($request && $request->input('trn_id')) {
            $rules += TrainingContent::fieldRules('regist_time', ['required']);
        } else {
            $rules += TrainingContent::fieldRules('regist_time');
        }

        if ($request && $request->input('trn_type') == AppConst::CODE_MASTER_12_1) {
            //-------
            // 資料
            //-------

            // URL 項目のバリデーションルールをベースにする
            $ruleUrl = TrainingContent::getFieldRule('url');
            $rules += ['file_doc' => array_merge($ruleUrl, ['required'])];

            // ファイルアップロードのチェック
            $rule = [
                // ファイル
                'file',
                // ファイルのタイプはチェックしない
            ];
            $rules += ['file_file_doc' => $rule];
        } else
        if ($request && $request->input('trn_type') == AppConst::CODE_MASTER_12_2) {
            //-------
            // 動画
            //-------
            $rules += TrainingContent::fieldRules('url', ['required', 'url']);
        } else {
            // 登録画面表示時、1000文字以内の文字数制限のみviewに反映する
            $rules += TrainingContent::fieldRules('url');
        }

        return $rules;
    }
}
