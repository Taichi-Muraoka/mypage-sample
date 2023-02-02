<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Account;
use App\Models\Office;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Lang;

/**
 * 事務局アカウント管理 - コントローラ
 */
class AccountMngController extends Controller
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

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(true);

        return view('pages.admin.account_mng', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
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

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch())->validate();
        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = Office::query();

        // MEMO: 一覧検索の条件はスコープで指定する
        // 教室の検索
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchRoomcd($form);
        }
        // 氏名の検索
        $query->SearchName($form);

        // サブクエリを作成
        $subquery = $this->mdlGetRoomQuery();

        // データを取得
        $offices = $query
            ->select(
                'adm_id',
                'name',
                'roomcd',
                // 汎用マスタの教室名称2
                'rooms.room_name'
            )
            ->leftJoinSub($subquery, 'rooms', function ($join) {
                $join->on('office.roomcd', '=', 'rooms.code');
            })
            // アカウントテーブルとJOIN（削除管理者非表示対応）
            ->sdJoin(Account::class, function ($join) {
                $join->on('office.adm_id', '=', 'account.account_id')
                    ->where('account.account_type', '=', AppConst::CODE_MASTER_7_3);
            })
            ->orderby('adm_id');

        // ページネータで返却
        return $this->getListAndPaginator($request, $offices);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $id = $request->input('id');

        // クエリを作成
        $query = Office::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // サブクエリを作成
        $subquery = $this->mdlGetRoomQuery();

        $office = $query
            // IDを指定
            ->where('office.adm_id', $id)
            // データを取得
            ->select(
                'account.email',
                'office.name',
                'rooms.room_name'
            )
            // アカウントテーブルをLeftJOIN
            ->sdLeftJoin(Account::class, function ($join) {
                $join->on('office.adm_id', '=', 'account.account_id')
                    ->where('account.account_type', AppConst::CODE_MASTER_7_3);
            })
            // 教室名取得のサブクエリをLeftJOIN
            ->leftJoinSub($subquery, 'rooms', function ($join) {
                $join->on('office.roomcd', '=', 'rooms.code');
            })
            // MEMO: 取得できない場合はエラーとする
            ->firstOrFail();

        return $office;
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {

        $rules = array();
        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 教室リストを取得
            $rooms = $this->mdlGetRoomList(true);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += Office::fieldRules('name');
        $rules += Office::fieldRules('roomcd', [$validationRoomList]);

        return $rules;
    }


    //==========================
    // 登録・編集
    //==========================

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(true);

        return view('pages.admin.account_mng-input', [
            'editData' => null,
            'rules' => $this->rulesForInput(null),
            'rooms' => $rooms
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
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // 教室管理者の場合、自分の教室コード以外はエラーとする
        if (AuthEx::isRoomAdmin()) {
            $account = Auth::user();
            if ($request['roomcd'] !== $account->roomcd) {
                return $this->responseErr();
            }
        }

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {
            // Officeテーブルへのinsert
            $form = $request->only(
                'name',
                'roomcd'
            );
            $office = new Office;
            // 登録
            $office->fill($form)->save();

            // accountテーブルへのinsert
            $account = new Account;
            // 登録
            $account->account_id = $office->adm_id;
            $account->account_type = AppConst::CODE_MASTER_7_3;
            $account->password_reset = AppConst::ACCOUNT_PWRESET_0;
            $account->email = $request['email'];
            $account->password = Hash::make($request['password']);
            $account->save();
        });

        return;
    }

    /**
     * 編集画面
     *
     * @param int $admId 事務局ID
     * @return view
     */
    public function edit($admId)
    {

        // IDのバリデーション
        $this->validateIds($admId);

        // 自アカウントIDを取得
        $account = Auth::user();

        // 編集画面表示時、自アカウントの場合に削除ボタンを非活性にする
        if ($admId == $account->account_id) {
            $delBtnSts = true;
        } else {
            $delBtnSts = false;
        }

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(true);

        // クエリを作成(PKでユニークに取る)
        $office = Office::select('office.adm_id', 'account.email', 'office.name', 'office.roomcd', 'account.email as before_email')
            // アカウントテーブルをLeftJOIN
            ->sdLeftJoin(Account::class, function ($join) {
                $join->on('office.adm_id', '=', 'account.account_id')
                    ->where('account.account_type', AppConst::CODE_MASTER_7_3);
            })
            ->where('office.adm_id', $admId)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        return view('pages.admin.account_mng-input', [
            'editData' => $office,
            'rules' => $this->rulesForInput(null),
            'rooms' => $rooms,
            'delBtnSts' => $delBtnSts
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

        // officeテーブルより対象データを取得(PKでユニークに取る)
        $office = Office::where('adm_id', $request['adm_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // accountテーブルより対象データを取得(PKでユニークに取る)
        $account = Account::where('account_id', $request['adm_id'])->where('account_type', AppConst::CODE_MASTER_7_3)
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request, $office, $account) {
            // MEMO: 必ず登録する項目のみに絞る。
            // officeテーブルのupdate
            $form = $request->only(
                'name',
                'roomcd'
            );
            // 登録
            $office->fill($form)->save();

            // MEMO: 必ず登録する項目のみに絞る。
            // accountテーブルのupdate
            $account->email = $request['email'];

            // 更新時はパスワードが入力されたときだけ変更する
            if (isset($request['password']) && filled($request['password'])) {
                $account->password = Hash::make($request['password']);
            }

            // 登録
            $account->save();
        });

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

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'adm_id');

        // 自アカウントIDを取得
        $account = Auth::user();

        // Formを取得
        $form = $request->all();

        // officeテーブルより対象データを取得(PKでユニークに取る)
        Office::where('adm_id', $request['adm_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // accountテーブルより対象データを取得(PKでユニークに取る)
        $account = Account::where('account_id', $form['adm_id'])->where('account_type', AppConst::CODE_MASTER_7_3)
            // ログインユーザは削除不可
            ->where('account_id', '!=', $account->account_id)
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // officeテーブルは削除しない
        // accountテーブルのdeleteを行う前に、emailを更新する（「DEL年月日時分秒@」を付加）
        $delStr = config('appconf.delete_email_prefix') . date("YmdHis") . config('appconf.delete_email_suffix');
        $account->email = $account->email . $delStr;
        $account->save();
        // accountテーブルのdelete
        $account->delete();

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

        // 独自バリデーション: emailは重複不可なので重複チェック
        $validationMail = function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            // メールが変わった場合
            if ($request['email'] !== $request['before_email']) {

                // 対象データを取得(ユニークに取る)
                $exists = Account::where('email', $request['email'])
                    ->exists();

                if ($exists) {
                    // 登録済みメールエラー
                    return $fail(Lang::get('validation.duplicate_email'));
                }
            }
        };

        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 教室リストを取得
            $rooms = $this->mdlGetRoomList(true);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += Account::fieldRules('email', ['required', $validationMail]);

        // 変更時：adm_idがある場合は、パスワードは空白でも良いとする。(パスワードを更新しない)
        // 新規登録時は必須。更新時は任意。
        $rules += Account::fieldRules('password', ['required_without:adm_id']);

        $rules += Office::fieldRules('name', ['required']);
        $rules += Office::fieldRules('roomcd', ['required', $validationRoomList]);

        return $rules;
    }
}
