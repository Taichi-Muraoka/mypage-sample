<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libs\AuthEx;
use App\Models\Tutor;
use App\Models\TutorCampus;
use App\Models\TutorSubject;
use App\Models\TutorFreePeriod;
use App\Models\CodeMaster;
use App\Models\MstSchool;
use App\Models\MstSubject;
use Illuminate\Support\Facades\DB;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;

/**
 * 空き講師検索 - コントローラ
 */
class TutorAssignController extends Controller
{

    // 機能共通処理：

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
        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 講師リストを取得
        $tutors = $this->mdlGetTutorList();

        // 曜日リストを取得
        $dayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // 性別リストを取得
        $genderList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_30);

        // 時限リストを取得（管理者用）
        $periods = $this->mdlGetPeriodListForAdmin();

        // 学年リストを取得
        $gradeList = $this->mdlGetTutorGradeList();

        // 科目リストを取得
        $subjects = $this->mdlGetSubjectList();

        return view('pages.admin.tutor_assign', [
            'rules' => $this->rulesForSearch(null),
            'rooms' => $rooms,
            'tutors' => $tutors,
            'periods' => $periods,
            'dayList' => $dayList,
            'genderList' => $genderList,
            'gradeList' => $gradeList,
            'subjects' => $subjects,
            'editData' => null
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

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
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

        // 独自バリデーション: リストのチェック 性別
        $validationGenderList =  function ($attribute, $value, $fail) {

            // 性別リストを取得
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_30);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 曜日
        $validationDayList =  function ($attribute, $value, $fail) {

            // 曜日リストを取得
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 時限
        $validationPeriodList =  function ($attribute, $value, $fail) {

            // 時限リストを取得
            $list = $this->mdlGetPeriodListForAdmin();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 科目
        $validationSubjectList =  function ($attribute, $value, $fail) {

            // 科目リストを取得
            $list = $this->mdlGetSubjectList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += TutorCampus::fieldRules('campus_cd', [$validationRoomList]);
        $rules += Tutor::fieldRules('tutor_id', [$validationTutorList]);
        $rules += Tutor::fieldRules('gender_cd', [$validationGenderList]);
        $rules += TutorSubject::fieldRules('subject_cd', [$validationSubjectList]);
        $rules += TutorFreePeriod::fieldRules('day_cd', [$validationDayList]);
        $rules += TutorFreePeriod::fieldRules('period_no', [$validationPeriodList]);
        // 学校名 学校マスタ項目のバリデーションルールをベースにする
        $ruleSchoolName = MstSchool::getFieldRule('name');
        $rules += ['school_j' => $ruleSchoolName];
        $rules += ['school_h' => $ruleSchoolName];
        $rules += ['school_u' => $ruleSchoolName];

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

        // クエリを作成（講師空き時間）
        $query = TutorFreePeriod::query();

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        // 校舎の絞り込み条件
        if (AuthEx::isRoomAdmin()) {
            $model = new TutorCampus;
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd($model));
        } else {
            // 全体管理者の場合検索フォームから取得
            if (isset($form['campus_cd']) && filled($form['campus_cd'])) {
                $query->where('tutor_campuses.campus_cd', $form['campus_cd']);
            }
        }

        // 講師IDの検索（TutorFreePeriodのscope使用）
        $query->SearchTutorId($form);

        // 曜日の検索（TutorFreePeriodのscope使用）
        $query->SearchDayCd($form);

        // 時限の検索（TutorFreePeriodのscope使用）
        $query->SearchPeriodNo($form);

        // 性別の検索
        if (isset($form['gender_cd']) && filled($form['gender_cd'])) {
            $query->where('tutors.gender_cd', $form['gender_cd']);
        }

        // 科目の検索
        if (isset($form['subject_cd']) && filled($form['subject_cd'])) {
            $query->where('tutor_subjects.subject_cd', $form['subject_cd']);
        }

        // 所属大学の絞り込み条件
        if (isset($form['school_u']) && filled($form['school_u'])) {
            // 学校名のLIKE検索
            $query->where('mst_school_u' . '.name', 'LIKE',  '%' . $form['school_u'] . '%');
        }

        // 出身高校の絞り込み条件
        if (isset($form['school_h']) && filled($form['school_h'])) {
            // 学校名のLIKE検索
            $query->where('mst_school_h' . '.name', 'LIKE',  '%' . $form['school_h'] . '%');
        }

        // 出身中学の絞り込み条件
        if (isset($form['school_j']) && filled($form['school_j'])) {
            // 学校名のLIKE検索
            $query->where('mst_school_j' . '.name', 'LIKE',  '%' . $form['school_j'] . '%');
        }

        // データを取得
        $TutorPeriods = $query
            ->select(
                'tutor_free_periods.free_period_id',
                'tutors.tutor_id',
                'tutor_campuses.campus_cd',
                'tutors.name as tutor_name',
                'room_names.room_name as campus_name',
                'tutor_free_periods.day_cd',
                'mst_codes.name as day_name',
                'tutor_free_periods.period_no',
            )
            // 講師情報とJOIN
            ->sdJoin(Tutor::class, function ($join) {
                $join->on('tutor_free_periods.tutor_id', 'tutors.tutor_id');
            })
            // 講師所属情報とJOIN
            ->sdJoin(TutorCampus::class, function ($join) {
                $join->on('tutor_free_periods.tutor_id', 'tutor_campuses.tutor_id');
            })
            // 講師担当科目情報とJOIN
            ->sdLeftJoin(TutorSubject::class, function ($join) {
                $join->on('tutor_free_periods.tutor_id', 'tutor_subjects.tutor_id');
            })
            // 校舎名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('tutor_campuses.campus_cd', '=', 'room_names.code');
            })
            // コードマスターとJOIN（曜日）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('tutor_free_periods.day_cd', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_16);
            })
            // 学校マスタとJOIN（大学）
            ->sdLeftJoin(MstSchool::class, function ($join) {
                $join->on('tutors.school_cd_u', 'mst_school_u.school_cd');
            }, 'mst_school_u')
            // 学校マスタとJOIN（高校）
            ->sdLeftJoin(MstSchool::class, function ($join) {
                $join->on('tutors.school_cd_h', 'mst_school_h.school_cd');
            }, 'mst_school_h')
            // 学校マスタとJOIN（中学）
            ->sdLeftJoin(MstSchool::class, function ($join) {
                $join->on('tutors.school_cd_j', 'mst_school_j.school_cd');
            }, 'mst_school_j')
            // レギュラー授業登録済みのコマを除外
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('regular_classes')
                    ->whereColumn('regular_classes.tutor_id', 'tutor_free_periods.tutor_id')
                    ->whereColumn('regular_classes.day_cd', 'tutor_free_periods.day_cd')
                    ->whereColumn('regular_classes.period_no', 'tutor_free_periods.period_no')
                    // delete_dt条件の追加
                    ->whereNull('regular_classes.deleted_at');
            })
            ->distinct()
            ->orderby('tutor_free_periods.day_cd', 'asc')
            ->orderby('tutor_free_periods.period_no', 'asc')
            ->orderby('tutor_campuses.campus_cd', 'asc')
            ->orderby('tutors.tutor_id', 'asc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $TutorPeriods);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getData(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'free_period_id');

        // クエリを作成（講師空き時間）
        $query = TutorFreePeriod::query();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $model = new TutorCampus;
        $query->where($this->guardRoomAdminTableWithRoomCd($model));

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        // データを取得
        $tutorPeriods = $query
            ->select(
                'tutors.tutor_id',
                'room_names.room_name as campus_name',
                'tutors.name as tutor_name',
                'tutors.tel',
                'tutors.email',
                'mst_codes_30.name as gender_name',
                'mst_codes_16.name as day_name',
                'tutor_free_periods.period_no',
                'mst_school_u.name as school_u_name',
                'mst_school_h.name as school_h_name',
                'mst_school_j.name as school_j_name',
            )
            // 講師情報とJOIN
            ->sdJoin(Tutor::class, function ($join) {
                $join->on('tutor_free_periods.tutor_id', 'tutors.tutor_id');
            })
            // 講師所属情報とJOIN
            ->sdJoin(TutorCampus::class, function ($join) {
                $join->on('tutor_free_periods.tutor_id', 'tutor_campuses.tutor_id');
            })
            // 校舎名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('tutor_campuses.campus_cd', '=', 'room_names.code');
            })
            // コードマスターとJOIN（曜日）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('tutor_free_periods.day_cd', '=', 'mst_codes_16.code')
                    ->where('mst_codes_16.data_type', AppConst::CODE_MASTER_16);
            }, 'mst_codes_16')
            // コードマスターとJOIN（性別）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('tutors.gender_cd', '=', 'mst_codes_30.code')
                    ->where('mst_codes_30.data_type', AppConst::CODE_MASTER_30);
            }, 'mst_codes_30')
            // 学校マスタとJOIN（大学）
            ->sdLeftJoin(MstSchool::class, function ($join) {
                $join->on('tutors.school_cd_u', 'mst_school_u.school_cd');
            }, 'mst_school_u')
            // 学校マスタとJOIN（高校）
            ->sdLeftJoin(MstSchool::class, function ($join) {
                $join->on('tutors.school_cd_h', 'mst_school_h.school_cd');
            }, 'mst_school_h')
            // 学校マスタとJOIN（中学）
            ->sdLeftJoin(MstSchool::class, function ($join) {
                $join->on('tutors.school_cd_j', 'mst_school_j.school_cd');
            }, 'mst_school_j')
            ->where('tutor_free_periods.free_period_id', '=', $request->free_period_id)
            ->firstOrFail();

        // クエリを作成（講師担当科目）
        $query = TutorSubject::query();
        // データを取得
        $tutorSubjects = $query
            ->select(
                'tutor_subjects.subject_cd',
                'mst_subjects.name as subject_name',
            )
            // 講師情報とJOIN
            ->sdJoin(Tutor::class, function ($join) {
                $join->on('tutor_subjects.tutor_id', 'tutors.tutor_id');
            })
            // 科目マスタとJOIN
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('tutor_subjects.subject_cd', 'mst_subjects.subject_cd');
            })
            // 対象の講師IDで絞り込み
            ->where('tutors.tutor_id', '=', $tutorPeriods->tutor_id)
            ->orderBy('tutor_subjects.subject_cd')
            ->get();

        // 取得データを配列->カンマ区切り文字列に変換しセット
        $arrSubjects = [];
        if (count($tutorSubjects) > 0) {
            foreach ($tutorSubjects as $subject) {
                array_push($arrSubjects, $subject['subject_name']);
            }
        }
        $tutorPeriods['subject_name'] = implode(', ', $arrSubjects);

        return $tutorPeriods;
    }
}
