<?php

namespace App\Http\Controllers\Traits;

use App\Http\Controllers\Traits\CtrlResponseTrait;
use App\Consts\AppConst;
use App\Models\MstCampus;
use App\Models\MstTutorGrade;
use App\Models\MstSchool;
use App\Models\MstSubject;
use App\Models\AdminUser;
use App\Models\Tutor;
use App\Models\TutorCampus;
use App\Models\TutorSubject;
use App\Models\CodeMaster;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Auth;

/**
 * 講師詳細 - 機能共通処理
 */
trait FuncTutorDetailTrait
{
    // 応答共通処理
    use CtrlResponseTrait;

    /**
     * 講師詳細情報を取得する（全体）
     *
     * @param integer $tid 講師ID
     */
    private function getTutorDetail($tid)
    {
        // 講師の基本情報を取得する
        $query = Tutor::query();

        // 勤続期間の月数取得のサブクエリ
        $enter_term_query = $this->mdlGetTutorEnterTermQuery();

        $tutor = $query
            ->select(
                'tutors.tutor_id',
                'tutors.name',
                'tutors.name_kana',
                'tutors.tel',
                'tutors.email',
                'tutors.address',
                'tutors.birth_date',
                'tutors.gender_cd',
                // コードマスタの名称（性別）
                'mst_codes_30.name as gender_name',
                'tutors.grade_cd',
                // 学年マスタの名称
                'mst_tutor_grades.name as grade_name',
                'tutors.school_cd_j',
                'tutors.school_cd_h',
                'tutors.school_cd_u',
                // 学校マスタの名称（中高大）
                'mst_schools_j.name as school_j_name',
                'mst_schools_h.name as school_h_name',
                'mst_schools_u.name as school_u_name',
                'tutors.hourly_base_wage',
                'tutors.tutor_status',
                // コードマスタの名称(講師ステータス)
                'mst_codes_29.name as status_name',
                'tutors.enter_date',
                'tutors.leave_date',
                // 勤続期間の月数
                'enter_term_query.enter_term',
                'tutors.memo',
            )
            // 勤続期間の月数の取得
            ->leftJoinSub($enter_term_query, 'enter_term_query', function ($join) {
                $join->on('tutors.tutor_id', '=', 'enter_term_query.tutor_id');
            })
            // 学年の名称取得
            ->sdLeftJoin(MstTutorGrade::class, 'tutors.grade_cd', '=', 'mst_tutor_grades.grade_cd')
            // 出身学校（中）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'tutors.school_cd_j', '=', 'mst_schools_j.school_cd', 'mst_schools_j')
            // 出身学校（高）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'tutors.school_cd_h', '=', 'mst_schools_h.school_cd', 'mst_schools_h')
            // 所属学校（大）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'tutors.school_cd_u', '=', 'mst_schools_u.school_cd', 'mst_schools_u')
            // コードマスターとJOIN 性別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('tutors.gender_cd', '=', 'mst_codes_30.code')
                    ->where('mst_codes_30.data_type', AppConst::CODE_MASTER_30);
            }, 'mst_codes_30')
            // コードマスターとJOIN ステータス
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('tutors.tutor_status', '=', 'mst_codes_29.code')
                    ->where('mst_codes_29.data_type', AppConst::CODE_MASTER_29);
            }, 'mst_codes_29')
            ->where('tutors.tutor_id', '=', $tid)
            ->firstOrFail();

        // 担当教科の取得
        $subject_names = $this->getTutorSubject($tid);

        // 所属校舎の取得
        $campuses = $this->getTutorCampus($tid);

        // 講師ステータスによって退職処理ボタンの押下を制御する（退職処理中・退職済は押下不可）
        $disabledLeaveBtn = false;
        if ($tutor['tutor_status'] == AppConst::CODE_MASTER_29_2 || $tutor['tutor_status'] == AppConst::CODE_MASTER_29_3) {
            $disabledLeaveBtn = true;
        }

        // 所属校舎数によって所属校舎新規登録ボタンの押下を制御する（3つ存在する場合は押下不可）
        $disabledNewBtn = false;
        if (3 <= count($campuses)) {
            $disabledNewBtn = true;
        }

        return [
            'tutor' => $tutor,
            'subject_names' => $subject_names,
            'campuses' => $campuses,
            'disabledLeaveBtn' => $disabledLeaveBtn,
            'disabledNewBtn' => $disabledNewBtn
        ];
    }

    /**
     * 講師担当教科を取得する
     *
     * @param integer $tid 講師ID
     */
    private function getTutorSubject($tid)
    {
        // 講師IDから担当教科を取得する。
        $query = TutorSubject::query();
        $subjects = $query
            ->select(
                'tutor_subjects.subject_cd',
                // 授業科目マスタ名称
                'mst_subjects.name'
            )
            ->sdLeftJoin(MstSubject::class, 'tutor_subjects.subject_cd', '=', 'mst_subjects.subject_cd')
            ->where('tutor_subjects.tutor_id', '=', $tid)
            ->orderby('tutor_subjects.subject_cd')
            ->get();

        // 担当教科が複数ある場合はカンマ区切りで表示する
        $subjectList = [];
        foreach ($subjects as $subject) {
            array_push($subjectList, $subject->name);
        };
        $subject_names = implode(',', $subjectList);

        return $subject_names;
    }

    /**
     * 講師所属校舎を取得する
     *
     * @param integer $tid 講師ID
     */
    private function getTutorCampus($tid)
    {
        // 所属校舎更新ボタンの押下制御用に、ログイン中管理者の校舎を取得する
        // 初期値は本部のコードとする
        $honbuCampusCd = AppConst::CODE_MASTER_6_0;
        $adminCampus = $honbuCampusCd;
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自教室の校舎コードをセットする
            $account = Auth::user();
            $adminCampus = AdminUser::where('adm_id', '=', $account->account_id)->first();
            $adminCampus = $adminCampus->campus_cd;
        }

        // 講師IDから所属校舎を取得する。
        $query = TutorCampus::query();
        $campuses = $query
            ->select(
                'tutor_campuses.tutor_campus_id',
                'tutor_campuses.campus_cd',
                // 校舎名
                'mst_campuses.name as campus_name',
                'tutor_campuses.travel_cost',
            )
            // 更新ボタン押下 可／不可の判定
            // 講師所属校舎がログイン中管理者の校舎コードと一致すればfalse、不一致ならtrueをセットする
            // 本部管理者の場合はfalseをセットする（全校舎更新可能）
            ->selectRaw(
                "CASE
                    WHEN $adminCampus = $honbuCampusCd THEN false
                    WHEN tutor_campuses.campus_cd = $adminCampus THEN false
                    WHEN tutor_campuses.campus_cd != $adminCampus THEN true
                END AS disabled_btn"
            )
            ->sdLeftJoin(MstCampus::class, 'tutor_campuses.campus_cd', '=', 'mst_campuses.campus_cd')
            ->where('tutor_campuses.tutor_id', '=', $tid)
            ->orderby('campus_cd')
            ->orderby('disp_order')
            ->get();

        return $campuses;
    }
}
