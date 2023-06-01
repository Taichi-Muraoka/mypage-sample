<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\WeeklyShift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncAgreementTrait;

/**
 * 特別期間講習日程連絡（生徒） - コントローラ
 */
class SeasonTutorController extends Controller
{

    // 機能共通処理：空き時間
    //use FuncAgreementTrait;

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
        //$rooms = $this->mdlGetRoomList(false);

        // ステータスのプルダウン取得
        //$statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);

        return view('pages.tutor.season_tutor', [
            //'statusList' => $statusList,
            //'rooms' => $rooms,
            'editData' => null,
            'rules' => $this->rulesForSearch()
        ]);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        return;
    }

    /**
     * バリデーション(検索用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForSearch(Request $request)
    {
        return;
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // ページネータで返却（モック用）
        return $this->getListAndPaginatorMock();
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {
        return;
    }
    //==========================
    // 詳細
    //==========================

    /**
     * 提出スケジュール詳細画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function detail($sid)
    {

        //==========================
        // モック用処理
        //==========================
        // 曜日の配列を取得 コードマスタより取得
        //$weekdayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // 時限リスト
        $timeList = array(
            '1時限目','2時限目','3時限目','4時限目','5時限目','6時限目','7時限目',
        );

        // 期間リスト（日付・曜日）
        $dayList = array(
            '03/27(月)','03/28(火)','03/29(水)','03/30(木)','03/31(金)','04/01(土)',
            '04/03(月)','04/04(火)','04/05(水)','04/06(木)','04/07(金)','04/08(土)'
        );

        // コロンを除いた値をIDとして扱う
        // 管理画面では送信しないが、教師画面と統一した
        $timeIdList = [];
        foreach ($timeList as $time) {
            //$timeId = str_replace(":", "", $time);
            $timeId = str_replace("時限目", "", $time);
            array_push($timeIdList, $timeId);
        }

        // 教師の空き時間を取得する
        //$weeklyShift = WeeklyShift::where('tid', $tid)
        //    ->get();

        // チェックボックスをセットするための値を生成
        // 例：['1_1030', '2_1030']
        //$editData = [];
        $editData = ['1_1', '1_2'];
        //foreach ($weeklyShift as $ws) {
        //    // 配列に追加
        //    array_push($editData, $ws->weekdaycd . '_' . $ws->start_time->format('Hi'));
        //}

        // 教師名を取得する
        //$teacher = $this->getTeacherName($tid);

        return view('pages.tutor.season_tutor-detail', [
            //'weekdayList' => $weekdayList,
            'periodList' => $timeList,
            'periodIdList' => $timeIdList,
            'dayList' => $dayList,
            'editData' => [
                'chkWs' => $editData
            ],
            //'extRirekisho' => $teacher,
        ]);
    }

    //==========================
    // 登録
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function new()
    {
        //==========================
        // 既存処理
        //==========================
        // // 曜日の配列を取得 コードマスタより取得
        // $weekdayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // // 時間帯 コードマスタにないのでappconfに定義した。
        // $timeList = config('appconf.weekly_shift_time');

        // // コロンを除いた値をIDとして扱う
        // $timeIdList = [];
        // foreach ($timeList as $time) {
        //     $timeId = str_replace(":", "", $time);
        //     array_push($timeIdList, $timeId);
        // }

        // // ログイン情報取得
        // $account = Auth::user();

        // // 教師の空き時間を取得する
        // $weeklyShift = WeeklyShift::where('tid', $account->account_id)
        //     ->get();

        // // チェックボックスをセットするための値を生成
        // // 例：['1_1030', '2_1030']
        // $editData = [];
        // foreach ($weeklyShift as $ws) {
        //     // 配列に追加
        //     array_push($editData, $ws->weekdaycd . '_' . $ws->start_time->format('Hi'));
        // }

        // return view('pages.tutor.weekly_shift', [
        //     'weekdayList' => $weekdayList,
        //     'timeList' => $timeList,
        //     'timeIdList' => $timeIdList,
        //     'editData' => [
        //         'chkWs' => $editData
        //     ]
        // ]);

        //==========================
        // モック用処理
        //==========================
        // 曜日の配列を取得 コードマスタより取得
        //$weekdayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // 時限リスト
        $timeList = array(
            '1時限目','2時限目','3時限目','4時限目','5時限目','6時限目','7時限目',
        );

        // 期間リスト（日付・曜日）
        $dayList = array(
            '03/27(月)','03/28(火)','03/29(水)','03/30(木)','03/31(金)','04/01(土)',
            '04/03(月)','04/04(火)','04/05(水)','04/06(木)','04/07(金)','04/08(土)'
        );

        // コロンを除いた値をIDとして扱う
        $timeIdList = [];
        foreach ($timeList as $time) {
            //$timeId = str_replace(":", "", $time);
            $timeId = str_replace("時限目", "", $time);
            array_push($timeIdList, $timeId);
        }

        // ログイン情報取得
        $account = Auth::user();

        // 教師の空き時間を取得する
        //$weeklyShift = WeeklyShift::where('tid', $account->account_id)
        //    ->get();

        // チェックボックスをセットするための値を生成
        // 例：['1_1030', '2_1030']
        $editData = [];
        //foreach ($weeklyShift as $ws) {
        //    // 配列に追加
        //    array_push($editData, $ws->weekdaycd . '_' . $ws->start_time->format('Hi'));
        //}

        return view('pages.tutor.season_tutor-input', [
            //'weekdayList' => $weekdayList,
            'periodList' => $timeList,
            'periodIdList' => $timeIdList,
            'dayList' => $dayList,
            'editData' => [
                'chkWs' => $editData
            ]
        ]);
    }

    /**
     * 編集処理
     *
     * @param request
     * @return void
     */
    public function create(Request $request)
    {
        //==========================
        // 既存処理
        //==========================
        // // MEMO: ログインアカウントのIDでデータを更新するのでガードは不要

        // // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        // Validator::make($request->all(), $this->rulesForInput())->validate();

        // // MEMO: 必ず登録する項目のみに絞る。
        // $form = $request->only(
        //     'chkWs'
        // );

        // // リクエストを配列に変換する
        // $weekDayTime = $this->splitValue($form['chkWs']);

        // // 複数の更新のためトランザクション
        // DB::transaction(function () use ($weekDayTime) {

        //     // ログイン情報取得
        //     $account = Auth::user();

        //     //----------------
        //     // 物理削除を行う
        //     //----------------

        //     // ログインしている教師のデータを全て削除（forceDelete）
        //     WeeklyShift::where('tid', $account->account_id)
        //         ->forceDelete();

        //     //----------------
        //     // 登録を行う
        //     //----------------

        //     foreach ($weekDayTime as $weekDayTimeObj) {

        //         // モデルのインスンタンス生成
        //         $weeklyShift = new WeeklyShift;
        //         $weeklyShift->tid = $account->account_id;
        //         $weeklyShift->weekdaycd = $weekDayTimeObj['weekdaycd'];

        //         // time型なので秒を追加する
        //         $weeklyShift->start_time = $weekDayTimeObj['start_time'] . ':00';

        //         // 登録
        //         $weeklyShift->save();
        //     }
        // });

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
        //==========================
        // 既存処理
        //==========================
        // // リクエストデータチェック
        // $validator = Validator::make($request->all(), $this->rulesForInput());
        // return $validator->errors();

        return;
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput()
    {

        $rules = array();

        // 独自バリデーション: チェックボックスの値が正しいかチェック
        $validationValue = function ($attribute, $value, $fail) {

            // 空白は無視する
            if (!filled($value)) {
                return;
            }

            // 曜日の配列を取得 コードマスタより取得
            $weekdayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

            // 時間帯 コードマスタにないのでappconfに定義した。
            $timeList = config('appconf.weekly_shift_time');

            // リクエストを配列に変換する
            $weekDayTime = $this->splitValue($value);

            // リクエストの中身のチェック
            foreach ($weekDayTime as $weekDayTimeObj) {

                // 曜日のチェック。配列のキーとして存在するか
                $key = $weekDayTimeObj['weekdaycd'];
                if (!isset($weekdayList[$key])) {
                    // 存在しない場合はエラー
                    return $fail(Lang::get('validation.invalid_input'));
                }

                // 時間帯のチェック。配列に存在するか
                if (!in_array($weekDayTimeObj['start_time'], $timeList)) {
                    // 存在しない場合はエラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        $rules += ['chkWs' => [$validationValue]];

        return $rules;
    }

    /**
     * チェックボックスの値を分割する
     * ある程度フォーマットのチェックは行っている
     *
     * @param string $value チェックボックスの値
     * @return array 配列
     */
    private function splitValue($value)
    {
        // パラメータ：
        // カンマ区切りで曜日_時間 のように飛んでくる。
        // 1_2030,2_1230
        //
        // 戻り値：
        // array (
        //   0 =>
        //   array (
        //     'weekdaycd' => '1',
        //     'start_time' => '20:30',
        //   ),
        //   1 =>
        //   array (
        //     'weekdaycd' => '2',
        //     'start_time' => '12:30',
        //   ),
        // )

        $rtn = [];

        // 空の場合は処理なし
        if (!filled($value)) {
            return $rtn;
        }

        // カンマ区切りで分割
        $commaList = explode(",", $value);

        foreach ($commaList as $commaVal) {

            // アンダーバー区切りで分割
            $weekDayTime = explode("_", $commaVal);

            // 必ず2つになる
            if (count($weekDayTime) != 2) {
                // 不正なエラー
                $this->illegalResponseErr();
            }

            // 1730 -> 17:30
            $timeId = $weekDayTime[1];
            if (strlen($timeId) != 4) {
                // 不正なエラー
                $this->illegalResponseErr();
            }

            array_push($rtn, [
                'weekdaycd' => $weekDayTime[0],
                // コロン区切りの時間にする
                'start_time' => substr($timeId, 0, 2) . ':' . substr($timeId, 2, 2),
            ]);
        }

        return $rtn;
    }
}
