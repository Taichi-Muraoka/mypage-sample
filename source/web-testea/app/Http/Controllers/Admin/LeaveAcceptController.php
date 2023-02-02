<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\CodeMaster;
use App\Models\LeaveApply;
use App\Models\ExtStudentKihon;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\Account;
use App\Models\Card;
use App\Models\CourseApply;
use App\Models\ExtRoom;
use App\Models\Notice;
use App\Models\NoticeDestination;
use App\Models\TutorRelate;
use App\Models\AbsentApply;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FuncLeaveTrait;

/**
 * 退会申請受付 - コントローラ
 */
class LeaveAcceptController extends Controller
{

    // 機能共通処理：退会
    use FuncLeaveTrait;

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

        // ステータスのプルダウン取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_5);

        return view('pages.admin.leave_accept', [
            'statusList' => $statusList,
            'rooms' => $rooms,
            'editData' => null,
            'rules' => $this->rulesForSearch()
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

        // クエリを作成
        $query = LeaveApply::query();

        // ステータスの検索
        $query->SearchLeaveState($form);

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

        // データを取得
        $leaveApply = $query
            ->select(
                // 編集時にIDとして使用
                'leave_apply_id',
                'apply_time',
                'leave_state',
                // コードマスタの名称(ステータス)
                'code_master.name as status',
                // 生徒名の取得
                'ext_student_kihon.name',
                'leave_apply.created_at'
            )
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('leave_apply.leave_state', '=', 'code_master.code')
                    ->where('data_type', AppConst::CODE_MASTER_5);
            })
            // 生徒基本情報とJOIN
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('leave_apply.sid', '=', 'ext_student_kihon.sid');
            })
            ->orderby('apply_time', 'desc')
            ->orderby('leave_apply.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $leaveApply);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'leave_apply_id');

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl":
                //---------
                // 詳細
                //---------

                // IDを取得
                $id = $request->input('leave_apply_id');

                // クエリを作成
                $query = LeaveApply::query();

                // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                $query->where($this->guardRoomAdminTableWithSid());

                $leaveApply = $query
                    // MEMO: 重要：表示に使用する項目のみ取得
                    ->select(
                        'apply_time',
                        'leave_reason',
                        'comment',
                        // コードマスタの名称(ステータス)
                        'code_master.name as status',
                        // 生徒名の取得
                        'ext_student_kihon.name',
                    )
                    // コードマスターとJOIN
                    ->sdLeftJoin(CodeMaster::class, function ($join) {
                        $join->on('leave_apply.leave_state', '=', 'code_master.code')
                            ->where('data_type', AppConst::CODE_MASTER_5);
                    })
                    // 生徒基本情報とJOIN
                    ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                        $join->on('leave_apply.sid', '=', 'ext_student_kihon.sid');
                    })
                    // IDを指定
                    ->where('leave_apply_id', $id)
                    // MEMO: 取得できない場合はエラーとする
                    ->firstOrFail();

                return $leaveApply;

            case "#modal-dtl-acceptance":
                //--------
                // 受付
                //--------

                // IDを取得
                $id = $request->input('leave_apply_id');

                // クエリを作成
                $query = LeaveApply::query();

                // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                $query->where($this->guardRoomAdminTableWithSid());

                $leaveApply = $query
                    // MEMO: 重要：表示に使用する項目のみ取得
                    ->select(
                        'apply_time',
                        // 生徒名の取得
                        'ext_student_kihon.name',
                    )
                    // 生徒基本情報とJOIN
                    ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                        $join->on('leave_apply.sid', '=', 'ext_student_kihon.sid');
                    })
                    // IDを指定
                    ->where('leave_apply_id', $id)
                    // MEMO: 取得できない場合はエラーとする
                    ->firstOrFail();

                return $leaveApply;
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

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'leave_apply_id');

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl-acceptance":
                //--------
                // 受付
                //--------

                // トランザクション(例外時は自動的にロールバック)
                DB::transaction(function () use ($request) {

                    // IDを取得
                    $id = $request->input('leave_apply_id');

                    // 1件取得
                    $leaveApply = LeaveApply::where('leave_apply_id', $id)
                        // 未対応の場合のみ
                        ->where('leave_state', AppConst::CODE_MASTER_5_0)
                        // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                        ->where($this->guardRoomAdminTableWithSid())
                        // 該当データがない場合はエラーを返す
                        ->firstOrFail();

                    // 受付
                    $leaveApply->leave_state = AppConst::CODE_MASTER_5_1;

                    // 保存
                    $leaveApply->save();

                    //-------------------------
                    // お知らせメッセージの登録
                    //-------------------------

                    $notice = new Notice;

                    // タイトルと本文(Langから取得する)
                    $notice->title = Lang::get('message.notice.leave_accept.title');
                    $notice->text = Lang::get('message.notice.leave_accept.text');

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
                    $noticeDestination->sid = $leaveApply->sid;

                    // 保存
                    $noticeDestination->save();
                });

                return;
            default:
                // 該当しない場合
                $this->illegalResponseErr();
        }
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {

        $rules = array();

        // 独自バリデーション: リストのチェック イベント
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
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_5);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        $rules += LeaveApply::fieldRules('leave_state', [$validationStateList]);
        $rules += ExtStudentKihon::fieldRules('name');
        $rules += ExtRoom::fieldRules('roomcd', [$validationRoomList]);

        return $rules;
    }

    //==========================
    // 編集
    //==========================

    /**
     * 編集画面
     *
     * @param int $leaveApplyId 退会申請Id
     * @return void
     */
    public function edit($leaveApplyId)
    {

        // IDのバリデーション
        $this->validateIds($leaveApplyId);

        // 1件取得(beforeのキー取得)
        $leaveApply = LeaveApply::select(
            '*',
            // 生徒名の取得
            'ext_student_kihon.name'
        )
            // 生徒基本情報とJOIN
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('leave_apply.sid', '=', 'ext_student_kihon.sid');
            })
            ->where('leave_apply_id', $leaveApplyId)
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // ステータスのプルダウン取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_5);

        return view('pages.admin.leave_accept-edit', [
            'editData' => $leaveApply,
            'statusList' => $statusList,
            'rules' => $this->rulesForInput()
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
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // MEMO: 必ず登録する項目のみに絞る。
        $form = $request->only(
            'apply_time',
            'leave_reason',
            'leave_state',
            'comment',
        );

        // 対象データを取得(PKでユニークに取る)
        $leaveApply = LeaveApply::where('leave_apply_id', $request['leave_apply_id'])
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 複数の更新のためトランザクション
        DB::transaction(function () use ($leaveApply, $request, $form) {

            // 更新前の退会状態
            $before_leave_state = $leaveApply->leave_state;

            // 更新
            $leaveApply->fill($form)->save();

            // 退会済みに変更された場合、退会扱いとして対象生徒の情報を削除する
            if (
                $before_leave_state != AppConst::CODE_MASTER_5_3 &&
                $form['leave_state'] == AppConst::CODE_MASTER_5_3
            ) {

                // 退会申請情報削除
                $leaveApply->delete();

                // 欠席情報削除
                $absentExists = AbsentApply::where('sid', $leaveApply->sid)
                    ->exists();
                // 対象生徒のデータがあれば削除
                if ($absentExists) {
                    AbsentApply::where('sid', $leaveApply->sid)->delete();
                }

                // ギフトカード情報削除
                $cardExists = Card::where('sid', $leaveApply->sid)
                    ->exists();
                // 対象生徒のデータがあれば削除
                if ($cardExists) {
                    Card::where('sid', $leaveApply->sid)->delete();
                }

                // 問い合わせ情報削除
                $contactExists = Contact::where('sid', $leaveApply->sid)
                    ->exists();
                // 対象生徒のデータがあれば削除
                if ($contactExists) {
                    Contact::where('sid', $leaveApply->sid)->delete();
                }

                // コース変更・授業追加情報削除
                $courseExists = CourseApply::where('sid', $leaveApply->sid)
                    ->exists();
                // 対象生徒のデータがあれば削除
                if ($courseExists) {
                    CourseApply::where('sid', $leaveApply->sid)->delete();
                }

                // お知らせ宛先情報削除
                $noticeExists = NoticeDestination::where('sid', $leaveApply->sid)
                    ->exists();
                // 対象生徒のデータがあれば削除
                if ($noticeExists) {
                    NoticeDestination::where('sid', $leaveApply->sid)->delete();
                }

                // 教師関連情報削除
                $tutorRelateExists = TutorRelate::where('sid', $leaveApply->sid)
                    ->exists();
                // 対象生徒のデータがあれば削除
                if ($tutorRelateExists) {
                    TutorRelate::where('sid', $leaveApply->sid)->delete();
                }

                // アカウント情報削除
                $account = Account::where('account_id', $leaveApply->sid)
                    ->where('account_type', AppConst::CODE_MASTER_7_1)
                    // 該当データがない場合はエラーを返す
                    ->firstOrFail();
                // accountテーブルのdeleteを行う前に、emailを更新する（「DEL年月日時分秒@」を付加）
                $delStr = config('appconf.delete_email_prefix') . date("YmdHis") . config('appconf.delete_email_suffix');
                $account->email = $account->email . $delStr;
                $account->save();
                // accountテーブルのdelete
                $account->delete();
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
        $this->validateIdsFromRequest($request, 'leave_apply_id');

        // Formを取得
        $form = $request->all();

        // 1件取得
        $leaveApply = LeaveApply::where('leave_apply_id', $form['leave_apply_id'])
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 削除
        $leaveApply->delete();

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
        $validator = Validator::make($request->all(), $this->rulesForInput());
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput()
    {

        $rules = array();
        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {

            // ステータスのプルダウン取得
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_5);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        $rules += LeaveApply::fieldRules('leave_apply_id', ['required']);
        $rules += LeaveApply::fieldRules('apply_time', ['required']);
        $rules += LeaveApply::fieldRules('leave_reason', ['required']);
        $rules += LeaveApply::fieldRules('leave_state', ['required', $validationStateList]);
        $rules += LeaveApply::fieldRules('comment');

        return $rules;
    }
}
