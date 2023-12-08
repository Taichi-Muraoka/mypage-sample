<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\MstCourse;
use App\Models\MstSubject;
use App\Models\CodeMaster;
use App\Models\Report;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use App\Http\Controllers\Traits\FuncStudentClassTrait;

/**
 * 授業情報検索 - コントローラ
 */
class StudentClassController extends Controller
{

    // 機能共通処理：カレンダー
    use FuncCalendarTrait;
    // 機能共通処理：授業情報検索
    use FuncStudentClassTrait;

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
        $absent_status = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_35, [AppConst::CODE_MASTER_35_SUB_0, AppConst::CODE_MASTER_35_SUB_1, AppConst::CODE_MASTER_35_SUB_2]);

        // 教科リストを取得
        $subjects = $this->mdlGetSubjectList();

        // 授業報告書ステータスを取得
        $report_status_list = $this->mdlMenuFromCodeMasterGenItem(AppConst::CODE_MASTER_4, "gen_item2");

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
            // スケジュール情報・受講生徒情報に存在するかチェックする。existsを使用した
            $query->where(function ($orQuery) use ($form) {
                // スケジュール情報から絞り込み（１対１授業）
                $orQuery->where('schedules.absent_status', $form['absent_status'])
                    // または受講生徒情報から絞り込み（１対多授業）
                    ->orWhereExists(function ($query) use ($form) {
                        $query->from('class_members')
                            ->whereColumn('class_members.schedule_id', 'schedules.schedule_id')
                            ->where('class_members.absent_status', $form['absent_status'])
                            // delete_dt条件の追加
                            ->whereNull('class_members.deleted_at');
                    });
            })
                // コース種別が面談・自習のものを除外
                ->whereNotIn('mst_courses.course_kind', [AppConst::CODE_MASTER_42_3, AppConst::CODE_MASTER_42_4]);
        }

        // 生徒名検索
        if (isset($form['student_name']) && filled($form['student_name'])) {
            // スケジュール情報・受講生徒情報に存在するかチェックする。existsを使用した
            $query->where(function ($orQuery) use ($form) {
                // スケジュール情報から絞り込み（１対１授業）
                $orQuery->where('students.name', 'LIKE', '%' . $form['student_name'] . '%')
                    // または受講生徒情報から絞り込み（１対多授業）
                    ->orWhereExists(function ($query) use ($form) {
                        $query->from('class_members')
                            ->join('students', 'students.student_id', '=', 'class_members.student_id')
                            ->whereColumn('class_members.schedule_id', 'schedules.schedule_id')
                            ->where('students.name', 'LIKE', '%' . $form['student_name'] . '%')
                            // delete_dt条件の追加
                            ->whereNull('class_members.deleted_at');
                    });
            });
        }

        // 講師名検索
        $formTutor = ['name' => $request->tutor_name];
        (new Tutor)->scopeSearchName($query, $formTutor);

        // 日付の絞り込み条件
        $query->SearchTargetDateFrom($form);
        $query->SearchTargetDateTo($form);

        // 授業報告書ステータス選択により絞り込み
        if (isset($form['report_status']) && filled($form['report_status'])) {
            // 現在日を取得
            $today = date("Y-m-d");

            switch ($form['report_status']) {
                    // ―（登録不要）
                case AppConst::CODE_MASTER_4_0:
                    // 授業日が当日以降(未実施)または当日欠席の授業 またはコース種別＝面談または自習
                    $query->where(function ($orQuery) use ($today) {
                        $orQuery
                            ->where('schedules.target_date', '>=', $today)
                            ->orWhere('schedules.absent_status', AppConst::CODE_MASTER_35_1)
                            ->orWhere('schedules.absent_status', AppConst::CODE_MASTER_35_2)
                            ->orWhereIn('mst_courses.course_kind', [AppConst::CODE_MASTER_42_3, AppConst::CODE_MASTER_42_4]);
                    });
                    break;

                    // ×（要登録・差戻し）
                case AppConst::CODE_MASTER_4_3:
                    // コース種別が面談・自習のものは除外する
                    $query->whereNotIn('mst_courses.course_kind', [AppConst::CODE_MASTER_42_3, AppConst::CODE_MASTER_42_4]);
                    $query->where(function ($orQuery) use ($today) {
                        $orQuery
                            // 授業日が当日以前かつ出欠ステータスが「実施前・出席」かつ授業報告書登録なし
                            ->where(function ($subQuery) use ($today) {
                                $subQuery
                                    ->where('schedules.target_date', '<', $today)
                                    ->where('schedules.absent_status', AppConst::CODE_MASTER_35_0)
                                    ->whereNull('schedules.report_id');
                            })
                            // 授業報告書登録あり かつ 承認状態＝差戻し
                            ->orWhere(function ($subQuery) use ($today) {
                                $subQuery->whereNotNull('schedules.report_id')
                                    ->where('reports.approval_status', '=', AppConst::CODE_MASTER_4_3);
                            });
                    });
                    break;

                    // △（承認待ち）
                case AppConst::CODE_MASTER_4_1:
                    // 授業報告書登録あり かつ 報告書承認状態＝承認待ち
                    // (reportsテーブルはベースのクエリでjoinされている前提)
                    $query->whereNotNull('schedules.report_id')
                        ->where('reports.approval_status', '=', AppConst::CODE_MASTER_4_1);
                    break;

                    // 〇（承認済み）
                case AppConst::CODE_MASTER_4_2:
                    // 授業報告書登録あり かつ 報告書承認状態＝承認の場合
                    // (reportsテーブルはベースのクエリでjoinされている前提)
                    $query->whereNotNull('schedules.report_id')
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
            'room_names.room_name_symbol as room_name',
            'schedules.target_date',
            'schedules.period_no',
            'schedules.start_time',
            'schedules.course_cd',
            'schedules.absent_status',
            'schedules.report_id as report_id',
            'mst_courses.short_name as course_name',
            'mst_courses.course_kind as course_kind',
            'students.name as student_name',
            'tutors.name as tutor_name',
            'mst_subjects.short_name as subject_name',
            'mst_codes_31.gen_item1 as lesson_kind_name',
            'mst_codes_35.gen_item1 as absent_status_name',
            'schedules.create_kind',
            'mst_codes_32.name as create_kind_name',
            'admin_users.name as admin_name',
            'reports.approval_status as approval_status'
        )
            // 授業報告書情報をJOIN
            ->sdLeftJoin(Report::class, function ($join) {
                $join->on('schedules.report_id', 'reports.report_id');
            })
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
            // データ作成区分名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.create_kind', '=', 'mst_codes_32.code')
                    ->where('mst_codes_32.data_type', AppConst::CODE_MASTER_32);
            }, 'mst_codes_32')
            // 出欠ステータス名の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.absent_status', 'mst_codes_35.code')
                    ->where('mst_codes_35.data_type', AppConst::CODE_MASTER_35);
            }, 'mst_codes_35')
            // 管理者名の取得
            ->sdLeftJoin(AdminUser::class, function ($join) {
                $join->on('schedules.adm_id', 'admin_users.adm_id');
            })
            // 振替済・リセット済スケジュールを除外
            ->whereNotIn('schedules.absent_status', [AppConst::CODE_MASTER_35_5, AppConst::CODE_MASTER_35_7])
            ->orderBy('schedules.target_date', 'desc')
            ->orderBy('schedules.start_time', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $schedules, function ($items) {
            // 報告書ステータスリスト取得
            $statusList = $this->mdlMenuFromCodeMasterGenItem(AppConst::CODE_MASTER_4, "gen_item1");
            foreach ($items as $item) {
                // 画面表示用報告書ステータス（文字列）取得
                $item['report_status'] = $this->fncStclGetReportStatus($item, $statusList, $item['approval_status']);
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
            $report_status_list = $this->mdlMenuFromCodeMasterGenItem(AppConst::CODE_MASTER_4, "gen_item2");
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
        $ruleStudentName = Student::getFieldRule('name');
        $rules += ['student_name' => $ruleStudentName];
        $ruleTutorName = Tutor::getFieldRule('name');
        $rules += ['tutor_name' => $ruleTutorName];
        $rules += ['report_status' => $validationReportStatusList];
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

        // スケジュール情報取得
        $query = Schedule::query();

        // スケジュール情報表示用のquery作成（select句・join句）
        $this->getScheduleQuery($query);

        // 個別の絞り込み条件を付加する
        $schedule = $query->where('schedules.schedule_id', $id)
            // スケジュールID指定
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            ->firstOrFail();

        // 振替の場合、授業区分に付加する文字列を設定
        $schedule['hurikae_name'] = "";
        if ($schedule['create_kind'] == AppConst::CODE_MASTER_32_2) {
            $schedule['hurikae_name'] = $schedule['create_kind_name'];
        }

        // １対多授業の場合、受講生徒名を取得
        if ($schedule['course_kind'] == AppConst::CODE_MASTER_42_2) {
            $schedule['class_student_names'] = $this->getClassMembers($schedule['schedule_id']);
        }

        // 不要な要素の削除
        unset($schedule['campus_cd']);
        unset($schedule['room_symbol']);
        unset($schedule['booth_cd']);
        unset($schedule['course_cd']);
        unset($schedule['student_id']);
        unset($schedule['tutor_id']);
        unset($schedule['subject_cd']);
        unset($schedule['summary_kind']);
        unset($schedule['absent_tutor_id']);
        unset($schedule['absent_status']);
        unset($schedule['tentative_status']);

        // 授業報告書ステータスの取得
        $report = Report::select(
            'approval_status'
        )
            // スケジュールIDを指定
            ->where('schedule_id', $id)
            ->first();

        // 報告書ステータスリスト取得
        $statusList = $this->mdlMenuFromCodeMasterGenItem(AppConst::CODE_MASTER_4, "gen_item2");

        // 画面表示用報告書ステータス（文字列）取得
        $approval_status = null;
        if ($report) {
            $approval_status = $report->approval_status;
        }
        $schedule['report_status'] = $this->fncStclGetReportStatus($schedule, $statusList, $approval_status);

        return $schedule;
    }
}
