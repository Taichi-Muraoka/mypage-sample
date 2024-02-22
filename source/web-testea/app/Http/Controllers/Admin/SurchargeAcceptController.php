<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncSurchargeTrait;
use App\Consts\AppConst;
use App\Models\Surcharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;

/**
 * 追加請求申請受付 - コントローラ
 */
class SurchargeAcceptController extends Controller
{
    // 機能共通処理：追加請求
    use FuncSurchargeTrait;

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
        // 校舎リストを取得 本部あり
        $rooms = $this->mdlGetRoomList(true);
        // 講師リストを取得
        $tutorList = $this->mdlGetTutorList();
        // 請求種別リストを取得
        $surchargeKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_26);
        // 承認ステータスリストを取得
        $approvalStatusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);
        // 支払状況リストを取得
        $paymentStatusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_27);

        return view('pages.admin.surcharge_accept', [
            'rules' => $this->rulesForSearch(null),
            'editData' => null,
            'rooms' => $rooms,
            'tutorList' => $tutorList,
            'surchargeKindList' => $surchargeKindList,
            'approvalStatusList' => $approvalStatusList,
            'paymentStatusList' => $paymentStatusList,
        ]);
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array  検索結果
     */
    public function search(Request $request)
    {
        // データを取得
        $surcharges = $this->getSurchargeList($request);

        // ページネータで返却
        return $this->getListAndPaginator($request, $surcharges);
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
     * バリデーションルールを取得(検索用)
     *
     * @return mixed ルール
     */
    private function rulesForSearch(?Request $request)
    {
        // 独自バリデーション: リストのチェック 校舎
        $validationCampusList =  function ($attribute, $value, $fail) {
            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(true);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 講師ID
        $validationTutorList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlGetTutorList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 請求種別
        $validationSurchargeKindList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_26);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ステータス
        $validationApprovalStatusListist =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 支払状況
        $validationPaymentStatusList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_27);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        $rules += Surcharge::fieldRules('campus_cd', [$validationCampusList]);
        $rules += Surcharge::fieldRules('tutor_id', [$validationTutorList]);
        $rules += Surcharge::fieldRules('surcharge_kind', [$validationSurchargeKindList]);
        $rules += Surcharge::fieldRules('approval_status', [$validationApprovalStatusListist]);
        $rules += Surcharge::fieldRules('payment_status', [$validationPaymentStatusList]);

        // 認定日 項目のバリデーションルールをベースにする
        $ruleApplyDate = Surcharge::getFieldRule('apply_date');

        // FromとToの大小チェックバリデーションを追加(Fromが存在する場合のみ)
        $validateFromTo = [];
        $keyFrom = 'apply_date_from';
        if (isset($request[$keyFrom]) && filled($request[$keyFrom])) {
            $validateFromTo = ['after_or_equal:' . $keyFrom];
        }

        // 日付From・Toのバリデーションの設定
        $rules += ['apply_date_from' => $ruleApplyDate];
        $rules += ['apply_date_to' => array_merge($validateFromTo, $ruleApplyDate)];

        return $rules;
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        // MEMO:データ取得は詳細モーダル・受付モーダル共通

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // 詳細データを作成
        $surcharge = $this->getSurchargeDetail($request['id']);

        return $surcharge;
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
        $this->validateIdsFromRequest($request, 'id');

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl-acceptance":
                //--------
                // 承認
                //--------
                // 対象データ取得
                $surcharge = $this->getTargetSurchargeAdmin($request['id']);

                // ステータスを「承認」に変更する
                $surcharge->approval_status = AppConst::CODE_MASTER_2_1;

                // 支払年月を実施日の翌月に設定する
                // 翌月1日にフォーマット(実施日:2023/12/31 → 2024/01/01)
                $nextMonth = date('Y-m-d', strtotime('first day of next month ' . $surcharge->working_date));
                $surcharge->payment_date = $nextMonth;

                // 保存
                $surcharge->save();

                return;

            default:
                // モーダルIDが該当しない場合
                $this->illegalResponseErr();
        }
    }

    //==========================
    // 編集
    //==========================

    /**
     * 編集画面
     *
     * @param int surchargeId 追加請求ID
     * @return view
     */
    public function edit($surchargeId)
    {
        // 対象データ取得
        $surcharge = $this->getTargetSurchargeAdmin($surchargeId);

        // 承認ステータスリストを取得
        $approvalStatusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);

        // 支払年月リストを取得 引数:実施日
        $paymentDateList = $this->getPaymentDateList($surcharge->working_date);

        return view('pages.admin.surcharge_accept-edit', [
            'editData' => $surcharge,
            'rules' => $this->rulesForInput(null),
            'approvalStatusList' => $approvalStatusList,
            'paymentDateList' => $paymentDateList,
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
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // 更新項目を取得
        $form = $request->only(
            'approval_status',
            'admin_comment',
        );

        // ステータス「承認」で更新する場合は「支払年月」をセット
        if ($request['approval_status'] == AppConst::CODE_MASTER_2_1) {
            $form += [
                'payment_date' => $request['payment_date'] . '-01'
            ];
        }

        // 対象データを取得
        $surcharge = $this->getTargetSurchargeAdmin($request['surcharge_id']);

        // データ更新
        $surcharge->fill($form)->save();

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
        // 独自バリデーション: リストのチェック ステータス
        $validationApprovalStatusList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 支払年月
        $validationPaymentStatusList =  function ($attribute, $value, $fail) use ($request) {
            // リストを取得し存在チェック
            $list = $this->getPaymentDateList($request['working_date']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        $rules += Surcharge::fieldRules('approval_status', ['required', $validationApprovalStatusList]);
        // ステータス「差戻し」選択時は管理者コメント必須
        $rules += Surcharge::fieldRules('admin_comment', ['required_if:approval_status,' . AppConst::CODE_MASTER_2_2]);
        // ステータス「承認」選択時は支払年月必須
        $rules += Surcharge::fieldRules('payment_date', ['required_if:approval_status,' . AppConst::CODE_MASTER_2_1, $validationPaymentStatusList]);

        return $rules;
    }
}
