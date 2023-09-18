<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Score;
use App\Models\Schedule;
use App\Models\CodeMaster;
use App\Models\Student;
//use App\Models\ExtTrialMaster;
use App\Consts\AppConst;
use App\Http\Controllers\Traits\FuncGradesTrait;
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
        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(false);

        return view('pages.tutor.grades_check', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'editData' => null
        ]);
    }

    /**
     * 生徒情報取得（教室リスト選択時）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 生徒情報
     */
    public function getDataSelect(Request $request)
    {
        // IDのバリデーション
        //$this->validateIdsFromRequest($request, 'id');
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
            'selectItems' => $students
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
            $query->SearchRoomForT($form);
        }

        // 生徒IDの検索（スコープで指定する）
        $query->SearchSid($form);

        // 受け持ち生徒に限定するガードを掛ける
        $query->where($this->guardTutorTableWithSid());

        // データを取得
        //$grades = $query
        //    ->select(
        //        'grades_id as id',
        //        'regist_time',
        //        'students.name as sname',
        //        'mst_codes_9.name as type_name',
        //        'mst_codes.name as teiki_name',
        //        'ext_trial_master.name as moshi_name',
        //        'scores.created_at'
        //    )
        //    // 試験種別名の取得
        //    ->sdLeftJoin(CodeMaster::class, function ($join) {
        //        $join->on('scores.exam_type', '=', 'mst_codes_9.code')
        //            ->where('mst_codes_9.data_type', AppConst::CODE_MASTER_9);
        //    }, 'mst_codes_9')
        //    // 定期試験名の取得
        //    ->sdLeftJoin(CodeMaster::class, function ($join) {
        //        $join->on('scores.exam_id', '=', 'mst_codes.code')
        //            ->where('mst_codes.data_type', AppConst::CODE_MASTER_10)
        //            ->where('scores.exam_type', AppConst::CODE_MASTER_9_2);
        //    })
        //    // 模擬試験名の取得
        //    ->sdLeftJoin(ExtTrialMaster::class, function ($join) {
        //        $join->on('scores.exam_id', '=', 'ext_trial_master.tmid')
        //            ->where('scores.exam_type', AppConst::CODE_MASTER_9_1);
        //    })
        //    // 生徒名の取得
        //    ->sdLeftJoin(Student::class, 'scores.student_id', '=', 'students.student_id')
        //    ->orderBy('scores.regist_time', 'desc')
        //    ->orderBy('scores.created_at', 'desc');

        // ページネータで返却
        //return $this->getListAndPaginator($request, $grades);
        return $this->getListAndPaginatorMock();
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

        return ['id' => $request->id];

        //==========================
        // 本番用処理
        //==========================
        // // IDのバリデーション
        // $this->validateIdsFromRequest($request, 'id');

        // // IDを取得
        // $id = $request->input('id');

        // // クエリを作成
        // $query = Score::query();

        // // 受け持ち生徒に限定するガードを掛ける
        // $query->where($this->guardTutorTableWithSid());

        // // データを取得（生徒成績）
        // $grades = $query
        //     // IDを指定
        //     ->where('scores.grades_id', $id)
        //     // データを取得
        //     ->select(
        //         'scores.regist_time',
        //         'students.name as sname',
        //         'mst_codes_9.name as type_name',
        //         'mst_codes.name as teiki_name',
        //         'ext_trial_master.name as moshi_name',
        //         'student_comment'
        //     )
        //     // 試験種別名の取得
        //     ->sdLeftJoin(CodeMaster::class, function ($join) {
        //         $join->on('scores.exam_type', '=', 'mst_codes_9.code')
        //             ->where('mst_codes_9.data_type', AppConst::CODE_MASTER_9);
        //     }, 'mst_codes_9')
        //     // 定期試験名の取得
        //     ->sdLeftJoin(CodeMaster::class, function ($join) {
        //         $join->on('scores.exam_id', '=', 'mst_codes.code')
        //             ->where('mst_codes.data_type', AppConst::CODE_MASTER_10)
        //             ->where('scores.exam_type', AppConst::CODE_MASTER_9_2);
        //     })
        //     // 模擬試験名の取得
        //     ->sdLeftJoin(ExtTrialMaster::class, function ($join) {
        //         $join->on('scores.exam_id', '=', 'ext_trial_master.tmid')
        //             ->where('scores.exam_type', AppConst::CODE_MASTER_9_1);
        //     })
        //     // 生徒名の取得
        //     ->sdLeftJoin(Student::class, 'scores.student_id', '=', 'students.student_id')
        //     ->firstOrFail();

        // // データを取得（生徒成績詳細）
        // $gradesDetails = $this->getGradesDetail($id);

        // return [
        //     'regist_time' => $grades->regist_time,
        //     'sname' => $grades->sname,
        //     'type_name' => $grades->type_name,
        //     'teiki_name' => $grades->teiki_name,
        //     'moshi_name' => $grades->moshi_name,
        //     'student_comment' => $grades->student_comment,
        //     'gradesDetails' => $gradesDetails
        // ];
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

        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            if ($value == -1) return;
            // 教室リストを取得
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

        $rules += Schedule::fieldRules('campus_cd', [$validationRoomList]);
        $rules += Score::fieldRules('student_id', [$validationStudentList]);

        return $rules;
    }
}
