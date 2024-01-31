<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Models\TutorFreePeriod;
use App\Models\RegularClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncWeeklyShiftTrait;

/**
 * 空き時間 - コントローラ
 */
class WeeklyShiftController extends Controller
{

    // 機能共通処理：空き時間
    use FuncWeeklyShiftTrait;

    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
    }

    //==========================
    // 登録
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        // ログイン情報取得
        $account = Auth::user();

        // 曜日の配列を取得 コードマスタより取得
        $weekdayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // 時限リストを取得（講師ID・時間割区分から）
        $periodList = $this->mdlGetPeriodListForTutor($account->account_id, AppConst::CODE_MASTER_37_0);

        // 講師の空き時間を取得する（緑色表示用）
        // 曜日コード_時限No 例：['1_1', '2_1']
        $chkData = $this->fncWksfGetFreePeriod($account->account_id);

        // レギュラー授業情報を取得する（黒色表示用）
        // 曜日コード_時限No 例：['1_1', '2_1']
        $regularData = $this->fncWksfGetRegularClass($account->account_id);

        return view('pages.tutor.weekly_shift', [
            'weekdayList' => $weekdayList,
            'periodList' => $periodList,
            'editData' => [
                'chkWs' => $chkData
            ],
            'exceptData' => $regularData,
        ]);
    }

    /**
     * 編集処理
     *
     * @param request
     * @return void
     */
    public function update(Request $request)
    {
        // MEMO: ログインアカウントのIDでデータを更新するのでガードは不要

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // MEMO: 必ず登録する項目のみに絞る。
        $form = $request->only(
            'chkWs'
        );

        // リクエストを配列に変換する
        $weekDayPeriods = $this->fncWksfSplitValue($form['chkWs']);

        // 複数の更新のためトランザクション
        DB::transaction(function () use ($weekDayPeriods) {

            // ログイン情報取得
            $account = Auth::user();

            // データを取得（レギュラースケジュール情報）
            $regulars = RegularClass::select(
                'day_cd',
                'period_no',
            )
                // 講師IDを指定
                ->where('tutor_id', $account->account_id)
                ->orderBy('day_cd')
                ->orderBy('period_no')
                ->get();

            //----------------
            // 物理削除を行う
            //----------------
            // ログインしている教師のデータを全て削除（forceDelete）
            TutorFreePeriod::where('tutor_id', $account->account_id)
                ->forceDelete();

            //----------------
            // 登録を行う（チェックされた空きコマ情報）
            //----------------
            foreach ($weekDayPeriods as $weekDayPeriod) {

                // モデルのインスンタンス生成
                $freePeriod = new TutorFreePeriod;
                $freePeriod->tutor_id = $account->account_id;
                $freePeriod->day_cd = $weekDayPeriod['dayCd'];
                $freePeriod->period_no = $weekDayPeriod['periodNo'];
                // 登録
                $freePeriod->save();
            }
            //----------------
            // 登録を行う（レギュラー授業情報）
            //----------------
            foreach ($regulars as $regular) {

                // モデルのインスンタンス生成
                $freePeriod = new TutorFreePeriod;
                $freePeriod->tutor_id = $account->account_id;
                $freePeriod->day_cd = $regular['day_cd'];
                $freePeriod->period_no = $regular['period_no'];
                // 登録
                $freePeriod->save();
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
        $validator = Validator::make($request->all(), $this->rulesForInput());
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

        // 独自バリデーション: チェックボックスの値が正しいかチェック
        $validationValue = function ($attribute, $value, $fail) {

            // 空白は無視する
            if (!filled($value)) {
                return;
            }

            // ログイン情報取得
            $account = Auth::user();

            // 曜日の配列を取得 コードマスタより取得
            $weekdayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

            // 時限リストを取得（講師ID・時間割区分から）
            $periodList = $this->mdlGetPeriodListForTutor($account->account_id, AppConst::CODE_MASTER_37_0);

            // リクエストを配列に変換する
            $weekDayPeriods = $this->fncWksfSplitValue($value);

            // リクエストの中身のチェック
            foreach ($weekDayPeriods as $weekDayPeriod) {

                // 曜日のチェック。配列のキーとして存在するか
                $key = $weekDayPeriod['dayCd'];
                if (!isset($weekdayList[$key])) {
                    // 存在しない場合はエラー
                    return $fail(Lang::get('validation.invalid_input'));
                }

                // 時限のチェック。配列のキーとして存在するか
                $key = $weekDayPeriod['periodNo'];
                if (!isset($periodList[$key])) {
                    // 存在しない場合はエラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        $rules += ['chkWs' => [$validationValue]];

        return $rules;
    }
}
