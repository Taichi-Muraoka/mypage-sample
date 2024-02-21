<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncSchoolSearchTrait;
use App\Http\Controllers\Traits\FuncDesireMngTrait;
use App\Consts\AppConst;
use App\Models\MstSchool;
use App\Models\StudentEntranceExam;
use App\Models\MstSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;

/**
 * 受験校管理 - コントローラ
 */
class DesiredMngController extends Controller
{
    // 機能共通処理：学校検索モーダル
    use FuncSchoolSearchTrait;

    // 機能共通処理：受験校管理
    use FuncDesireMngTrait;

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

        // 教室管理者の場合、自分の校舎の生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // 生徒名を取得する
        $name = $this->mdlGetStudentName($sid);

        return view('pages.admin.desired_mng', [
            'name' => $name,
            // 検索用にIDを渡す（hidden）
            'editData' => [
                'student_id' => $sid
            ]
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
        // システムマスタ「現年度」を取得（更新ボタン制御用）
        $currentYear = MstSystem::select('value_num')
            ->where('key_id', AppConst::SYSTEM_KEY_ID_1)
            ->whereNotNull('value_num')
            ->firstOrFail();

        // クエリ作成
        $query = StudentEntranceExam::query();

        // 画面表示中生徒のデータに絞り込み
        $query->where('student_entrance_exams.student_id', $request['student_id']);

        // 教室管理者の場合、自分の校舎の生徒のみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithSid());

        // 受験校情報表示用のquery作成 FuncDegireMngTrait
        // データを取得
        $examList = $this->fncDsirGetEntranceExamQuery($query)
            // 更新ボタン押下制御
            // 受験年度が前年度以前の場合、trueをセットする（更新不可）
            ->selectRaw(
                "CASE
                    WHEN exam_year < $currentYear->value_num THEN true
                END AS disabled_btn"
            )
            ->orderBy('student_entrance_exams.exam_year', 'desc')
            ->orderBy('student_entrance_exams.priority_no', 'asc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $examList);
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

        // クエリを作成
        $query = StudentEntranceExam::query();

        // 教室管理者の場合、自分の校舎の生徒のみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithSid());

        // 受験校情報表示用のquery作成 FuncDegireMngTrait
        // データを取得
        $exam = $this->fncDsirGetEntranceExamQuery($query)
            // IDを指定
            ->where('student_entrance_exams.student_exam_id', $request['id'])
            ->firstOrFail();

        return $exam;
    }

    //==========================
    // 登録・編集・削除
    //==========================

    /**
     * 登録画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function new($sid)
    {
        // IDのバリデーション
        $this->validateIds($sid);

        // 教室管理者の場合、自分の校舎の生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // 生徒名を取得する
        $name = $this->mdlGetStudentName($sid);

        // 受験年度リストを取得
        $examYearList = $this->mdlGetExamYearList();
        // 志望順リストを取得
        $priorityList = $this->mdlGetPriorityList();
        // 合否リストを取得
        $resultList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_52);

        // 学校検索モーダル用のデータ渡し
        // 学校種リストを取得
        $schoolKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_49);
        // 設置区分リストを取得
        $establishKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_50);

        // テンプレートは編集と同じ
        return view('pages.admin.desired_mng-input', [
            'editData' => [
                'student_id' => $sid
            ],
            'name' => $name,
            'examYearList' => $examYearList,
            'priorityList' => $priorityList,
            'resultList' => $resultList,
            'rules' => $this->rulesForInput(null),

            // 学校検索モーダル用のバリデーションルール
            'rulesSchool' => $this->rulesForSearchSchool(),
            'schoolKindList' => $schoolKindList,
            'establishKindList' => $establishKindList,
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

        // 保存する項目に絞る
        $form = $request->only(
            'student_id',
            'school_cd',
            'department_name',
            'priority_no',
            'exam_year',
            'exam_name',
            'exam_date',
            'result',
            'memo',
        );

        // 保存
        $exam = new StudentEntranceExam;
        $exam->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int $desiredId 受験校ID
     * @return view
     */
    public function edit($desiredId)
    {
        // IDのバリデーション
        $this->validateIds($desiredId);

        // クエリを作成(PKでユニークに取る)
        $query = StudentEntranceExam::query();
        $exam = $query
            ->select(
                'student_entrance_exams.student_exam_id',
                'student_entrance_exams.student_id',
                'student_entrance_exams.school_cd',
                'student_entrance_exams.department_name',
                'student_entrance_exams.priority_no',
                'student_entrance_exams.exam_year',
                'student_entrance_exams.exam_name',
                'student_entrance_exams.exam_date',
                'student_entrance_exams.result',
                'student_entrance_exams.memo',
                // 画面表示用に、学校名はtext_xxxのように指定する
                'mst_schools.name as text_school_cd',
            )
            ->where('student_exam_id', $desiredId)
            // 教室管理者の場合、自分の校舎の生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 学校マスタとJOIN
            ->sdLeftJoin(MstSchool::class, 'mst_schools.school_cd', '=', 'student_entrance_exams.school_cd')
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 編集可能なデータかチェック
        // システムマスタ「現年度」を取得
        $currentYear = MstSystem::select('value_num')
            ->where('key_id', AppConst::SYSTEM_KEY_ID_1)
            ->whereNotNull('value_num')
            ->firstOrFail();

        if ($exam->exam_year < $currentYear->value_num) {
            // 受験年度が前年度以前の場合、エラーを表示する
            return $this->illegalResponseErr();
        }

        // 生徒名を取得する
        $name = $this->mdlGetStudentName($exam->student_id);

        // 受験年度リストを取得
        $examYearList = $this->mdlGetExamYearList();
        // 志望順リストを取得
        $priorityList = $this->mdlGetPriorityList();
        // 合否リストを取得
        $resultList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_52);

        // 学校検索モーダル用のデータ渡し
        // 学校種リストを取得
        $schoolKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_49);
        // 設置区分リストを取得
        $establishKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_50);

        return view('pages.admin.desired_mng-input', [
            'editData' => $exam,
            'name' => $name,
            'examYearList' => $examYearList,
            'priorityList' => $priorityList,
            'resultList' => $resultList,
            'rules' => $this->rulesForInput(null),

            // 学校検索モーダル用のバリデーションルール
            'rulesSchool' => $this->rulesForSearchSchool(),
            'schoolKindList' => $schoolKindList,
            'establishKindList' => $establishKindList,
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

        // 保存する項目に絞る
        $form = $request->only(
            'school_cd',
            'department_name',
            'priority_no',
            'exam_year',
            'exam_name',
            'exam_date',
            'result',
            'memo',
        );

        // 対象データを取得
        $exam = StudentEntranceExam::where('student_exam_id', $request['student_exam_id'])
            // 教室管理者の場合、自分の校舎の生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $exam->fill($form)->save();

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
        $this->validateIdsFromRequest($request, 'student_exam_id');

        // 対象データを取得(IDでユニークに取る)
        $exam = StudentEntranceExam::where('student_exam_id', $request['student_exam_id'])
            // 教室管理者の場合、自分の校舎の生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 削除
        $exam->delete();

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

        // 独自バリデーション: リストのチェック 受験年度
        $validationExamYearList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlGetExamYearList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 志望順
        $validationPriorityList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlGetPriorityList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 合否
        $validationResultList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_52);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += StudentEntranceExam::fieldRules('exam_year', ['required', $validationExamYearList]);
        $rules += StudentEntranceExam::fieldRules('priority_no', ['required', $validationPriorityList]);
        $rules += StudentEntranceExam::fieldRules('department_name', ['required']);
        $rules += StudentEntranceExam::fieldRules('exam_name', ['required']);
        $rules += StudentEntranceExam::fieldRules('exam_date', ['required']);
        $rules += StudentEntranceExam::fieldRules('result', ['required', $validationResultList]);
        $rules += StudentEntranceExam::fieldRules('memo');
        $rules += ['school_cd' => ['required']];

        return $rules;
    }

    //==========================
    // 学校検索
    //==========================

    /**
     * 検索結果取得(学校検索)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function searchSchool(Request $request)
    {
        // 検索結果を取得
        $schoolList = $this->getSchoolList($request);

        // ページネータで返却
        return $this->getListAndPaginator($request, $schoolList);
    }

    /**
     * バリデーション(学校検索用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForSearchSchool(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForSearchSchool());
        return $validator->errors();
    }
}
