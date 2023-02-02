<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\RoomHoliday;
use Illuminate\Support\Facades\Lang;
use App\Libs\AuthEx;

/**
 * 休業日登録 - コントローラ
 */
class RoomHolidayController extends Controller
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
        $rooms = $this->mdlGetRoomList(false);

        return view('pages.admin.room_holiday', [
            'rules' => $this->rulesForSearch(null),
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
        $validator = Validator::make($request->all(), $this->rulesForSearch($request));
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
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = RoomHoliday::query();

        // MEMO: 一覧検索の条件はスコープで指定する
        // 教室の検索
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchRoomcd($form);
        }

        // 日付の検索
        $query->SearchHolidayDateFrom($form);
        $query->SearchHolidayDateTo($form);

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // データを取得
        $roomholiday = $query
            ->select(
                'room_holiday_id',
                'holiday_date',
                // 教室名
                'room_names.room_name'
            )
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('room_holiday.roomcd', '=', 'room_names.code');
            })
            ->orderby('holiday_date')->orderby('roomcd');

        // ページネータで返却
        return $this->getListAndPaginator($request, $roomholiday);
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch(?Request $request)
    {

        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 教室リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += RoomHoliday::fieldRules('roomcd', [$validationRoomList]);

        // 休業日 項目のバリデーションルールをベースにする
        $ruleHolidayDate = RoomHoliday::getFieldRule('holiday_date');

        // FromとToの大小チェックバリデーションを追加(Fromが存在する場合のみ)
        $validateFromTo = [];
        $keyFrom = 'holiday_date_from';
        if (isset($request[$keyFrom]) && filled($request[$keyFrom])) {
            $validateFromTo = ['after_or_equal:' . $keyFrom];
        }

        // 日付From・Toのバリデーションの設定
        $rules += ['holiday_date_from' => $ruleHolidayDate];
        $rules += ['holiday_date_to' => array_merge($validateFromTo, $ruleHolidayDate)];

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
        $rooms = $this->mdlGetRoomList(false);

        return view('pages.admin.room_holiday-input', [
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

        $form = $request->only(
            // 教室管理者の場合の教室コードのチェックはバリデーション(validationRoomList)で行っている
            'roomcd',
            'holiday_date'
        );

        $roomholidays = new RoomHoliday;

        // 登録(ガードは不要)
        $roomholidays->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int $roomHolidayId 教室休業日ID
     * @return view
     */
    public function edit($roomHolidayId)
    {

        // IDのバリデーション
        $this->validateIds($roomHolidayId);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // クエリを作成(PKでユニークに取る)
        $roomholiday = RoomHoliday::where('room_holiday_id', $roomHolidayId)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        return view('pages.admin.room_holiday-input', [
            'editData' => $roomholiday,
            'rules' => $this->rulesForInput(null),
            'rooms' => $rooms
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
            // 教室管理者の場合の教室コードのチェックはバリデーション(validationRoomList)で行っている
            'roomcd',
            'holiday_date'
        );

        // 対象データを取得(PKでユニークに取る)
        $roomHoliday = RoomHoliday::where('room_holiday_id', $request['room_holiday_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $roomHoliday->fill($form)->save();

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
        $this->validateIdsFromRequest($request, 'room_holiday_id');

        // Formを取得
        $form = $request->all();

        // 対象データを取得(IDでユニークに取る)
        $roomholiday = RoomHoliday::where('room_holiday_id', $form['room_holiday_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 削除
        $roomholiday->delete();

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

        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 教室リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 重複チェック
        $validationKey = function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            // 対象データを取得(PKでユニークに取る)
            $roomHoliday = RoomHoliday::where('roomcd', $request['roomcd'])->where('holiday_date', $request['holiday_date']);

            // 変更時は自分のキー以外を検索
            if (filled($request['room_holiday_id'])) {
                $roomHoliday->where('room_holiday_id', '!=', $request['room_holiday_id']);
            }

            $exists = $roomHoliday->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += RoomHoliday::fieldRules('room_holiday_id');
        $rules += RoomHoliday::fieldRules('roomcd', ['required', $validationRoomList]);
        $rules += RoomHoliday::fieldRules('holiday_date', ['required', $validationKey]);

        return $rules;
    }
}
