<?php

namespace App\Http\Controllers\MypageCommon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\FuncCalendarTrait;

/**
 * カレンダー - コントローラ
 */
class CalendarController extends Controller
{

    // 機能共通処理：カレンダー
    use FuncCalendarTrait;

    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {

        return view('pages.mypage-common.calendar');
    }

    /**
     * カレンダー取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array アカウントID
     */
    public function getCalendar(Request $request)
    {

        // MEMO: ログインアカウントのIDでデータを取得するのでガードは不要

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForCalendar())->validate();

        // ログイン者のNo.を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        // ダミーデータを返却
        if (AuthEx::isStudent()) {
            // 生徒
            return $this->getStudentCalendar($request, $account_id);
        } else {
            // 教師
            return $this->getTutorCalendar($request, $account_id);
        }
    }
}
