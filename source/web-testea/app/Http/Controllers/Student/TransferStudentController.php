<?php

namespace App\Http\Controllers\Student;

use App\Consts\AppConst;
use App\Libs\CommonDateFormat;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncScheduleTrait;
use App\Http\Controllers\Traits\FuncTransferTrait;
use App\Mail\TransferAdjustmentRequest;
use App\Mail\TransferApplyRegistSchedule;
use App\Mail\TransferRemandStudentToAdmin;
use App\Models\Notice;
use App\Models\NoticeDestination;
use App\Models\Schedule;
use App\Models\TransferApplication;
use App\Models\TransferApplicationDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

/**
 * 振替調整 - コントローラ
 */
class TransferStudentController extends Controller
{

    // スケジュール情報取得用
    use FuncScheduleTrait;
    // 振替調整取得用
    use FuncTransferTrait;

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
        return view('pages.student.transfer_student');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // 一覧用SQLを取得（ユーザー権限によるガードはfunction内で行う）
        $transfers = $this->fncTranGetATransferApplicationList(null);

        // ページネータで返却
        return $this->getListAndPaginator($request, $transfers);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getData(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $id = $request->input('id');

        // データを取得（ユーザー権限によるガードはfunction内で行う）
        $tranApp = $this->fncTranGetTransferApplicationData($id);

        return $tranApp;
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
        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // 振替対象日の範囲
        $targetPeriod = $this->fncTranTargetDateFromTo();

        // 授業情報を取得（ユーザー権限によるガードはfunction内で行う）
        $lessons = $this->fncTranGetTransferSchedule($targetPeriod['from_date'], $targetPeriod['to_date']);
        // プルダウン用にリスト作成
        $lesson_list = $this->mdlGetScheduleMasterList($lessons);

        // 登録画面用テンプレートを使用
        return view('pages.student.transfer_student-new', [
            'editData' => ['student_id' => $account_id],
            'rules' => $this->rulesForInput(null),
            'lesson_list' => $lesson_list,
            'preferred_date' => null,
            'period_no' => null
        ]);
    }

    /**
     * 授業情報取得（スケジュールより）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 教室、講師、コース、科目情報
     */
    public function getDataSelectSchedule(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $schedule_id = $request->input('id');

        // 授業情報を取得
        $lesson = $this->fncTranGetTargetScheduleInfo($schedule_id);

        // 振替候補日の範囲
        $targetPeriod = $this->fncTranCandidateDateFromTo($lesson->target_date);

        // 振替候補日を取得
        $candidates = $this->fncTranGetTransferCandidateDates($lesson, $targetPeriod);

        return [
            'campus_cd' => $lesson->campus_cd,
            'campus_name' => $lesson->campus_name,
            'course_name' => $lesson->course_name,
            'tutor_id' => $lesson->tutor_id,
            'tutor_name' => $lesson->tutor_name,
            'student_id' => $lesson->student_id,
            'student_name' => $lesson->student_name,
            'subject_name' => $lesson->subject_name,
            'preferred_from' => $targetPeriod['from_date'],
            'preferred_to' => $targetPeriod['to_date'],
            'candidates' => $this->objToArray($candidates)
        ];
    }

    /**
     * 時限情報取得（校舎コード、日付より）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 時限コード、時限名
     */
    public function getDataSelectCalender(Request $request)
    {
        // 校舎コードを取得
        $campusCd = $request->input('campus_cd');
        // 対象日付を取得
        $targetDate = $request->input('target_date');

        // 対象日・対象校舎の時限リストを取得
        $periods = $this->mdlGetPeriodListByDate($campusCd, $targetDate);
        return [
            'periods' => $this->objToArray($periods)
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
        // 関連チェック
        Validator::make($request->all(), $this->rulesForInputRelated($request));

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // 当月依頼回数取得(申請者種別=生徒)
        $countPref = $this->fncTranGetTransferRequestCount($request['tutor_id'], $account_id, AppConst::CODE_MASTER_53_1);

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request, $countPref) {

            // 振替依頼情報
            $transApp = new TransferApplication;

            $form = $request->only(
                'schedule_id',
                'student_id',
                'tutor_id',
                'transfer_reason'
            );

            // 申請者種別：生徒
            $transApp->apply_kind = AppConst::CODE_MASTER_53_1;
            $transApp->apply_date = now();
            $transApp->monthly_count = $countPref + 1;
            // 承認ステータス：承認待ち
            $transApp->approval_status = AppConst::CODE_MASTER_3_1;
            $transApp->fill($form)->save();

            // 振替依頼日程情報登録
            $app_date_index = 0;
            for ($i = 1; $i <= 3; $i++) {
                $app_date = null;
                $app_period = null;

                if ($request['preferred' . $i . '_type'] == AppConst::TRANSFER_PREF_TYPE_SELECT) {
                    // 候補日選択リスト
                    if ($request->filled('preferred_date' . $i . '_select') && $request['preferred_date' . $i . '_select'] != '') {
                        $selectData = $this->splitPreferredKeyId($request['preferred_date' . $i . '_select']);
                        $app_date = $selectData['pre_date'];
                        $app_period = $selectData['pre_period'];
                    }
                } else {
                    // フリー入力
                    if ($request->filled('preferred_date' . $i . '_calender') && $request['preferred_date' . $i . '_calender'] != '') {
                        $app_date = $request['preferred_date' . $i . '_calender'];
                    }
                    if ($request->filled('preferred_date' . $i . '_period') && $request['preferred_date' . $i . '_period'] != '') {
                        $app_period = $request['preferred_date' . $i . '_period'];
                    }
                }
                // 希望日・時限があるときだけデータ登録
                if ($app_date != null && $app_period != null) {
                    $app_date_index++;
                    $transAppDate = new TransferApplicationDate();
                    $transAppDate->transfer_apply_id = $transApp->transfer_apply_id;
                    $transAppDate->request_no = $app_date_index;
                    $transAppDate->transfer_date = $app_date;
                    $transAppDate->period_no = $app_period;
                    $transAppDate->save();
                }
            }

            // スケジュール情報の更新
            $schedule = Schedule::where('schedule_id', '=', $request['schedule_id'])
                // 自分のアカウントIDでガードを掛ける（sid）
                ->where($this->guardStudentTableWithSid())
                ->firstOrFail();

            // 振替依頼ID
            $schedule->transfer_id = $transApp->transfer_apply_id;
            // 出欠ステータス:振替中
            $schedule->absent_status = AppConst::CODE_MASTER_35_4;
            $res = $schedule->save();

            //-------------------------
            // メール送信
            //-------------------------
            // save成功時のみ送信
            if ($res) {
                // 校舎名取得
                $campus_name = $this->mdlGetRoomName($schedule->campus_cd);
                // 生徒名取得
                $student_name = $this->mdlGetStudentName($schedule->student_id);
                // 講師名取得
                $tutor_name = $this->mdlGetTeacherName($schedule->tutor_id);

                // 送信先メールアドレス取得
                $tutorEmail = $this->mdlGetAccountMail($schedule->tutor_id, AppConst::CODE_MASTER_7_2);
                $campusEmail = $this->mdlGetCampusMail($schedule->campus_cd);

                $mail_body = [
                    'from_name' => $student_name,
                    'schedule_date_time' => CommonDateFormat::formatYmdDay($schedule->target_date) .
                        ' ' . $schedule->period_no . '時限目',
                    'campus_name' => $campus_name,
                    'tutor_name' => $tutor_name,
                    'student_name' => $student_name
                ];

                Mail::to($tutorEmail)->cc($campusEmail)->send(new TransferAdjustmentRequest($mail_body));
            }
        });

        return;
    }

    /**
     * 編集画面
     *
     * @param int $transferId 振替依頼ID
     * @return void
     */
    public function edit($transferId)
    {
        // IDのバリデーション
        $this->validateIds($transferId);

        // データを取得
        $editdata = $this->fncTranGetEditTransferData($transferId);

        // 承認ステータスリスト用データ
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_3, [AppConst::CODE_MASTER_3_SUB_1]);

        return view('pages.student.transfer_student-edit', [
            'editData' => $editdata,
            'rules' => $this->rulesForInput(null),
            'statusList' => $statusList
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
        Validator::make($request->all(), $this->fncTranRulesForApproval($request))->validate();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {
            // 振替依頼情報取得 -----------------------------------
            $transApp = TransferApplication::query()
                ->where('transfer_apply_id', $request->input('transfer_apply_id'))
                // 自分のアカウントIDでガードを掛ける（sid）
                ->where($this->guardStudentTableWithSid())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            // 振替前の授業情報取得 -------------------------------
            $befSchedule = Schedule::query()
                ->where('schedule_id', $transApp->schedule_id)
                // 自分のアカウントIDでガードを掛ける（sid）
                ->where($this->guardStudentTableWithSid())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            // 振替依頼情報 更新 ----------------------------------
            $transApp->approval_status = $request->input('approval_status');
            $transApp->comment = $request->input('comment');

            // 承認の場合 授業情報登録
            if ($request->input('approval_status') == AppConst::CODE_MASTER_3_2) {
                // 振替依頼日程情報取得 ---------------------------
                $transAppDate = $this->fncTranGetTransferDate($request->input('transfer_date_id'));

                // 空きブース取得
                $boothCd = $this->fncScheSearchBooth(
                    $befSchedule->campus_cd,
                    $befSchedule->booth_cd,
                    $transAppDate->transfer_date,
                    $transAppDate->period_no,
                    $befSchedule->how_to_kind,
                    null,
                    false
                );

                // 振替依頼日の時限の開始・終了時刻 取得
                $periodTime = $this->fncScheGetTimetableByDatePeriod(
                    $befSchedule->campus_cd,
                    $transAppDate->transfer_date,
                    $transAppDate->period_no
                );
                // 終了時刻計算
                $endTime = $this->fncTranEndTime($periodTime['start_time'], $befSchedule->minites);

                // スケジュール情報登録 -----------------------------
                $newSchedule = new Schedule;
                $data = [
                    'campus_cd' => $befSchedule->campus_cd,
                    'target_date' => $transAppDate->transfer_date,
                    'period_no' => $transAppDate->period_no,
                    'start_time' => $periodTime['start_time'],
                    'end_time' => $endTime,
                    'minites' => $befSchedule->minites,
                    'booth_cd' => $boothCd,
                    'course_cd' => $befSchedule->course_cd,
                    'course_kind' => $befSchedule->course_kind,
                    'student_id' => $transApp->student_id,
                    'tutor_id' => $transApp->tutor_id,
                    'subject_cd' => $befSchedule->subject_cd,
                    'create_kind' => AppConst::CODE_MASTER_32_2,         // 振替
                    'lesson_kind' => $befSchedule->lesson_kind,
                    'how_to_kind' => $befSchedule->how_to_kind,
                    'absent_status' => AppConst::CODE_MASTER_35_0,       // 実施前・出席
                    'transfer_class_id' => $befSchedule->schedule_id,    // 振替元授業ID
                    'memo' => $befSchedule->memo
                ];
                $newSchedule->fill($data)->save();

                // 振替前スケジュール 更新 ---------------------------
                $befSchedule->absent_status = AppConst::CODE_MASTER_35_5;   // 振替済
                $befSchedule->save();

                // 振替依頼情報 更新
                $transApp->transfer_schedule_id = $newSchedule->schedule_id;
                $transApp->confirm_date_id = $request->input('transfer_apply_id');
                $transApp->transfer_kind = AppConst::CODE_MASTER_54_1;
                $transApp->save();


                // お知らせ通知＆メール送信用 --------------------------
                // 校舎名取得
                $campus_name = $this->mdlGetRoomName($befSchedule->campus_cd);
                // 生徒名取得
                $student_name = $this->mdlGetStudentName($befSchedule->student_id);
                // 講師名取得
                $tutor_name = $this->mdlGetTeacherName($befSchedule->tutor_id);

                // お知らせ通知 -----------------------------------
                // お知らせメッセージ登録
                $notice = new Notice;

                // タイトルと本文(Langから取得する)
                $notice->title = Lang::get('message.notice.transfer_apply_regist_schedule.title');
                $notice->text = Lang::get(
                    'message.notice.transfer_apply_regist_schedule.text',
                    [
                        'targetDate' => CommonDateFormat::formatYmdDay($befSchedule->target_date),
                        'targetPeriodNo' => $befSchedule->period_no,
                        'transferDate' => CommonDateFormat::formatYmdDay($transAppDate->transfer_date),
                        'transferPeriodNo' => $transAppDate->period_no,
                        'roomName' => $campus_name,
                        'tutorName' => $tutor_name,
                        'studentName' => $student_name
                    ]
                );

                // お知らせ種別（その他）
                $notice->notice_type = AppConst::CODE_MASTER_14_4;
                // 管理者ID
                $notice->adm_id = 0;
                $notice->campus_cd = $befSchedule->campus_cd;
                // 保存
                $notice->save();

                // お知らせ宛先の登録
                $noticeDestination = new NoticeDestination;

                // 先に登録したお知らせIDをセット
                $noticeDestination->notice_id = $notice->notice_id;
                // 宛先連番: 1固定
                $noticeDestination->destination_seq = 1;
                // 宛先種別（講師）
                $noticeDestination->destination_type = AppConst::CODE_MASTER_15_3;
                // 講師ID
                $noticeDestination->tutor_id = $befSchedule->tutor_id;

                // 保存
                $res = $noticeDestination->save();

                // メール送信 ----------------------------------
                // save成功時のみ送信
                if ($res) {

                    // 送信先メールアドレス取得
                    $tutorEmail = $this->mdlGetAccountMail($befSchedule->tutor_id, AppConst::CODE_MASTER_7_2);

                    $mail_body = [
                        'from_name' => $student_name,
                        'schedule_date_time' => CommonDateFormat::formatYmdDay($befSchedule->target_date) .
                            ' ' . $befSchedule->period_no . '時限目',
                        'transfer_date_time' => CommonDateFormat::formatYmdDay($transAppDate->transfer_date) .
                            ' ' . $transAppDate->period_no . '時限目',
                        'campus_name' => $campus_name,
                        'tutor_name' => $tutor_name,
                        'student_name' => $student_name
                    ];

                    Mail::to($tutorEmail)->send(new TransferApplyRegistSchedule($mail_body));
                }
            } else {
                // 振替依頼情報 更新
                $transApp->transfer_schedule_id = null;
                $transApp->confirm_date_id = null;
                $transApp->transfer_kind = null;
                $res = $transApp->save();

                // 差戻し(日程不都合),(代講希望)の場合
                if (
                    $request->input('approval_status') == AppConst::CODE_MASTER_3_3 ||
                    $request->input('approval_status') == AppConst::CODE_MASTER_3_4
                ) {

                    // 校舎名取得
                    $campus_name = $this->mdlGetRoomName($befSchedule->campus_cd);
                    // 生徒名取得
                    $student_name = $this->mdlGetStudentName($befSchedule->student_id);
                    // 講師名取得
                    $tutor_name = $this->mdlGetTeacherName($befSchedule->tutor_id);

                    // メール送信 ----------------------------------
                    // save成功時のみ送信
                    if ($res) {

                        // 送信先メールアドレス取得
                        $tutorEmail = $this->mdlGetAccountMail($befSchedule->tutor_id, AppConst::CODE_MASTER_7_2);
                        $campusEmail = $this->mdlGetCampusMail($befSchedule->campus_cd);

                        $mail_body = [
                            'from_name' => $student_name,
                            'schedule_date_time' => CommonDateFormat::formatYmdDay($befSchedule->target_date) .
                                ' ' . $befSchedule->period_no . '時限目',
                            'campus_name' => $campus_name,
                            'tutor_name' => $tutor_name,
                            'student_name' => $student_name
                        ];

                        Mail::to($tutorEmail)->cc($campusEmail)->send(new TransferRemandStudentToAdmin($mail_body));
                    }
                }
            }
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
        // リクエストデータチェック（項目チェック）
        $validator = Validator::make($request->all(), $this->rulesForInput($request));
        if (count($validator->errors()) != 0) {
            // 項目チェックエラーがある場合はここでエラー情報を返す
            return $validator->errors();
        }
        // リクエストデータチェック（関連チェック追加）
        $validatorRerated = Validator::make($request->all(), $this->rulesForInputRelated($request));
        // 項目チェックエラー無し時は関連チェックを行い、結果を返す
        return $validatorRerated->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: 第1希望日
        // 候補日選択の場合 必須
        $rules += ['preferred_date1_select' => ['required_if:preferred1_type,' . AppConst::TRANSFER_PREF_TYPE_SELECT]];
        // フリー入力の場合 必須
        $rules += ['preferred_date1_calender' => ['required_if:preferred1_type,' . AppConst::TRANSFER_PREF_TYPE_INPUT, 'date_format:Y-m-d']];
        $rules += ['preferred_date1_period' => ['required_if:preferred1_type,' . AppConst::TRANSFER_PREF_TYPE_INPUT]];

        // 独自バリデーション: 第2希望日
        // フリー入力の場合 どちらかが入力されていたら必須
        $rules += ['preferred_date2_calender' => ['required_with:preferred_date2_period', 'date_format:Y-m-d']];
        $rules += ['preferred_date2_period' => ['required_with:preferred_date2_calender']];

        // 独自バリデーション: 第3希望日
        // フリー入力の場合 どちらかが入力されていたら必須
        $rules += ['preferred_date3_calender' => ['required_with:preferred_date3_period', 'date_format:Y-m-d']];
        $rules += ['preferred_date3_period' => ['required_with:preferred_date3_calender']];


        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += TransferApplication::fieldRules('schedule_id', ['required']);
        $rules += TransferApplication::fieldRules('student_id', ['required']);
        $rules += TransferApplication::fieldRules('tutor_id', ['required']);
        $rules += TransferApplication::fieldRules('transfer_reason', ['required']);

        return $rules;
    }

    /**
     * バリデーションルールを取得(登録用・関連チェック)
     *
     * @return array ルール
     */
    private function rulesForInputRelated(?Request $request)
    {
        $rules = array();

        // 授業情報から授業日・時限を取得
        $schedules = Schedule::select(
            'target_date',
            'period_no',
            'campus_cd',
            'tutor_id',
            'student_id',
            'minites',
            'booth_cd',
            'how_to_kind'
        )
            // IDを指定
            ->where('schedule_id', $request['schedule_id'])
            // 自分のアカウントIDでガードを掛ける（sid）
            ->where($this->guardStudentTableWithSid())
            ->firstOrFail();

        // 振替対象日の範囲
        $targetPeriod = $this->fncTranCandidateDateFromTo($schedules->target_date);

        // 独自バリデーション: 第１希望日のチェック 候補日選択リスト
        if ($request['preferred1_type'] == AppConst::TRANSFER_PREF_TYPE_SELECT) {
            $validationPreferred1_select =  function ($attribute, $value, $fail) use ($request, $schedules, $targetPeriod) {
                if (!$request->filled('preferred_date1_select') || $request['preferred_date1_select'] == '') {
                    return;
                }

                // 候補日リストチェック
                // 振替候補日を取得
                $candidates = $this->fncTranGetTransferCandidateDates($schedules, $targetPeriod);
                if (!isset($candidates[$value])) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            };

            $rules += ['preferred_date1_select' => [$validationPreferred1_select]];
        }

        // 独自バリデーション: 第１希望日のフリー入力のチェック
        if ($request['preferred1_type'] == AppConst::TRANSFER_PREF_TYPE_INPUT) {
            $validationPreferred1_input_calender = $this->fncTranGetValidateInputCalender1($request, $targetPeriod);
            $validationPreferred1_input_period = $this->fncTranGetValidateInputPeriod1($request, $targetPeriod);
            $validationPreferred1_input = $this->fncTranGetValidateInput1($request, $schedules);

            $rules += ['preferred_date1_calender' => [$validationPreferred1_input_calender, $validationPreferred1_input]];
            $rules += ['preferred_date1_period' => [$validationPreferred1_input, $validationPreferred1_input_period]];
        }

        // 独自バリデーション: 第２希望日のチェック 候補日選択リスト
        $validationPreferred2_select =  function ($attribute, $value, $fail) use ($request, $schedules, $targetPeriod) {
            if ($request->filled('preferred2_type') && $request['preferred2_type'] == AppConst::TRANSFER_PREF_TYPE_INPUT) {
                return;
            }
            if (!$request->filled('preferred_date2_select') || $request['preferred_date2_select'] == '') {
                return;
            }

            // 候補日リストチェック
            // 振替候補日を取得
            $candidates = $this->fncTranGetTransferCandidateDates($schedules, $targetPeriod);
            if (!isset($candidates[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }

            // 第１～２候補日を取得
            $preferred_datetime = [];
            for ($i = 1; $i <= 2; $i++) {
                if ($request['preferred' . $i . '_type'] == AppConst::TRANSFER_PREF_TYPE_SELECT) {
                    // 候補日選択の場合
                    $preferred_datetime[$i] = $request['preferred_date' . $i . '_select'];
                } else {
                    // フリー入力の場合
                    $preferred_datetime[$i] = $request['preferred_date' . $i . '_calender'] . '_' . $request['preferred_date' . $i . '_period'];
                }
            }

            if ($preferred_datetime[2] != '_') {
                if ($preferred_datetime[1] == $preferred_datetime[2]) {
                    // 希望日重複エラー
                    return $fail(Lang::get('validation.preferred_datetime_distinct'));
                }
            }
        };
        // 候補日選択の場合
        $rules += ['preferred_date2_select' => [$validationPreferred2_select]];

        // 独自バリデーション: 第２希望日・時限のチェック フリー入力
        if ($request->filled('preferred2_type') && $request['preferred2_type'] == AppConst::TRANSFER_PREF_TYPE_INPUT) {
            $validationPreferred2_input_calender = $this->fncTranGetValidateInputCalender2($request, $targetPeriod);
            $validationPreferred2_input_period = $this->fncTranGetValidateInputPeriod2($request, $targetPeriod);
            $validationPreferred2_input = $this->fncTranGetValidateInput2($request, $schedules);

            $rules += ['preferred_date2_calender' => [$validationPreferred2_input_calender, $validationPreferred2_input]];
            $rules += ['preferred_date2_period' => [$validationPreferred2_input_period, $validationPreferred2_input]];
        }

        // 独自バリデーション: 第３希望日のチェック 候補日選択リスト
        $validationPreferred3_select =  function ($attribute, $value, $fail) use ($request, $schedules, $targetPeriod) {
            if ($request->filled('preferred3_type') && $request['preferred3_type'] == AppConst::TRANSFER_PREF_TYPE_INPUT) {
                return;
            }
            if (!$request->filled('preferred_date3_select') || $request['preferred_date3_select'] == '') {
                return;
            }

            // 候補日リストチェック
            // 振替候補日を取得
            $candidates = $this->fncTranGetTransferCandidateDates($schedules, $targetPeriod);
            if (!isset($candidates[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }

            // 第１～３候補日を取得
            $preferred_datetime = [];
            for ($i = 1; $i <= 3; $i++) {
                if ($request['preferred' . $i . '_type'] == AppConst::TRANSFER_PREF_TYPE_SELECT) {
                    // 候補日選択の場合
                    $preferred_datetime[$i] = $request['preferred_date' . $i . '_select'];
                } else {
                    // フリー入力の場合
                    $preferred_datetime[$i] = $request['preferred_date' . $i . '_calender'] . '_' . $request['preferred_date' . $i . '_period'];
                }
            }
            if ($preferred_datetime[3] != '_') {
                if (
                    $preferred_datetime[1] == $preferred_datetime[3] ||
                    $preferred_datetime[2] == $preferred_datetime[3]
                ) {
                    // 希望日重複エラー
                    return $fail(Lang::get('validation.preferred_datetime_distinct'));
                }
            }
        };
        // 候補日選択の場合
        $rules += ['preferred_date3_select' => [$validationPreferred3_select]];

        // 独自バリデーション: 第３希望日・時限のチェック フリー入力
        if ($request->filled('preferred3_type') && $request['preferred3_type'] == AppConst::TRANSFER_PREF_TYPE_INPUT) {
            $validationPreferred3_input_calender = $this->fncTranGetValidateInputCalender3($request, $targetPeriod);
            $validationPreferred3_input_period = $this->fncTranGetValidateInputPeriod3($request, $targetPeriod);
            $validationPreferred3_input = $this->fncTranGetValidateInput3($request, $schedules);

            $rules += ['preferred_date3_calender' => [$validationPreferred3_input_calender, $validationPreferred3_input]];
            $rules += ['preferred_date3_period' => [$validationPreferred3_input_period, $validationPreferred3_input]];
        }

        return $rules;
    }

    /**
     * バリデーション(承認用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForApproval(Request $request)
    {
        // リクエストデータチェック（項目チェック）
        $validator = Validator::make($request->all(), $this->fncTranRulesForApproval($request));
        // 項目チェックエラーがある場合はここでエラー情報を返す
        return $validator->errors();
    }
}
