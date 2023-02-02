<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Models\ExtStudentKihon;
use App\Models\ExtTrialMaster;
use App\Models\TrialApply;
use App\Models\Event;
use App\Models\EventApply;
use App\Consts\AppConst;
use App\Http\Controllers\Traits\FuncEventTrialTrait;

/**
 * 模試・イベント - コントローラ
 */
class EventController extends Controller
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
    // 申込
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        // 模試・イベント種別プルダウン
        $typeMastrData = $this->getTypeMastrData();

        // 参加人数 コードマスタにないのでappconfに定義した。
        $eventMembers = config('appconf.event_members');

        // 現在日を取得
        $today = date("Y/m/d");

        // 学年を取得
        $cls = $this->getCls();

        // 模試のプルダウンを取得
        $trialMastrData = $this->getMenuOfTrial($cls, $today);

        // イベントのプルダウンを取得
        $eventMastrData = $this->getMenuOfEvent($cls, $today);

        return view('pages.student.event', [
            'rules' => $this->rulesForInput(null),
            'trialMastrData' => $trialMastrData,
            'eventMastrData' => $eventMastrData,
            'trialEditData' => null,
            'eventEditData' => null,
            'typeMastrData' => $typeMastrData,
            'typeEditData' => null,
            'eventMembers' => $eventMembers
        ]);
    }

    /**
     * 初期画面(IDを指定して直接遷移)
     * お知らせのモーダルから遷移する
     *
     * @param int $type お知らせ種別
     * @param int $tmidEventId 模試ID又はイベントID
     * @return view
     */
    public function direct($type, $tmidEventId)
    {
        // MEMO: notice_typeの不正チェックは、以下のif文で判断
        // tmid_event_idはプルダウンを選択しているだけなので特にチェックしない
        // →存在しない場合はプルダウンを選択しないだけなので

        // IDのバリデーション
        $this->validateIds($type, $tmidEventId);

        $direct_keys = [
            'notice_type' => $type,
            'tmid_event_id' => $tmidEventId
        ];

        // 模試・イベント種別プルダウン
        $typeMastrData = $this->getTypeMastrData();

        // 参加人数 コードマスタにないのでappconfに定義した。
        $eventMembers = config('appconf.event_members');

        $trialEditData = null;
        $eventEditData = null;
        if ($direct_keys['notice_type'] == AppConst::CODE_MASTER_14_1) {
            //----------
            // 模試
            //----------
            $typeEditData = ['tm_event_type' => AppConst::CODE_MASTER_14_1];
            $trialEditData = ['tmid' => $direct_keys['tmid_event_id']];
        } elseif ($direct_keys['notice_type'] == AppConst::CODE_MASTER_14_2) {
            //----------
            // イベント
            //----------
            $typeEditData = ['tm_event_type' => AppConst::CODE_MASTER_14_2];
            $eventEditData = ['event_id' => $direct_keys['tmid_event_id']];
        } else {
            // それ以外はエラー
            $this->illegalResponseErr();
        }

        // 学年を取得
        $cls = $this->getCls();

        // 現在日を取得
        $today = date("Y/m/d");

        // 模試のプルダウンを取得
        $trialMastrData = $this->getMenuOfTrial($cls, $today);

        // イベントのプルダウンを取得
        $eventMastrData = $this->getMenuOfEvent($cls, $today);

        return view('pages.student.event', [
            'rules' => $this->rulesForInput(null),
            'trialMastrData' => $trialMastrData,
            'eventMastrData' => $eventMastrData,
            'trialEditData' => $trialEditData,
            'eventEditData' => $eventEditData,
            'typeMastrData' => $typeMastrData,
            'typeEditData' => $typeEditData,
            'eventMembers' => $eventMembers
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
        // MEMO: ログインアカウントのIDでデータを登録するのでガードは不要

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        $tm_event_type = $request->input('tm_event_type');

        $apply_time = date("Y-m-d");

        // ログイン者の生徒No.を取得する。
        $account = Auth::user();
        $sid = $account->account_id;

        if ($tm_event_type == AppConst::CODE_MASTER_14_1) {
            //---------
            // 模試
            //---------

            // フォームから受け取った値を格納
            $form = $request->only(
                'tmid',
            );

            // 保存
            $trialApply = new TrialApply;
            $trialApply->sid = $sid;
            $trialApply->apply_time = $apply_time;
            $trialApply->fill($form)->save();
        } elseif ($tm_event_type == AppConst::CODE_MASTER_14_2) {
            //---------
            // イベント
            //---------

            // フォームから受け取った値を格納
            $form = $request->only(
                'event_id',
                'members'
            );

            // 保存
            $eventApply = new EventApply;
            $eventApply->sid = $sid;
            $eventApply->apply_time = $apply_time;
            $eventApply->fill($form)->save();
        } else {
            // それ以外はエラー
            $this->illegalResponseErr();
        }

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
     * バリデーションルールを取得(ベース)
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {

        // 独自バリデーション: リストのチェック 模試・イベント種別
        $validationTypeList =  function ($attribute, $value, $fail) {

            // 模試・イベント種別プルダウン
            $typeMastrData = $this->getTypeMastrData();
            if (!isset($typeMastrData[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 参加人数
        $validationEventMembersList =  function ($attribute, $value, $fail) {

            // 参加人数
            $eventMembers = config('appconf.event_members');
            if (!isset($eventMembers[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 模試名
        $validationTrialList =  function ($attribute, $value, $fail) {

            // 現在日を取得
            $today = date("Y/m/d");

            // 学年を取得
            $cls = $this->getCls();

            // 模試のプルダウンを取得
            $trialMastrData = $this->getMenuOfTrial($cls, $today);
            if (!isset($trialMastrData[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック イベント名
        $validationEventList =  function ($attribute, $value, $fail) {

            // 現在日を取得
            $today = date("Y/m/d");

            // 学年を取得
            $cls = $this->getCls();

            // イベントのプルダウンを取得
            $eventMastrData = $this->getMenuOfEvent($cls, $today);

            if (!isset($eventMastrData[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 模試・イベント種別
        $rules = array(
            'tm_event_type' => ['integer', 'required', $validationTypeList]
        );

        // ログイン者の生徒No.を取得する。
        $account = Auth::user();
        $sid = $account->account_id;

        if ($request) {
            if (isset($request['tm_event_type'])) {
                if ($request->input('tm_event_type') == AppConst::CODE_MASTER_14_1) {
                    //---------
                    // 模試
                    //---------

                    // 独自バリデーション: 重複チェック(模試)
                    $validationKey = function ($attribute, $value, $fail) use ($request, $sid) {

                        if (!isset($request['tmid'])) {
                            // requiredでチェックするのでreturn
                            return;
                        }

                        // 対象データを取得(PKでユニークに取る)
                        $exists = TrialApply::where('tmid', $request['tmid'])
                            ->where('sid', $sid)
                            ->exists();

                        if ($exists) {
                            // 登録済みエラー
                            return $fail(Lang::get('validation.duplicate_data'));
                        }
                    };

                    $rules += TrialApply::fieldRules('tmid', ['required', $validationKey, $validationTrialList]);
                    $rules += EventApply::fieldRules('event_id');
                    $rules += EventApply::fieldRules('members');
                } elseif ($request->input('tm_event_type') == AppConst::CODE_MASTER_14_2) {
                    //---------
                    // イベント
                    //---------

                    // 独自バリデーション: 重複チェック(イベント)
                    $validationKey = function ($attribute, $value, $fail) use ($request, $sid) {

                        if (!isset($request['event_id'])) {
                            // requiredでチェックするのでreturn
                            return;
                        }

                        // 対象データを取得(PKでユニークに取る)
                        $exists = EventApply::where('event_id', $request['event_id'])
                            ->where('sid', $sid)
                            ->exists();

                        if ($exists) {
                            // 登録済みエラー
                            return $fail(Lang::get('validation.duplicate_data'));
                        }
                    };

                    $rules += TrialApply::fieldRules('tmid');
                    $rules += EventApply::fieldRules('event_id', ['required', $validationKey, $validationEventList]);
                    $rules += EventApply::fieldRules('members', ['required', $validationEventMembersList]);
                }
            } else {
                // その他はエラー
                $this->illegalResponseErr();
            }
        } else {
            // 初期画面表示時
            $rules += TrialApply::fieldRules('tmid');
            $rules += EventApply::fieldRules('event_id');
            $rules += EventApply::fieldRules('members');
        }

        return $rules;
    }

    /**
     * ログインアカウントの学年を取得
     *
     * @return string 学年
     */
    private function getCls()
    {
        // 生徒の学年を取得する。
        $query = ExtStudentKihon::query();
        $student = $query
            ->select(
                'cls_cd'
            )
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            ->firstOrFail();

        // 学年の取得
        return $student->cls_cd;
    }

    /**
     * 種別のプルダウン
     *
     * @return array 種別情報
     */
    private function getTypeMastrData()
    {
        $res = [
            AppConst::CODE_MASTER_14_1 => ["value" => '模試'],
            AppConst::CODE_MASTER_14_2 => ["value" => 'イベント']
        ];

        return $res;
    }

    /**
     * 模試名リスト取得
     *
     * @param string $cls 学年
     * @param date $today 日付
     * @return array 模試名情報
     */
    private function getMenuOfTrial($cls, $today)
    {
        // 模試のプルダウンを取得
        return ExtTrialMaster::select(
            'tmid',
            'trial_date',
            'name AS value'
        )
            ->where('ext_trial_master.cls_cd', '=', $cls)
            ->where('ext_trial_master.trial_date', '>', $today)
            ->orderBy('trial_date', 'desc')
            ->get()
            ->keyBy('tmid');
    }

    /**
     * イベントリスト取得
     *
     * @param string $cls 学年
     * @param date $today 日付
     * @return array イベントリスト
     */
    private function getMenuOfEvent($cls, $today)
    {
        // イベントのプルダウンを取得
        return Event::select(
            'event_id',
            'event_date',
            'name AS value'
        )
            ->where('event.cls_cd', '=', $cls)
            ->where('event.event_date', '>', $today)
            ->orderBy('event_date', 'desc')
            ->get()
            ->keyBy('event_id');
    }
}
