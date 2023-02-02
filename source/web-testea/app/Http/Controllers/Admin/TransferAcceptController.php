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
 * 振替連絡受付 - コントローラ
 */
class TransferAcceptController extends Controller
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
        $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);

        return view('pages.admin.transfer_accept', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'states' => $states,
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

        // クエリを作成
        $query = TransferApply::query();

        // データを取得
        $query->SearchState($form);

        // 生徒名の絞込（生徒名、教師名で名前が被るため回避）
        $formStudent = ['name' => $request->student_name];
        (new ExtStudentKihon)->scopeSearchName($query, $formStudent);

        // 教師名の絞込
        $formTeacher = ['name' => $request->teacher_name];
        (new ExtRirekisho)->scopeSearchName($query, $formTeacher);

        // 教室の検索(スケジュールの教室コードを参照)
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd(ExtSchedule::class));
        } else {
            // 管理者の場合検索フォームから取得
            (new ExtSchedule)->scopeSearchRoomcd($query, $form);
        }

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        $transferApply = $query
            ->select(
                'transfer_apply.transfer_apply_id',
                'transfer_apply.apply_time',
                'ext_rirekisho.name as teacher_name',
                'ext_schedule.lesson_date',
                'ext_schedule.start_time',
                'room_names.room_name as room_name',
                'ext_student_kihon.name as student_name',
                'transfer_apply.state as statecd',
                'code_master.name as state',
                'transfer_apply.created_at'
            )
            // 状態
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('transfer_apply.state', '=', 'code_master.code')
                    ->where('data_type', AppConst::CODE_MASTER_1);
            })
            // 授業日時、教師、生徒、教室
            // スケジュールIDで1対1
            ->sdJoin(ExtSchedule::class, function ($join) {
                $join->on('transfer_apply.id', '=', 'ext_schedule.id');
            })
            // 生徒基本情報の取得
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('ext_schedule.sid', '=', 'ext_student_kihon.sid');
            })
            // 教師情報の取得
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('ext_schedule.tid', '=', 'ext_rirekisho.tid');
            })
            // 教室名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('ext_schedule.roomcd', '=', 'room_names.code');
            })
            ->orderBy('transfer_apply.apply_time', 'desc')
            ->orderBy('transfer_apply.created_at', 'desc');

        return $this->getListAndPaginator($request, $transferApply);
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
        $this->validateIdsFromRequest($request, 'transfer_apply_id');

        // IDを取得
        $transferApplyId = $request->input('transfer_apply_id');

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl-acceptance":
                //---------
                // 受付
                //---------

                // 教室名取得のサブクエリ
                $room = $this->mdlGetRoomQuery();

                // クエリを作成
                $query = TransferApply::query();

                // 教室管理者の場合、自分の教室コードのみにガードを掛ける
                $query->where($this->guardRoomAdminTableWithRoomCd(ExtSchedule::class));

                $transferApply = $query
                    ->where('transfer_apply_id', $transferApplyId)
                    ->select(
                        'ext_rirekisho.name as teacher_name',
                        'ext_schedule.lesson_date',
                        'ext_schedule.start_time',
                        'room_names.room_name as room_name',
                        'ext_student_kihon.name as student_name',
                        'transfer_apply.transfer_date',
                        'transfer_apply.transfer_time',
                    )
                    // 授業日時、教師、生徒、教室
                    ->sdJoin(ExtSchedule::class, function ($join) {
                        $join->on('transfer_apply.id', '=', 'ext_schedule.id');
                    })
                    // 生徒基本情報の取得
                    ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                        $join->on('ext_schedule.sid', '=', 'ext_student_kihon.sid');
                    })
                    // 教師情報の取得
                    ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                        $join->on('ext_schedule.tid', '=', 'ext_rirekisho.tid');
                    })
                    // 教室名の取得
                    ->leftJoinSub($room, 'room_names', function ($join) {
                        $join->on('ext_schedule.roomcd', '=', 'room_names.code');
                    })
                    ->firstOrFail();

                return $transferApply;

            case "#modal-dtl":
                //---------
                // 詳細
                //---------

                // 教室名取得のサブクエリ
                $room = $this->mdlGetRoomQuery();

                // クエリを作成
                $query = TransferApply::query();

                // 教室管理者の場合、自分の教室コードのみにガードを掛ける
                $query->where($this->guardRoomAdminTableWithRoomCd(ExtSchedule::class));

                $transferApply = $query
                    ->where('transfer_apply_id', $transferApplyId)
                    ->select(
                        'transfer_apply.apply_time',
                        'ext_rirekisho.name as teacher_name',
                        'ext_schedule.lesson_date',
                        'ext_schedule.start_time',
                        'room_names.room_name as room_name',
                        'ext_student_kihon.name as student_name',
                        'transfer_apply.transfer_date',
                        'transfer_apply.transfer_time',
                        'transfer_apply.transfer_reason',
                        'code_master.name as state'
                    )
                    // 状態
                    ->sdLeftJoin(CodeMaster::class, function ($join) {
                        $join->on('transfer_apply.state', '=', 'code_master.code')
                            ->where('data_type', AppConst::CODE_MASTER_1);
                    })
                    // 授業日時、教師、生徒、教室
                    ->sdJoin(ExtSchedule::class, function ($join) {
                        $join->on('transfer_apply.id', '=', 'ext_schedule.id');
                    })
                    // 生徒基本情報の取得
                    ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                        $join->on('ext_schedule.sid', '=', 'ext_student_kihon.sid');
                    })
                    // 教師情報の取得
                    ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                        $join->on('ext_schedule.tid', '=', 'ext_rirekisho.tid');
                    })
                    // 教室名の取得
                    ->leftJoinSub($room, 'room_names', function ($join) {
                        $join->on('ext_schedule.roomcd', '=', 'room_names.code');
                    })
                    ->firstOrFail();

                return $transferApply;

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
        $this->validateIdsFromRequest($request, 'transfer_apply_id');

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl-acceptance":

                // 複数の更新のためトランザクション
                DB::transaction(function () use ($request) {

                    // 振替連絡IDを取得
                    $transferApplyId = $request['transfer_apply_id'];

                    // 教室名取得のサブクエリ
                    $room = $this->mdlGetRoomQuery();

                    // クエリを作成
                    $query = TransferApply::query();

                    // 教室管理者の場合、自分の教室コードのみにガードを掛ける
                    $query->where($this->guardRoomAdminTableWithRoomCd(ExtSchedule::class));

                    $transferApplyData = $query
                        ->where('transfer_apply_id', $transferApplyId)
                        ->select(
                            'ext_schedule.tid',
                            'ext_schedule.lesson_date',
                            'ext_schedule.start_time',
                            'room_names.room_name as room_name',
                            'transfer_apply.transfer_date',
                            'transfer_apply.transfer_time',
                        )
                        // 授業日時、教師、生徒、教室
                        ->sdJoin(ExtSchedule::class, function ($join) {
                            $join->on('transfer_apply.id', '=', 'ext_schedule.id');
                        })
                        // 教室名の取得
                        ->leftJoinSub($room, 'room_names', function ($join) {
                            $join->on('ext_schedule.roomcd', '=', 'room_names.code');
                        })
                        ->firstOrFail();

                    //-------------------------
                    // ◆当該レコードの状態を１：対応済みにupdateする。
                    //-------------------------

                    // 1件取得
                    $transferApply = TransferApply::where('transfer_apply_id', $transferApplyId)
                        // 未対応の場合のみ
                        ->where('state', AppConst::CODE_MASTER_1_0)
                        ->firstOrFail();

                    // 受付
                    $transferApply->state = AppConst::CODE_MASTER_1_1;

                    // 保存
                    $transferApply->save();

                    //-------------------------
                    // お知らせメッセージの登録
                    //-------------------------
                    //-------------------------
                    // ◆教師への受付メッセージ自動送信
                    // １．お知らせ情報に、お知らせ種別=4のお知らせをinsertする。
                    //-------------------------

                    $notice = new Notice;

                    // JOINで取得された項目はCarbonにならないっぽい・・
                    // 自分でCarbonで初期化する
                    $cbLessonDate = new Carbon($transferApplyData->lesson_date);
                    $cbLessonTime = new Carbon($transferApplyData->start_time);
                    $cbTransferDate = new Carbon($transferApplyData->transfer_date);
                    $cbTransferTime = new Carbon($transferApplyData->transfer_time);

                    // タイトルと本文(Langから取得する)
                    $notice->title = Lang::get('message.notice.transfer_accept.title');
                    $notice->text = Lang::get(

                        'message.notice.transfer_accept.text',
                        [
                            'lesson_datetime' => $cbLessonDate->format('Y/m/d') . ' ' . $cbLessonTime->format('H:i'),
                            'room_name' => $transferApplyData->room_name,
                            'transfer_datetime' => $cbTransferDate->format('Y/m/d') . ' ' . $cbTransferTime->format('H:i'),
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
                    // ◆教師への受付メッセージ自動送信
                    // ２．受付処理を行ったレコードについて、お知らせ宛先情報に以下の条件でinsertする。
                    // ・お知らせID=上記で作成したお知らせのお知らせID
                    // ・宛先連番=1
                    // ・宛先種別=3
                    // ・教師No.=各レコードの教師No.
                    //-------------------------

                    $noticeDestination = new NoticeDestination;

                    // 先に登録したお知らせIDをセット
                    $noticeDestination->notice_id = $notice->notice_id;
                    // 宛先連番
                    $noticeDestination->destination_seq = 1;
                    // 宛先種別（教師）
                    $noticeDestination->destination_type = AppConst::CODE_MASTER_15_3;
                    // 教師No
                    $noticeDestination->tid = $transferApplyData->tid;

                    // 保存
                    $noticeDestination->save();
                });

                break;
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

        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 教室プルダウン
            $rooms = $this->mdlGetRoomList(true);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {

            // ステータスプルダウン
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        // 教室コード
        $rules += ExtSchedule::fieldRules('roomcd', [$validationRoomList]);
        // ステータス
        $rules += TransferApply::fieldRules('state', [$validationStateList]);
        // 生徒名
        $ruleSname = ExtStudentKihon::getFieldRule('name');
        $rules += ['student_name' => $ruleSname];
        // 教師名
        $ruleTname = ExtRirekisho::getFieldRule('name');
        $rules += ['teacher_name' => $ruleTname];

        return $rules;
    }

    //==========================
    // 編集
    //==========================

    /**
     * 編集画面
     *
     * @param int transferApplyId 振替連絡Id
     * @return view
     */
    public function edit($transferApplyId)
    {
        // IDのバリデーション
        $this->validateIds($transferApplyId);

        // ステータスプルダウン
        $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);

        // IDから編集するデータを取得する
        // クエリを作成
        $query = TransferApply::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd(ExtSchedule::class));

        // 詳細を取得
        $transferApply = $query
            ->where('transfer_apply_id', $transferApplyId)
            ->select(
                'transfer_apply.transfer_apply_id',
                'transfer_apply.apply_time',
                'ext_rirekisho.name as teacher_name',
                'ext_schedule.id',
                'ext_schedule.id as _id', // hiddenに退避
                'ext_schedule.tid',
                'ext_schedule.sid',
                'transfer_apply.transfer_date',
                'transfer_apply.transfer_time',
                'transfer_apply.transfer_reason',
                'transfer_apply.state'
            )
            // 授業日時、教師、生徒、教室
            ->sdJoin(ExtSchedule::class, function ($join) {
                $join->on('transfer_apply.id', '=', 'ext_schedule.id');
            })
            // 教師情報の取得
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('ext_schedule.tid', '=', 'ext_rirekisho.tid');
            })
            ->firstOrFail();

        // 教師の担当している生徒の一覧を取得(家庭教師は除く)
        $students = $this->mdlGetStudentListForT(null, $transferApply->tid, AppConst::EXT_GENERIC_MASTER_101_900);

        return view('pages.admin.transfer_accept-edit', [
            'editData' => $transferApply,
            'rules' => $this->rulesForInput(null),
            'states' => $states,
            'students' => $students
        ]);
    }

    /**
     * 教室・教師情報取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 教室、教師情報
     */
    public function getDataSelect(Request $request)
    {

        // IDのバリデーション
        // スケジュールIDは生徒IDの後に受け取れるのでsidのみ必須チェックする
        $this->validateIdsFromRequest($request, 'transferApplyId', 'sid');

        // IDを取得
        $transferApplyId =  $request->input('transferApplyId');
        $schedule_id = $request->input('id');
        $sid = $request->input('sid');

        // transferApplyIdを取得してtidを取得する
        $tid = $this->getTidFormTransferApply($transferApplyId);

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------

        // 教師の担当している生徒の一覧を取得(家庭教師は除く)
        $students = $this->mdlGetStudentListForT(null, $tid, AppConst::EXT_GENERIC_MASTER_101_900);

        // 生徒一覧にsidがあるかチェック
        $this->guardListValue($students, $sid);

        //---------------------------
        // スケジュールプルダウンの作成
        //---------------------------

        // 教師のスケジュールを取得
        // 教室管理者の場合、自分の教室コードのスケジュールのみにガードを掛ける
        if (AuthEx::isRoomAdmin()) {
            $account = Auth::user();
            $myRoomCd = $account->roomcd;
        } else {
            $myRoomCd = null;
        }

        // 教師のスケジュールを取得(指定された生徒IDで絞る)
        $lessons = $this->getTeacherScheduleList($tid, $myRoomCd, $sid, false);

        // スケジュールのプルダウンメニューを作成
        $scheduleMaster = $this->mdlGetScheduleMasterList($lessons);

        //---------------------------
        // 教室を返却する
        //---------------------------
        $room_name_full = null;
        if (filled($schedule_id)) {
            // idが指定されている場合のみ

            // [ガード] スケジュールIDがプルダウンの中にあるかチェック
            $this->guardListValue($scheduleMaster, $schedule_id);

            // スケジュールの取得(ガードはこの中でも掛ける)
            $lesson = $this->mdlGetScheduleDtl($schedule_id);

            // 変数にセット
            $room_name_full = $lesson->room_name_full;
        }

        return [
            'selectItems' => $this->objToArray($scheduleMaster),
            'class_name' => $room_name_full
        ];
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

        $form = $request->only(
            'id',
            'transfer_date',
            'transfer_time',
            'transfer_reason',
            'state',
            'apply_time'
        );

        // 教室管理者の場合、自分の教室コードのスケジュールのみにガードを掛ける
        ExtSchedule::where('id', $request['id'])
            ->where($this->guardRoomAdminTableWithRoomCd())
            ->firstOrFail();

        // 対象データを取得(IDでユニークに取る)
        $transferApply = TransferApply::where('transfer_apply_id', $request['transfer_apply_id'])
            ->firstOrFail();

        // 登録
        $transferApply->fill($form)->save();

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
        $this->validateIdsFromRequest($request, 'transfer_apply_id');

        // Formを取得
        $form = $request->all();

        // 教室管理者の場合、自分の教室コードのスケジュールのみにガードを掛ける
        ExtSchedule::where('id', $form['id'])
            ->where($this->guardRoomAdminTableWithRoomCd())
            ->firstOrFail();

        // 対象データを取得(IDでユニークに取る)
        $transferApply = TransferApply::where('transfer_apply_id', $form['transfer_apply_id'])
            ->firstOrFail();

        // 削除
        $transferApply->delete();

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

        // 独自バリデーション: リストのチェック 生徒ID
        $validationSidList =  function ($attribute, $value, $fail) use ($request) {

            // transferApplyIdを取得してtidを取得する
            $tid = $this->getTidFormTransferApply($request['transfer_apply_id']);

            // 教師の担当している生徒の一覧を取得(家庭教師は除く)
            $students = $this->mdlGetStudentListForT(null, $tid, AppConst::EXT_GENERIC_MASTER_101_900);

            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 重複チェック(スケジュールID)
        $validationKeySchedule = function ($attribute, $value, $fail) use ($request) {

            if (!isset($request['id'])) {
                // requiredでチェックするのでreturn
                return;
            }

            // 対象データを取得(スケジュールID)
            $exists = TransferApply::where('id', $request['id'])
                ->where('transfer_apply_id', '!=', $request['transfer_apply_id'])
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // 独自バリデーション: リストのチェック 授業日時
        $validationScheduleMasterList =  function ($attribute, $value, $fail) use ($request) {

            // sidの取得(チェックはvalidationSidListで行う)
            $sid = $request['sid'];

            // transferApplyIdを取得してtidを取得する
            $tid = $this->getTidFormTransferApply($request['transfer_apply_id']);

            // 教師のスケジュールを取得
            // 教室管理者の場合、自分の教室コードのスケジュールのみにガードを掛ける
            if (AuthEx::isRoomAdmin()) {
                $account = Auth::user();
                $myRoomCd = $account->roomcd;
            } else {
                $myRoomCd = null;
            }
            $lessons = $this->getTeacherScheduleList($tid, $myRoomCd, $sid, false);

            // スケジュールのプルダウンメニューを作成
            $scheduleMaster = $this->mdlGetScheduleMasterList($lessons);

            if (!isset($scheduleMaster[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {

            // ステータスプルダウン
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 生徒ID。TransferApplyには格納されないが、スケジュールIDのチェックのため
        $rule = [];
        $rule = ['required', $validationSidList];
        $rules += ExtSchedule::fieldRules('sid', $rule);

        // 申請日
        $rules += TransferApply::fieldRules('apply_time', ['required']);
        // スケジュールID
        $rules += TransferApply::fieldRules('id', ['required', $validationKeySchedule, $validationScheduleMasterList]);
        // 振替日
        $rules += TransferApply::fieldRules('transfer_date', ['required']);
        // 開始時刻
        $rules += TransferApply::fieldRules('transfer_time', ['required']);
        // 振替理由
        $rules += TransferApply::fieldRules('transfer_reason', ['required']);
        // ステータス
        $rules += TransferApply::fieldRules('state', ['required', $validationStateList]);

        return $rules;
    }

    /**
     * 欠席申請IDから教師IDを取得
     * 
     * @param int $transferApplyId 振替ID
     */
    private function getTidFormTransferApply($transferApplyId)
    {

        // IDから編集するデータを取得する
        // クエリを作成
        $query = TransferApply::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd(ExtSchedule::class));

        $transferApply = $query
            ->where('transfer_apply_id', $transferApplyId)
            ->select(
                'ext_schedule.tid'
            )
            // 授業日時、教師、生徒、教室
            ->sdJoin(ExtSchedule::class, function ($join) {
                $join->on('transfer_apply.id', '=', 'ext_schedule.id');
            })
            ->firstOrFail();

        return $transferApply->tid;
    }
}
