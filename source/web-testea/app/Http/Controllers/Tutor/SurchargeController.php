<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncSurchargeTrait;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\Surcharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

/**
 * 追加請求申請 - コントローラ
 */
class SurchargeController extends Controller
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
        return view('pages.tutor.surcharge');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // データを取得
        $surcharges = $this->getSurchargeList();

        // ページネータで返却
        return $this->getListAndPaginator($request, $surcharges);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getData(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // データを取得
        $surcharge = $this->getSurchargeDetail($request['id']);

        return $surcharge;
    }

    //==========================
    // 登録・編集
    //==========================

    /**
     * 請求種別サブコードの取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed テンプレート
     */
    public function getDataSelect(Request $request)
    {
        // バリデーション id:surcharge_kind
        $this->validateIdsFromRequest($request, 'id');

        // 請求種別のサブコードを取得する
        $query = CodeMaster::query();
        $subCode = $query->where('code', $request['id'])
            ->where('data_type', AppConst::CODE_MASTER_26)
            ->first();

        return [
            'subCode' => $subCode->sub_code,
        ];
    }

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        // 請求種別リストを取得
        $kindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_26);
        // 校舎リストを取得 本部あり
        $rooms = $this->mdlGetRoomList(true);

        return view('pages.tutor.surcharge-input', [
            'rules' => $this->rulesForInput(null),
            'editData' => null,
            'kindList' => $kindList,
            'rooms' => $rooms,
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

        // 講師IDを取得する
        $account = Auth::user();
        $tid = $account->account_id;

        // 先行して保存するデータをセット
        $surcharge = new Surcharge;
        $surcharge->tutor_id = $tid;

        // Surchargesテーブルへ保存
        $this->saveToSurchargeTutor($request, $surcharge);

        return;
    }

    /**
     * 編集画面
     *
     * @param int $surchargeId 追加請求ID
     * @return view
     */
    public function edit($surchargeId)
    {
        // IDのバリデーション
        $this->validateIds($surchargeId);

        // 対象データを取得
        $surcharge = $this->getTargetSurchargeTutor($surchargeId);

        // 請求種別リストを取得
        $kindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_26);
        // 校舎リストを取得 本部あり
        $rooms = $this->mdlGetRoomList(true);

        return view('pages.tutor.surcharge-input', [
            'rules' => $this->rulesForInput(null),
            'editData' => $surcharge,
            'kindList' => $kindList,
            'rooms' => $rooms,
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

        // 対象データを取得
        $surcharge = $this->getTargetSurchargeTutor($request['surcharge_id']);

        // データ更新
        $this->saveToSurchargeTutor($request, $surcharge);

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
        $this->validateIdsFromRequest($request, 'surcharge_id');

        // 対象データを取得
        $surcharge = $this->getTargetSurchargeTutor($request['surcharge_id']);

        // 削除
        $surcharge->delete();

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
        // 独自バリデーション: リストのチェック 請求種別
        $validationKindList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_26);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {
            // 校舎リストを取得 本部あり
            $rooms = $this->mdlGetRoomList(true);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        // 共通バリデーション
        $rules += Surcharge::fieldRules('surcharge_kind', ['required', $validationKindList]);
        $rules += Surcharge::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += Surcharge::fieldRules('working_date', ['required']);
        $rules += Surcharge::fieldRules('comment', ['required']);

        // 請求種別=サブコード8 時給 のバリデーション
        if ($request && $request['sub_code'] == AppConst::CODE_MASTER_26_SUB_8) {
            $rules += Surcharge::fieldRules('start_time', ['required']);
            $rules += Surcharge::fieldRules('minutes', ['required']);
        }

        // 請求種別=サブコード9,10 固定金額 のバリデーション
        if ($request && $request['sub_code'] != AppConst::CODE_MASTER_26_SUB_8) {
            $rules += Surcharge::fieldRules('tuition', ['required']);
        }

        return $rules;
    }
}
