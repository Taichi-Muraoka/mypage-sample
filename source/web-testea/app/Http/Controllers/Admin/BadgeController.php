<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Badge;
use App\Models\Student;
use App\Models\AdminUser;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;
use Carbon\Carbon;

/**
 * 生徒カルテ - コントローラ
 */
class BadgeController extends Controller
{

    // 機能共通処理：

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

        // 生徒名を取得する
        $student = $this->getStudentName($sid);

        return view('pages.admin.badge', [
            'name' => $student->name,
            'sid' => $sid,
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
        // 校舎名取得(JOIN)
        $campus_names = $this->mdlGetRoomQuery();

        // クエリ作成
        $query = Badge::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        $badgeList = $query
            ->select(
                'badges.badge_id',
                'badges.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'badges.campus_cd',
                // 校舎の名称
                'campus_names.room_name as campus_name',
                'badges.badge_type',
                // コードマスタの名称（バッジ種別）
                'mst_codes.name as kind_name',
                'badges.reason',
                'badges.authorization_date',
                'badges.adm_id',
                // 管理者アカウントの名前
                'admin_users.name as admin_name',
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('badges.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'badges.student_id', '=', 'students.student_id')
            // 管理者名を取得
            ->sdLeftJoin(AdminUser::class, 'badges.adm_id', '=', 'admin_users.adm_id')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('badges.badge_type', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_55);
            })
            ->orderBy('badges.authorization_date', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $badgeList);
    }

    //==========================
    // 登録・編集・削除
    //==========================

    /**
     * バッジ付与テンプレート文取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed テンプレート
     */
    public function getDataSelectTemplate(Request $request)
    {
        // バッジ種別のバリデーション
        $this->validateIdsFromRequest($request, 'id');
        // 定型文ID
        $badgeType = $request->input('id');

        // \DB::enableQueryLog();

        $query = CodeMaster::query();
        $codeMaster = $query
            ->select(
                'gen_item1'
            )
            ->where('data_type', AppConst::CODE_MASTER_55)
            ->where('code', $badgeType)
            ->firstOrFail();

        // $this->debug($codeMaster->gen_item1);
        // $this->debug(\DB::getQueryLog());

        return [
            'reason' => $codeMaster->gen_item1,
        ];
    }

    /**
     * 登録画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function new($sid)
    {
        // 教室管理者の場合、自分の教室の生徒のみにガードを掛ける

        // バッジ種別リストを取得
        $kindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_55);

        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        $editData = [
            'sid' => $sid
            //"record_kind" => 1
        ];

        // テンプレートは編集と同じ
        return view('pages.admin.badge-input', [
            'kindList' => $kindList,
            'rooms' => $rooms,
            'editData' => $editData,
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
     * @param int $recordId 生徒カルテID
     * @return view
     */
    public function edit($recordId)
    {

        $editData = [
            "sid" => 1,
            "record_kind" => 1
        ];

        return view('pages.admin.badge-input', [
            'editData' => $editData,
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
        $query = Student::query();
        $student = $query
            ->select(
                'name'
            )
            ->where('students.student_id', '=', $sid)
            ->firstOrFail();

        return $student;
    }
}
