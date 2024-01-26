<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use App\Models\MstSubject;

/**
 * 授業科目マスタ管理 - コントローラ
 */
class MasterMngSubjectController extends Controller
{
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
        return view('pages.admin.master_mng_subject');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // データを取得
        $mstSubject = MstSubject::select(
            'mst_subjects.subject_cd',
            'mst_subjects.name',
            'mst_subjects.short_name',
        )
            ->orderby('mst_subjects.subject_cd');

        // ページネータで返却
        return $this->getListAndPaginator($request, $mstSubject);
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
        return view('pages.admin.master_mng_subject-input', [
            'rules' => $this->rulesForInput(null),
            'editData' => null,
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

        // 登録する項目に絞る
        $form = $request->only(
            'subject_cd',
            'name',
            'short_name',
        );

        // 登録
        $mstSubject = new MstSubject;
        $mstSubject->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int
     * @return view
     */
    public function edit($subjectCd)
    {
        // データ取得
        $mstSubject = MstSubject::select(
            'mst_subjects.subject_cd',
            'mst_subjects.subject_cd as _subject_cd',
            'mst_subjects.name',
            'mst_subjects.short_name',
        )
            ->where('subject_cd', $subjectCd)
            ->firstOrFail();

        return view('pages.admin.master_mng_subject-input', [
            'editData' => $mstSubject,
            'rules' => $this->rulesForInput(null),
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
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // MEMO: 必ず登録する項目のみに絞る。
        $form = $request->only(
            'subject_cd',
            'name',
            'short_name',
        );

        // 対象データを取得(hiddenのコードでユニークに取る)
        $mstSubject = MstSubject::where('subject_cd', $request['_subject_cd'])
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $mstSubject->fill($form)->save();

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
        // 削除前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForDelete($request))->validate();

        // 対象データを取得
        $mstSubject = MstSubject::where('subject_cd', $request['_subject_cd'])
            ->firstOrFail();

        // 物理削除
        $mstSubject->forceDelete();

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
        $validator = Validator::make($request->all(), $this->rulesForInput($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        // 独自バリデーション: 重複チェック
        $validationKey = function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            $exists = null;

            // コード編集ありの場合にバリデーション
            if ($request['subject_cd'] != $request['_subject_cd']) {
                $exists = MstSubject::where('subject_cd', $request['subject_cd'])
                    ->exists();
            }

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        $rules = array();
        $rules += MstSubject::fieldRules('subject_cd', ['required', $validationKey]);
        $rules += MstSubject::fieldRules('name', ['required']);
        $rules += MstSubject::fieldRules('short_name', ['required']);

        return $rules;
    }

    /**
     * バリデーション(削除用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForDelete(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForDelete($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(削除用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForDelete(?Request $request)
    {
        if (!$request) {
            return;
        }

        // 独自バリデーション: 削除時変更不可
        $validationKey = function ($attribute, $value, $fail) use ($request) {
            // コードを編集し削除ボタンを押した場合はエラーを返す
            if ($request['subject_cd'] != $request['_subject_cd']) {
                return $fail(Lang::get('validation.delete_cannot_change'));
            }
        };

        $rules = array();
        $rules += MstSubject::fieldRules('subject_cd', ['required', $validationKey]);

        return $rules;
    }
}
