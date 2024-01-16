<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Models\Student;
use App\Models\MstSchool;
use App\Models\CodeMaster;

/**
 * 受験校管理 - 機能共通処理
 */
trait FuncDesireMngTrait
{
    //==========================
    // 関数名を区別するために
    // fncDsirを先頭につける
    //==========================

    /**
     * 受験校情報表示用のquery作成
     * select句・join句を設定する
     * 個別のwhere条件・ガード・ソートは呼び元で行うこと
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    private function fncDsirGetEntranceExamQuery($query)
    {

        return $query
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
                // 生徒情報の名前
                'students.name as student_name',
                // 学校名
                'mst_schools.name as school_name',
                // コードマスタの名称（合否）
                'mst_codes.name as result_name',
                'student_entrance_exams.memo',
            )
            // 学校マスタとJOIN
            ->sdLeftJoin(MstSchool::class, 'mst_schools.school_cd', '=', 'student_entrance_exams.school_cd')
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'student_entrance_exams.student_id', '=', 'students.student_id')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('student_entrance_exams.result', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_52);
            });
    }
}
