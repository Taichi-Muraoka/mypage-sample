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
use App\Models\Notice;
use App\Models\NoticeDestination;
use App\Models\MstCampus;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Libs\AuthEx;

/**
 * バッジ付与 - コントローラ
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

        // 教室管理者の場合、自分の教室の生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // 生徒名を取得する
        $name = $this->mdlGetStudentName($sid);

        return view('pages.admin.badge', [
            'name' => $name,
            // 検索用にIDを渡す（hidden）
            'editData' => [
                'student_id' => $sid
            ]
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
            // 画面表示中生徒のデータに絞り込み
            ->where('badges.student_id', $request['student_id'])
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
            ->orderBy('badges.authorization_date', 'desc')
            ->orderBy('badges.badge_type', 'asc')
            ->orderBy('badges.campus_cd', 'asc');

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
        /**
         * MEMO:
         * badge-input.js 側での selected で指定した項目は、共通処理内で、id として渡される
         * コントローラー側でも、$request['id']として受け取る必要がある（selectedをオブジェクト{}で渡せば項目名指定可能）
         */

        // バッジ種別のコードのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // バッジ種別のコードを取得
        $badgeType = $request->input('id');

        // コードマスタ（バッジ種別）から汎用項目1を取得
        $query = CodeMaster::query();
        $codeMaster = $query
            ->select(
                'gen_item1'
            )
            ->where('data_type', AppConst::CODE_MASTER_55)
            ->where('code', $badgeType)
            ->firstOrFail();

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
        // IDのバリデーション
        $this->validateIds($sid);

        // 教室管理者の場合、自分の教室の生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // 生徒名を取得する
        $name = $this->mdlGetStudentName($sid);

        // バッジ種別リストを取得
        $kindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_55);

        // 生徒所属校舎に紐づく校舎リストを取得（メソッドは当コントローラー最下部に記載）
        $rooms = $this->mdlGetStudentRoomList($sid);

        // hidden用,route用データセット
        $editData = [
            'student_id' => $sid,
        ];

        // テンプレートは編集と同じ
        return view('pages.admin.badge-input', [
            'name' => $name,
            'kindList' => $kindList,
            'rooms' => $rooms,
            'editData' => $editData
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

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            //-------------------------
            // バッジデータの登録
            //-------------------------

            // フォームから受け取った値を格納
            $form = $request->only(
                // 教室管理者の場合の校舎コードのチェックはバリデーション(validationRoomList)で行っている
                'student_id',
                'campus_cd',
                'badge_type',
                'reason',
            );

            // 管理者IDを取得（ログイン者）
            $account = Auth::user();
            $adm_id = $account->account_id;

            // 「認定日」用に今の日時を取得
            $now = Carbon::now();

            // 保存
            $badge = new Badge;
            $badge->authorization_date = $now;
            $badge->adm_id = $adm_id;
            $badge->fill($form)->save();

            //-------------------------
            // お知らせメッセージの登録
            //-------------------------

            $notice = new Notice;

            // タイトルと本文(Langから取得する)
            $notice->title = Lang::get('message.notice.badge_give.title');
            $notice->text = Lang::get(
                'message.notice.badge_give.text',
                [
                    'reason' => $badge->reason
                ]
            );

            // お知らせ種別（その他）
            $notice->notice_type = AppConst::CODE_MASTER_14_4;
            // 管理者ID
            $account = Auth::user();
            $notice->adm_id = $account->account_id;
            $notice->campus_cd = $account->campus_cd;

            // 保存
            $notice->save();

            //-------------------------
            // お知らせ宛先の登録
            //-------------------------

            $noticeDestination = new NoticeDestination;

            // 先に登録したお知らせIDをセット
            $noticeDestination->notice_id = $notice->notice_id;
            // 宛先連番: 1固定
            $noticeDestination->destination_seq = 1;
            // 宛先種別（生徒）
            $noticeDestination->destination_type = AppConst::CODE_MASTER_15_2;
            // 生徒ID
            $noticeDestination->student_id = $badge->student_id;

            // 保存
            $noticeDestination->save();
        });

        return;
    }

    /**
     * 編集画面
     *
     * @param int $badgeId バッジID
     * @return view
     */
    public function edit($badgeId)
    {
        // IDのバリデーション
        $this->validateIds($badgeId);

        // クエリを作成(PKでユニークに取る)
        $badges = Badge::where('badge_id', $badgeId)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 生徒IDを取得（生徒名,生徒所属校舎の取得用）
        $sid = $badges->student_id;

        // 生徒名を取得する
        $name = $this->mdlGetStudentName($sid);

        // 生徒所属校舎に紐づく校舎リストを取得（メソッドは当コントローラー最下部に記載）
        $rooms = $this->mdlGetStudentRoomList($sid);

        // バッジ種別リストを取得
        $kindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_55);

        return view('pages.admin.badge-input', [
            'name' => $name,
            'kindList' => $kindList,
            'rooms' => $rooms,
            'editData' => $badges,
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
            // 教室管理者の場合の校舎コードのチェックはバリデーション(validationRoomList)で行っている
            'campus_cd',
            'badge_type',
            'reason',
        );

        // 対象データを取得(IDでユニークに取る)
        $badge = Badge::where('badge_id', $request['badge_id'])
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $badge ->fill($form)->save();

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
        $this->validateIdsFromRequest($request, 'badge_id');

        // Formを取得
        $form = $request->all();

        // 対象データを取得(IDでユニークに取る)
        $badge = Badge::where('badge_id', $form['badge_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 削除
        $badge->delete();

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

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック バッジ種別
        $validationKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_55);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += Badge::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += Badge::fieldRules('badge_type', ['required', $validationKindList]);

        return $rules;
    }

    //==========================
    // 生徒所属校舎に紐づく校舎リストの取得
    //==========================
    protected function mdlGetStudentRoomList($sid)
    {
        // 校舎マスタより校舎情報を取得
        $model = new MstCampus;
        $query = MstCampus::query();
        $query->select('mst_campuses.campus_cd as code', 'name as value', 'disp_order')
            // 非表示フラグの条件を付加
            ->where('is_hidden', AppConst::CODE_MASTER_11_1);

        // ログインユーザ
        $account = Auth::user();

        // 権限によって見れるリストを変更する
        if (AuthEx::isRoomAdmin()) {
            //-------------
            // 教室管理者
            //-------------
            // 教室管理者の場合、自分の管理教室のみ絞り込み
            $query->where('campus_cd', $account->campus_cd);
        }

        // 生徒所属校舎に紐づく校舎リストを絞り込み取得
        $rooms = $query->where($this->mdlWhereRoomBySidQuery($query, $model, $sid))
            ->orderBy('disp_order')
            ->get()->keyBy('code');

        return $rooms;
    }
}
