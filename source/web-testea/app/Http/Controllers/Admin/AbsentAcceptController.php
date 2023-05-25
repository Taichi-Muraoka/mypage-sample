<?php

namespace App\Http\Controllers\Admin;

use App\Consts\AppConst;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libs\AuthEx;
use App\Mail\AbsentApplyToTeacher;
use App\Models\AbsentApply;
use App\Models\Account;
use App\Models\CodeMaster;
use App\Models\ExtRoom;
use App\Models\ExtSchedule;
use App\Models\ExtStudentKihon;
use App\Models\ExtRirekisho;
use App\Models\Notice;
use App\Models\NoticeDestination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\FuncAbsentTrait;

/**
 * 欠席申請受付 - コントローラ
 */
class AbsentAcceptController extends Controller
{

    // 機能共通処理：欠席申請
    use FuncAbsentTrait;

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
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);

        return view('pages.admin.absent_accept', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'statusList' => $statusList,
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

        $form = $request->all();

        // クエリを作成
        $query = AbsentApply::query();

        // ステータスの検索
        $query->SearchState($form);

        // 生徒の教室の検索(生徒基本情報参照)
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchRoom($form);
        }

        // 生徒名の絞込（生徒名、教師名で名前が被るため回避）
        $formStudent = ['name' => $request->name];
        (new ExtStudentKihon)->scopeSearchName($query, $formStudent);

        // 教師名の絞込
        $formTeacher = ['name' => $request->tname];
        (new ExtRirekisho)->scopeSearchName($query, $formTeacher);

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        // データの取得
        $absentList = $query->select(
            'absent_apply_id',
            'apply_time',
            'ext_student_kihon.name as sname',
            'lesson_date',
            'start_time',
            'room_names.room_name',
            'ext_rirekisho.name as tname',
            'code_master.name as status',
            'state as statecd',
            'absent_apply.created_at'
        )
            // 生徒情報
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('absent_apply.sid', '=', 'ext_student_kihon.sid');
            })
            // 教師情報
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('absent_apply.tid', '=', 'ext_rirekisho.tid');
            })
            // 教室名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('absent_apply.roomcd', '=', 'room_names.code');
            })
            // ステータス
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('absent_apply.state', '=', 'code_master.code')
                    ->where('code_master.data_type', AppConst::CODE_MASTER_1);
            })
            ->orderby('absent_apply.apply_time', 'desc')
            ->orderby('absent_apply.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $absentList);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        // // IDのバリデーション
        // $this->validateIdsFromRequest($request, 'absent_apply_id');

        // // モーダルによって処理を行う
        // $modal = $request->input('target');

        // switch ($modal) {
        //     case "#modal-dtl-acceptance":
        //         //---------
        //         // 受付
        //         //---------

        //         // 教室名取得のサブクエリ
        //         $room = $this->mdlGetRoomQuery();

        //         // IDを取得
        //         $absentApplyId = $request->input('absent_apply_id');

        //         // クエリを作成
        //         $query = AbsentApply::query();

        //         // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        //         $query->where($this->guardRoomAdminTableWithRoomCd());

        //         // データの取得
        //         $absentApply = $query->select(
        //             'ext_student_kihon.name as sname',
        //             'lesson_date',
        //             'start_time',
        //             'room_names.room_name',
        //             'ext_rirekisho.name as tname',
        //         )
        //             ->where('absent_apply_id', $absentApplyId)
        //             // 生徒情報
        //             ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
        //                 $join->on('absent_apply.sid', '=', 'ext_student_kihon.sid');
        //             })
        //             // 教室名の取得
        //             ->leftJoinSub($room, 'room_names', function ($join) {
        //                 $join->on('absent_apply.roomcd', '=', 'room_names.code');
        //             })
        //             // 教師名
        //             ->sdLeftJoin(ExtRirekisho::class, function ($join) {
        //                 $join->on('absent_apply.tid', '=', 'ext_rirekisho.tid');
        //             })
        //             ->firstOrFail();

        //         return $absentApply;

        //         break;
        //     case "#modal-dtl":
        //         //---------
        //         // 詳細
        //         //---------

        //         // 教室名取得のサブクエリ
        //         $room = $this->mdlGetRoomQuery();

        //         // IDを取得
        //         $absentApplyId = $request->input('absent_apply_id');

        //         // クエリを作成
        //         $query = AbsentApply::query();

        //         // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        //         $query->where($this->guardRoomAdminTableWithRoomCd());

        //         // データの取得
        //         $absentApply = $query->select(
        //             'apply_time',
        //             'ext_student_kihon.name as sname',
        //             'lesson_type',
        //             'absent.name as lesson_name',
        //             'lesson_date',
        //             'start_time',
        //             'room_names.room_name',
        //             'ext_rirekisho.name as tname',
        //             'absent_reason',
        //             'code_master.name as status'
        //         )
        //             ->where('absent_apply_id', $absentApplyId)
        //             // 生徒名の取得
        //             ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
        //                 $join->on('absent_apply.sid', '=', 'ext_student_kihon.sid');
        //             })
        //             // 教室名の取得
        //             ->leftJoinSub($room, 'room_names', function ($join) {
        //                 $join->on('absent_apply.roomcd', '=', 'room_names.code');
        //             })
        //             // 教師名
        //             ->sdLeftJoin(ExtRirekisho::class, function ($join) {
        //                 $join->on('absent_apply.tid', '=', 'ext_rirekisho.tid');
        //             })
        //             // ステータス
        //             ->sdLeftJoin(CodeMaster::class, function ($join) {
        //                 $join->on('absent_apply.state', '=', 'code_master.code')
        //                     ->where('code_master.data_type', AppConst::CODE_MASTER_1);
        //             })
        //             // 授業種別
        //             ->sdLeftJoin(CodeMaster::class, function ($join) {
        //                 $join->on('absent_apply.lesson_type', '=', 'absent.code')
        //                     ->where('absent.data_type', AppConst::CODE_MASTER_8);
        //             }, 'absent')
        //             ->firstOrFail();

        //         return $absentApply;

        //         break;
        //     default:
        //         // 該当しない場合
        //         $this->illegalResponseErr();
        // }
    }

    /**
     * モーダル処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function execModal(Request $request)
    {
        // // IDのバリデーション
        // $this->validateIdsFromRequest($request, 'absent_apply_id');

        // // モーダルによって処理を行う
        // $modal = $request->input('target');

        // switch ($modal) {
        //     case "#modal-dtl-acceptance":
        //         //--------
        //         // 受付
        //         //--------

        //         // IDのバリデーション
        //         $this->validateIdsFromRequest($request, 'absent_apply_id');

        //         // トランザクション(例外時は自動的にロールバック)
        //         DB::transaction(function () use ($request) {

        //             // IDを取得
        //             $absentApplyId = $request->input('absent_apply_id');

        //             // 1件取得
        //             $absentApply = AbsentApply::where('absent_apply_id', $absentApplyId)
        //                 // 申請中の場合のみ
        //                 ->where('state', AppConst::CODE_MASTER_1_0)
        //                 // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        //                 ->where($this->guardRoomAdminTableWithRoomCd())
        //                 // 該当データがない場合はエラーを返す
        //                 ->firstOrFail();

        //             // 受付
        //             $absentApply->state = AppConst::CODE_MASTER_1_1;

        //             // 保存
        //             $absentApply->save();

        //             //-------------------------
        //             // お知らせメッセージの使用データ取得
        //             //-------------------------

        //             // 教室名取得のサブクエリ
        //             $room = $this->mdlGetRoomQuery();

        //             $acceptanceMng = AbsentApply::select(
        //                 'lesson_date',
        //                 'start_time',
        //                 'room_names.room_name',
        //                 'ext_student_kihon.name as sname',
        //                 'absent_apply.tid'
        //             )
        //                 ->where('absent_apply_id', $absentApplyId)
        //                 // 教室名の取得
        //                 ->leftJoinSub($room, 'room_names', function ($join) {
        //                     $join->on('absent_apply.roomcd', '=', 'room_names.code');
        //                 })
        //                 // 生徒情報
        //                 ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
        //                     $join->on('absent_apply.sid', '=', 'ext_student_kihon.sid');
        //                 })
        //                 // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        //                 ->where($this->guardRoomAdminTableWithRoomCd())
        //                 ->firstOrFail();

        //             //-------------------------
        //             // お知らせメッセージの登録（生徒）
        //             //-------------------------

        //             $notice = new Notice;

        //             // タイトルと本文(Langから取得する)
        //             $notice->title = Lang::get('message.notice.absent_apply_accept_student.title');
        //             $notice->text = Lang::get(
        //                 'message.notice.absent_apply_accept_student.text',
        //                 [
        //                     'lessonDate' => $acceptanceMng->lesson_date->format('Y/m/d'),
        //                     'startTime' => $acceptanceMng->start_time->format('H:i'),
        //                     'roomName' => $acceptanceMng->room_name
        //                 ]
        //             );

        //             // お知らせ種別
        //             $notice->notice_type = AppConst::CODE_MASTER_14_4;

        //             // 事務局ID
        //             $account = Auth::user();
        //             $notice->adm_id = $account->account_id;
        //             $notice->roomcd = $account->roomcd;

        //             // 保存
        //             $notice->save();

        //             //-------------------------
        //             // お知らせ宛先の登録（生徒）
        //             //-------------------------

        //             $noticeDestination = new NoticeDestination();

        //             // 先に登録したお知らせIDをセット
        //             $noticeDestination->notice_id = $notice->notice_id;

        //             // 宛先連番: 1固定
        //             $noticeDestination->destination_seq = 1;

        //             // 宛先種別（生徒）
        //             $noticeDestination->destination_type = AppConst::CODE_MASTER_15_2;

        //             // 生徒No
        //             $noticeDestination->sid = $absentApply->sid;

        //             // 保存
        //             $noticeDestination->save();

        //             //-------------------------
        //             // お知らせメッセージの登録(教師)
        //             //-------------------------

        //             $notice = new Notice;

        //             // タイトルと本文(Langから取得する)
        //             $notice->title = Lang::get('message.notice.absent_apply_accept_teacher.title');
        //             $notice->text = Lang::get(
        //                 'message.notice.absent_apply_accept_teacher.text',
        //                 [
        //                     'sname' => $acceptanceMng->sname,
        //                     'lessonDate' => $acceptanceMng->lesson_date->format('Y/m/d'),
        //                     'startTime' => $acceptanceMng->start_time->format('H:i'),
        //                     'roomName' => $acceptanceMng->room_name
        //                 ]
        //             );

        //             // お知らせ種別
        //             $notice->notice_type = AppConst::CODE_MASTER_14_4;

        //             // 事務局ID
        //             $account = Auth::user();
        //             $notice->adm_id = $account->account_id;
        //             $notice->roomcd = $account->roomcd;

        //             // 保存
        //             $notice->save();

        //             //-------------------------
        //             // お知らせ宛先の登録(教師)
        //             //-------------------------

        //             $noticeDestination = new NoticeDestination();

        //             // 先に登録したお知らせIDをセット
        //             $noticeDestination->notice_id = $notice->notice_id;

        //             // 宛先連番: 1固定
        //             $noticeDestination->destination_seq = 1;

        //             // 宛先種別（教師）
        //             $noticeDestination->destination_type = AppConst::CODE_MASTER_15_3;

        //             // 教師No
        //             $noticeDestination->tid = $absentApply->tid;

        //             // 保存
        //             $res = $noticeDestination->save();

        //             // save成功時のみ送信
        //             if ($res) {

        //                 $mail_body = [
        //                     'name' => $acceptanceMng->sname,
        //                     'datetime' => $acceptanceMng->lesson_date->format('Y/m/d') .
        //                         ' ' . $acceptanceMng->start_time->format('H:i'),
        //                     'room_name' => $acceptanceMng->room_name
        //                 ];

        //                 $teacherAccount = Account::select('email')
        //                     ->where('account_id', $acceptanceMng->tid)
        //                     ->where('account_type', AppConst::CODE_MASTER_7_2)
        //                     ->firstOrFail();

        //                 // 欠席申請メール送信用の、事務局用メールアドレスを設定(env)から取得
        //                 $email = $teacherAccount->email;
        //                 Mail::to($email)->send(new AbsentApplyToTeacher($mail_body));
        //             }
        //         });
        //         return;
        //     default:
        //         // 該当しない場合
        //         $this->illegalResponseErr();
        // }
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

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 教室コード
        $rules += ExtRoom::fieldRules('roomcd', [$validationRoomList]);
        // ステータス
        $rules += AbsentApply::fieldRules('state', [$validationStateList]);
        // 生徒名
        $rules += ExtStudentKihon::fieldRules('name');
        // 教師名
        $rules += AbsentApply::fieldRules('tname');

        return $rules;
    }

    //==========================
    // 編集
    //==========================

    /**
     * 編集画面
     *
     * @param int absentApplyId 欠席申請ID
     * @return void
     */
    public function edit($absentApplyId)
    {
        // // IDのバリデーション
        // $this->validateIds($absentApplyId);

        // // 編集データの取得
        // $query = AbsentApply::query();
        // $editData = $query->select(
        //     'absent_apply_id',
        //     'absent_apply.sid',
        //     'absent_apply.tid',
        //     'lesson_type',
        //     'apply_time',
        //     'ext_student_kihon.name',
        //     'lesson_date',
        //     'start_time',
        //     'absent_apply.id',
        //     'absent_reason',
        //     'state'
        // )
        //     // 生徒情報
        //     ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
        //         $join->on('absent_apply.sid', '=', 'ext_student_kihon.sid');
        //     })
        //     // キーの指定
        //     ->where('absent_apply_id', $absentApplyId)
        //     // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        //     ->where($this->guardRoomAdminTableWithRoomCd())
        //     ->firstOrFail();

        // if ($editData->lesson_type == AppConst::CODE_MASTER_8_1) {
        //     // 個別教室
        //     unset($editData->lesson_date);
        //     unset($editData->start_time);
        //     unset($editData->tid);
        // } else if ($editData->lesson_type == AppConst::CODE_MASTER_8_2) {
        //     // 家庭教師
        //     unset($editData->id);
        // } else {
        //     // エラー
        //     $this->illegalResponseErr();
        // }

        // // 生徒ID
        // $sid = $editData->sid;

        // // レギュラーと個別講習のプルダウンメニューを作成
        // $scheduleMaster = $this->getScheduleMasterList($sid);

        // // 教師名のプルダウンメニューを作成
        // $home_teachers = $this->getTeacherList($sid);

        // // ステータスプルダウン
        // $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);

        return view('pages.admin.absent_accept-edit', [
            'editData' => null,
            'rules' => $this->rulesForInput(null),
            'statusList' => null,
            'scheduleMaster' => null,
            'teacherList' => null,
        ]);
    }

    /**
     * 教室・教師情報取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getDataSelect(Request $request)
    {
        // 生徒IDを取得するために、欠席申請IDを受け取る
        // 自分の教室の欠席申請IDのみとし、その上で生徒IDを取得する

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id', 'absentApplyId');

        // IDを取得
        $schedule_id = $request->input('id');
        $absentApplyId = $request->input('absentApplyId');

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------

        // 欠席申請からsidを取得
        $sid = $this->getSidFormAbsentApply($absentApplyId);

        // レギュラーと個別講習のプルダウンメニューを作成
        $scheduleMaster = $this->getScheduleMasterList($sid);
        $this->guardListValue($scheduleMaster, $schedule_id);

        //------------------------
        // スケジュールから、教師名・教室を取得
        //------------------------

        $lesson = $this->getScheduleDetail($schedule_id);

        return [
            'class_name' => $lesson->room_name_full,
            'teacher_name' => $lesson->name
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
            'apply_time',
            'lesson_type',
            'absent_reason',
            'state'
        );

        // 対象データを取得(PKでユニークに取る)
        $absentApply = AbsentApply::where('absent_apply_id', $request['absent_apply_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        if ($request->input('lesson_type') == AppConst::CODE_MASTER_8_1) {
            //--------------
            // 個別教室
            //--------------
            // スケジュールを取得
            $extSchedule = ExtSchedule::select(
                'lesson_date',
                'start_time',
                'tid',
                'roomcd'
            )
                ->where('id', $request['id'])
                ->firstOrFail();

            $absentApply->id = $request['id'];

            // スケジュールからセット
            $absentApply->lesson_date = $extSchedule['lesson_date'];
            $absentApply->start_time = $extSchedule['start_time'];
            $absentApply->tid = $extSchedule['tid'];
            $absentApply->roomcd = $extSchedule['roomcd'];
        } elseif ($request->input('lesson_type') == AppConst::CODE_MASTER_8_2) {
            //--------------
            // 家庭教師
            //--------------
            $absentApply->id = null;
            $absentApply->lesson_date = $request['lesson_date'];
            $absentApply->start_time = $request['start_time'];
            $absentApply->tid = $request['tid'];
            $absentApply->roomcd = AppConst::EXT_GENERIC_MASTER_101_900;
        } else {
            $this->illegalResponseErr();
        }

        // 更新
        $absentApply->fill($form)->save();

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
        $this->validateIdsFromRequest($request, 'absent_apply_id');

        // Formを取得
        $form = $request->all();

        // 1件取得
        $card = AbsentApply::where('absent_apply_id', $form['absent_apply_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
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

        // 独自バリデーション: 重複チェック(個別教室登録用)
        $validationDuplicateRegular = function ($attribute, $value, $fail) use ($request) {

            if (!isset($request['id'])) {
                // requiredでチェックするのでreturn
                return;
            }

            // 対象データを取得(PKでユニークに取る)
            // スケジュールID
            $exists = AbsentApply::where('id', $request['id'])
                // 授業種別
                ->where('lesson_type', AppConst::CODE_MASTER_8_1)
                // キー以外
                ->where('absent_apply_id', '!=', $request['absent_apply_id'])
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // 独自バリデーション: 重複チェック(家庭教師登録用)
        $validationDuplicateHomeTeacher = function ($attribute, $value, $fail) use ($request) {

            if (!isset($request['lesson_date'])) {
                // requiredでチェックするのでreturn
                return;
            }

            // 欠席申請からsidを取得
            $sid = $this->getSidFormAbsentApply($request['absent_apply_id']);

            $lesson_date = $request['lesson_date'];
            $start_time = $request['start_time'];

            // 対象データを取得(PKでユニークに取る)
            $exists = AbsentApply::where('sid', $sid)
                ->where('lesson_date', $lesson_date)
                ->where('start_time', $start_time)
                // 授業種別
                ->where('lesson_type', AppConst::CODE_MASTER_8_2)
                // キー以外
                ->where('absent_apply_id', '!=', $request['absent_apply_id'])
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // 独自バリデーション: 授業種別（ラジオ）
        $validationRadioLessonType = function ($attribute, $value, $fail) use ($request) {

            // ラジオの値のチェック
            if (
                $request->input('lesson_type') != AppConst::CODE_MASTER_8_1 &&
                $request->input('lesson_type') != AppConst::CODE_MASTER_8_2
            ) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
            // 教室管理者の場合、ラジオの値の変更は不可とする
            if (AuthEx::isRoomAdmin()) {
                $account = Auth::user();
                if (
                    $account->roomcd == AppConst::EXT_GENERIC_MASTER_101_900 &&
                    $request->input('lesson_type') == AppConst::CODE_MASTER_8_1
                ) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                } else if (
                    $account->roomcd != AppConst::EXT_GENERIC_MASTER_101_900 &&
                    $request->input('lesson_type') == AppConst::CODE_MASTER_8_2
                ) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 独自バリデーション: リストのチェック 授業日時
        $validationScheduleList =  function ($attribute, $value, $fail) use ($request) {

            // 欠席申請からsidを取得
            $sid = $this->getSidFormAbsentApply($request['absent_apply_id']);

            // レギュラーと個別講習のプルダウンメニューを作成
            $scheduleMaster = $this->getScheduleMasterList($sid);
            if (!isset($scheduleMaster[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 教師名
        $validationTeacherList =  function ($attribute, $value, $fail) use ($request) {

            // 欠席申請からsidを取得
            $sid = $this->getSidFormAbsentApply($request['absent_apply_id']);

            // 教師名のプルダウンメニューを作成
            $home_teachers = $this->getTeacherList($sid);

            if (!isset($home_teachers[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        $rules += AbsentApply::fieldRules('absent_apply_id', ['required']);
        $rules += AbsentApply::fieldRules('apply_time', ['required']);

        // 授業種別 (値のチェックも行う)
        $rules += AbsentApply::fieldRules('lesson_type', ['required', $validationRadioLessonType]);

        $rule = [];
        if ($request && $request->input('lesson_type') == AppConst::CODE_MASTER_8_1) {
            // 個別教室登録
            $rule = ['required', $validationDuplicateRegular, $validationScheduleList];
        }
        $rules += ExtSchedule::fieldRules('id', $rule);

        $rule = [];
        if ($request && $request->input('lesson_type') == AppConst::CODE_MASTER_8_2) {
            // 家庭教師登録
            $rule = ['required', $validationTeacherList];
        }
        $rules += AbsentApply::fieldRules('tid', $rule);

        $rule = [];
        if ($request && $request->input('lesson_type') == AppConst::CODE_MASTER_8_2) {
            // 家庭教師登録
            $rule = ['required'];
        }
        $rules += AbsentApply::fieldRules('lesson_date', $rule);

        $rule = [];
        if ($request && $request->input('lesson_type') == AppConst::CODE_MASTER_8_2) {
            // 家庭教師登録
            $rule = ['required', $validationDuplicateHomeTeacher];
        }
        $rules += AbsentApply::fieldRules('start_time', $rule);

        $rules += AbsentApply::fieldRules('absent_reason', ['required']);
        $rules += AbsentApply::fieldRules('state', ['required', $validationStateList]);

        return $rules;
    }

    /**
     * 欠席申請IDから生徒IDを取得
     *
     * @param int $sid 生徒ID
     */
    private function getSidFormAbsentApply($absentApplyId)
    {
        // 欠席申請からsidを取得
        $absentApply = AbsentApply::select(
            'sid',
        )
            ->where('absent_apply_id', $absentApplyId)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            ->firstOrFail();

        return $absentApply->sid;
    }

    /**
     * 授業日時を取得
     *
     * @param int $sid 生徒ID
     */
    private function getScheduleMasterList($sid)
    {

        // 生徒に紐づくスケジュールを取得
        // 教室管理者の場合、自分の教室コードのスケジュールのみにガードを掛ける
        if (AuthEx::isRoomAdmin()) {
            $account = Auth::user();
            $myRoomCd = $account->roomcd;
        } else {
            $myRoomCd = null;
        }
        $lessons = $this->getStudentSchedule($sid, $myRoomCd);

        // レギュラーと個別講習のプルダウンメニューを作成
        $scheduleMaster = $this->mdlGetScheduleMasterList($lessons);

        return $scheduleMaster;
    }
}
