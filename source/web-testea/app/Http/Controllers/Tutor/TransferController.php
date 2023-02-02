<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Models\ExtSchedule;
use App\Models\TransferApply;
use App\Models\ExtStudentKihon;
use App\Http\Controllers\Traits\FuncTransferTrait;
use App\Consts\AppConst;
use Carbon\Carbon;

/**
 * 振替申請 - コントローラ
 */
class TransferController extends Controller
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
    // 申請
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        // ログイン者の生徒No.を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        // 教師の担当している生徒の一覧を取得(家庭教師は除く)
        $students = $this->mdlGetStudentListForT(null, $account_id, AppConst::EXT_GENERIC_MASTER_101_900);

        return view('pages.tutor.transfer', [
            'rules' => $this->rulesForInput(null),
            'editData' => null,
            'students' => $students
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

        // 現在日時を取得
        $now = Carbon::now();

        // フォームから受け取った値を格納
        $form = $request->only(
            'id',
            'transfer_date',
            'transfer_time',
            'transfer_reason'
        );

        // 受け持ち生徒のスケジュールのみに限定するガードを掛ける
        ExtSchedule::where('id', $request['id'])
            ->where($this->guardTutorTableWithSid())
            ->firstOrFail();

        $transferApply = new TransferApply;
        $transferApply->apply_time = $now;

        // 登録
        $transferApply->fill($form)->save();

        return;
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
        $this->validateIdsFromRequest($request, 'sid');

        // IDを取得
        $schedule_id = $request->input('id');
        $sid = $request->input('sid');

        // ログイン者の生徒No.を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------

        // 教師の担当している生徒の一覧を取得(家庭教師は除く)
        $students = $this->mdlGetStudentListForT(null, $account_id, AppConst::EXT_GENERIC_MASTER_101_900);

        // 生徒一覧にsidがあるかチェック
        $this->guardListValue($students, $sid);

        //---------------------------
        // スケジュールプルダウンの作成
        //---------------------------

        // 教師のスケジュールを取得(指定された生徒IDで絞る)
        $lessons = $this->getTeacherScheduleList($account_id, null, $sid, true);

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

            // 教室名取得のサブクエリ
            $room_names = $this->mdlGetRoomQuery();

            // $requestからidを取得し、検索結果を返却する。idはスケジュールID
            $query = ExtSchedule::query();

            // 詳細の取得
            $lesson = $query
                ->select(
                    'room_name_full'
                )
                // 教室名の取得
                ->leftJoinSub($room_names, 'room_names', function ($join) {
                    $join->on('ext_schedule.roomcd', '=', 'room_names.code');
                })
                ->where('ext_schedule.id', '=', $schedule_id)
                ->firstOrFail();

            // 変数にセット
            $room_name_full = $lesson->room_name_full;
        }

        return [
            'selectItems' => $this->objToArray($scheduleMaster),
            'class_name' => $room_name_full
        ];
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

        // 独自バリデーション: リストのチェック 生徒ID
        $validationSidList =  function ($attribute, $value, $fail) {

            // ログイン者の生徒No.を取得する。
            $account = Auth::user();
            $account_id = $account->account_id;

            // 教師の担当している生徒の一覧を取得(家庭教師は除く)
            $students = $this->mdlGetStudentListForT(null, $account_id, AppConst::EXT_GENERIC_MASTER_101_900);

            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック スケジュール
        $validationscheduleList =  function ($attribute, $value, $fail) use ($request) {

            // sidの取得(チェックはvalidationSidListで行う)
            $sid = $request['sid'];

            // ログイン者の生徒No.を取得する。
            $account = Auth::user();
            $account_id = $account->account_id;

            // 教師のスケジュールを取得(sidを指定する)
            $lessons = $this->getTeacherScheduleList($account_id, null, $sid, true);

            // スケジュールのプルダウンメニューを作成
            $scheduleMaster = $this->mdlGetScheduleMasterList($lessons);

            if (!isset($scheduleMaster[$value])) {
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
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        $rules = array();

        // 生徒ID。TransferApplyには格納されないが、スケジュールIDのチェックのため
        $rule = [];
        $rule = ['required', $validationSidList];
        $rules += ExtSchedule::fieldRules('sid', $rule);

        // スケジュールID
        $rule = [];
        $rule = ['required', $validationKeySchedule, $validationscheduleList];
        $rules += ExtSchedule::fieldRules('id', $rule);

        $rules += TransferApply::fieldRules('transfer_date', ['required']);
        $rules += TransferApply::fieldRules('transfer_time', ['required']);
        $rules += TransferApply::fieldRules('transfer_reason', ['required']);

        return $rules;
    }
}
