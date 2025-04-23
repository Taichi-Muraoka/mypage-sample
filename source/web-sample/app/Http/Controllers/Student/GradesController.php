<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncGradesTrait;
use App\Models\Score;
use App\Consts\AppConst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

/**
 * 生徒成績 - コントローラ
 */
class GradesController extends Controller
{
    // 機能共通処理：生徒成績
    use FuncGradesTrait;

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
        return view('pages.student.grades');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // クエリを作成
        $query = Score::query();

        // 自分の生徒IDのみにガードを掛ける
        $query->where($this->guardStudentTableWithSid());

        // データを取得
        $scores = $this->getScoreList($query);

        // ページネータで返却
        return $this->getListAndPaginator($request, $scores);
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

        // 成績IDを取得
        $id = $request->input('id');

        // 生徒成績を取得
        $score = $this->getScore($id);

        // 生徒成績詳細を取得
        $scoreDetails = $this->getScoreDetail($id);

        return [
            'exam_type' => $score->exam_type,
            'regist_date' => $score->regist_date,
            'exam_type_name' => $score->exam_type_name,
            'practice_exam_name' => $score->practice_exam_name,
            'regular_exam_name' => $score->regular_exam_name,
            'term_name' => $score->term_name,
            'exam_date' => $score->exam_date,
            'student_comment' => $score->student_comment,
            'scoreDetails' => $scoreDetails
        ];
    }

    //==========================
    // 登録・編集・削除
    //==========================

    /**
     * 成績入力欄数の取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed テンプレート
     */
    public function getDataSelect(Request $request)
    {
        // バリデーション
        $this->validateIdsFromRequest($request, 'exam_type', 'school_kind');

        $count = $this->getDisplayCount($request->exam_type, $request->school_kind);

        return [
            'count' => $count,
        ];
    }

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        // ログイン者の情報を取得する
        $account = Auth::user();
        // アカウント情報より生徒IDを取得
        $sid = $account->account_id;

        // 生徒の学年を取得する
        $grade = $this->getGradeAtRegist($sid);
        // 学年に応じた教科リストを取得する
        $subjectList = $this->mdlGetGradeSubjectList($grade->school_kind);
        // 学年に応じた成績入力欄数を取得する（模試用）
        $display_count = $this->getDisplayCount(AppConst::CODE_MASTER_43_0, $grade->school_kind);

        // 試験種別リストを取得
        // 学校区分によって非表示項目があるためFuncGradesTraitで処理
        $examTypeList = $this->getExamTypeList($grade->school_kind);

        // 定期考査名リストを取得
        $teikiList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_45);
        // 学期リストを取得
        $termList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_44);

        // hidden用データセット
        $editData = [
            "grade_cd" => $grade->grade_cd,
            // 模試を初期表示とする
            "exam_type" => AppConst::CODE_MASTER_43_0,
        ];

        // 成績入力欄数の判定用データセット
        $displayCountData = [
            "school_kind" => $grade->school_kind,
            "display_count" => $display_count,
        ];

        return view('pages.student.grades-input', [
            'rules' => $this->rulesForInput(null),
            'examTypeList' => $examTypeList,
            'teikiList' => $teikiList,
            'termList' => $termList,
            'subjectList' => $subjectList,
            'editData' => $editData,
            'editDataDtls' => [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null],
            'displayCountData' => $displayCountData,
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

        // Traitにて生徒成績データの登録
        $this->saveToScore($request);

        return;
    }

    /**
     * 編集画面
     *
     * @param int $scoreId 生徒成績ID
     * @return void
     */
    public function edit($scoreId)
    {
        // IDのバリデーション
        $this->validateIds($scoreId);

        // クエリを作成(PKでユニークに取る)
        $score = Score::where('score_id', $scoreId)
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // データを取得（生徒成績詳細）
        $scoreDetails = $this->getScoreDetailEdit($scoreId);

        // 成績登録時の学年を取得する
        $grade = $this->getGradeAtEdit($scoreId);
        // 成績登録時の学年に応じた教科リストを取得する
        $subjectList = $this->mdlGetGradeSubjectList($grade->school_kind);
        // 学年に応じた成績入力欄数を取得する
        $display_count = $this->getDisplayCount($score->exam_type, $grade->school_kind);

        // 試験種別リストを取得
        // 学校区分によって非表示項目があるためFuncGradesTraitで処理
        $examTypeList = $this->getExamTypeList($grade->school_kind);

        // 定期考査名リストを取得
        $teikiList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_45);
        // 学期リストを取得
        $termList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_44);

        // 成績入力欄数の判定用データセット
        $displayCountData = [
            "school_kind" => $grade->school_kind,
            "display_count" => $display_count,
        ];

        return view('pages.student.grades-input', [
            'rules' => $this->rulesForInput(null),
            'grade' => $grade,
            'examTypeList' => $examTypeList,
            'teikiList' => $teikiList,
            'termList' => $termList,
            'subjectList' => $subjectList,
            'editData' => $score,
            'editDataDtls' => $scoreDetails,
            'displayCountData' => $displayCountData,
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

        // Traitにて生徒成績データの更新
        $this->updateToScore($request);

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
        // Traitにて削除処理
        $this->deleteScore($request);

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
        // Traitにてルールを取得する
        $rules = $this->setRulesForScore($request);

        return $rules;
    }
}
