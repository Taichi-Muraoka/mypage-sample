<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\ClassMember;
use App\Models\MstCourse;
use App\Models\MstSubject;
use App\Models\CodeMaster;
use App\Models\MstBooth;
use App\Models\Report;
use Illuminate\Support\Facades\Lang;

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

        // 出欠ステータスリストを取得
        $absent_status = CodeMaster::query()
            ->select('code', 'name as value')
            ->where('data_type', AppConst::CODE_MASTER_35)
            // 振替済みを除外
            ->where('code', '!=', AppConst::CODE_MASTER_35_5)
            ->orderby('order_code')
            ->get()
            ->keyBy('code');

        // 教科リストを取得
        $subjects = $this->mdlGetSubjectList();

        // 授業報告書ステータスを取得 コードマスタにないのでappconfに定義しています。
        $report_status_list = config('appconf.report_status');

        return view('pages.admin.student_class', [
            'rules' => $this->rulesForSearch(null),
            'editData' => null,
            'rooms' => $rooms,
            'courses' => $courses,
            'lesson_kind' => $lesson_kind,
            'absent_status' => $absent_status,
            'subjects' => $subjects,
            'report_status_list' => $report_status_list
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
            $query->SearchAbsentStatus($form)
                ->where('course_kind', '!=', AppConst::CODE_MASTER_42_3);
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

        // 授業報告書ステータス選択により絞り込み
        if (isset($form['report_status']) && filled($form['report_status'])) {
            switch ($form['report_status']) {
                // ―（登録不要）
                case AppConst::REPORT_STATUS_1:
                    // 授業日が当日以降(未実施)または当日欠席の授業
                    $today = now();
                    $query->where('schedules.target_date', '>=', $today)
                        ->orWhere('schedules.absent_status', AppConst::CODE_MASTER_35_1)
                        ->orWhere('schedules.absent_status', AppConst::CODE_MASTER_35_2);
                    break;
                // ×（要登録・差戻し）
                case AppConst::REPORT_STATUS_2:
                    $today = now();
                    $query
                        ->sdLeftJoin(Report::class, 'schedules.report_id', 'reports.report_id')
                        ->where(function ($orQuery) use ($today) {
                            $orQuery
                                // 授業報告書登録あり かつ 承認状態＝差戻し
                                ->where(function ($orQuery) {
                                    $orQuery
                                        ->whereNotNull('schedules.report_id')
                                        ->where('reports.approval_status', '=', AppConst::CODE_MASTER_4_3);
                                })
                                // 授業日が当日以前かつ出欠ステータスが「実施前・出席」かつ授業報告書登録なし
                                ->orWhere(function ($orQuery) use ($today) {
                                    $orQuery
                                        ->where('schedules.target_date', '<', $today)
                                        ->where('schedules.absent_status', AppConst::CODE_MASTER_35_0)
                                        ->whereNull('schedules.report_id');
                                });
                        });
                    break;
                // △（承認待ち）
                case AppConst::REPORT_STATUS_3:
                    // 授業報告書登録あり かつ 承認状態＝承認待ち
                    $query->whereNotNull('schedules.report_id')
                        ->sdLeftJoin(Report::class, 'schedules.report_id', 'reports.report_id')
                        ->where('reports.approval_status', '=', AppConst::CODE_MASTER_4_1);
                    break;
                // 〇（登録済み）
                case AppConst::REPORT_STATUS_4:
                    // 授業報告書登録あり かつ 承認状態＝承認の場合
                    $query->whereNotNull('schedules.report_id')
                        ->sdLeftJoin(Report::class, 'schedules.report_id', 'reports.report_id')
                        ->where('reports.approval_status', '=', AppConst::CODE_MASTER_4_2);
                    break;

                default:
                    break;
            }
        }

        // 校舎名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        $schedules = $query->select(
            'schedules.schedule_id as id',
            'room_names.room_name as room_name',
            'schedules.target_date',
            'schedules.period_no',
            'schedules.start_time',
            'schedules.course_cd',
            'schedules.absent_status',
            'schedules.report_id as report_id',
            'mst_courses.name as course_name',
            'mst_courses.course_kind as course_kind',
            'students.name as student_name',
            'tutors.name as tutor_name',
            'mst_subjects.name as subject_name',
            'mst_codes_31.name as lesson_kind_name',
            'mst_codes_35.name as absent_status_name',
            'reports_.approval_status as approval_status'
        )
            // 出欠ステータスが振替済みのものは除外
            ->where('schedules.absent_status', '!=', AppConst::CODE_MASTER_35_5)
            ->sdLeftJoin(Report::class, function ($join) {
                $join->on('schedules.report_id', 'reports_.report_id');
            }, 'reports_')
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
                $join->on('schedules.lesson_kind', 'mst_codes_31.code')
                    ->where('mst_codes_31.data_type', AppConst::CODE_MASTER_31);
            }, 'mst_codes_31')
            // 出欠ステータス名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.absent_status', 'mst_codes_35.code')
                    ->where('mst_codes_35.data_type', AppConst::CODE_MASTER_35);
            }, 'mst_codes_35')
            ->distinct()
            ->orderBy('schedules.target_date', 'desc')
            ->orderBy('schedules.period_no', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $schedules, function ($items) {
            // 報告書ステータス設定
            foreach ($items as $item) {
                $item['report_status'] = null;
                // 面談は登録不要ステータスを設定
                if ($item['course_kind'] == AppConst::CODE_MASTER_42_3) {
                    $item['report_status'] = '―';
                } else if ($item['report_id'] != null) {
                    if ($item['approval_status'] == AppConst::CODE_MASTER_4_1) {
                        $item['report_status'] = '△';
                    }
                    if ($item['approval_status'] == AppConst::CODE_MASTER_4_2) {
                        $item['report_status'] = '〇';
                    }
                    if ($item['approval_status'] == AppConst::CODE_MASTER_4_3) {
                        $item['report_status'] = '✕';
                    }
                } else {
                    if (
                        $item['target_date'] >= now() ||
                        $item['absent_status'] == AppConst::CODE_MASTER_35_1 || $item['absent_status'] == AppConst::CODE_MASTER_35_2
                    ) {
                        $item['report_status'] = '―';
                    }
                    if ($item['target_date'] < now() && $item['absent_status'] == AppConst::CODE_MASTER_35_0) {
                        $item['report_status'] = '✕';
                    }
                }
            }
            return $items;
        });
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

        // 独自バリデーション: リストのチェック 授業報告書ステータス
        $validationReportStatusList =  function ($attribute, $value, $fail) {

            // 授業報告書ステータスリストを取得
            $report_status_list = config('appconf.report_status');
            if (!isset($report_status_list[$value])) {
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
        $rules += ['tutor_name' => $validationReportStatusList];
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
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $id = $request->input('id');

        // クエリ作成
        $query = Schedule::query();

        // 校舎名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        $schedules = $query->where('schedules.schedule_id', $id)
            ->select(
                'schedules.schedule_id as id',
                'room_names.room_name as room_name',
                'schedules.target_date',
                'schedules.period_no',
                'schedules.start_time',
                'schedules.end_time',
                'schedules.course_cd',
                'schedules.absent_status',
                'schedules.transfer_class_id',
                'schedules.report_id',
                'schedules.memo',
                'mst_booths.name as booth_name',
                'mst_courses.name as course_name',
                'mst_courses.course_kind as course_kind',
                'students.name as student_name',
                'tutors.name as tutor_name',
                'mst_subjects.name as subject_name',
                'mst_codes_31.name as lesson_kind_name',
                'mst_codes_33.name as how_to_kind_name',
                'mst_codes_34.name as substitute_kind_name',
                'mst_codes_35.name as absent_status_name',
                'reports_.approval_status as approval_status',
                'schedules_.target_date as transfer_target_date',
                'schedules_.period_no as transfer_priod_no'
            )
            // 授業報告書情報
            ->sdLeftJoin(Report::class, function ($join) {
                $join->on('schedules.report_id', 'reports_.report_id');
            }, 'reports_')
            // 振替元スケジュール情報取得
            ->sdLeftJoin(Schedule::class, function ($join) {
                $join->on('schedules.transfer_class_id', 'schedules_.schedule_id');
            }, 'schedules_')
            // 校舎名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'room_names.code');
            })
            // ブース名の取得
            ->sdLeftJoin(MstBooth::class, 'schedules.booth_cd', '=', 'mst_booths.booth_cd')
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
                $join->on('schedules.lesson_kind', 'mst_codes_31.code')
                    ->where('mst_codes_31.data_type', AppConst::CODE_MASTER_31);
            }, 'mst_codes_31')
            // 通塾種別名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.how_to_kind', '=', 'mst_codes_33.code')
                    ->where('mst_codes_33.data_type', AppConst::CODE_MASTER_33);
            }, 'mst_codes_33')
            // 代講種別名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.substitute_kind', '=', 'mst_codes_34.code')
                    ->where('mst_codes_34.data_type', AppConst::CODE_MASTER_34);
            }, 'mst_codes_34')
            // 出欠ステータス名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.absent_status', 'mst_codes_35.code')
                    ->where('mst_codes_35.data_type', AppConst::CODE_MASTER_35);
            }, 'mst_codes_35')
            ->firstOrFail();

        // 集団授業の生徒名取得
        $class_members = ClassMember::query()
            ->where('class_members.schedule_id', '=', $schedules->id)
            ->where('class_members.absent_status', '=', AppConst::CODE_MASTER_35_0)
            ->select('class_members.student_id')
            ->get();

        // 受講人数カウント
        $number_people = count($class_members);

        // 受講生徒名を配列に格納
        $class_member_names = [];
        for ($i = 0; $i < $number_people; $i++) {
            $class_member_names[$i] = $this->mdlGetStudentName($class_members[$i]['student_id']);
        }

        // 授業報告書ステータス
        $report_status = null;
        // 面談は登録不要ステータスを設定
        if ($schedules->course_kind == AppConst::CODE_MASTER_42_3) {
            $report_status = '―';
        } else if ($schedules->report_id != null) {
            if ($schedules->approval_status == AppConst::CODE_MASTER_4_1) {
                $report_status = '△';
            }
            if ($schedules->approval_status == AppConst::CODE_MASTER_4_2) {
                $report_status = '〇';
            }
            if ($schedules->approval_status == AppConst::CODE_MASTER_4_3) {
                $report_status = '✕';
            }
        } else {
            if (
                $schedules->target_date >= now() ||
                $schedules->absent_status == AppConst::CODE_MASTER_35_1 ||
                $schedules->absent_status == AppConst::CODE_MASTER_35_2
            ) {
                $report_status = '―';
            }
            if ($schedules->target_date < now() && $schedules->absent_status == AppConst::CODE_MASTER_35_0) {
                $report_status = '✕';
            }
        }

        return [
            'room_name' => $schedules->room_name,
            'booth_name' => $schedules->booth_name,
            'course_name' => $schedules->course_name,
            'course_kind' => $schedules->course_kind,
            'lesson_kind_name' => $schedules->lesson_kind_name,
            'target_date' => $schedules->target_date,
            'period_no' => $schedules->period_no,
            'start_time' => $schedules->start_time,
            'end_time' => $schedules->end_time,
            'tutor_name' => $schedules->tutor_name,
            'student_name' => $schedules->student_name,
            'class_member_names' => $class_member_names,
            'subject_name' => $schedules->subject_name,
            'how_to_kind_name' => $schedules->how_to_kind_name,
            'substitute_kind_name' => $schedules->substitute_kind_name,
            'absent_status_name' => $schedules->absent_status_name,
            'memo' => $schedules->memo,
            'transfer_class_id' => $schedules->transfer_class_id,
            'transfer_target_date' => $schedules->transfer_target_date,
            'transfer_priod_no' => $schedules->transfer_priod_no,
            'report_status' => $report_status
        ];
    }
}
