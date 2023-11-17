<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\MstGrade;
use App\Models\Student;
use App\Models\Score;
use App\Models\ScoreDetail;
use App\Models\MstGradeSubject;
use App\Libs\AuthEx;
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
    private function getScoreDetailCount()
    {
        // TraitだとConstが定義できないため
        // 高校の最大数に合わせた
        return 15;
    }

    //==========================
    // 一覧画面で使用
    //==========================
    /**
     * 生徒成績を取得 一覧用
     *
     * @param \Illuminate\Database\Eloquent\Builder $query クエリ
     */
    private function getScoreList($query)
    {
        return $query
            ->select(
                'scores.score_id as id',
                'scores.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'scores.exam_type',
                // コードマスタの名称（種別）
                'mst_codes_43.name as exam_type_name',
                'scores.regular_exam_cd',
                // コードマスタの名称（定期考査コード）
                'mst_codes_45.name as regular_exam_name',
                'scores.practice_exam_name',
                'scores.term_cd',
                // コードマスタの名称（学期コード）
                'mst_codes_44.name as term_name',
                'scores.regist_date',
            )
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'scores.student_id', '=', 'students.student_id')
            // 種別の名称取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('scores.exam_type', '=', 'mst_codes_43.code')
                    ->where('mst_codes_43.data_type', AppConst::CODE_MASTER_43);
            }, 'mst_codes_43')
            // 定期考査コードの名称取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('scores.regular_exam_cd', '=', 'mst_codes_45.code')
                    ->where('mst_codes_45.data_type', AppConst::CODE_MASTER_45);
            }, 'mst_codes_45')
            // 学期コードの名称取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('scores.term_cd', '=', 'mst_codes_44.code')
                    ->where('mst_codes_44.data_type', AppConst::CODE_MASTER_44);
            }, 'mst_codes_44')
            ->orderBy('scores.regist_date', 'desc');
    }

    /**
     * 生徒成績を取得 モーダル用
     *
     * @param integer $scoreId 成績ID
     * @return object
     */
    private function getScore($scoreId)
    {
        // クエリを作成
        $query = Score::query();

        if (AuthEx::isAdmin()) {
            // 教室管理者の場合、自分の校舎コードの生徒のみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithSid());
        }
        if (AuthEx::isStudent()) {
            // 生徒の場合、自分の生徒IDのみにガードを掛ける
            $query->where($this->guardStudentTableWithSid());
        }
        if (AuthEx::isTutor()) {
            // 講師の場合、自分の担当生徒のみにガードを掛ける
            $query->where($this->guardTutorTableWithSid());
        }

        return $query
            ->select(
                'scores.score_id',
                'scores.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'scores.exam_type',
                // コードマスタの名称（種別）
                'mst_codes_43.name as exam_type_name',
                'scores.regular_exam_cd',
                // コードマスタの名称（定期考査コード）
                'mst_codes_45.name as regular_exam_name',
                'scores.practice_exam_name',
                'scores.term_cd',
                // コードマスタの名称（学期コード）
                'mst_codes_44.name as term_name',
                'scores.exam_date',
                'scores.student_comment',
                'scores.regist_date',
            )
            // 詳細ボタン押下時に指定したIDで絞り込み
            ->where('scores.score_id', $scoreId)
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'scores.student_id', '=', 'students.student_id')
            // 種別の名称取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('scores.exam_type', '=', 'mst_codes_43.code')
                    ->where('mst_codes_43.data_type', AppConst::CODE_MASTER_43);
            }, 'mst_codes_43')
            // 定期考査コードの名称取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('scores.regular_exam_cd', '=', 'mst_codes_45.code')
                    ->where('mst_codes_45.data_type', AppConst::CODE_MASTER_45);
            }, 'mst_codes_45')
            // 学期コードの名称取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('scores.term_cd', '=', 'mst_codes_44.code')
                    ->where('mst_codes_44.data_type', AppConst::CODE_MASTER_44);
            }, 'mst_codes_44')
            ->first();
    }

    /**
     * 生徒成績詳細を取得 モーダル用
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

    //==========================
    // 新規登録時に使用
    //==========================
    /**
     * 成績登録時の学年の取得 新規登録用
     *
     * @param integer $sid 生徒ID
     * @return object
     */
    private function getGradeAtRegist($sid)
    {
        // 生徒情報より学年を取得
        // 学年マスタより学校区分を取得
        $query = Student::query();
        $grade = $query
            ->select(
                'students.grade_cd',
                'mst_grades.school_kind'
            )
            ->sdLeftJoin(MstGrade::class, function ($join) {
                $join->on('mst_grades.grade_cd', '=', 'students.grade_cd');
            })
            ->where('students.student_id', $sid)
            ->firstOrFail();

        return $grade;
    }

    //==========================
    // 編集時に使用
    //==========================
    /**
     * 成績登録時の学年の取得 編集用
     *
     * @param int $scoreId 成績ID
     * @return object
     */
    protected function getGradeAtEdit($scoreId)
    {
        $query = Score::query();
        $grade = $query
            ->select(
                'mst_grades.school_kind',
                'mst_grades.name',
            )
            ->sdLeftJoin(MstGrade::class, function ($join) {
                $join->on('mst_grades.grade_cd', '=', 'scores.grade_cd');
            })
            ->where('score_id', '=', $scoreId)
            ->firstOrFail();

        return $grade;
    }

    /**
     * 生徒成績詳細を取得 編集用
     *
     * @param integer $scoreId 成績ID
     * @return array
     */
    private function getScoreDetailEdit($scoreId)
    {
        // 成績詳細を取得
        $query = ScoreDetail::query();
        $scores = $query
            ->select(
                'g_subject_cd',
                'score',
                'full_score',
                'average',
                'deviation_score',
            )
            // IDを指定
            ->where('score_details.score_id', $scoreId)
            ->get();

        // 表示件数
        $count = $this->getScoreDetailCount();

        // データを格納
        $scoreDetails = [];
        for ($i = 0; $i < $count; $i++) {
            // 取得した成績詳細の数が表示件数より下回る場合はNULLをセット
            if (empty($scores[$i])) {
                $scoreDetails[$i] = null;
            } else {
                $scoreDetails[$i] = [
                    'g_subject_cd_' . $i => $scores[$i]->g_subject_cd,
                    'score_' . $i => $scores[$i]->score,
                    'rating_' . $i => $scores[$i]->score,
                    'full_score_' . $i => $scores[$i]->full_score,
                    'average_' . $i => $scores[$i]->average,
                    'deviation_score_' . $i => $scores[$i]->deviation_score,
                ];
            }
        }

        return $scoreDetails;
    }

    //==========================
    // 新規登録・編集 共通使用
    //==========================
    /**
     * 成績入力欄項目数の取得
     *
     * @param int $examType 試験種別
     * @param int $schoolKind 学校区分
     * @return string
     */
    protected function getDisplayCount($examType, $schoolKind)
    {
        $count = null;

        // 試験種別、学校区分によって項目数指定
        if ($examType == AppConst::CODE_MASTER_43_0) {
            // 模試 小6、中7、高その他10項目
            switch ($schoolKind) {
                case AppConst::CODE_MASTER_39_1:
                    $count = 6;
                    break;
                case AppConst::CODE_MASTER_39_2:
                    $count = 7;
                    break;
                case AppConst::CODE_MASTER_39_3 || AppConst::CODE_MASTER_39_4:
                    $count = 10;
                    break;
            }
        }
        if ($examType == AppConst::CODE_MASTER_43_1) {
            // 定期考査 中高その他15項目
            $count = 15;
        }
        if ($examType == AppConst::CODE_MASTER_43_2) {
            // 評定 中9、高その他15項目
            switch ($schoolKind) {
                case AppConst::CODE_MASTER_39_2:
                    $count = 9;
                    break;
                case AppConst::CODE_MASTER_39_3 || AppConst::CODE_MASTER_39_4:
                    $count = 15;
                    break;
            }
        }

        return $count;
    }

    /**
     * 試験種別プルダウンリストの取得
     *
     * @param int $schoolKind 学校区分
     * @return string
     */
    protected function getExamTypeList($schoolKind)
    {
        if ($schoolKind != AppConst::CODE_MASTER_39_1) {
            return $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_43);
        } else {
            // 小学生は模試のみ取得する
            $query = CodeMaster::query();

            return $query->select('code', 'name as value')
                ->where('data_type', AppConst::CODE_MASTER_43)
                ->where('code', AppConst::CODE_MASTER_43_0)
                ->orderby('order_code')
                ->get()
                ->keyBy('code');
        }
    }

    /**
     * 生徒成績詳細情報へ登録
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param integer $scoreId 生徒成績ID
     * @return array
     */
    private function saveToScoreDetail($request, $scoreId)
    {
        // 件数
        $count = $this->getScoreDetailCount();

        for ($i = 0; $i < $count; $i++) {
            if (isset($request['g_subject_cd_' . $i]) && filled($request['g_subject_cd_' . $i])) {

                $scoreDetail = new ScoreDetail;
                $scoreDetail->score_id = $scoreId;
                $scoreDetail->g_subject_cd = $request['g_subject_cd_' . $i];

                // 試験種別によって保存項目分岐
                // 編集時に試験種別を変更した場合に対応し、明示的にnullを保存する
                if ($request['exam_type'] == AppConst::CODE_MASTER_43_0) {
                    // 模試
                    $scoreDetail->score = $request['score_' . $i];
                    $scoreDetail->full_score = $request['full_score_' . $i];
                    $scoreDetail->average = $request['average_' . $i];
                    $scoreDetail->deviation_score = $request['deviation_score_' . $i];
                }
                if ($request['exam_type'] == AppConst::CODE_MASTER_43_1) {
                    // 定期考査
                    $scoreDetail->score = $request['score_' . $i];
                    $scoreDetail->full_score = null;
                    $scoreDetail->average = $request['average_' . $i];
                    $scoreDetail->deviation_score = null;
                }
                if ($request['exam_type'] == AppConst::CODE_MASTER_43_2) {
                    // 評定
                    $scoreDetail->score =  $request['rating_' . $i];
                    $scoreDetail->full_score = null;
                    $scoreDetail->average = null;
                    $scoreDetail->deviation_score = null;
                }

                $scoreDetail->save();
            }
        }
    }

    //==========================
    // バリデーション
    //==========================
    /**
     * リストのチェック 試験種別
     *
     * @param $value
     * @param $fail
     */
    private  function validationExamTypeList($value, $fail)
    {
        // 試験種別リストを取得
        $examTypes = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_43);
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
        // 試験種別が定期考査の場合のみチェック
        if ($request['exam_type'] != AppConst::CODE_MASTER_43_1) {
            return;
        }

        // 定期考査名リストを取得
        $teikiNames = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_45);

        if (!isset($teikiNames[$value])) {
            // 不正な値エラー
            return $fail(Lang::get('validation.invalid_input'));
        }
    }

    /**
     * リクエストに成績詳細が存在するかチェック(1件以上)
     *
     * @param $request
     */
    private function validationScoreDetail($request)
    {
        if (!$request) {
            return true;
        }

        // 件数
        $count = $this->getScoreDetailCount();

        // 1件以上存在するかチェック
        for ($i = 0; $i < $count; $i++) {
            if (isset($request['g_subject_cd_' . $i]) && filled($request['g_subject_cd_' . $i])) {
                // 指定された
                return true;
            }
        }

        // エラー
        return false;
    }

    /**
     * 生徒成績のルールをセット
     *
     * @param $rules
     */
    private function setRulesForScoreDetail($rules, $request)
    {
        // 件数
        $count = $this->getScoreDetailCount();

        // 生徒成績詳細 項目のバリデーションルールをベースにする
        $ruleSubjectCd = ScoreDetail::getFieldRule('g_subject_cd');
        $ruleScore = ScoreDetail::getFieldRule('score');
        $ruleFullScore = ScoreDetail::getFieldRule('full_score');
        $ruleAvarage = ScoreDetail::getFieldRule('average');
        $ruleDeviationScore = ScoreDetail::getFieldRule('deviation_score');

        // 送信ボタン押下前の初期表示用バリデーションルールセット（入力文字数）
        if (!$request) {
            for ($i = 0; $i < $count; $i++) {
                $rules += ['score_' . $i => $ruleScore];
                $rules += ['full_score_' . $i => $ruleFullScore];
                $rules += ['average_' . $i => $ruleAvarage];
                $rules += ['deviation_score_' . $i => $ruleDeviationScore];
                $rules += ['rating_' . $i => $ruleScore];
            }
            return $rules;
        }

        // // 独自バリデーション: リストのチェック 教科
        $validationSubjectList =  function ($attribute, $value, $fail) use ($request) {

            // 生徒の学年を取得する
            // 新規登録か編集かは成績IDの存在有無で判定する（新規登録時は成績IDは割り振られていない為）
            if (empty($request['score_id'])) {
                // 新規登録時は画面表示時の学年
                $grade = $this->getGradeAtRegist($request['student_id']);
            } else {
                // 編集時は成績登録時の学年
                $grade = $this->getGradeAtEdit($request['score_id']);
            }

            // 学年に応じた教科リストを取得する
            $subjectList = $this->mdlGetGradeSubjectList($grade->school_kind);

            if (!isset($subjectList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 教科の重複チェック
        $validationDupSubject =  function ($attribute, $value, $fail) use ($request, $count) {
            $subjects = [];
            // 1件以上存在するかチェック
            for ($i = 0; $i < $count; $i++) {
                if (isset($request['g_subject_cd_' . $i]) && filled($request['g_subject_cd_' . $i])) {
                    // 科目選択されている場合、配列にセット
                    array_push($subjects, $request['g_subject_cd_' . $i]);
                }
            }
            // 教科毎にカウントし、カウント数が1より大きい場合は重複エラーとする
            $counts = array_count_values($subjects);
            foreach ($counts as $key => $val) {
                if ($key == $value && $val > 1) {
                    // 重複エラー
                    return $fail(Lang::get('validation.duplicate_subject'));
                }
            }
        };

        // 成績欄を1行ずつチェック
        for ($i = 0; $i < $count; $i++) {
            if ($request['exam_type'] == AppConst::CODE_MASTER_43_0) {
                // 模試の必須項目
                $rules += ['score_' . $i => array_merge($ruleScore, ['required_with_all:g_subject_cd_' . $i])];
                $rules += ['full_score_' . $i => array_merge($ruleFullScore, ['required_with_all:g_subject_cd_' . $i])];
            } elseif ($request['exam_type'] == AppConst::CODE_MASTER_43_1) {
                // 定期考査の必須項目
                $rules += ['score_' . $i => array_merge($ruleScore, ['required_with_all:g_subject_cd_' . $i])];
            } elseif ($request['exam_type'] == AppConst::CODE_MASTER_43_2) {
                // 評定の必須項目
                $rules += ['rating_' . $i => array_merge($ruleScore, ['required_with_all:g_subject_cd_' . $i])];
            }

            // 教科バリデーション
            $rule = [];
            if ($i == 0) {
                // 1行目に「1件以上の必須チェック」を入れる
                $rule[] = 'array_required';
            }
            // 教科未選択で平均点などを入力している時は教科選択を必須とする
            $rule[] = 'required_with:score_' . $i . ',full_score_' . $i . ',average_' . $i . ',deviation_score_' . $i . ',rating_' . $i;

            // 共通のルールセット
            $rules += ['g_subject_cd_' . $i => array_merge($ruleSubjectCd, $rule, [$validationSubjectList], [$validationDupSubject])];
            $rules += ['average_' . $i => $ruleAvarage];
            $rules += ['deviation_score_' . $i => $ruleDeviationScore];
        }

        return $rules;
    }
}
