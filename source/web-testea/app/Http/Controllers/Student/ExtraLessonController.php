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
 *  追加授業依頼- コントローラ
 */
class ExtraLessonController extends Controller
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
    // 申請
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        return view('pages.student.extra_lesson', [
            'rules' => $this->rulesForInput(null),
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
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInput(Request $request)
    {
        return;
    }

    /**
     * バリデーションルールを取得(事前に渡す用)
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        return;
    }
}
