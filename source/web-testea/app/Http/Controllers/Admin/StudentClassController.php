<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Consts\AppConst;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\MstCourse;
use App\Models\MstSubject;
use App\Models\CodeMaster;
use Illuminate\Support\Facades\Lang;
//use App\Http\Controllers\Traits\FuncReportTrait;
use Carbon\Carbon;

/**
 * 授業情報検索 - コントローラ
 */
class StudentClassController extends Controller
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
        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // コースリストを取得
        $courses = $this->mdlGetCourseList();

        // 授業区分リストを取得
        $lesson_kind = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_31);

        // 出欠ステータス
        $absent_status = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_35);

        // 教科リストを取得
        $subjects = $this->mdlGetSubjectList();

        return view('pages.admin.student_class', [
            'rules' => $this->rulesForSearch(null),
            'editData' => null,
            'rooms' => $rooms,
            'courses' => $courses,
            'lesson_kind' => $lesson_kind,
            'absent_status' => $absent_status,
            'subjects' => $subjects,
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
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = Schedule::query();

        // 校舎コード選択による絞り込み条件
        // -1 は未選択状態のため、-1以外の場合に校舎コードの絞り込みを行う
        if (isset($form['campus_cd']) && filled($form['campus_cd']) && $form['campus_cd'] != -1) {
            // 検索フォームから取得（スコープ）
            $query->SearchCampusCd($form);
        }

        // コースコード選択により絞り込み条件
        if (isset($form['course_cd']) && filled($form['course_cd'])) {
            // 検索フォームから取得（スコープ）
            $query->SearchCourseCd($form);
        }

        // 教科コード選択により絞り込み条件
        if (isset($form['subject_cd']) && filled($form['subject_cd'])) {
            // 検索フォームから取得（スコープ）
            $query->SearchSubjectCd($form);
        }

        // 授業区分選択により絞り込み条件
        if (isset($form['lesson_kind']) && filled($form['lesson_kind'])) {
            // 検索フォームから取得（スコープ）
            $query->SearchLessonKind($form);
        }

        // 出欠ステータス選択により絞り込み条件
        if (isset($form['absent_status']) && filled($form['absent_status'])) {
            // 検索フォームから取得（スコープ）
            $query->SearchAbsentStatus($form);
        }

        // 生徒名検索
        $formStudent = ['name' => $request->student_name];
        (new Student)->scopeSearchName($query, $formStudent);
        // 講師名検索
        $formTutor = ['name' => $request->tutor_name];
        (new Tutor)->scopeSearchName($query, $formTutor);

        // 日付の絞り込み条件
        $query->SearchTargetDateFrom($form);
        $query->SearchTargetDateTo($form);

        // 校舎名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        $schedules = $query->select(
                'schedules.schedule_id as id',
                'room_names.room_name as room_name',
                'schedules.target_date',
                'schedules.period_no',
                'schedules.start_time',
                'schedules.course_cd',
                'mst_courses.name as course_name',
                'mst_courses.course_kind as course_kind',
                'students.name as student_name',
                'tutors.name as tutor_name',
                'mst_subjects.name as subject_name',
                'mst_codes1.name as lesson_kind_name',
                'mst_codes2.name as absent_status_name',
            )
            // 校舎名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'room_names.code');
            })
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, 'schedules.course_cd', '=', 'mst_courses.course_cd')
            // 生徒名の取得
            ->sdLeftJoin(Student::class, 'schedules.student_id', '=', 'students.student_id')
            // 講師名の取得
            ->sdLeftJoin(Tutor::class, 'schedules.tutor_id', '=', 'tutors.tutor_id')
            // コース名の取得
            ->sdLeftJoin(MstSubject::class, 'schedules.subject_cd', '=', 'mst_subjects.subject_cd')
            // 授業区分名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.lesson_kind', 'mst_codes1.code')
                    ->where('mst_codes1.data_type', AppConst::CODE_MASTER_31);
            }, 'mst_codes1')
            // 出欠ステータス名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.absent_status', 'mst_codes2.code')
                    ->where('mst_codes2.data_type', AppConst::CODE_MASTER_35);
            }, 'mst_codes2')
            ->distinct()
            ->orderBy('schedules.target_date', 'desc')
            ->orderBy('schedules.period_no', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $schedules);
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
        $validator = Validator::make($request->all(), $this->rulesForSearch($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch(?Request $request)
    {

        $rules = array();

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {

                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック コース
        $validationCourseList =  function ($attribute, $value, $fail) {

            // コースリストを取得
            $courses = $this->mdlGetCourseList();
            if (!isset($courses[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 授業区分
        $validationLessonKindList =  function ($attribute, $value, $fail) {

            // 授業区分リストを取得
            $lesson_kind = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_31);
            if (!isset($lesson_kind[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 出欠ステータス
        $validationAbsentStatusList =  function ($attribute, $value, $fail) {

            // 出欠ステータスリストを取得
            $absent_status = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_35);
            if (!isset($absent_status[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 教科
        $validationSubjectList =  function ($attribute, $value, $fail) {

            // 教科リストを取得
            $subjects = $this->mdlGetSubjectList();
            if (!isset($subjects[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $ruleLessonDate = Schedule::getFieldRule('target_date');
        // FromとToの大小チェックバリデーションを追加(Fromが存在する場合のみ)
        $validateFromTo = [];
        $keyFrom = 'target_date_from';
        if (isset($request[$keyFrom]) && filled($request[$keyFrom])) {
            $validateFromTo = ['after_or_equal:' . $keyFrom];
        }

        $rules += Schedule::fieldRules('campus_cd', [$validationRoomList]);
        $rules += Schedule::fieldRules('course_cd', [$validationCourseList]);
        $rules += Schedule::fieldRules('lesson_kind', [$validationLessonKindList]);
        $rules += Schedule::fieldRules('absent_status', [$validationAbsentStatusList]);
        $rules += Schedule::fieldRules('subject_cd', [$validationSubjectList]);
        $rules += ['student_name' => ['string', 'max:50']];
        $rules += ['tutor_name' => ['string', 'max:50']];
        $rules += ['target_date_from' => $ruleLessonDate];
        $rules += ['target_date_to' => array_merge($validateFromTo, $ruleLessonDate)];

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
        //$query = ExtStudentKihon::query();
        //$student = $query
        //    ->select(
        //        'name'
        //    )
        //    ->where('ext_student_kihon.sid', '=', $sid)
        //    ->firstOrFail();

        //return $student;
    }
}
