<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\CodeMaster;
use App\Models\CourseApply;
use App\Models\ExtStudentKihon;
use App\Models\ExtRoom;
use App\Models\Notice;
use App\Models\NoticeDestination;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FuncCourseTrait;

/**
 * コース変更・授業追加受付 - コントローラ
 */
class CourseMngController extends Controller
{

    // 機能共通処理：コース変更・授業追加
    use FuncCourseTrait;

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
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);

        return view('pages.admin.course_mng', [
            'statusList' => $statusList,
            'rooms' => $rooms,
            'editData' => null,
            'rules' => $this->rulesForSearch()
        ]);
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
        $this->validateIdsFromRequest($request, 'change_id');

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl":
                //---------
                // 詳細
                //---------

                // IDを取得
                $changeId = $request->input('change_id');

                // クエリを作成
                $query = CourseApply::query();

                // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                $query->where($this->guardRoomAdminTableWithSid());

                // データ取得
                $courseApply = $query
                    ->select(
                        'apply_time',
                        'ext_student_kihon.name',
                        'course.name as course_name',
                        'changes_text',
                        'code_master.name as status',
                        'comment'
                    )
                    // 変更状態
                    ->sdleftJoin(CodeMaster::class, function ($join) {
                        $join->on('course_apply.changes_state', '=', 'code_master.code')
                            ->where('data_type', AppConst::CODE_MASTER_2);
                    })
                    // 生徒情報取得
                    ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                        $join->on('course_apply.sid', '=', 'ext_student_kihon.sid');
                    })
                    // コース変更種別(二回目のコードマスタJOINなので別名を指定)
                    ->sdleftJoin(CodeMaster::class, function ($join) {
                        $join->on('course_apply.change_type', '=', 'course.code')
                            ->where('course.data_type', AppConst::CODE_MASTER_13);
                    }, 'course')
                    ->where('course_apply.change_id', $changeId)
                    ->firstOrFail();

                return $courseApply;

            case "#modal-dtl-acceptance":
                //---------
                // 受付
                //---------

                // IDを取得
                $changeId = $request->input('change_id');

                // クエリを作成
                $query = CourseApply::query();

                // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                $query->where($this->guardRoomAdminTableWithSid());

                // データ取得
                $courseApply = $query
                    ->select(
                        'apply_time',
                        'ext_student_kihon.name',
                        'code_master.name as course_name',
                    )
                    // 生徒情報取得
                    ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                        $join->on('course_apply.sid', '=', 'ext_student_kihon.sid');
                    })
                    // コース変更種別を取得
                    ->sdLeftJoin(CodeMaster::class, function ($join) {
                        $join->on('course_apply.change_type', '=', 'code_master.code')
                            ->where('data_type', AppConst::CODE_MASTER_13);
                    })
                    ->where('course_apply.change_id', $changeId)
                    ->firstOrFail();

                return $courseApply;

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
        $this->validateIdsFromRequest($request, 'change_id');

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl-acceptance":
                //--------
                // 受付
                //--------

                // トランザクション(例外時は自動的にロールバック)
                DB::transaction(function () use ($request) {
                    // コース変更・授業追加申請ID取得
                    $changeId = $request->input('change_id');

                    // 1件取得
                    $courseApply = CourseApply::where('change_id', $changeId)
                        // 未対応の場合のみ
                        ->where('changes_state', AppConst::CODE_MASTER_2_0)
                        // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                        ->where($this->guardRoomAdminTableWithSid())
                        // 該当データがない場合はエラーを返す
                        ->firstOrFail();

                    // 受付
                    $courseApply->changes_state = AppConst::CODE_MASTER_2_1;

                    // 保存
                    $courseApply->save();

                    //-------------------------
                    // お知らせメッセージの登録
                    //-------------------------

                    // コース種別と希望内容を取得
                    $courseMng = CourseApply::select('code_master.name as type_name', 'changes_text')
                        ->sdLeftJoin(CodeMaster::class, function ($join) {
                            $join->on('course_apply.change_type', '=', 'code_master.code')
                                ->where('code_master.data_type', AppConst::CODE_MASTER_13);
                        })
                        ->where('change_id', $changeId)
                        ->firstOrFail();

                    $notice = new Notice;

                    // タイトルと本文(Langから取得する)
                    $notice->title = Lang::get('message.notice.course_accept.title');
                    $notice->text = Lang::get(
                        'message.notice.course_accept.text',
                        [
                            'courseType' => $courseMng->type_name,
                            'courseContents' => $courseMng->changes_text
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
                    $noticeDestination->sid = $courseApply->sid;

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

        $query = CourseApply::query();

        // ステータスの検索
        $query->SearchChangesState($form);

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

        // データ取得
        $courseApply = $query
            ->select(
                'change_id',
                'course_apply.sid',
                'apply_time',
                'changes_state',
                'code_master.name as status',
                'ext_student_kihon.name',
                'course.name as course_name',
                'course_apply.created_at'
            )
            // 変更状態
            ->sdleftJoin(CodeMaster::class, function ($join) {
                $join->on('course_apply.changes_state', '=', 'code_master.code')
                    ->where('data_type', AppConst::CODE_MASTER_2);
            })
            // 生徒基本情報とJOIN
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('course_apply.sid', '=', 'ext_student_kihon.sid');
            })
            // コース変更種別(二回目のコードマスタJOINなので別名を指定)
            ->sdleftJoin(CodeMaster::class, function ($join) {
                $join->on('course_apply.change_type', '=', 'course.code')
                    ->where('course.data_type', AppConst::CODE_MASTER_13);
            }, 'course')
            ->orderby('apply_time', 'desc')
            ->orderby('course_apply.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $courseApply);
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

            // ステータスのプルダウン取得
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);

            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += CourseApply::fieldRules('changes_state', [$validationStateList]);
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
     * @param int $changeId コース変更・授業追加申請ID
     * @return view
     */
    public function edit($changeId)
    {
        // IDのバリデーション
        $this->validateIds($changeId);

        // 1件取得(beforeのキー取得)
        $courseApply = CourseApply::select(
            '*',
            // 生徒名の取得
            'ext_student_kihon.name'
        )
            // 生徒基本情報とJOIN
            ->sdLeftJoin(ExtStudentKihon::class, function ($join) {
                $join->on('course_apply.sid', '=', 'ext_student_kihon.sid');
            })
            ->where('change_id', $changeId)
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 追加・変更種別プルダウンを取得
        $changeType = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_13);

        // ステータスのプルダウン取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);

        return view('pages.admin.course_mng-edit', [
            'editData' => $courseApply,
            'changeType' => $changeType,
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
            'change_type',
            'changes_text',
            'changes_state',
            'comment',
        );

        // 更新対象データの取得
        $courseApply = CourseApply::where('change_id', $request['change_id'])
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            ->firstOrFail();

        // 保存
        $courseApply->fill($form)->save();

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
        $this->validateIdsFromRequest($request, 'change_id');

        // Formを取得
        $form = $request->only(
            'change_id'
        );

        // 1件取得
        $courseApply = CourseApply::where('change_id', $form['change_id'])
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 削除
        $courseApply->delete();

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

        // 独自バリデーション: リストのチェック 追加・変更種別
        $validationChangeTypeList =  function ($attribute, $value, $fail) {

            // 追加・変更種別プルダウンを取得
            $changeType = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_13);

            if (!isset($changeType[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {

            // ステータスのプルダウン取得
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);

            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += CourseApply::fieldRules('change_id', ['required']);
        $rules += CourseApply::fieldRules('apply_time', ['required']);
        $rules += CourseApply::fieldRules('change_type', ['required', $validationChangeTypeList]);
        $rules += CourseApply::fieldRules('changes_text', ['required']);
        $rules += CourseApply::fieldRules('changes_state', ['required', $validationStateList]);
        $rules += CourseApply::fieldRules('comment');

        return $rules;
    }
}
