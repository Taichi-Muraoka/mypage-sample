<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use App\Models\MstGrade;
use App\Models\MstSubject;
use App\Models\MstText;

/**
 * 授業教材マスタ管理 - コントローラ
 */
class MasterMngTextController extends Controller
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
        // 学年リストを取得
        $grades = $this->mdlGetGradeList();

        // 教科リストを取得（授業教科、教材教科）
        $subjects = $this->mdlGetSubjectList();

        return view('pages.admin.master_mng_text', [
            'rules' => $this->rulesForSearch(null),
            'grades' => $grades,
            'subjects' => $subjects,
            'textSubjects' => $subjects,
            'editData' => null
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
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = MstText::query();

        // MEMO: 一覧検索の条件はスコープで指定する
        // 学年の絞り込み条件
        $query->SearchGradeCd($form);

        // 授業教科の絞り込み条件
        $query->SearchSubjectCd($form);

        // 教材教科の絞り込み条件
        $query->SearchTextSubjectCd($form);

        // データを取得
        $mstText = $query
            ->select(
                'mst_texts.text_cd',
                'mst_texts.name',
                // 学年マスタの名称
                'mst_grades.name as grade_name',
                // 授業教科マスタの名称(授業教科)
                'mst_lesson_subjects.name as l_subject_name',
                // 授業教科マスタの名称(教材教科)
                'mst_text_subjects.name as t_subject_name'
            )
            // 学年名称の取得
            ->sdLeftJoin(MstGrade::class, function ($join) {
                $join->on('mst_texts.grade_cd', '=', 'mst_grades.grade_cd');
            })
            // 授業教科名称の取得
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('mst_texts.l_subject_cd', '=', 'mst_lesson_subjects.subject_cd');
            }, 'mst_lesson_subjects')
            // 教材教科名称の取得
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('mst_texts.t_subject_cd', '=', 'mst_text_subjects.subject_cd');
            }, 'mst_text_subjects')

            ->orderby('text_cd');

        // ページネータで返却
        return $this->getListAndPaginator($request, $mstText);
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 学年
        $validationGradesList =  function ($attribute, $value, $fail) {
            // 学年リストを取得
            $grades = $this->mdlGetGradeList(false);
            if (!isset($grades[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 授業教科
        $validationSubjectsList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $subjects = $this->mdlGetSubjectList();
            if (!isset($subjects[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 教材教科
        $validationTextSubjectsList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $textSubjects = $this->mdlGetSubjectList();
            if (!isset($textSubjects[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 学年コード
        $rules += MstText::fieldRules('grade_cd', [$validationGradesList]);
        // 授業教科コード
        $rules += MstText::fieldRules('l_subject_cd', [$validationSubjectsList]);
        // 教材教科コード
        $rules += MstText::fieldRules('t_subject_cd', [$validationTextSubjectsList]);

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
        // 学年リストを取得
        $grades = $this->mdlGetGradeList();
        $gradeLists = $this->mdlFormatInputList($grades, 2);

        // 教科リストを取得（授業教科、教材教科）
        $subjects = $this->mdlGetSubjectList();
        $subjectLists = $this->mdlFormatInputList($subjects, 3);

        return view('pages.admin.master_mng_text-input', [
            'rules' => $this->rulesForInput(null),
            'grades' => $gradeLists,
            'subjects' => $subjectLists,
            'textSubjects' => $subjectLists,
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

        $form = $request->only(
            'text_cd',
            'grade_cd',
            'l_subject_cd',
            't_subject_cd',
            'name'
        );

        $mstText = new MstText();

        // 登録(ガードは不要)
        $mstText->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int
     * @return view
     */
    public function edit($textCd)
    {
        // コース種別リストを取得
        // 学年リストを取得
        $grades = $this->mdlGetGradeList();
        $gradeLists = $this->mdlFormatInputList($grades, 2);

        // 教科リストを取得（授業教科、教材教科）
        $subjects = $this->mdlGetSubjectList();
        $subjectLists = $this->mdlFormatInputList($subjects, 3);

        // クエリを作成(PKでユニークに取る)
        $mstText = MstText::select(
            'mst_texts.text_cd',
            'mst_texts.text_cd as _text_cd',
            'mst_texts.grade_cd',
            'mst_texts.l_subject_cd',
            'mst_texts.t_subject_cd',
            'mst_texts.name',
        )
            ->where('text_cd', $textCd)
            ->firstOrFail();

        return view('pages.admin.master_mng_text-input', [
            'rules' => $this->rulesForInput(null),
            'grades' => $gradeLists,
            'subjects' => $subjectLists,
            'textSubjects' => $subjectLists,
            'editData' => $mstText
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
            'text_cd',
            'grade_cd',
            'l_subject_cd',
            't_subject_cd',
            'name'
        );

        // 対象データを取得(hiddenのコードでユニークに取る)
        $mstText = MstText::where('text_cd', $request['_text_cd'])
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $mstText->fill($form)->save();

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

        // Formを取得
        $form = $request->only(
            'text_cd',
            '_text_cd'
        );

        // 対象データを取得(IDでユニークに取る)
        $mstText = MstText::where('text_cd', $form['_text_cd'])
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 物理削除
        $mstText->forceDelete();

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
        $rules = array();

        // 独自バリデーション: リストのチェック 学年
        $validationGradesList =  function ($attribute, $value, $fail) {

            // 学年リストを取得
            $grades = $this->mdlGetGradeList(false);
            if (!isset($grades[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 授業教科・教材教科
        $validationSubjectsList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $subjects = $this->mdlGetSubjectList();
            if (!isset($subjects[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 重複チェック
        $validationKey = function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            $exists = null;

            // 授業教材コード編集ありの場合にバリデーション
            if ($request['text_cd'] != $request['_text_cd']) {
                $exists = MstText::where('text_cd', $request['text_cd'])
                    ->exists();
            }

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += MstText::fieldRules('text_cd', ['required', $validationKey]);
        $rules += MstText::fieldRules('grade_cd', ['required', $validationGradesList]);
        $rules += MstText::fieldRules('l_subject_cd', ['required', $validationSubjectsList]);
        $rules += MstText::fieldRules('t_subject_cd', ['required', $validationSubjectsList]);
        $rules += MstText::fieldRules('name', ['required']);

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

        $rules = array();

        // 独自バリデーション: 削除時変更不可
        $validationKey = function ($attribute, $value, $fail) use ($request) {
            // 授業教材コードを編集し削除ボタンを押した場合はエラーを返す
            if ($request['text_cd'] != $request['_text_cd']) {
                return $fail(Lang::get('validation.delete_cannot_change'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += MstText::fieldRules('text_cd', ['required', $validationKey]);

        return $rules;
    }

}
