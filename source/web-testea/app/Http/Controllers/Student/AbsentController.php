<?php

namespace App\Http\Controllers\Student;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Lang;
use App\Models\ExtSchedule;
use App\Models\AbsentApply;
use App\Models\ExtRirekisho;
use App\Consts\AppConst;
use App\Mail\AbsentApplyToOffice;
use App\Models\ExtGenericMaster;
use App\Http\Controllers\Traits\FuncAbsentTrait;
use Illuminate\Support\Carbon;

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

        // ログイン者の生徒No.を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        // 生徒に紐づくスケジュールを取得
        $lessons = $this->getStudentSchedule($account_id, null);

        // レギュラーと個別講習のプルダウンメニューを作成
        $scheduleMaster = $this->mdlGetScheduleMasterList($lessons);

        // 教師名のプルダウンメニューを作成
        $home_teachers = $this->getTeacherList($account_id);

        return view('pages.student.absent', [
            'rules' => $this->rulesForInput(null),
            'home_teachers' => $home_teachers,
            'editData' => null,
            'scheduleMaster' => $scheduleMaster
        ]);
    }

    /**
     * 初期画面(IDを指定して直接遷移)
     * カレンダーのモーダルの欠席申請から遷移する
     *
     * @return view
     */
    public function direct($scheduleId)
    {
        // IDのバリデーション
        $this->validateIds($scheduleId);

        // ログイン者の生徒No.を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        // 翌日を取得（明日以降のスケジュール取得を分かりやすくするため）
        $tomorrow = date("Y/m/d", strtotime('+1 day'));

        // directでの欠席申請を表示するかどうか
        $query = ExtSchedule::query();
        $direct = $query
            ->where('ext_schedule.id', '=', $scheduleId)
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // 明日以降
            ->where('ext_schedule.lesson_date', '>=', $tomorrow)
            ->where(function ($orQuery) {
                // 出欠・振替コードが2（振替）以外 ※NULLのものを含む
                $orQuery->whereNotIn('ext_schedule.atd_status_cd', [AppConst::ATD_STATUS_CD_2])
                    ->orWhereNull('ext_schedule.atd_status_cd');
            })
            ->exists();

        if (!$direct) {
            $this->illegalResponseErr();
        }

        // 生徒に紐づくスケジュールを取得
        $lessons = $this->getStudentSchedule($account_id, null);

        // レギュラー＋個別講習のプルダウンメニューを作成
        $scheduleMaster = $this->mdlGetScheduleMasterList($lessons);

        $editData = [
            // 個別教室の場合なので選択する
            'lesson_type' => AppConst::CODE_MASTER_8_1,
            'id' => $scheduleId
        ];

        // 教師名のプルダウンメニューを作成
        $home_teachers = $this->getTeacherList($account_id);

        return view('pages.student.absent', [
            'rules' => $this->rulesForInput(null),
            'home_teachers' => $home_teachers,
            'editData' => $editData,
            'scheduleMaster' => $scheduleMaster
        ]);
    }

    /**
     * 教室・教師情報取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 教室、教師情報
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

        // ログイン者の生徒No.を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        // 生徒に紐づくスケジュールを取得
        $lessons = $this->getStudentSchedule($account_id, null);

        // レギュラーと個別講習のプルダウンメニューを作成
        $scheduleMaster = $this->mdlGetScheduleMasterList($lessons);
        $this->guardListValue($scheduleMaster, $schedule_id);

        //------------------------
        // スケジュールから、教師名・教室を取得
        //------------------------

        $lesson = $this->getScheduleDetail($schedule_id);

        return [
            'class_name' => $lesson->room_name_full,
            'teacher_name' => $lesson->name
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
        // MEMO: ログインアカウントのIDでデータを登録するのでガードは不要

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // 生徒IDを取得
        $account = Auth::user();
        $sid = $account->account_id;

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        if ($request->input('lesson_type') == AppConst::CODE_MASTER_8_1) {
            //---------------
            // 個別教室登録
            //---------------

            $id = $request->input('id');

            // スケジュールidから授業日・授業開始時間・教室・教師を取得する。
            // idに対するガードはvalidationScheduleListの方で掛けるのでここでは不要
            $query = ExtSchedule::query();
            $lesson = $query
                ->select(
                    'roomcd',
                    'lesson_date',
                    'start_time',
                    'ext_schedule.tid',
                    'ext_rirekisho.name AS teacher_name',
                    'room_name_full'
                )
                ->leftJoinSub($room_names, 'room_names', function ($join) {
                    $join->on('ext_schedule.roomcd', '=', 'room_names.code');
                })
                ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                    $join->on('ext_rirekisho.tid', '=', 'ext_schedule.tid');
                })
                ->where('ext_schedule.id', '=', $id)
                ->firstOrFail();

            $roomcd = $lesson->roomcd;
            $lesson_date = $lesson->lesson_date->format('Y/m/d');
            $start_time = $lesson->start_time->format('H:i');
            $tid = $lesson->tid;
            $room_name = $lesson->room_name_full;
            $teacher_name = $lesson->teacher_name;
        } elseif ($request->input('lesson_type') == AppConst::CODE_MASTER_8_2) {
            //---------------
            // 家庭教師登録
            //---------------

            $id = null;
            $tid = $request->input('tid');

            // 教師名取得
            $query = ExtRirekisho::query();
            $teacher = $query
                ->select(
                    'name'
                )
                ->where('ext_rirekisho.tid', '=', $tid)
                ->firstOrFail();

            // 教室名取得（家庭教師）
            $query = ExtGenericMaster::query();
            $room = $query
                ->select(
                    'name1'
                )
                ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_000_101)
                ->where('ext_generic_master.code', '=', AppConst::EXT_GENERIC_MASTER_101_900)
                ->firstOrFail();

            $roomcd = AppConst::EXT_GENERIC_MASTER_101_900;
            $lesson_date = strtotime($request['lesson_date']);
            $lesson_date = date('Y/m/d', $lesson_date);
            $start_time = $request['start_time'];
            $room_name = $room->name1;
            $teacher_name = $teacher->name;
        } else {
            $this->illegalResponseErr();
        }

        // フォームから受け取った値を格納
        $form = $request->only(
            'lesson_type',
            'absent_reason'
        );

        // 本日の日付をセット
        $now = Carbon::now();

        // 保存
        $absentApply = new AbsentApply;
        $absentApply->sid = $sid;
        $absentApply->tid = $tid;
        $absentApply->roomcd = $roomcd;
        $absentApply->id = $id;
        $absentApply->lesson_date = $lesson_date;
        $absentApply->start_time = $start_time;
        $absentApply->apply_time = $now;
        $absentApply->state = AppConst::CODE_MASTER_1_0;
        $res = $absentApply->fill($form)->save();

        // save成功時のみメール送信
        if ($res) {

            $mail_body = [
                'sid' => $sid,
                'name' => $account->name,
                'datetime' => $lesson_date . ' ' . $start_time,
                'room_name' => $room_name,
                'teacher_name' => $teacher_name,
                'absent_reason' => $form['absent_reason']
            ];

            // 欠席申請メール送信用の、事務局用メールアドレスを設定(env)から取得
            $email = config('appconf.mail_absent_to_address');
            Mail::to($email)->send(new AbsentApplyToOffice($mail_body));
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

        // 独自バリデーション: 重複チェック(個別教室登録用)
        $validationDuplicateRegular = function ($attribute, $value, $fail) use ($request) {

            if (!isset($request['id'])) {
                // requiredでチェックするのでreturn
                return;
            }

            // 対象データを取得(PKでユニークに取る)
            // スケジュールID
            $exists = AbsentApply::where('id', $request['id'])
                // 授業種別
                ->where('lesson_type', AppConst::CODE_MASTER_8_1)
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // 独自バリデーション: 重複チェック(家庭教師登録用)
        $validationDuplicateHomeTeacher = function ($attribute, $value, $fail) use ($request) {

            if (!isset($request['lesson_date'])) {
                // requiredでチェックするのでreturn
                return;
            }

            // 生徒IDを取得
            $account = Auth::user();
            $sid = $account->account_id;

            $lesson_date = $request['lesson_date'];
            $start_time = $request['start_time'];

            // 対象データを取得(PKでユニークに取る)
            $exists = AbsentApply::where('sid', $sid)
                ->where('lesson_date', $lesson_date)
                ->where('start_time', $start_time)
                // 授業種別
                ->where('lesson_type', AppConst::CODE_MASTER_8_2)
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // 独自バリデーション: 授業日チェック(家庭教師登録用)
        $validationDateHomeTeacher = function ($attribute, $value, $fail) use ($request) {

            if (!isset($request['lesson_date'])) {
                // requiredでチェックするのでreturn
                return;
            }

            // 授業日が翌日以降のみ登録可とする
            $lessonDate = $request['lesson_date'];
            $today = date("Y-m-d");

            if (strtotime($lessonDate) <= strtotime($today)) {
                // 授業日チェックエラー
                return $fail(Lang::get('validation.after_tomorrow'));
            }
        };

        // 独自バリデーション: リストのチェック スケジュール
        $validationScheduleList =  function ($attribute, $value, $fail) {

            // ログイン者の生徒No.を取得する。
            $account = Auth::user();
            $account_id = $account->account_id;

            // 生徒に紐づくスケジュールを取得
            $lessons = $this->getStudentSchedule($account_id, null);

            // レギュラーと個別講習のプルダウンメニューを作成
            $scheduleMaster = $this->mdlGetScheduleMasterList($lessons);
            if (!isset($scheduleMaster[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 教師名
        $validationHomeTeacherList =  function ($attribute, $value, $fail) {

            // ログイン者の生徒No.を取得する。
            $account = Auth::user();
            $account_id = $account->account_id;

            // 教師名のプルダウンメニューを作成
            $home_teachers = $this->getTeacherList($account_id);
            if (!isset($home_teachers[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 授業種別（ラジオ）
        $validationRadioLessonType = function ($attribute, $value, $fail) use ($request) {

            // ラジオの値のチェック
            if (
                $request->input('lesson_type') != AppConst::CODE_MASTER_8_1 &&
                $request->input('lesson_type') != AppConst::CODE_MASTER_8_2
            ) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        $rule = [];
        if ($request && $request->input('lesson_type') == AppConst::CODE_MASTER_8_1) {
            // 個別教室登録
            $rule = ['required', $validationDuplicateRegular, $validationScheduleList];
        }
        $rules += ExtSchedule::fieldRules('id', $rule);

        $rule = [];
        if ($request && $request->input('lesson_type') == AppConst::CODE_MASTER_8_2) {
            // 家庭教師登録
            $rule = ['required', $validationHomeTeacherList];
        }
        $rules += AbsentApply::fieldRules('tid', $rule);

        // 授業種別 (値のチェックも行う)
        $rules += AbsentApply::fieldRules('lesson_type', ['required', $validationRadioLessonType]);

        $rule = [];
        if ($request && $request->input('lesson_type') == AppConst::CODE_MASTER_8_2) {
            // 家庭教師登録
            $rule = ['required', $validationDateHomeTeacher];
        }
        $rules += AbsentApply::fieldRules('lesson_date', $rule);

        $rule = [];
        if ($request && $request->input('lesson_type') == AppConst::CODE_MASTER_8_2) {
            // 家庭教師登録
            $rule = ['required', $validationDuplicateHomeTeacher];
        }
        $rules += AbsentApply::fieldRules('start_time', $rule);

        // 欠席理由
        $rules += AbsentApply::fieldRules('absent_reason', ['required']);

        return $rules;
    }
}
