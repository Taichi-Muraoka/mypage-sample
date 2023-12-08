<?php

namespace App\Http\Controllers\Tutor;

use App\Consts\AppConst;
use App\Libs\CommonDateFormat;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use App\Http\Controllers\Traits\FuncScheduleTrait;
use App\Http\Controllers\Traits\FuncTransferTrait;
use App\Mail\TransferAdjustmentRequest;
use App\Mail\TransferAdjustmentRequestOver;
use App\Mail\TransferApplyRegistSchedule;
use App\Mail\TransferRemandTutorToAdmin;
use App\Models\Notice;
use App\Models\NoticeDestination;
use App\Models\Schedule;
use App\Models\TransferApplication;
use App\Models\TransferApplicationDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;

/**
 * 振替調整(講師) - コントローラ
 */
class TransferTutorController extends Controller
{
    // カレンダー情報取得用
    use FuncCalendarTrait;
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
        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(false);
        // 振替承認ステータスリストを取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_3);

        return view('pages.tutor.transfer_tutor', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'statusList' => $statusList,
            'editData' => null
        ]);
    }

    /**
     * 生徒情報取得（校舎リスト選択時）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 生徒情報
     */
    public function getDataSelectSearch(Request $request)
    {
        // campus_cdを取得
        $campus_cd = $request->input('id');

        // 生徒リスト取得
        if ($campus_cd == -1 || !filled($campus_cd)) {
            // -1 または 空白の場合、自分の受け持ちの生徒だけに絞り込み
            // 生徒リストを取得
            $students = $this->mdlGetStudentListForT(null, null);
        } else {
            $students = $this->mdlGetStudentListForT($campus_cd, null);
        }

        return [
            'selectItems' => $this->objToArray($students),
        ];
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

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // formを取得
        $form = $request->all();

        // 校舎コード選択による絞り込み条件
        $campusCd = null;
        // -1 は未選択状態のため、-1以外の場合に校舎コードの絞り込みを行う
        if (isset($form['campus_cd']) && filled($form['campus_cd']) && $form['campus_cd'] != -1) {
            $campusCd = $form['campus_cd'];
        }

        // クエリを作成
        $transfers = $this->fncTranGetATransferApplicationList(null, $account_id, $campusCd, null, false);

        // 生徒の絞り込み条件
        $transfers->SearchStudentId($form);
        // ステータスの絞り込み条件
        $transfers->SearchApprovalStatus($form);

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

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // データを取得
        $tranApp = $this->fncTranGetTransferApplicationData($id, null, $account_id);

        return $tranApp;
    }

    /**
     * バリデーション(検索用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 検索結果
     */
    public function validationForSearch(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForSearch());
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 校舎
        $validationCampusList =  function ($attribute, $value, $fail) {

            // 初期表示の時はエラーを発生させないようにする
            if ($value == -1) return;

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒ID
        $validationStudentList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $students = $this->mdlGetStudentListForT(null, null);
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 振替承認ステータス
        $validationStatus =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_3);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };


        $rules += ['campus_cd' => $validationCampusList];
        $rules += TransferApplication::fieldRules('student_id', [$validationStudentList]);
        $rules += TransferApplication::fieldRules('approval_status', [$validationStatus]);

        return $rules;
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

        // 担当生徒のリストを取得
        $students = $this->mdlGetStudentListForT(null, $account_id);

        // 振替依頼のアラート回数 システムマスタより取得
        $skip_count = $this->fncTranGetTransferSkip();

        // 登録画面用テンプレートを使用
        return view('pages.tutor.transfer_tutor-new', [
            'editData' => [
                'tutor_id' => $account_id,
                'monthly_count' => 0,
                'skip_count' => $skip_count
            ],
            'rules' => $this->rulesForInput(null),
            'students' => $students
        ]);
    }

    /**
     * 授業情報取得（生徒IDより）
     * 
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 教室、コース、科目情報
     */
    public function getDataSelectStudentSchedule(Request $request)
    {

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // 対象生徒IDを取得
        $student_id = $request->input('id');

        // 振替対象日の範囲
        $targetPeriod = $this->fncTranTargetDateFromTo();
        // 授業情報を取得
        $lessons = $this->fncTranGetTransferSchedule($targetPeriod['from_date'], $targetPeriod['to_date'], $student_id, $account_id);
        // プルダウン用にリスト作成
        $lesson_list = $this->mdlGetScheduleMasterList($lessons);

        // 当月振替依頼回数
        $monthly_count = $this->fncTranGetTransferRequestCount($account_id, $student_id, AppConst::CODE_MASTER_53_2);

        return [
            'lessons' => $this->objToArray($lesson_list),
            'monthly_count' => ($monthly_count + 1)
        ];
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

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // IDを取得
        $schedule_id = $request->input('id');

        // 授業情報を取得
        $lesson = $this->fncTranGetTargetScheduleInfo($schedule_id, null, $account_id);

        // 振替候補日の範囲
        $targetPeriod = $this->fncTranCandidateDateFromTo($lesson->target_date);

        // 振替候補日を取得
        $candidates = $this->fncTranGetTransferCandidateDates($lesson, $targetPeriod);

        return [
            'campus_cd' => $lesson->campus_cd,
            'campus_name' => $lesson->campus_name,
            'course_cd' => $lesson->course_cd,
            'course_name' => $lesson->course_name,
            'course_kind' => $lesson->course_kind,
            'subject_cd' => $lesson->subject_cd,
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

        // 当月依頼回数取得(申請者種別=講師)
        $countPref = $this->fncTranGetTransferRequestCount($account_id, $request['student_id'], AppConst::CODE_MASTER_53_2);

        // 振替依頼のアラート回数 システムマスタより取得
        $skipCount = $this->fncTranGetTransferSkip();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request, $countPref, $skipCount) {

            // 振替依頼情報
            $transApp = new TransferApplication;

            $form = $request->only(
                'schedule_id',
                'student_id',
                'tutor_id',
                'transfer_reason'
            );

            // 申請者種別：講師
            $transApp->apply_kind = AppConst::CODE_MASTER_53_2;
            $transApp->apply_date = now();
            $transApp->monthly_count = $countPref + 1;
            // 承認ステータス
            if (($countPref + 1) < $skipCount) {
                //承認待ち
                $transApp->approval_status = AppConst::CODE_MASTER_3_1;
            } else {
                // 振替調整スキップ回数以上の場合は、管理者承認待ち
                $transApp->approval_status = AppConst::CODE_MASTER_3_0;
            }
            $transApp->fill($form)->save();

            // 振替依頼日程情報登録
            $app_date_index = 0;
            for ($i = 1; $i <= 3; $i++) {
                $app_date = null;
                $app_period = null;

                if ($request->filled('preferred_date' . $i . '_calender') && $request['preferred_date' . $i . '_calender'] != '') {
                    $app_date = $request['preferred_date' . $i . '_calender'];
                }
                if ($request->filled('preferred_date' . $i . '_period') && $request['preferred_date' . $i . '_period'] != '') {
                    $app_period = $request['preferred_date' . $i . '_period'];
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
                // 自分のアカウントIDでガードを掛ける（tid）
                ->where($this->guardTutorTableWithTid())
                // 講師の担当生徒IDでガードを掛ける（sid）
                ->where($this->guardTutorTableWithSid())
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
                $campusEmail = $this->mdlGetCampusMail($schedule->campus_cd);

                if (($countPref + 1) < $skipCount) {
                    // 振替調整スキップ回数未満の場合は、生徒へ依頼
                    // 送信先メールアドレス取得
                    $studentEmail = $this->mdlGetAccountMail($schedule->student_id, AppConst::CODE_MASTER_7_1);
                    $mail_body = [
                        'from_name' => $tutor_name,
                        'schedule_date_time' => CommonDateFormat::formatYmdDay($schedule->target_date) .
                            ' ' . $schedule->period_no . '時限目',
                        'campus_name' => $campus_name,
                        'tutor_name' => $tutor_name,
                        'student_name' => $student_name
                    ];
                    Mail::to($studentEmail)->cc($campusEmail)->send(new TransferAdjustmentRequest($mail_body));
                } else {
                    // 振替調整スキップ回数以上の場合は、管理者承認待ち
                    $mail_body = [
                        'from_name' => $tutor_name,
                        'schedule_date_time' => CommonDateFormat::formatYmdDay($schedule->target_date) .
                            ' ' . $schedule->period_no . '時限目',
                        'campus_name' => $campus_name,
                        'tutor_name' => $tutor_name,
                        'student_name' => $student_name,
                        'apply_count' => ($countPref + 1)
                    ];
                    Mail::to($campusEmail)->send(new TransferAdjustmentRequestOver($mail_body));
                }
            }
        });

        return;
    }

    /**
     * 編集画面
     *
     * @param int $transferId 振替日ID
     * @return void
     */
    public function edit($transferId)
    {
        // IDのバリデーション
        $this->validateIds($transferId);

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // データを取得
        $tranApp = $this->fncTranGetTransferApplicationData($transferId, null, $account_id);

        // 希望日のスケジュール重複チェック
        $campusCd = $tranApp->campus_cd;
        $studentId = $tranApp->student_id;
        $tutorId = $tranApp->tutor_id;
        $boothCd = $tranApp->booth_cd;
        $howToKind = $tranApp->how_to_kind;
        $tran_date[1] = $this->dtFormatYmd($tranApp->transfer_date_1);
        $tran_date[2] = $this->dtFormatYmd($tranApp->transfer_date_2);
        $tran_date[3] = $this->dtFormatYmd($tranApp->transfer_date_3);
        $tran_period[1] = $tranApp->period_no_1;
        $tran_period[2] = $tranApp->period_no_2;
        $tran_period[3] = $tranApp->period_no_3;
        $freeCheck = [];
        for ($i = 1; $i <= 3; $i++) {
            $freeCheck[$i] = null;
            if ($tran_period[$i] != null && $tran_period[$i] != '') {
                // 対象日・対象校舎の時限・開始～終了時刻を取得
                $timeTables = $this->getTimetableByDate($campusCd, $tran_date[$i]);
                $periodList = $timeTables->keyBy('period_no');
                if (!isset($periodList[$tran_period[$i]])) {
                    // 時限リストに該当の時限のデータがない
                    $freeCheck[$i] = Lang::get('validation.invalid_period');
                } else {
                    $periodData = $periodList[$tran_period[$i]];

                    // 生徒スケジュール重複チェック
                    if (!$this->fncScheChkDuplidateSid(
                        $tran_date[$i],
                        $periodData['start_time'],
                        $periodData['end_time'],
                        $studentId
                    )) {
                        $freeCheck[$i] = Lang::get('validation.duplicate_student');
                    } else {
                        // 講師スケジュール重複チェック
                        if (!$this->fncScheChkDuplidateTid(
                            $tran_date[$i],
                            $periodData['start_time'],
                            $periodData['end_time'],
                            $tutorId
                        )) {
                            $freeCheck[$i] = Lang::get('validation.duplicate_tutor');
                        } else {
                            // ブース空きチェック
                            if ($this->fncScheSearchBooth(
                                $campusCd,
                                $boothCd,
                                $tran_date[$i],
                                $tran_period[$i],
                                $howToKind,
                                null,
                                false
                            ) == null) {
                                $freeCheck[$i] = Lang::get('validation.duplicate_booth');
                            }
                        }
                    }
                }
            }
        }

        $editdata = [
            'transfer_apply_id' => $tranApp->transfer_apply_id,
            'target_date' => CommonDateFormat::formatYmdDay($tranApp->lesson_target_date),
            'period_no' => $tranApp->lesson_period_no,
            'campus_name' => $tranApp->campus_name,
            'course_name' => $tranApp->course_name,
            'student_name' => $tranApp->student_name,
            'subject_name' => $tranApp->subject_name,
            'transfer_reason' => $tranApp->transfer_reason,
            'transfer_date_id_1' => $tranApp->transfer_date_id_1,
            'transfer_date_id_2' => $tranApp->transfer_date_id_2,
            'transfer_date_id_3' => $tranApp->transfer_date_id_3,
            'subject_name' => $tranApp->subject_name,
            'approval_status' => $tranApp->approval_status,
            'comment' => $tranApp->comment
        ];
        for ($i = 1; $i <= 3; $i++) {
            $fmtDate = '';
            if ($tran_date[$i] != '') {
                $fmtDate = CommonDateFormat::formatYmdDay($tran_date[$i]);
            }
            $editdata += [
                'transfer_date_' . $i => $fmtDate,
                'period_no_' . $i => $tran_period[$i],
                'free_check_' . $i => $freeCheck[$i]
            ];
        }

        // 承認ステータスリスト用データ
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_3, [AppConst::CODE_MASTER_3_SUB_1]);

        return view('pages.tutor.transfer_tutor-edit', [
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
        Validator::make($request->all(), $this->rulesForApproval($request))->validate();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {
            // 振替依頼情報取得 -----------------------------------
            $transApp = TransferApplication::query()
                ->where('transfer_apply_id', $request->input('transfer_apply_id'))
                // 自分のアカウントIDでガードを掛ける（tid）
                ->where($this->guardTutorTableWithTid())
                // 自分の担当生徒IDでガードを掛ける（sid）
                ->where($this->guardTutorTableWithSid())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            // 振替前の授業情報取得 -------------------------------
            $befSchedule = Schedule::query()
                ->where('schedule_id', $transApp->schedule_id)
                // 自分のアカウントIDでガードを掛ける（tid）
                ->where($this->guardTutorTableWithTid())
                // 自分の担当生徒IDでガードを掛ける（sid）
                ->where($this->guardTutorTableWithSid())
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
                $periodTime = $this->getTimetablePeriodTimeByDatePeriod(
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
                // 宛先種別（生徒）
                $noticeDestination->destination_type = AppConst::CODE_MASTER_15_2;
                // 生徒ID
                $noticeDestination->student_id = $befSchedule->student_id;

                // 保存
                $res = $noticeDestination->save();

                // メール送信 ----------------------------------
                // save成功時のみ送信
                if ($res) {

                    // 送信先メールアドレス取得
                    $studentEmail = $this->mdlGetAccountMail($befSchedule->student_id, AppConst::CODE_MASTER_7_1);

                    $mail_body = [
                        'from_name' => $tutor_name,
                        'schedule_date_time' => CommonDateFormat::formatYmdDay($befSchedule->target_date) .
                            ' ' . $befSchedule->period_no . '時限目',
                        'transfer_date_time' => CommonDateFormat::formatYmdDay($transAppDate->transfer_date) .
                            ' ' . $transAppDate->period_no . '時限目',
                        'campus_name' => $campus_name,
                        'tutor_name' => $tutor_name,
                        'student_name' => $student_name
                    ];

                    Mail::to($studentEmail)->send(new TransferApplyRegistSchedule($mail_body));
                }
            } else {
                // 振替依頼情報 更新
                $transApp->transfer_schedule_id = null;
                $transApp->confirm_date_id = null;
                $transApp->transfer_kind = null;
                $res = $transApp->save();

                // 差戻し(日程不都合),(代講希望)の場合→管理者調整
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
                        $campusEmail = $this->mdlGetCampusMail($befSchedule->campus_cd);

                        $mail_body = [
                            'from_name' => $tutor_name,
                            'schedule_date_time' => $befSchedule->target_date->format('Y/m/d') .
                                ' ' . $befSchedule->period_no . '時限目',
                            'campus_name' => $campus_name,
                            'tutor_name' => $tutor_name,
                            'student_name' => $student_name
                        ];

                        Mail::to($campusEmail)->send(new TransferRemandTutorToAdmin($mail_body));
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
        $rules += ['preferred_date1_calender' => ['required', 'date_format:Y-m-d']];
        $rules += ['preferred_date1_period' => ['required']];

        // 独自バリデーション: 第2希望日
        // どちらかが入力されていたら必須
        $rules += ['preferred_date2_calender' => ['required_with:preferred_date2_period', 'date_format:Y-m-d']];
        $rules += ['preferred_date2_period' => ['required_with:preferred_date2_calender']];

        // 独自バリデーション: 第3希望日
        // どちらかが入力されていたら必須
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
            'period_no'
        )
            // IDを指定
            ->where('schedule_id', $request['schedule_id'])
            // 自分のアカウントIDでガードを掛ける（tid）
            ->where($this->guardTutorTableWithTid())
            // 講師の担当生徒IDでガードを掛ける（sid）
            ->where($this->guardTutorTableWithSid())
            ->firstOrFail();
        $scheduleDate = $schedules->target_date;
        $schedulePeriod = $schedules->period_no;

        // 振替対象日の範囲
        $targetPeriod = $this->fncTranCandidateDateFromTo($scheduleDate);

        // 独自バリデーション: 第１希望日
        // 独自バリデーション: カレンダー入力の日付範囲チェック
        $validationPreferred1_input_calender =  function ($attribute, $value, $fail) use ($request, $targetPeriod) {
            if (!$request->filled('preferred_date1_calender') || $request['preferred_date1_calender'] == '') {
                // 未入力の場合は必須チェックでエラー
                return;
            }
            // 範囲チェック
            if (!$this->dtCheckDateFromTo($request['preferred_date1_calender'], $targetPeriod['from_date'], $targetPeriod['to_date'])) {
                // 希望日範囲外エラー
                return $fail(Lang::get('validation.preferred_date_out_of_range'));
            }
            // 休業日チェック
            // 期間区分の取得（年間授業予定）
            $dateKind = $this->getYearlyDateKind($request['campus_cd'], $request['preferred_date1_calender']);
            if ($dateKind == AppConst::CODE_MASTER_38_9) {
                // 休業日の場合、エラー
                return $fail(Lang::get('validation.preferred_date_closed'));
            }
        };
        $validationPreferred1_input_period =  function ($attribute, $value, $fail) use ($request) {
            if ((!$request->filled('preferred_date1_calender') || $request['preferred_date1_calender'] == '') ||
                (!$request->filled('preferred_date1_period') || $request['preferred_date1_period'] == '')
            ) {
                // 未入力の場合は必須チェックでエラー
                return;
            }

            // 時限リストを取得
            $list = $this->mdlGetPeriodListByDate($request['campus_cd'], $request['preferred_date1_calender']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        $validationPreferred1_input =  function ($attribute, $value, $fail) use ($request, $scheduleDate, $schedulePeriod) {
            if ((!$request->filled('preferred_date1_calender') || $request['preferred_date1_calender'] == '') ||
                (!$request->filled('preferred_date1_period') || $request['preferred_date1_period'] == '')
            ) {
                // 未入力の場合は必須チェックでエラー
                return;
            }

            // 振替対象の選択授業日・時限とチェック
            if (
                strtotime($request['preferred_date1_calender']) == strtotime($scheduleDate) &&
                $request['preferred_date1_period'] == $schedulePeriod
            ) {
                // 重複エラー
                return $fail(Lang::get('validation.preferred_datetime_same'));
            }

            // 対象日・対象校舎の時限・開始～終了時刻を取得
            $timeTables = $this->getTimetableByDate($request['campus_cd'], $request['preferred_date1_calender']);
            $periodList = $timeTables->keyBy('period_no');
            if (!isset($periodList[$request['preferred_date1_period']])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
            $periodData = $periodList[$request['preferred_date1_period']];

            // 生徒スケジュール重複チェック
            if (!$this->fncScheChkDuplidateSid(
                $request['preferred_date1_calender'],
                $periodData['start_time'],
                $periodData['end_time'],
                $request['student_id']
            )) {
                // 重複エラー
                return $fail(Lang::get('validation.duplicate_student'));
            }

            // 講師スケジュール重複チェック
            if (!$this->fncScheChkDuplidateTid(
                $request['preferred_date1_calender'],
                $periodData['start_time'],
                $periodData['end_time'],
                $request['tutor_id']
            )) {
                // 重複エラー
                return $fail(Lang::get('validation.duplicate_tutor'));
            }
        };

        $rules += ['preferred_date1_calender' => [$validationPreferred1_input_calender, $validationPreferred1_input]];
        $rules += ['preferred_date1_period' => [$validationPreferred1_input_period, $validationPreferred1_input]];

        // 独自バリデーション: 第２希望日日付のチェック
        $validationPreferred2_input_calender =  function ($attribute, $value, $fail) use ($request, $targetPeriod) {
            if ((!$request->filled('preferred_date2_calender') || $request['preferred_date2_calender'] == '') &&
                (!$request->filled('preferred_date2_period') || $request['preferred_date2_period'] == '')
            ) {
                // カレンダーも時限も未入力の場合はOK
                return;
            }

            if ((!$request->filled('preferred_date2_calender') || $request['preferred_date2_calender'] == '') &&
                ($request->filled('preferred_date2_period') && $request['preferred_date2_period'] != '')
            ) {
                // 時限入力あり・カレンダー入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }

            // カレンダー入力ありの場合
            if ($request->filled('preferred_date2_calender') && $request['preferred_date2_calender'] != '') {
                // 範囲チェック
                if (!$this->dtCheckDateFromTo($request['preferred_date2_calender'], $targetPeriod['from_date'], $targetPeriod['to_date'])) {
                    // 希望日範囲外エラー
                    return $fail(Lang::get('validation.preferred_date_out_of_range'));
                }
                // 休業日チェック
                // 期間区分の取得（年間授業予定）
                $dateKind = $this->getYearlyDateKind($request['campus_cd'], $request['preferred_date2_calender']);
                if ($dateKind == AppConst::CODE_MASTER_38_9) {
                    // 休業日の場合、エラー
                    return $fail(Lang::get('validation.preferred_date_closed'));
                }
            }
        };
        // 独自バリデーション: 第２希望日時限のチェック
        $validationPreferred2_input_period =  function ($attribute, $value, $fail) use ($request, $targetPeriod) {
            if ((!$request->filled('preferred_date2_calender') || $request['preferred_date2_calender'] == '') &&
                (!$request->filled('preferred_date2_period') || $request['preferred_date2_period'] == '')
            ) {
                // カレンダーも時限も未入力の場合はOK
                return;
            }
            if (($request->filled('preferred_date2_calender') && $request['preferred_date2_calender'] != '') &&
                (!$request->filled('preferred_date2_period') || $request['preferred_date2_period'] == '')
            ) {
                // カレンダー入力あり・時限入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }

            // 時限リストを取得
            $list = $this->mdlGetPeriodListByDate($request['campus_cd'], $request['preferred_date2_calender']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        $validationPreferred2_input =  function ($attribute, $value, $fail) use ($request, $scheduleDate, $schedulePeriod) {
            if ((!$request->filled('preferred_date2_calender') || $request['preferred_date2_calender'] == '') &&
                (!$request->filled('preferred_date2_period') || $request['preferred_date2_period'] == '')
            ) {
                // カレンダーも時限も未入力の場合はOK
                return;
            }
            if (($request->filled('preferred_date2_calender') && $request['preferred_date2_calender'] != '') &&
                (!$request->filled('preferred_date2_period') || $request['preferred_date2_period'] == '')
            ) {
                // カレンダー入力あり・時限入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }
            if ((!$request->filled('preferred_date2_calender') || $request['preferred_date2_calender'] == '') &&
                ($request->filled('preferred_date2_period') && $request['preferred_date2_period'] != '')
            ) {
                // 時限入力あり・カレンダー入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }

            // 振替対象の選択授業日・時限とチェック
            if (
                strtotime($request['preferred_date2_calender']) == strtotime($scheduleDate) &&
                $request['preferred_date2_period'] == $schedulePeriod
            ) {
                // 重複エラー
                return $fail(Lang::get('validation.preferred_datetime_same'));
            }

            // 対象日・対象校舎の時限・開始～終了時刻を取得
            $timeTables = $this->getTimetableByDate($request['campus_cd'], $request['preferred_date2_calender']);
            $periodList = $timeTables->keyBy('period_no');
            if (!isset($periodList[$request['preferred_date2_period']])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
            $periodData = $periodList[$request['preferred_date2_period']];

            // 生徒スケジュール重複チェック
            if (!$this->fncScheChkDuplidateSid(
                $request['preferred_date2_calender'],
                $periodData['start_time'],
                $periodData['end_time'],
                $request['student_id']
            )) {
                // 重複エラー
                return $fail(Lang::get('validation.duplicate_student'));
            }

            // 講師スケジュール重複チェック
            if (!$this->fncScheChkDuplidateTid(
                $request['preferred_date2_calender'],
                $periodData['start_time'],
                $periodData['end_time'],
                $request['tutor_id']
            )) {
                // 重複エラー
                return $fail(Lang::get('validation.duplicate_tutor'));
            }

            // 第１～２候補日を取得
            $preferred_datetime = [];
            for ($i = 1; $i <= 2; $i++) {
                $preferred_datetime[$i] = $request['preferred_date' . $i . '_calender'] . '_' . $request['preferred_date' . $i . '_period'];
            }
            if ($preferred_datetime[2] != '_') {
                if ($preferred_datetime[1] == $preferred_datetime[2]) {
                    // 希望日重複エラー
                    return $fail(Lang::get('validation.preferred_datetime_distinct'));
                }
            }
        };
        $rules += ['preferred_date2_calender' => [$validationPreferred2_input_calender, $validationPreferred2_input]];
        $rules += ['preferred_date2_period' => [$validationPreferred2_input_period, $validationPreferred2_input]];

        // 独自バリデーション: 第３希望日日付のチェック
        $validationPreferred3_input_calender =  function ($attribute, $value, $fail) use ($request, $targetPeriod) {
            if ((!$request->filled('preferred_date3_calender') || $request['preferred_date3_calender'] == '') &&
                (!$request->filled('preferred_date3_period') || $request['preferred_date3_period'] == '')
            ) {
                // カレンダーも時限も未入力の場合はOK
                return;
            }
            if ((!$request->filled('preferred_date3_calender') || $request['preferred_date3_calender'] == '') &&
                ($request->filled('preferred_date3_period') && $request['preferred_date3_period'] != '')
            ) {
                // 時限入力あり・カレンダー入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }

            // カレンダー入力ありの場合
            if ($request->filled('preferred_date3_calender') && $request['preferred_date3_calender'] != '') {
                // 範囲チェック
                if (!$this->dtCheckDateFromTo($request['preferred_date3_calender'], $targetPeriod['from_date'], $targetPeriod['to_date'])) {
                    // 希望日範囲外エラー
                    return $fail(Lang::get('validation.preferred_date_out_of_range'));
                }
                // 休業日チェック
                // 期間区分の取得（年間授業予定）
                $dateKind = $this->getYearlyDateKind($request['campus_cd'], $request['preferred_date3_calender']);
                if ($dateKind == AppConst::CODE_MASTER_38_9) {
                    // 休業日の場合、エラー
                    return $fail(Lang::get('validation.preferred_date_closed'));
                }
            }
        };
        // 独自バリデーション: 第３希望日時限のチェック
        $validationPreferred3_input_period =  function ($attribute, $value, $fail) use ($request, $targetPeriod) {
            if ((!$request->filled('preferred_date3_calender') || $request['preferred_date3_calender'] == '') &&
                (!$request->filled('preferred_date3_period') || $request['preferred_date3_period'] == '')
            ) {
                // カレンダーも時限も未入力の場合はOK
                return;
            }
            if (($request->filled('preferred_date3_calender') && $request['preferred_date3_calender'] != '') &&
                (!$request->filled('preferred_date3_period') || $request['preferred_date3_period'] == '')
            ) {
                // カレンダー入力あり・時限入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }

            // 時限リストを取得
            $list = $this->mdlGetPeriodListByDate($request['campus_cd'], $request['preferred_date3_calender']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        $validationPreferred3_input =  function ($attribute, $value, $fail) use ($request, $scheduleDate, $schedulePeriod) {
            if ((!$request->filled('preferred_date3_calender') || $request['preferred_date3_calender'] == '') &&
                (!$request->filled('preferred_date3_period') || $request['preferred_date3_period'] == '')
            ) {
                // カレンダーも時限も未入力の場合はOK
                return;
            }
            if (($request->filled('preferred_date3_calender') && $request['preferred_date3_calender'] != '') &&
                (!$request->filled('preferred_date3_period') || $request['preferred_date3_period'] == '')
            ) {
                // カレンダー入力あり・時限入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }
            if ((!$request->filled('preferred_date3_calender') || $request['preferred_date3_calender'] == '') &&
                ($request->filled('preferred_date3_period') && $request['preferred_date3_period'] != '')
            ) {
                // 時限入力あり・カレンダー入力なし エラー
                return $fail(Lang::get('validation.preferred_input_reqired'));
            }

            // 振替対象の選択授業日・時限とチェック
            if (
                strtotime($request['preferred_date3_calender']) == strtotime($scheduleDate) &&
                $request['preferred_date3_period'] == $schedulePeriod
            ) {
                // 重複エラー
                return $fail(Lang::get('validation.preferred_datetime_same'));
            }

            // 対象日・対象校舎の時限・開始～終了時刻を取得
            $timeTables = $this->getTimetableByDate($request['campus_cd'], $request['preferred_date3_calender']);
            $periodList = $timeTables->keyBy('period_no');
            if (!isset($periodList[$request['preferred_date3_period']])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
            $periodData = $periodList[$request['preferred_date3_period']];

            // 生徒スケジュール重複チェック
            if (!$this->fncScheChkDuplidateSid(
                $request['preferred_date3_calender'],
                $periodData['start_time'],
                $periodData['end_time'],
                $request['student_id']
            )) {
                // 重複エラー
                return $fail(Lang::get('validation.duplicate_student'));
            }

            // 講師スケジュール重複チェック
            if (!$this->fncScheChkDuplidateTid(
                $request['preferred_date3_calender'],
                $periodData['start_time'],
                $periodData['end_time'],
                $request['tutor_id']
            )) {
                // 重複エラー
                return $fail(Lang::get('validation.duplicate_tutor'));
            }

            // 第１～３候補日を取得
            $preferred_datetime = [];
            for ($i = 1; $i <= 3; $i++) {
                $preferred_datetime[$i] = $request['preferred_date' . $i . '_calender'] . '_' . $request['preferred_date' . $i . '_period'];
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
        $rules += ['preferred_date3_calender' => [$validationPreferred3_input_calender, $validationPreferred3_input]];
        $rules += ['preferred_date3_period' => [$validationPreferred3_input_period, $validationPreferred3_input]];

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
        $validator = Validator::make($request->all(), $this->rulesForApproval($request));
        // 項目チェックエラーがある場合はここでエラー情報を返す
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(承認用)
     *
     * @return array ルール
     */
    private function rulesForApproval(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: ステータスと希望日選択
        // 承認ステータス
        $validationApprovalStatus = function ($attribute, $value, $fail) use ($request) {

            // 振替承認ステータスリストを取得
            $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_3, [AppConst::CODE_MASTER_3_SUB_1]);
            if (!isset($statusList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }

            // 承認の場合、希望日選択必須
            if ($request->filled('approval_status') && $request['approval_status'] == AppConst::CODE_MASTER_3_2) {
                // 振替希望日選択チェック
                if (!$request->filled('transfer_date_id') || $request['transfer_date_id'] == '') {
                    // 希望日選択なしエラー
                    return $fail(Lang::get('validation.preferred_approval_not_select'));
                }

                // 授業情報取得
                $schedule = $this->fncTranGetScheduleByTranAppId($request['transfer_apply_id']);
                // 振替依頼日程情報取得
                $transferDate = $this->fncTranGetTransferDate($request['transfer_date_id']);
                // 振替依頼日・時限 開始～終了時間取得
                $periodTime = $this->getTimetablePeriodTimeByDatePeriod($schedule->campus_cd, $transferDate->transfer_date, $transferDate->period_no);
                // 終了時刻計算
                $endTime = $this->fncTranEndTime($periodTime->start_time, $schedule->minites);

                // 生徒スケジュール重複チェック
                if (!$this->fncScheChkDuplidateSid(
                    $transferDate->transfer_date,
                    $periodTime->start_time,
                    $endTime,
                    $schedule->student_id
                )) {
                    return $fail(Lang::get('validation.duplicate_student'));
                }

                // 講師スケジュール重複チェック
                if (!$this->fncScheChkDuplidateTid(
                    $transferDate->transfer_date,
                    $periodTime->start_time,
                    $endTime,
                    $schedule->tutor_id
                )) {
                    return $fail(Lang::get('validation.duplicate_tutor'));
                }

                // ブース空きチェック
                if ($this->fncScheSearchBooth(
                    $schedule->campus_cd,
                    $schedule->booth_cd,
                    $transferDate->transfer_date,
                    $transferDate->period_no,
                    $schedule->how_to_kind,
                    null,
                    false
                ) == null) {
                    return $fail(Lang::get('validation.duplicate_booth'));
                }
            }

            // 振替希望日選択チェック
            if ($request->filled('transfer_date_id') && $request['transfer_date_id'] != '') {
                // 希望日選択ありだが承認待ち・差戻し(日程不都合)・〃(代講希望)の場合、エラー
                if (
                    $request->filled('approval_status') &&
                    ($request['approval_status'] == AppConst::CODE_MASTER_3_1 ||
                        $request['approval_status'] == AppConst::CODE_MASTER_3_3 ||
                        $request['approval_status'] == AppConst::CODE_MASTER_3_4)
                ) {
                    // 希望日選択ありエラー
                    return $fail(Lang::get('validation.preferred_status_not_apply'));
                }
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += TransferApplication::fieldRules('approval_status', ['required', $validationApprovalStatus]);
        // コメント:承認ステータス=差戻し(日程不都合) or 差戻し(代講希望) の場合に必須
        $rules += TransferApplication::fieldRules('comment', ['required_if:approval_status,' . AppConst::CODE_MASTER_3_3, 'required_if:approval_status,' . AppConst::CODE_MASTER_3_4]);

        return $rules;
    }
}
