<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Models\MstCampus;
use App\Models\MstGrade;
use App\Models\MstSchool;
use App\Models\MstBooth;
use App\Models\MstCourse;
use App\Models\MstSubject;
use App\Models\MstSystem;
use App\Models\Student;
use App\Models\StudentView;
use App\Models\StudentCampus;
use App\Models\Record;
use App\Models\AdminUser;
use App\Models\RegularClass;
use App\Models\RegularClassMember;
use App\Models\Tutor;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\StudentEntranceExam;
use App\Models\Score;
use App\Models\Badge;
use App\Models\CodeMaster;
use App\Http\Controllers\Traits\CtrlResponseTrait;
use App\Http\Controllers\Traits\FuncGradesTrait;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use Carbon\Carbon;

/**
 * 生徒カルテ - 機能共通処理
 */
trait FuncMemberDetailTrait
{
    // 応答共通処理
    use CtrlResponseTrait;

    // 機能共通処理：生徒成績詳細
    use FuncGradesTrait;

    // 機能共通処理：カレンダー
    use FuncCalendarTrait;

    /**
     * 生徒カルテを取得する（全体）
     *
     * @param integer $sid 生徒ID
     */
    private function getMemberDetail($sid)
    {
        // 生徒の基本情報を取得する
        $query = StudentView::query();
        $student = $query
            ->select(
                'students_view.student_id',
                'students_view.name',
                'students_view.name_kana',
                'students_view.tel_stu',
                'students_view.tel_par',
                'students_view.email_stu',
                'students_view.email_par',
                'students_view.birth_date',
                'students_view.grade_cd',
                // 学年マスタの名称
                'mst_grades.name as grade_name',
                'students_view.school_cd_e',
                'students_view.school_cd_h',
                'students_view.school_cd_j',
                // 学校マスタの名称（小中高）
                'mst_schools_e.name as school_e_name',
                'mst_schools_j.name as school_j_name',
                'mst_schools_h.name as school_h_name',
                'students_view.stu_status',
                // コードマスタの名称(会員ステータス)
                'mst_codes.name as status_name',
                'students_view.enter_date',
                'students_view.leave_date',
                'students_view.enter_term',
                'students_view.lead_id',
                'students_view.storage_link',
                'students_view.memo',
            )
            // 学年の取得
            ->sdLeftJoin(MstGrade::class, 'students_view.grade_cd', '=', 'mst_grades.grade_cd')
            // 所属学校（小）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'students_view.school_cd_e', '=', 'mst_schools_e.school_cd', 'mst_schools_e')
            // 所属学校（中）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'students_view.school_cd_j', '=', 'mst_schools_j.school_cd', 'mst_schools_j')
            // 所属学校（高）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'students_view.school_cd_h', '=', 'mst_schools_h.school_cd', 'mst_schools_h')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('students_view.stu_status', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_28);
            })
            ->where('students_view.student_id', '=', $sid)
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

        // 所属校舎が複数ある場合はカンマ区切りで表示する
        $campusList = [];
        foreach ($campuses as $campus) {
            array_push($campusList, $campus->campus_name);
        };
        $campus_names = implode(',', $campusList);

        // 連絡記録の取得
        $records = $this->getRecord($sid);
        // レギュラー授業情報の取得
        $regular_classes = $this->getRegularClass($sid);
        // 未振替授業情報の取得
        $not_yet_transfer_classes = $this->getNotYetTransferClass($sid);
        // イレギュラー授業情報の取得
        $irregular_classes = $this->getIrregularClass($sid);
        // 受験校情報の取得
        $entrance_exams = $this->getEntranceExam($sid);
        // 成績情報の取得
        $scores = $this->getGrade($sid);
        // バッジ付与情報の取得
        $badges = $this->getBadge($sid);

        // 会員ステータスによって退会ボタンの押下を制御する（退会処理中・退会済は押下不可）
        $disabled = false;
        if($student['stu_status'] == AppConst::CODE_MASTER_28_4 || $student['stu_status'] == AppConst::CODE_MASTER_28_5){
            $disabled = true;
        }

        return [
            'student' => $student,
            'campus_names' => $campus_names,
            'records' => $records,
            'regular_classes' => $regular_classes,
            'not_yet_transfer_classes' => $not_yet_transfer_classes,
            'irregular_classes' => $irregular_classes,
            'entrance_exams' => $entrance_exams,
            'scores' => $scores,
            'badges' => $badges,
            'disabled' => $disabled,
        ];
    }

    /**
     * 連絡記録を取得する
     *
     * @param integer $sid 生徒ID
     */
    private function getRecord($sid)
    {
        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        // クエリ作成
        $query = Record::query();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        $records = $query
            ->select(
                'records.record_id',
                'records.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'records.campus_cd',
                // 校舎名
                'campus_names.room_name as campus_name',
                'records.record_kind',
                // コードマスタの名称（記録種別）
                'mst_codes.name as kind_name',
                'records.adm_id',
                // 管理者の名前
                'admin_users.name as admin_name',
                'records.received_date',
                'records.received_time',
                'records.regist_time',
                'records.memo'
            )
            // 画面表示中生徒のデータに絞り込み
            ->where('records.student_id', $sid)
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('records.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'records.student_id', '=', 'students.student_id')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('records.record_kind', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_46);
            })
            // 管理者名を取得
            ->sdLeftJoin(AdminUser::class, 'records.adm_id', '=', 'admin_users.adm_id')
            ->orderby('records.received_date', 'desc')
            ->orderby('records.received_time', 'desc')
            ->get();

        return $records;
    }

    /**
     * レギュラー授業情報を取得する
     *
     * @param integer $sid 生徒ID
     */
    private function getRegularClass($sid)
    {
        // 校舎名取得
        $campus_names = $this->mdlGetRoomQuery();

        $query = RegularClass::query();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        $regular_classes = $query
            ->select(
                'regular_classes.regular_class_id',
                'regular_classes.campus_cd',
                // 校舎の名称
                'campus_names.room_name as campus_name',
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
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('regular_classes.campus_cd', '=', 'campus_names.code');
            })
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
            // 画面表示中生徒のデータに絞り込み レギュラー情報またはレギュラー受講生徒情報
            ->where(function ($query) use ($sid) {
                $query->where('regular_classes.student_id', '=', $sid)
                    ->orWhere('regular_class_members.student_id', '=', $sid);
            })
            ->orderBy('regular_classes.day_cd', 'asc')
            ->orderBy('regular_classes.period_no', 'asc')
            ->get();

        return $regular_classes;
    }

    /**
     * 未振替授業情報を取得する
     *
     * @param integer $sid 生徒ID
     */
    private function getNotYetTransferClass($sid)
    {
        // 校舎名取得
        $campus_names = $this->mdlGetRoomQuery();

        $query = Schedule::query();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        $not_yet_transfer_classes = $query
            ->select(
                'schedules.schedule_id',
                'schedules.student_id',
                'schedules.campus_cd',
                // 校舎の名称
                'campus_names.room_name as campus_name',
                'schedules.target_date',
                'schedules.period_no',
                'schedules.course_cd',
                // コースの名称
                'mst_courses.name as course_name',
                'schedules.tutor_id',
                // 講師の名前
                'tutors.name as tutor_name',
                'schedules.subject_cd',
                // 科目の名称
                'mst_subjects.name as subject_name',
                'schedules.lesson_kind',
                // コードマスタの名称(授業区分)
                'mst_codes_31.name as lesson_kind_name',
                'schedules.absent_status',
                // コードマスタの名称(出欠ステータス)
                'mst_codes_35.name as absent_status_name',
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'campus_names.code');
            })
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, 'schedules.course_cd', '=', 'mst_courses.course_cd')
            // 講師名の取得
            ->sdLeftJoin(Tutor::class, 'schedules.tutor_id', '=', 'tutors.tutor_id')
            // 科目の取得
            ->sdLeftJoin(MstSubject::class, 'schedules.subject_cd', '=', 'mst_subjects.subject_cd')
            // 授業区分の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.lesson_kind', '=', 'mst_codes_31.code')
                    ->where('mst_codes_31.data_type', AppConst::CODE_MASTER_31);
            }, 'mst_codes_31')
            // 出欠ステータスの取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.absent_status', '=', 'mst_codes_35.code')
                    ->where('mst_codes_35.data_type', AppConst::CODE_MASTER_35);
            }, 'mst_codes_35')
            // 画面表示中生徒のデータに絞り込み
            ->where('schedules.student_id', '=', $sid)
            // 出欠ステータス＝未振替or振替中に該当するデータに絞り込み
            ->whereIn('schedules.absent_status', [AppConst::CODE_MASTER_35_3, AppConst::CODE_MASTER_35_4])
            ->orderBy('schedules.target_date', 'asc')
            ->orderBy('schedules.period_no', 'asc')
            ->get();

        return $not_yet_transfer_classes;
    }

    /**
     * イレギュラー授業情報を取得する
     *
     * @param integer $sid 生徒ID
     */
    private function getIrregularClass($sid)
    {
        // 現在日を取得 絞り込みで使用
        $today = Carbon::today();

        // 校舎名取得
        $campus_names = $this->mdlGetRoomQuery();

        $query = Schedule::query();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        $irregular_classes = $query
            ->select(
                'schedules.schedule_id',
                // モーダル選択時の閲覧ガード用にstudent_idを取得する
                'schedules.student_id',
                'class_members.student_id as class_student_id',
                'schedules.campus_cd',
                // 校舎の名称
                'campus_names.room_name as campus_name',
                'schedules.target_date',
                'schedules.period_no',
                'schedules.course_cd',
                // コースの名称
                'mst_courses.name as course_name',
                'schedules.tutor_id',
                // 講師の名前
                'tutors.name as tutor_name',
                'schedules.subject_cd',
                // 科目の名称
                'mst_subjects.name as subject_name',
                'schedules.lesson_kind',
                // コードマスタの名称(授業区分)
                'mst_codes_31.name as lesson_kind_name',
                'schedules.absent_status',
                // コードマスタの名称(出欠ステータス) 個別指導用
                'mst_codes_35.name as absent_status_name',
                // 受講生徒情報の出欠ステータス
                'class_members.absent_status as class_absent_status',
                // コードマスタの名称(出欠ステータス) 1対多用
                'mst_codes_35_class.name as class_absent_status_name',
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'campus_names.code');
            })
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, 'schedules.course_cd', '=', 'mst_courses.course_cd')
            // 講師名の取得
            ->sdLeftJoin(Tutor::class, 'schedules.tutor_id', '=', 'tutors.tutor_id')
            // 科目の取得
            ->sdLeftJoin(MstSubject::class, 'schedules.subject_cd', '=', 'mst_subjects.subject_cd')
            // 授業区分の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.lesson_kind', '=', 'mst_codes_31.code')
                    ->where('mst_codes_31.data_type', AppConst::CODE_MASTER_31);
            }, 'mst_codes_31')
            // 出欠ステータスの取得（個別指導用）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.absent_status', '=', 'mst_codes_35.code')
                    ->where('mst_codes_35.data_type', AppConst::CODE_MASTER_35);
            }, 'mst_codes_35')
            // 受講生徒情報とJOIN 回数パックの集団授業もイレギュラーとして表示するため
            ->sdLeftJoin(ClassMember::class, 'schedules.schedule_id', '=', 'class_members.schedule_id')
            // 出欠ステータスの取得（1対多用）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('class_members.absent_status', '=', 'mst_codes_35_class.code')
                    ->where('mst_codes_35_class.data_type', AppConst::CODE_MASTER_35);
            }, 'mst_codes_35_class')
            // 授業日がシステム日付より未来日のデータに絞り込み（当日を含まない）
            ->where('schedules.target_date', '>', $today)
            // 画面表示中生徒のデータに絞り込み
            ->where(function ($query) use ($sid) {
                $query->where('schedules.student_id', '=', $sid)
                    ->orWhere('class_members.student_id', '=', $sid);
            })
            // 1,2どちらかの条件を満たすデータを取得する
            ->where(function ($query) {
                // 1.下記a~dの条件を全て満たすデータ
                // a.コースコードよりコースマスタを参照し、コースマスタ->コース種別 = 面談、その他 以外
                // b.データ作成種別 = 一括 以外
                // c.授業区分 = 特別期間講習 以外
                // d.出欠ステータス = 振替済 以外
                $query->whereNotIn('mst_courses.course_kind', [AppConst::CODE_MASTER_42_3, AppConst::CODE_MASTER_42_4])
                    ->where('schedules.create_kind', '!=', AppConst::CODE_MASTER_32_0)
                    ->where('schedules.lesson_kind', '!=', AppConst::CODE_MASTER_31_2)
                    ->where('schedules.absent_status', '!=', AppConst::CODE_MASTER_35_5)

                    // 2.授業代講種別 = なし 以外のデータ
                    ->orWhere('schedules.substitute_kind', '!=', AppConst::CODE_MASTER_34_0);
            })
            ->orderBy('schedules.target_date', 'asc')
            ->orderBy('schedules.period_no', 'asc')
            ->get();

        return $irregular_classes;
    }

    /**
     * 受験校情報を取得する
     *
     * @param integer $sid 生徒ID
     */
    private function getEntranceExam($sid)
    {
        // 受験年度絞り込み用のデータを用意（システムマスタ「現年度」）
        $currentYear = MstSystem::select('value_num')
            ->where('key_id', AppConst::SYSTEM_KEY_ID_1)
            ->first();

        // クエリ作成
        $query = StudentEntranceExam::query();

        $entrance_exams = $query
            ->select(
                'student_entrance_exams.student_exam_id',
                'student_entrance_exams.student_id',
                'student_entrance_exams.school_cd',
                // 学校マスタの名称
                'mst_schools.name as school_name',
                'student_entrance_exams.department_name',
                'student_entrance_exams.priority_no',
                'student_entrance_exams.exam_year',
                'student_entrance_exams.exam_name',
                'student_entrance_exams.exam_date',
                'student_entrance_exams.result',
                // コードマスタの名称（合否）
                'mst_codes.name as result_name',
            )
            // 画面表示中生徒のデータに絞り込み
            ->where('student_entrance_exams.student_id', $sid)
            // 受験年度が現年度のデータに絞り込み
            ->where('student_entrance_exams.exam_year', $currentYear->value_num)
            // 学校名の取得
            ->sdLeftJoin(MstSchool::class, 'student_entrance_exams.school_cd', '=', 'mst_schools.school_cd')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('student_entrance_exams.result', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_52);
            })
            ->orderBy('student_entrance_exams.priority_no', 'asc')
            ->get();

        return $entrance_exams;
    }

    /**
     * 成績情報を取得する
     *
     * @param integer $sid 生徒ID
     */
    private function getGrade($sid)
    {
        // クエリ作成
        $query = Score::query();

        // 画面表示中生徒のデータに絞り込み
        $query->where('scores.student_id', $sid);

        // データを取得 FuncGradesTrait
        $scores = $this->getScoreList($query);
        $scores = $scores->get();

        return $scores;
    }

    /**
     * バッジ情報を取得する
     *
     * @param integer $sid 生徒ID
     */
    private function getBadge($sid)
    {
        // 校舎名取得
        $campus_names = $this->mdlGetRoomQuery();

        // クエリ作成
        $query = Badge::query();

        $badges = $query
            ->select(
                'badges.campus_cd',
                // 校舎の名称
                'campus_names.room_name as campus_name',
                'badges.reason',
                'badges.authorization_date',
                'badges.adm_id',
                // 管理者アカウントの名前
                'admin_users.name as admin_name',
            )
            // 画面表示中生徒のデータに絞り込み
            ->where('badges.student_id', $sid)
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('badges.campus_cd', '=', 'campus_names.code');
            })
            // 管理者名を取得
            ->sdLeftJoin(AdminUser::class, 'badges.adm_id', '=', 'admin_users.adm_id')
            ->orderBy('badges.authorization_date', 'desc')
            ->orderBy('badges.badge_type', 'asc')
            ->orderBy('badges.campus_cd', 'asc')
            ->get();

        return $badges;
    }

    //==========================
    // モーダル用のデータ取得
    //==========================

    /**
     * モーダル用 連絡記録を取得する
     *
     * @param integer $id 連絡記録ID
     */
    private function getModalRecord($id)
    {
        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        // クエリ作成
        $query = Record::query();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        $record = $query
            ->select(
                'records.record_id',
                'records.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'records.campus_cd',
                // 校舎名
                'campus_names.room_name as campus_name',
                'records.record_kind',
                // コードマスタの名称（記録種別）
                'mst_codes.name as kind_name',
                'records.adm_id',
                // 管理者の名前
                'admin_users.name as admin_name',
                'records.received_date',
                'records.received_time',
                'records.regist_time',
                'records.memo'
            )
            // 詳細ボタン押下時に指定したIDで絞り込み
            ->where('records.record_id', $id)
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('records.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'records.student_id', '=', 'students.student_id')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('records.record_kind', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_46);
            })
            // 管理者名を取得
            ->sdLeftJoin(AdminUser::class, 'records.adm_id', '=', 'admin_users.adm_id')
            // モーダル用データはfirstで取得
            ->first();

        // モーダル用にデータセット
        return [
            'received_date' => $record->received_date,
            'received_time' => $record->received_time->format('H:i'),
            'regist_time' => $record->regist_time,
            'admin_name' => $record->admin_name,
            'campus_name' => $record->campus_name,
            'student_name' => $record->student_name,
            'kind_name' => $record->kind_name,
            'memo' => $record->memo,
        ];
    }

    /**
     * モーダル用 授業情報を取得する
     *
     * @param integer $id スケジュールID
     */
    private function getModalSchedule($id)
    {
        // 校舎名取得
        $campus_names = $this->mdlGetRoomQuery();

        $query = Schedule::query();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        $schedule = $query
            ->select(
                'schedules.schedule_id',
                'schedules.campus_cd',
                // 校舎の名称
                'campus_names.room_name as room_name',
                'schedules.target_date',
                'schedules.period_no',
                'schedules.start_time',
                'schedules.end_time',
                'schedules.booth_cd',
                // ブースの名称
                'mst_booths.name as booth_name',
                'schedules.course_cd',
                // コースの名称
                'mst_courses.name as course_name',
                // コース種別 モーダル分岐に使用
                'mst_courses.course_kind',
                'schedules.student_id',
                // 生徒の名前
                'students.name as student_name',
                'schedules.tutor_id',
                // 講師の名前
                'org_tutors.name as tutor_name',
                'schedules.absent_tutor_id',
                // 欠席講師の名前
                'absent_tutors.name as absent_tutor_name',
                'schedules.subject_cd',
                // 科目の名称
                'mst_subjects.name as subject_name',
                'schedules.create_kind',
                // コードマスタの名称(データ作成種別）
                'mst_codes_32.name as create_kind_name',
                'schedules.lesson_kind',
                // コードマスタの名称(授業区分)
                'mst_codes_31.name as lesson_kind_name',
                'schedules.how_to_kind',
                // コードマスタの名称(通塾種別)
                'mst_codes_33.name as how_to_kind_name',
                'schedules.substitute_kind',
                // コードマスタの名称(代講種別)
                'mst_codes_34.name as substitute_kind_name',
                'schedules.absent_status',
                // コードマスタの名称(出欠ステータス)
                'mst_codes_35.name as absent_name',
                'schedules.adm_id',
                // 管理者の名前
                'admin_users.name as admin_name',
                'schedules.memo',
                // 振替授業情報
                'transfer_schedules.target_date as transfer_date',
                'transfer_schedules.period_no as transfer_period_no',
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'campus_names.code');
            })
            // ブース名の取得
            ->sdLeftJoin(MstBooth::class, 'schedules.booth_cd', '=', 'mst_booths.booth_cd')
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, 'schedules.course_cd', '=', 'mst_courses.course_cd')
            // 生徒名の取得
            ->sdLeftJoin(Student::class, 'schedules.student_id', '=', 'students.student_id')
            // 講師名取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('schedules.tutor_id', '=', 'org_tutors.tutor_id');
            }, 'org_tutors')
            // 欠席講師名取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('schedules.absent_tutor_id', '=', 'absent_tutors.tutor_id');
            }, 'absent_tutors')
            // 科目の取得
            ->sdLeftJoin(MstSubject::class, 'schedules.subject_cd', '=', 'mst_subjects.subject_cd')
            // データ作成種別の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.create_kind', '=', 'mst_codes_32.code')
                    ->where('mst_codes_32.data_type', AppConst::CODE_MASTER_32);
            }, 'mst_codes_32')
            // 授業区分の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.lesson_kind', '=', 'mst_codes_31.code')
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
            // 出欠ステータスの取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.absent_status', '=', 'mst_codes_35.code')
                    ->where('mst_codes_35.data_type', AppConst::CODE_MASTER_35);
            }, 'mst_codes_35')
            // 管理者名の取得
            ->sdLeftJoin(AdminUser::class, function ($join) {
                $join->on('schedules.adm_id', 'admin_users.adm_id');
            })
            // 振替情報の取得
            ->sdLeftJoin(Schedule::class, function ($join) {
                $join->on('schedules.transfer_class_id', '=', 'transfer_schedules.schedule_id');
            }, 'transfer_schedules')
            // 詳細ボタン押下時に指定したIDで絞り込み
            ->where('schedules.schedule_id', $id)
            ->first();

        // モーダル表示用
        $schedule['hurikae_name'] = "";
        // 振替の場合、授業区分に付加する文字列を設定
        if ($schedule['create_kind'] == AppConst::CODE_MASTER_32_2) {
            $schedule['hurikae_name'] = $schedule['create_kind_name'];
        }

        // 集団授業用に受講生徒情報を取得
        $class_student_names = $this->getClassMembers($id);

        return [
            'room_name' => $schedule->room_name,
            'booth_name' => $schedule->booth_name,
            'course_name' => $schedule->course_name,
            'course_kind' => $schedule->course_kind,
            'lesson_kind_name' => $schedule->lesson_kind_name,
            'target_date' => $schedule->target_date,
            'period_no' => $schedule->period_no,
            'start_time' => $schedule->start_time->format('H:i'),
            'end_time' => $schedule->end_time->format('H:i'),
            'tutor_name' => $schedule->tutor_name,
            'student_name' => $schedule->student_name,
            'subject_name' => $schedule->subject_name,
            'how_to_kind_name' => $schedule->how_to_kind_name,
            'substitute_kind_name' => $schedule->substitute_kind_name,
            'absent_tutor_name' => $schedule->absent_tutor_name,
            'absent_name' => $schedule->absent_name,
            'create_kind' => $schedule->create_kind,
            'transfer_date' => $schedule->transfer_date,
            'transfer_period_no' => $schedule->transfer_period_no,
            'admin_name' => $schedule->admin_name,
            'memo' => $schedule->memo,
            'hurikae_name' => $schedule->hurikae_name,
            'class_student_names' => $class_student_names,
        ];
    }

    /**
     * モーダル用 受験校情報を取得する
     *
     * @param integer $id 受験ID
     */
    private function getModalEntranceExam($id)
    {
        // クエリ作成
        $query = StudentEntranceExam::query();

        $entrance_exam = $query
            ->select(
                'student_entrance_exams.student_exam_id',
                'student_entrance_exams.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'student_entrance_exams.school_cd',
                // 学校マスタの名称
                'mst_schools.name as school_name',
                'student_entrance_exams.department_name',
                'student_entrance_exams.priority_no',
                'student_entrance_exams.exam_year',
                'student_entrance_exams.exam_name',
                'student_entrance_exams.exam_date',
                'student_entrance_exams.result',
                // コードマスタの名称（合否）
                'mst_codes.name as result_name',
                'student_entrance_exams.memo',
            )
            // 詳細ボタン押下時に指定したIDで絞り込み
            ->where('student_entrance_exams.student_exam_id', $id)
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'student_entrance_exams.student_id', '=', 'students.student_id')
            // 学校名の取得
            ->sdLeftJoin(MstSchool::class, 'student_entrance_exams.school_cd', '=', 'mst_schools.school_cd')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('student_entrance_exams.result', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_52);
            })
            ->first();

        return [
            'student_name' => $entrance_exam->student_name,
            'priority_no' => $entrance_exam->priority_no,
            'school_name' => $entrance_exam->school_name,
            'department_name' => $entrance_exam->department_name,
            'exam_year' => $entrance_exam->exam_year,
            'exam_name' => $entrance_exam->exam_name,
            'exam_date' => $entrance_exam->exam_date,
            'result_name' => $entrance_exam->result_name,
            'memo' => $entrance_exam->memo,
        ];
    }

    /**
     * モーダル用 成績情報を取得する
     *
     * @param integer $id 生徒成績ID
     */
    private function getModalScore($id)
    {
        // 生徒成績を取得 FuncGradesTrait
        $scores = $this->getScore($id);

        // 生徒成績詳細を取得
        $scoreDetails = $this->getScoreDetail($id);

        return [
            'exam_type' => $scores->exam_type,
            'regist_date' => $scores->regist_date,
            'student_name' => $scores->student_name,
            'exam_type_name' => $scores->exam_type_name,
            'practice_exam_name' => $scores->practice_exam_name,
            'regular_exam_name' => $scores->regular_exam_name,
            'term_name' => $scores->term_name,
            'exam_date' => $scores->exam_date,
            'student_comment' => $scores->student_comment,
            'scoreDetails' => $scoreDetails
        ];
    }
}
