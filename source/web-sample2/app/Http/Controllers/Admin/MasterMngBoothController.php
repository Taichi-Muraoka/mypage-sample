<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\MstBooth;
use Illuminate\Support\Facades\Lang;
use App\Libs\AuthEx;

/**
 * ブースマスタ管理 - コントローラ
 */
class MasterMngBoothController extends Controller
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

        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 用途種別リストを取得
        $kindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_41);

        return view('pages.admin.master_mng_booth', [
            'rules' => $this->rulesForSearch(null),
            'rooms' => $rooms,
            'kindList' => $kindList,
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

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = MstBooth::query();

        // MEMO: 一覧検索の条件はスコープで指定する
        // 校舎の絞り込み条件
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        } else {
            // 本部管理者の場合検索フォームから取得
            $query->SearchCampusCd($form);
        }

        // 用途種別の絞り込み条件
        $query->SearchUsageKind($form);

        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        // データを取得
        $mstBooth = $query
            ->select(
                'mst_booths.booth_id as id',
                'mst_booths.booth_cd',
                'mst_booths.name',
                'mst_booths.usage_kind',
                // コードマスタの名称(用途種別)
                'mst_codes.name as kind_name',
                'mst_booths.disp_order',
                'mst_booths.campus_cd',
                // 校舎名
                'campus_names.room_name as campus_name'
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('mst_booths.campus_cd', '=', 'campus_names.code');
            })
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_booths.usage_kind', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_41);
            })
            ->orderby('campus_cd')->orderby('disp_order');

        // ページネータで返却
        return $this->getListAndPaginator($request, $mstBooth);
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
        $validationCampusList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 用途種別
        $validationKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_41);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 校舎コード
        $rules += MstBooth::fieldRules('campus_cd', [$validationCampusList]);
        // 用途種別
        $rules += MstBooth::fieldRules('usage_kind', [$validationKindList]);

        return $rules;
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

        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 用途種別リストを取得
        $kindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_41);

        return view('pages.admin.master_mng_booth-input', [
            'rules' => $this->rulesForInput(null),
            'rooms' => $rooms,
            'kindList' => $kindList,
            'editData' => null,
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

        $form = $request->only(
            // 教室管理者の場合の校舎コードのチェックはバリデーション(validationRoomList)で行っている
            'campus_cd',
            'booth_cd',
            'usage_kind',
            'name',
            'disp_order'
        );

        $mstBooth = new MstBooth;

        // 登録(ガードは不要)
        $mstBooth->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int
     * @return view
     */
    public function edit($boothId)
    {
        // 教室管理者の場合、画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // IDのバリデーション
        $this->validateIds($boothId);

        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 用途種別リストを取得
        $kindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_41);

        // クエリを作成(PKでユニークに取る)
        $mstBooth = MstBooth::where('booth_id', $boothId)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        return view('pages.admin.master_mng_booth-input', [
            'rules' => $this->rulesForInput(null),
            'rooms' => $rooms,
            'kindList' => $kindList,
            'editData' => $mstBooth
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

        // MEMO: 必ず登録する項目のみに絞る。
        $form = $request->only(
            // 教室管理者の場合の校舎コードのチェックはバリデーション(validationRoomList)で行っている
            'campus_cd',
            'booth_cd',
            'usage_kind',
            'name',
            'disp_order'
        );

        // 対象データを取得(IDでユニークに取る)
        $mstBooth = MstBooth::where('booth_id', $request['booth_id'])
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $mstBooth->fill($form)->save();

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

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'booth_id');

        // Formを取得
        $form = $request->all();

        // 対象データを取得(IDでユニークに取る)
        $mstBooth = MstBooth::where('booth_id', $form['booth_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 物理削除
        $mstBooth->forceDelete();

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

        // 独自バリデーション: リストのチェック 用途種別
        $validationKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_41);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 重複チェック
        $validationKey = function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            // 対象データを取得(UNIQUEキーで取得)
            $mstBooth = MstBooth::where('campus_cd', $request['campus_cd'])->where('booth_cd', $request['booth_cd']);

            // 変更時は自分のキー以外を検索
            if (filled($request['booth_id'])) {
                $mstBooth->where('booth_id', '!=', $request['booth_id']);
            }

            $exists = $mstBooth->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // 独自バリデーション: システム定義ブースコードチェック
        $validationDefinedKey = function ($attribute, $value, $fail) {

            if ($value == config('appconf.timetable_boothId') || $value == config('appconf.transfer_boothId')) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += MstBooth::fieldRules('booth_id');
        $rules += MstBooth::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += MstBooth::fieldRules('booth_cd', ['required', $validationKey, $validationDefinedKey]);
        $rules += MstBooth::fieldRules('usage_kind', ['required', $validationKindList]);
        $rules += MstBooth::fieldRules('name', ['required']);
        $rules += MstBooth::fieldRules('disp_order', ['required']);

        return $rules;
    }
}
