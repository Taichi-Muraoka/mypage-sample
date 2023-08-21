<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\ExtStudentKihon;
use App\Models\ExtSchedule;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;
//use App\Http\Controllers\Traits\FuncReportTrait;
use Carbon\Carbon;


/**
 * 成績事例検索 - コントローラ
 */
class GradeExampleController extends Controller
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
        // 定期考査コードチェックボックス
        $regular_exam = array("1学期（前期）中間考査","1学期（前期）末考査","2学期（後期）中間考査","2学期（後期）末考査","3学期中間考査","3学期末考査","学年末考査");
        // 学期コードチェックボックス
        $term = array("1学期（前期）","2学期（後期）","3学期","学年");
        // 小学校グループチェックボックス
        $noticeGroup_p = array("小1","小2","小3","小4","小5","小6");
        // 中学校グループチェックボックス
        $noticeGroup_j = array("中1","中2","中3");
        // 高校グループチェックボックス
        $noticeGroup_h = array("高1","高2","高3");

        // 教科グループチェックボックス
        $subjectGroup = array("国語","数学","理科","社会","英語");

        return view('pages.admin.grade_example', [
            'rules' => $this->rulesForSearch(),
            'editData' => null,
            'regular_exam' => $regular_exam,
            'term' => $term,
            'noticeGroup_p' => $noticeGroup_p,
            'noticeGroup_j' => $noticeGroup_j,
            'noticeGroup_h' => $noticeGroup_h,
            'subjectGroup' => $subjectGroup,
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

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getData(Request $request)
    {
        return [
        ];
    }

    //==========================
    // クラス内共通処理
    //==========================

    /**
     * 生徒名の取得
     *
     * @param int $sid 生徒Id
     * @return object
     */
    private function getStudentName($sid)
    {
        // 生徒名を取得する
        $query = ExtStudentKihon::query();
        $student = $query
            ->select(
                'name'
            )
            ->where('ext_student_kihon.sid', '=', $sid)
            ->firstOrFail();

        return $student;
    }

}
