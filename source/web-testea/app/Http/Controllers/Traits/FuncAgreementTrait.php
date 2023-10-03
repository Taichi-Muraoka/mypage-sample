<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Models\Student;
use App\Models\StudentCampus;
use App\Models\MstCampus;
use App\Models\MstGrade;
use App\Models\MstSchool;
use App\Models\MstCourse;
use App\Models\MstSubject;
use App\Models\Tutor;
use App\Models\Badge;
use App\Models\RegularClass;
use App\Models\RegularClassMember;
use App\Models\CodeMaster;
use App\Http\Controllers\Traits\CtrlResponseTrait;

/**
 * 生徒情報 - 機能共通処理
 */
trait FuncAgreementTrait
{
    // 応答共通処理
    use CtrlResponseTrait;

    /**
     * 生徒情報を取得する
     *
     * @param integer $sid 生徒ID
     */
    private function getStudentAgreement($sid)
    {
        // 生徒の基本情報を取得する
        $query = Student::query();
        $student = $query
            ->select(
                'students.student_id',
                'students.name',
                'students.grade_cd',
                // 学年マスタの名称
                'mst_grades.name as grade_name',
                'students.school_cd_e',
                'students.school_cd_h',
                'students.school_cd_j',
                // 学校マスタの名称（小中高）
                'mst_schools_e.name as school_e_name',
                'mst_schools_j.name as school_j_name',
                'mst_schools_h.name as school_h_name',
                'students.email_stu',
                'students.email_par',
                'students.stu_status',
                // コードマスタの名称(会員ステータス)
                'mst_codes.name as status_name',
                'students.enter_date',
            )
            // 学年の取得
            ->sdLeftJoin(MstGrade::class, 'students.grade_cd', '=', 'mst_grades.grade_cd')
            // 所属学校（小）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'students.school_cd_e', '=', 'mst_schools_e.school_cd', 'mst_schools_e')
            // 所属学校（中）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'students.school_cd_j', '=', 'mst_schools_j.school_cd', 'mst_schools_j')
            // 所属学校（高）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'students.school_cd_h', '=', 'mst_schools_h.school_cd', 'mst_schools_h')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('students.stu_status', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_28);
            })
            ->where('students.student_id', '=', $sid)
            ->firstOrFail();

        // 生徒IDから所属校舎を取得する。
        $query = StudentCampus::query();
        $campuses = $query
            ->select(
                'student_campuses.student_id',
                'student_campuses.campus_cd',
                // 校舎名
                'mst_campuses.name as campus_name'
            )
            ->sdLeftJoin(MstCampus::class, 'student_campuses.campus_cd', '=', 'mst_campuses.campus_cd')
            ->where('student_campuses.student_id', '=', $sid)
            ->orderby('campus_cd')
            ->orderby('disp_order')
            ->get();

        // 複数校舎所属の場合を考慮し、校舎名を連結する
        $str_campus_names = "";
        foreach ($campuses as $campus) {
            $str_campus_names = $str_campus_names . " " . $campus->campus_name;
        };
        $str_campus_names = ltrim($str_campus_names);

        // バッジ情報の取得
        $badges = $this->getStudentBadge($sid);

        // レギュラー授業情報の取得
        $regular_classes = $this->getStudentRegularClass($sid);

        return [
            'student' => $student,
            'str_campus_names' => $str_campus_names,
            'badges' => $badges,
            'regular_classes' => $regular_classes,
        ];
    }

    /**
     * 生徒のバッジ情報を取得する
     *
     * @param integer $sid 生徒ID
     */
    private function getStudentBadge($sid)
    {
        // 生徒のバッジ情報を取得する
        $query = Badge::query();
        $total_badges = $query
            ->select(
                'badges.badge_id',
                'badges.student_id',
            )
            ->where('badges.student_id', '=', $sid)
            ->count();

        // バッジ色の分岐用に10の位,1の位を分ける
        $tens_place = ($total_badges/10) % 10;
        $ones_place = ($total_badges/1) % 10;

        return [
            'total_badges' => $total_badges,
            'tens_place' => $tens_place,
            'ones_place' => $ones_place,
        ];
    }

    /**
     * 生徒のレギュラー授業情報を取得する
     *
     * @param integer $sid 生徒ID
     */
    private function getStudentRegularClass($sid)
    {
        // 生徒のレギュラー授業情報を取得する
        $query = RegularClass::query();

        $regular_classes = $query
            ->select(
                'regular_classes.regular_class_id',
                'regular_classes.campus_cd',
                // 校舎の名称
                'mst_campuses.name as campus_name',
                'regular_classes.day_cd',
                // コードマスタの名称(曜日)
                'mst_codes.name as day_name',
                'regular_classes.period_no',
                'regular_classes.course_cd',
                // コースの名称
                'mst_courses.name as course_name',
                'regular_classes.tutor_id',
                // 講師の名前
                'tutors.name as tutor_name',
                'regular_classes.subject_cd',
                // 科目の名称
                'mst_subjects.name as subject_name',
            )
            // 校舎名の取得
            ->sdLeftJoin(MstCampus::class, 'regular_classes.campus_cd', '=', 'mst_campuses.campus_cd')
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, 'regular_classes.course_cd', '=', 'mst_courses.course_cd')
            // 講師名の取得
            ->sdLeftJoin(Tutor::class, 'regular_classes.tutor_id', '=', 'tutors.tutor_id')
            // 科目の取得
            ->sdLeftJoin(MstSubject::class, 'regular_classes.subject_cd', '=', 'mst_subjects.subject_cd')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('regular_classes.day_cd', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_16);
            })
            // レギュラー受講生徒情報とJOIN
            ->sdLeftJoin(RegularClassMember::class, 'regular_classes.regular_class_id', '=', 'regular_class_members.regular_class_id')
            ->where('regular_classes.student_id', '=', $sid)
            ->orWhere('regular_class_members.student_id', '=', $sid)
            ->orderBy('regular_classes.day_cd', 'asc')
            ->orderBy('regular_classes.period_no', 'asc')
            ->get();

        return $regular_classes;
    }
}
