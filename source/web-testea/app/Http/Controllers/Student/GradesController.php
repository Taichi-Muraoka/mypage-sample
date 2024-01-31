<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncGradesTrait;
use App\Models\Score;
use App\Models\ScoreDetail;
use App\Consts\AppConst;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
    { }

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

        // hidden用,route用データセット
        $editData = [
            "student_id" => $sid,
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

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            $score = new Score;
            // 共通保存項目
            $score->student_id = $request->student_id;
            $score->exam_type =  $request['exam_type'];
            $score->grade_cd =  $request['grade_cd'];
            $score->student_comment = $request['student_comment'];
            $score->regist_date = Carbon::now();

            // 試験種別によって保存項目分岐
            if ($request['exam_type'] == AppConst::CODE_MASTER_43_0) {
                // 模試
                $score->practice_exam_name = $request['practice_exam_name'];
                $score->exam_date = $request['exam_date'];
            }
            if ($request['exam_type'] == AppConst::CODE_MASTER_43_1) {
                // 定期考査
                $score->regular_exam_cd = $request['regular_exam_cd'];
                $score->exam_date = $request['exam_date'];
            }
            if ($request['exam_type'] == AppConst::CODE_MASTER_43_2) {
                // 評定
                $score->term_cd = $request['term_cd'];
            }

            // Scoreテーブルへのinsert
            $score->save();

            // ScoreDetailテーブルへのinsert
            $this->saveToScoreDetail($request, $score->score_id);
        });

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

        // クエリを作成
        $query = Score::query();

        // 自分の生徒IDのみにガードを掛ける
        $query->where($this->guardStudentTableWithSid());

        $score = $query
            // 対象データを取得(PKでユニークに取る)
            ->where('score_id', $request['score_id'])
            // MEMO: 取得できない場合はエラーとする
            ->firstOrFail();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request, $score) {

            // 共通保存項目
            $score->exam_type =  $request['exam_type'];
            $score->student_comment = $request['student_comment'];

            // 試験種別によって保存項目分岐
            // 編集時に試験種別を変更した場合に対応し、明示的にnullを保存する
            if ($request['exam_type'] == AppConst::CODE_MASTER_43_0) {
                // 模試
                $score->regular_exam_cd = null;
                $score->practice_exam_name = $request['practice_exam_name'];
                $score->term_cd = null;
                $score->exam_date = $request['exam_date'];
            }
            if ($request['exam_type'] == AppConst::CODE_MASTER_43_1) {
                // 定期考査
                $score->regular_exam_cd = $request['regular_exam_cd'];
                $score->practice_exam_name = null;
                $score->term_cd = null;
                $score->exam_date = $request['exam_date'];
            }
            if ($request['exam_type'] == AppConst::CODE_MASTER_43_2) {
                // 評定
                $score->regular_exam_cd = null;
                $score->practice_exam_name = null;
                $score->term_cd = $request['term_cd'];
                $score->exam_date = null;
            }

            $score->save();

            // ScoreDatailテーブルの更新
            // MEMO: updateではなく、forceDelete・insertとする

            // 成績IDに紐づく成績詳細を全て削除（forceDelete）
            ScoreDetail::where('score_id', $score->score_id)
                ->forceDelete();

            // ScoreDetailテーブルへのinsert
            $this->saveToScoreDetail($request, $score->score_id);
        });

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
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'score_id');

        // クエリを作成
        $query = Score::query();

        // 自分の生徒IDのみにガードを掛ける
        $query->where($this->guardStudentTableWithSid());

        $score = $query
            // 対象データを取得(PKでユニークに取る)
            ->where('score_id', $request['score_id'])
            // MEMO: 取得できない場合はエラーとする
            ->firstOrFail();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($score) {
            // ScoreDatailテーブルのdelete
            // 成績IDに紐づく成績詳細を全て削除(論理削除)
            ScoreDetail::where('score_id', $score->score_id)
                ->delete();

            // Scoreテーブルのdelete
            $score->delete();
        });

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
        $rules = array();

        // 独自バリデーション: リストのチェック 試験種別
        $validationExamTypeList =  function ($attribute, $value, $fail) {
            return $this->validationExamTypeList($value, $fail);
        };

        // 独自バリデーション: リストのチェック 定期考査名
        $validationTeikiNameList =  function ($attribute, $value, $fail) use ($request) {
            return $this->validationTeikiNameList($request, $value, $fail);
        };

        // 独自バリデーション: 生徒成績の存在チェック(1件以上)
        $validationScoreDetail = function ($attribute, $value, $parameters) use ($request) {
            return $this->validationScoreDetail($request);
        };

        // 全種別共通
        $rules += Score::fieldRules('exam_type', ['required', $validationExamTypeList]);
        $rules += Score::fieldRules('student_comment', ['required']);
        // Laravelの独自バリデーションは、空白の時は呼んでくれないので、
        // 今回のように存在チェックの場合は、以下のように指定し空の場合も呼んでもらう
        Validator::extendImplicit('array_required', $validationScoreDetail);

        // 模試・定期考査
        $rules += Score::fieldRules('exam_date', ['required_if:exam_type,' . AppConst::CODE_MASTER_43_0 . ',' . AppConst::CODE_MASTER_43_1]);
        // 模試
        $rules += Score::fieldRules('practice_exam_name', ['required_if:exam_type,' . AppConst::CODE_MASTER_43_0]);
        // 定期考査
        $rules += Score::fieldRules('regular_exam_cd', ['required_if:exam_type,' . AppConst::CODE_MASTER_43_1, $validationTeikiNameList]);
        // 評定
        $rules += Score::fieldRules('term_cd', ['required_if:exam_type,' . AppConst::CODE_MASTER_43_2]);

        // 成績欄のルールを取得する
        $rules = $this->setRulesForScoreDetail($rules, $request);

        return $rules;
    }
}
