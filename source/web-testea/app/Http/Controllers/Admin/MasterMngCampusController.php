<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Models\CodeMaster;
use App\Models\MstCampus;
use App\Models\MstSystem;
use App\Models\SeasonMng;
use App\Models\YearlySchedulesImport;

/**
 * 校舎マスタ管理 - コントローラ
 */
class MasterMngCampusController extends Controller
{
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
        // 教室管理者の場合、画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        return view('pages.admin.master_mng_campus');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // 教室管理者の場合、処理を行わない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // データを取得
        $mstCampus = MstCampus::select(
            'mst_campuses.campus_cd',
            'mst_campuses.name',
            'mst_campuses.short_name',
            'mst_campuses.email_campus',
            'mst_campuses.tel_campus',
            'mst_campuses.disp_order',
            // コードマスタの名称(非表示フラグ)
            'mst_codes.name as is_hidden_name',
        )
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_campuses.is_hidden', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_11);
            })
            ->orderby('disp_order');

        // ページネータで返却
        return $this->getListAndPaginator($request, $mstCampus);
    }

    //==========================
    // 登録・編集・削除
    //==========================

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        // 教室管理者の場合、画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 非表示フラグリストを取得
        $hiddenFlagList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_11);

        return view('pages.admin.master_mng_campus-input', [
            'rules' => $this->rulesForInput(null),
            'editData' => null,
            'hiddenFlagList' => $hiddenFlagList,
        ]);
    }

    /**
     * 登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function create(Request $request)
    {
        // 教室管理者の場合、処理を行わない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // 登録する項目のみに絞る
        $form = $request->only(
            'campus_cd',
            'name',
            'short_name',
            'email_campus',
            'tel_campus',
            'disp_order',
            'is_hidden',
        );

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($form) {
            //-------------------------
            // 校舎マスタ登録
            //-------------------------
            $mstCampus = new MstCampus;
            $mstCampus->fill($form)->save();

            //-------------------------
            // 年間予定取込情報
            // 当年度・翌年度のレコード作成
            //-------------------------
            // システムマスタ「現年度」を取得
            $currentYear = MstSystem::select('value_num')
                ->where('key_id', AppConst::SYSTEM_KEY_ID_1)
                ->first();

            // 当年度・翌年度のレコード作成
            for ($i = 0; $i < 2; $i++) {
                // 同年度・同校舎コードでレコードが存在するかチェック
                $exists = YearlySchedulesImport::where('school_year', $currentYear->value_num + $i)
                    ->where('campus_cd', $mstCampus->campus_cd)
                    ->exists();

                // 存在しない場合は新規作成
                if (!$exists) {
                    $yearlySchedulesImport = new YearlySchedulesImport;
                    $yearlySchedulesImport->school_year = $currentYear->value_num + $i;
                    $yearlySchedulesImport->campus_cd = $mstCampus->campus_cd;
                    $yearlySchedulesImport->save();
                }
            }

            //-------------------------
            // 特別期間講習管理
            // 当年度・翌年度春期のレコード作成
            //-------------------------
            // 特別期間コードリスト取得
            $seasonCodes = $this->mdlFormatSeasonCd();

            foreach ($seasonCodes as $seasonCd) {
                // 同特別期間コード・同校舎コードでレコードが存在するかチェック
                $exists = SeasonMng::where('season_cd', $seasonCd)
                    ->where('campus_cd', $mstCampus->campus_cd)
                    ->exists();

                // 存在しない場合は新規作成
                if (!$exists) {
                    $seasonMng = new SeasonMng;
                    $seasonMng->season_cd = $seasonCd;
                    $seasonMng->campus_cd = $mstCampus->campus_cd;
                    $seasonMng->save();
                }
            }
        });

        return;
    }

    /**
     * 編集画面
     *
     * @param int
     * @return view
     */
    public function edit($campusCd)
    {
        // 教室管理者の場合、画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 非表示フラグリストを取得
        $hiddenFlagList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_11);

        // クエリを作成(PKでユニークに取る)
        $mstCampus = Mstcampus::select(
            'mst_campuses.campus_cd',
            // hidden用
            'mst_campuses.campus_cd as _campus_cd',
            'mst_campuses.name',
            'mst_campuses.short_name',
            'mst_campuses.email_campus',
            'mst_campuses.tel_campus',
            'mst_campuses.disp_order',
            'mst_campuses.is_hidden',
        )
            ->where('campus_cd', $campusCd)
            ->firstOrFail();

        return view('pages.admin.master_mng_campus-input', [
            'rules' => $this->rulesForInput(null),
            'editData' => $mstCampus,
            'hiddenFlagList' => $hiddenFlagList,
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
        // 教室管理者の場合、処理を行わない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // 更新する項目のみに絞る
        $form = $request->only(
            'name',
            'short_name',
            'email_campus',
            'tel_campus',
            'disp_order',
            'is_hidden',
        );

        // 対象データを取得(hiddenのコードでユニークに取る)
        $mstCampus = MstCampus::where('campus_cd', $request['_campus_cd'])
            ->firstOrFail();

        // 更新
        $mstCampus->fill($form)->save();

        return;
    }

    /**
     * 削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function delete(Request $request)
    {
        // 教室管理者の場合、処理を行わない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 校舎コードのバリデーション
        $this->validateIdsFromRequest($request, '_campus_cd');

        // 対象データを取得
        $mstCampus = MstCampus::where('campus_cd', $request['_campus_cd'])
            ->firstOrFail();

        // 物理削除
        $mstCampus->forceDelete();

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
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        // 独自バリデーション: リストのチェック 非表示フラグ
        $validationHiddenFlagList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_11);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 重複チェック
        $validationDuplicate = function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            // 既に存在する校舎コードかチェック
            $exists = MstCampus::where('campus_cd', $request['campus_cd'])
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        $rules = array();
        $rules += MstCampus::fieldRules('campus_cd', ['required_without:_campus_cd', $validationDuplicate]);
        $rules += MstCampus::fieldRules('name', ['required']);
        $rules += MstCampus::fieldRules('short_name', ['required']);
        $rules += MstCampus::fieldRules('email_campus');
        $rules += MstCampus::fieldRules('tel_campus');
        $rules += MstCampus::fieldRules('disp_order', ['required']);
        $rules += MstCampus::fieldRules('is_hidden', ['required', $validationHiddenFlagList]);

        return $rules;
    }
}
