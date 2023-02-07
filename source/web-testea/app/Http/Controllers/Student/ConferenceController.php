<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Lang;
use App\Models\ExtSchedule;
use App\Models\AbsentApply;
use App\Models\ExtRirekisho;
use App\Consts\AppConst;
use App\Mail\AbsentApplyToOffice;
use App\Models\ExtGenericMaster;
use App\Http\Controllers\Traits\FuncAbsentTrait;
use Illuminate\Support\Carbon;

/**
 * 欠席申請 - コントローラ
 */
class ConferenceController extends Controller
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

        return view('pages.student.conference', [
            'rules' => $this->rulesForInput(null),
            'editData' => null,
        ]);
    }

    /**
     * 初期画面(IDを指定して直接遷移)
     * カレンダーのモーダルの～～～から遷移する
     *
     * @return view
     */
    public function direct($scheduleId)
    {
        // IDのバリデーション
        $this->validateIds($scheduleId);

        // ログイン者の生徒No.を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        return view('pages.student.conference', [
            'rules' => $this->rulesForInput(null),
            'editData' => $editData,
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
     * バリデーションルールを取得(事前に渡す用)
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {

        $rules = array();

        return $rules;
    }
}
