<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Libs\AuthEx;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\Notice;
use App\Models\NoticeDestination;
use App\Models\AdminUser;
use App\Models\NoticeGroup;
use App\Models\ExtStudentKihon;
use App\Models\ExtRirekisho;
use App\Models\ExtTrialMaster;
use App\Models\Event;
use App\Models\NoticeTemplate;
use App\Models\ExtGenericMaster;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncNoticeTrait;

/**
 * お知らせ通知 - コントローラ
 */
class NoticeRegistController extends Controller
{

    // 機能共通処理：お知らせ
    use FuncNoticeTrait;

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

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(true);

        // 宛先種別プルダウンを作成
        $destination_types = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_15);

        // お知らせ種別プルダウンを作成
        $notice_type_list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_14);

        return view('pages.admin.notice_regist', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'destination_types' => $destination_types,
            'notice_type_list' => $notice_type_list,
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
        Validator::make($request->all(), $this->rulesForSearch())->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成(主テーブルはお知らせとした)
        $query = Notice::query();

        // 教室の検索
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchCampusCd($form);
        }

        // 宛先種別(お知らせ宛先参照)
        (new NoticeDestination)->scopeSearchType($query, $form);

        // お知らせ種別絞り込み
        $query->SearchNoticeType($form);

        // タイトル(お知らせ)
        $query->SearchTitle($form);

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // クエリ作成
        $notices = $query
            ->distinct()
            ->select(
                'notices.notice_id as id',
                'notices.regist_time as date',
                'notices.title',
                'mst_codes1.name as type_name',
                'room_name',
                'notice_destinations.destination_type as destination_type',
                'mst_codes2.name as notice_type_name',
            )
            // お知らせ宛先
            ->sdLeftJoin(NoticeDestination::class, function ($join) {
                // 1件取得
                $join->on('notice_destinations.notice_id', '=', 'notices.notice_id');
            })
            // 宛先種別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_codes1.code', '=', 'notice_destinations.destination_type')
                    ->where('mst_codes1.data_type', '=', AppConst::CODE_MASTER_15);
            }, 'mst_codes1')
            // お知らせ種別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_codes2.code', '=', 'notices.notice_type')
                    ->where('mst_codes2.data_type', '=', AppConst::CODE_MASTER_14);
            }, 'mst_codes2')
            // 教室名取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('notices.campus_cd', '=', 'room_names.code');
            })
            ->orderBy('notices.regist_time', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $notices);
    }

    /**
     * 詳細画面
     *
     * @param integer $noticeId お知らせID
     * @return view
     */
    public function detail($noticeId)
    {
        return view('pages.admin.notice_regist-detail', [
            'editData' => [
                'noticeId' => null
            ]
        ]);

    //==========================
    // 本番用
    //==========================
        // // IDのバリデーション
        // $this->validateIds($noticeId);

        // // 教室管理者の場合、見れていいidかチェックする
        // if (AuthEx::isRoomAdmin()) {
        //     $notice = Notice::where('notice_id', $noticeId)
        //         // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        //         ->where($this->guardRoomAdminTableWithRoomCd())
        //         ->firstOrFail();
        // }

        // // 教室名取得のサブクエリ
        // $room_names = $this->mdlGetRoomQuery();

        // // クエリを作成
        // $query = Notice::query();
        // $notice = $query
        //     ->select(
        //         'notice.notice_id AS id',
        //         'notice.regist_time',
        //         'notice.title',
        //         'notice.text',
        //         'notice.notice_type',
        //         'mst_codes.name AS type_name',
        //         'room_name',
        //         'office.name AS sender',
        //         'ext_trial_master.name AS trial_name',
        //         'ext_trial_master.trial_date',
        //         'event.name AS event_name',
        //         'event.event_date'
        //     )
        //     // お知らせ宛先
        //     ->sdLeftJoin(NoticeDestination::class, function ($join) {
        //         // 1件取得
        //         $join->on('notice_destination.notice_id', '=', 'notice.notice_id')
        //             ->limit(1);
        //     })
        //     // 宛先種別
        //     ->sdLeftJoin(CodeMaster::class, function ($join) {
        //         $join->on('mst_codes.code', '=', 'notice_destination.destination_type')
        //             ->where('mst_codes.data_type', '=', AppConst::CODE_MASTER_15);
        //     })
        //     // 事務局マスタ(送信者名)
        //     ->sdLeftJoin(AdminUser::class, 'office.adm_id', '=', 'notice.adm_id')
        //     // 教室名
        //     ->leftJoinSub($room_names, 'room_names', function ($join) {
        //         $join->on('notice.roomcd', '=', 'room_names.code');
        //     })
        //     // 模試名の取得
        //     ->sdLeftJoin(ExtTrialMaster::class, 'ext_trial_master.tmid', '=', 'notice.tmid_event_id')
        //     // イベント名の取得
        //     ->sdLeftJoin(Event::class, 'event.event_id', '=', 'notice.tmid_event_id')
        //     // IDで絞り込み
        //     ->where('notice.notice_id', '=', $noticeId)
        //     ->firstOrFail();

        // // 該当データである場合は、模試・イベントいずれかのみ残す
        // if ($notice['notice_type'] == AppConst::CODE_MASTER_14_1) {
        //     //---------
        //     // 模試
        //     //---------
        //     $notice['tm_event_name'] = $notice['trial_name'];
        //     // 日時の取得(JOIN先の方はCarbonで取れないので以下初期化)
        //     $tmpDate = new Carbon($notice['trial_date']);
        //     $notice['tm_event_date'] = $tmpDate->format('Y/m/d');
        // } elseif ($notice['notice_type'] == AppConst::CODE_MASTER_14_2) {
        //     //---------
        //     // イベント
        //     //---------
        //     $notice['tm_event_name'] = $notice['event_name'];
        //     // 日時の取得(JOIN先の方はCarbonで取れないので以下初期化)
        //     $tmpDate = new Carbon($notice['event_date']);
        //     $notice['tm_event_date'] = $tmpDate->format('Y/m/d');
        // } else {
        //     $notice['tm_event_name'] = '';
        // }
        // unset($notice['trial_name']);
        // unset($notice['trial_date']);
        // unset($notice['event_name']);
        // unset($notice['event_date']);

        // // 宛先名の取得
        // $query = NoticeDestination::query();
        // $destination_names = $query
        //     ->distinct()
        //     ->select(
        //         'ext_student_kihon.name AS student_name',
        //         'ext_rirekisho.name AS teacher_name',
        //         'notice_destination.notice_group_id',
        //         'group_name'
        //     )
        //     // 生徒名の取得
        //     ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
        //         $join->on('ext_student_kihon.sid', '=', 'notice_destination.sid');
        //     })
        //     // 教師名の取得
        //     ->sdLeftJoin(ExtRirekisho::class, function ($join) {
        //         $join->on('ext_rirekisho.tid', '=', 'notice_destination.tid');
        //     })
        //     // お知らせグループ
        //     ->sdLeftJoin(NoticeGroup::class, function ($join) {
        //         $join->on('notice_group.notice_group_id', '=', 'notice_destination.notice_group_id');
        //     })
        //     ->where('notice_destination.notice_id', '=', $noticeId)
        //     ->orderBy('notice_destination.notice_group_id', 'asc')
        //     ->get();

        // return view('pages.admin.notice_regist-detail', [
        //     'notice' => $notice,
        //     'destination_names' => $destination_names,
        //     'editData' => [
        //         'noticeId' => $notice->id
        //     ]
        // ]);
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {

        $rules = array();

        // // 独自バリデーション: リストのチェック 教室
        // $validationRoomList =  function ($attribute, $value, $fail) {

        //     // 教室リストを取得
        //     $rooms = $this->mdlGetRoomList();
        //     if (!isset($rooms[$value])) {
        //         // 不正な値エラー
        //         return $fail(Lang::get('validation.invalid_input'));
        //     }
        // };

        // // 独自バリデーション: リストのチェック ステータス
        // $validationDestinationTypesList =  function ($attribute, $value, $fail) {

        //     // 宛先種別プルダウンを作成
        //     $destination_types = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_15);
        //     if (!isset($destination_types[$value])) {
        //         // 不正な値エラー
        //         return $fail(Lang::get('validation.invalid_input'));
        //     }
        // };

        // $rules += Notice::fieldRules('roomcd', [$validationRoomList]);
        // $rules += NoticeDestination::fieldRules('destination_type', [$validationDestinationTypesList]);
        // $rules += Notice::fieldRules('title');

        return $rules;
    }

    //==========================
    // 登録・削除
    //==========================

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {

        // 定型文のプルダウン取得
        $templates = $this->getMenuOfNoticeTemplate();

        // 宛先グループチェックボックス
        $noticeGroup = $this->getMenuOfNoticeGroup();

        // 宛先種別プルダウンを作成
        $destination_types = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_15);

        return view('pages.admin.notice_regist-new', [
            'rules' => $this->rulesForInput(null),
            'templates' => $templates,
            'editData' => null,
            'noticeGroup' => $noticeGroup,
            'destination_types' => $destination_types
        ]);
    }

    /**
     * タイトル・内容情報取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed タイトル、内容等取得
     */
    public function getDataSelectTemplate(Request $request)
    {

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // 定型文ID
        $id = $request->input('id');

        // 定型文を取得
        $query = NoticeTemplate::query();
        $template = $query
            ->select(
                'title',
                'text',
                'notice_type',
                'mst_codes.name as notice_type_name',
            )
            ->where('template_id', '=', $id)
            // お知らせ種別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_codes.code', '=', 'notice_templates.notice_type')
                    ->where('mst_codes.data_type', '=', AppConst::CODE_MASTER_14);
            })
            ->firstOrFail();

        return [
            'title' => $template->title,
            'text' => $template->text,
            'notice_type' => $template->notice_type,
            'notice_type_name' => $template->notice_type_name,
        ];
    }

    /**
     * 宛先種別プルダウンを選択
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 教師、生徒、教室情報取得
     */
    public function getDataSelect(Request $request)
    {
        // MEMO: 不正アクセス防止のため宛先種別のバリデーションを入れる
        $this->validateIdsFromRequest($request, 'destinationType');

        $destination_type = '';
        $rooms = [];
        $students = [];
        $teachers = [];

        if ($request->filled('destinationType')) {

            // 宛先種別
            $destination_type = $request->input('destinationType');
            if (!($destination_type == AppConst::CODE_MASTER_15_1 ||
                $destination_type == AppConst::CODE_MASTER_15_2 ||
                $destination_type == AppConst::CODE_MASTER_15_3)) {
                return [
                    'rooms' => [],
                    'students' => [],
                    'teachers' => []
                ];
            }
        }

        // 教室のプルダウンリストを取得
        $rooms = $this->mdlGetRoomList(false);

        if ($destination_type == AppConst::CODE_MASTER_15_2) {

            if ($request->filled('roomcdStudent')) {

                $roomcd = $request->input('roomcdStudent');

                // 教室が選択されている場合
                $students = $this->mdlGetStudentListWithSid($roomcd);
            }
        } elseif ($destination_type == AppConst::CODE_MASTER_15_3) {

            // 教師リスト取得
            $teachers = $this->getMenuOfTeacher();
        }

        return [
            'rooms' => $this->objToArray($rooms),
            'students' => $this->objToArray($students),
            'teachers' => $this->objToArray($teachers),
        ];
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

        $tmid_event_id = null;

        // ログイン者のidを取得する。
        $account = Auth::user();
        $adm_id = $account->account_id;

        // 定型文よりお知らせ種別の取得
        $notice_type = NoticeTemplate::select('notice_type')
            ->where('template_id', '=', $request->input('template_id'))
            ->firstOrFail()
            ->notice_type;

        // 必要な要素のみ選択する
        $form = $request->only(
            'title',
            'text'
        );

        // お知らせ種別により保存する値を分岐
        switch ($notice_type) {
            case AppConst::CODE_MASTER_14_1:

                $tmid_event_id = $request->input('tmid');

                break;
            case AppConst::CODE_MASTER_14_2:

                $tmid_event_id = $request->input('event_id');

                break;
            default:
                break;
        }

        // 宛先種別により保存内容を分岐
        $destination_type = $request->input('destination_type');
        $destinations = [];

        switch ($destination_type) {
                // グループ一斉
            case AppConst::CODE_MASTER_15_1:

                // グループを配列にする
                $notice_groups = explode(",", $request->input('notice_groups'));
                // 配列を昇順に並び替える
                sort($notice_groups);

                // 教室コード指定の指定がある場合
                if ($request->filled('roomcd_group') || AuthEx::isRoomAdmin()) {

                    $roomcd = $request->input('roomcd_group');
                    if (AuthEx::isRoomAdmin()) {
                        // 教室管理者の場合、強制的に教室コードを指定する
                        $roomcd = Auth::user()->roomcd;
                    }

                    $seq = 1;
                    for ($i = 0; $i < count($notice_groups); $i++) {
                        $destination = [
                            'destination_seq' => $seq,
                            'destination_type' => AppConst::CODE_MASTER_15_1,
                            'sid' => null,
                            'tid' => null,
                            'notice_group_id' => $notice_groups[$i],
                            'roomcd' => $roomcd
                        ];

                        // グループが教師の場合はnull
                        if ($notice_groups[$i] == AppConst::NOTICE_GROUP_ID_15) {
                            $destination['roomcd'] = null;
                        }

                        array_push($destinations, $destination);
                        $seq++;
                    }
                    // 教室コード指定の指定がない場合（全教室対象）
                } else {

                    // 全ての教室コードを取得する。ただし除外対象の教室コードを除く
                    $codemasters = CodeMaster::select('gen_item1', 'gen_item2')
                        ->where('data_type', AppConst::CODE_MASTER_6)
                        ->firstOrFail();

                    $query = ExtGenericMaster::query();
                    $rooms = $query->select('code')
                        ->where('codecls', $codemasters->gen_item1)
                        ->where('code', '<=', $codemasters->gen_item2)
                        ->whereNotIn('code', config('appconf.excluded_roomcd'))
                        ->orderBy('disp_order', 'asc')
                        ->get();

                    // 教室コードの配列
                    $room_codes = [];
                    foreach ($rooms as $room) {
                        array_push($room_codes, $room->code);
                    }

                    // 全て教室コード×全てのグループで配列を作る
                    $seq = 1;
                    $tutor_flg = false;
                    for ($i = 0; $i < count($room_codes); $i++) {
                        $destination = [
                            'destination_type' => AppConst::CODE_MASTER_15_1,
                            'roomcd' => $room_codes[$i],
                            'sid' => null,
                            'tid' => null
                        ];

                        for ($j = 0; $j < count($notice_groups); $j++) {
                            $destination['destination_seq'] = $seq;
                            $destination['notice_group_id'] = $notice_groups[$j];

                            // グループに教師が含まれる場合、フラグのみ立てておく
                            if ($destination['notice_group_id'] == AppConst::NOTICE_GROUP_ID_15) {
                                $tutor_flg = true;
                                continue;
                            }
                            array_push($destinations, $destination);
                            $seq++;
                        }
                    }
                    if ($tutor_flg) {
                        $destination = [
                            'destination_type' => AppConst::CODE_MASTER_15_1,
                            'roomcd' => null,
                            'sid' => null,
                            'tid' => null,
                            'destination_seq' => $seq,
                            'notice_group_id' => AppConst::NOTICE_GROUP_ID_15
                        ];
                        array_push($destinations, $destination);
                    }
                }

                break;
                // 個別（生徒）
            case AppConst::CODE_MASTER_15_2:

                // sidのチェック
                if (AuthEx::isRoomAdmin()) {
                    ExtStudentKihon::where('sid', $request->input('sid'))
                        // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                        ->where($this->guardRoomAdminTableWithSid())
                        ->firstOrFail();
                }

                $destinations = [
                    [
                        'destination_seq' => 1,
                        'destination_type' => AppConst::CODE_MASTER_15_2,
                        'sid' => $request->input('sid'),
                        'tid' => null,
                        'notice_group_id' => null,
                        'roomcd' => null
                    ]
                ];

                break;
                // 個別（教師）
            case AppConst::CODE_MASTER_15_3:

                $destinations = [
                    [
                        'destination_seq' => 1,
                        'destination_type' => AppConst::CODE_MASTER_15_3,
                        'sid' => null,
                        'tid' => $request->input('tid'),
                        'notice_group_id' => null,
                        'roomcd' => null
                    ]
                ];

                break;
            default:
                break;
        }

        // 保存内容
        $notice = new Notice;
        $notice->notice_type = $notice_type;
        $notice->tmid_event_id = $tmid_event_id;
        $notice->adm_id = $adm_id;
        $notice->roomcd = $account->roomcd;

        DB::transaction(function () use ($form, $notice, $destinations) {

            $notice->fill($form)->save();
            $notice_id = $notice->notice_id;

            for ($i = 0; $i < count($destinations); $i++) {
                $destinations[$i]['notice_id'] = $notice_id;
            }

            foreach ($destinations as $data) {
                $notice_destination = new NoticeDestination;
                $notice_destination->notice_id = $data['notice_id'];
                $notice_destination->destination_seq = $data['destination_seq'];
                $notice_destination->destination_type = $data['destination_type'];
                $notice_destination->sid = $data['sid'];
                $notice_destination->tid = $data['tid'];
                $notice_destination->notice_group_id = $data['notice_group_id'];
                $notice_destination->roomcd = $data['roomcd'];
                $notice_destination->save();
            }
        });

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
        $this->validateIdsFromRequest($request, 'noticeId');

        $noticeId = $request->input('noticeId');

        // 教室管理者の場合、削除していいidかチェックする
        if (AuthEx::isRoomAdmin()) {
            $notice = Notice::where('notice_id', $noticeId)
                // 教室管理者の場合、自分の教室コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                ->firstOrFail();
        }

        DB::transaction(function () use ($noticeId) {
            // 削除
            NoticeDestination::where('notice_id', '=', $noticeId)->delete();
            Notice::where('notice_id', '=', $noticeId)->delete();
        });

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

        // // 独自バリデーション: リストのチェック 模試名
        // $validationTrialsList =  function ($attribute, $value, $fail) {

        //     $today = date("Y/m/d");
        //     // 模試のプルダウン取得
        //     $trials = $this->getMenuOfTrial($today);
        //     if (!isset($trials[$value])) {
        //         // 不正な値エラー
        //         return $fail(Lang::get('validation.invalid_input'));
        //     }
        // };

        // // 独自バリデーション: リストのチェック イベント
        // $validationEventList =  function ($attribute, $value, $fail) {

        //     $today = date("Y/m/d");
        //     // イベントチェックボックス
        //     $events = $this->getMenuOfEvent($today);
        //     if (!isset($events[$value])) {
        //         // 不正な値エラー
        //         return $fail(Lang::get('validation.invalid_input'));
        //     }
        // };

        // // 独自バリデーション: リストのチェック 定型文
        // $validationTemplatesList =  function ($attribute, $value, $fail) {

        //     // 定型文のプルダウン取得
        //     $templates = $this->getMenuOfNoticeTemplate();

        //     if (!isset($templates[$value])) {
        //         // 不正な値エラー
        //         return $fail(Lang::get('validation.invalid_input'));
        //     }
        // };

        // // 独自バリデーション: リストのチェック 宛先
        // $validationDestinationTypesList =  function ($attribute, $value, $fail) {

        //     // 宛先種別プルダウンを作成
        //     $destination_types = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_15);
        //     if (!isset($destination_types[$value])) {
        //         // 不正な値エラー
        //         return $fail(Lang::get('validation.invalid_input'));
        //     }
        // };

        // // 独自バリデーション: リストのチェック 教室
        // $validationRoomList =  function ($attribute, $value, $fail) {

        //     // 教室リストを取得
        //     $rooms = $this->mdlGetRoomList();
        //     if (!isset($rooms[$value])) {
        //         // 不正な値エラー
        //         return $fail(Lang::get('validation.invalid_input'));
        //     }
        // };

        // // 独自バリデーション: リストのチェック 生徒
        // $validationStudentList =  function ($attribute, $value, $fail) use ($request) {

        //     if (!isset($request->roomcd_student)) return;
        //     // 生徒リスト取得
        //     $students = $this->mdlGetStudentListWithSid($request->roomcd_student);
        //     if (!isset($students[$value])) {
        //         // 不正な値エラー
        //         return $fail(Lang::get('validation.invalid_input'));
        //     }
        // };

        // // 独自バリデーション: リストのチェック 教師
        // $validationTeacherList =  function ($attribute, $value, $fail) {

        //     // 教師リスト取得
        //     $teachers = $this->getMenuOfTeacher();
        //     if (!isset($teachers[$value])) {
        //         // 不正な値エラー
        //         return $fail(Lang::get('validation.invalid_input'));
        //     }
        // };

        // // 独自バリデーション: チェックボックス 宛先グループ
        // $validationNoticeGroupList =  function ($attribute, $value, $fail) use ($request) {

        //     // グループを配列にする
        //     $inputNoticeGroup = explode(",", $request->notice_groups);

        //     // 宛先グループチェックボックス
        //     $noticeGroups = $this->getMenuOfNoticeGroup();

        //     // 配列にしたグループの整形
        //     $group = [];
        //     foreach ($inputNoticeGroup as $val) {
        //         $group[$val] = $val;
        //     }
        //     // IDとインデックスを合わせるため整形
        //     // MEMO:他への影響を考えgetMenuOfNoticeGroupの処理にkeyByをつけた修正をしない
        //     $noticeGroup = [];
        //     foreach ($noticeGroups as $noticeGroups) {
        //         $noticeGroup[$noticeGroups->notice_group_id] = $noticeGroups->notice_group_id;
        //     }

        //     foreach ($group as $val) {
        //         if (!isset($noticeGroup[$val])) {
        //             // 不正な値エラー
        //             return $fail(Lang::get('validation.invalid_input'));
        //         }
        //     }
        // };

        // // 必須要素の分岐用
        // $tm_required = [];
        // $event_required = [];
        // $roomcd_group_required = '';
        // $roomcd_student_required = '';

        // if ($request != null) {
        //     if ($request->filled('template_id') && $request->filled('destination_type')) {
        //         $template_id = $request->input('template_id');
        //         $destination_type = $request->input('destination_type');

        //         // 宛先種別ごとのバリデーションルール
        //         if ($destination_type == AppConst::CODE_MASTER_15_1) {
        //             // チェックボックスのバリデーション
        //             $rules += ['notice_groups' => ['required', $validationNoticeGroupList]];
        //             // 教室管理者の場合
        //             if (AuthEx::isRoomAdmin()) {
        //                 $roomcd_group_required = 'required';
        //                 // 宛先が教師のみの場合は、教室は必須としない
        //                 $notice_groups = $request->input('notice_groups');
        //                 if ($notice_groups === (string) AppConst::NOTICE_GROUP_ID_15) {
        //                     $roomcd_group_required = null;
        //                 }
        //             }
        //         } elseif ($destination_type == AppConst::CODE_MASTER_15_2) {
        //             // 生徒No.のバリデーション
        //             $rules += ExtStudentKihon::fieldRules('sid', ['required', $validationStudentList]);
        //             // 教室管理者の場合
        //             if (AuthEx::isRoomAdmin()) {
        //                 $roomcd_student_required = 'required';
        //             }
        //         } elseif ($destination_type == AppConst::CODE_MASTER_15_3) {
        //             // 教師No.のバリデーション
        //             $rules += ExtRirekisho::fieldRules('tid', ['required', $validationTeacherList]);
        //         } else {
        //             $this->illegalResponseErr();
        //         }

        //         // 模試・イベントidのバリデーション
        //         $notice_type = NoticeTemplate::select('notice_type')
        //             ->where('template_id', $template_id)
        //             ->firstOrFail()
        //             ->notice_type;

        //         if ($notice_type == AppConst::CODE_MASTER_14_1) {
        //             $tm_required = ['required', $validationTrialsList];
        //         } elseif ($notice_type == AppConst::CODE_MASTER_14_2) {
        //             $event_required = ['required', $validationEventList];
        //         }
        //     }
        // }

        // $rules += NoticeTemplate::fieldRules('template_id', ['required', $validationTemplatesList]);
        // $rules += Notice::fieldRules('title', ['required']);
        // $rules += Notice::fieldRules('text', ['required']);
        // $rules += NoticeDestination::fieldRules('destination_type', ['required', $validationDestinationTypesList]);
        // $rules += ExtTrialMaster::fieldRules('tmid', $tm_required);
        // $rules += Event::fieldRules('event_id', $event_required);
        // $rules += ['roomcd_group' => ['integer', $roomcd_group_required, $validationRoomList]];
        // $rules += ['roomcd_student' => ['integer', $roomcd_student_required, $validationRoomList]];

        return $rules;
    }

    /**
     * テンプレートメニューの取得
     *
     * @return array 定型文情報取得
     */
    private function getMenuOfNoticeTemplate()
    {
        return NoticeTemplate::select(
            'template_id',
            'template_name AS value',
        )
            ->orderBy('order_code', 'asc')
            ->get()
            ->keyBy('template_id');
    }

    /**
     * 宛先グループメニューの取得
     *
     * @return array 宛先グループリスト
     */
    private function getMenuOfNoticeGroup()
    {
        return NoticeGroup::select(
            'notice_group_id',
            'group_name AS value'
        )
            ->orderBy('notice_group_id', 'asc')
            ->get();
    }

    /**
     * 模試一覧取得
     *
     * @return array 模試一覧
     */
    private function getMenuOfTrial($today)
    {
        // 本日以降の模試一覧を取得する
        return ExtTrialMaster::select(
            'tmid',
            DB::raw('CONCAT(name,"：", ext_generic_master.name2) AS value')
        )
            // 学年名称の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'ext_trial_master.cls_cd')
                ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_112);
            })
            ->where('ext_trial_master.trial_date', '>', $today)
            ->orderBy('tmid', 'asc')
            ->get()
            ->keyBy('tmid');
    }

    /**
     * イベント一覧取得
     *
     * @return array イベント一覧
     */
    private function getMenuOfEvent($today)
    {
        // 本日以降のイベント一覧を取得する
        return Event::select(
            'event_id',
            DB::raw('CONCAT(name,"：", ext_generic_master.name2) AS value')
        )
            // 学年名称の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'event.cls_cd')
                ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_112);
            })
            ->where('event.event_date', '>', $today)
            ->orderBy('event_id', 'asc')
            ->get()
            ->keyBy('event_id');
    }

    /**
     * 教師リスト一覧取得
     *
     * @return array 教師リスト
     */
    private function getMenuOfTeacher()
    {

        return ExtRirekisho::select(
            'tid AS id',
            DB::raw('CONCAT(tid,"：", name) AS value')
            )
            // アカウントテーブルとJOIN（削除教師非表示対応）
            ->sdJoin(Account::class, function ($join) {
                $join->on('ext_rirekisho.tid', '=', 'accounts.account_id')
                    ->where('accounts.account_type', AppConst::CODE_MASTER_7_2);
            })
            ->orderBy('tid', 'asc')
            ->get()
            ->keyBy('id');
    }
}
