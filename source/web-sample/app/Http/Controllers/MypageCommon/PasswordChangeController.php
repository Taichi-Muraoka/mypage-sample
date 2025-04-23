<?php

namespace App\Http\Controllers\MypageCommon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * パスワード - コントローラ
 */
class PasswordChangeController extends Controller
{
    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
    }

    //==========================
    // 登録
    //==========================

    /**
     * 変更画面
     *
     * @return view
     */
    public function index()
    {

        return view('pages.mypage-common.password_change', [
            'editData' => [],
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

        // MEMO: ログインアカウントのIDでデータを更新するのでガードは不要

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // フォームから値を取得
        $form = $request->only(
            'current_pass',
            'new_pass',
            'new_pass_confirmation'
        );

        // user_idとaccount_typeを取得
        $account = Auth::user();
        $accountId = $account->account_id;
        $accountType = $account->account_type;

        // パスワード変更処理
        $passwordChange = Account::where('account_id', $accountId)->where('account_type', $accountType)->first();
        $passwordChange->password = Hash::make($form['new_pass']);
        $passwordChange->save();

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

        // パスワードをチェックする独自バリデーション
        $validationPass = function ($attribute, $value, $fail) {

            // user_idとaccount_typeを取得
            $account = Auth::user();
            $accountId = $account->account_id;
            $accountType = $account->account_type;

            // パスワードを特別、アカウントテーブルから取得
            $table = (new Account)->getTable();
            $user = DB::table($table)
                ->select('password')
                ->where('account_id', $accountId)
                ->where('account_type', $accountType)
                ->whereNull('deleted_at') //論理削除
                ->first();

            if (!Hash::check($value, $user->password)) {
                return $fail("パスワードが違います");
            }
        };

        // パスワード項目のバリデーションルールをベースにする
        $rulePass = Account::getFieldRule('password');

        $rules += ['current_pass' => array_merge(['required', $validationPass], $rulePass)];
        $rules += ['new_pass' => array_merge(['required', 'confirmed'], $rulePass)];
        $rules += ['new_pass_confirmation' => array_merge(['required'], $rulePass)];

        return $rules;
    }
}
