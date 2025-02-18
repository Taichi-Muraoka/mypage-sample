<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\SeasonMng;
use App\Models\Schedule;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FuncSeasonTrait;

/**
 * 特別期間講習コマ組み - コントローラ
 */
class SeasonMngController extends Controller
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
        // 現在日を取得
        $todayYmd = date("Ymd");

        return view('pages.admin.season_mng', [
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
        // 一覧画面表示対象の特別期間コード取得
        $dispSeasonCd = $this->fncSasnGetDispSeasonCd();

        // クエリを作成
        $query = SeasonMng::query();

        // 校舎名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // データを取得
        $SeasonMng = $query
            ->select(
                'season_mng.season_mng_id',
                'season_mng.season_cd',
                DB::raw('LEFT(season_mng.season_cd, 4) as year'),
                'mst_codes_38.gen_item2 as season_name',
                'room_names.room_name as campus_name',
                'season_mng.s_start_date',
                'season_mng.s_end_date',
                'season_mng.t_start_date',
                'season_mng.t_end_date',
                'mst_codes_48.name as status_name',
                'mst_codes_48.gen_item1 as status_name_bef',
                'season_mng.confirm_date',
            )
            // 校舎名の取得
            ->joinSub($room_names, 'room_names', function ($join) {
                $join->on('season_mng.campus_cd', '=', 'room_names.code');
            })
            // コードマスターとJOIN（確定ステータス）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('season_mng.status', '=', 'mst_codes_48.code')
                    ->where('mst_codes_48.data_type', AppConst::CODE_MASTER_48);
            }, 'mst_codes_48')
            // コードマスターとJOIN（期間区分）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on(DB::raw('RIGHT(season_mng.season_cd, 2)'), '=', 'mst_codes_38.gen_item1')
                    ->where('mst_codes_38.data_type', AppConst::CODE_MASTER_38);
            }, 'mst_codes_38')
            // 表示対象の特別期間を絞り込み
            ->where('season_mng.season_cd', '<=', $dispSeasonCd)
            ->orderby('season_mng.season_cd', 'desc')
            ->orderby('season_mng.campus_cd');

        // ページネータで返却
        return $this->getListAndPaginator($request, $SeasonMng);
    }

    //==========================
    // 編集
    //==========================

    /**
     * 編集画面
     *
     * @param int $seasonMngId 特別期間管理ID
     * @return view
     */
    public function edit($seasonMngId)
    {
        // IDのバリデーション
        $this->validateIds($seasonMngId);

        // 現在日を取得
        $today = date("Y-m-d");

        // クエリを作成
        $query = SeasonMng::query();

        // 校舎名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // データを取得
        $seasonMng = $query
            ->select(
                'season_mng.season_mng_id',
                'season_mng.season_cd',
                'season_mng.campus_cd',
                DB::raw('LEFT(season_mng.season_cd, 4) as year'),
                'mst_codes_38.gen_item2 as season_name',
                'room_names.room_name as campus_name',
                'season_mng.s_start_date',
                'season_mng.s_end_date',
                'season_mng.t_start_date',
                'season_mng.t_end_date',
            )
            // 校舎名の取得
            ->joinSub($room_names, 'room_names', function ($join) {
                $join->on('season_mng.campus_cd', '=', 'room_names.code');
            })
            // コードマスターとJOIN（期間区分）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on(DB::raw('RIGHT(season_mng.season_cd, 2)'), '=', 'mst_codes_38.gen_item1')
                    ->where('mst_codes_38.data_type', AppConst::CODE_MASTER_38);
            }, 'mst_codes_38')
            // IDを指定
            ->where('season_mng_id', $seasonMngId)
            // 生徒受付終了日がNULL または 当日以降
            ->where(function ($orQuery) use ($today) {
                $orQuery
                    ->where('season_mng.s_end_date', '>=', $today)
                    ->orWhereNull('season_mng.s_end_date');
            })
            ->firstOrFail();

        return view('pages.admin.season_mng-edit', [
            'editData' => $seasonMng,
            'rules' => $this->rulesForInput()
        ]);
    }

    /**
     * 編集処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function update(Request $request)
    {
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // MEMO: 必ず登録する項目のみに絞る。
        $form = $request->only(
            't_start_date',
            't_end_date',
            's_start_date',
            's_end_date'
        );

        // 対象データを取得(IDでユニークに取る)
        $seasonMng = SeasonMng::where('season_mng_id', $request['season_mng_id'])
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $seasonMng->fill($form)->save();

        return;
    }

    /**
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
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
    private function rulesForInput()
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

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += SeasonMng::fieldRules('season_mng_id');
        $rules += SeasonMng::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += SeasonMng::fieldRules('t_start_date', ['required']);
        $rules += SeasonMng::fieldRules('t_end_date', ['required', 'after_or_equal:t_start_date']);
        $rules += SeasonMng::fieldRules('s_start_date', ['required']);
        $rules += SeasonMng::fieldRules('s_end_date', ['required', 'after_or_equal:s_start_date']);

        return $rules;
    }

    //==========================
    // コマ組み確定処理
    //==========================
    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        return [];
    }

    /**
     * モーダル処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function execModal(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'season_mng_id');

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            // 現在日を取得
            $todayYmd = date("Ymd");

            // データを取得（特別期間講習管理）
            $seasonMng = SeasonMng::select(
                'season_mng.season_mng_id',
                'season_mng.season_cd',
                'season_mng.campus_cd',
                'season_mng.s_start_date',
                'season_mng.s_end_date'
            )
                // IDを指定
                ->where('season_mng_id', $request['season_mng_id'])
                // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                ->firstOrFail();

            // 生徒受付期間外の場合はエラーとする
            if (
                !$seasonMng->s_start_date || !$seasonMng->s_end_date
                || $seasonMng->s_start_date->format('Ymd') > $todayYmd
                || $seasonMng->s_end_date->format('Ymd') < $todayYmd
            ) {
                // 不正なエラー
                $this->illegalResponseErr();
            }

            // 特別期間日付リストを取得（校舎コード・特別期間コード指定）
            $dateList = $this->fncSasnGetSeasonDate($seasonMng->campus_cd, $seasonMng->season_cd);

            // 仮登録スケジュール情報を取得
            $schedules = Schedule::select(
                'schedules.schedule_id'
            )
                // 校舎コードで絞り込み
                ->where('schedules.campus_cd', $seasonMng->campus_cd)
                // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                // 授業種別＝特別期間講習
                ->where('schedules.lesson_kind', AppConst::CODE_MASTER_31_2)
                // 仮登録フラグ＝仮登録
                ->where('schedules.tentative_status', AppConst::CODE_MASTER_36_1)
                // 対象の特別期間の日付範囲
                ->whereIn('schedules.target_date', array_column($dateList, 'dateYmd'))
                ->orderBy('schedules.target_date')
                ->orderBy('schedules.period_no')
                ->get();

            //----------------
            // スケジュール情報 更新処理
            //----------------
            foreach ($schedules as $schedule) {
                // 対象データを取得(IDでユニークに取る)
                $updSchedule = Schedule::where('schedules.schedule_id', $schedule->schedule_id)
                    // 該当データがない場合はエラーを返す
                    ->firstOrFail();

                // 仮登録フラグ＝本登録
                $updSchedule->tentative_status = AppConst::CODE_MASTER_36_0;
                // 更新
                $updSchedule->save();
            }

            //----------------
            // 特別期間講習管理 更新処理
            //----------------
            // 確定ステータス＝確定済
            $seasonMng->status = AppConst::CODE_MASTER_48_1;
            $seasonMng->confirm_date = date('Y-m-d');
            // 更新
            $seasonMng->save();
        });

        return;
    }
}
