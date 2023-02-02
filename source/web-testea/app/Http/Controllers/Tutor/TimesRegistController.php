<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\TimesReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncTimesTrait;

/**
 * 回数報告 - コントローラ
 */
class TimesRegistController extends Controller
{

    // 機能共通処理：回数報告
    use FuncTimesTrait;

    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
    }

    //==========================
    // 報告
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        // 実施月プルダウンの取得
        $reportDate = $this->getTimesDateList();

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(false);

        return view('pages.tutor.times_regist', [
            'rules' => $this->rulesForInput(null),
            'reportDate' => $reportDate,
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

        // 教師IDの取得
        $account = Auth::user();
        $tid = $account->account_id;

        $form = $request->only(
            'report_date',
            'roomcd',
            'office_work',
            'other'
        );

        // 現在日の取得
        $today = date('Y-m-d');

        $timesReport = new TimesReport;
        $timesReport->tid = $tid;
        $timesReport->regist_time = $today;
        // 登録
        $timesReport->fill($form)->save();

        return;
    }

    /**
     * 詳細情報取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getDataSelect(Request $request)
    {

        // 日付のバリデーション
        $this->validateDatesFromRequest($request, 'id');

        // 教師IDの取得
        $account = Auth::user();
        $tid = $account->account_id;

        // 選択された日時を取得
        $startMonth = $request['id'];

        // 選択された教室を取得
        $roomcd = $request['roomcd'];

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------

        // 実施月プルダウンの取得
        $reportDate = $this->getTimesDateList();
        $this->guardListValue($reportDate, $startMonth);

        // 教師の所属教室のプルダウン取得
        $rooms = $this->mdlGetRoomList(false);
        $this->guardListValue($rooms, $roomcd);

        //------------------------
        // 生徒のレポートを取得
        //------------------------
        $studentReport = $this->getStudentReportList($tid, $startMonth, $roomcd);

        return [
            // 授業一覧
            'class' => $studentReport['reportList'],
            // 生徒一覧(回数)
            'student' => $studentReport['countList']
        ];
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

        // 独自バリデーション: 重複チェック（実施月＆教室コード＆教師No.）
        $validationMonth = function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            // 教師IDの取得
            $account = Auth::user();
            $tid = $account->account_id;

            // 対象データを取得(ユニークに取る)
            $exists = TimesReport::where('tid', $tid)
                ->where('report_date', $request->report_date)
                ->where('roomcd', $request->roomcd)
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // 独自バリデーション: リストのチェック 実施月
        $validationReportDateList =  function ($attribute, $value, $fail) {

            // 実施月プルダウンの取得
            $reportDate = $this->getTimesDateList();
            if (!isset($reportDate[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 教室コード
        $validationRoomsList =  function ($attribute, $value, $fail) {

            // 教師の所属教室のプルダウンの取得
            $rooms = $this->mdlGetRoomList(false);

            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += TimesReport::fieldRules('report_date', ['required', $validationMonth, $validationReportDateList]);
        $rules += TimesReport::fieldRules('office_work');
        $rules += TimesReport::fieldRules('other');
        $rules += TimesReport::fieldRules('roomcd', ['required', $validationMonth, $validationRoomsList]);

        return $rules;
    }
}
