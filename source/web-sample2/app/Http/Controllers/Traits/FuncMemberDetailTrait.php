<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Models\MstCampus;
use App\Models\MstGrade;
use App\Models\MstSchool;
use App\Models\MstCourse;
use App\Models\MstSubject;
use App\Models\MstSystem;
use App\Models\Student;
use App\Models\StudentCampus;
use App\Models\Record;
use App\Models\Tutor;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\StudentEntranceExam;
use App\Models\Score;
use App\Models\Badge;
use App\Models\CodeMaster;
use App\Http\Controllers\Traits\CtrlResponseTrait;
use App\Http\Controllers\Traits\FuncGradesTrait;
use App\Http\Controllers\Traits\FuncScheduleTrait;
use App\Http\Controllers\Traits\FuncRecordTrait;
use App\Http\Controllers\Traits\FuncDesireMngTrait;
use App\Http\Controllers\Traits\FuncBadgeTrait;
use App\Http\Controllers\Traits\FuncAgreementTrait;
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

    // 機能共通処理：スケジュール関連
    use FuncScheduleTrait;

    // 機能共通処理：連絡記録
    use FuncRecordTrait;

    // 機能共通処理：受験校管理
    use FuncDesireMngTrait;

    // 機能共通処理：バッジ付与管理
    use FuncBadgeTrait;

    // 機能共通処理：生徒情報
    use FuncAgreementTrait;

    /**
     * 生徒カルテを取得する（全体）
     *
     * @param integer $sid 生徒ID
     */
    private function getMemberDetail($sid)
    {
        // 生徒の基本情報を取得する
        $query = Student::query();

        // 通塾期間の月数取得のサブクエリ
        $enter_term_query = $this->mdlGetStudentEnterTermQuery();

        $student = $query
            ->select(
                'students.student_id',
                'students.name',
                'students.name_kana',
                'students.tel_stu',
                'students.tel_par',
                'students.email_stu',
                'students.email_par',
                'students.birth_date',
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
                'students.stu_status',
                // コードマスタの名称(会員ステータス)
                'mst_codes.name as status_name',
                'students.enter_date',
                'students.leave_date',
                // 通塾期間の月数
                'enter_term_query.enter_term',
                'students.lead_id',
                'students.storage_link',
                'students.memo',
            )
            // 通塾期間の月数の取得
            ->leftJoinSub($enter_term_query, 'enter_term_query', function ($join) {
                $join->on('students.student_id', '=', 'enter_term_query.student_id');
            })
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

        // 所属校舎が複数ある場合はカンマ区切りで表示する
        $campusList = [];
        foreach ($campuses as $campus) {
            array_push($campusList, $campus->campus_name);
        };
        $campus_names = implode(',', $campusList);

        // 連絡記録の取得
        $records = $this->getRecord($sid);
        // レギュラー授業情報の取得
        $regular_classes = $this->fncAgreGetRegularClass($sid);
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

        // 会員ステータスによって退会ボタンの押下を制御する（見込客・退会処理中・退会済は押下不可）
        $disabled = false;
        if ($student['stu_status'] == AppConst::CODE_MASTER_28_0 || $student['stu_status'] == AppConst::CODE_MASTER_28_4 || $student['stu_status'] == AppConst::CODE_MASTER_28_5) {
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
     * 連絡記録情報を取得する
     *
     * @param integer $sid 生徒ID
     */
    private function getRecord($sid)
    {
        // クエリ作成
        $query = Record::query();

        // 画面表示中生徒のデータに絞り込み
        $query->where('records.student_id', $sid);

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // 連絡記録情報表示用のquery作成 FuncRecordTrait
        // データを取得
        $records = $this->fncRecdGetRecordQuery($query)
            ->orderby('records.received_date', 'desc')
            ->orderby('records.received_time', 'desc')
            ->get();

        return $records;
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
                // 校舎の名称
                'campus_names.room_name as campus_name',
                'schedules.target_date',
                'schedules.period_no',
                // コースの名称
                'mst_courses.name as course_name',
                // 講師の名前
                'tutors.name as tutor_name',
                // 科目の名称
                'mst_subjects.name as subject_name',
                // コードマスタの名称(授業区分)
                'mst_codes_31.name as lesson_kind_name',
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
                // 校舎の名称
                'campus_names.room_name as campus_name',
                'schedules.target_date',
                'schedules.period_no',
                'schedules.create_kind',
                'schedules.substitute_kind',
                // コースの名称
                'mst_courses.name as course_name',
                // 講師の名前
                'tutors.name as tutor_name',
                // 科目の名称
                'mst_subjects.name as subject_name',
                // コードマスタの名称(授業区分)
                'mst_codes_31.name as lesson_kind_name',
                // コードマスタの名称(出欠ステータス) 個別指導用
                'mst_codes_35.name as absent_status_name',
                // コードマスタの名称(出欠ステータス) 1対多用
                'mst_codes_35_class.name as class_absent_status_name',
                // コードマスタの名称(データ作成区分)
                'mst_codes_32.name as create_kind_name',
                // コードマスタの名称(代講種別)
                'mst_codes_34.name as substitute_kind_name',
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
            // データ作成区分の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.create_kind', '=', 'mst_codes_32.code')
                    ->where('mst_codes_32.data_type', AppConst::CODE_MASTER_32);
            }, 'mst_codes_32')
            // 代講種別の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('schedules.substitute_kind', '=', 'mst_codes_34.code')
                    ->where('mst_codes_34.data_type', AppConst::CODE_MASTER_34);
            }, 'mst_codes_34')
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
                // d.出欠ステータス = 振替済、リセット済 以外
                $query->whereNotIn('mst_courses.course_kind', [AppConst::CODE_MASTER_42_3, AppConst::CODE_MASTER_42_4])
                    ->where('schedules.create_kind', '!=', AppConst::CODE_MASTER_32_0)
                    ->where('schedules.lesson_kind', '!=', AppConst::CODE_MASTER_31_2)
                    ->whereNotIn('schedules.absent_status', [AppConst::CODE_MASTER_35_5, AppConst::CODE_MASTER_35_7])

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
            ->whereNotNull('value_num')
            ->firstOrFail();

        // クエリ作成
        $query = StudentEntranceExam::query();

        // 画面表示中生徒のデータに絞り込み
        $query->where('student_entrance_exams.student_id', $sid);

        // 教室管理者の場合、自分の校舎の生徒のみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithSid());

        // 受験校情報表示用のquery作成 FuncDegireMngTrait
        // データを取得
        $entrance_exams = $this->fncDsirGetEntranceExamQuery($query)
            // 受験年度が現年度のデータに絞り込み
            ->where('student_entrance_exams.exam_year', $currentYear->value_num)
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

        // 教室管理者の場合、自分の校舎コードの生徒のみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithSid());

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
        // クエリ作成
        $query = Badge::query();

        // 画面表示中生徒のデータに絞り込み
        $query->where('badges.student_id', $sid);

        // 教室管理者の場合も全校舎表示対象とする
        // 校舎コードによるガードは無し

        // バッジ付与情報表示用のquery作成 FuncBadgeTrait
        // データを取得
        $badges = $this->fncBageGetBadgeQuery($query)
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
        // クエリを作成
        $query = Record::query();

        // 連絡記録情報表示用のquery作成 FuncRecordTrait
        // データを取得
        $record = $this->fncRecdGetRecordQuery($query)
            // 詳細ボタン押下時に指定したIDで絞り込み
            ->where('records.record_id', $id)
            ->firstOrFail();

        // モーダル用にデータセット
        return $record;
    }

    /**
     * モーダル用 授業情報を取得する
     *
     * @param integer $id スケジュールID
     */
    private function getModalSchedule($id)
    {

        $query = Schedule::query();

        // スケジュール情報表示用のquery作成 FuncCalendarTrait
        // データを取得
        $schedule = $this->fncScheGetScheduleQuery($query)
            // 詳細ボタン押下時に指定したIDで絞り込み
            ->where('schedules.schedule_id', $id)
            ->firstOrFail();

        // モーダル表示用
        $schedule['hurikae_name'] = "";
        // 振替の場合、授業区分に付加する文字列を設定
        if ($schedule['create_kind'] == AppConst::CODE_MASTER_32_2) {
            $schedule['hurikae_name'] = $schedule['create_kind_name'];
        }

        // 集団授業用に受講生徒情報を取得
        $schedule['class_student_names'] = $this->fncScheGetClassMembers($id);

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
        unset($schedule['report_id']);

        // モーダル用にデータセット
        return $schedule;
    }

    /**
     * モーダル用 受験校情報を取得する
     *
     * @param integer $id 受験ID
     */
    private function getModalEntranceExam($id)
    {
        // クエリを作成
        $query = StudentEntranceExam::query();

        // 受験校情報表示用のquery作成 FuncDegireMngTrait
        // データを取得
        $entranceExam = $this->fncDsirGetEntranceExamQuery($query)
            // 詳細ボタン押下時に指定したIDで絞り込み
            ->where('student_entrance_exams.student_exam_id', $id)
            ->firstOrFail();

        // モーダル用にデータセット
        return $entranceExam;
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
