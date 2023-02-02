<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Models\ExtGenericMaster;
use App\Models\Event;
use App\Models\EventApply;
use App\Consts\AppConst;
use App\Models\Notice;
use App\Models\CodeMaster;
use App\Models\ExtStudentKihon;
use App\Models\NoticeDestination;
use App\Http\Controllers\Traits\FuncEventTrialTrait;

/**
 * イベント管理 - コントローラ
 */
class EventMngController extends Controller
{

    // 機能共通処理：模試・イベント
    use FuncEventTrialTrait;

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

        // 学年プルダウン
        $cls = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

        return view('pages.admin.event_mng', [
            'rules' => $this->rulesForSearch(null),
            'cls' => $cls,
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
        $validator = Validator::make($request->all(), $this->rulesForSearch($request));
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

        // MEMO: イベントマスタそのものは教室管理者でも全て見れるのでガードは不要

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = Event::query();

        $query->SearchName($form);
        $query->SearchClsCd($form);
        $query->SearchEventDateFrom($form);
        $query->SearchEventDateTo($form);

        // データを取得
        $event = $query
            // MEMO: 重要：表示に使用する項目のみ取得
            // パスワードのような重要情報は返却しない。
            ->select(
                'event.event_id',
                'event.name',
                'event.cls_cd',
                'ext_generic_master.name1 as cls',
                'event.event_date',
                'event.created_at'
            )
            // 学年名称の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'event.cls_cd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_112);
            })
            ->orderBy('event.event_date', 'desc')
            ->orderBy('event.created_at', 'desc');

        return $this->getListAndPaginator($request, $event);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {

        // MEMO: イベントマスタそのものは教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $id = $request->input('id');

        // クエリを作成
        $query = Event::query();

        $eventMngDtl = $query
            ->where('event.event_id', $id)
            ->select(
                'event.name',
                'ext_generic_master.name1 as cls',
                'event.event_date',
                'event.start_time',
                'event.end_time'
            )
            // 学年名称の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'event.cls_cd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_112);
            })
            ->firstOrFail();

        return $eventMngDtl;
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch(?Request $request)
    {

        $rules = array();

        // 独自バリデーション: リストのチェック 学年
        $validationClsList =  function ($attribute, $value, $fail) {

            // 学年プルダウン
            $cls = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);
            if (!isset($cls[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += Event::fieldRules('name');
        $rules += Event::fieldRules('cls_cd', [$validationClsList]);

        // 開催日 項目のバリデーションルールをベースにする
        $ruleEventDate = Event::getFieldRule('event_date');

        // FromとToの大小チェックバリデーションを追加(Fromが存在する場合のみ)
        $validateFromTo = [];
        $keyFrom = 'event_date_from';
        if (isset($request[$keyFrom]) && filled($request[$keyFrom])) {
            $validateFromTo = ['after_or_equal:' . $keyFrom];
        }

        // 日付From・Toのバリデーションの設定
        $rules += ['event_date_from' => $ruleEventDate];
        $rules += ['event_date_to' => array_merge($validateFromTo, $ruleEventDate)];

        return $rules;
    }

    //==========================
    // イベント申込者一覧
    //==========================

    /**
     * 一覧画面
     *
     * @param int $eventId イベントID
     * @return view
     */
    public function entry($eventId)
    {

        // MEMO: イベントマスタそのものは教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIds($eventId);

        // イベント詳細の取得
        $query = Event::query();
        $event = $query
            ->select(
                'event.event_id',
                'event.name',
                'ext_generic_master.name1 as cls',
                'event.event_date',
            )
            // 学年名称の取得
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_generic_master.code', '=', 'event.cls_cd')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_112);
            })
            ->where('event.event_id', $eventId)
            ->firstOrFail();

        return view(
            'pages.admin.event_mng-entry',
            [
                'event' => $event
            ]
        );
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 検索結果
     */
    public function searchEntry(Request $request)
    {

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'event_id');

        // イベントIDを取得
        $eventId = $request->input('event_id');

        $query = EventApply::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithSid());

        // 一覧取得
        $eventApply = $query
            ->select(
                'event_apply.event_id',
                'event_apply.event_apply_id',
                'event_apply.apply_time',
                'event_apply.members',
                'ext_student_kihon.name',
                'code_master.name as changes_state',
                'event_apply.created_at'
            )
            // 氏名
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('ext_student_kihon.sid', '=', 'event_apply.sid');
            })
            // ステータスの条件
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('code_master.code', '=', 'event_apply.changes_state')
                    ->where('code_master.data_type', '=', AppConst::CODE_MASTER_2);
            })
            // イベントIDで絞り込み
            ->where('event_apply.event_id', '=', $eventId)
            ->orderBy('event_apply.apply_time', 'desc')
            ->orderBy('event_apply.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $eventApply);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getDataEntry(Request $request)
    {

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl":
                //----------
                // 詳細
                //----------
                // IDのバリデーション
                $this->validateIdsFromRequest($request, 'event_apply_id');

                // イベントID
                $eventApplyId = $request->input('event_apply_id');

                // イベント申込情報
                $query = EventApply::query();

                // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                $query->where($this->guardRoomAdminTableWithSid());

                $eventApply = $query
                    ->select(
                        'event_apply.event_id',
                        'event_apply.apply_time',
                        'event_apply.members',
                        'ext_student_kihon.name',
                        'code_master.name as changes_state',
                        'event.name as event_name'
                    )
                    // 氏名
                    ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                        $join->on('ext_student_kihon.sid', '=', 'event_apply.sid');
                    })
                    // ステータスの条件
                    ->sdLeftJoin(CodeMaster::class, function ($join) {
                        $join->on('code_master.code', '=', 'event_apply.changes_state')
                            ->where('code_master.data_type', '=', AppConst::CODE_MASTER_2);
                    })
                    // イベント名の取得
                    ->sdLeftJoin(Event::class, 'event.event_id', '=', 'event_apply.event_id')
                    // PKで1件取得
                    ->where('event_apply.event_apply_id', '=', $eventApplyId)
                    ->firstOrFail();

                return [
                    'apply_time' => $eventApply->apply_time,
                    'name' => $eventApply->name,
                    'members' => $eventApply->members,
                    'event_name' => $eventApply->event_name,
                    'changes_state' => $eventApply->changes_state
                ];

                break;
            case "#modal-dtl-new":
                // スケジュール登録
                return [];

                break;
            case "#modal-dtl-output":
                // 一括受付・一覧出力
                return [];

                break;
            default:
                // 該当しない場合
                $this->illegalResponseErr();
        }
    }

    /**
     * モーダル処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function execModalEntry(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'event_id');

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl-new":
                //--------------
                // スケジュール登録
                //--------------

                // 複数の更新のためトランザクション
                DB::transaction(function () use ($request) {

                    // イベントIDを取得
                    $eventId = $request['event_id'];

                    $query = EventApply::query();

                    // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                    $query->where($this->guardRoomAdminTableWithSid());

                    // 複数行なのでupdateで対応
                    $query->where('event_id', $eventId)
                        // 受付が対象
                        ->where('changes_state', AppConst::CODE_MASTER_2_1)
                        ->update([
                            'changes_state' => AppConst::CODE_MASTER_2_2
                        ]);
                });

                return;

            case "#modal-dtl-output":
                //--------------
                // 一覧出力
                //--------------

                // イベントIDを取得
                $eventId = $request['event_id'];

                // イベント詳細の取得
                $event = Event::select(
                    'event_id',
                    'name',
                    'cls_cd',
                    'event_date',
                    'start_time',
                    'end_time',
                    'ext_generic_master.name1 AS cls'
                )
                    // 学年名称の取得
                    ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                        $join->on('ext_generic_master.code', '=', 'event.cls_cd')
                            ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_112);
                    })
                    ->where('event_id', $eventId)
                    ->firstOrFail();

                // 一覧を取得(検索と同じ)
                $query = EventApply::query();

                // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                $query->where($this->guardRoomAdminTableWithSid());

                // 一覧取得
                $eventApply = $query
                    ->select(
                        'apply_time',
                        'event_apply.sid',
                        'ext_student_kihon.name',
                        'members',
                        'code_master.name AS state',
                        'event_apply.created_at'
                    )
                    // 氏名
                    ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                        $join->on('ext_student_kihon.sid', '=', 'event_apply.sid');
                    })
                    // ステータス
                    ->sdLeftJoin(CodeMaster::class, function ($join) {
                        $join->on('event_apply.changes_state', '=', 'code_master.code')
                            ->where('code_master.data_type', AppConst::CODE_MASTER_2);
                    })
                    // イベントIDで絞り込み
                    ->where('event_apply.event_id', '=', $eventId)
                    ->orderBy('event_apply.apply_time', 'desc')
                    ->orderBy('event_apply.created_at', 'desc')
                    ->get();

                //---------------------
                // CSV出力内容を配列に保持
                //---------------------
                $arrayCsv = [];

                // イベント詳細
                $arrayCsv[] = [Lang::get('message.file.event_entry_output.detail.eventId'), $event->event_id];
                $arrayCsv[] = [Lang::get('message.file.event_entry_output.detail.name'), $event->name];
                $arrayCsv[] = [Lang::get('message.file.event_entry_output.detail.cls'), $event->cls];
                $arrayCsv[] = [Lang::get('message.file.event_entry_output.detail.eventDate'), $event->event_date->format('Y/m/d')];

                // ヘッダ
                $arrayCsv[] = Lang::get(
                    'message.file.event_entry_output.header'
                );

                // 生徒詳細
                foreach ($eventApply as $data) {
                    // 一行出力
                    $arrayCsv[] = [
                        $data->apply_time->format('Y/m/d'),
                        $data->sid,
                        $data->name,
                        $data->members,
                        $data->state
                    ];
                }

                //---------------------
                // ファイル名の取得と出力
                //---------------------

                $filename = Lang::get(
                    'message.file.event_entry_output.name',
                    [
                        'eventDate' => $event->event_date->format('Ymd'),
                        'eventName' => $event->name,
                        'cls' => $event->cls,
                        'outputDate' => date("Ymd")
                    ]
                );

                // ファイルダウンロードヘッダーの指定
                $this->fileDownloadHeader($filename, true);

                //-----------------------------------------------------------
                // ステータスが「未対応」のレコードを一括で「受付済み」に変更し、
                // お知らせ通知を行う。
                //-----------------------------------------------------------

                // 複数の更新のためトランザクション
                DB::transaction(function () use ($request) {

                    // イベントIDを取得
                    $eventId = $request['event_id'];

                    //--------------------------
                    // 変更状態が
                    // 未対応の情報を更新前に取得
                    //--------------------------

                    $query = EventApply::query();

                    // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                    $query->where($this->guardRoomAdminTableWithSid());

                    // 一覧を取得
                    $notCompatibles = $query->select(
                        'event_id',
                        'sid'
                    )
                        ->where('event_id', $eventId)
                        // 未対応を取得
                        ->where('changes_state', AppConst::CODE_MASTER_2_0)
                        ->get();

                    // 存在しない場合は処理終了
                    if (count($notCompatibles) <= 0) {
                        return;
                    }

                    //--------------------------
                    // 変更状態を対応済みに変更
                    //--------------------------

                    // ◆一覧で変更状態が0：未対応のレコードについて、変更状態を1：受付にupdateする。
                    $query = EventApply::query();

                    // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                    $query->where($this->guardRoomAdminTableWithSid());

                    // 複数行なのでupdateで対応
                    $query->where('event_id', $eventId)
                        // 未対応が対象
                        ->where('changes_state', AppConst::CODE_MASTER_2_0)
                        ->update([
                            'changes_state' => AppConst::CODE_MASTER_2_1
                        ]);

                    //-------------------------
                    // お知らせメッセージの登録
                    //-------------------------

                    // イベント名と開催日の取得
                    $event = Event::select('name', 'event_date')
                        ->where('event_id', $eventId)
                        ->firstOrFail();

                    // ◆お知らせ情報に、お知らせ種別=1、イベント・イベントID=当該イベントIDのお知らせをinsertする。
                    $notice = new Notice;

                    // タイトルと本文(Langから取得する)
                    $notice->title = Lang::get('message.notice.event_entry_acceptance.title');
                    $notice->text = Lang::get(
                        'message.notice.event_entry_acceptance.text',
                        // 動的に表示(イベント名と開催日)
                        [
                            'eventName' => $event->name,
                            'eventDate' => $event->event_date->format('Y/m/d')
                        ]
                    );

                    // お知らせ種別
                    $notice->notice_type = AppConst::CODE_MASTER_14_4;
                    // イベントID
                    $notice->tmid_event_id = $eventId;
                    // 事務局ID
                    $account = Auth::user();
                    $notice->adm_id = $account->account_id;
                    $notice->roomcd = $account->roomcd;

                    // 保存
                    $notice->save();

                    //-------------------------
                    // お知らせ宛先の登録
                    //-------------------------

                    // ◆受付処理を行ったレコード毎に、お知らせ宛先情報に以下の条件でinsertする。
                    foreach ($notCompatibles as $index => $notCompatible) {

                        // ・お知らせID=上記で作成したお知らせのお知らせID
                        // ・宛先連番=1 からのインクリメント
                        // ・宛先種別=2
                        // ・生徒No.=各レコードの生徒No.

                        $noticeDestination = new NoticeDestination;

                        // 先に登録したお知らせIDをセット
                        $noticeDestination->notice_id = $notice->notice_id;
                        // 宛先連番
                        $noticeDestination->destination_seq = $index + 1;
                        // 宛先種別（生徒）
                        $noticeDestination->destination_type = AppConst::CODE_MASTER_15_2;
                        // 生徒No
                        $noticeDestination->sid = $notCompatible->sid;

                        // 保存
                        $noticeDestination->save();
                    }
                });

                // CSVを出力する
                $this->outputCsv($arrayCsv);

                return;

            default:
                // 該当しない場合
                $this->illegalResponseErr();
        }
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

        // MEMO: イベントマスタそのものは教室管理者でも全て見れるのでガードは不要

        // 学年プルダウン
        $cls = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

        return view('pages.admin.event_mng-input', [
            'editData' => null,
            'rules' => $this->rulesForInput(null),
            'cls' => $cls,
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

        // MEMO: イベントマスタそのものは教室管理者でも全て見れるのでガードは不要

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        $form = $request->only(
            'name',
            'cls_cd',
            'event_date',
            'start_time',
            'end_time'
        );

        $event = new Event;

        // 登録
        $event->fill($form)->save();

        return;
    }

    /**
     * イベント編集画面
     *
     * @param int $eventId イベントID
     * @return view
     */
    public function edit($eventId)
    {
        // MEMO: イベントマスタそのものは教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIds($eventId);

        // 学年プルダウン
        $cls = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

        // IDから編集するデータを取得する
        $event = Event::select(
            'event_id',
            'name',
            'cls_cd',
            'event_date',
            'start_time',
            'end_time'
        )
            ->where('event_id', $eventId)
            ->firstOrFail();

        return view('pages.admin.event_mng-input', [
            'editData' => $event,
            'rules' => $this->rulesForInput(null),
            'cls' => $cls,
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

        // MEMO: イベントマスタそのものは教室管理者でも全て見れるのでガードは不要

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        $form = $request->only(
            'event_id',
            'name',
            'cls_cd',
            'event_date',
            'start_time',
            'end_time'
        );

        // 対象データを取得(IDでユニークに取る)
        $event = Event::where('event_id', $form['event_id'])
            ->select('event_id')
            ->firstOrFail();

        // 登録
        $event->fill($form)->save();

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
        // MEMO: イベントマスタそのものは教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'event_id');

        // Formを取得
        $form = $request->all();

        // 対象データを取得(IDでユニークに取る)
        $event = Event::where('event_id', $form['event_id'])
            ->firstOrFail();

        // 削除
        $event->delete();

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
        // 独自バリデーション: リストのチェック 学年
        $validationClsList =  function ($attribute, $value, $fail) {

            // 学年プルダウン
            $cls = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);
            if (!isset($cls[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション：時間の大小チェック
        $validationEndTime = function ($attribute, $value, $fail) use ($request) {
            if (!$request || !(isset($request['start_time']) || isset($request['end_time']))) {
                return;
            }

            // 時刻がゼロ埋めで来ないケースもあるので、strtotimeで時刻に変換
            // 1:3 とか 20:00 という文字列も変換できた。(年月日は本日になる)
            $start = strtotime($request['start_time']);
            $end = strtotime($request['end_time']);
            if (!$start || !$end) {
                // 時刻の形式チェックは別で行うので、時刻として取れない場合はここでは無視
                return;
            }

            // 終了時刻が開始時刻より前の場合
            if ($start >= $end) {
                return $fail(Lang::get('validation.after_time'));
            }
        };

        $rules += Event::fieldRules('name', ['required']);
        $rules += Event::fieldRules('cls_cd', ['required', $validationClsList]);
        $rules += Event::fieldRules('event_date', ['required']);
        $rules += Event::fieldRules('start_time', ['required']);
        $rules += Event::fieldRules('end_time', ['required', $validationEndTime]);

        return $rules;
    }

    //==========================
    // イベント申込 編集
    //==========================

    /**
     * イベント申込編集画面
     *
     * @param $eventId イベントId
     * @param $eventApplyId イベント申込Id
     * @return view
     */
    public function entryEdit($eventId, $eventApplyId)
    {

        // IDのバリデーション
        $this->validateIds($eventId, $eventApplyId);

        // ステータスリストを取得
        $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);

        // 人数リスト
        $members = config('appconf.event_members');

        // 申し込み情報を取得する(PKでユニークに取る・更新前情報分も項目取得)
        $eventApply = EventApply::select(
            '*',
            // 生徒名の取得
            'ext_student_kihon.name',
            'ext_student_kihon.cls_cd',
        )
            // 生徒基本情報とJOIN
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('event_apply.sid', '=', 'ext_student_kihon.sid');
            })
            ->where('event_apply.event_apply_id', $eventApplyId)
            // キーは上記なので、上記だけで絞れるが、URLの都合上、event_idも条件として入れる
            // http://localhost:8000/event_mng/entry/1/edit/1
            // このチェックをしないと1の部分が何でも良くなってしまうため
            ->where('event_apply.event_id', $eventId)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // イベント名リストを取得 生徒の学年で絞り込む
        $events = $this->getMenuOfEvents($eventApply->cls_cd);

        return view('pages.admin.event_mng-entry-edit', [
            'eventId' => $eventId,
            'events' => $events,
            'states' => $states,
            'members' => $members,
            'editData' => $eventApply,
            'rules' => $this->rulesForInputEntry(null),
        ]);
    }

    /**
     * イベント申込編集 編集処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function updateEntry(Request $request)
    {
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInputEntry($request))->validate();

        $form = $request->only(
            'event_id',
            'changes_state',
            'apply_time',
            'members'
        );

        // 対象データを取得(PKでユニークに取る)
        $eventApply = EventApply::where('event_apply_id', $request['event_apply_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $eventApply->fill($form)->save();

        return;
    }

    /**
     * イベント申込編集 削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function deleteEntry(Request $request)
    {

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'event_apply_id');

        // Formを取得
        $form = $request->all();

        // 対象データを取得(IDでユニークに取る)
        $eventApply = EventApply::where('event_apply_id', $form['event_apply_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 削除
        $eventApply->delete();

        return;
    }

    /**
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInputEntry(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInputEntry($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInputEntry(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: 変更後のキーが存在しないかチェック
        $validationKey = function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            // 対象データを取得(PKでユニークに取る)
            $eventApply = EventApply::where('event_apply_id', $request['event_apply_id'])
                ->firstOrFail();

            // 別なイベントに同じ生徒が存在するかチェック
            $exists = EventApply::where('event_id', $request['event_id'])
                ->where('sid', $eventApply->sid)
                // 同じイベントだったらチェックはしない
                ->where('event_id', '!=', $eventApply->event_id)
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // 独自バリデーション: リストのチェック イベント
        $validationEventList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $events = $this->getMenuOfEvents();
            if (!isset($events[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 人数
        $validationMemberList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $members = config('appconf.event_members');
            if (!isset($members[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += EventApply::fieldRules('event_id', ['required', $validationKey, $validationEventList]);
        $rules += EventApply::fieldRules('event_apply_id', ['required']);
        $rules += EventApply::fieldRules('changes_state', ['required', $validationStateList]);
        $rules += EventApply::fieldRules('apply_time', ['required']);
        $rules += EventApply::fieldRules('members', ['required', $validationMemberList]);

        return $rules;
    }

    /**
     * イベントメニューの取得
     *
     * @param $cls_cd 学年
     * @return array イベント名
     */
    private function getMenuOfEvents($cls_cd = null)
    {
        // イベント名リストを取得(イベント管理の一覧と同様のソート順にした)
        return Event::select('event_id as code', 'name as value')
            ->orderBy('event_date', 'desc')
            ->when($cls_cd, function ($query, $cls_cd) {
                return $query->where('cls_cd', '=', $cls_cd);
            })
            ->get()
            ->keyBy('code');
    }
}
