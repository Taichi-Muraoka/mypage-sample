<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\ExtStudentKihon;
use App\Models\ExtSchedule;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;
//use App\Http\Controllers\Traits\FuncReportTrait;
use Carbon\Carbon;
use App\Models\MstSchool;
use App\Models\CodeMaster;
use App\Http\Controllers\Traits\FuncSchoolSearchTrait;

/**
 * 受験校管理 - コントローラ
 */
class DesiredMngController extends Controller
{
    // 機能共通処理：学校検索モーダル
    use FuncSchoolSearchTrait;

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
     * @param int $sid 生徒ID
     * @return view
     */
    public function index($sid)
    {
        // IDのバリデーション
        $this->validateIds($sid);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 生徒名を取得する
        $student = $this->getStudentName($sid);

        return view('pages.admin.desired_mng', [
            'rules' => $this->rulesForSearch(),
            'name' => $student->name,
            'sid' => $sid,
            'rooms' => $rooms,
            'editData' => null
        ]);
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
        // ページネータで返却（モック用）
        return $this->getListAndPaginatorMock();
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
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {

        $rules = array();

        return $rules;
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getData(Request $request)
    {
        return [
        ];
    }

    //==========================
    // 登録・編集・削除
    //==========================

    /**
     * 登録画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function new($sid)
    {
        // 学校検索モーダル用のデータ渡し
        // 学校種リストを取得
        $schoolKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_49);

        // 設置区分リストを取得
        $establishKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_50);

        $editData = [
            'sid' => $sid
            //"record_kind" => 1
        ];

        // テンプレートは編集と同じ
        return view('pages.admin.desired_mng-input', [
            'editData' => $editData,
            'rules' => $this->rulesForInput(null),

            // 学校検索モーダル用のバリデーションルール
            'rulesSchool' => $this->rulesForSearchSchool(),
            'schoolKindList' => $schoolKindList,
            'establishKindList' => $establishKindList,
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
     * @param int $desiredId 受験校ID
     * @return view
     */
    public function edit($desiredId)
    {
        // 学校検索モーダル用のデータ渡し
        // 学校種リストを取得
        $schoolKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_49);

        // 設置区分リストを取得
        $establishKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_50);

        $editData = [
            "sid" => 1,
            "record_kind" => 1,

            // TODO: サンプル。表示用(学校名)と、ID(学校ID)を指定する
            // 学校名はtext_xxxのように指定する
            'text_school_cd' => '東京都立青山高等学校',
            'school_cd' => 99
        ];

        return view('pages.admin.desired_mng-input', [
            'editData' => $editData,
            'rules' => $this->rulesForInput(null),

            // 学校検索モーダル用のバリデーションルール
            'rulesSchool' => $this->rulesForSearchSchool(),
            'schoolKindList' => $schoolKindList,
            'establishKindList' => $establishKindList,
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
     * 削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function delete(Request $request)
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
    //==========================
    // クラス内共通処理
    //==========================

    /**
     * 生徒名の取得
     *
     * @param int $sid 生徒Id
     * @return object
     */
    private function getStudentName($sid)
    {
        // 生徒名を取得する
        $query = ExtStudentKihon::query();
        $student = $query
            ->select(
                'name'
            )
            ->where('ext_student_kihon.sid', '=', $sid)
            ->firstOrFail();

        return $student;
    }

    //==========================
    // 学校検索
    //==========================

    /**
     * 検索結果取得(学校検索)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function searchSchool(Request $request)
    {
        // 検索結果を取得
        $schoolList = $this->getSchoolList($request);

        // ページネータで返却
        return $this->getListAndPaginator($request, $schoolList);
    }

    /**
     * バリデーション(学校検索用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForSearchSchool(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForSearchSchool());
        return $validator->errors();
    }
}
