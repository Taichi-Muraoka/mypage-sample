<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Models\ExtStudentKihon;
use App\Models\Score;
use App\Models\ScoreDetail;
use App\Models\ExtSchedule;
use App\Models\ExtTrialMaster;
use App\Models\ExtGenericMaster;
use App\Models\MstGradeSubject;
use Illuminate\Support\Facades\Lang;

/**
 * 生徒成績 - 機能共通処理
 */
trait FuncGradesTrait
{
    /**
     * 成績詳細の件数
     *
     * @return int
     */
    private function getGradesDetailCount()
    {
        // 10件。TraitだとConstが定義できないため
        return 10;
    }

    /**
     * 模試名プルダウンメニューのリストを取得（対象生徒のもののみ）
     *
     * @param integer $sid 生徒No
     * @return array
     */
    private function getTrialList($sid)
    {

        // 模試名リストを取得
        $query = ExtSchedule::query();
        $query->select('ext_schedule.tmid', 'ext_trial_master.name as value')
            ->sdLeftJoin(ExtTrialMaster::class, function ($join) {
                $join->on('ext_schedule.tmid', '=', 'ext_trial_master.tmid')
                    ->where('ext_schedule.lesson_type', AppConst::EXT_GENERIC_MASTER_109_3);
            })
            ->where('ext_schedule.sid', $sid);

        // 教室リストを取得
        $moshiNames = $query->orderBy('tmid', 'desc')
            ->get()->keyBy('tmid');

        return $moshiNames;
    }

    /**
     * 教科プルダウンメニューのリストを取得
     *
     * @param integer $sid 生徒No
     * @return array
     */
    private function getCurriculumList($sid)
    {

        // 生徒基本情報より学年を取得
        // 汎用マスタより小・中・高別＋一般を取得
        $school = ExtStudentKihon::select('ext_generic_master.value1')
            ->where('ext_student_kihon.sid', $sid)
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_student_kihon.cls_cd', '=', 'ext_generic_master.code')
                    ->where('ext_generic_master.codecls', AppConst::EXT_GENERIC_MASTER_112);
            })
            ->firstOrFail();

        // 小・中・高別＋一般それぞれで表示の上限を設定
        switch ($school->value1) {
            case AppConst::EXT_GENERIC_MASTER_114_0:
                $curriculmMax = AppConst::EXT_GENERIC_MASTER_114_0_MAX;
                break;
            case AppConst::EXT_GENERIC_MASTER_114_1:
                $curriculmMax = AppConst::EXT_GENERIC_MASTER_114_1_MAX;
                break;
            case AppConst::EXT_GENERIC_MASTER_114_2:
                $curriculmMax = AppConst::EXT_GENERIC_MASTER_114_2_MAX;
                break;
            case AppConst::EXT_GENERIC_MASTER_114_9:
            default:
                $curriculmMax = AppConst::EXT_GENERIC_MASTER_114_9_MAX;
                break;
        }

        // 小・中・高別＋一般に応じた教科名リストを取得
        $curriculumList = ExtGenericMaster::select('code', 'name1 as value', 'disp_order')
            ->where('codecls', AppConst::EXT_GENERIC_MASTER_114)
            ->where('value1', $school->value1)
            ->where('code', 'LIKE', $school->value1 . '%')
            ->where('code', '<=', $curriculmMax)
            ->orderBy('disp_order')
            ->get()->keyBy('code');

        return $curriculumList;
    }

    /**
     * 生徒成績詳細を取得
     *
     * @param integer $scoreId 成績ID
     * @return array
     */
    private function getScoreDetail($scoreId)
    {
        // クエリを作成（生徒成績詳細）
        $query = ScoreDetail::query();

        // データを取得（生徒成績詳細）
        return $query
            // IDを指定
            ->where('score_details.score_id', $scoreId)
            // データを取得
            ->select(
                'score_details.g_subject_cd',
                // 成績科目の名称
                'mst_grade_subjects.name as g_subject_name',
                'score_details.score',
                'score_details.full_score',
                'score_details.average',
                'score_details.deviation_score'
            )
            // 成績科目名の取得
            ->sdLeftJoin(MstGradeSubject::class, 'mst_grade_subjects.g_subject_cd', '=', 'score_details.g_subject_cd')
            ->orderBy('score_details.score_datail_id')
            ->get();
    }

    /**
     * 生徒成績詳細を取得(編集用データ)
     *
     * @param integer $gradesId 成績ID
     * @return array
     */
    private function getGradesDetailEdit($gradesId)
    {
        $gradesDetails = [];

        // 件数
        $count = $this->getGradesDetailCount();

        // データを取得（生徒成績詳細）
        for ($i = 0; $i < $count; $i++) {
            $gradesDetails[$i] = ScoreDetail::select(
                'grades_seq as grades_seq_' . $i,
                'curriculumcd as curriculumcd_' . $i,
                'curriculum_name as curriculum_name_' . $i,
                'score as score_' . $i,
                'previoustime as previoustime_' . $i,
                'average as average_' . $i
            )
                // IDを指定
                ->where('score_details.score_id', $gradesId)
                // grades_seqを指定
                ->where('score_details.grades_seq', $i + 1)
                // 取得できない場合はNULLとしてセット
                ->first();
        }

        return $gradesDetails;
    }

    /**
     * 生徒成績詳細情報へ登録
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param integer $gradesId 生徒成績ID
     * @return array
     */
    private function saveToGradesDetail($request, $gradesId)
    {
        // 件数
        $count = $this->getGradesDetailCount();

        // GradesDetailテーブルへのinsert
        for ($i = 0; $i < $count; $i++) {
            // 1～5行目の登録
            if ($i < 5 && isset($request['curriculumcd_' . $i]) && filled($request['curriculumcd_' . $i])) {

                // 教科コードから教科名を取得
                $curriculum_name = ExtGenericMaster::select('name1')
                    ->where('codecls', AppConst::EXT_GENERIC_MASTER_114)
                    ->where('code', $request['curriculumcd_' . $i])
                    ->first();

                $gradesDetail = new GradesDetail;
                // 登録
                $gradesDetail->grades_id = $gradesId;
                $gradesDetail->grades_seq = $i + 1;
                $gradesDetail->curriculumcd =  $request['curriculumcd_' . $i];
                $gradesDetail->curriculum_name =  $curriculum_name->name1;
                $gradesDetail->score =  $request['score_' . $i];
                $gradesDetail->previoustime =  $request['previoustime_' . $i];
                $gradesDetail->average =  $request['average_' . $i];
                $gradesDetail->save();
            }
            // 6～10行目の登録
            else if ($i >= 5 && isset($request['curriculum_name_' . $i]) && filled($request['curriculum_name_' . $i])) {
                // GradesDetailテーブルへのinsert
                $gradesDetail = new GradesDetail;
                // 登録
                $gradesDetail->grades_id = $gradesId;
                $gradesDetail->grades_seq = $i + 1;
                $gradesDetail->curriculumcd =  null;
                $gradesDetail->curriculum_name =  $request['curriculum_name_' . $i];
                $gradesDetail->score =  $request['score_' . $i];
                $gradesDetail->previoustime =  $request['previoustime_' . $i];
                $gradesDetail->average =  $request['average_' . $i];
                $gradesDetail->save();
            }
        }
    }

    //==========================
    // バリデーション
    //==========================

    /**
     * リクエストの生徒成績詳細のデータ存在チェック
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param integer $gradesId 生徒成績ID
     * @return array
     */
    private function checkGradesDetailFromReqest($request)
    {
        // 件数
        $count = $this->getGradesDetailCount();

        for ($i = 0; $i < $count; $i++) {
            // gradesDatailテーブルよりupdate対象データを取得(PKでユニークに取る)
            if (isset($request['grades_seq_' . $i]) && filled($request['grades_seq_' . $i])) {
                ScoreDetail::where('grades_id', $request['grades_id'])
                    ->where('grades_seq', $request['grades_seq_' . $i])
                    // MEMO: 取得できない場合はエラーとする
                    ->firstOrFail();
            }
        }
    }

    /**
     * リクエストに成績詳細が存在するかチェック(1件以上)
     *
     * @param $request
     */
    private function validationGradesDetail($request)
    {

        if (!$request) {
            return true;
        }

        // 件数
        $count = $this->getGradesDetailCount();

        // 1件以上存在するかチェック
        for ($i = 0; $i < $count; $i++) {
            if (isset($request['curriculumcd_' . $i]) && filled($request['curriculumcd_' . $i])) {
                // 指定された
                return true;
            }
            // 6行目以降にのみ入力されている場合も有効とする
            if (isset($request['curriculum_name_' . $i]) && filled($request['curriculum_name_' . $i])) {
                // 指定された
                return true;
            }
        }

        // エラー
        return false;
    }

    /**
     * 重複チェック(模試ID)
     *
     * @param $request
     * @param int $sid 生徒ID
     */
    private function validationKeyMoshi($request, $sid, $fail)
    {

        if (!$request) {
            return;
        }
        // 試験種別が模試の場合のみ
        if ($request['exam_type'] != AppConst::CODE_MASTER_9_1) {
            return;
        }

        // 対象データを取得(ログイン者のデータ)
        $grades = Score::where('sid', $sid)
            ->where('exam_type', AppConst::CODE_MASTER_9_1)
            ->where('exam_id', $request['moshi_id']);

        // 変更時は更新中のキー以外を検索
        if (filled($request['grades_id'])) {
            $grades->where('grades_id', '!=', $request['grades_id']);
        }

        $exists = $grades->exists();

        if ($exists) {
            // 登録済みエラー
            return $fail(Lang::get('validation.duplicate_data'));
        }
    }

    /**
     * 重複チェック(定期考査ID)
     *
     * @param $request
     * @param int $sid 生徒ID
     */
    private function validationKeyTeiki($request, $sid, $fail)
    {

        if (!$request) {
            return;
        }
        // 試験種別が定期考査の場合のみ
        if ($request['exam_type'] != AppConst::CODE_MASTER_9_2) {
            return;
        }

        // 対象データを取得(ログイン者のデータ)
        $grades = Score::where('sid', $sid)
            ->where('exam_type', AppConst::CODE_MASTER_9_2)
            ->where('exam_id', $request['teiki_id']);

        // 変更時は更新中のキー以外を検索
        if (filled($request['grades_id'])) {
            $grades->where('grades_id', '!=', $request['grades_id']);
        }

        $exists = $grades->exists();

        if ($exists) {
            // 登録済みエラー
            return $fail(Lang::get('validation.duplicate_data'));
        }
    }

    /**
     * 生徒成績のルールをセット
     *
     * @param $rules
     */
    private function setRulesForGradesDetail($rules, $sid)
    {

        // 件数
        $count = $this->getGradesDetailCount();

        // 生徒成績詳細 項目のバリデーションルールをベースにする
        $ruleCurriculumName = ScoreDetail::getFieldRule('curriculum_name');
        $ruleCurriculumcd = ScoreDetail::getFieldRule('curriculumcd');
        $ruleScore = ScoreDetail::getFieldRule('score');
        $rulePrevioustime = ScoreDetail::getFieldRule('previoustime');
        $ruleAvarage = ScoreDetail::getFieldRule('average');

        // // 独自バリデーション: リストのチェック 教科
        $validationCurriculumList =  function ($attribute, $value, $fail) use ($sid) {

            // 教科リストを取得（対象生徒のもの）
            $curriculums = $this->getCurriculumList($sid);

            if (!isset($curriculums[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 前回比
        $validationUpdownList =  function ($attribute, $value, $fail) {

            // 前回比リストを取得
            $updown = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_11);

            if (!isset($updown[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        for ($i = 0; $i < $count; $i++) {
            if ($i < 5) {
                // 1～5行目のバリデーション
                $rules += ['score_' . $i =>  array_merge($ruleScore, ['required_with_all:curriculumcd_' . $i])];
                // 学年平均の必須チェックは行わない
                $rules += ['average_' . $i =>  $ruleAvarage];
                $rules += ['previoustime_' . $i =>  array_merge($rulePrevioustime, ['required_with_all:curriculumcd_' . $i], [$validationUpdownList])];

                // 教科バリデーション
                $rule = [];
                if ($i == 0) {
                    // 1行目に「1件以上の必須チェック」を入れる
                    $rule[] = 'array_required';
                }
                $rule[] = 'required_with:score_' . $i . ',average_' . $i;
                $rules += ['curriculumcd_' . $i =>  array_merge($ruleCurriculumcd, $rule, [$validationCurriculumList])];
            } else {
                // 6～10行目のバリデーション
                $rules += ['score_' . $i =>  array_merge($ruleScore, ['required_with_all:curriculum_name_' . $i])];
                // 学年平均の必須チェックは行わない
                $rules += ['average_' . $i =>  $ruleAvarage];
                $rules += ['previoustime_' . $i =>  array_merge($rulePrevioustime, ['required_with_all:curriculum_name_' . $i])];
                $rules += ['curriculum_name_' . $i =>  array_merge($ruleCurriculumName, ['required_with:score_' . $i . ',average_' . $i])];
            }
        }

        return $rules;
    }

    /**
     * リストのチェック 試験種別
     * 
     * @param $value
     * @param $fail
     */
    private  function validationExamTypeList($value, $fail)
    {
        // 試験種別リストを取得
        $examTypes = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_9);
        if (!isset($examTypes[$value])) {
            // 不正な値エラー
            return $fail(Lang::get('validation.invalid_input'));
        }
    }

    /**
     * リストのチェック 定期考査名
     * 
     * @param $value
     * @param $fail
     */
    function validationTeikiNameList($request, $value, $fail)
    {
        // 試験種別が定期考査の場合のみ
        if ($request['exam_type'] != AppConst::CODE_MASTER_9_2) {
            return;
        }

        // 定期考査名リストを取得
        $teikiNames = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_10);

        if (!isset($teikiNames[$value])) {
            // 不正な値エラー
            return $fail(Lang::get('validation.invalid_input'));
        }
    }

    /**
     * リストのチェック 定期考査名
     * 
     * @param $value
     * @param $fail
     */
    function validationMoshiNameList($request, $sid, $value, $fail)
    {
        // 試験種別が模試の場合のみ
        if ($request['exam_type'] != AppConst::CODE_MASTER_9_1) {
            return;
        }

        // 模試名リストを取得（対象生徒のもの）
        $moshiNames = $this->getTrialList($sid);

        if (!isset($moshiNames[$value])) {
            // 不正な値エラー
            return $fail(Lang::get('validation.invalid_input'));
        }
    }
}
