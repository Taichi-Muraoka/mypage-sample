<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\SeasonMng;
use App\Models\SeasonStudentRequest;
use App\Models\SeasonStudentPeriod;
use App\Models\SeasonStudentTime;
use App\Models\MstSubject;
use App\Models\CodeMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncSeasonTrait;

/**
 * 特別期間講習日程連絡（生徒） - コントローラ
 */
class SeasonStudentController extends Controller
{

    // 機能共通処理：特別期間講習
    use FuncSeasonTrait;

    /**
     * 教科受講回数欄の行数
     */
    const SUBJECT_LINE_MAX = 5;

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
        // 現在日を取得
        $todayYmd = date("Ymd");

        return view('pages.student.season_student', [
            'todayYmd' => $todayYmd,
        ]);
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        $account = Auth::user();
        // 現在日を取得
        $today = date("Y-m-d");

        // クエリを作成
        $query = SeasonStudentRequest::query();

        // 校舎名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // データを取得
        $SeasonRequests = $query
            ->select(
                'season_student_requests.season_student_id',
                'season_student_requests.season_cd',
                DB::raw('LEFT(season_student_requests.season_cd, 4) as year'),
                'mst_codes_38.gen_item2 as season_name',
                'room_names.room_name as campus_name',
                'season_mng.s_start_date',
                'season_mng.s_end_date',
                'season_student_requests.regist_status',
                'mst_codes_5.name as status_name',
                'season_student_requests.apply_date'
            )
            // 校舎名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('season_student_requests.campus_cd', '=', 'room_names.code');
            })
            // 特別期間管理情報とJOIN
            ->sdLeftJoin(SeasonMng::class, function ($join) {
                $join->on('season_student_requests.season_cd', '=', 'season_mng.season_cd')
                    ->on('season_student_requests.campus_cd', '=', 'season_mng.campus_cd');
            })
            // コードマスターとJOIN（登録ステータス）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('season_student_requests.regist_status', '=', 'mst_codes_5.code')
                    ->where('mst_codes_5.data_type', AppConst::CODE_MASTER_5);
            }, 'mst_codes_5')
            // コードマスターとJOIN（期間区分）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on(DB::raw('RIGHT(season_student_requests.season_cd, 2)'), '=', 'mst_codes_38.gen_item1')
                    ->where('mst_codes_38.data_type', AppConst::CODE_MASTER_38);
            }, 'mst_codes_38')
            // 自分の生徒IDで絞り込み
            ->where('student_id', $account->account_id)
            // 生徒受付開始日が当日以前のもの
            ->where('season_mng.s_start_date', '<=', $today)
            ->orderby('season_student_requests.season_cd', 'desc')
            ->orderby('season_student_requests.campus_cd');

        // ページネータで返却
        return $this->getListAndPaginator($request, $SeasonRequests);
    }

    //==========================
    // 詳細
    //==========================

    /**
     * 提出スケジュール詳細画面
     *
     * @param int $seasonStudentId 生徒連絡情報ID
     * @return view
     */
    public function detail($seasonStudentId)
    {
        // IDのバリデーション
        $this->validateIds($seasonStudentId);

        // クエリを作成（生徒連絡情報）
        $query = SeasonStudentRequest::query();

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        // データを取得
        $seasonStudent = $query
            ->select(
                'season_student_requests.season_student_id',
                'season_student_requests.season_cd',
                'season_student_requests.campus_cd',
                DB::raw('LEFT(season_student_requests.season_cd, 4) as year'),
                'mst_codes.gen_item2 as season_name',
                'room_names.room_name as campus_name',
                'season_student_requests.comment'
            )
            // 校舎名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('season_student_requests.campus_cd', '=', 'room_names.code');
            })
            // コードマスターとJOIN（期間区分）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on(DB::raw('RIGHT(season_student_requests.season_cd, 2)'), '=', 'mst_codes.gen_item1')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_38);
            })
            // IDを指定
            ->where('season_student_id', $seasonStudentId)
            // 登録済データのみ表示可
            ->where('regist_status', AppConst::CODE_MASTER_5_1)
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            ->firstOrFail();

        // クエリを作成（受講回数情報）
        $query = SeasonStudentTime::query();
        // データを取得
        $subjectTimes = $query
            ->select(
                'season_student_times.season_student_id',
                'mst_subjects.name as subject_name',
                'season_student_times.times'
            )
            // 科目名の取得
            ->sdLeftJoin(MstSubject::class, function ($join) {
                $join->on('season_student_times.subject_cd', 'mst_subjects.subject_cd');
            })
            // IDを指定
            ->where('season_student_id', $seasonStudentId)
            ->get();

        // 時限リストを取得（校舎・時間割区分から）
        $periodList = $this->mdlGetPeriodListByKind($seasonStudent['campus_cd'], AppConst::CODE_MASTER_37_1);

        // 特別期間日付リストを取得（校舎・特別期間コード指定）
        $dateList = $this->fncSasnGetSeasonDate($seasonStudent['campus_cd'], $seasonStudent['season_cd']);

        // 生徒連絡コマ情報を取得する
        // クエリを作成（生徒連絡コマ情報）
        $query = SeasonStudentPeriod::query();
        // データを取得
        $studentPeriods = $query
            ->select(
                'season_student_periods.season_student_id',
                'season_student_periods.lesson_date',
                'season_student_periods.period_no'
            )
            // IDを指定
            ->where('season_student_id', $seasonStudentId)
            ->get();

        // チェックボックスをセットするための値を生成
        // 例：['20231225_1', '20231226_2']
        $editData = [];
        foreach ($studentPeriods as $datePeriod) {
            // 配列に追加
            array_push($editData, $datePeriod->lesson_date->format('Ymd') . '_' . $datePeriod->period_no);
        }

        return view('pages.student.season_student-detail', [
            'rules' => null,
            'seasonStudent' => $seasonStudent,
            'subjectTimesList' => $subjectTimes,
            'periodList' => $periodList,
            'dateList' => $dateList,
            'editData' => [
                'chkWs' => $editData
            ]
        ]);
    }

    //==========================
    // 登録
    //==========================

    /**
     * 編集画面
     *
     * @param int $seasonStudenId
     * @return void
     */
    public function edit($seasonStudentId)
    {

        // IDのバリデーション
        $this->validateIds($seasonStudentId);

        // クエリを作成
        $query = SeasonStudentRequest::query();

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        // データを取得
        $seasonStudent = $query
            ->select(
                'season_student_requests.season_student_id',
                'season_student_requests.season_cd',
                'season_student_requests.campus_cd',
                DB::raw('LEFT(season_student_requests.season_cd, 4) as year'),
                'mst_codes.gen_item2 as season_name',
                'room_names.room_name as campus_name'
            )
            // 校舎名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('season_student_requests.campus_cd', '=', 'room_names.code');
            })
            // コードマスターとJOIN（期間区分）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on(DB::raw('RIGHT(season_student_requests.season_cd, 2)'), '=', 'mst_codes.gen_item1')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_38);
            })
            // IDを指定
            ->where('season_student_id', $seasonStudentId)
            // 未登録時のみ表示可
            ->where('regist_status', AppConst::CODE_MASTER_5_0)
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            ->firstOrFail();

        // 科目リストを取得
        $subjects = $this->mdlGetSubjectList();

        // 時限リストを取得（校舎・時間割区分から）
        $periodList = $this->mdlGetPeriodListByKind($seasonStudent['campus_cd'], AppConst::CODE_MASTER_37_1);

        // 特別期間日付リストを取得（校舎・特別期間コード指定）
        $dateList = $this->fncSasnGetSeasonDate($seasonStudent['campus_cd'], $seasonStudent['season_cd']);

        return view('pages.student.season_student-input', [
            'rules' => $this->rulesForInput(null),
            'seasonStudent' => $seasonStudent,
            'subjects' => $subjects,
            'periodList' => $periodList,
            'dateList' => $dateList,
            'editData' => [
                'chkWs' => null
            ]
        ]);
    }

    /**
     * 登録処理
     *
     * @param request
     * @return void
     */
    public function update(Request $request)
    {
        // MEMO: ログインアカウントのIDでデータを更新するのでガードは不要

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // MEMO: 必ず登録する項目のみに絞る。
        $form = $request->only(
            'campus_cd',
            'season_student_id',
            'season_cd',
            'chkWs',
            'subject_cd_1',
            'times_1',
            'subject_cd_2',
            'times_2',
            'subject_cd_3',
            'times_3',
            'subject_cd_4',
            'times_4',
            'subject_cd_5',
            'times_5',
            'comment',
        );

        // リクエストを配列に変換する
        $datePeriods = $this->fncSasnSplitValue($form['chkWs']);

        // 複数の更新のためトランザクション
        DB::transaction(function () use ($form, $datePeriods) {

            // ログイン情報取得
            $account = Auth::user();

            // 対象データを取得(IDでユニークに取る)
            $seasonStudent = SeasonStudentRequest::where('season_student_id', $form['season_student_id'])
                ->where('student_id', $account->account_id)
                ->where('season_cd', $form['season_cd'])
                ->where('campus_cd', $form['campus_cd'])
                // 自分の生徒IDのみにガードを掛ける
                ->where($this->guardStudentTableWithSid())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            //----------------
            // 物理削除
            //----------------
            // 対象の生徒連絡コマ情報を全て削除（forceDelete）
            SeasonStudentPeriod::where('season_student_id', $form['season_student_id'])
                ->forceDelete();

            // 対象の生徒受講回数情報を全て削除（forceDelete）
            SeasonStudentTime::where('season_student_id', $form['season_student_id'])
                ->forceDelete();

            //----------------
            // 登録処理
            //----------------
            // 生徒連絡コマ情報の登録
            foreach ($datePeriods as $datePeriod) {
                // モデルのインスンタンス生成
                $seasonPeriod = new SeasonStudentPeriod;
                $seasonPeriod->season_student_id = $form['season_student_id'];
                $seasonPeriod->lesson_date = $datePeriod['lesson_date'];
                $seasonPeriod->period_no = $datePeriod['period_no'];
                // 登録
                $seasonPeriod->save();
            }

            // 生徒受講回数情報の登録
            for ($i = 1; $i <= self::SUBJECT_LINE_MAX; $i++) {
                if (isset($form['subject_cd_' . $i]) && filled($form['subject_cd_' . $i])) {
                    // モデルのインスンタンス生成
                    $seasonTimes = new SeasonStudentTime;
                    $seasonTimes->season_student_id = $form['season_student_id'];
                    $seasonTimes->subject_cd =  $form['subject_cd_' . $i];
                    $seasonTimes->times =  $form['times_' . $i];
                    // 登録
                    $seasonTimes->save();
                }
            }

            //----------------
            // 更新処理
            //----------------
            $seasonStudent->apply_date = date('Y-m-d');
            $seasonStudent->comment = $form['comment'];
            $seasonStudent->regist_status = AppConst::CODE_MASTER_5_1;
            // 更新
            $seasonStudent->save();
        });

        return;
    }

    /**
     * バリデーション(登録用)
     *
     * @param request
     * @return mixed バリデーション結果
     */
    public function validationForInput(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInput($request));
        return $validator->errors();

        return;
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {

        $rules = array();

        // 独自バリデーション: チェックボックスの値が正しいかチェック
        $validationValue = function ($attribute, $value, $fail) use ($request) {

            // 空白は無視する
            if (!filled($value)) {
                return;
            }

            if (!$request) {
                return;
            }
            if (
                !$request->filled('campus_cd') || !$request->filled('season_cd')
            ) {
                // 検索項目がrequestにない場合はチェックしない
                return;
            }

            // 特別期間日付リストを取得（校舎・特別期間コード指定）
            $dateIdList = $this->fncSasnGetSeasonDate($request['campus_cd'], $request['season_cd']);

            // 時限リストを取得（校舎・時間割区分から）
            $periodList = $this->mdlGetPeriodListByKind($request['campus_cd'], AppConst::CODE_MASTER_37_1);

            // リクエストを配列に変換する
            $datePeriods = $this->fncSasnSplitValue($value);
            // リクエストの中身のチェック
            foreach ($datePeriods as $datePeriod) {

                // 日付のチェック。配列に存在するか
                if (!in_array($datePeriod['dateId'], array_column($dateIdList, 'dateId'))) {
                    // 存在しない場合はエラー
                    return $fail(Lang::get('validation.invalid_input'));
                }

                // 時限のチェック。配列のキーとして存在するか
                $key = $datePeriod['period_no'];
                if (!isset($periodList[$key])) {
                    // 存在しない場合はエラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 独自バリデーション: リストのチェック 教科
        $validationSubjectList =  function ($attribute, $value, $fail) {

            // 科目リストを取得
            $list = $this->mdlGetSubjectList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 教科の重複チェック
        $validationDupSubject =  function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            $subjects = [];
            // 1件以上存在するかチェック
            for ($i = 0; $i < self::SUBJECT_LINE_MAX; $i++) {
                if (isset($request['subject_cd_' . $i]) && filled($request['subject_cd_' . $i])) {
                    // 科目選択されている場合、配列にセット
                    array_push($subjects, $request['subject_cd_' . $i]);
                }
            }
            // 科目毎にカウントし、カウント数が1より大きい場合は重複エラーとする
            $counts = array_count_values($subjects);
            foreach ($counts as $key => $val) {
                if ($key == $value && $val > 1) {
                    // 科目重複エラー
                    return $fail(Lang::get('validation.duplicate_subject'));
                }
            }
        };

        // 独自バリデーション: 教科の存在チェック(1件以上)
        $validationSubjectTimes = function ($attribute, $value, $parameters) use ($request) {

            if (!$request) {
                return true;
            }

            // 1件以上存在するかチェック
            for ($i = 0; $i < self::SUBJECT_LINE_MAX; $i++) {
                if (isset($request['subject_cd_' . $i]) && filled($request['subject_cd_' . $i])) {
                    // 指定された
                    return true;
                }
            }
            // エラー
            return false;
        };

        // 独自バリデーション: 生徒登録期間内チェック
        $validationDateTerm =  function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return true;
            }
            if (
                !$request->filled('campus_cd') || !$request->filled('season_cd')
            ) {
                // 検索項目がrequestにない場合はチェックしない
                return true;
            }

            // 生徒登録開始日・終了日を取得
            $seasonMng = SeasonMng::select(
                's_start_date',
                's_end_date'
            )
                ->where('season_cd', $request['season_cd'])
                ->where('campus_cd', $request['campus_cd'])
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            if (!$seasonMng['s_start_date'] || !$seasonMng['s_end_date']) {
                // null（未設定）の場合、登録期間外エラーとする
                return false;
            }
            // 現在日を取得
            $today = date("Y-m-d");
            // $today が 登録期間内か
            if (
                strtotime($today) < strtotime($seasonMng['s_start_date']) ||
                strtotime($today) > strtotime($seasonMng['s_end_date'])
            ) {
                // 登録期間外エラー
                return false;
            }
            return true;
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += SeasonStudentRequest::fieldRules('campus_cd');
        $rules += SeasonStudentRequest::fieldRules('season_cd');
        $rules += SeasonStudentRequest::fieldRules('season_student_id');
        $rules += SeasonStudentRequest::fieldRules('comment');
        $rules += ['chkWs' => [$validationValue]];

        // Laravelの独自バリデーションは、空白の時は呼んでくれないので、
        // 今回のように存在チェックの場合は、以下のように指定し空の場合も呼んでもらう
        Validator::extendImplicit('array_required', $validationSubjectTimes);

        // 登録期間チェックも、以下のように指定し呼んでもらう
        Validator::extendImplicit('out_of_range_regist_term', $validationDateTerm);
        $rules += ['s_date_term' => ['out_of_range_regist_term']];

        // 生徒実施回数情報 項目のバリデーションルールをベースにする
        $ruleSubject = SeasonStudentTime::getFieldRule('subject_cd');
        $ruleTimes = SeasonStudentTime::getFieldRule('times');
        for ($i = 1; $i <= self::SUBJECT_LINE_MAX; $i++) {
            $rule = [];
            if ($i == 1) {
                // 1行目に「1件以上の必須チェック」を入れる
                $rule[] = 'array_required';
            }
            $rule[] = 'required_with:times_' . $i;
            $rules += ['subject_cd_' . $i =>  array_merge($ruleSubject, $rule, [$validationSubjectList], [$validationDupSubject])];
            $rules += ['times_' . $i =>  array_merge($ruleTimes, ['required_with:subject_cd_' . $i],)];
        }

        return $rules;
    }
}
