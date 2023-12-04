<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Schedule;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;
//use App\Http\Controllers\Traits\FuncReportTrait;
use Carbon\Carbon;

/**
 * 要振替授業管理 - コントローラ
 */
class TransferRequiredController extends Controller
{

    // 機能共通処理：

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
        // 校舎プルダウン
        $rooms = $this->mdlGetRoomList(false);

        // 生徒リストを取得
        $studentList = $this->mdlGetStudentList();

        // 講師リストを取得
        $tutorList = $this->mdlGetTutorList();

        return view('pages.admin.transfer_required', [
            'rules' => $this->rulesForSearch(),
            'editData' => null,
            'rooms' => $rooms,
            'studentList' => $studentList,
            'tutorList' => $tutorList
        ]);
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
        // ページネータで返却（モック用）
        return $this->getListAndPaginatorMock();
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
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {

        $rules = array();

        return $rules;
    }
}
