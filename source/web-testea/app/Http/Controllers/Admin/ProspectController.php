<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\CodeMaster;
use App\Models\CourseApply;
use App\Models\ExtStudentKihon;
use App\Models\ExtRoom;
use App\Models\Notice;
use App\Models\NoticeDestination;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FuncCourseTrait;

/**
 * 見込み客管理 - コントローラ
 */
class ProspectController extends Controller
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
        $classes = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

        return view('pages.admin.prospect', [
            'classes' => $classes,
            'rooms' => null,
            'editData' => null,
            'rules' => $this->rulesForSearch()
        ]);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        return;
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

        $query = CourseApply::query();

        // ステータスの検索
        $query->SearchChangesState($form);

        // 生徒の教室の検索(生徒基本情報参照)
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithSid());
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchRoom($form);
        }

        // 生徒名の検索(生徒基本情報参照)
        (new ExtStudentKihon)->scopeSearchName($query, $form);

        // データ取得
        $courseApply = $query
            ->select(
                'change_id',
                'course_apply.sid',
                'apply_time',
                'changes_state',
                'code_master.name as status',
                'ext_student_kihon.name',
                'course.name as course_name',
                'course_apply.created_at'
            )
            // 変更状態
            ->sdleftJoin(CodeMaster::class, function ($join) {
                $join->on('course_apply.changes_state', '=', 'code_master.code')
                    ->where('data_type', AppConst::CODE_MASTER_2);
            })
            // 生徒基本情報とJOIN
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('course_apply.sid', '=', 'ext_student_kihon.sid');
            })
            // コース変更種別(二回目のコードマスタJOINなので別名を指定)
            ->sdleftJoin(CodeMaster::class, function ($join) {
                $join->on('course_apply.change_type', '=', 'course.code')
                    ->where('course.data_type', AppConst::CODE_MASTER_13);
            }, 'course')
            ->orderby('apply_time', 'desc')
            ->orderby('course_apply.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $courseApply);
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {

        $rules = array();

        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 教室リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {

            // ステータスのプルダウン取得
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);

            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += CourseApply::fieldRules('changes_state', [$validationStateList]);
        $rules += ExtStudentKihon::fieldRules('name');
        $rules += ExtRoom::fieldRules('roomcd', [$validationRoomList]);

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
        // 学年リストを取得
        $classes = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

        return view('pages.admin.prospect-input', [
            'classes' => $classes,
            'editData' => null,
            'rules' => $this->rulesForInput(null)
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
        return;
    }

    /**
     * 編集画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function edit($sid)
    {
        // IDのバリデーション
        $this->validateIds($sid);

        // 学年リストを取得
        $classes = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

        // 生徒情報を取得する
        $query = ExtStudentKihon::query();
        $student = $query
            ->select(
                'name',
                'cls_cd',
                'mailaddress1'
            )
            ->where('ext_student_kihon.sid', '=', $sid)
            ->firstOrFail();

        $editData = [
            'sid' => $sid,
            'name' => $student['name'],
            'cls_cd' => $student['cls_cd'],
            'email_student' => $student['mailaddress1'],
        ];

        return view('pages.admin.prospect-input', [
            'classes' => $classes,
            'editData' => $editData,
            'rules' => $this->rulesForInput(null)
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
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        $rules = array();

        return $rules;
    }
}
