<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncGradesTrait;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;

/**
 * 生徒成績 - コントローラ
 */
class GradesCheckController extends Controller
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
        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        return view('pages.tutor.grades_check', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'editData' => null
        ]);
    }

    /**
     * 生徒情報取得（校舎リスト選択時）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 生徒情報
     */
    public function getDataSelect(Request $request)
    {
        // campus_cdを取得
        $campus_cd = $request->input('id');

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // $requestのcampus_cdから、生徒IDリストを取得し、検索結果を返却する。
        // 生徒リスト取得
        if ($campus_cd == -1 || !filled($campus_cd)) {
            // -1 または 空白の場合、自分の受け持ちの生徒だけに絞り込み
            $students = $this->mdlGetStudentListForT(null, $account_id);
        } else {
            $students = $this->mdlGetStudentListForT($campus_cd, $account_id);
        }

        return [
            'selectItems' => $this->objToArray($students)
        ];
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

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = Score::query();

        // 校舎コード選択による絞り込み条件
        // -1 は未選択状態のため、-1以外の場合に校舎コードの絞り込みを行う
        if (isset($form['campus_cd']) && filled($form['campus_cd']) && $form['campus_cd'] != -1) {
            // 検索フォームから取得（スコープ）
            $query->SearchRoom($form);
        }

        // 生徒IDの検索（スコープで指定する）
        $query->SearchSid($form);

        // 受け持ち生徒に限定するガードを掛ける
        $query->where($this->guardTutorTableWithSid());

        // 成績データを取得
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
            'student_name' => $score->student_name,
            'exam_type_name' => $score->exam_type_name,
            'practice_exam_name' => $score->practice_exam_name,
            'regular_exam_name' => $score->regular_exam_name,
            'term_name' => $score->term_name,
            'exam_date' => $score->exam_date,
            'student_comment' => $score->student_comment,
            'scoreDetails' => $scoreDetails
        ];
    }

    /**
     * バリデーション(検索用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 検索結果
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
        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎未選択時(-1)はチェックしない
            if ($value == -1) return;

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒名
        $validationStudentList =  function ($attribute, $value, $fail) {

            // ログイン者の情報を取得する
            $account = Auth::user();
            $account_id = $account->account_id;

            // 生徒リスト取得
            $students = $this->mdlGetStudentListForT(null, $account_id);
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        $rules = array();

        $rules += ['campus_cd' => [$validationRoomList]];
        $rules += Score::fieldRules('student_id', [$validationStudentList]);

        return $rules;
    }
}
