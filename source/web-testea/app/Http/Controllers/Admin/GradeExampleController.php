<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncGradesTrait;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\CodeMaster;
use App\Models\MstCampus;
use App\Models\MstGrade;
use App\Models\MstSchool;
use App\Models\Score;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;

/**
 * 成績事例検索 - コントローラ
 */
class GradeExampleController extends Controller
{
    // 機能共通処理：生徒成績
    use FuncGradesTrait;

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

        // 学校区分リスト
        $schoolKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_39);

        // 種別リスト
        $examTypeList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_43);

        return view('pages.admin.grade_example', [
            'rules' => $this->rulesForSearch(null),
            'editData' => null,
            'rooms' => $rooms,
            'schoolKindList' => $schoolKindList,
            'examTypeList' => $examTypeList,
        ]);
    }

    /**
     * 学年リスト取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 学年リスト
     */
    public function getDataSelectGrade(Request $request)
    {
        // 学校区分コードのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // 学校区分を取得
        $schoolKind = $request->input('id');

        // 学校区分に応じた学年リストを取得
        $gradeList = $this->mdlGetGradeList($schoolKind);

        return [
            'gradeList' => $this->objToArray($gradeList),
        ];
    }

    /**
     * 定期考査または学期リスト取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 定期考査または学期リスト
     */
    public function getDataSelectExam(Request $request)
    {
        // 試験種別コードのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // 試験種別を取得
        $examType = $request->input('id');

        // 種別に応じた定期考査コードまたは学期コードを取得
        if ($examType == AppConst::CODE_MASTER_43_1) {
            $examList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_45);
        } elseif ($examType == AppConst::CODE_MASTER_43_2) {
            $examList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_44);
        } else {
            // 模試の場合はそのままreturn
            return;
        }

        return [
            'examList' => $this->objToArray($examList),
        ];
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
        $validator = Validator::make($request->all(), $this->rulesForSearch($request));
        return $validator->errors();
    }

    /**
     * 検索結果取得(一覧と一覧出力CSV用)
     * 検索結果一覧を表示するとのCSVのダウンロードが同じため共通化
     *
     * @param mixed $form 検索フォーム
     */
    private function getSearchResult($form)
    {
        // クエリ作成
        $query = Score::query();

        // MEMO: 一覧検索の条件はスコープで指定する
        // 校舎の絞り込み条件
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の校舎コードの生徒のみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithSid());
        } else {
            // 本部管理者の場合検索フォームから取得
            $query->SearchRoom($form);
        }

        // 学年の絞り込み条件
        $query->SearchGradeCd($form);

        // 種別の絞り込み条件
        if ($form['exam_type'] == AppConst::CODE_MASTER_43_0) {
            // 模試の場合
            $query->SearchPracticeExam($form);
        }
        if ($form['exam_type'] == AppConst::CODE_MASTER_43_1) {
            // 定期考査の場合
            $query->SearchRegularExamCd($form);
        }
        if ($form['exam_type'] == AppConst::CODE_MASTER_43_2) {
            // 評定の場合
            $query->SearchTermCd($form);
        }

        // 対象期間の絞り込み条件
        if ($form['exam_type'] != AppConst::CODE_MASTER_43_2) {
            // 種別が模試・定期考査の場合
            $query->SearchExamDateFrom($form);
            $query->SearchExamDateTo($form);
        }
        if ($form['exam_type'] == AppConst::CODE_MASTER_43_2) {
            // 種別が評定の場合
            $query->SearchRegistDateFrom($form);
            $query->SearchRegistDateTo($form);
        }

        // 成績情報取得
        $gradeList = $query
            ->select(
                'scores.score_id',
                'scores.student_id',
                'scores.exam_type',
                'scores.regular_exam_cd',
                'scores.practice_exam_name',
                'scores.term_cd',
                'scores.grade_cd',
                'scores.exam_date',
                'scores.regist_date',
                // 生徒情報の名前
                'students.name as student_name',
                // コードマスタの名称（種別）
                'mst_codes_43.name as exam_type_name',
                // コードマスタの名称（定期考査コード）
                'mst_codes_45.name as regular_exam_name',
                // コードマスタの名称（学期コード）
                'mst_codes_44.name as term_name',
                // 学年の名称
                'mst_grades.name as grade_name',
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
            // 学年の名称を取得
            ->sdLeftJoin(MstGrade::class, 'scores.grade_cd', '=', 'mst_grades.grade_cd')
            // ソート登録日降順
            ->orderBy('scores.regist_date', 'desc');

        return $gradeList;
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array  検索結果
     */
    public function search(Request $request)
    {
        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // 検索結果を取得
        $gradeList = $this->getSearchResult($form);

        // ページネータで返却
        return $this->getListAndPaginator($request, $gradeList);
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 校舎
        $validationCampusList =  function ($attribute, $value, $fail) {
            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 学校区分
        $validationSchoolKindList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_39);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 学年
        $validationGradeList =  function ($attribute, $value, $fail) use ($request) {
            // 全学年リストを取得
            $gradeList = $this->mdlGetGradeList();

            // requestされた学年配列グループの整形
            $group = [];
            foreach ($request['grade_cd'] as $val) {
                $group[$val] = $val;
            }

            // 学年コードとインデックスを合わせるため整形
            $gradeGroup = [];
            foreach ($gradeList as $grade) {
                $gradeGroup[$grade->code] = $grade->code;
            }

            foreach ($group as $val) {
                if (!isset($gradeGroup[$val])) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 独自バリデーション: リストのチェック 種別
        $validationExamTypeList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_43);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 定期考査コード
        $validationRegularExamCdList =  function ($attribute, $value, $fail) use ($request) {
            // 試験種別が定期考査の場合のみチェック
            if ($request['exam_type'] != AppConst::CODE_MASTER_43_1) {
                return;
            }

            // 定期考査リストを取得
            $regularExamList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_45);

            // requestされた定期考査コード配列グループの整形
            $group = [];
            foreach ($request['exam_cd'] as $val) {
                $group[$val] = $val;
            }

            // 定期考査コードとインデックスを合わせるため整形
            $regularExamGroup = [];
            foreach ($regularExamList as $regularExam) {
                $regularExamGroup[$regularExam->code] = $regularExam->code;
            }

            foreach ($group as $val) {
                if (!isset($regularExamGroup[$val])) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 学期コード
        $validationTermCdList =  function ($attribute, $value, $fail) use ($request) {
            // 試験種別が評定の場合のみチェック
            if ($request['exam_type'] != AppConst::CODE_MASTER_43_2) {
                return;
            }

            // 学期コードリストを取得
            $termList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_44);

            // requestされた学期コード配列グループの整形
            $group = [];
            foreach ($request['exam_cd'] as $val) {
                $group[$val] = $val;
            }

            // 学期コードとインデックスを合わせるため整形
            $termGroup = [];
            foreach ($termList as $term) {
                $termGroup[$term->code] = $term->code;
            }

            foreach ($group as $val) {
                if (!isset($termGroup[$val])) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 校舎
        $rules += MstCampus::fieldRules('campus_cd', [$validationCampusList]);

        // 学年
        $rules += MstSchool::fieldRules('school_kind', ['required', $validationSchoolKindList]);
        $rules += ['grade_cd' => ['required', $validationGradeList]];

        // 種別
        $rules += Score::fieldRules('exam_type', ['required', $validationExamTypeList]);
        if ($request && $request['exam_type'] != AppConst::CODE_MASTER_43_0) {
            // 定期考査・評定の場合
            $rules += ['exam_cd' => ['required', $validationRegularExamCdList, $validationTermCdList]];
        }

        // 対象期間
        // 登録日 項目のバリデーションルールをベースにする
        $ruleDate = Score::getFieldRule('regist_date');

        // FromとToの大小チェックバリデーションを追加(Fromが存在する場合のみ)
        $validateFromTo = [];
        $keyFrom = 'date_from';
        if (isset($request[$keyFrom]) && filled($request[$keyFrom])) {
            $validateFromTo = ['after_or_equal:' . $keyFrom];
        }

        $rules += ['date_from' => $ruleDate];
        $rules += ['date_to' => array_merge($validateFromTo, $ruleDate)];

        return $rules;
    }

    /**
     * 詳細取得（CSV出力の確認モーダル用）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getData(Request $request)
    {
        // ここでの処理は特になし
        return [];
    }

    /**
     * モーダル処理（CSV出力）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function execModal(Request $request)
    {
        //--------------
        // 一覧出力
        //--------------
        // formを取得
        $form = $request->all();

        // 検索結果を取得
        $gradeList = $this->getSearchResult($form)
            ->get();

        //---------------------
        // CSV出力内容を配列に保持
        //---------------------
        // CSV出力データ格納用の配列準備
        $arrayCsv = [];
        // 成績詳細ヘッダ格納用の配列準備
        $detailHeader = [];

        // 学校区分に応じた教科リストを取得する
        $subjectList = $this->mdlGetGradeSubjectList($form['school_kind']);

        // 模試の場合の出力項目
        if ($form['exam_type'] == AppConst::CODE_MASTER_43_0) {

            // 成績概要のヘッダ
            $gradeHeader = ['登録日', '学年', '生徒名', '種別', '模擬試験名', '試験日(開始日)'];

            // 最大教科数分の成績詳細ヘッダを用意する
            for ($i = 1; $i <= count($subjectList); $i++) {
                array_push($detailHeader, '教科', '得点', '満点', '平均点', '偏差値');
            }

            // 概要・詳細ヘッダを結合し、CSV出力用の配列に格納する
            $arrayCsv[] = array_merge($gradeHeader, $detailHeader);

            // 検索結果数分ループ処理(1行出力)
            foreach ($gradeList as $grade) {
                // 成績概要
                $gradeOutline = [
                    $grade->regist_date->format('Y/m/d'),
                    $grade->grade_name,
                    $grade->student_name,
                    $grade->exam_type_name,
                    $grade->practice_exam_name,
                    $grade->exam_date->format('Y/m/d'),
                ];

                // 成績情報に紐づく成績詳細情報を取得
                $gradeDetailList = $this->getScoreDetail($grade->score_id);

                // 成績詳細データ格納用の配列準備
                // MEMO:成績データ1行ごとに配列の初期化が必要なためこちらに記載
                $arrayDetail = [];

                // 教科コード順にループ処理
                foreach ($subjectList as $subject) {
                    // 該当教科コードの点数データ存在有無判定用
                    $exist = false;

                    // 成績詳細情報をループ処理
                    foreach ($gradeDetailList as $gradeDetail) {
                        // 該当教科のデータがある場合は値を$arrayDetailに格納
                        if ($subject->code == $gradeDetail->g_subject_cd) {
                            array_push($arrayDetail, $subject->value, $gradeDetail->score, $gradeDetail->full_score, $gradeDetail->average, $gradeDetail->deviation_score);

                            // データ存在判定をtrueにする
                            $exist = true;
                        }
                    }
                    // 該当教科のデータがない(存在判定がfalse)場合はnullを格納
                    if ($exist == false) {
                        array_push($arrayDetail, $subject->value, null, null, null, null);
                    }
                }

                // 概要・詳細情報を結合し、CSV出力用の配列に格納する
                $arrayCsv[] = array_merge($gradeOutline, $arrayDetail);
            }
        }

        // 定期考査の場合
        if ($form['exam_type'] == AppConst::CODE_MASTER_43_1) {

            // 成績概要のヘッダ
            $gradeHeader = ['登録日', '学年', '生徒名', '種別', '定期考査名', '試験日(開始日)'];

            // 最大教科数分の成績詳細ヘッダを用意する
            for ($i = 1; $i <= count($subjectList); $i++) {
                array_push($detailHeader, '教科', '得点', '平均点');
            }

            // 概要・詳細ヘッダを結合し、CSV出力用の配列に格納する
            $arrayCsv[] = array_merge($gradeHeader, $detailHeader);

            // 検索結果数分ループ処理(1行出力)
            foreach ($gradeList as $grade) {
                // 成績概要
                $gradeOutline = [
                    $grade->regist_date->format('Y/m/d'),
                    $grade->grade_name,
                    $grade->student_name,
                    $grade->exam_type_name,
                    $grade->regular_exam_name,
                    $grade->exam_date->format('Y/m/d'),
                ];

                // 成績情報に紐づく成績詳細情報を取得
                $gradeDetailList = $this->getScoreDetail($grade->score_id);

                // 成績詳細データ格納用の配列準備
                // MEMO:成績データ1行ごとに配列の初期化が必要なためこちらに記載
                $arrayDetail = [];

                // 教科コード順にループ処理
                foreach ($subjectList as $subject) {
                    // 該当教科コードの点数データ存在有無判定用
                    $exist = false;

                    // 成績詳細情報をループ処理
                    foreach ($gradeDetailList as $gradeDetail) {
                        // 該当教科のデータがある場合は値を$arrayDetailに格納
                        if ($subject->code == $gradeDetail->g_subject_cd) {
                            array_push($arrayDetail, $subject->value, $gradeDetail->score, $gradeDetail->average);

                            // データ存在判定をtrueにする
                            $exist = true;
                        }
                    }
                    // 該当教科のデータがない(存在判定がfalse)場合はnullを格納
                    if ($exist == false) {
                        array_push($arrayDetail, $subject->value, null, null);
                    }
                }

                // 概要・詳細情報を結合し、CSV出力用の配列に格納する
                $arrayCsv[] = array_merge($gradeOutline, $arrayDetail);
            }
        }

        // 評定の場合
        if ($form['exam_type'] == AppConst::CODE_MASTER_43_2) {

            // 成績概要のヘッダ
            $gradeHeader = ['登録日', '学年', '生徒名', '種別', '学期'];

            // 最大教科数分の成績詳細ヘッダを用意する
            $detailHeader = [];
            for ($i = 1; $i <= count($subjectList); $i++) {
                array_push($detailHeader, '教科', '評定');
            }

            // 概要・詳細ヘッダを結合し、CSV出力用の配列に格納する
            $arrayCsv[] = array_merge($gradeHeader, $detailHeader);

            // 検索結果数分ループ処理(1行出力)
            foreach ($gradeList as $grade) {
                // 成績概要
                $gradeOutline = [
                    $grade->regist_date->format('Y/m/d'),
                    $grade->grade_name,
                    $grade->student_name,
                    $grade->exam_type_name,
                    $grade->term_name,
                ];

                // 成績情報に紐づく成績詳細情報を取得
                $gradeDetailList = $this->getScoreDetail($grade->score_id);

                // 成績詳細データ格納用の配列準備
                // MEMO:成績データ1行ごとに配列の初期化が必要なためこちらに記載
                $arrayDetail = [];

                // 教科コード順にループ処理
                foreach ($subjectList as $subject) {
                    // 該当教科コードの点数データ存在有無判定用
                    $exist = false;

                    // 成績詳細情報をループ処理
                    foreach ($gradeDetailList as $gradeDetail) {
                        // 該当教科のデータがある場合は値を$arrayDetailに格納
                        if ($subject->code == $gradeDetail->g_subject_cd) {
                            array_push($arrayDetail, $subject->value, $gradeDetail->score);

                            // データ存在判定をtrueにする
                            $exist = true;
                        }
                    }
                    // 該当教科のデータがない(存在判定がfalse)場合はnullを格納
                    if ($exist == false) {
                        array_push($arrayDetail, $subject->value, null);
                    }
                }

                // 概要・詳細情報を結合し、CSV出力用の配列に格納する
                $arrayCsv[] = array_merge($gradeOutline, $arrayDetail);
            }
        }

        //---------------------
        // ファイル名の取得と出力
        //---------------------

        $filename = Lang::get(
            'message.file.grade_example_output.name',
            [
                'outputDate' => date("Ymd")
            ]
        );

        // ファイルダウンロードヘッダーの指定
        $this->fileDownloadHeader($filename, true);

        // CSVを出力する
        $this->outputCsv($arrayCsv);

        return;
    }
}
