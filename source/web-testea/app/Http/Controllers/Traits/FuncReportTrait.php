<?php

namespace App\Http\Controllers\Traits;

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

/**
 * 授業報告 - 機能共通処理
 */
trait FuncReportTrait
{
    /**
     * 授業時間数の間隔・最大値
     * constがTraitで定義できないので変数にした
     */
    private $REPORT_MINUTES_INTERVAL = 5;
    private $REPORT_MINUTES_MIN = 5;
    private $REPORT_MINUTES_MAX = 200;

    /**
     * 授業時間数のプルダウンメニュー取得
     *
     * @return array
     */
    private function getMenuOfMinutes()
    {
        $minutes = array();
        for ($i = $this->REPORT_MINUTES_MIN; $i <= $this->REPORT_MINUTES_MAX; $i += $this->REPORT_MINUTES_INTERVAL) {
            $minutes += [$i => ["value" => $i]];
        }
        return $minutes;
    }

    /**
     * 授業報告書用スケジュールリスト（個別教室用）取得
     *
     * @param integer $tid 教師No
     * @param string $roomcd 教室コード（指定しない場合はnull）
     * @param string $sid 生徒ID（指定しない場合はnull）
     * @return array
     */
    private function getScheduleListReport($tid, $roomcd, $sid = null)
    {
        // 授業日・開始時刻が現在日付時刻以前の授業のみ登録可とする
        $today_date = date("Y/m/d");
        $today_time = date("H:i");

        // レギュラー＋個別講習の抽出条件
        $scheLessonTypes = [AppConst::EXT_GENERIC_MASTER_109_0, AppConst::EXT_GENERIC_MASTER_109_1];

        // 生徒No.に紐づくスケジュール（レギュラー＋個別講習）を取得する。
        $query = ExtSchedule::query();
        $lessons = $query
            ->select(
                'id',
                'lesson_date',
                'start_time'
            )
            // 自分の受け持ちのスケジュールのみ
            ->where('ext_schedule.tid', '=', $tid)
            ->whereIn('ext_schedule.lesson_type', $scheLessonTypes)

            // 以下の条件はクロージャで記述(orを含むため)
            ->where(function ($query) use ($today_date, $today_time) {
                // 授業日・開始時刻が現在日付時刻以前の授業のみ
                $query->where('ext_schedule.lesson_date', '<', $today_date)
                    ->orwhere(function ($orQuery) use ($today_date, $today_time) {
                        $orQuery->where('ext_schedule.lesson_date', '=', $today_date)
                            ->where('ext_schedule.start_time', '<=', $today_time);
                    });
            })
            // 後日振替のレコードを除外
            ->where(function ($orQuery) {
                // 出欠・振替コードが2（振替）以外 ※NULLのものを含む
                $orQuery->whereNotIn('ext_schedule.atd_status_cd', [AppConst::ATD_STATUS_CD_2])
                    ->orWhereNull('ext_schedule.atd_status_cd');
            })
            // 教室が指定された場合のみ絞り込み
            ->when($roomcd, function ($query) use ($roomcd) {
                return $query->where('.roomcd', $roomcd);
            })
            // 生徒IDが指定された倍のみ絞り込み
            ->when($sid, function ($query) use ($sid) {
                return $query->where('.sid', $sid);
            })
            ->orderBy('ext_schedule.lesson_date', 'desc')
            ->orderBy('ext_schedule.start_time', 'desc')
            ->get();

        // 個別教室のスケジュールプルダウンメニューを作成
        $scheduleMaster = $this->mdlGetScheduleMasterList($lessons);

        return $scheduleMaster;
    }

    /**
     * 授業報告書教材情報の取得
     *
     * @param integer $id report_id
     * @return array
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
            })
            ->first();
        
        return $report;
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
    private function getReportText($report_id,$sub_code)
    {
        $report_text = ReportUnit::query()
            ->where('report_units.report_id', '=', $report_id)
            ->where('report_units.sub_cd', '=', $sub_code)
            ->select(
                'mst_texts.name as text_name1',// 教材名
                'report_units.free_text_name as free_text_name1',// 教材名フリー
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
        if($no == 1) {
            $report_category = ReportUnit::query()
                ->where('report_units.report_id', '=', $report_id)
                ->where('report_units.sub_cd', '=', $sub_code)
                ->select(
                    'mst_unit_categories.name as unit_category_name',// 単元分類名
                    'report_units.free_category_name1 as free_category_name',// 単元分類名フリー
                    'mst_units.name as unit_name',// 単元名
                    'report_units.free_unit_name1 as free_unit_name',// 単元名フリー
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
        if($no == 2) {
            $report_category = ReportUnit::query()
                ->where('report_units.report_id', '=', $report_id)
                ->where('report_units.sub_cd', '=', $sub_code)
                ->select(
                    'mst_unit_categories.name as unit_category_name',// 単元分類名
                    'report_units.free_category_name2 as free_category_name',// 単元分類名フリー
                    'mst_units.name as unit_name',// 単元名
                    'report_units.free_unit_name2 as free_unit_name',// 単元名フリー
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
        if($no == 3) {
            $report_category = ReportUnit::query()
                ->where('report_units.report_id', '=', $report_id)
                ->where('report_units.sub_cd', '=', $sub_code)
                ->select(
                    'mst_unit_categories.name as unit_category_name',// 単元分類名
                    'report_units.free_category_name3 as free_category_name',// 単元分類名フリー
                    'mst_units.name as unit_name',// 単元名
                    'report_units.free_unit_name3 as free_unit_name',// 単元名フリー
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
     * @return array
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
                'mst_texts1.name as text_name1',// 教材名
                'report_units.free_text_name as free_text_name',// 教材名フリー
                'report_units.text_page as text_page1',
                'report_units.unit_category_cd1',
                'mst_unit_categories1.name as unit_category_name1',// 単元分類名
                'report_units.free_category_name1 as free_category_name1',// 単元分類名フリー
                'report_units.unit_cd1',
                'mst_units1.name as unit_name1',// 単元名
                'report_units.free_unit_name1 as free_unit_name1',// 単元名フリー
                'report_units.unit_category_cd2',
                'mst_unit_categories2.name as unit_category_name2',// 単元分類名
                'report_units.free_category_name2 as free_category_name2',// 単元分類名フリー
                'report_units.unit_cd2',
                'mst_units2.name as unit_name2',// 単元名
                'report_units.free_unit_name2 as free_unit_name2',// 単元名フリー
                'report_units.unit_category_cd3',
                'mst_unit_categories3.name as unit_category_name3',// 単元分類名
                'report_units.free_category_name3 as free_category_name3',// 単元分類名フリー
                'report_units.unit_cd3',
                'mst_units3.name as unit_name3',// 単元名
                'report_units.free_unit_name3 as free_unit_name3',// 単元名フリー
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
