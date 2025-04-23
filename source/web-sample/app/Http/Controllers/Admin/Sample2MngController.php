<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\AdminUser;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\Sample;
use App\Models\Student;
use Illuminate\Support\Facades\Lang;
// Traitを使う場合
//use App\Http\Controllers\Traits\FuncXXXXTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * サンプル管理2 - コントローラ
 */
class Sample2MngController extends Controller
{

    // 機能共通処理：XXXX（共通処理がある場合）
    //use FuncXXXXTrait;

    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct() {}

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
        // 生徒リストを取得
        $studentList = $this->mdlGetStudentList();

        // ステータス取得
        $sampleStateList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);

        // 検索条件値を保持する場合
        // セッションから検索条件を取得
        $searchCond = $this->getSearchCond();
        $searchCondForm = $searchCond ? $searchCond->form : null;

        return view('pages.admin.sample2_mng', [
            'rules' => $this->rulesForSearch(),
            'editData' => null,
            'students' => $studentList,
            'sampleStateList' => $sampleStateList,
            // 検索条件入力値をeditDataに設定
            'editData' => $searchCondForm,
            // 入力モーダル用のバリデーションルール
            'rulesExec' => $this->rulesForExec(null),
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
        $query = Sample::query();

        // MEMO: 一覧検索の条件はスコープで指定する（モデルに定義）
        // 生徒IDの検索
        $query->SearchStudentId($form);

        // ステータスの検索
        $query->SearchSampleStates($form);

        // 件名の検索（部分一致検索）
        $query->SearchSampleTitle($form);

        $sampleList = $query
            ->select(
                'sample_id',
                'regist_date',
                'students.name as sname',
                'sample_title',
                'mst_codes.name as sample_state_name',
                'samples.created_at'
            )
            // 生徒情報テーブルを結合（生徒名取得）
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('samples.student_id', '=', 'students.student_id');
            })
            // コードマスタを結合（ステータス取得）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('samples.sample_state', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_1);
            })
            ->orderBy('regist_date', 'desc')
            ->orderBy('samples.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $sampleList);
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {
        $rules = array();

        // プルダウンリスト項目の場合は以下のようにリストバリデーションを入れる
        // 独自バリデーション: リストのチェック 生徒
        $validationStudentList =  function ($attribute, $value, $fail) {
            // 生徒リストを取得
            $students = $this->mdlGetStudentList();
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {
            // ステータス取得
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += Sample::fieldRules('sample_state', [$validationStateList]);
        $rules += Sample::fieldRules('student_id', [$validationStudentList]);
        $rules += Sample::fieldRules('sample_title');

        return $rules;
    }

    /**
     * 詳細取得（モーダル表示用）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 検索結果
     */
    public function getData(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $id = $request->input('id');

        // クエリを作成
        $query = Sample::query();

        $sampleData = $query
            ->select(
                'sample_id',
                'regist_date',
                'students.name as sname',
                'sample_title',
                'sample_text',
                'sample_state',
                'admin_users.name as adm_name',
                'mst_codes.name as sample_state_name'
            )
            // 取得対象のIDで絞り込み
            ->where('sample_id', $id)
            // 生徒名取得
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('samples.student_id', '=', 'students.student_id');
            })
            // ステータス取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('samples.sample_state', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_1);
            })
            // 管理者名取得
            ->sdLeftJoin(AdminUser::class, 'samples.adm_id', '=', 'admin_users.adm_id')
            // ログイン者が参照・編集できないデータがある場合は、除外する条件を加える
            //
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        return $sampleData;
    }

    //==========================
    // モーダルフォーム処理
    //==========================

    /**
     * モーダル処理（更新）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function execModal(Request $request)
    {
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForExec($request))->validate();

        // 更新する項目のみに絞る。
        $form = $request->only(
            'sample_id',
            'sample_title',
            'sample_text',
            'sample_state',
        );

        // 対象データを取得
        $sample = Sample::where('sample_id', $request['sample_id'])
            ->firstOrFail();

        // 更新
        $sample->fill($form)->save();

        return;
    }

    /**
     * バリデーション(モーダル更新用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForExec(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForExec());
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(モーダル更新用)
     *
     * @return array ルール
     */
    private function rulesForExec()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {

            // ステータスリスト取得
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);

            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 項目バリデーション
        $rules += Sample::fieldRules('sample_id');
        $rules += Sample::fieldRules('sample_title', ['required']);
        $rules += Sample::fieldRules('sample_text', ['required']);
        $rules += Sample::fieldRules('sample_state', ['required', $validationStateList]);

        return $rules;
    }

    //==========================
    // 登録・編集・削除
    //==========================

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        // 生徒リストを取得
        $studentList = $this->mdlGetStudentList();

        // ステータスリストを取得
        $sampleStateList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);

        return view('pages.admin.sample2_mng-input', [
            'rules' => $this->rulesForInput(null),
            'editData' => null,
            'students' => $studentList,
            'sampleStateList' => $sampleStateList
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
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // 登録する項目のみに絞る
        $form = $request->only(
            'regist_date',
            'student_id',
            'sample_title',
            'sample_text',
            'adm_id',
            'sample_state'
        );

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($form) {
            //-------------------------
            // サンプルデータ登録
            //-------------------------
            $sample = new Sample;
            // 登録
            // formの項目を全てそのまま更新するなら以下のようにできる
            //$sample->fill($form)->save();

            // 個別に加工する必要があれば、以下のようにする
            $sample->regist_date =  $form['regist_date'];
            $sample->student_id =  $form['student_id'];
            $sample->sample_title =  $form['sample_title'];
            $sample->sample_text =  $form['sample_text'];
            $sample->sample_state =  $form['sample_state'];
            // ログイン者のIDを取得し設定
            $account = Auth::user();
            $sample->adm_id = $account->account_id;
            // 登録
            $sample->save();
        });

        return;
    }

    /**
     * 編集画面
     *
     * @param int sampleId サンプルID
     * @return view
     */
    public function edit($sampleId)
    {
        // IDのバリデーション
        $this->validateIds($sampleId);

        // ステータス取得
        $sampleStateList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);

        // サンプルデータ情報の取得
        $editData = Sample::select(
            'sample_id',
            'regist_date',
            'sample_title',
            'sample_text',
            'sample_state',
            'samples.student_id',
            // 生徒名
            'students.name',
            // 登録者名
            'admin_users.name as adm_name',
        )
            // 生徒情報とJOIN
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('samples.student_id', '=', 'students.student_id');
            })
            // 管理者情報とJOIN
            ->sdLeftJoin(AdminUser::class, 'samples.adm_id', '=', 'admin_users.adm_id')
            // 取得対象のIDで絞り込み
            ->where('sample_id', $sampleId)
            // ログイン者が参照・編集できないデータがある場合は、除外する条件を加える
            //
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        return view('pages.admin.sample2_mng-input', [
            'editData' => $editData,
            'rules' => $this->rulesForInput(),
            'sampleStateList' => $sampleStateList
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
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // 保存する項目のみに絞る
        $form = $request->only(
            'sample_id',
            'regist_date',
            'student_id',
            'sample_title',
            'sample_text',
            'sample_state'
        );

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($form) {

            // 対象データを取得(IDでユニークに取る)
            $sample = Sample::where('sample_id', $form['sample_id'])
                // ログイン者が参照・編集できないデータがある場合は、除外する条件を加える
                //
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            //-------------------------
            // サンプルデータ更新
            //-------------------------
            // formの項目を全てそのまま更新するなら以下のようにできる
            //$sample->fill($form)->save();

            // 個別に加工する必要があれば、以下のようにする
            $sample->regist_date =  $form['regist_date'];
            $sample->student_id =  $form['student_id'];
            $sample->sample_title =  $form['sample_title'];
            $sample->sample_text =  $form['sample_text'];
            $sample->sample_state =  $form['sample_state'];
            // ログイン者のIDを取得し設定
            $account = Auth::user();
            $sample->adm_id = $account->account_id;
            $sample->save();
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
        $this->validateIdsFromRequest($request, 'sample_id');

        // Formを取得
        $form = $request->all();

        // 削除対象データの取得(IDでユニークに取る)
        $sample = Sample::where('sample_id', $form['sample_id'])
            // ログイン者が参照・編集できないデータがある場合は、除外する条件を加える
            //
            ->firstOrFail();

        //-------------------------
        // サンプルデータ削除
        //-------------------------
        // 削除
        $sample->delete();
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
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInput());
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 生徒
        $validationStudentList =  function ($attribute, $value, $fail) {
            // 生徒リストを取得
            $students = $this->mdlGetStudentList();
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {

            // ステータスリスト取得
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);

            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += Sample::fieldRules('regist_date', ['required']);
        $rules += Sample::fieldRules('student_id', ['required', $validationStudentList]);
        $rules += Sample::fieldRules('sample_title', ['required']);
        $rules += Sample::fieldRules('sample_text', ['required']);
        $rules += Sample::fieldRules('sample_state', ['required', $validationStateList]);

        return $rules;
    }
}
