<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Libs\AuthEx;
use App\Models\MstSystem;
use Illuminate\Support\Facades\Lang;

/**
 * システムマスタ管理 - コントローラ
 */
class MasterMngSystemController extends Controller
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

        return view('pages.admin.master_mng_system');
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
        $mstSystem = MstSystem::select(
            'mst_systems.key_id',
            'mst_systems.name',
            'mst_systems.datatype_kind',
            'mst_systems.value_num',
            'mst_systems.value_str',
            'mst_systems.value_date',
            'mst_systems.change_flg',
            // コードマスタの名称(可否フラグ)
            'mst_codes.name as change_flg_name',
        )
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_systems.change_flg', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_9);
            })
            ->orderby('key_id');

        // ページネータで返却
        return $this->getListAndPaginator($request, $mstSystem);
    }

    //==========================
    // 編集
    //==========================

    /**
     * 編集画面
     *
     * @param int
     * @return view
     */
    public function edit($systemId)
    {
        // 教室管理者の場合、画面表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // クエリを作成(PKでユニークに取る)
        $mstSystem = MstSystem::select(
            'mst_systems.key_id',
            'mst_systems.name',
            'mst_systems.datatype_kind',
            'mst_systems.value_num',
            'mst_systems.value_num as value_flg',
            'mst_systems.value_str',
            'mst_systems.value_date',
        )
            ->where('key_id', $systemId)
            // 変更できないものは除く
            ->where('change_flg', '!=', AppConst::CODE_MASTER_9_1)
            ->firstOrFail();

        // 可否フラグリストを取得
        $flgList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_9);

        return view('pages.admin.master_mng_system-input', [
            'editData' => $mstSystem,
            'flgList' => $flgList,
            'rules' => $this->rulesForInput(null),
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
            'value_num',
            'value_str',
            'value_date',
            'value_flg',
        );

        // 対象データを取得
        $mstSystem = MstSystem::where('key_id', $request['key_id'])
            // 変更できないものは除く
            ->where('change_flg', '!=', AppConst::CODE_MASTER_9_1)
            ->firstOrFail();

        // 更新
        $mstSystem->name = $form['name'];
        if($request['datatype_kind'] == AppConst::SYSTEM_DATATYPE_4) {
            $mstSystem->value_num = $form['value_flg'];
        } else {
            $mstSystem->value_num = $form['value_num'];
        }
        $mstSystem->value_str = $form['value_str'];
        $mstSystem->value_date = $form['value_date'];
        $mstSystem->save();

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

        // 独自バリデーション: リストのチェック 可否フラグ
        $validationFlgList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_9);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += MstSystem::fieldRules('name', ['required']);
        // データ型種別により必須項目の分岐
        $rules += MstSystem::fieldRules('value_num', ['required_if:datatype_kind,' . AppConst::SYSTEM_DATATYPE_1]);
        $rules += MstSystem::fieldRules('value_str', ['required_if:datatype_kind,' . AppConst::SYSTEM_DATATYPE_2]);
        $rules += MstSystem::fieldRules('value_date', ['required_if:datatype_kind,' . AppConst::SYSTEM_DATATYPE_3]);
        $rules += ['value_flg' => ['required_if:datatype_kind,' . AppConst::SYSTEM_DATATYPE_4, $validationFlgList]];

        return $rules;
    }
}
