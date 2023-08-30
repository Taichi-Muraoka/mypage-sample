<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ExtStudentKihon;
use App\Models\ExtSchedule;
use App\Models\TransferApply;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\ExtRirekisho;
use App\Models\Notice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Models\NoticeDestination;
use Carbon\Carbon;
use App\Libs\AuthEx;
use App\Http\Controllers\Traits\FuncTransferTrait;

/**
 * コースマスタ管理 - コントローラ
 */
class MasterMngCourseController extends Controller
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
    // 一覧
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        return view('pages.admin.master_mng_course');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // ページネータで返却（モック用）
        return $this->getListAndPaginatorMock();
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    //public function getData(Request $request)
    //{
    //    return [
    //    ];
    //}

    //==========================
    // 登録・編集・削除
    //==========================

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        return view('pages.admin.master_mng_course-input', [
            'rules' => null,
            'editData' => null,
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
     * 編集画面
     *
     * @param int
     * @return view
     */
    public function edit($courseId)
    {
        return view('pages.admin.master_mng_course-input', [
            'editData' => ['course_cd'=>'10100',
                           'name'=>'個別指導コース',
                           'course_kind'=>'1',],
            'rules' => $this->rulesForInput(null),
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
        $rules = array();

        return $rules;
    }
}
