<?php

namespace App\Http\Controllers\Traits;

use App\Libs\CommonDateFormat;
use App\Models\TutorFreePeriod;
use App\Models\YearlySchedule;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\MstCourse;
use App\Models\MstSubject;
use App\Models\MstSystem;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\TransferApplication;
use App\Models\TransferApplicationDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

/**
 * 振替申請 - 機能共通処理
 */
trait FuncTransferTrait
{

    //==========================
    // 関数名を区別するために
    // fncTranを先頭につける
    //==========================

    //------------------------------
    // データ取得系
    //------------------------------

    /**
     * 振替依頼対象スケジュール情報を取得
     * 
     * @param   $fromDate 対象開始日
     * @param   $toDate   対象終了日
     * @param   $sid      生徒ID
     * @param   $tid      講師ID
     * @return  object スケジュール情報
     */
    private function fncTranGetTransferSchedule($fromDate, $toDate, $sid = null, $tid = null)
    {

        $query = Schedule::query();
        $lessons = $query
            ->select(
                'schedule_id',
                'target_date',
                'period_no'
            )
            // コース種別 = 授業単 のみ対象とする
            ->sdJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd')
                    ->where('mst_courses.course_kind', '=', AppConst::CODE_MASTER_42_1);;
            })
            // キーの指定
            // 生徒IDが指定された場合のみ絞り込み
            ->when($sid, function ($query) use ($sid) {
                return $query->where('schedules.student_id', $sid);
            })
            // 講師IDが指定された場合のみ絞り込み
            ->when($tid, function ($query) use ($tid) {
                return $query->where('schedules.tutor_id', $tid);
            })

            // 出欠・振替コードが0:実施前・出席、3:未振替
            ->whereIn('schedules.absent_status', [AppConst::CODE_MASTER_35_0, AppConst::CODE_MASTER_35_3])
            // 対象の授業日範囲
            ->whereBetween('schedules.target_date', [$fromDate, $toDate])
            // 仮登録状態 = 0:本登録
            ->whereIn('schedules.tentative_status', [AppConst::CODE_MASTER_36_0, AppConst::CODE_MASTER_35_3])
            ->orderby('schedules.target_date')
            ->orderby('schedules.period_no')
            ->get();

        return $lessons;
    }

    /**
     * 選択対象スケジュール情報を取得
     * 
     * @param   $scheduleId スケジュールID
     * @param   $sid        生徒ID
     * @param   $tid        講師ID
     * @return  object スケジュール情報(選択時表示用)
     */
    private function fncTranGetTargetScheduleInfo($scheduleId, $sid, $tid = null)
    {
        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        $query = Schedule::query();
        $lesson = $query
            // キーの指定
            ->where('schedules.schedule_id', '=', $scheduleId)
            ->select(
                'schedules.schedule_id',
                'schedules.target_date',
                'schedules.campus_cd',
                'schedules.minites',
                'schedules.booth_cd',
                'schedules.how_to_kind',
                // 校舎名
                'campus_names.room_name as campus_name',
                'schedules.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'schedules.tutor_id',
                // 教師情報の名前
                'tutors.name as tutor_name',
                'schedules.course_cd',
                // コース名
                'mst_courses.name as course_name',
                'schedules.subject_cd',
                // 科目名
                'mst_subjects.name as subject_name'
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'schedules.student_id', '=', 'students.student_id')
            // 講師名を取得
            ->sdLeftJoin(Tutor::class, 'schedules.tutor_id', '=', 'tutors.tutor_id')
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd');
            })
            // 科目名の取得
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('mst_subjects.subject_cd', '=', 'schedules.subject_cd');
            })
            // 生徒IDが指定された場合のみ絞り込み
            ->when($sid, function ($query) use ($sid) {
                return $query->where('schedules.student_id', $sid);
            })
            // 講師IDが指定された場合のみ絞り込み
            ->when($tid, function ($query) use ($tid) {
                return $query->where('schedules.tutor_id', $tid);
            })
            ->firstOrFail();

        return $lesson;
    }

    /**
     * 一覧表示用 振替依頼情報を取得SQLを作成
     * 
     * @param   $sid        生徒ID
     * @param   $tid        講師ID
     * @param   $campusCd   校舎コード
     * @param   $status     振替依頼承認ステータス
     * @param   $forS       生徒向け(ステータス条件あり)
     * @return  object 振替依頼情報
     */
    private function fncTranGetATransferApplicationList($sid = null, $tid = null, $campusCd = null, $status = null, $forS = true)
    {
        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        $query = TransferApplication::query();
        // データを取得
        $transfers = $query
            ->select(
                'transfer_applications.transfer_apply_id',
                'transfer_applications.apply_date',
                'transfer_applications.apply_kind',
                'transfer_applications.approval_status',
                'transfer_applications.monthly_count',
                // 授業情報
                'schedules.target_date',
                'schedules.period_no',
                // 生徒の名称
                'students.name as student_name',
                // 講師の名称
                'tutors.name as tutor_name',
                // 校舎名
                'campus_names.room_name as campus_name',
                // コースの名称
                'mst_courses.name as course_name',
                // コードマスタ（申請者種別）の名称
                'mst_codes_53.name as apply_kind_name',
                // コードマスタ（振替承認ステータス）の名称・汎用項目１
                'mst_codes_3.gen_item1 as approval_status_name_for_student', // 振替承認ステータス(生徒向け)
                'mst_codes_3.name as approval_status_name'                   // 振替承認ステータス(講師・運用管理向け)
            )
            // 授業情報の取得
            ->sdLeftJoin(Schedule::class, function ($join) {
                $join->on('transfer_applications.schedule_id', '=', 'schedules.schedule_id');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'transfer_applications.student_id', '=', 'students.student_id')
            // 講師名称の取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('transfer_applications.tutor_id', '=', 'tutors.tutor_id');
            })
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('schedules.campus_cd', '=', 'campus_names.code');
            })
            // コース名称の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd')
                    ->where('course_kind', '=', AppConst::CODE_MASTER_42_1);;
            })
            // コードマスタ（申請者種別）名称の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('transfer_applications.apply_kind', '=', 'mst_codes_53.code')
                    ->where('mst_codes_53.data_type', '=', AppConst::CODE_MASTER_53);
            }, 'mst_codes_53')
            // コードマスタ（振替承認ステータス）名称の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('transfer_applications.approval_status', '=', 'mst_codes_3.code')
                    ->where('mst_codes_3.data_type', '=', AppConst::CODE_MASTER_3);
            }, 'mst_codes_3')

            // キーの指定
            // 生徒IDが指定された場合のみ絞り込み
            ->when($sid, function ($query) use ($sid) {
                return $query->where('transfer_applications.student_id', $sid);
            })
            // 講師IDが指定された場合のみ絞り込み
            ->when($tid, function ($query) use ($tid) {
                return $query->where('transfer_applications.tutor_id', $tid);
            })
            // 校舎コードが指定された場合のみ絞り込み
            ->when($campusCd, function ($query) use ($campusCd) {
                return $query->where('schedules.campus_cd', $campusCd);
            })
            // 振替承認ステータスが指定された場合のみ絞り込み
            ->when($status, function ($query) use ($status) {
                return $query->where('transfer_applications.approval_status', $status);
            })
            // 生徒向けフラグ=trueの場合のみ絞り込み
            ->when($forS, function ($query) {
                return $query->where('transfer_applications.approval_status', '!=', AppConst::CODE_MASTER_3_0);
            })

            ->orderby('apply_date', 'desc')
            ->orderby('target_date', 'asc')
            ->orderby('period_no', 'asc');

        return $transfers;
    }

    /**
     * 振替依頼情報を取得
     * 
     * @param   $id    振替依頼情報ID
     * @param   $sid   生徒ID
     * @param   $tid   講師ID
     * @return  object 振替依頼情報・振替依頼日程情報
     */
    private function fncTranGetTransferApplicationData($id, $sid = null, $tid = null)
    {
        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        $query = TransferApplication::query();
        $tranApp = $query
            ->where('transfer_applications.transfer_apply_id', '=', $id)
            ->select(
                'transfer_applications.transfer_apply_id',
                'transfer_applications.apply_kind',
                'mst_codes_53.name as apply_kind_name',     // 申請者種別
                'transfer_applications.schedule_id',
                'lesson_schedules.target_date as lesson_target_date',   // 振替前授業日
                'lesson_schedules.period_no as lesson_period_no',       // 振替前時限
                'lesson_schedules.campus_cd',               // 校舎コード
                'lesson_schedules.booth_cd',                // ブースコード
                'lesson_schedules.how_to_kind',             // 通塾種別
                'campus_names.room_name as campus_name',    // 校舎名
                'mst_courses.name as course_name',          // コース名
                'mst_subjects.name as subject_name',        // 科目名
                'transfer_applications.student_id',
                'students.name as student_name',            // 生徒名
                'transfer_applications.tutor_id',
                'lesson_tutors.name as lesson_tutor_name',  // 講師名
                'transfer_applications.transfer_reason',
                'transfer_applications.apply_date',         // 依頼日
                'transfer_applications.monthly_count',
                'transfer_applications.approval_status',    // 承認ステータス
                'mst_codes_3.gen_item1 as approval_status_name_for_student', // 振替承認ステータス(生徒向け)
                'mst_codes_3.name as approval_status_name', // 振替承認ステータス
                'transfer_applications.confirm_date_id',
                'transfer_applications.comment',
                'transfer_applications.transfer_schedule_id',   // 振替後スケジュールID
                'transfer_schedules.target_date as transfer_target_date',   // 振替後授業日
                'transfer_schedules.period_no as transfer_period_no',       // 振替後時限
                'transfer_applications.transfer_kind',
                'mst_codes_54.name as transfer_kind_name',  // 振替代講区分
                'transfer_applications.substitute_tutor_id',
                'sub_tutors.name as sub_tutor_name',        // 代講講師名
                'transfer_application_dates_1.transfer_date_id as transfer_date_id_1',
                'transfer_application_dates_1.transfer_date as transfer_date_1',
                'transfer_application_dates_1.period_no as period_no_1',
                'transfer_application_dates_2.transfer_date_id as transfer_date_id_2',
                'transfer_application_dates_2.transfer_date as transfer_date_2',
                'transfer_application_dates_2.period_no as period_no_2',
                'transfer_application_dates_3.transfer_date_id as transfer_date_id_3',
                'transfer_application_dates_3.transfer_date as transfer_date_3',
                'transfer_application_dates_3.period_no as period_no_3'
            )
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'transfer_applications.student_id', '=', 'students.student_id')
            // 講師名を取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('transfer_applications.tutor_id', '=', 'lesson_tutors.tutor_id');
            }, 'lesson_tutors')
            // スケジュール情報とJOIN
            ->sdLeftJoin(Schedule::class, function ($join) {
                $join->on('transfer_applications.schedule_id', '=', 'lesson_schedules.schedule_id');
            }, 'lesson_schedules')
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('lesson_schedules.campus_cd', '=', 'campus_names.code');
            })
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('lesson_schedules.course_cd', '=', 'mst_courses.course_cd');
            })
            // 科目の取得
            ->sdLeftJoin(MstSubject::class, 'lesson_schedules.subject_cd', '=', 'mst_subjects.subject_cd')
            // コードマスターとJOIN 申請者種別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('transfer_applications.apply_kind', '=', 'mst_codes_53.code')
                    ->where('mst_codes_53.data_type', AppConst::CODE_MASTER_53);
            }, 'mst_codes_53')
            // コードマスターとJOIN 振替依頼承認ステータス
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('transfer_applications.approval_status', '=', 'mst_codes_3.code')
                    ->where('mst_codes_3.data_type', AppConst::CODE_MASTER_3);
            }, 'mst_codes_3')
            // コードマスターとJOIN 振替代講区分
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('transfer_applications.transfer_kind', '=', 'mst_codes_54.code')
                    ->where('mst_codes_54.data_type', AppConst::CODE_MASTER_54);
            }, 'mst_codes_54')

            // 代講講師名を取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('transfer_applications.substitute_tutor_id', '=', 'sub_tutors.tutor_id');
            }, 'sub_tutors')

            // スケジュール情報とJOIN 振替スケジュール
            ->sdLeftJoin(Schedule::class, function ($join) {
                $join->on('transfer_applications.transfer_schedule_id', '=', 'transfer_schedules.schedule_id');
            }, 'transfer_schedules')

            // 振替依頼日程情報とJOIN 第1希望
            ->sdLeftJoin(TransferApplicationDate::class, function ($join) {
                $join->on('transfer_applications.transfer_apply_id', '=', 'transfer_application_dates_1.transfer_apply_id')
                    ->where('transfer_application_dates_1.request_no', '=', 1);
            }, 'transfer_application_dates_1')
            // 振替依頼日程情報とJOIN 第2希望
            ->sdLeftJoin(TransferApplicationDate::class, function ($join) {
                $join->on('transfer_applications.transfer_apply_id', '=', 'transfer_application_dates_2.transfer_apply_id')
                    ->where('transfer_application_dates_2.request_no', '=', 2);
            }, 'transfer_application_dates_2')
            // 振替依頼日程情報とJOIN 第3希望
            ->sdLeftJoin(TransferApplicationDate::class, function ($join) {
                $join->on('transfer_applications.transfer_apply_id', '=', 'transfer_application_dates_3.transfer_apply_id')
                    ->where('transfer_application_dates_3.request_no', '=', 3);
            }, 'transfer_application_dates_3')

            // 生徒IDが指定された場合のみ絞り込み
            ->when($sid, function ($query) use ($sid) {
                return $query->where('transfer_applications.student_id', $sid);
            })
            // 講師IDが指定された場合のみ絞り込み
            ->when($tid, function ($query) use ($tid) {
                return $query->where('transfer_applications.tutor_id', $tid);
            })

            ->firstOrFail();

        return $tranApp;
    }

    /**
     * 講師の空き時間情報を取得
     * 
     * @param   $tutorId    講師ID
     * @return  object      曜日,時限のリスト
     */
    private function fncTranGetTutorFreePeriods($tutorId)
    {
        $tutorFreeList = TutorFreePeriod::select(
            'day_cd',
            'period_no'
        )
            ->where('tutor_free_periods.tutor_id', '=', $tutorId)
            ->orderby('day_cd', 'asc')
            ->orderby('period_no', 'asc')
            ->get();

        return $tutorFreeList;
    }

    /**
     * 講師の空き授業時間を指定期間の範囲で取得
     * 対象講師、生徒の登録済みスケジュールもチェックする
     * 
     * @param $tutorId  講師ID
     * @param $fromDate 開始日
     * @param $toDate   終了日
     * @param $campusCd 校舎コード
     * @param $studentId 生徒ID
     * @param $minites  授業時間
     * @param $boothCd  ブースコード
     * @param $howToKind 通塾種別
     * @return object   日付,時限の配列
     */
    private function fncTranGetTutorFreeSchedule($tutorId, $fromDate, $toDate, $campusCd, $studentId, $minites, $boothCd, $howToKind)
    {
        // 対象期間の年間予定を取得
        $lessonDate = $this->fncTranGetScheduleFromTo($campusCd, $fromDate, $toDate);

        // 講師空き時間情報の取得
        $tutorFreeList = $this->fncTranGetTutorFreePeriods($tutorId);

        // 対象期間で講師空き時間から授業可能日・時限を生成
        $enableDateTime = array();
        foreach ($lessonDate as $lDate) {
            // 対象日の曜日を取得
            $youbi = $this->dtGetDayOfWeekCd($lDate);
            // 対象日・対象校舎の時限・開始～終了時刻を取得
            $timeTables = $this->getTimetableByDate($campusCd, $lDate);
            $periodList = $timeTables->keyBy('period_no');

            foreach ($tutorFreeList as $tutorFree) {
                // 空き時間と曜日が一致していたらスケジュールを作る
                if ($tutorFree->day_cd == $youbi) {
                    // 空き時間の時限が、対象日にあるかどうか
                    if (isset($periodList[$tutorFree->period_no])) {
                        // 終了時刻計算
                        $endTime = $this->fncTranEndTime($periodList[$tutorFree->period_no]['start_time'], $minites);

                        // 講師のスケジュール重複チェック
                        if ($this->fncScheChkDuplidateTid($lDate, $periodList[$tutorFree->period_no]['start_time'], $endTime, $tutorId)) {
                            // 生徒のスケジュール重複チェック
                            if ($this->fncScheChkDuplidateSid($lDate, $periodList[$tutorFree->period_no]['start_time'], $endTime, $studentId)) {
                                // ブース空きチェック
                                if ($this->fncScheSearchBooth($campusCd, $boothCd, $lDate, $tutorFree->period_no, $howToKind, null, false) != null) {
                                    // 重複していない場合は空きスケジュールの候補に追加
                                    $enableDateTime[] = [
                                        'target_date' => $lDate,
                                        'period_no' => $tutorFree->period_no
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        return $enableDateTime;
    }

    /**
     * 抽出したスケジュールより日・時限のプルダウンのリストを取得
     *
     * @param array $lessons schedulesよりget
     * @return array id     yyyy-mm-dd_period
     *               value  プルダウンメニュー用日時 Ym/d n限
     */
    private function fncTranGetScheduleMasterList($lessons)
    {
        // プルダウンメニューを作る
        $scheduleMasterValue = [];
        $scheduleMasterKeys = [];
        if (count($lessons) > 0) {
            foreach ($lessons as $lesson) {
                $schedule = [
                    'id' => date('Y-m-d', strtotime($lesson['target_date'])) . '_' . $lesson['period_no'],
                    'value' => CommonDateFormat::formatYmdDay($lesson['target_date']) . ' ' . $lesson['period_no'] . '限'
                ];
                $schedule = (object) $schedule;
                array_push($scheduleMasterKeys, $schedule->id);
                array_push($scheduleMasterValue, $schedule);
            }
        }

        $res = array_combine($scheduleMasterKeys, $scheduleMasterValue);

        return $res;
    }

    /**
     * 対象のスケジュール情報から、振替候補日のリストを取得
     * 
     * @param $schedule     スケジュール情報
     * @param $targetPeriod 候補日対象範囲
     * @return object 
     */
    private function fncTranGetTransferCandidateDates($schedule, $targetPeriod)
    {
        // 講師空き時間の取得
        $tutorFreePeriod = $this->fncTranGetTutorFreeSchedule(
            $schedule->tutor_id,
            $targetPeriod['from_date'],
            $targetPeriod['to_date'],
            $schedule->campus_cd,
            $schedule->student_id,
            $schedule->minites,
            $schedule->booth_cd,
            $schedule->how_to_kind
        );
        // プルダウン用リストに変換
        return $this->fncTranGetScheduleMasterList($tutorFreePeriod);
    }

    /**
     * 年間予定から日付の取得（空き時間候補生成用・開始日から終了日まで）
     *
     * @param string $campusCd 校舎コード
     * @param string $startDate 取得範囲開始日付
     * @param string $endDate 取得範囲終了日付
     * @return array
     */
    private function fncTranGetScheduleFromTo($campusCd, $startDate, $endDate)
    {
        // 年間予定情報から指定した範囲のデータを取得
        $query = YearlySchedule::query();
        $lessonDates = $query
            ->select('lesson_date', 'day_cd')
            ->where('campus_cd', $campusCd)
            ->whereBetween('lesson_date', [$startDate, $endDate])
            // 休日は除外する
            ->where('date_kind', "<>", AppConst::CODE_MASTER_38_9)
            ->orderBy('lesson_date')
            ->get();

        // 配列に格納
        $arrLessenDate = [];
        foreach ($lessonDates as $lessonDate) {
            array_push($arrLessenDate, $lessonDate->lesson_date->format('Y-m-d'));
        }

        return $arrLessenDate;
    }

    /**
     * 校舎・日付から時間割情報を時限をキーにしたリストで取得
     *
     * @param string    $campusCd 校舎コード
     * @param date      $targetDate 対象日
     * @return array    時限をキーにした、開始時間,終了時間 のリスト
     */
    private function getTimetablePeriodListByDate($campusCd, $targetDate)
    {
        $timeTables = $this->getTimetableByDate($campusCd, $targetDate);

        // period_noをキーにした配列にする
        $lists = array();
        foreach ($timeTables as $ttable) {
            $lists[$ttable->period_no] = [
                'start_time' => $ttable->start_time->format('H:i'),
                'end_time' => $ttable->end_time->format('H:i')
            ];
        }
        return $lists;
    }

    /**
     * 日・時限のプルダウンのリストのID値から、日・時限を返す
     *
     * @param string $keyId     yyyy-mm-dd_period
     * @return array pre_date   yyyy-mm-dd
     *               pre_period period
     */
    private function splitPreferredKeyId($keyId)
    {
        if ($keyId == null || $keyId == '') {
            return [
                'pre_date' => '',
                'pre_period' => ''
            ];
        }
        $select_pre = explode('_', $keyId);
        return [
            'pre_date' => $select_pre[0],
            'pre_period' => $select_pre[1]
        ];
    }

    /**
     * 当月振替依頼回数を取得
     * 依頼日がシステム日付と同月の、申請者種別別・講師＆生徒の組合せの依頼をカウント
     * 
     * @param   $tutorId        講師ID
     * @param   $studentId      生徒ID
     * @param   $applicantType  申請者種別
     * @return  int             回数
     */
    private function fncTranGetTransferRequestCount($tutorId, $studentId, $applicantType)
    {
        // システム日付 今月初日
        $first_date = date("Y-m-01");
        // 今月末日
        $last_date = date("Y-m-t");

        $transferApp = TransferApplication::selectRaw(
            'count(transfer_applications.transfer_apply_id) as requestCount'
        )
            ->where('transfer_applications.tutor_id', '=', $tutorId)
            ->where('transfer_applications.student_id', '=', $studentId)
            ->where('transfer_applications.apply_kind', '=', $applicantType)
            ->whereBetween('transfer_applications.apply_date', [$first_date, $last_date])
            ->groupBy('transfer_applications.tutor_id', 'transfer_applications.student_id', 'transfer_applications.apply_kind')
            ->first();

        if ($transferApp && $transferApp->count()) {
            return $transferApp->requestCount;
        } else {
            return 0;
        }
    }

    /**
     * 振替依頼情報IDからスケジュール情報を取得
     * 
     * @param $tranAppId    振替依頼情報ID
     * @return object       スケジュール情報
     */
    private function fncTranGetScheduleByTranAppId($tranAppId)
    {

        // スケジュール情報取得
        $query = Schedule::query();
        // データを取得
        $schedule = $query
            ->select(
                'schedules.schedule_id',
                'schedules.target_date',
                'schedules.period_no',
                'schedules.campus_cd',
                'schedules.booth_cd',
                'schedules.student_id',
                'schedules.tutor_id',
                'schedules.how_to_kind',
                'schedules.minites'
            )
            // 振替依頼情報の取得
            ->sdJoin(TransferApplication::class, function ($join) use ($tranAppId) {
                $join->on('schedules.schedule_id', '=', 'transfer_applications.schedule_id')
                    ->where('transfer_applications.transfer_apply_id', '=', $tranAppId);
            })
            ->firstOrFail();

        return $schedule;
    }

    /**
     * 振替依頼日程情報を取得
     * 
     * @param   $transferDateId 振替依頼日程情報ID
     * @return  object
     */
    private function fncTranGetTransferDate($transferDateId)
    {
        $transferDate = TransferApplicationDate::select(
            'request_no',
            'transfer_date',
            'period_no'
        )
            ->where('transfer_application_dates.transfer_date_id', '=', $transferDateId)
            ->firstOrFail();

        return $transferDate;
    }

    /**
     * 振替調整スキップ回数を取得する
     */
    private function fncTranGetTransferSkip()
    {
        // システムマスタ「振替調整スキップ回数」
        $skipCount = MstSystem::select('value_num')
            ->where('key_id', AppConst::SYSTEM_KEY_ID_3)
            ->first();

        return $skipCount->value_num;
    }

    /**
     * 承認画面用 振替依頼情報依頼情報
     * 
     * @param $transferId   振替依頼情報ID
     * @param $sId          生徒ID
     * @param $tId          講師ID
     * @return object 表示用データ
     */
    private function fncTranGetEditTransferData($transferId, $sId, $tId)
    {
        // データを取得
        $tranApp = $this->fncTranGetTransferApplicationData($transferId, $sId, $tId);

        // 希望日のスケジュール重複チェック
        $campusCd = $tranApp->campus_cd;
        $studentId = $tranApp->student_id;
        $tutorId = $tranApp->tutor_id;
        $boothCd = $tranApp->booth_cd;
        $howToKind = $tranApp->how_to_kind;
        $tran_date[1] = $this->dtFormatYmd($tranApp->transfer_date_1);
        $tran_date[2] = $this->dtFormatYmd($tranApp->transfer_date_2);
        $tran_date[3] = $this->dtFormatYmd($tranApp->transfer_date_3);
        $tran_period[1] = $tranApp->period_no_1;
        $tran_period[2] = $tranApp->period_no_2;
        $tran_period[3] = $tranApp->period_no_3;
        $freeCheck = [];
        for ($i = 1; $i <= 3; $i++) {
            $freeCheck[$i] = null;
            if ($tran_period[$i] != null && $tran_period[$i] != '') {
                // 対象日が、過去の場合は選択不可
                if (strtotime($tran_date[$i]) < strtotime('now')) {
                    $freeCheck[$i] = Lang::get('validation.invalid_date_cannot_select');
                } else {
                    // 対象日・対象校舎の時限・開始～終了時刻を取得
                    $timeTables = $this->getTimetableByDate($campusCd, $tran_date[$i]);
                    $periodList = $timeTables->keyBy('period_no');
                    if (!isset($periodList[$tran_period[$i]])) {
                        // 時限リストに該当の時限のデータがない
                        $freeCheck[$i] = Lang::get('validation.invalid_period');
                    } else {
                        $periodData = $periodList[$tran_period[$i]];

                        // 生徒スケジュール重複チェック
                        if (!$this->fncScheChkDuplidateSid(
                            $tran_date[$i],
                            $periodData['start_time'],
                            $periodData['end_time'],
                            $studentId
                        )) {
                            $freeCheck[$i] = Lang::get('validation.duplicate_student');
                        } else {
                            // 講師スケジュール重複チェック
                            if (!$this->fncScheChkDuplidateTid(
                                $tran_date[$i],
                                $periodData['start_time'],
                                $periodData['end_time'],
                                $tutorId
                            )) {
                                $freeCheck[$i] = Lang::get('validation.duplicate_tutor');
                            } else {
                                // ブース空きチェック
                                if ($this->fncScheSearchBooth(
                                    $campusCd,
                                    $boothCd,
                                    $tran_date[$i],
                                    $tran_period[$i],
                                    $howToKind,
                                    null,
                                    false
                                ) == null) {
                                    $freeCheck[$i] = Lang::get('validation.duplicate_booth');
                                }
                            }
                        }
                    }
                }
            }
        }

        $editdata = [
            'transfer_apply_id' => $tranApp->transfer_apply_id,
            'target_date' => CommonDateFormat::formatYmdDay($tranApp->lesson_target_date),
            'period_no' => $tranApp->lesson_period_no,
            'campus_name' => $tranApp->campus_name,
            'course_name' => $tranApp->course_name,
            'tutor_name' => $tranApp->lesson_tutor_name,
            'student_name' => $tranApp->student_name,
            'subject_name' => $tranApp->subject_name,
            'transfer_reason' => $tranApp->transfer_reason,
            'transfer_date_id_1' => $tranApp->transfer_date_id_1,
            'transfer_date_id_2' => $tranApp->transfer_date_id_2,
            'transfer_date_id_3' => $tranApp->transfer_date_id_3,
            'subject_name' => $tranApp->subject_name,
            'approval_status' => $tranApp->approval_status,
            'comment' => $tranApp->comment
        ];
        for ($i = 1; $i <= 3; $i++) {
            $fmtDate = '';
            if ($tran_date[$i] != '') {
                $fmtDate = CommonDateFormat::formatYmdDay($tran_date[$i]);
            }
            $editdata += [
                'transfer_date_' . $i => $fmtDate,
                'period_no_' . $i => $tran_period[$i],
                'free_check_' . $i => $freeCheck[$i]
            ];
        }
        return $editdata;
    }

    //------------------------------
    // 日付・時刻計算
    //------------------------------

    /**
     * システム日時を基準に振替調整対象授業日の範囲を取得
     * 
     * @return array 開始日～終了日
     */
    protected function fncTranTargetDateFromTo()
    {
        $nowTime = date('H:i');
        $fromDate = null;
        $toDate = null;
        if ($nowTime < '22:00') {
            // 現在時刻が22時までは、翌日～翌日より1ヶ月(30日)先
            $fromDate = date('Y/m/d', strtotime('+1 day'));
            $toDate = date('Y/m/d', strtotime('+31 day'));
        } else {
            // 現在時刻が22時以降は、翌々日～翌々日より1ヶ月(30日)先
            $fromDate = date('Y/m/d', strtotime('+2 day'));
            $toDate = date('Y/m/d', strtotime('+32 day'));
        }

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate
        ];
    }

    /**
     * 対象日を基準に振替候補日の範囲を取得
     * 
     * @param string  $target_date 基準日
     * @return object 開始日～終了日
     */
    protected function fncTranCandidateDateFromTo($target_date)
    {
        // システム日付から、候補の開始日の最小値を求める
        $nowTime = date('H:i');
        $minDate = null;
        if ($nowTime < '22:00') {
            // 現在時刻が22時までは、翌日以降
            $minDate = date('Y/m/d', strtotime('+1 day'));
        } else {
            // 現在時刻が22時以降は、翌々日以降
            $minDate = date('Y/m/d', strtotime('+2 day'));
        }

        // 対象日から前後2週間の日付
        $fromDate = date('Y/m/d', strtotime($target_date . ' -2 week'));
        $toDate = date('Y/m/d', strtotime($target_date . ' +2 week'));

        // 2週間前の日付と、候補の開始日の最小の日付と比較
        if (strtotime($minDate) > strtotime($fromDate)) {
            $fromDate = $minDate;
        }

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate
        ];
    }

    /**
     * 開始時刻に分数を加算し終了時刻を求める
     *
     * @param $start_time   開始時刻
     * @param $minutes      分数
     * @return string       終了時刻
     */
    private function fncTranEndTime($start_time, $minutes)
    {
        $end_time = date("H:i", strtotime($start_time) + 60 * $minutes);

        return $end_time;
    }

    //------------------------------
    // バリデーション（共通処理）
    //------------------------------

    /**
     * 第1希望日のカレンダー入力チェック
     */
    private function fncTranGetValidateInputCalender1($request, $targetPeriod)
    {
        // 独自バリデーション: フリー入力の日付範囲チェック
        $validationPreferred1_input_calender =  function ($attribute, $value, $fail) use ($request, $targetPeriod) {
            if (!$request->filled('preferred_date1_calender') || $request['preferred_date1_calender'] == '') {
                // 未入力の場合は必須チェックでエラー
                return;
            }
            // 範囲チェック
            if (!$this->dtCheckDateFromTo($request['preferred_date1_calender'], $targetPeriod['from_date'], $targetPeriod['to_date'])) {
                // 希望日範囲外エラー
                return $fail(Lang::get('validation.preferred_date_out_of_range'));
            }
            // 休業日チェック
            // 期間区分の取得（年間授業予定）
            $dateKind = $this->getYearlyDateKind($request['campus_cd'], $request['preferred_date1_calender']);
            if ($dateKind == AppConst::CODE_MASTER_38_9) {
                // 休業日の場合、エラー
                return $fail(Lang::get('validation.preferred_date_closed'));
            }
        };

        return $validationPreferred1_input_calender;
    }

    /**
     * 第1希望の時限チェック
     */
    private function fncTranGetValidateInputPeriod1($request, $targetPeriod)
    {
        $validationPreferred1_input_period =  function ($attribute, $value, $fail) use ($request) {
            if ((!$request->filled('preferred_date1_calender') || $request['preferred_date1_calender'] == '') ||
                (!$request->filled('preferred_date1_period') || $request['preferred_date1_period'] == '')
            ) {
                // 未入力の場合は必須チェックでエラー
                return;
            }

            // 時限リストを取得
            $list = $this->mdlGetPeriodListByDate($request['campus_cd'], $request['preferred_date1_calender']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        return $validationPreferred1_input_period;
    }

    /**
     * 第1希望日・時限チェック
     */
    private function fncTranGetValidateInput1($request, $schedules, $checkT = false)
    {
        $validationPreferred1_input =  function ($attribute, $value, $fail) use ($request, $schedules, $checkT) {
            if ((!$request->filled('preferred_date1_calender') || $request['preferred_date1_calender'] == '') ||
                (!$request->filled('preferred_date1_period') || $request['preferred_date1_period'] == '')
            ) {
                // 未入力の場合は必須チェックでエラー
                return;
            }

            // 振替対象の選択授業日・時限とチェック
            if (
                strtotime($request['preferred_date1_calender']) == strtotime($schedules->target_date) &&
                $request['preferred_date1_period'] == $schedules->period_no
            ) {
                // 重複エラー
                return $fail(Lang::get('validation.preferred_datetime_same'));
            }

            // 対象日・対象校舎の時限・開始～終了時刻を取得
            $timeTables = $this->getTimetableByDate($request['campus_cd'], $request['preferred_date1_calender']);
            $periodList = $timeTables->keyBy('period_no');
            if (!isset($periodList[$request['preferred_date1_period']])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
            $periodTime = $periodList[$request['preferred_date1_period']];

            // 生徒スケジュール重複チェック
            if (!$this->fncScheChkDuplidateSid(
                $request['preferred_date1_calender'],
                $periodTime['start_time'],
                $periodTime['end_time'],
                $request['student_id']
            )) {
                return $fail(Lang::get('validation.duplicate_student'));
            }

            if ($checkT) {
                // 講師スケジュール重複チェック
                if (!$this->fncScheChkDuplidateTid(
                    $request['preferred_date1_calender'],
                    $periodTime['start_time'],
                    $periodTime['end_time'],
                    $request['tutor_id']
                )) {
                    // 重複エラー
                    return $fail(Lang::get('validation.duplicate_tutor'));
                }
            }

            // ブース空きチェック
            if ($this->fncScheSearchBooth(
                $request['campus_cd'],
                $schedules->booth_cd,
                $request['preferred_date1_calender'],
                $request['preferred_date1_period'],
                $schedules->how_to_kind,
                null,
                false
            ) == null) {
                return $fail(Lang::get('validation.duplicate_booth'));
            }
        };
        return $validationPreferred1_input;
    }

    /**
     * 第2希望日のカレンダー入力チェック
     */
    private function fncTranGetValidateInputCalender2($request, $targetPeriod)
    {
        $validationPreferred2_input_calender =  function ($attribute, $value, $fail) use ($request, $targetPeriod) {
            if ((!$request->filled('preferred_date2_calender') || $request['preferred_date2_calender'] == '') &&
                (!$request->filled('preferred_date2_period') || $request['preferred_date2_period'] == '')
            ) {
                // カレンダーも時限も未入力の場合はOK
                return;
            }

            if ((!$request->filled('preferred_date2_calender') || $request['preferred_date2_calender'] == '') &&
                ($request->filled('preferred_date2_period') && $request['preferred_date2_period'] != '')
            ) {
                // 時限入力あり・カレンダー入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }

            // カレンダー入力ありの場合
            if ($request->filled('preferred_date2_calender') && $request['preferred_date2_calender'] != '') {
                // 範囲チェック
                if (!$this->dtCheckDateFromTo($request['preferred_date2_calender'], $targetPeriod['from_date'], $targetPeriod['to_date'])) {
                    // 希望日範囲外エラー
                    return $fail(Lang::get('validation.preferred_date_out_of_range'));
                }
                // 休業日チェック
                // 期間区分の取得（年間授業予定）
                $dateKind = $this->getYearlyDateKind($request['campus_cd'], $request['preferred_date2_calender']);
                if ($dateKind == AppConst::CODE_MASTER_38_9) {
                    // 休業日の場合、エラー
                    return $fail(Lang::get('validation.preferred_date_closed'));
                }
            }
        };
        return $validationPreferred2_input_calender;
    }

    /**
     * 第2希望の時限チェック
     */
    private function fncTranGetValidateInputPeriod2($request, $targetPeriod)
    {
        $validationPreferred2_input_period =  function ($attribute, $value, $fail) use ($request, $targetPeriod) {
            if ((!$request->filled('preferred_date2_calender') || $request['preferred_date2_calender'] == '') &&
                (!$request->filled('preferred_date2_period') || $request['preferred_date2_period'] == '')
            ) {
                // カレンダーも時限も未入力の場合はOK
                return;
            }
            if (($request->filled('preferred_date2_calender') && $request['preferred_date2_calender'] != '') &&
                (!$request->filled('preferred_date2_period') || $request['preferred_date2_period'] == '')
            ) {
                // カレンダー入力あり・時限入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }

            // 時限リストを取得
            $list = $this->mdlGetPeriodListByDate($request['campus_cd'], $request['preferred_date2_calender']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        return $validationPreferred2_input_period;
    }

    /**
     * 第2希望日・時限チェック
     */
    private function fncTranGetValidateInput2($request, $schedules, $checkT = false)
    {
        $validationPreferred2_input =  function ($attribute, $value, $fail) use ($request, $schedules, $checkT) {
            if ((!$request->filled('preferred_date2_calender') || $request['preferred_date2_calender'] == '') &&
                (!$request->filled('preferred_date2_period') || $request['preferred_date2_period'] == '')
            ) {
                // カレンダーも時限も未入力の場合はOK
                return;
            }
            if (($request->filled('preferred_date2_calender') && $request['preferred_date2_calender'] != '') &&
                (!$request->filled('preferred_date2_period') || $request['preferred_date2_period'] == '')
            ) {
                // カレンダー入力あり・時限入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }
            if ((!$request->filled('preferred_date2_calender') || $request['preferred_date2_calender'] == '') &&
                ($request->filled('preferred_date2_period') && $request['preferred_date2_period'] != '')
            ) {
                // 時限入力あり・カレンダー入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }

            // 振替対象の選択授業日・時限とチェック
            if (
                strtotime($request['preferred_date2_calender']) == strtotime($schedules->target_date) &&
                $request['preferred_date2_period'] == $schedules->period_no
            ) {
                // 重複エラー
                return $fail(Lang::get('validation.preferred_datetime_same'));
            }

            // 対象日・対象校舎の時限・開始～終了時刻を取得
            $timeTables = $this->getTimetableByDate($request['campus_cd'], $request['preferred_date2_calender']);
            $periodList = $timeTables->keyBy('period_no');
            if (!isset($periodList[$request['preferred_date2_period']])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
            $periodTime = $periodList[$request['preferred_date2_period']];

            // 生徒スケジュール重複チェック
            if (!$this->fncScheChkDuplidateSid(
                $request['preferred_date2_calender'],
                $periodTime['start_time'],
                $periodTime['end_time'],
                $request['student_id']
            )) {
                return $fail(Lang::get('validation.duplicate_student'));
            }

            if ($checkT) {
                // 講師スケジュール重複チェック
                if (!$this->fncScheChkDuplidateTid(
                    $request['preferred_date2_calender'],
                    $periodTime['start_time'],
                    $periodTime['end_time'],
                    $request['tutor_id']
                )) {
                    // 重複エラー
                    return $fail(Lang::get('validation.duplicate_tutor'));
                }
            }

            // ブース空きチェック
            if ($this->fncScheSearchBooth(
                $request['campus_cd'],
                $schedules->booth_cd,
                $request['preferred_date2_calender'],
                $request['preferred_date2_period'],
                $schedules->how_to_kind,
                null,
                false
            ) == null) {
                return $fail(Lang::get('validation.duplicate_booth'));
            }

            // 第１～２候補日を取得
            $preferred_datetime = [];
            for ($i = 1; $i <= 2; $i++) {
                if (!$checkT) {
                    // 生徒向け
                    if ($request['preferred' . $i . '_type'] == AppConst::TRANSFER_PREF_TYPE_SELECT) {
                        // 候補日選択の場合
                        $preferred_datetime[$i] = $request['preferred_date' . $i . '_select'];
                    } else {
                        // フリー入力の場合
                        $preferred_datetime[$i] = $request['preferred_date' . $i . '_calender'] . '_' . $request['preferred_date' . $i . '_period'];
                    }
                } else {
                    // 講師向け
                    $preferred_datetime[$i] = $request['preferred_date' . $i . '_calender'] . '_' . $request['preferred_date' . $i . '_period'];
                }
            }
            if ($preferred_datetime[2] != '_') {
                if ($preferred_datetime[1] == $preferred_datetime[2]) {
                    // 希望日重複エラー
                    return $fail(Lang::get('validation.preferred_datetime_distinct'));
                }
            }
        };
        return $validationPreferred2_input;
    }


    /**
     * 第3希望日のカレンダー入力チェック
     */
    private function fncTranGetValidateInputCalender3($request, $targetPeriod)
    {
        $validationPreferred3_input_calender =  function ($attribute, $value, $fail) use ($request, $targetPeriod) {
            if ((!$request->filled('preferred_date3_calender') || $request['preferred_date3_calender'] == '') &&
                (!$request->filled('preferred_date3_period') || $request['preferred_date3_period'] == '')
            ) {
                // カレンダーも時限も未入力の場合はOK
                return;
            }
            if ((!$request->filled('preferred_date3_calender') || $request['preferred_date3_calender'] == '') &&
                ($request->filled('preferred_date3_period') && $request['preferred_date3_period'] != '')
            ) {
                // 時限入力あり・カレンダー入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }

            // カレンダー入力ありの場合
            if ($request->filled('preferred_date3_calender') && $request['preferred_date3_calender'] != '') {
                // 範囲チェック
                if (!$this->dtCheckDateFromTo($request['preferred_date3_calender'], $targetPeriod['from_date'], $targetPeriod['to_date'])) {
                    // 希望日範囲外エラー
                    return $fail(Lang::get('validation.preferred_date_out_of_range'));
                }
                // 休業日チェック
                // 期間区分の取得（年間授業予定）
                $dateKind = $this->getYearlyDateKind($request['campus_cd'], $request['preferred_date3_calender']);
                if ($dateKind == AppConst::CODE_MASTER_38_9) {
                    // 休業日の場合、エラー
                    return $fail(Lang::get('validation.preferred_date_closed'));
                }
            }
        };
        return $validationPreferred3_input_calender;
    }

    /**
     * 第3希望の時限チェック
     */
    private function fncTranGetValidateInputPeriod3($request, $targetPeriod)
    {
        $validationPreferred3_input_period =  function ($attribute, $value, $fail) use ($request, $targetPeriod) {
            if ((!$request->filled('preferred_date3_calender') || $request['preferred_date3_calender'] == '') &&
                (!$request->filled('preferred_date3_period') || $request['preferred_date3_period'] == '')
            ) {
                // カレンダーも時限も未入力の場合はOK
                return;
            }
            if (($request->filled('preferred_date3_calender') && $request['preferred_date3_calender'] != '') &&
                (!$request->filled('preferred_date3_period') || $request['preferred_date3_period'] == '')
            ) {
                // カレンダー入力あり・時限入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }

            // 時限リストを取得
            $list = $this->mdlGetPeriodListByDate($request['campus_cd'], $request['preferred_date3_calender']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        return $validationPreferred3_input_period;
    }

    /**
     * 第3希望日・時限チェック
     */
    private function fncTranGetValidateInput3($request, $schedules, $checkT = false)
    {
        $validationPreferred3_input =  function ($attribute, $value, $fail) use ($request, $schedules, $checkT) {
            if ((!$request->filled('preferred_date3_calender') || $request['preferred_date3_calender'] == '') &&
                (!$request->filled('preferred_date3_period') || $request['preferred_date3_period'] == '')
            ) {
                // カレンダーも時限も未入力の場合はOK
                return;
            }
            if (($request->filled('preferred_date3_calender') && $request['preferred_date3_calender'] != '') &&
                (!$request->filled('preferred_date3_period') || $request['preferred_date3_period'] == '')
            ) {
                // カレンダー入力あり・時限入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }
            if ((!$request->filled('preferred_date3_calender') || $request['preferred_date3_calender'] == '') &&
                ($request->filled('preferred_date3_period') && $request['preferred_date3_period'] != '')
            ) {
                // 時限入力あり・カレンダー入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }

            // 振替対象の選択授業日・時限とチェック
            if (
                strtotime($request['preferred_date3_calender']) == strtotime($schedules->target_date) &&
                $request['preferred_date3_period'] == $schedules->period_no
            ) {
                // 重複エラー
                return $fail(Lang::get('validation.preferred_datetime_same'));
            }

            // 対象日・対象校舎の時限・開始～終了時刻を取得
            $timeTables = $this->getTimetableByDate($request['campus_cd'], $request['preferred_date3_calender']);
            $periodList = $timeTables->keyBy('period_no');
            if (!isset($periodList[$request['preferred_date3_period']])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
            $periodTime = $periodList[$request['preferred_date3_period']];

            // 生徒スケジュール重複チェック
            if (!$this->fncScheChkDuplidateSid(
                $request['preferred_date3_calender'],
                $periodTime['start_time'],
                $periodTime['end_time'],
                $request['student_id']
            )) {
                return $fail(Lang::get('validation.duplicate_student'));
            }

            if ($checkT) {
                // 講師スケジュール重複チェック
                if (!$this->fncScheChkDuplidateTid(
                    $request['preferred_date3_calender'],
                    $periodTime['start_time'],
                    $periodTime['end_time'],
                    $request['tutor_id']
                )) {
                    // 重複エラー
                    return $fail(Lang::get('validation.duplicate_tutor'));
                }
            }

            // ブース空きチェック
            if ($this->fncScheSearchBooth(
                $request['campus_cd'],
                $schedules->booth_cd,
                $request['preferred_date3_calender'],
                $request['preferred_date3_period'],
                $schedules->how_to_kind,
                null,
                false
            ) == null) {
                return $fail(Lang::get('validation.duplicate_booth'));
            }

            // 第１～３候補日を取得
            $preferred_datetime = [];
            for ($i = 1; $i <= 3; $i++) {
                if (!$checkT) {
                    // 生徒向け
                    if ($request['preferred' . $i . '_type'] == AppConst::TRANSFER_PREF_TYPE_SELECT) {
                        // 候補日選択の場合
                        $preferred_datetime[$i] = $request['preferred_date' . $i . '_select'];
                    } else {
                        // フリー入力の場合
                        $preferred_datetime[$i] = $request['preferred_date' . $i . '_calender'] . '_' . $request['preferred_date' . $i . '_period'];
                    }
                } else {
                    // 講師向け
                    $preferred_datetime[$i] = $request['preferred_date' . $i . '_calender'] . '_' . $request['preferred_date' . $i . '_period'];
                }
            }
            if ($preferred_datetime[3] != '_') {
                if (
                    $preferred_datetime[1] == $preferred_datetime[3] ||
                    $preferred_datetime[2] == $preferred_datetime[3]
                ) {
                    // 希望日重複エラー
                    return $fail(Lang::get('validation.preferred_datetime_distinct'));
                }
            }
        };
        return $validationPreferred3_input;
    }

    /**
     * バリデーションルールを取得(承認用)
     *
     * @return array ルール
     */
    private function rulesForApproval(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: ステータスと希望日選択
        // 承認ステータス
        $validationApprovalStatus = function ($attribute, $value, $fail) use ($request) {

            // 振替承認ステータスリストを取得
            $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_3, [AppConst::CODE_MASTER_3_SUB_1]);
            if (!isset($statusList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }

            // 承認の場合、希望日選択必須
            if ($request->filled('approval_status') && $request['approval_status'] == AppConst::CODE_MASTER_3_2) {
                // 振替希望日選択チェック
                if (!$request->filled('transfer_date_id') || $request['transfer_date_id'] == '') {
                    // 希望日選択なしエラー
                    return $fail(Lang::get('validation.preferred_approval_not_select'));
                }

                // 授業情報取得
                $schedule = $this->fncTranGetScheduleByTranAppId($request['transfer_apply_id']);
                // 振替依頼日程情報取得
                $transferDate = $this->fncTranGetTransferDate($request['transfer_date_id']);
                // 振替依頼日・時限 開始～終了時間取得
                $periodTime = $this->getTimetablePeriodTimeByDatePeriod($schedule->campus_cd, $transferDate->transfer_date, $transferDate->period_no);
                // 終了時刻計算
                $endTime = $this->fncTranEndTime($periodTime->start_time, $schedule->minites);

                // 生徒スケジュール重複チェック
                if (!$this->fncScheChkDuplidateSid(
                    $transferDate->transfer_date,
                    $periodTime->start_time,
                    $endTime,
                    $schedule->student_id
                )) {
                    return $fail(Lang::get('validation.duplicate_student'));
                }

                // 講師スケジュール重複チェック
                if (!$this->fncScheChkDuplidateTid(
                    $transferDate->transfer_date,
                    $periodTime->start_time,
                    $endTime,
                    $schedule->tutor_id
                )) {
                    return $fail(Lang::get('validation.duplicate_tutor'));
                }

                // ブース空きチェック
                if ($this->fncScheSearchBooth(
                    $schedule->campus_cd,
                    $schedule->booth_cd,
                    $transferDate->transfer_date,
                    $transferDate->period_no,
                    $schedule->how_to_kind,
                    null,
                    false
                ) == null) {
                    return $fail(Lang::get('validation.duplicate_booth'));
                }
            }

            // 振替希望日選択チェック
            if ($request->filled('transfer_date_id') && $request['transfer_date_id'] != '') {
                // 希望日選択ありだが承認待ち・差戻し(日程不都合)・〃(代講希望)の場合、エラー
                if (
                    $request->filled('approval_status') &&
                    ($request['approval_status'] == AppConst::CODE_MASTER_3_1 ||
                        $request['approval_status'] == AppConst::CODE_MASTER_3_3 ||
                        $request['approval_status'] == AppConst::CODE_MASTER_3_4)
                ) {
                    // 希望日選択ありエラー
                    return $fail(Lang::get('validation.preferred_status_not_apply'));
                }
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += TransferApplication::fieldRules('approval_status', ['required', $validationApprovalStatus]);
        // コメント:承認ステータス=差戻し(日程不都合) or 差戻し(代講希望) の場合に必須
        $rules += TransferApplication::fieldRules('comment', ['required_if:approval_status,' . AppConst::CODE_MASTER_3_3, 'required_if:approval_status,' . AppConst::CODE_MASTER_3_4]);

        return $rules;
    }
}
