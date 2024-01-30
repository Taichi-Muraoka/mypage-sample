<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use App\Libs\AuthEx;
use App\Models\MstGrade;
use App\Models\MstSubject;
use App\Models\MstUnitCategory;

/**
 * 授業単元分類マスタ管理 - コントローラ
 */
class MasterMngCategoryController extends Controller
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
        // 教室管理者の場合、画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 学年リストを取得
        $grades = $this->mdlGetGradeList();

        // 授業科目リストを取得（教材科目プルダウン用）
        $subjects = $this->mdlGetSubjectList();

        return view('pages.admin.master_mng_category', [
            'rules' => $this->rulesForSearch(null),
            'grades' => $grades,
            'subjects' => $subjects,
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
        // 教室管理者の場合、処理を行わない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = MstUnitCategory::query();

        // MEMO: 一覧検索の条件はスコープで指定する
        // 学年の絞り込み条件
        $query->SearchGradeCd($form);

        // 教材科目の絞り込み条件
        $query->SearchSubjectCd($form);

        // データを取得
        $mstCategory = $query
            ->select(
                'mst_unit_categories.unit_category_cd',
                'mst_unit_categories.name',
                // 学年マスタの名称
                'mst_grades.name as grade_name',
                // 授業科目マスタの名称
                'mst_subjects.name as subject_name'
            )
            // 学年名称の取得
            ->sdLeftJoin(MstGrade::class, function ($join) {
                $join->on('mst_unit_categories.grade_cd', '=', 'mst_grades.grade_cd');
            })
            // 授業科目名称の取得
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('mst_unit_categories.t_subject_cd', '=', 'mst_subjects.subject_cd');
            })
            ->orderby('unit_category_cd');

        // ページネータで返却
        return $this->getListAndPaginator($request, $mstCategory);
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
            $grades = $this->mdlGetGradeList();
            if (!isset($grades[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 教材科目
        $validationTextSubjectsList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $textSubjects = $this->mdlGetSubjectList();
            if (!isset($textSubjects[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += MstUnitCategory::fieldRules('grade_cd', [$validationGradesList]);
        $rules += MstUnitCategory::fieldRules('t_subject_cd', [$validationTextSubjectsList]);

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
        // 教室管理者の場合、画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 学年リストを取得
        $grades = $this->mdlGetGradeList();
        $gradeLists = $this->mdlFormatInputList($grades, 2);

        // 授業科目リストを取得（教材科目）
        $subjects = $this->mdlGetSubjectList();
        $subjectLists = $this->mdlFormatInputList($subjects, 3);

        return view('pages.admin.master_mng_category-input', [
            'rules' => $this->rulesForInput(null),
            'grades' => $gradeLists,
            'subjects' => $subjectLists,
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
        // 教室管理者の場合、処理を行わない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // 登録する項目に絞る
        $form = $request->only(
            'unit_category_cd',
            'grade_cd',
            't_subject_cd',
            'name'
        );

        // 登録
        $mstCategory = new MstUnitCategory;
        $mstCategory->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int
     * @return view
     */
    public function edit($categoryCd)
    {
        // 教室管理者の場合、画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 学年リストを取得
        $grades = $this->mdlGetGradeList();
        $gradeLists = $this->mdlFormatInputList($grades, 2);

        // 授業科目リストを取得（教材科目）
        $subjects = $this->mdlGetSubjectList();
        $subjectLists = $this->mdlFormatInputList($subjects, 3);

        // クエリを作成(PKでユニークに取る)
        $mstCategory = MstUnitCategory::select(
            'mst_unit_categories.unit_category_cd',
            'mst_unit_categories.unit_category_cd as _unit_category_cd',
            'mst_unit_categories.grade_cd',
            'mst_unit_categories.t_subject_cd',
            'mst_unit_categories.name',
        )
            ->where('unit_category_cd', $categoryCd)
            ->firstOrFail();

        return view('pages.admin.master_mng_category-input', [
            'rules' => $this->rulesForInput(null),
            'grades' => $gradeLists,
            'subjects' => $subjectLists,
            'editData' => $mstCategory
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
        // 教室管理者の場合、処理を行わない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // 更新する項目のみに絞る。
        $form = $request->only(
            'unit_category_cd',
            'grade_cd',
            't_subject_cd',
            'name'
        );

        // 対象データを取得(hiddenのコードでユニークに取る)
        $mstCategory = MstUnitCategory::where('unit_category_cd', $request['_unit_category_cd'])
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $mstCategory->fill($form)->save();

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
        // 教室管理者の場合、処理を行わない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 削除前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForDelete($request))->validate();

        // 対象データを取得(IDでユニークに取る)
        $mstCategory = MstUnitCategory::where('unit_category_cd', $request['_unit_category_cd'])
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 物理削除
        $mstCategory->forceDelete();

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
            $grades = $this->mdlGetGradeList();
            if (!isset($grades[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 教材科目
        $validationTextSubjectsList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $textSubjects = $this->mdlGetSubjectList();
            if (!isset($textSubjects[$value])) {
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

            // コード編集ありの場合にバリデーション
            if ($request['unit_category_cd'] != $request['_unit_category_cd']) {
                $exists = MstUnitCategory::where('unit_category_cd', $request['unit_category_cd'])
                    ->exists();
            }

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        $rules += MstUnitCategory::fieldRules('unit_category_cd', ['required', $validationKey]);
        $rules += MstUnitCategory::fieldRules('grade_cd', ['required', $validationGradesList]);
        $rules += MstUnitCategory::fieldRules('t_subject_cd', ['required', $validationTextSubjectsList]);
        $rules += MstUnitCategory::fieldRules('name', ['required']);

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
            // コードを編集し削除ボタンを押した場合はエラーを返す
            if ($request['unit_category_cd'] != $request['_unit_category_cd']) {
                return $fail(Lang::get('validation.delete_cannot_change'));
            }
        };

        $rules += MstUnitCategory::fieldRules('unit_category_cd', ['required', $validationKey]);

        return $rules;
    }
}
