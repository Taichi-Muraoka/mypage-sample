<?php

namespace App\Http\Controllers\Student;

use App\Consts\AppConst;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Conference;
use App\Models\ConferenceDate;

/**
 * 面談日程調整 - コントローラ
 */
class ConferenceController extends Controller
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
    // 申請
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        return view('pages.student.conference', [
            'rules' => $this->rulesForInput(null),
            'rooms' => $rooms,
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
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // ログイン者の生徒No.を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        try {
            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($request, $account_id) {
                $conference = new Conference;

                // 面談連絡情報insert
                $conference->student_id = $account_id;
                $conference->campus_cd = $request['campus_cd'];
                $conference->comment = $request['comment'];
                $conference->status = AppConst::CODE_MASTER_5_0;
                $conference->apply_date = now();

                $conference->save();

                // 面談日程情報insert
                $conference_date = new ConferenceDate;
                $conference_date->conference_id = $conference->conference_id;
                $conference_date->request_no = 1;
                $conference_date->conference_date = $request['conference_date1'];
                $conference_date->start_time = $request['start_time1'];
                $conference_date->save();
                // 第2希望ありの場合
                if (($request['conference_date2'] and $request['start_time2']) != null) {
                    $conference_date = new ConferenceDate;
                    $conference_date->conference_id = $conference->conference_id;
                    $conference_date->request_no = 2;
                    $conference_date->conference_date = $request['conference_date2'];
                    $conference_date->start_time = $request['start_time2'];
                    $conference_date->save();
                    // 第3希望ありの場合
                    if (($request['conference_date3'] and $request['start_time3']) != null) {
                        $conference_date = new ConferenceDate;
                        $conference_date->conference_id = $conference->conference_id;
                        $conference_date->request_no = 3;
                        $conference_date->conference_date = $request['conference_date3'];
                        $conference_date->start_time = $request['start_time3'];
                        $conference_date->save();
                    }
                }
            });
        } catch (\Exception  $e) {
            // この時点では補足できないエラーとして、詳細は返さずエラーとする
            Log::error($e);
            return $this->illegalResponseErr();
        }

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
     * バリデーションルールを取得(事前に渡す用)
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {

        $rules = array();

        // 独自バリデーション: リストのチェック 生徒ID
        $validationStudentList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $students = $this->mdlGetStudentList();
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 希望日時が現在日付時刻以降のみ登録可とする
        $validationDateTime = function ($attribute, $value, $fail) use ($request) {

            $targetNo = substr($attribute, -1);
            if (!$request['conference_date' . $targetNo]) {
                // 対応する希望日が未入力の場合、ここでは検出せずスキップする
                return;
            }
            if (!$this->dtCheckTimeFormat($value)) {
                // 時刻の形式エラーとなる場合、ここでは検出せずスキップする
                return;
            }
            $request_datetime = $request['conference_date' . $targetNo] . " " . $value;
            $this->debug($request_datetime);
            $today = date("Y/m/d H:i");

            if (strtotime($request_datetime) < strtotime($today)) {
                // 日時チェックエラー
                return $fail(Lang::get('validation.after_or_equal_time'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += Conference::fieldRules('student_id', [$validationStudentList]);
        $rules += Conference::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += Conference::fieldRules('comment');
        // 面談希望日・開始時刻 項目のバリデーションルールをベースにする
        $ruleConferenceDate = ConferenceDate::getFieldRule('conference_date');
        $ruleStartTime = ConferenceDate::getFieldRule('start_time');
        $rules += ['conference_date1' => array_merge($ruleConferenceDate, ['required'])];
        $rules += ['start_time1' => array_merge($ruleStartTime, ['required', $validationDateTime])];
        $rules += ['conference_date2' => array_merge($ruleConferenceDate, ['required_with:start_time2'])];
        $rules += ['start_time2' => array_merge($ruleStartTime, ['required_with:conference_date2', $validationDateTime])];
        $rules += ['conference_date3' => array_merge($ruleConferenceDate, ['required_with:start_time3'])];
        $rules += ['start_time3' => array_merge($ruleStartTime, ['required_with:conference_date3', $validationDateTime])];

        return $rules;
    }
}
