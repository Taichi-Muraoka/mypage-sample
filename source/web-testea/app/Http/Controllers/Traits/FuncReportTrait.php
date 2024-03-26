<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\ReportUnit;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\MstCourse;
use App\Models\MstSubject;
use App\Models\MstText;
use App\Models\MstUnitCategory;
use App\Models\MstUnit;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use App\Libs\AuthEx;

/**
 * 授業報告 - 機能共通処理
 */
trait FuncReportTrait
{
    /**
     * 授業報告書教材情報の取得
     *
     * @param integer $id report_id
     * @return object
     */
    private function getReport($id)
    {
        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        // クエリを作成
        $query = Report::query();

        // データを取得
        $report = $query
            // IDを指定
            ->where('reports.report_id', $id)
            ->select(
                'reports.report_id',
                'reports.schedule_id',
                'reports.regist_date',
                'reports.lesson_date',
                'reports.period_no',
                'reports.campus_cd',
                // 校舎名
                'campus_names.room_name as campus_name',
                'reports.course_cd',
                // コース名
                'mst_courses.name as course_name',
                'reports.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'reports.tutor_id',
                // 講師情報の名前
                'tutors.name as tutor_name',
                'reports.monthly_goal',
                'reports.test_contents',
                'reports.test_score',
                'reports.test_full_score',
                'reports.achievement',
                'reports.goodbad_point',
                'reports.solution',
                'reports.others_comment',
                'reports.approval_status',
                // コードマスタの名称（ステータス）
                'mst_codes.name as status',
                // コース種別
                'mst_courses.course_kind as course_kind',
                'reports.admin_comment',
                'schedules.subject_cd as subject_cd',
                // 科目名
                'mst_subjects.name as subject_name',
            )
            // 受講生徒情報のJOIN
            ->sdLeftJoin(ClassMember::class, 'reports.schedule_id', '=', 'class_members.schedule_id')
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('reports.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'reports.student_id', '=', 'students.student_id')
            // 講師名を取得
            ->sdLeftJoin(Tutor::class, 'reports.tutor_id', '=', 'tutors.tutor_id')
            // スケジュール情報とJOIN
            ->sdLeftJoin(Schedule::class, 'reports.schedule_id', '=', 'schedules.schedule_id')
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd');
            })
            // 科目の取得
            ->sdLeftJoin(MstSubject::class, 'schedules.subject_cd', '=', 'mst_subjects.subject_cd')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('reports.approval_status', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_2);
            });

        // ユーザー権限による絞り込みを入れる
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎でガードを掛ける
            $report->where($this->guardRoomAdminTableWithRoomCd());
        }
        if (AuthEx::isTutor()) {
            // // 講師の場合、自分の担当生徒のみにガードを掛ける
            // $query->where($this->guardTutorTableWithSid());

            // ログイン者の情報を取得する
            $account = Auth::user();
            $account_id = $account->account_id;

            // 受け持ち生徒リスト（配列）取得
            $myStudents = $this->mdlGetStudentArrayForT();

            // ガード）担当生徒で絞り込み
            // 以下の条件はクロージャで記述(orを含むため)
            $report->where(function ($query) use ($myStudents) {
                // スケジュール情報から絞り込み（１対１授業）
                $query->whereIn('schedules.student_id', $myStudents)
                    // または受講生徒情報から絞り込み（１対多授業）
                    ->orWhereIn('class_members.student_id', $myStudents);
            })
            // 自分の報告書かどうかで取得条件切り分け
            // 以下の条件はクロージャで記述(orを含むため)
            ->where(function ($query) use ($account_id) {
                // 自分の報告書は承認ステータス全て取得
                $query->where('reports.tutor_id', $account_id)
                    // 自分以外の報告書は承認済みのもののみ
                    ->OrWhere(function ($query) use ($account_id) {
                        $query->where('reports.tutor_id', '<>', $account_id)
                            ->where('reports.approval_status', AppConst::CODE_MASTER_4_2);
                    });
            });
        }

        return $report->firstOrFail();
    }
    /**
     * 集団授業の生徒名取得
     *
     * @param integer $schedule_id スケジュールID
     * @return array
     */
    private function getClassMember($schedule_id)
    {
        $class_members = ClassMember::query()
            ->where('class_members.schedule_id', '=', $schedule_id)
            ->select('class_members.student_id')
            ->get();

        // 受講人数カウント
        $number_people = count($class_members);

        // 受講生徒名を配列に格納
        $class_member_names = [];
        for ($i = 0; $i < $number_people; $i++) {
            $class_member_names[$i] = $this->mdlGetStudentName($class_members[$i]['student_id']);
        }

        return $class_member_names;
    }
    /**
     * 授業報告書教材情報の取得 詳細モーダル表示用
     *
     * @param integer $report_id 授業報告書ID
     * @param integer $sub_code AppConst 授業報告書サブコード
     * @return array
     */
    private function getReportText($report_id, $sub_code)
    {
        $report_text = ReportUnit::query()
            ->where('report_units.report_id', '=', $report_id)
            ->where('report_units.sub_cd', '=', $sub_code)
            ->select(
                'mst_texts.name as text_name1', // 教材名
                'report_units.free_text_name as free_text_name1', // 教材名フリー
                'report_units.text_page as text_page1',
            )
            // 教材名の取得
            ->sdLeftJoin(MstText::class, function ($join) {
                $join->on('report_units.text_cd', '=', 'mst_texts.text_cd');
            })
            ->first();

        return $report_text;
    }
    /**
     * 単元分類・単元情報取得 詳細モーダル表示用
     *
     * @param integer $report_id 授業報告書ID
     * @param integer $sub_code AppConst 授業報告書サブコード
     * @param integer $i 1~3単元分類の数
     * @return array
     */
    private function getReportCategory($report_id, $sub_code, $no)
    {
        if ($no == 1) {
            $report_category = ReportUnit::query()
                ->where('report_units.report_id', '=', $report_id)
                ->where('report_units.sub_cd', '=', $sub_code)
                ->select(
                    'mst_unit_categories.name as unit_category_name', // 単元分類名
                    'report_units.free_category_name1 as free_category_name', // 単元分類名フリー
                    'mst_units.name as unit_name', // 単元名
                    'report_units.free_unit_name1 as free_unit_name', // 単元名フリー
                )
                // 単元分類名の取得
                ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                    $join->on('report_units.unit_category_cd1', '=', 'mst_unit_categories.unit_category_cd');
                })
                // 単元名取得
                ->sdLeftJoin(MstUnit::class, function ($join) {
                    $join->on('report_units.unit_cd1', '=', 'mst_units.unit_cd')
                        ->on('report_units.unit_category_cd1', '=', 'mst_units.unit_category_cd');
                })
                ->first();
        }
        if ($no == 2) {
            $report_category = ReportUnit::query()
                ->where('report_units.report_id', '=', $report_id)
                ->where('report_units.sub_cd', '=', $sub_code)
                ->select(
                    'mst_unit_categories.name as unit_category_name', // 単元分類名
                    'report_units.free_category_name2 as free_category_name', // 単元分類名フリー
                    'mst_units.name as unit_name', // 単元名
                    'report_units.free_unit_name2 as free_unit_name', // 単元名フリー
                )
                // 単元分類名の取得
                ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                    $join->on('report_units.unit_category_cd2', '=', 'mst_unit_categories.unit_category_cd');
                })
                // 単元名取得
                ->sdLeftJoin(MstUnit::class, function ($join) {
                    $join->on('report_units.unit_cd2', '=', 'mst_units.unit_cd')
                        ->on('report_units.unit_category_cd2', '=', 'mst_units.unit_category_cd');
                })
                ->first();
        }
        if ($no == 3) {
            $report_category = ReportUnit::query()
                ->where('report_units.report_id', '=', $report_id)
                ->where('report_units.sub_cd', '=', $sub_code)
                ->select(
                    'mst_unit_categories.name as unit_category_name', // 単元分類名
                    'report_units.free_category_name3 as free_category_name', // 単元分類名フリー
                    'mst_units.name as unit_name', // 単元名
                    'report_units.free_unit_name3 as free_unit_name', // 単元名フリー
                )
                // 単元分類名の取得
                ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                    $join->on('report_units.unit_category_cd3', '=', 'mst_unit_categories.unit_category_cd');
                })
                // 単元名取得
                ->sdLeftJoin(MstUnit::class, function ($join) {
                    $join->on('report_units.unit_cd3', '=', 'mst_units.unit_cd')
                        ->on('report_units.unit_category_cd3', '=', 'mst_units.unit_category_cd');
                })
                ->first();
        }
        return $report_category;
    }
    /**
     * 授業報告書教材情報の取得 編集画面用
     *
     * @param integer $report_id 授業報告書ID
     * @param integer $sub_code AppConst 授業報告書サブコード
     * @return object
     */
    private function getReportUnit($report_id, $sub_code)
    {
        $report_unit = ReportUnit::query()
            ->where('report_units.report_id', '=', $report_id)
            ->where('report_units.sub_cd', '=', $sub_code)
            ->select(
                'report_units.report_id',
                'report_units.sub_cd',
                'report_units.text_cd',
                'mst_texts1.name as text_name1', // 教材名
                'report_units.free_text_name as free_text_name', // 教材名フリー
                'report_units.text_page as text_page1',
                'report_units.unit_category_cd1',
                'mst_unit_categories1.name as unit_category_name1', // 単元分類名
                'report_units.free_category_name1 as free_category_name1', // 単元分類名フリー
                'report_units.unit_cd1',
                'mst_units1.name as unit_name1', // 単元名
                'report_units.free_unit_name1 as free_unit_name1', // 単元名フリー
                'report_units.unit_category_cd2',
                'mst_unit_categories2.name as unit_category_name2', // 単元分類名
                'report_units.free_category_name2 as free_category_name2', // 単元分類名フリー
                'report_units.unit_cd2',
                'mst_units2.name as unit_name2', // 単元名
                'report_units.free_unit_name2 as free_unit_name2', // 単元名フリー
                'report_units.unit_category_cd3',
                'mst_unit_categories3.name as unit_category_name3', // 単元分類名
                'report_units.free_category_name3 as free_category_name3', // 単元分類名フリー
                'report_units.unit_cd3',
                'mst_units3.name as unit_name3', // 単元名
                'report_units.free_unit_name3 as free_unit_name3', // 単元名フリー
            )
            // 教材名の取得
            ->sdLeftJoin(MstText::class, function ($join) {
                $join->on('report_units.text_cd', '=', 'mst_texts1.text_cd');
            }, 'mst_texts1')
            // 単元分類名の取得
            ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                $join->on('report_units.unit_category_cd1', '=', 'mst_unit_categories1.unit_category_cd');
            }, 'mst_unit_categories1')
            ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                $join->on('report_units.unit_category_cd2', '=', 'mst_unit_categories2.unit_category_cd');
            }, 'mst_unit_categories2')
            ->sdLeftJoin(MstUnitCategory::class, function ($join) {
                $join->on('report_units.unit_category_cd3', '=', 'mst_unit_categories3.unit_category_cd');
            }, 'mst_unit_categories3')
            // 単元名取得
            ->sdLeftJoin(MstUnit::class, function ($join) {
                $join->on('report_units.unit_cd1', '=', 'mst_units1.unit_cd')
                    ->on('report_units.unit_category_cd1', '=', 'mst_units1.unit_category_cd');
            }, 'mst_units1')
            ->sdLeftJoin(MstUnit::class, function ($join) {
                $join->on('report_units.unit_cd2', '=', 'mst_units2.unit_cd')
                    ->on('report_units.unit_category_cd2', '=', 'mst_units2.unit_category_cd');
            }, 'mst_units2')
            ->sdLeftJoin(MstUnit::class, function ($join) {
                $join->on('report_units.unit_cd3', '=', 'mst_units3.unit_cd')
                    ->on('report_units.unit_category_cd3', '=', 'mst_units3.unit_category_cd');
            }, 'mst_units3')
            ->first();

        return $report_unit;
    }
}
