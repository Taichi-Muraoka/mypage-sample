<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\Schedule;
use App\Models\CodeMaster;
use App\Models\MstCourse;
use App\Models\MstSubject;
use Illuminate\Support\Facades\Lang;
//use App\Http\Controllers\Traits\FuncReportTrait;
use Carbon\Carbon;

/**
 * 要振替授業管理 - コントローラ
 */
class TransferRequiredController extends Controller
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
        // 校舎プルダウン
        $rooms = $this->mdlGetRoomList(false);

        // 生徒リストを取得
        $studentList = $this->mdlGetStudentList();

        // 講師リストを取得
        $tutorList = $this->mdlGetTutorList();

        return view('pages.admin.transfer_required', [
            'rules' => $this->rulesForSearch(),
            'editData' => null,
            'rooms' => $rooms,
            'studentList' => $studentList,
            'tutorList' => $tutorList
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

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = Schedule::query();

        // 校舎コード選択による絞り込み条件
        if (isset($form['campus_cd']) && filled($form['campus_cd'])) {
            // 検索フォームから取得（スコープ）
            $query->SearchCampusCd($form);
        }

        // 生徒の絞り込み条件
        $query->SearchStudentId($form);

        // 講師の絞り込み条件
        $query->SearchTutorId($form);

        // 日付の絞り込み条件
        $query->SearchTargetDateFrom($form);
        $query->SearchTargetDateTo($form);

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        $schedule = $query
            ->select(
                'schedule_id as id',
                // 校舎の名称
                'room_names.room_name as room_name',
                // 生徒情報の名前
                'students.name as student_name',
                // 講師情報の名前
                'tutors.name as tutor_name',
                // コードマスタの名称（ステータス）
                'mst_codes.name as status_name',
                'target_date',
                'period_no',
                //コース名
                'mst_courses.name as course_name',
                // 教科名
                'mst_subjects.name as subject_name',
                'absent_status'
            )
            // 出欠ステータスが振替中または未振替のもの
            ->where(function ($orQuery) {
                $orQuery
                    ->orWhere('absent_status', AppConst::CODE_MASTER_35_3)
                    ->orWhere('absent_status', AppConst::CODE_MASTER_35_4);
            })
            // 校舎名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'room_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'schedules.student_id', '=', 'students.student_id')
            // 講師名を取得
            ->sdLeftJoin(Tutor::class, 'schedules.tutor_id', '=', 'tutors.tutor_id')
            // コースマスターとJOIN
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd');
            })
            // 教科マスタとJOIN
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('schedules.subject_cd', '=', 'mst_subjects.subject_cd');
            })
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.absent_status', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_35);
            })
            ->orderBy('target_date', 'desc')
            ->orderBy('period_no', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $schedule);
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
}
