<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\CodeMaster;
use Illuminate\Support\Facades\Lang;
use App\Models\MstGradeSubject;

/**
 * 成績科目マスタ管理 - コントローラ
 */
class MasterMngGradeSubjectController extends Controller
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

        return view('pages.admin.master_mng_grade_subject');
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

        // データを取得
        $mstGradeSubject = MstGradeSubject::select(
            'mst_grade_subjects.g_subject_cd',
            'mst_grade_subjects.school_kind',
            'mst_grade_subjects.name',
            // コードマスタの名称(学校区分)
            'mst_codes.name as school_kind_name',
        )
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_grade_subjects.school_kind', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_39);
            })
            ->orderby('mst_grade_subjects.g_subject_cd');

        // ページネータで返却
        return $this->getListAndPaginator($request, $mstGradeSubject);
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

        // 学校区分リストを取得
        $schoolKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_39);

        return view('pages.admin.master_mng_grade_subject-input', [
            'rules' => $this->rulesForInput(null),
            'editData' => null,
            'schoolKindList' => $schoolKindList,
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
            'g_subject_cd',
            'school_kind',
            'name',
        );

        // 登録
        $mstGradeSubject = new MstGradeSubject;
        $mstGradeSubject->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int
     * @return view
     */
    public function edit($gradeSubjectCd)
    {
        // 教室管理者の場合、画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 学校区分リストを取得
        $schoolKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_39);

        // 対象のデータを取得
        $mstGradeSubject = MstGradeSubject::select(
            'mst_grade_subjects.g_subject_cd',
            // hidden用
            'mst_grade_subjects.g_subject_cd as _g_subject_cd',
            'mst_grade_subjects.school_kind',
            'mst_grade_subjects.name',
        )
            ->where('g_subject_cd', $gradeSubjectCd)
            ->firstOrFail();

        return view('pages.admin.master_mng_grade_subject-input', [
            'editData' => $mstGradeSubject,
            'schoolKindList' => $schoolKindList,
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
        // 教室管理者の場合、処理を行わない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // 変更する項目のみに絞る。
        $form = $request->only(
            'g_subject_cd',
            'school_kind',
            'name',
        );

        // 対象データを取得(hiddenのコードでユニークに取る)
        $mstGradeSubject = MstGradeSubject::where('g_subject_cd', $request['_g_subject_cd'])
            ->firstOrFail();

        // 更新
        $mstGradeSubject->fill($form)->save();

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

        // 対象データを取得
        $mstGradeSubject = MstGradeSubject::where('g_subject_cd', $request['_g_subject_cd'])
            ->firstOrFail();

        // 物理削除
        $mstGradeSubject->forceDelete();

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
        // 独自バリデーション: リストのチェック 学校区分
        $validationSchoolKindList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_39);
            if (!isset($list[$value])) {
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
            if ($request['g_subject_cd'] != $request['_g_subject_cd']) {
                $exists = MstGradeSubject::where('g_subject_cd', $request['g_subject_cd'])
                    ->exists();
            }

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        $rules = array();
        $rules += MstGradeSubject::fieldRules('g_subject_cd', ['required', $validationKey]);
        $rules += MstGradeSubject::fieldRules('school_kind', ['required', $validationSchoolKindList]);
        $rules += MstGradeSubject::fieldRules('name', ['required']);

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
            if ($request['g_subject_cd'] != $request['_g_subject_cd']) {
                return $fail(Lang::get('validation.delete_cannot_change'));
            }
        };

        $rules = array();
        $rules += MstGradeSubject::fieldRules('g_subject_cd', ['required', $validationKey]);

        return $rules;
    }
}
