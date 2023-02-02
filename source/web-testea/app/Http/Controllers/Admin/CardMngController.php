<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Libs\AuthEx;
use App\Consts\AppConst;
use App\Models\Card;
use App\Models\ExtStudentKihon;
use App\Models\CodeMaster;
use App\Models\ExtRoom;
use App\Models\Notice;
use App\Models\NoticeDestination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncCardTrait;
use Illuminate\Support\Carbon;

/**
 * ギフトカード管理 - コントローラ
 */
class CardMngController extends Controller
{

    // 機能共通処理：カード
    use FuncCardTrait;

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
        $rooms = $this->mdlGetRoomList(false);

        // 使用状態取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_4);

        return view('pages.admin.card_mng', [
            'rules' => $this->rulesForSearch(null),
            'rooms' => $rooms,
            'editData' => null,
            'statusList' => $statusList
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
     * 検索結果取得(一覧と一覧出力CSV用)
     * 検索結果一覧を表示するとのCSVのダウンロードが同じため共通化
     *
     * @param mixed $form 検索フォーム
     */
    private function getSearchResult($form)
    {
        // クエリを作成
        $query = Card::query();

        // ステータスの検索
        $query->SearchCardState($form);

        // 生徒の教室の検索(生徒基本情報参照)
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithSid());
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchRoom($form);
        }

        // 日付の検索
        $query->SearchApplyTimeFrom($form);
        $query->SearchApplyTimeTo($form);

        // 生徒名の検索(生徒基本情報参照)
        (new ExtStudentKihon)->scopeSearchName($query, $form);

        // データを取得
        $cardList = $query
            ->select(
                'card_id',
                'grant_time',
                'apply_time',
                'ext_student_kihon.name',
                'card_name',
                'code_master.name as status',
                'code_master.code',
                // 以下CSV用。検索結果一覧でも見えて問題ない
                'card.sid',
                'discount',
                'term_start',
                'term_end',
                'comment'
            )
            // 生徒名
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('card.sid', '=', 'ext_student_kihon.sid');
            })
            // ステータス
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('card.card_state', '=', 'code_master.code')
                    ->where('data_type', AppConst::CODE_MASTER_4);
            })
            ->orderby('grant_time', 'desc');

        return $cardList;
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

        // 検索結果を取得
        $cardList = $this->getSearchResult($form);

        // ページネータで返却
        return $this->getListAndPaginator($request, $cardList, function ($items) {

            // ギフトカードが申請中以外の時、「使用」ボタンを非活性
            foreach ($items as $card) {
                if ($card->code != AppConst::CODE_MASTER_4_1) {
                    // 使用ボタンを非活性
                    $card['disabled'] = true;
                } else {
                    // 使用可能
                    $card['disabled'] = false;
                }
            }

            return $items;
        });
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl":
                //---------
                // 詳細
                //---------

                // IDのバリデーション
                $this->validateIdsFromRequest($request, 'card_id');

                // IDを取得
                $cardId = $request->input('card_id');

                // クエリを作成
                $query = Card::query();

                // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                $query->where($this->guardRoomAdminTableWithSid());

                // データを取得
                $card = $query
                    ->select(
                        'grant_time',
                        'apply_time',
                        'card_name',
                        'discount',
                        'term_start',
                        'term_end',
                        'code_master.name as status',
                        'comment'
                    )
                    // コードマスターとJOIN
                    ->sdLeftJoin(CodeMaster::class, function ($join) {
                        $join->on('card.card_state', '=', 'code_master.code')
                            ->where('data_type', AppConst::CODE_MASTER_4);
                    })
                    // IDを指定
                    ->where('card.card_id', $cardId)
                    // MEMO: 取得できない場合はエラーとする
                    ->firstOrFail();

                return $card;

            case "#modal-dtl-acceptance":
                //--------
                // 受付
                //--------

                // IDのバリデーション
                $this->validateIdsFromRequest($request, 'card_id');

                // IDを取得
                $cardId = $request->input('card_id');

                // クエリを作成
                $query = Card::query();

                // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                $query->where($this->guardRoomAdminTableWithSid());

                $card = $query
                    // MEMO: 重要：表示に使用する項目のみ取得
                    ->select(
                        'apply_time',
                        // 生徒名の取得
                        'ext_student_kihon.name',
                        'card_name'
                    )
                    // 生徒基本情報とJOIN
                    ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                        $join->on('card.sid', '=', 'ext_student_kihon.sid');
                    })
                    // IDを指定
                    ->where('card.card_id', $cardId)
                    // MEMO: 取得できない場合はエラーとする
                    ->firstOrFail();

                return $card;

            case "#modal-dtl-output":
                // 一覧出力
                return [];

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
    public function execModal(Request $request)
    {

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl-acceptance":
                //--------
                // 受付
                //--------

                // IDのバリデーション
                $this->validateIdsFromRequest($request, 'card_id');

                // トランザクション(例外時は自動的にロールバック)
                DB::transaction(function () use ($request) {

                    // IDを取得
                    $cardId = $request->input('card_id');

                    // 1件取得
                    $card = Card::where('card_id', $cardId)
                        // 申請中の場合のみ
                        ->where('card_state', AppConst::CODE_MASTER_4_1)
                        // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                        ->where($this->guardRoomAdminTableWithSid())
                        // 該当データがない場合はエラーを返す
                        ->firstOrFail();

                    // 受付
                    $card->card_state = AppConst::CODE_MASTER_4_2;

                    // 保存
                    $card->save();

                    //-------------------------
                    // お知らせメッセージの登録
                    //-------------------------

                    // ギフトカード名と割引内容の取得
                    $acceptanceMng = Card::select('card_name', 'discount')
                        ->where('card_id', $cardId)
                        ->firstOrFail();

                    $notice = new Notice;

                    // タイトルと本文(Langから取得する)
                    $notice->title = Lang::get('message.notice.card_acceptance.title');
                    $notice->text = Lang::get(
                        'message.notice.card_acceptance.text',
                        [
                            'cardName' => $acceptanceMng->card_name,
                            'cardContents' => $acceptanceMng->discount
                        ]
                    );

                    // お知らせ種別
                    $notice->notice_type = AppConst::CODE_MASTER_14_4;

                    // 事務局ID
                    $account = Auth::user();
                    $notice->adm_id = $account->account_id;
                    $notice->roomcd = $account->roomcd;

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

                    // 生徒No
                    $noticeDestination->sid = $card->sid;

                    // 保存
                    $noticeDestination->save();
                });

                return;

            case "#modal-dtl-output":
                //--------------
                // 一覧出力
                //--------------

                // formを取得
                $form = $request->all();

                // 検索結果を取得
                $cardList = $this->getSearchResult($form)
                    // 結果を取得
                    ->get();

                //---------------------
                // CSV出力内容を配列に保持
                //---------------------
                $arrayCsv = [];

                // ヘッダ
                $arrayCsv[] = Lang::get(
                    'message.file.card_output.header'
                );

                // ギフトカード詳細
                foreach ($cardList as $data) {
                    // 値がNULLの時はformatをしない
                    $applyTime = isset($data->apply_time) ? $data->apply_time->format('Y/m/d') : $data->apply_time;

                    // 一行出力
                    $arrayCsv[] = [
                        $data->grant_time->format('Y/m/d'),
                        $applyTime,
                        $data->sid,
                        $data->name,
                        $data->card_name,
                        $data->discount,
                        $data->term_start->format('Y/m/d'),
                        $data->term_end->format('Y/m/d'),
                        $data->status,
                        $data->comment
                    ];
                }

                //---------------------
                // ファイル名の取得と出力
                //---------------------

                $filename = Lang::get(
                    'message.file.card_output.name',
                    [
                        'outputDate' => date("Ymd")
                    ]
                );

                // ファイルダウンロードヘッダーの指定
                $this->fileDownloadHeader($filename, true);

                // CSVを出力する
                $this->outputCsv($arrayCsv);

                return;

            default:
                // 該当しない場合
                $this->illegalResponseErr();
        }
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForSearch(?Request $request)
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

            // 使用状態取得
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_4);

            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        $rules += ExtRoom::fieldRules('roomcd', [$validationRoomList]);
        $rules += Card::fieldRules('card_state', [$validationStateList]);
        $rules += ExtStudentKihon::fieldRules('name');

        // 申請日 項目のバリデーションルールをベースにする
        $ruleCardDate = Card::getFieldRule('apply_time');

        // FromとToの大小チェックバリデーションを追加(Fromが存在する場合のみ)
        $validateFromTo = [];
        $keyFrom = 'apply_time_from';
        if (isset($request[$keyFrom]) && filled($request[$keyFrom])) {
            $validateFromTo = ['after_or_equal:' . $keyFrom];
        }

        // 日付From・Toのバリデーションの設定
        $rules += ['apply_time_from' => $ruleCardDate];
        $rules += ['apply_time_to' => array_merge($validateFromTo, $ruleCardDate)];

        return $rules;
    }

    //==========================
    // 付与・編集
    //==========================

    /**
     * 付与画面
     *
     * @return view
     */
    public function new()
    {
        // 教室名を取得
        $roomList = $this->mdlGetRoomList(false);

        return view('pages.admin.card_mng-new', [
            'editData' => null,
            'rules' => $this->rulesForInputNew(null),
            'roomList' => $roomList
        ]);
    }

    /**
     * 生徒情報取得（教室リスト選択時）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 生徒情報
     */
    public function getDataSelectNew(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // roomcdを取得
        $roomcd = $request->input('id');

        // 教室コードで動的に変更
        // 教室管理者の場合は、自分の教室の生徒のみになる
        $students = $this->mdlGetStudentList($roomcd);

        return [
            'selectItems' => $students
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
        Validator::make($request->all(), $this->rulesForInputNew($request))->validate();

        // フォームから受け取った値を格納
        $form = $request->only(
            'sid',
            'card_name',
            'discount',
            'term_start',
            'term_end',
        );

        // 教室管理者のためのガード用select
        ExtStudentKihon::where('sid', $request['sid'])
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 本日の日付をセットする
        $now = Carbon::now();

        // 保存
        $card = new card;
        $card->grant_time = $now;
        $card->card_state = AppConst::CODE_MASTER_4_0;
        $card->fill($form)->save();
        return;
    }

    /**
     * 付与情報編集画面
     *
     * @param int cardId カードID
     * @return view
     */
    public function edit($cardId)
    {

        // IDのバリデーション
        $this->validateIds($cardId);

        // 使用状態取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_4);

        // ギフトカード情報の取得
        $card = Card::select(
            '*',
            // 生徒名の取得
            'ext_student_kihon.name'
        )
            // 生徒基本情報とJOIN
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('card.sid', '=', 'ext_student_kihon.sid');
            })
            ->where('card_id', $cardId)
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        return view('pages.admin.card_mng-edit', [
            'editData' => $card,
            'rules' => $this->rulesForInputEdit(),
            'statusList' => $statusList
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
        Validator::make($request->all(), $this->rulesForInputEdit())->validate();

        $form = $request->only(
            'grant_time',
            'apply_time',
            'card_name',
            'discount',
            'card_state',
            'comment',
            'term_start',
            'term_end',
        );

        // 対象データを取得(PKでユニークに取る)
        $card = Card::where('card_id', $request['card_id'])
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $card->fill($form)->save();

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
        $this->validateIdsFromRequest($request, 'card_id');

        // Formを取得
        $form = $request->all();

        // 1件取得
        $card = Card::where('card_id', $form['card_id'])
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 削除
        $card->delete();

        return;
    }

    /**
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInputNew(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInputNew($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInputNew(?Request $request)
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

        // 独自バリデーション: リストのチェック 生徒
        $validationStudentList =  function ($attribute, $value, $fail) use ($request) {

            // 生徒リストを取得
            $students = $this->mdlGetStudentList($request->roomcd);
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += ExtRoom::fieldRules('roomcd', ['required', $validationRoomList]);
        $rules += Card::fieldRules('sid', ['required', $validationStudentList]);
        $rules += Card::fieldRules('card_name', ['required']);
        $rules += Card::fieldRules('discount', ['required']);

        $rules += Card::fieldRules('term_start', ['required']);
        // 日付From・Toのバリデーションの設定
        $rules += Card::fieldRules('term_end', ['required', 'after_or_equal:term_start']);

        return $rules;
    }

    /**
     * バリデーション(更新用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInputEdit(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInputEdit());
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(更新用)
     *
     * @return array ルール
     */
    private function rulesForInputEdit()
    {

        $rules = array();

        $rules += Card::fieldRules('card_id', ['required']);
        $rules += Card::fieldRules('grant_time', ['required']);
        $rules += Card::fieldRules('apply_time');
        $rules += Card::fieldRules('card_name', ['required']);
        $rules += Card::fieldRules('discount', ['required']);
        $rules += Card::fieldRules('card_state', ['required']);
        $rules += Card::fieldRules('comment');

        $rules += Card::fieldRules('term_start', ['required']);
        // 日付From・Toのバリデーションの設定
        $rules += Card::fieldRules('term_end', ['required', 'after_or_equal:term_start']);

        return $rules;
    }
}
