<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Score;
use App\Models\ScoreDetail;
use App\Models\CodeMaster;
use App\Models\ExtStudentKihon;
use App\Models\ExtRoom;
use App\Models\ExtTrialMaster;
use Illuminate\Support\Facades\DB;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Http\Controllers\Traits\FuncGradesTrait;
use Illuminate\Support\Facades\Lang;

/**
 * 生徒成績 - コントローラ
 */
class GradesMngController extends Controller
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
     * @param int $sid 生徒ID
     * @return view
     */
    public function index($sid)
    {
        // IDのバリデーション
        $this->validateIds($sid);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 学年リストを取得
        $classes = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

        return view('pages.admin.grades_mng', [
            'rules' => $this->rulesForSearch(),
            'name' => "CWテスト生徒１",
            'sid' => $sid,
            'rooms' => $rooms,
            //'classes' => $classes,
            'editData' => null
        ]);
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

        // 生徒の教室の検索(生徒基本情報参照)
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithSid());
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchRoom($form);
        }

        // 学年の検索(生徒基本情報参照)
        (new ExtStudentKihon)->scopeSearchCls($query, $form);

        // 生徒名の検索(生徒基本情報参照)
        (new ExtStudentKihon)->scopeSearchName($query, $form);

        // データを取得
        $grades = $query
            ->select(
                'grades_id as id',
                'regist_time',
                'ext_student_kihon.name as sname',
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
            // 生徒名の取得
            ->sdLeftJoin(ExtStudentKihon::class, 'scores.student_id', '=', 'ext_student_kihon.sid')
            ->orderBy('scores.regist_time', 'desc')
            ->orderBy('scores.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $grades);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
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

        // // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
        // $query->where($this->guardRoomAdminTableWithSid());

        // // データを取得（生徒成績）
        // $grades = $query
        //     // IDを指定
        //     ->where('scores.grades_id', $id)
        //     // データを取得
        //     ->select(
        //         'scores.regist_time',
        //         'ext_student_kihon.name as sname',
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
        //     ->sdLeftJoin(ExtStudentKihon::class, 'scores.student_id', '=', 'ext_student_kihon.sid')
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
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {

        $rules = array();

        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 教室リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 学年
        $validationClasseList =  function ($attribute, $value, $fail) {

            // 学年リストを取得
            $classes = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

            if (!isset($classes[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        $rules += ExtStudentKihon::fieldRules('name');
        $rules += ExtStudentKihon::fieldRules('cls_cd', [$validationClasseList]);
        $rules += ExtRoom::fieldRules('roomcd', [$validationRoomList]);

        return $rules;
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
        // 試験種別リストを取得
        $examTypes = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_9);

        // 定期考査名リストを取得
        $teikiNames = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_10);

        $editData = [
            "student_id" => 1,
        ];

        return view('pages.admin.grades_mng-input', [
            'editData' => $editData,
            'editDataDtls' => [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null],
            'rules' => $this->rulesForInput(null),
            'examTypes' => $examTypes,
            'teikiNames' => $teikiNames,
            'curriculums' => null,
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
            $grades->student_id = $account->account_id;
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
     * @param int $gradesId  生徒成績Id
     * @return view
     */
    public function edit($gradesId)
    {
        // // IDのバリデーション
        // $this->validateIds($gradesId);

        // 試験種別リストを取得
        $examTypes = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_9);

        // 定期考査名リストを取得
        $teikiNames = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_10);

        // // クエリを作成
        // $query = Score::query();

        // // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
        // $query->where($this->guardRoomAdminTableWithSid());

        // // データを取得（生徒成績）
        // $grades = $query
        //     // データを取得
        //     ->select(
        //         'regist_time',
        //         'ext_student_kihon.name as sname',
        //         'grades_id',
        //         'scores.student_id',
        //         'exam_type',
        //         'exam_id as teiki_id',
        //         'exam_id as moshi_id',
        //         'student_comment'
        //     )
        //     // 生徒名の取得
        //     ->sdLeftJoin(ExtStudentKihon::class, 'scores.student_id', '=', 'ext_student_kihon.sid')
        //     // IDを指定
        //     ->where('scores.grades_id', $gradesId)
        //     // MEMO: 取得できない場合はエラーとする
        //     ->firstOrFail();

        // // データを取得（生徒成績詳細）
        // $gradesDetails = $this->getGradesDetailEdit($gradesId);

        // // 教科リストを取得（対象生徒のもの）
        // $curriculums = $this->getCurriculumList($grades->student_id);

        // // 模試名リストを取得（対象生徒のもの）
        // $moshiNames = $this->getTrialList($grades->student_id);

        $editData = [
            "student_id" => 1,
        ];

        return view('pages.admin.grades_mng-input', [
            'editData' => $editData,
            'editDataDtls' => [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null],
            'rules' => $this->rulesForInput(null),
            'examTypes' => $examTypes,
            'teikiNames' => $teikiNames,
            'curriculums' => null,
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

        // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithSid());

        $grades = $query
            // gradesテーブルより対象データを取得(PKでユニークに取る)
            ->where('grades_id', $request['grades_id'])
            // MEMO: 取得できない場合はエラーとする
            ->firstOrFail();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request, $grades) {

            // Gradesテーブルへのupdate
            // MEMO: 必ず登録する項目のみに絞る。
            $grades->regist_time =  $request['regist_time'];
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

        // クエリを作成
        $query = Score::query();

        // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithSid());

        $grades = $query
            // gradesテーブルより対象データを取得(PKでユニークに取る)
            ->where('grades_id', $request['grades_id'])
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
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {

        $rules = array();

        // 生徒ID取得(sidをチェックに使うのでリクエストで受け取らない)
        $sid = null;
        if (isset($request)) {
            // 生徒成績からsidを取得
            $grades = Score::select(
                'student_id',
            )
                ->where('grades_id', $request->grades_id)
                ->firstOrFail();
            $sid = $grades->student_id;
        }

        // 独自バリデーション: リストのチェック 試験種別
        $validationExamTypeList =  function ($attribute, $value, $fail) {

            return $this->validationExamTypeList($value, $fail);
        };

        // 独自バリデーション: リストのチェック 定期考査名
        $validationTeikiNameList =  function ($attribute, $value, $fail) use ($request) {

            return $this->validationTeikiNameList($request, $value, $fail);
        };

        // 独自バリデーション: リストのチェック 模試名
        $validationMoshiNameList =  function ($attribute, $value, $fail) use ($request, $sid) {

            return $this->validationMoshiNameList($request, $sid, $value, $fail);
        };

        // 独自バリデーション: 生徒成績の存在チェック(1件以上)
        $validationGradesDetail = function ($attribute, $value, $parameters) use ($request) {

            return $this->validationGradesDetail($request);
        };

        // 独自バリデーション: 重複チェック(模試ID)
        $validationKeyMoshi = function ($attribute, $value, $fail) use ($request, $sid) {

            // 対象データを取得(対象生徒のデータ)
            return $this->validationKeyMoshi($request, $sid, $fail);
        };

        // 独自バリデーション: 重複チェック(定期考査ID)
        $validationKeyTeiki = function ($attribute, $value, $fail) use ($request, $sid) {

            // 対象データを取得(対象生徒のデータ)
            return $this->validationKeyTeiki($request, $sid, $fail);
        };

        // Laravelの独自バリデーションは、空白の時は呼んでくれないので、
        // 今回のように存在チェックの場合は、以下のように指定し空の場合も呼んでもらう
        Validator::extendImplicit('array_required', $validationGradesDetail);

        // 生徒成績 項目のバリデーションルールをベースにする
        $ruleExamId = Score::getFieldRule('exam_id');

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += Score::fieldRules('regist_time', ['required']);
        $rules += Score::fieldRules('exam_type', ['required', $validationExamTypeList]);
        $rules += ['moshi_id' =>  array_merge($ruleExamId, ['required_if:exam_type,1', $validationKeyMoshi, $validationMoshiNameList])];
        // 定期考査の場合、定期考査IDの重複チェックは行わないこととする（複数年度登録不具合対応）
        //$rules += ['teiki_id' =>  array_merge($ruleExamId, ['required_if:exam_type,2', $validationKeyTeiki, $validationTeikiNameList])];
        $rules += Score::fieldRules('student_comment', ['required']);

        // 生徒成績のルールを取得する
        $rules = $this->setRulesForGradesDetail($rules, $sid);

        return $rules;
    }
}
