<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\CourseApply;
use Illuminate\Support\Facades\Auth;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncCourseTrait;
use Illuminate\Support\Carbon;

/**
 * コース変更・授業追加申請 - コントローラ
 */
class CourseController extends Controller
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
    // 申請
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        // 追加・変更種別プルダウンを作成
        $codeMaster = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_13);

        return view('pages.student.course', [
            'rules' => $this->rulesForInput(),
            'codeMaster' => $codeMaster,
            'editData' => null
        ]);
    }

    /**
     * お知らせからの短期講習申込
     *
     * @return view
     */
    public function direct()
    {
        // 追加・変更種別プルダウンを作成
        $codeMaster = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_13);

        return view('pages.student.course', [
            'rules' => $this->rulesForInput(),
            'codeMaster' => $codeMaster,
            'editData' => [
                'change_type' => AppConst::CODE_MASTER_13_4
            ]
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

        // 生徒IDを取得
        $account = Auth::user();
        $sid = $account->account_id;

        // フォームから受け取った値を格納
        $form = $request->only(
            'change_type',
            'changes_text'
        );

        // 本日の日付をセット
        $now = Carbon::now();

        // 保存
        $courseApply = new CourseApply;
        $courseApply->sid = $sid;
        $courseApply->apply_time = $now;
        $courseApply->changes_state = AppConst::CODE_MASTER_2_0;
        $courseApply->fill($form)->save();

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

        // 独自バリデーション: リストのチェック 追加・変更種別
        $validationCourseList =  function ($attribute, $value, $fail) {

            // 追加・変更種別プルダウンを作成
            $codeMaster = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_13);
            if (!isset($codeMaster[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        $rules += CourseApply::fieldRules('change_type', ['required', $validationCourseList]);
        $rules += CourseApply::fieldRules('changes_text', ['required']);

        return $rules;
    }
}
