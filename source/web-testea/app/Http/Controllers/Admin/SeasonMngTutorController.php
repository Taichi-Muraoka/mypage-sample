<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\SeasonTutorRequest;
use App\Models\SeasonTutorPeriod;
use App\Models\Tutor;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\MstCourse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FuncSeasonTrait;

/**
 * 特別期間講習 講師提出スケジュール - コントローラ
 */
class SeasonMngTutorController extends Controller
{

    // 機能共通処理：特別期間講習
    use FuncSeasonTrait;

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
        // 特別期間リストを取得
        $seasonList = $this->fncSasnGetGetSeasonList();

        // 講師リストを取得
        $tutors = $this->mdlGetTutorList();

        return view('pages.admin.season_mng_tutor', [
            'seasonList' => $seasonList,
            'tutors' => $tutors,
            'editData' => null,
            'rules' => $this->rulesForSearch()
        ]);
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
        $validator = Validator::make($request->all(), $this->rulesForSearch());
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 特別期間コード
        $validationSeasonList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $list = $this->fncSasnGetGetSeasonList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 講師
        $validationTutorList =  function ($attribute, $value, $fail) {

            // 講師リストを取得
            $list = $this->mdlGetTutorList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 特別期間コード
        $rules += SeasonTutorRequest::fieldRules('season_cd', [$validationSeasonList]);
        // 講師
        $rules += SeasonTutorRequest::fieldRules('tutor_id', [$validationTutorList]);

        return $rules;
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
        Validator::make($request->all(), $this->rulesForSearch())->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成（講師日程連絡情報）
        $query = SeasonTutorRequest::query();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithTid());

        // 特別期間コードの絞り込み条件
        $query->SearchSeasonCd($form);

        // 講師の絞り込み条件
        $query->SearchTid($form);

        // データを取得
        $SeasonRequests = $query
            ->select(
                'season_tutor_requests.season_tutor_id',
                'tutors.name as tutor_name',
                'season_tutor_requests.season_cd',
                DB::raw('LEFT(season_tutor_requests.season_cd, 4) as year'),
                'mst_codes.gen_item2 as season_name',
                'season_tutor_requests.apply_date',
            )
            // コードマスターとJOIN（期間区分）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on(DB::raw('RIGHT(season_tutor_requests.season_cd, 2)'), '=', 'mst_codes.gen_item1')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_38);
            })
            // 講師名の取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('season_tutor_requests.tutor_id', 'tutors.tutor_id');
            })
            ->orderby('season_tutor_requests.apply_date', 'desc')
            ->orderby('season_tutor_requests.season_tutor_id', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $SeasonRequests);
    }

    //==========================
    // 詳細
    //==========================

    /**
     * 講師提出スケジュール詳細画面
     *
     * @param int $seasonTutorId 講師連絡情報ID
     * @return view
     */
    public function detail($seasonTutorId)
    {

        // クエリを作成（講師連絡情報）
        $query = SeasonTutorRequest::query();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithTid());

        // データを取得
        $seasonTutor = $query
            ->select(
                'season_tutor_requests.season_tutor_id',
                'season_tutor_requests.season_cd',
                'season_tutor_requests.tutor_id',
                DB::raw('LEFT(season_tutor_requests.season_cd, 4) as year'),
                'mst_codes.gen_item2 as season_name',
                'season_tutor_requests.comment'
            )
            // コードマスターとJOIN（期間区分）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on(DB::raw('RIGHT(season_tutor_requests.season_cd, 2)'), '=', 'mst_codes.gen_item1')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_38);
            }, 'mst_codes')
            // IDを指定
            ->where('season_tutor_id', $seasonTutorId)
            ->firstOrFail();

        // 時限リストを取得（講師ID・時間割区分から）
        $periodList = $this->mdlGetPeriodListForTutor($seasonTutor->tutor_id, AppConst::CODE_MASTER_37_1);

        // 特別期間日付リストを取得（講師ID・特別期間コード指定）
        $dateList = $this->fncSasnGetSeasonDateForTutor($seasonTutor->tutor_id, $seasonTutor->season_cd);

        // 講師連絡コマ情報を取得する
        // クエリを作成（講師連絡コマ情報）
        $query = SeasonTutorPeriod::query();
        // データを取得
        $tutorPeriods = $query
            ->select(
                'season_tutor_periods.lesson_date',
                'season_tutor_periods.period_no'
            )
            // IDを指定
            ->where('season_tutor_id', $seasonTutorId)
            ->orderBy('season_tutor_periods.lesson_date')
            ->orderBy('season_tutor_periods.period_no')
            ->get();

        // チェックボックスをセットするための値を生成
        // 例：['20231225_1', '20231226_2']
        $editData = [];
        foreach ($tutorPeriods as $datePeriod) {
            // 配列に追加
            array_push($editData, $datePeriod->lesson_date->format('Ymd') . '_' . $datePeriod->period_no);
        }

        // 期間中の授業情報取得
        // クエリを作成（スケジュール情報）
        $query = Schedule::query();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithTid());

        // データを取得
        $schedules = $query
            ->select(
                'schedules.target_date',
                'schedules.period_no',
                'mst_courses.short_name as course_sname',
                'students.name as student_name'
            )
            // 生徒名の取得
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('schedules.student_id', 'students.student_id');
            })
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('schedules.course_cd', '=', 'mst_courses.course_cd');
            })
            // 対象の講師ID
            ->where('schedules.tutor_id', $seasonTutor->tutor_id)
            // 対象の特別期間の日付範囲
            ->whereIn('schedules.target_date', array_column($dateList, 'dateYmd'))
            ->orderBy('schedules.target_date')
            ->orderBy('schedules.period_no')
            ->get();

        // チェックボックスをセットするための値を生成
        // 例：['20231225_1', '20231226_2']
        $exceptData = [];
        $lessonInfo = [];
        foreach ($schedules as $schedule) {
            // 配列に追加
            $classKey = $schedule->target_date->format('Ymd') . '_' . $schedule->period_no;
            array_push($exceptData, $classKey);
            array_push($lessonInfo, [
                // '20231225_1'の形式
                'key' => $classKey,
                // 生徒名（１対多の場合はコース名）
                'student' => $schedule->student_name ?? $schedule->course_sname
            ]);
        }

        // 講師名を取得
        $tutor_name = $this->mdlGetTeacherName($seasonTutor->tutor_id);

        return view('pages.admin.season_mng_tutor-detail', [
            'tutor_name' => $tutor_name,
            'seasonTutor' => $seasonTutor,
            'periodList' => $periodList,
            'dateList' => $dateList,
            'editData' => [
                'chkWs' => $editData
            ],
            'exceptData' => $exceptData,
            'lessonInfo' => $lessonInfo,
        ]);
    }
}
