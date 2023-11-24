<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncAbsentTrait;
use App\Consts\AppConst;
use App\Models\AbsentApplication;
use App\Mail\AbsentApplyToOffice;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;

/**
 * 欠席申請 - コントローラ
 */
class AbsentController extends Controller
{
    // 機能共通処理：欠席申請
    use FuncAbsentTrait;

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
        // ログイン者の生徒IDを取得する。
        $account = Auth::user();
        $sid = $account->account_id;

        // 生徒に紐づくスケジュールを取得
        $lessons = $this->getStudentSchedule($sid);

        // プルダウンメニューを作成
        $scheduleList = $this->mdlGetScheduleMasterList($lessons);

        return view('pages.student.absent', [
            'rules' => $this->rulesForInput(null),
            'scheduleList' => $scheduleList
        ]);
    }

    /**
     * 校舎・教科・講師情報取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 校舎、教科、講師情報
     */
    public function getDataSelect(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $schedule_id = $request->input('id');

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------
        // ログイン者の生徒IDを取得する。
        $account = Auth::user();
        $sid = $account->account_id;

        // 生徒に紐づくスケジュールを取得
        $lessons = $this->getStudentSchedule($sid);
        // プルダウンメニューを作成
        $scheduleList = $this->mdlGetScheduleMasterList($lessons);
        // 値のチェック
        $this->guardListValue($scheduleList, $schedule_id);

        // スケジュールから、講師名・教科・校舎・校舎電話番号を取得
        $lesson = $this->getScheduleDetail($schedule_id);

        return [
            'campus_name' => $lesson->campus_name,
            'tutor_name' => $lesson->tutor_name,
            'subject_name' => $lesson->subject_name,
            'tel_campus' => $lesson->tel_campus
        ];
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

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {
            //==========================
            // 欠席申請情報の登録
            //==========================
            // 生徒IDを取得
            $account = Auth::user();
            $sid = $account->account_id;

            $absentApply = new AbsentApplication;
            $absentApply->schedule_id = $request->schedule_id;
            $absentApply->student_id = $sid;
            $absentApply->absent_reason = $request->absent_reason;
            // 状態：未対応
            $absentApply->status = AppConst::CODE_MASTER_1_0;
            // 現在日時をセット
            $absentApply->apply_date = Carbon::now();
            // 保存
            $absentApply->save();

            //==========================
            // 校舎へメール送信
            //==========================
            // 生徒名を取得
            $student_name = $this->mdlGetStudentName($sid);

            // スケジュールから校舎メールアドレスやメールに記載する情報を取得
            $lesson = $this->getScheduleDetail($request->schedule_id);

            // メール本文に記載する情報をセット
            $mail_body = [
                'student_name' => $student_name,
                'date_period' => $lesson->target_date->format('Y/m/d') . ' ' . $lesson->period_no . '時限目',
                'campus_name' => $lesson->campus_name,
                'tutor_name' => $lesson->tutor_name,
            ];

            // メール送信
            Mail::to($lesson->email_campus)->send(new AbsentApplyToOffice($mail_body));
        });

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
     * バリデーションルールを取得
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        // ログイン者の生徒IDを取得する。
        $account = Auth::user();
        $sid = $account->account_id;

        // 独自バリデーション: 重複チェック
        $validationDuplicate = function ($attribute, $value, $fail) use ($request, $sid) {

            if (!isset($request['schedule_id'])) {
                return;
            }

            // 欠席申請情報に同スケジュールID・同生徒からの申請が存在するかチェック
            $exists = AbsentApplication::where('schedule_id', $request['schedule_id'])
                ->where('student_id', $sid)
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // 独自バリデーション: リストのチェック スケジュール
        $validationScheduleList =  function ($attribute, $value, $fail) use ($sid) {

            // 生徒に紐づくスケジュールを取得
            $lessons = $this->getStudentSchedule($sid, null);

            // プルダウンメニューを作成
            $scheduleMaster = $this->mdlGetScheduleMasterList($lessons);
            if (!isset($scheduleMaster[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        $rules += AbsentApplication::fieldRules('schedule_id', ['required',$validationDuplicate, $validationScheduleList]);
        $rules += AbsentApplication::fieldRules('absent_reason', ['required']);

        return $rules;
    }
}
