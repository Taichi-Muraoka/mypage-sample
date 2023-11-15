<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\SeasonMng;
use App\Models\SeasonTutorRequest;
use App\Models\SeasonTutorPeriod;
use App\Models\TutorCampus;
use App\Models\CodeMaster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncSeasonTrait;

/**
 * 特別期間講習日程連絡（講師） - コントローラ
 */
class SeasonTutorController extends Controller
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
        $account = Auth::user();
        // 現在日を取得
        $today = date("Y-m-d");

        // クエリを作成（特別期間講習管理）
        $query = SeasonMng::query();

        // 日程連絡可能な特別期間のコードを取得
        $SeasonMng = $query
            ->select(
                'season_mng.season_cd'
            )
            // 講師所属情報とJOIN
            ->sdJoin(TutorCampus::class, function ($join) {
                $join->on('season_mng.campus_cd', '=', 'tutor_campuses.campus_cd');
            })
            // 自分の講師IDで絞り込み
            ->where('tutor_campuses.tutor_id', $account->account_id)
            // 講師受付期間内
            ->where('season_mng.t_start_date', '<=', $today)
            ->where('season_mng.t_end_date', '>=', $today)
            // 登録済みのものを除外
            ->whereNotExists(function ($query) use ($account) {
                $query->select(DB::raw(1))
                    ->from('season_tutor_requests')
                    ->whereColumn('season_mng.season_cd', 'season_tutor_requests.season_cd')
                    ->where('season_tutor_requests.tutor_id', $account->account_id)
                    // delete_dt条件の追加
                    ->whereNull('season_tutor_requests.deleted_at');
            })
            ->orderby('season_mng.season_cd')
            ->distinct()
            ->first();

        $seasonCd = "";
        $newBtnDisabled = true;
        // 日程連絡可能な特別期間がある場合
        if ($SeasonMng) {
            // 登録ボタンを押下可とし、取得した特別期間コードをセット
            $seasonCd = $SeasonMng->season_cd;
            $newBtnDisabled = false;
        }

        return view('pages.tutor.season_tutor', [
            'newBtnDisabled' => $newBtnDisabled,
            'seasonCd' => $seasonCd,
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

        // クエリを作成（講師日程連絡情報）
        $query = SeasonTutorRequest::query();

        // データを取得
        $SeasonRequests = $query
            ->select(
                'season_tutor_requests.season_tutor_id',
                'season_tutor_requests.season_cd',
                DB::raw('LEFT(season_tutor_requests.season_cd, 4) as year'),
                'mst_codes.gen_item2 as season_name',
                'season_tutor_requests.apply_date',
            )
            // コードマスターとJOIN（期間区分）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on(DB::raw('RIGHT(season_tutor_requests.season_cd, 2)'), '=', 'mst_codes.gen_item1')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_38);
            }, 'mst_codes')
            // 自分の講師IDで絞り込み
            ->where('tutor_id', $account->account_id)
            ->orderby('season_tutor_requests.apply_date', 'desc')
            ->orderby('season_tutor_requests.season_cd', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $SeasonRequests);
    }

    //==========================
    // 詳細
    //==========================

    /**
     * 提出スケジュール詳細画面
     *
     * @param int $seasonTutorId 講師連絡情報ID
     * @return view
     */
    public function detail($seasonTutorId)
    {
        $account = Auth::user();

        // クエリを作成（講師連絡情報）
        $query = SeasonTutorRequest::query();

        // データを取得
        $seasonTutor = $query
            ->select(
                'season_tutor_requests.season_tutor_id',
                'season_tutor_requests.season_cd',
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
            // 自分の講師IDのみにガードを掛ける
            ->where($this->guardTutorTableWithTid())
            ->firstOrFail();

        // 時限リストを取得（講師ID・時間割区分から）
        $periodList = $this->mdlGetPeriodListForTutor($account->account_id, AppConst::CODE_MASTER_37_1);

        // 特別期間日付リストを取得（講師ID・特別期間コード指定）
        $dateList = $this->fncSasnGetSeasonDateForTutor($account->account_id, $seasonTutor->season_cd);

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
            ->get();

        // チェックボックスをセットするための値を生成
        // 例：['20231225_1', '20231226_2']
        $editData = [];
        foreach ($tutorPeriods as $datePeriod) {
            // 配列に追加
            array_push($editData, $datePeriod->lesson_date->format('Ymd') . '_' . $datePeriod->period_no);
        }

        return view('pages.tutor.season_tutor-detail', [
            'seasonTutor' => $seasonTutor,
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
     * 登録画面
     *
     * @param string $seasonCd
     * @return view
     */
    public function new($seasonCd)
    {
        $account = Auth::user();

        // パラメータ（特別期間コード）のチェック
        SeasonMng::select(
            'season_cd'
        )
            // 講師所属情報とJOIN
            ->sdJoin(TutorCampus::class, function ($join) {
                $join->on('season_mng.campus_cd', '=', 'tutor_campuses.campus_cd');
            })
            // 特別期間コードで絞り込み
            ->where('season_cd', $seasonCd)
            // 自分の講師IDで絞り込み
            ->where('tutor_campuses.tutor_id', $account->account_id)
            ->firstOrFail();

        // パラメータ切り分け
        $year = substr($seasonCd, 0, 4);
        $dateKind = substr($seasonCd, 4, 2);

        // 特別期間名の取得
        $seasonName = CodeMaster::select('gen_item2 as season_name')
            ->where('data_type', AppConst::CODE_MASTER_38)
            ->where('gen_item1', $dateKind)
            ->firstOrFail();

        $seasonName['year'] = $year;

        // 時限リストを取得（講師ID・時間割区分から）
        $periodList = $this->mdlGetPeriodListForTutor($account->account_id, AppConst::CODE_MASTER_37_1);

        // 特別期間日付リストを取得（講師ID・特別期間コード指定）
        $dateList = $this->fncSasnGetSeasonDateForTutor($account->account_id, $seasonCd);

        return view('pages.tutor.season_tutor-input', [
            'rules' => $this->rulesForInput(null),
            'seasonName' => $seasonName,
            'periodList' => $periodList,
            'dateList' => $dateList,
            'editData' => [
                'chkWs' => null,
                'season_cd' => $seasonCd,
            ]
        ]);
    }

    /**
     * 登録処理
     *
     * @param request
     * @return void
     */
    public function create(Request $request)
    {

        // MEMO: ログインアカウントのIDでデータを更新するのでガードは不要

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // MEMO: 必ず登録する項目のみに絞る。
        $form = $request->only(
            'season_cd',
            'chkWs',
            'comment',
        );

        // リクエストを配列に変換する
        $datePeriods = $this->fncSasnSplitValue($form['chkWs']);

        // 複数の更新のためトランザクション
        DB::transaction(function () use ($form, $datePeriods) {

            // ログイン情報取得
            $account = Auth::user();

            //----------------
            // 登録処理
            //----------------
            // 講師連絡情報の登録
            $seasonRequest = new SeasonTutorRequest;
            $seasonRequest->tutor_id = $account->account_id;
            $seasonRequest->season_cd = $form['season_cd'];
            $seasonRequest->apply_date = date('Y-m-d');
            $seasonRequest->comment = $form['comment'];
            // 登録
            $seasonRequest->save();

            // 講師連絡コマ情報の登録
            foreach ($datePeriods as $datePeriod) {
                // モデルのインスンタンス生成
                $seasonPeriod = new SeasonTutorPeriod;
                $seasonPeriod->season_tutor_id = $seasonRequest->season_tutor_id;
                $seasonPeriod->lesson_date = $datePeriod['lesson_date'];
                $seasonPeriod->period_no = $datePeriod['period_no'];
                // 登録
                $seasonPeriod->save();
            }
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
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        // ログイン情報取得
        $account = Auth::user();
        $tutor_id = $account->account_id;

        $rules = array();

        // 独自バリデーション: チェックボックスの値が正しいかチェック
        $validationValue = function ($attribute, $value, $fail) use ($request, $tutor_id) {

            // 空白は無視する
            if (!filled($value)) {
                return;
            }
            if (!$request) {
                return;
            }
            if (!$request->filled('season_cd')) {
                // 検索項目がrequestにない場合はチェックしない
                return;
            }

            // 特別期間日付リストを取得（講師ID・特別期間コード指定）
            $dateIdList = $this->fncSasnGetSeasonDateForTutor($tutor_id, $request['season_cd']);

            // 時限リストを取得（講師ID・時間割区分から）
            $periodList = $this->mdlGetPeriodListForTutor($tutor_id, AppConst::CODE_MASTER_37_1);

            // リクエストを配列に変換する
            $datePeriods = $this->fncSasnSplitValue($value);
            $this->debug($datePeriods);
            $this->debug($dateIdList);
            // リクエストの中身のチェック
            foreach ($datePeriods as $datePeriod) {

                // 日付のチェック。配列に存在するか
                if (!in_array($datePeriod['dateId'], $dateIdList)) {
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

        // 独自バリデーション: 講師登録期間内チェック
        $validationDateTerm =  function ($attribute, $value, $fail) use ($request, $tutor_id) {

            if (!$request) {
                return true;
            }
            if (!$request->filled('season_cd')) {
                // 検索項目がrequestにない場合はチェックしない
                return true;
            }

            // 講師登録開始日・終了日を取得
            $seasonMng = SeasonMng::select(
                DB::raw('min(t_start_date) as t_start_date'),
                DB::raw('max(t_end_date) as t_end_date')
            )
                // 講師所属情報とJOIN
                ->sdJoin(TutorCampus::class, function ($join) {
                    $join->on('season_mng.campus_cd', '=', 'tutor_campuses.campus_cd');
                })
                // 特別期間コードで絞り込み
                ->where('season_cd', $request['season_cd'])
                // 自分の講師IDで絞り込み
                ->where('tutor_campuses.tutor_id', $tutor_id)
                ->firstOrFail();

            if (!$seasonMng['t_start_date'] || !$seasonMng['t_end_date']) {
                // null（未設定）の場合、登録期間外エラーとする
                return false;
            }
            // 現在日を取得
            $today = date("Y-m-d");
            // $today が 登録期間内か
            if (
                strtotime($today) < strtotime($seasonMng['t_start_date']) ||
                strtotime($today) > strtotime($seasonMng['t_end_date'])
            ) {
                // 登録期間外エラー
                $this->debug("out_of_range_regist_term");
                return false;
            }
            return true;
        };

        // 独自バリデーション: 日程連絡重複チェック
        $validationDupRequest =  function ($attribute, $value, $fail) use ($request, $tutor_id) {

            if (!$request) {
                return true;
            }
            if (!$request->filled('season_cd')) {
                // 検索項目がrequestにない場合はチェックしない
                return true;
            }

            $exists = SeasonTutorRequest::
                // 特別期間コードで絞り込み
                where('season_cd', $request['season_cd'])
                // 自分の講師IDで絞り込み
                ->where('tutor_id', $tutor_id)
                ->exists();

            if ($exists) {
                // 登録済みの場合
                $this->debug("duplicate_data");
                return false;
            }
            return true;
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += SeasonTutorRequest::fieldRules('season_cd');
        $rules += SeasonTutorRequest::fieldRules('comment');
        $rules += ['chkWs' => [$validationValue]];

        // 入力項目に関わらないバリデーションは以下のように指定する
        // 登録期間チェック
        Validator::extendImplicit('out_of_range_regist_term', $validationDateTerm);
        // 日程連絡重複チェック
        Validator::extendImplicit('duplicate_data', $validationDupRequest);
        $rules += ['t_date_term' => ['out_of_range_regist_term', 'duplicate_data']];

        return $rules;
    }
}
