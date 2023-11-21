<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Auth;
use App\Models\CodeMaster;
use App\Models\MstCampus;
use App\Models\MstBooth;
use App\Models\MstCourse;
use App\Models\MstTimetable;
use App\Models\MstSubject;
use App\Models\MstGradeSubject;
use App\Models\MstText;
use App\Models\MstUnitCategory;
use App\Models\MstUnit;
use App\Models\Student;
use App\Models\StudentCampus;
use App\Models\Tutor;
use App\Models\TutorCampus;
use App\Models\AdminUser;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\Account;
use App\Models\MstGrade;
use App\Models\YearlySchedule;
use App\Models\MstSystem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * モデル - コントローラ共通処理
 */
trait CtrlModelTrait
{

    //==========================
    // 関数名を区別するために
    // mdl(モデル)を先頭につける
    //==========================

    //------------------------------
    // プルダウン向けリストの作成
    //------------------------------

    /**
     * 汎用マスタからプルダウンメニューのリストを取得
     * codeclsを指定する
     *
     * @param string $codecls
     * @return array
     */
    //protected function mdlMenuFromExtGenericMaster($codecls)
    //{
    //    return  ExtGenericMaster::select('code', 'name1 as value')
    //        ->where('codecls', $codecls)
    //        ->orderby('disp_order')
    //        ->orderby('code')
    //        ->get()
    //        ->keyBy('code');
    //}

    /**
     * コードマスタからプルダウンメニューのリストを取得
     * data_typeを指定する
     *
     * @param integer $dataType
     * @param array $subCode サブコード（配列で指定）省略可
     * @return array
     */
    protected function mdlMenuFromCodeMaster($dataType, $subCodes = null)
    {

        $query = CodeMaster::query();

        // サブコードが指定された場合絞り込み
        $query->when($subCodes, function ($query) use ($subCodes) {
            return $query->whereIn('sub_code', $subCodes);
        });

        // プルダウンリストを取得する
        return $query->select('code', 'name as value')
            ->where('data_type', $dataType)
            ->orderby('order_code')
            ->get()
            ->keyBy('code');
    }

    /**
     * 講師プルダウンメニューのリストを取得
     * 管理者向け（教室管理者の場合は自分の校舎のみ）
     * 教室管理者以外は、指定されたcampusCdで検索
     *
     * @param string $campusCd 校舎コード 指定なしの場合null
     * @return array
     */
    protected function mdlGetTutorList($campusCd = null)
    {
        // 講師情報の検索
        $query = Tutor::query();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、強制的に校舎コードで絞り込み
            $account = Auth::user();
            $this->mdlWhereTidByRoomQuery($query, Tutor::class, $account->campus_cd);
        } else {
            if (isset($campusCd)) {
                // 本部管理者で指定ありの場合、指定された校舎コードで絞り込み
                $this->mdlWhereTidByRoomQuery($query, Tutor::class, $campusCd);
            }
        }
        // 退職講師を除外
        $query->where('tutor_status', '<>', AppConst::CODE_MASTER_29_3);

        // プルダウンリストを取得する
        return $query->select('tutor_id as id', 'name as value', 'name_kana')
            ->orderby('name_kana')
            ->get()
            ->keyBy('id');
    }

    /**
     * 生徒プルダウンメニューのリストを取得
     * 管理者向け（教室管理者の場合は自分の校舎のみ）
     * 教室管理者以外は、指定されたcampusCdで検索
     *
     * @param string $campusCd 校舎コード 指定なしの場合null
     * @return array
     */
    protected function mdlGetStudentList($campusCd = null)
    {
        // 生徒情報の検索
        $query = Student::query();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、強制的に校舎コードで絞り込み
            $account = Auth::user();
            $this->mdlWhereSidByRoomQuery($query, Student::class, $account->campus_cd);
        } else {
            if (isset($campusCd)) {
                // 本部管理者で指定ありの場合、指定された校舎コードで絞り込み
                $this->mdlWhereSidByRoomQuery($query, Student::class, $campusCd);
            }
        }
        // 退会会員を除外
        $query->where('stu_status', '<>', AppConst::CODE_MASTER_28_5);

        // プルダウンリストを取得する
        return $query->select('student_id as id', 'name as value', 'name_kana')
            ->orderby('name_kana')
            ->get()
            ->keyBy('id');
    }

    /**
     * 生徒プルダウンメニューのリストを取得（お知らせ登録用・生徒ID付き）
     * 管理者向け（教室管理者の場合は自分の校舎のみ）
     * 教室管理者以外は、指定されたcampusCdで検索
     *
     * @param string $campusCd 校舎コード 指定なしの場合null
     * @return array
     */
    protected function mdlGetStudentListWithSid($campusCd = null)
    {
        // 生徒情報の検索
        $query = Student::query();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、強制的に校舎コードで絞り込み
            $account = Auth::user();
            $this->mdlWhereSidByRoomQuery($query, Student::class, $account->campus_cd);
        } else {
            if (isset($campusCd)) {
                // 本部管理者で指定ありの場合、指定された校舎コードで絞り込み
                $this->mdlWhereSidByRoomQuery($query, Student::class, $campusCd);
            }
        }
        // 退会会員を除外
        $query->where('stu_status', '<>', AppConst::CODE_MASTER_28_5);

        // プルダウンリストを取得する
        return $query->select(
            'student_id as id',
            DB::raw('CONCAT(student_id, "：", name) AS value'),
            'name_kana'
        )
            ->orderby('name_kana')
            ->get()
            ->keyBy('id');
    }

    /**
     * 生徒プルダウンメニューのリストを取得
     * 講師向け
     *
     * @param string $campusCd 校舎コード 指定なしの場合null
     * @param int $tutorId 講師ID
     * @param string $excludeCampusCd 除外する校舎コード(削除予定)
     * @return array
     */
    //protected function mdlGetStudentListForT($campusCd, $tid, $excludeCampusCd = null)
    protected function mdlGetStudentListForT($campusCd, $tutorId)
    {
        $query = Student::query();

        $this->mdlWhereSidByRoomQueryForT($query, Student::class, $tutorId, $campusCd);

        // 退会会員を除外
        $query->where('stu_status', '<>', AppConst::CODE_MASTER_28_5);

        // プルダウンリストを取得する
        return $query->select('student_id as id', 'name as value', 'name_kana')
            ->distinct()
            ->orderby('name_kana')
            ->get()
            ->keyBy('id');
    }

    /**
     * 事務局アカウントプルダウンメニューのリストを取得
     * 管理者向け（教室管理者の場合は自分の校舎のみ）
     *
     * @return array
     */
    protected function mdlGetOfficeList()
    {
        $query = AdminUser::query();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、校舎コードで絞る
            $account = Auth::user();
            $query->where('campus_cd', $account->campus_cd);
        }
        // アカウントテーブルとJOIN（削除管理者アカウント非表示対応）
        $query->sdJoin(Account::class, function ($join) {
            $join->on('admin_users.adm_id', '=', 'accounts.account_id')
                ->where('account.account_type', '=', AppConst::CODE_MASTER_7_3);
        });

        // プルダウンリストを取得する
        return $query->select('adm_id', 'name as value')
            ->orderby('adm_id')
            ->get()
            ->keyBy('adm_id');
    }

    /**
     * 校舎プルダウンメニューのリストを取得
     * 権限によってメニューが違う
     *
     * @param boolean $honbu 本部を表示するかどうか
     * @return array
     */
    protected function mdlGetRoomList($honbu = true)
    {
        // 校舎マスタより校舎情報を取得
        $query = MstCampus::query();
        $query->select('mst_campuses.campus_cd as code', 'name as value', 'disp_order')
            // 非表示フラグの条件を付加
            ->where('is_hidden', AppConst::CODE_MASTER_11_1);

        // ログインユーザ
        $account = Auth::user();

        // 権限によって見れるリストを変更する
        if (AuthEx::isRoomAdmin()) {
            //-------------
            // 教室管理者
            //-------------

            // 教室管理者の場合、自分の管理教室のみ絞り込み
            // なのでここでは本部は絶対に追加されない
            $query->where('campus_cd', $account->campus_cd);
        } else {

            if (AuthEx::isStudent()) {
                //-------------
                // 生徒の場合
                //-------------

                // 自分の在籍している校舎のみ対応する（生徒所属情報とJOIN）
                $query->sdJoin(StudentCampus::class, function ($join) use ($account) {
                    // campus_cdでjoin
                    $join->on('student_campuses.campus_cd', '=', 'mst_campuses.campus_cd')
                        // 自分のものだけ
                        ->where('student_id', $account->account_id);
                });
            } else if (AuthEx::isTutor()) {
                //-------------
                // 講師の場合
                //-------------

                // 自分の在籍している校舎のみ対応する（講師所属情報とJOIN）
                $query->sdJoin(TutorCampus::class, function ($join) use ($account) {
                    // campus_cdでjoin
                    $join->on('tutor_campuses.campus_cd', '=', 'mst_campuses.campus_cd')
                        // 自分のものだけ
                        ->where('tutor_id', $account->account_id);
                });
            }

            // 本部を追加するかどうか
            if ($honbu) {
                // コードマスタより「本部」名称取得
                $queryHonbu = CodeMaster::select('gen_item1', 'name as value', 'order_code as disp_order')
                    ->where('data_type', AppConst::CODE_MASTER_6);

                // UNIONで校舎リストに加える
                $query->union($queryHonbu);
            }
        }

        // 校舎リストを取得
        $rooms = $query->orderBy('disp_order')
            ->get()->keyBy('code');

        return $rooms;
    }

    /**
     * ブースプルダウンメニューのリストを取得
     * 管理者向け（教室管理者の場合は自分の校舎のみ）
     *
     * @param string $campusCd 校舎コード 指定なしの場合null
     * @param int $usageKind 用途種別 省略可
     * @return array
     */
    protected function mdlGetBoothList($campusCd, $usageKind = null)
    {
        $query = MstBooth::query();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、校舎コードで絞る
            $account = Auth::user();
            $query->where('campus_cd', $account->campus_cd);
        }
        // 校舎が指定された場合絞り込み
        $query->when($campusCd, function ($query) use ($campusCd) {
            return $query->where('campus_cd', $campusCd);
        });
        // 用途種別が指定された場合絞り込み
        $query->when($usageKind, function ($query) use ($usageKind) {
            return $query->where('usage_kind', $usageKind);
        });

        // プルダウンリストを取得する
        return $query->select('booth_cd as code', 'name as value')
            ->orderby('campus_cd')->orderby('disp_order')
            ->get()
            ->keyBy('code');
    }

    /**
     * コースプルダウンメニューのリストを取得
     * 管理者向け
     *
     * @param int $courseKind コース種別 省略可
     * @param int $exceptCourseKind 除外するコース種別 省略可
     * @return array
     */
    protected function mdlGetCourseList($courseKind = null, $exceptCourseKind = null)
    {
        $query = MstCourse::query();

        // コース種別が指定された場合絞り込み
        $query->when($courseKind, function ($query) use ($courseKind) {
            return $query->where('course_kind', $courseKind);
        });
        // 除外するコース種別が指定された場合絞り込み
        $query->when($exceptCourseKind, function ($query) use ($exceptCourseKind) {
            return $query->where('course_kind', '<>', $exceptCourseKind);
        });

        // プルダウンリストを取得する
        return $query->select('course_cd as code', 'name as value')
            ->orderby('course_cd')
            ->get()
            ->keyBy('code');
    }

    /**
     * 時限プルダウンメニューのリストを取得（校舎・時間割区分指定）
     *
     * @param string $campusCd 校舎コード
     * @param int $timetableKind 時間割区分
     * @return array
     */
    protected function mdlGetPeriodListByKind($campusCd, $timetableKind)
    {
        $query = MstTimetable::query();
        $account = Auth::user();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('campus_cd', $account->campus_cd);
        } else if (AuthEx::isTutor()) {
            // 講師の場合、所属校舎で絞る（ガード）
            $this->mdlWhereRoomByTidQuery($query, MstTimetable::class, $account->account_id);
        } else if (AuthEx::isStudent()) {
            // 生徒の場合、所属校舎で絞る（ガード）
            $this->mdlWhereRoomBySidQuery($query, MstTimetable::class, $account->account_id);
        }

        // 校舎は指定されている前提として絞り込み
        $query->where('campus_cd', $campusCd);
        // 時間割区分は指定されている前提として絞り込み
        $query->where('timetable_kind', $timetableKind);

        // プルダウンリストを取得する
        return $query->select(
            //'timetable_id as code',
            'period_no as code',
            DB::raw('CONCAT(period_no, "限") AS value')
        )
            ->orderby('campus_cd')->orderby('period_no')
            ->get()
            ->keyBy('code');
    }

    /**
     * 時限プルダウンメニューのリストを取得（校舎・日付指定）
     *
     * @param string $campusCd 校舎コード
     * @param date $date 日付
     * @return array
     */
    protected function mdlGetPeriodListByDate($campusCd, $date)
    {
        $query = MstTimetable::query();
        $account = Auth::user();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、所属校舎で絞る（ガード）
            $query->where('mst_timetables.campus_cd', $account->campus_cd);
        } else if (AuthEx::isTutor()) {
            // 講師の場合、所属校舎で絞る（ガード）
            $this->mdlWhereRoomByTidQuery($query, MstTimetable::class, $account->account_id);
        } else if (AuthEx::isStudent()) {
            // 生徒の場合、所属校舎で絞る（ガード）
            $this->mdlWhereRoomBySidQuery($query, MstTimetable::class, $account->account_id);
        }
        // 年間予定情報とJOIN
        $query->sdJoin(YearlySchedule::class, function ($join) use ($date) {
            $join->on('mst_timetables.campus_cd', 'yearly_schedules.campus_cd')
                ->where('yearly_schedules.lesson_date', $date);
        })
            // 期間区分
            ->sdJoin(CodeMaster::class, function ($join) {
                $join->on('yearly_schedules.date_kind', '=', 'mst_codes.code')
                    ->on('mst_timetables.timetable_kind', '=', 'mst_codes.sub_code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_38);
            })
            // 校舎は指定されている前提として絞り込み
            ->where('mst_timetables.campus_cd', $campusCd);

        // プルダウンリストを取得する
        return $query->select(
            //'timetable_id as code',
            'period_no as code',
            DB::raw('CONCAT(period_no, "限") AS value')
        )
            ->orderby('mst_timetables.campus_cd')->orderby('period_no')
            ->get()
            ->keyBy('code');
    }

    /**
     * 時限プルダウンメニューのリストを取得（講師ID・時間割区分指定）
     *
     * @param string $tutorId 講師ID
     * @param int $timetableKind 時間割区分
     * @return array
     */
    protected function mdlGetPeriodListForTutor($tutorId, $timetableKind)
    {
        $query = MstTimetable::query();

        // プルダウンリストを取得する
        return $query->select(
            'period_no as code',
            DB::raw('CONCAT(period_no, "限") AS value')
        )
            // 講師所属情報とJOIN
            ->sdJoin(TutorCampus::class, function ($join) use ($tutorId) {
                $join->on('mst_timetables.campus_cd', '=', 'tutor_campuses.campus_cd')
                    ->where('tutor_campuses.tutor_id', $tutorId);
            })
            ->where('timetable_kind', $timetableKind)
            ->orderby('period_no')
            ->distinct()
            ->get()
            ->keyBy('code');
    }

    /**
     * 授業科目プルダウンメニューのリストを取得
     *
     * @param int $courseKind コース種別 省略可
     * @return array
     */
    protected function mdlGetSubjectList()
    {
        $query = MstSubject::query();

        // プルダウンリストを取得する
        return $query->select('subject_cd as code', 'name as value')
            ->orderby('subject_cd')
            ->get()
            ->keyBy('code');
    }

    /**
     * 成績科目プルダウンメニューのリストを取得
     *
     * @param int $schoolKind 学校区分 省略可
     * @return array
     */
    protected function mdlGetGradeSubjectList($schoolKind = null)
    {
        $query = MstGradeSubject::query();

        // 学校区分が指定された場合絞り込み
        $query->when($schoolKind, function ($query) use ($schoolKind) {
            return $query->where('school_kind', $schoolKind);
        });

        // プルダウンリストを取得する
        return $query->select('g_subject_cd as code', 'name as value')
            ->orderby('g_subject_cd')
            ->get()
            ->keyBy('code');
    }

    /**
     * 抽出したスケジュールより日時のプルダウンメニューのリストを取得
     *
     * @param array $lessons schedulesよりget
     * @return array プルダウンメニュー用日時 Y/m/d n限
     */
    protected function mdlGetScheduleMasterList($lessons)
    {
        // プルダウンメニューを作る
        $scheduleMasterValue = [];
        $scheduleMasterKeys = [];
        if (count($lessons) > 0) {
            foreach ($lessons as $lesson) {
                //$lesson['target_datetime'] = $lesson['target_date']->format('Y/m/d') . " " . $lesson['period'] . "限";
                $schedule = [
                    'id' => $lesson['schedule_id'],
                    'value' => $lesson['target_date']->format('Y/m/d') . " " . $lesson['period_no'] . "限"
                ];
                $schedule = (object) $schedule;
                array_push($scheduleMasterKeys, $lesson['schedule_id']);
                array_push($scheduleMasterValue, $schedule);
            }
        }

        $res = array_combine($scheduleMasterKeys, $scheduleMasterValue);

        return $res;
    }

    /**
     * スケジュールIDをもとにスケジュールの詳細を取得する
     * 権限によって制御をかける
     * getDataSelectで使用される想定
     * 校舎名と生徒名を返却する。機能のみではなかったのでここに定義
     *
     * @param int $schedule_id スケジュールID
     */
    protected function mdlGetScheduleDtl($schedule_id)
    {

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // $requestからidを取得し、検索結果を返却する。idはスケジュールID
        $query = Schedule::query();

        if (AuthEx::isRoomAdmin()) {
            // 教室管理者
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        } else if (AuthEx::isTutor()) {
            // 講師は無し(使用しない)
        } else if (AuthEx::isStudent()) {
            // 生徒は無し(使用しない)
            return;
        }

        $lesson = $query
            ->select(
                'room_name',
                'name as '
            )
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'room_names.code');
            })
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('students.student_id', '=', 'schedules.student_id');
            })
            ->where('schedules.schedule_id', '=', $schedule_id)
            ->firstOrFail();

        return $lesson;
    }

    /**
     * 担当生徒リスト（配列）を取得
     * 講師向け
     * selectのIN句に指定する想定
     *
     * @param int $tutorId 講師ID
     * @return array
     */
    protected function mdlGetStudentArrayForT($tutorId = null)
    {
        $query = Student::query();

        $this->mdlWhereSidByRoomQueryForT($query, Student::class, $tutorId);

        // 退会会員を除外
        $query->where('stu_status', '<>', AppConst::CODE_MASTER_28_5);

        // 生徒リストを取得する
        $students = $query->select('student_id')
            ->distinct()
            ->orderby('student_id')
            ->get();

        // 配列に格納
        $arrStudents = [];
        foreach ($students as $student) {
            array_push($arrStudents, $student->student_id);
        }

        return $arrStudents;
    }

    /**
     * 学年プルダウンメニューのリストを取得
     * 管理者向け
     *
     * @param int $ schoolKind 学校区分 省略可
     * @return array
     */
    protected function mdlGetGradeList($schoolKind = null)
    {
        $query = MstGrade::query();

        // 学校区分が指定された場合絞り込み
        $query->when($schoolKind, function ($query) use ($schoolKind) {
            return $query->where('school_kind', $schoolKind);
        });

        // プルダウンリストを取得する
        return $query->select('grade_cd as code', 'name as value')
            ->orderby('grade_cd')
            ->get()
            ->keyBy('code');
    }

    /**
     * 授業教材プルダウンメニューのリストを取得
     *
     * @param string $lSubjectCd 授業科目コード
     * @param int $gradeCd 学年コード
     * @param string $tSubjectCd 教材科目コード
     * @return array
     */
    protected function mdlGetTextList($lSubjectCd = null, $gradeCd = null, $tSubjectCd = null)
    {
        $query = MstText::query();

        // 授業科目コードが指定された場合絞り込み
        $query->when($lSubjectCd, function ($query) use ($lSubjectCd) {
            return $query->where('l_subject_cd', $lSubjectCd);
        });

        // 学年コードが指定された場合絞り込み
        $query->when($gradeCd, function ($query) use ($gradeCd) {
            return $query->where('grade_cd', $gradeCd);
        });

        // 教材科目コードが指定された場合絞り込み
        $query->when($tSubjectCd, function ($query) use ($tSubjectCd) {
            return $query->where('t_subject_cd', $tSubjectCd);
        });

        // プルダウンリストを取得する
        return $query->select('text_cd as code', 'name as value')
            ->orderby('text_cd')
            ->get()
            ->keyBy('code');
    }

    /**
     * 授業単元分類プルダウンメニューのリストを取得
     *
     * @param int $gradeCd 学年コード
     * @param string $tSubjectCd 教材科目コード
     * @return array
     */
    protected function mdlGetUnitCategoryList($gradeCd = null, $tSubjectCd = null)
    {
        $query = MstUnitCategory::query();

        // 学年コードが指定された場合絞り込み
        $query->when($gradeCd, function ($query) use ($gradeCd) {
            return $query->where('grade_cd', $gradeCd);
        });

        // 教材科目コードが指定された場合絞り込み
        $query->when($tSubjectCd, function ($query) use ($tSubjectCd) {
            return $query->where('t_subject_cd', $tSubjectCd);
        });

        // プルダウンリストを取得する
        return $query->select('unit_category_cd as code', 'name as value')
            ->orderby('unit_category_cd')
            ->get()
            ->keyBy('code');
    }

    /**
     * 授業単元プルダウンメニューのリストを取得
     *
     * @param string $categoryCd 単元分類コード 省略可
     * @return array
     */
    protected function mdlGetUnitList($categoryCd = null)
    {
        $query = MstUnit::query();

        // 単元分類コードが指定された場合絞り込み
        $query->when($categoryCd, function ($query) use ($categoryCd) {
            return $query->where('unit_category_cd', $categoryCd);
        });

        // プルダウンリストを取得する
        return $query->select('unit_cd as code', 'name as value')
            ->orderby('unit_cd')
            ->get()
            ->keyBy('code');
    }

    /**
     * 登録画面プルダウン用データフォーマット
     * name を 「コード (名称)」 の形式にする
     *
     * @param  $collection リストデータ
     * @param  int $digit コード0埋め桁数
     * @return フォーマット後リストデータ
     */
    protected function mdlFormatInputList($collection, int $digit)
    {
        $lists = $collection->map(function ($item, $key) use ($digit) {
            return [
                'code' => $item['code'],
                'value' => str_pad($item['code'], $digit, '0', STR_PAD_LEFT) . ' (' . $item['value'] . ')'
            ];
        });

        return $lists;
    }

    /**
     * 特別期間コードデータフォーマット
     * コードマスタ期間区分コードを「01」の形式にする
     *
     * @param  $collection リストデータ
     * @param  int $digit コード0埋め桁数
     * @return フォーマット後リストデータ
     */
    protected function mdlFormatSeasonCd()
    {
        // 「特別期間コード」は、年＋期間区分のコードを生成し格納する。(例：202301)

        // 年は、システムマスタの「現年度」から取得する。
        $currentYear = MstSystem::select('value_num')
            ->where('key_id', AppConst::SYSTEM_KEY_ID_1)
            ->first();

        // 期間区分を取得する（サブコード1のみ：春期1,夏期2,冬期3）
        $termList = CodeMaster::select('code')
            ->where('data_type', AppConst::CODE_MASTER_38)
            ->where('sub_code', AppConst::CODE_MASTER_38_SUB_1)
            ->get();

        // 現年度分 特別期間コード生成 期間区分コードを2桁で0埋め
        $seasonCodes = [];
        foreach ($termList as $term) {
            $seasonCodes[] = $currentYear->value_num . str_pad($term->code, 2, '0', STR_PAD_LEFT);
        }

        // 翌年度分 特別期間コード生成 春期のみ
        $seasonCodes[] = $currentYear->value_num + 1 . str_pad($termList[0]->code, 2, '0', STR_PAD_LEFT);

        return $seasonCodes;
    }

    //------------------------------
    // 名称取得（共通で使用されるもの）
    //------------------------------

    /**
     * 校舎名の取得
     *
     * @param string $campusCd 校舎コード
     * @return string
     */
    protected function mdlGetRoomName($campusCd)
    {
        // 校舎名を取得
        $query = MstCampus::query();
        $campus = $query
            ->select('name')
            ->where('campus_cd', $campusCd)
            ->firstOrFail();

        return $campus->name;
    }

    /**
     * 生徒名の取得
     *
     * @param int $studentId 生徒Id
     * @return string
     */
    protected function mdlGetStudentName($studentId)
    {
        // 生徒名を取得する
        $query = Student::query();
        $student = $query
            ->select(
                'name'
            )
            ->where('student_id', '=', $studentId)
            ->firstOrFail();

        return $student->name;
    }

    /**
     * 講師名の取得
     *
     * @param int $tutorId 講師ID
     * @return string
     */
    protected function mdlGetTeacherName($tutorId)
    {
        // 講師名を取得する
        $query = Tutor::query();
        $tutor = $query
            ->select(
                'name'
            )
            ->where('tutor_id', '=', $tutorId)
            ->firstOrFail();

        return $tutor->name;
    }

    //------------------------------
    // メールアドレス取得（共通で使用されるもの）
    //------------------------------

    /**
     * アカウント情報からメールアドレスの取得
     *
     * @param string $accountId アカウントID
     * @param string $accountType アカウント種別
     * @return string メールアドレス
     */
    protected function mdlGetAccountMail($accountId, $accountType) {
        $account = Account::select('email')
            ->where('account_id', $accountId)
            ->where('account_type', $accountType)
            ->firstOrFail();

        return $account->email;
    }

    /**
     * 校舎マスタからメールアドレスの取得
     *
     * @param string $campusCd 校舎コード
     * @return string メールアドレス
     */
    protected function mdlGetCampusMail($campusCd)
    {
        $campus = MstCampus::select('email_campus')
            ->where('campus_cd', $campusCd)
            ->firstOrFail();

        return $campus->email_campus;
    }

    //------------------------------
    // join向けリストの作成
    //------------------------------

    /**
     * 校舎のJOINクエリを取得
     * 権限共通。leftJoinSubされる想定
     *
     * @return array
     */
    protected function mdlGetRoomQuery()
    {
        // 校舎一覧を取得
        $query = MstCampus::query();
        $query->select('campus_cd as code', 'name as room_name',  'short_name as room_name_symbol', 'disp_order')
            // 非表示フラグの条件を付加
            ->where('is_hidden', AppConst::CODE_MASTER_11_1);

        // コードマスタより「本部」名称取得
        $queryHonbu = CodeMaster::select('gen_item1', 'name as room_name', 'name as room_name_symbol', 'order_code as disp_order')
            ->where('data_type', AppConst::CODE_MASTER_6);
        $query->union($queryHonbu);

        return $query;
    }

    //------------------------------
    // whereの条件
    //------------------------------

    /**
     * 教室管理者向け 自分の校舎で生徒IDを絞り込む
     * whereに指定する条件を取得する
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $model sidを絞るテーブルのモデルクラス
     */
    protected function mdlWhereSidForRoomAdminQuery($query, $model)
    {
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、強制的に校舎コードで検索する
            $account = Auth::user();
            $this->mdlWhereSidByRoomQuery($query, $model, $account->campus_cd);
        }
    }

    /**
     * 指定された校舎コードの生徒IDのみを絞り込む
     * 生徒所属情報を検索
     * whereに指定する条件を取得する
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $model 生徒IDを絞るテーブルのモデルクラス
     * @param string $campusCd 校舎コード
     */
    protected function mdlWhereSidByRoomQuery($query, $model, $campusCd)
    {

        // 生徒所属情報に存在するかチェックする。existsを使用した
        $query->whereExists(function ($query) use ($model, $campusCd) {

            // 対象テーブル(モデルから取得)
            $modelObj = new $model();

            // テーブル名取得
            $table = $modelObj->getTable();

            // 生徒所属情報テーブル
            $studentCampus = (new StudentCampus)->getTable();

            // 1件存在するかチェック
            $query->select(DB::raw(1))
                ->from($studentCampus)
                // 対象テーブルと生徒所属情報のstudent_idを連結
                ->whereRaw($table . '.student_id = ' . $studentCampus . '.student_id')
                // 指定された校舎コード
                ->where($studentCampus . '.campus_cd', $campusCd)
                // delete_dt条件の追加
                ->whereNull($studentCampus . '.deleted_at');
        });
    }

    /**
     * 指定された校舎コードで講師の受け持ちの生徒IDを絞り込む
     * whereに指定する条件を取得する
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $model 生徒IDを絞るテーブルのモデルクラス
     * @param int $tutorId 講師ID（講師向け画面からの場合null許可）
     * @param string $campusCd 校舎コード
     */
    protected function mdlWhereSidByRoomQueryForT($query, $model, $tutorId, $campusCd = null)
    {

        // ログインユーザ情報取得
        $account = Auth::user();

        // 講師の場合はアカウントIDをセットする
        if (AuthEx::isTutor()) {
            $tutorId = $account->account_id;
        }

        // スケジュール情報に存在するかチェックする。existsを使用した
        $query->whereExists(function ($query) use ($model, $campusCd, $tutorId) {

            // 受け持ち判定期間（当日日付の1ヶ月前月初から1ヶ月後月末）
            //$startDate = Carbon::parse('- 1 month')->startOfMonth()->format('y-m-d');
            //$endDate = Carbon::parse('+ 1 month')->endOfMonth()->format('y-m-d');
            // 受け持ち判定期間（当日日付の30日前から30日後）
            $startDate = Carbon::parse('- 30 day')->format('y-m-d');
            $endDate = Carbon::parse('+ 30 day')->format('y-m-d');

            // 対象テーブル(モデルから取得)
            $modelObj = new $model();

            // テーブル名取得
            $table = $modelObj->getTable();

            // スケジュール情報テーブル
            $schedule = (new Schedule)->getTable();
            // 受講生徒情報テーブル
            $classMember = (new ClassMember)->getTable();

            // 1件存在するかチェック
            $query->select(DB::raw(1))
                ->from($schedule)
                ->leftJoin($classMember, function ($join) use ($classMember, $schedule) {
                    $join->on($classMember . '.schedule_id', '=', $schedule . '.schedule_id')
                        ->whereNull($classMember . '.deleted_at');
                })
                // 以下の条件はクロージャで記述(orを含むため)
                ->where(function ($query) use ($table, $schedule, $classMember) {
                    // 対象テーブルとスケジュール情報のstudent_idを連結（１対１授業）
                    $query->whereRaw($table . '.student_id = ' . $schedule . '.student_id')
                        // または対象テーブルと受講生徒情報のstudent_idを連結（１対多授業）
                        ->orWhereRaw($table . '.student_id = ' . $classMember . '.student_id');
                })
                // ログインユーザのID または指定のtutor_id
                ->where($schedule . '.tutor_id', $tutorId)
                // スケジュール情報を受け持ち判定期間で絞り込み
                ->whereBetween($schedule . '.target_date', [$startDate, $endDate])
                // 教室が指定された場合のみ絞り込み
                ->when($campusCd, function ($query) use ($schedule, $campusCd) {
                    return $query->where($schedule . '.campus_cd', $campusCd);
                })
                // delete_dt条件の追加
                ->whereNull($schedule . '.deleted_at');
        });
    }

    /**
     * 指定された生徒IDのスケジュールのみを絞り込む
     * 授業報告書検索用
     * whereに指定する条件を取得する
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $model 生徒IDを絞るテーブルのモデルクラス
     * @param int $studentId 生徒ID
     */
    protected function mdlWhereScheduleBySidQuery($query, $model, $studentId)
    {

        // スケジュール情報に存在するかチェックする。existsを使用した
        $query->whereExists(function ($query) use ($model, $studentId) {

            // 対象テーブル(モデルから取得)
            $modelObj = new $model();

            // テーブル名取得
            $table = $modelObj->getTable();

            // スケジュール情報テーブル
            $schedule = (new Schedule)->getTable();
            // 受講生徒情報テーブル
            $classMember = (new ClassMember)->getTable();

            // 1件存在するかチェック
            $query->select(DB::raw(1))
                ->from($schedule)
                // 対象テーブルとスケジュール情報のschedule_idを連結
                ->whereRaw($table . '.schedule_id = ' . $schedule . '.schedule_id')
                // スケジュール情報と受講生徒情報を連結
                ->leftJoin($classMember, function ($join) use ($classMember, $schedule) {
                    $join->on($classMember . '.schedule_id', '=', $schedule . '.schedule_id')
                        ->whereNull($classMember . '.deleted_at');
                })
                // 以下の条件はクロージャで記述(orを含むため)
                ->where(function ($query) use ($schedule, $classMember, $studentId) {
                    // 対象テーブルとスケジュール情報のstudent_idを連結（１対１授業）
                    $query->where($schedule . '.student_id', $studentId)
                        // または対象テーブルと受講生徒情報のstudent_idを連結（１対多授業）
                        ->orWhere($classMember . '.student_id', $studentId);
                })
                // delete_dt条件の追加
                ->whereNull($schedule . '.deleted_at');
        });
    }

    /**
     * 指定された校舎コードの講師IDのみを絞り込む（管理者向け画面用）
     * 講師所属情報を検索
     * whereに指定する条件を取得する
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $campusCd 校舎コード
     */
    protected function mdlWhereTidByRoomQuery($query, $model, $campusCd)
    {

        // 生徒所属情報に存在するかチェックする。existsを使用した
        $query->whereExists(function ($query) use ($model, $campusCd) {

            // 対象テーブル(モデルから取得)
            $modelObj = new $model();

            // テーブル名取得
            $table = $modelObj->getTable();

            // 生徒所属情報テーブル
            $tutorCampus = (new TutorCampus)->getTable();

            // 1件存在するかチェック
            $query->select(DB::raw(1))
                ->from($tutorCampus)
                // 対象テーブルと講師所属情報の講師IDを連結
                ->whereRaw($table . '.tutor_id = ' . $tutorCampus . '.tutor_id')
                // 指定された校舎コード
                ->where($tutorCampus . '.campus_cd', $campusCd)
                // delete_dt条件の追加
                ->whereNull($tutorCampus . '.deleted_at');
        });
    }

    /**
     * 指定された生徒IDの所属校舎のみを絞り込む
     * 生徒所属情報を検索
     * whereに指定する条件を取得する
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $model 校舎を絞るテーブルのモデルクラス
     * @param int $studentId 生徒ID
     */
    protected function mdlWhereRoomBySidQuery($query, $model, $studentId)
    {

        // 生徒所属情報に存在するかチェックする。existsを使用した
        $query->whereExists(function ($query) use ($model, $studentId) {

            // 対象テーブル(モデルから取得)
            $modelObj = new $model();

            // テーブル名取得
            $table = $modelObj->getTable();

            // 生徒所属情報テーブル
            $studentCampus = (new StudentCampus)->getTable();

            // 1件存在するかチェック
            $query->select(DB::raw(1))
                ->from($studentCampus)
                // 対象テーブルと生徒所属情報のcampus_cdを連結
                ->whereRaw($table . '.campus_cd = ' . $studentCampus . '.campus_cd')
                // 指定された生徒ID
                ->where($studentCampus . '.student_id', $studentId)
                // delete_dt条件の追加
                ->whereNull($studentCampus . '.deleted_at');
        });
    }

    /**
     * 指定された講師IDの所属校舎のみを絞り込む
     * 講師所属情報を検索
     * whereに指定する条件を取得する
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $model 校舎を絞るテーブルのモデルクラス
     * @param int $tutorId 講師ID
     */
    protected function mdlWhereRoomByTidQuery($query, $model, $tutorId)
    {

        // 講師所属情報に存在するかチェックする。existsを使用した
        $query->whereExists(function ($query) use ($model, $tutorId) {

            // 対象テーブル(モデルから取得)
            $modelObj = new $model();

            // テーブル名取得
            $table = $modelObj->getTable();

            // 講師所属情報テーブル
            $tutorCampus = (new TutorCampus)->getTable();

            // 1件存在するかチェック
            $query->select(DB::raw(1))
                ->from($tutorCampus)
                // 対象テーブルと講師所属情報のcampus_cdを連結
                ->whereRaw($table . '.campus_cd = ' . $tutorCampus . '.campus_cd')
                // 指定された生徒ID
                ->where($tutorCampus . '.tutor_id', $tutorId)
                // delete_dt条件の追加
                ->whereNull($tutorCampus . '.deleted_at');
        });
    }

    //------------------------------
    // SQLヘルパー
    //------------------------------

    /**
     * テーブル項目の日付のフォーマット 年月日
     *
     * @param string $col カラム名
     */
    protected function mdlFormatYmd($col)
    {
        return DB::raw("DATE_FORMAT(" . $col . ", '%Y-%m-%d')");
    }
}
