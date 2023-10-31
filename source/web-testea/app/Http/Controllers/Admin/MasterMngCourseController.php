<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\MstCourse;
use Illuminate\Support\Facades\Lang;
use App\Libs\AuthEx;

/**
 * コースマスタ管理 - コントローラ
 */
class MasterMngCourseController extends Controller
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
        return view('pages.admin.master_mng_course');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // クエリを作成
        $query = MstCourse::query();

        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        // データを取得
        $mstCourse = $query
            ->select(
                'mst_courses.course_cd',
                'mst_courses.name',
                'mst_courses.short_name',
                'mst_courses.course_kind',
                // コードマスタの名称(コース種別)
                'mst_codes_42.name as course_kind_name',
                'mst_courses.summary_kind',
                // コードマスタの名称(給与集計種別)
                'mst_codes_25.name as summary_kind_name',
            )
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_courses.course_kind', '=', 'mst_codes_42.code')
                    ->where('mst_codes_42.data_type', AppConst::CODE_MASTER_42);
            }, 'mst_codes_42')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_courses.summary_kind', '=', 'mst_codes_25.code')
                    ->where('mst_codes_25.data_type', AppConst::CODE_MASTER_25);
            }, 'mst_codes_25')
            ->orderby('mst_courses.course_cd');

        // ページネータで返却
        return $this->getListAndPaginator($request, $mstCourse);
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
        // コース種別リストを取得
        $courseKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_42);

        // 給与集計種別リストを取得
        $summaryKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_25);

        return view('pages.admin.master_mng_course-input', [
            'rules' => $this->rulesForInput(null),
            'courseKindList' => $courseKindList,
            'summaryKindList' => $summaryKindList,
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
            'course_cd',
            'name',
            'short_name',
            'course_kind',
            'summary_kind'
        );

        $mstCourse = new MstCourse;

        // 登録(ガードは不要)
        $mstCourse->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int
     * @return view
     */
    public function edit($courseId)
    {
        // コース種別リストを取得
        $courseKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_42);

        // 給与集計種別リストを取得
        $summaryKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_25);

        // クエリを作成(PKでユニークに取る)
        $mstCourse = MstCourse::select(
                'mst_courses.course_cd',
                'mst_courses.course_cd as _course_cd',
                'mst_courses.name',
                'mst_courses.short_name',
                'mst_courses.course_kind',
                'mst_courses.summary_kind',
            )
            ->where('course_cd', $courseId)
            ->firstOrFail();

        return view('pages.admin.master_mng_course-input', [
            'rules' => $this->rulesForInput(null),
            'courseKindList' => $courseKindList,
            'summaryKindList' => $summaryKindList,
            'editData' => $mstCourse,
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
            'course_cd',
            'name',
            'short_name',
            'course_kind',
            'summary_kind'
        );

        // 対象データを取得(hiddenのコードでユニークに取る)
        $mstCourse = MstCourse::where('course_cd', $request['_course_cd'])
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $mstCourse->fill($form)->save();

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
            'course_cd',
            '_course_cd'
        );

        // 対象データを取得(IDでユニークに取る)
        $mstCourse = MstCourse::where('course_cd', $form['_course_cd'])
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 物理削除
        $mstCourse->forceDelete();

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

        // 独自バリデーション: リストのチェック コース種別
        $validationCourseKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_42);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 給与集計種別
        $validationSummaryKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_25);
            if (!isset($states[$value])) {
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

            // コースコード編集ありの場合にバリデーション
            if($request['course_cd'] != $request['_course_cd']){
                $exists = MstCourse::where('course_cd', $request['course_cd'])
                ->exists();
            }

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += MstCourse::fieldRules('course_cd', ['required', $validationKey]);
        $rules += MstCourse::fieldRules('name', ['required']);
        $rules += MstCourse::fieldRules('short_name', ['required']);
        $rules += MstCourse::fieldRules('course_kind', ['required', $validationCourseKindList]);
        $rules += MstCourse::fieldRules('summary_kind', ['required', $validationSummaryKindList]);

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
            // コースコードを編集し削除ボタンを押した場合はエラーを返す
            if ($request['course_cd'] != $request['_course_cd']) {
                return $fail(Lang::get('validation.delete_cannot_change'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += MstCourse::fieldRules('course_cd', ['required', $validationKey]);

        return $rules;
    }
}
