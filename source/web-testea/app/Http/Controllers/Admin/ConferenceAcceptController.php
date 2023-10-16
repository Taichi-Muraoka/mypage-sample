<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ExtStudentKihon;
use App\Models\ExtSchedule;
use App\Models\TransferApply;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\Conference;
use App\Models\ConferenceDate;
use App\Models\ExtRirekisho;
use App\Models\Notice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Models\NoticeDestination;
use Carbon\Carbon;
use App\Libs\AuthEx;
use App\Http\Controllers\Traits\FuncTransferTrait;

/**
 * 面談日程連絡受付 - コントローラ
 */
class ConferenceAcceptController extends Controller
{

    // 機能共通処理：振替申請
    use FuncTransferTrait;

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
        // 教室プルダウン
        $rooms = $this->mdlGetRoomList(true);

        // ステータスプルダウン
        $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_5);

        // 生徒リストを取得
        $studentList = $this->mdlGetStudentList();

        return view('pages.admin.conference_accept', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'states' => $states,
            'studentList' => $studentList,
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
        $query = Conference::query();

        // MEMO: 一覧検索の条件はスコープで指定する
        // 校舎の絞り込み条件
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        } else {
            // 本部管理者の場合検索フォームから取得
            $query->SearchCampusCd($form);
        }

        // ステータスの絞り込み条件
        $query->SearchStatus($form);

        // 生徒の絞り込み条件
        $query->SearchStudentId($form);

        // 連絡日の絞り込み条件
        $query->SearchConferenceDateFrom($form);
        $query->SearchConferenceDateTo($form);

        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        $conferenceList = $query
            ->select(
                'conferences.conference_id',
                'conferences.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'conferences.campus_cd',
                // 校舎の名称
                'campus_names.room_name as campus_name',
                'conferences.status',
                // コードマスタの名称（バッジ種別）
                'mst_codes.name as status_name',
                'conferences.conference_date',
                'conferences.apply_date',
                'conferences.start_time',
                'conferences.conference_schedule_id'
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('conferences.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'conferences.student_id', '=', 'students.student_id')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('conferences.status', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_5);
            })
            ->orderby('apply_date', 'desc')->orderby('conference_id', 'asc');

        // $this->debug($conferenceList);

        // ページネータで返却（モック用）
        return $this->getListAndPaginatorMock();
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        return [
        ];
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

        // テンプレートは編集と同じ
        return view('pages.admin.conference_accept-new', [
            'rules' => null,
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

        return;
    }

    /**
     * 編集画面
     *
     * @param int conferenceAcceptId 面談日程連絡Id
     * @return view
     */
    public function edit($conferenceAcceptId)
    {
        return view('pages.admin.conference_accept-edit', [
            'editData' => null,
            'rules' => $this->rulesForInput(null),
            'students' => null,
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
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        $rules = array();

        return $rules;
    }
}
