<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Score;
use App\Models\ScoreDetail;
use App\Models\CodeMaster;
use App\Models\ExtTrialMaster;
use Illuminate\Support\Facades\DB;
use App\Consts\AppConst;
use App\Http\Controllers\Traits\FuncGradesTrait;
use Carbon\Carbon;

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

        // データを取得
        $grades = $query
            ->select(
                'grades_id as id',
                'regist_time',
                'mst_codes_9.name as type_name',
                'mst_codes.name as teiki_name',
                'ext_trial_master.name as moshi_name',
                'scores.created_at'
            )
            // 試験種別名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('scores.exam_type', '=', 'mst_codes_9.code')
                    ->where('mst_codes_9.data_type', AppConst::CODE_MASTER_9);
            }, 'mst_codes_9')
            // 定期試験名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('scores.exam_id', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_10)
                    ->where('scores.exam_type', AppConst::CODE_MASTER_9_2);
            })
            // 模擬試験名の取得
            ->sdLeftJoin(ExtTrialMaster::class, function ($join) {
                $join->on('scores.exam_id', '=', 'ext_trial_master.tmid')
                    ->where('scores.exam_type', AppConst::CODE_MASTER_9_1);
            })
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // ソート
            ->orderBy('scores.regist_time', 'desc')
            ->orderBy('scores.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $grades);
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

        // IDを取得
        //$id = $request->input('id');

        // クエリを作成
        //$query = Score::query();

        // データを取得（生徒成績）
        //$grades = $query
        //    // IDを指定
        //    ->where('scores.grades_id', $id)
        //    // 自分の生徒IDのみにガードを掛ける
        //    ->where($this->guardStudentTableWithSid())
        //    // データを取得
        //    ->select(
        //        'mst_codes_9.name as type_name',
        //        'mst_codes.name as teiki_name',
        //        'ext_trial_master.name as moshi_name',
        //        'student_comment'
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
        //    // MEMO: 取得できない場合はエラーとする
        //    ->firstOrFail();

        // データを取得（生徒成績詳細）
        //$gradesDetails = $this->getGradesDetail($id);

        return [
        //    'type_name' => $grades->type_name,
        //    'teiki_name' => $grades->teiki_name,
        //    'moshi_name' => $grades->moshi_name,
        //    'student_comment' => $grades->student_comment,
        //    'gradesDetails' => $gradesDetails
            'id' => $request->id
        ];
    }

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

        // ログイン者の情報を取得する
        $account = Auth::user();

        // 試験種別リストを取得
        $examTypes = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_9);

        // 定期考査名リストを取得
        $teikiNames = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_10);

        // 模試名リストを取得
        $moshiNames = $this->getTrialList($account->account_id);

        // 教科リストを取得
        $curriculums = $this->getCurriculumList($account->account_id);

        // 前回比リストを取得
        $updownList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_11);

        return view('pages.student.grades-input', [
            'editData' => null,
            'editDataDtls' => [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null],
            'rules' => $this->rulesForInput(null),
            'examTypes' => $examTypes,
            'teikiNames' => $teikiNames,
            'moshiNames' => $moshiNames,
            'curriculums' => $curriculums,
            'updownList' => $updownList
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

        // ログイン者の情報を取得する
        $account = Auth::user();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request, $account) {

            // 現在日時を取得
            $now = Carbon::now();
            // Gradesテーブルへのinsert
            $grades = new Grades;
            // 登録
            $grades->sid = $account->account_id;
            $grades->exam_type =  $request['exam_type'];
            if ($request['exam_type'] == AppConst::CODE_MASTER_9_1) {
                $grades->exam_id = $request['moshi_id'];
            } else {
                $grades->exam_id = $request['teiki_id'];
            }
            $grades->student_comment = $request['student_comment'];
            $grades->regist_time = $now;
            $grades->save();

            // GradesDetailテーブルへのinsert
            $this->saveToGradesDetail($request, $grades->grades_id);
        });

        return;
    }

    /**
     * 編集画面
     *
     * @param int $gradesId 生徒成績ID
     * @return void
     */
    public function edit($gradesId)
    {
        // IDのバリデーション
        // $this->validateIds($gradesId);

        // ログイン者の情報を取得する
        $account = Auth::user();

        // 試験種別リストを取得
        $examTypes = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_9);

        // 定期考査名リストを取得
        $teikiNames = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_10);

        // 模試名リストを取得
        $moshiNames = $this->getTrialList($account->account_id);

        // 教科リストを取得
        $curriculums = $this->getCurriculumList($account->account_id);

        // 前回比リストを取得
        $updownList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_11);

        // // クエリを作成
        // $query = Score::query();

        // // データを取得（生徒成績）
        // $grades = $query
        //     // データを取得
        //     ->select(
        //         'grades_id',
        //         'exam_type',
        //         'exam_id as teiki_id',
        //         'exam_id as moshi_id',
        //         'student_comment'
        //     )
        //     // IDを指定
        //     ->where('scores.grades_id', $gradesId)
        //     // 自分の生徒IDのみにガードを掛ける
        //     ->where($this->guardStudentTableWithSid())
        //     // MEMO: 取得できない場合はエラーとする
        //     ->firstOrFail();

        // // データを取得（生徒成績詳細）
        // $gradesDetails = $this->getGradesDetailEdit($gradesId);

        return view('pages.student.grades-input', [
            'editData' => null,
            'editDataDtls' => [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null],
            'rules' => $this->rulesForInput(null),
            'examTypes' => $examTypes,
            'teikiNames' => $teikiNames,
            'moshiNames' => $moshiNames,
            'curriculums' => $curriculums,
            'updownList' => $updownList
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

        // gradesテーブルより対象データを取得(PKでユニークに取る)
        $grades = Score::where('grades_id', $request['grades_id'])
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // MEMO: 取得できない場合はエラーとする
            ->firstOrFail();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request, $grades) {

            // Gradesテーブルへのupdate
            // MEMO: 必ず登録する項目のみに絞る。
            $grades->exam_type =  $request['exam_type'];
            if ($request['exam_type'] == AppConst::CODE_MASTER_9_1) {
                $grades->exam_id = $request['moshi_id'];
            } else {
                $grades->exam_id = $request['teiki_id'];
            }
            $grades->student_comment = $request['student_comment'];
            // 登録
            $grades->save();

            // GradesDatailテーブルの更新
            // MEMO: updateではなく、forceDelete・insertとする

            // 成績IDに紐づく成績詳細を全て削除（forceDelete）
            ScoreDetail::where('grades_id', $request['grades_id'])
                ->forceDelete();

            // GradesDetailテーブルへのinsert
            $this->saveToGradesDetail($request, $grades->grades_id);
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
        $this->validateIdsFromRequest($request, 'grades_id');

        // gradesテーブルより対象データを取得(PKでユニークに取る)
        $grades = Score::where('grades_id', $request['grades_id'])
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // MEMO: 取得できない場合はエラーとする
            ->firstOrFail();

        // リクエストのデータが存在するかチェック
        $this->checkGradesDetailFromReqest($request);

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request, $grades) {

            // Gradesテーブルのdelete
            $grades->delete();

            // GradesDatailテーブルのdelete
            // 成績IDに紐づく成績詳細を全て削除(論理削除)
            ScoreDetail::where('grades_id', $request['grades_id'])
                ->delete();
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

        // 独自バリデーション: リストのチェック 模試名
        $validationMoshiNameList =  function ($attribute, $value, $fail) use ($request) {

            $account = Auth::user();
            return $this->validationMoshiNameList($request, $account->account_id, $value, $fail);
        };

        // 独自バリデーション: 生徒成績の存在チェック(1件以上)
        $validationGradesDetail = function ($attribute, $value, $parameters) use ($request) {

            return $this->validationGradesDetail($request);
        };

        // 独自バリデーション: 重複チェック(模試ID)
        $validationKeyMoshi = function ($attribute, $value, $fail) use ($request) {

            // ログイン者の情報を取得する
            $account = Auth::user();
            return $this->validationKeyMoshi($request, $account->account_id, $fail);
        };

        // 独自バリデーション: 重複チェック(定期考査ID)
        $validationKeyTeiki = function ($attribute, $value, $fail) use ($request) {

            // ログイン者の情報を取得する
            $account = Auth::user();
            return $this->validationKeyTeiki($request, $account->account_id, $fail);
        };

        // Laravelの独自バリデーションは、空白の時は呼んでくれないので、
        // 今回のように存在チェックの場合は、以下のように指定し空の場合も呼んでもらう
        Validator::extendImplicit('array_required', $validationGradesDetail);

        // 生徒成績 項目のバリデーションルールをベースにする
        $ruleExamId = Score::getFieldRule('exam_id');

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        // MEMO: 不正アクセス対策として、grades_idもルールに追加する
        $rules += Score::fieldRules('grades_id');
        $rules += Score::fieldRules('exam_type', ['required', $validationExamTypeList]);
        $rules += ['moshi_id' =>  array_merge($ruleExamId, ['required_if:exam_type,1', $validationKeyMoshi, $validationMoshiNameList])];
        // 定期考査の場合、定期考査IDの重複チェックは行わないこととする（複数年度登録不具合対応）
        //$rules += ['teiki_id' =>  array_merge($ruleExamId, ['required_if:exam_type,2', $validationKeyTeiki, $validationTeikiNameList])];
        $rules += Score::fieldRules('student_comment', ['required']);

        // 生徒成績のルールを取得する
        $account = Auth::user();
        $rules = $this->setRulesForGradesDetail($rules, $account->account_id);

        return $rules;
    }
}
