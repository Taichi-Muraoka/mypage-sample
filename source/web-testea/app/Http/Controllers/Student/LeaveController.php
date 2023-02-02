<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\LeaveApply;
use Illuminate\Support\Facades\Auth;
use App\Consts\AppConst;
use App\Http\Controllers\Traits\FuncLeaveTrait;
use Illuminate\Support\Carbon;

/**
 * 退会申請 - コントローラ
 */
class LeaveController extends Controller
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
    // 退会
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        // 生徒IDを取得
        $account = Auth::user();
        $sid = $account->account_id;

        // 退会済みか判定 true:退会未申請 false:退会申請済み
        $leave = LeaveApply::where('sid', $sid)->first();
        $isLeave = empty($leave) ? true : false;

        return view('pages.student.leave',  [
            'rules' => $this->rulesForInput(),
            'isLeave' => $isLeave
        ]);
    }

    /**
     * 退会処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function update(Request $request)
    {

        // MEMO: ログインアカウントのIDでデータを更新するのでガードは不要

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // 生徒IDを取得
        $account = Auth::user();
        $sid = $account->account_id;

        // フォームから受け取った値を格納
        $form = $request->only(
            'leave_reason'
        );

        // 本日の日付をセット
        $now = Carbon::now();

        // 保存
        $leave = new LeaveApply;
        $leave->sid = $sid;
        $leave->apply_time = $now;
        // 未対応
        $leave->leave_state = AppConst::CODE_MASTER_5_0;
        $leave->fill($form)->save();

        return;
    }

    /**
     * バリデーション(退会用)
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
     * バリデーションルールを取得(退会用)
     *
     * @return array ルール
     */
    private function rulesForInput()
    {

        $rules = array();

        $rules += LeaveApply::fieldRules('leave_reason', ['required']);

        return $rules;
    }
}
