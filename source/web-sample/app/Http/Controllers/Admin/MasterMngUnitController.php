<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use App\Libs\AuthEx;
use App\Models\MstGrade;
use App\Models\MstSubject;
use App\Models\MstUnit;
use App\Models\MstUnitCategory;

/**
 * 授業単元マスタ管理 - コントローラ
 */
class MasterMngUnitController extends Controller
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

        return view('pages.admin.master_mng_unit', [
            'rules' => $this->rulesForSearch(null),
            'editData' => null,
            'grades' => $grades,
            'subjects' => $subjects,
        ]);
    }

    /**
     * 単元分類リスト取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 単元分類リスト
     */
    public function getDataSelectCategory(Request $request)
    {
        // 学年コード・教材科目コードのバリデーション
        if ($request['grade_cd']) {
            $this->validateIdsFromRequest($request, 'grade_cd');
        }
        if ($request['t_subject_cd']) {
            $this->validateIdsFromRequest($request, 't_subject_cd');
        }

        // 学年コード・教材科目コードに応じた単元分類リスト取得
        $categories = $this->mdlGetUnitCategoryList($request['grade_cd'], $request['t_subject_cd']);

        return [
            'categories' => $this->objToArray($categories),
        ];
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
        $query = MstUnit::query();

        // MEMO: 一覧検索の条件はスコープで指定する
        // 学年の絞り込み条件
        $query->SearchGradeCd($form);

        // 教材科目の絞り込み条件
        $query->SearchTextSubjectCd($form);

        // 単元分類の絞り込み条件
        $query->SearchUnitCategoryCd($form);

        // データを取得
        $mstUnit = $query
            ->select(
                'mst_units.unit_id',
                'mst_units.unit_cd',
                'mst_units.name',
                // 学年マスタの名称
                'mst_grades.name as grade_name',
                // 授業科目マスタの名称
                'mst_subjects.name as subject_name',
                // 単元分類マスタの名称
                'mst_unit_categories.name as category_name'
            )
            // 単元分類名称の取得
            ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                $join->on('mst_units.unit_category_cd', '=', 'mst_unit_categories.unit_category_cd');
            })
            // 学年名称の取得（単元分類マスタと結合）
            ->sdLeftJoin(MstGrade::class, function ($join) {
                $join->on('mst_unit_categories.grade_cd', '=', 'mst_grades.grade_cd');
            })
            // 授業科目名称の取得（単元分類マスタと結合）
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('mst_unit_categories.t_subject_cd', '=', 'mst_subjects.subject_cd');
            })
            // ソート順 単元分類コード・単元コード
            ->orderby('mst_units.unit_category_cd')
            ->orderby('mst_units.unit_cd');

        // ページネータで返却
        return $this->getListAndPaginator($request, $mstUnit);
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

        // 独自バリデーション: リストのチェック 単元分類
        $validationUnitCategoryList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlGetUnitCategoryList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // MEMO:grade_cd等は単元マスタに無いカラムのため、授業単元分類マスタのルールを適用する
        $rules += MstUnitCategory::fieldRules('grade_cd', [$validationGradesList]);
        $rules += MstUnitCategory::fieldRules('t_subject_cd', [$validationTextSubjectsList]);
        $rules += MstUnitCategory::fieldRules('unit_category_cd', [$validationUnitCategoryList]);

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

        return view('pages.admin.master_mng_unit-input', [
            'rules' => $this->rulesForInput(null),
            'editData' => null,
            'grades' => $gradeLists,
            'subjects' => $subjectLists,
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
            'unit_cd',
            'name'
        );

        // 登録
        $mstUnit = new MstUnit;
        $mstUnit->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int
     * @return view
     */
    public function edit($unitId)
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

        // 対象データを取得
        $mstUnit = MstUnit::select(
            'mst_units.unit_id',
            'mst_units.unit_category_cd',
            'mst_units.unit_category_cd as _unit_category_cd',
            'mst_units.unit_cd',
            'mst_units.name',
            'mst_unit_categories.grade_cd',
            'mst_unit_categories.t_subject_cd'
        )
            // 単元分類マスタと結合
            ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                $join->on('mst_units.unit_category_cd', '=', 'mst_unit_categories.unit_category_cd');
            })
            ->where('unit_id', $unitId)
            ->firstOrFail();

        return view('pages.admin.master_mng_unit-input', [
            'rules' => $this->rulesForInput(null),
            'editData' => $mstUnit,
            'grades' => $gradeLists,
            'subjects' => $subjectLists,
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
            'unit_cd',
            'name'
        );

        // 対象データを取得
        $mstUnit = MstUnit::where('unit_id', $request['unit_id'])
            ->firstOrFail();

        // 更新
        $mstUnit->fill($form)->save();

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

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'unit_id');

        // 対象データを取得(IDでユニークに取る)
        $mstUnit = MstUnit::where('unit_id', $request['unit_id'])
            ->firstOrFail();

        // 物理削除
        $mstUnit->forceDelete();

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

        // 独自バリデーション: リストのチェック 単元分類
        $validationUnitCategoryList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlGetUnitCategoryList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 重複チェック 単元分類コード×単元コードの組み合わせ
        $validationKey = function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            // 対象データを取得(UNIQUEキーで取得)
            $mstUnit = MstUnit::where('unit_category_cd', $request['unit_category_cd'])
                ->where('unit_cd', $request['unit_cd']);

            // 変更時は自分のキー以外を検索
            if (filled($request['unit_id'])) {
                $mstUnit->where('unit_id', '!=', $request['unit_id']);
            }

            $exists = $mstUnit->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        $rules += MstUnit::fieldRules('unit_category_cd', ['required', $validationUnitCategoryList]);
        $rules += MstUnit::fieldRules('unit_cd', ['required', $validationKey]);
        $rules += MstUnit::fieldRules('name', ['required']);
        // MEMO:grade_cd等は単元マスタに無いカラムのため、授業単元分類マスタのルールを適用する
        $rules += MstUnitCategory::fieldRules('grade_cd', ['required', $validationGradesList]);
        $rules += MstUnitCategory::fieldRules('t_subject_cd', ['required', $validationTextSubjectsList]);

        return $rules;
    }
}
