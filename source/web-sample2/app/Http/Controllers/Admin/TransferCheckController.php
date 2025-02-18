<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Consts\AppConst;
use App\Libs\CommonDateFormat;
use App\Models\Schedule;
use App\Models\TransferApplication;
use App\Models\TransferApplicationDate;
use App\Models\Notice;
use App\Models\NoticeDestination;
use Illuminate\Support\Facades\Mail;
use App\Mail\TransferAdjustmentRequest;
use App\Mail\TransferApplyRegistSchedule;
use App\Mail\SubstituteRegistSchedule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Libs\AuthEx;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use App\Http\Controllers\Traits\FuncScheduleTrait;
use App\Http\Controllers\Traits\FuncTransferTrait;
use App\Http\Controllers\Traits\CtrlDateTrait;

/**
 * 振替調整一覧 - コントローラ
 */
class TransferCheckController extends Controller
{

    // カレンダー情報取得用
    use FuncCalendarTrait;
    // スケジュール情報取得用
    use FuncScheduleTrait;
    // 振替調整取得用
    use FuncTransferTrait;
    // 振替可能授業の範囲取得用
    use CtrlDateTrait;

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
        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);
        // ステータスリストを取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_3);
        // 生徒リストを取得
        $studentList = $this->mdlGetStudentList();
        // 講師リストを取得
        $tutorList = $this->mdlGetTutorList();

        return view('pages.admin.transfer_check', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'statusList' => $statusList,
            'studentList' => $studentList,
            'tutorList' => $tutorList,
            'editData' => null
        ]);
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

        // ログイン者の情報を取得する
        $account = Auth::user();

        // formを取得
        $form = $request->all();

        // 校舎コード選択による絞り込み条件
        $campusCd = null;
        // 校舎の絞り込み条件
        if (AuthEx::isRoomAdmin()) {
            $campusCd = $account->campus_cd;
        } else {
            // 本部管理者の場合検索フォームから取得
            $campusCd = $form['campus_cd'];
        }

        // クエリを作成
        $transfers = $this->fncTranGetATransferApplicationList($campusCd);

        // ステータスの絞り込み条件
        $transfers->SearchApprovalStatus($form);
        // 生徒の絞り込み条件
        $transfers->SearchStudentId($form);
        // 講師の絞り込み条件
        $transfers->SearchTutorId($form);

        // ページネータで返却
        return $this->getListAndPaginator($request, $transfers);
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
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 校舎
        $validationCampusList =  function ($attribute, $value, $fail) {

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
            $students = $this->mdlGetStudentList();
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 講師
        $validationTutorList =  function ($attribute, $value, $fail) {
            // 講師リストを取得
            $tutors = $this->mdlGetTutorList();
            if (!isset($tutors[$value])) {
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

        $rules += Schedule::fieldRules('campus_cd', [$validationCampusList]);
        $rules += TransferApplication::fieldRules('student_id', [$validationStudentList]);
        $rules += TransferApplication::fieldRules('tutor_id', [$validationTutorList]);
        $rules += TransferApplication::fieldRules('approval_status', [$validationStatus]);

        return $rules;
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        // MEMO:データ取得は詳細モーダル・承認モーダル共通

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $id = $request->input('id');

        // データを取得（ユーザー権限によるガードはfunction内で行う）
        $tranApp = $this->fncTranGetTransferApplicationData($id);

        return $tranApp;
    }

    /**
     * 承認処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function execModal(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl-approval":
                //--------
                // 承認
                //--------
                // トランザクション(例外時は自動的にロールバック)
                DB::transaction(function () use ($request) {
                    // 振替依頼情報取得 -----------------------------------                    // 対象データ取得
                    $transApp = TransferApplication::query()
                        ->where('transfer_apply_id', $request->input('id'))
                        // ステータス「管理者承認待ち」のみ
                        ->where('approval_status', AppConst::CODE_MASTER_3_0)
                        // 該当データがない場合はエラーを返す
                        ->firstOrFail();

                    // 振替前の授業情報取得 -------------------------------
                    $schedule = Schedule::query()
                        ->where('schedule_id', $transApp->schedule_id)
                        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
                        ->where($this->guardRoomAdminTableWithRoomCd())
                        // 該当データがない場合はエラーを返す
                        ->firstOrFail();

                    //-------------------------
                    // 振替依頼情報のステータス更新
                    //-------------------------
                    // ステータスを「承認待ち」 とする
                    $transApp->approval_status = AppConst::CODE_MASTER_3_1;
                    $res = $transApp->save();

                    //-------------------------
                    // メール送信(生徒宛)
                    //-------------------------
                    // save成功時のみ送信
                    if ($res) {
                        // メール送信用 --------------------------
                        // 校舎名取得
                        $campus_name = $this->mdlGetRoomName($schedule->campus_cd);
                        // 生徒名取得
                        $student_name = $this->mdlGetStudentName($schedule->student_id);
                        // 講師名取得
                        $tutor_name = $this->mdlGetTeacherName($schedule->tutor_id);
                        // 送信先メールアドレス取得
                        $studentEmail = $this->mdlGetAccountMail($transApp->student_id, AppConst::CODE_MASTER_7_1);
                        // メール本文をセット
                        $mail_body = [
                            'from_name' => $tutor_name,
                            'schedule_date_time' => CommonDateFormat::formatYmdDay($schedule->target_date) .
                                ' ' . $schedule->period_no . '時限目',
                            'campus_name' => $campus_name,
                            'tutor_name' => $tutor_name,
                            'student_name' => $student_name
                        ];

                        // メール送信
                        Mail::to($studentEmail)->send(new TransferAdjustmentRequest($mail_body));
                    }
                });
                return;

            default:
                // モーダルIDが該当しない場合
                $this->illegalResponseErr();
        }
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
     * 授業情報取得（生徒IDより）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 教室、コース、科目情報
     */
    public function getDataSelectStudent(Request $request)
    {

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // 対象生徒IDを取得
        $studentId = $request->input('id');

        // 振替対象日の範囲（当日も許可）
        $targetPeriod = $this->dtGetTargetDateFromTo(true);
        // 授業情報を取得
        $lessons = $this->fncTranGetTransferScheduleAdmin($targetPeriod['from_date'], $targetPeriod['to_date'], $studentId);
        // プルダウン用にリスト作成
        $lessonList = $this->mdlGetScheduleMasterList($lessons);

        return [
            'lessons' => $this->objToArray($lessonList)
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
        // 対象スケジュールIDを取得
        $scheduleId = $request->input('id');

        // 授業情報を取得（ユーザー権限によるガードはfunction内で行う）
        $lesson = $this->fncTranGetTargetScheduleInfo($scheduleId);

        // 講師リストを取得
        $tutorList = $this->mdlGetTutorList($lesson->campus_cd);

        return [
            'campus_cd' => $lesson->campus_cd,
            'campus_name' => $lesson->campus_name,
            'course_name' => $lesson->course_name,
            'tutor_name' => $lesson->tutor_name,
            'subject_name' => $lesson->subject_name,
            'tutors' => $this->objToArray($tutorList)
        ];
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
        // 生徒リストを取得
        $studentList = $this->mdlGetStudentList();

        // 登録画面用テンプレートを使用
        return view('pages.admin.transfer_check-new', [
            'editData' => null,
            'rules' => $this->rulesForInput(null),
            'students' => $studentList,
        ]);
    }

    /**
     * 登録画面(スケジュールIDを指定して直接遷移)
     * 要振替授業一覧画面から遷移する
     *
     * @param int $scheduleId スケジュールID
     * @return view
     */
    public function newRequired($scheduleId)
    {
        // IDのバリデーション
        $this->validateIds($scheduleId);

        // 授業情報を取得（ユーザー権限によるガードはfunction内で行う）
        $lesson = $this->fncTranGetTargetScheduleInfo($scheduleId);

        // 出欠ステータスが「未振替」以外はエラーとする
        if ($lesson->absent_status != AppConst::CODE_MASTER_35_3) {
            $this->illegalResponseErr();
        }

        // 講師リストを取得
        $tutorList = $this->mdlGetTutorList($lesson->campus_cd);

        // 要振替画面からの遷移用テンプレートを使用
        return view('pages.admin.transfer_check-required', [
            'editData' => [
                'schedule_id' => $scheduleId,
                'campus_cd' => $lesson->campus_cd,
                'student_id' => $lesson->student_id,
            ],
            'rules' => $this->rulesForInput(null),
            'student_name' => $lesson->student_name,
            'target_date' => $lesson->target_date,
            'period_no' => $lesson->period_no,
            'campus_name' => $lesson->campus_name,
            'course_name' => $lesson->course_name,
            'tutor_name' => $lesson->tutor_name,
            'subject_name' => $lesson->subject_name,
            'tutors' => $tutorList
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
        // 関連チェック
        Validator::make($request->all(), $this->rulesForInputRelated($request));

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {
            // 振替前の授業情報取得 -------------------------------
            $befSchedule = Schedule::query()
                ->where('schedule_id', $request->input('schedule_id'))
                // 教室管理者の場合、自分の教室コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            // スケジュール情報登録・更新 --------------------------

            // 振替授業情報設定
            $transferSchedule = $this->fncTranSetTrasferSchedule(AppConst::CODE_MASTER_54_1, $request, $befSchedule);

            // 振替時のテーブル更新処理
            // スケジュール情報登録 -----------------------------
            // 空きブース取得
            $boothCd = $this->fncScheSearchBooth(
                $transferSchedule->campus_cd,
                $transferSchedule->booth_cd,
                $transferSchedule->target_date,
                $transferSchedule->period_no,
                $transferSchedule->how_to_kind,
                null,
                false
            );
            if (!$boothCd) {
                // 空きなし時は不正な値としてエラーレスポンスを返却（事前にバリデーションを行っているため）
                $this->illegalResponseErr();
            }
            // スケジュール情報登録
            $transferSchedule->course_kind = AppConst::CODE_MASTER_42_1;
            $this->fncScheCreateSchedule($transferSchedule, $transferSchedule['target_date'], $boothCd, AppConst::CODE_MASTER_32_2);

            // 振替前スケジュール 更新 ---------------------------
            $befSchedule->absent_status = AppConst::CODE_MASTER_35_5;   // 振替済
            $befSchedule->save();

            // お知らせ通知＆メール送信用 --------------------------
            // 校舎名取得
            $campus_name = $this->mdlGetRoomName($befSchedule->campus_cd);
            // 生徒名取得
            $student_name = $this->mdlGetStudentName($befSchedule->student_id);
            // 講師名取得
            $tutor_name = $this->mdlGetTeacherName($transferSchedule->tutor_id);
            if ($request->filled('change_tid')) {
                // 振替で講師変更の場合、変更講師名を取得
                $tutor_name = $tutor_name . "（講師変更）";
            }
            // お知らせ通知 -----------------------------------
            // お知らせメッセージ登録
            $notice = new Notice;

            // タイトルと本文(Langから取得する)
            // 振替授業登録メッセージ
            $notice->title = Lang::get('message.notice.transfer_apply_regist_schedule.title');
            $notice->text = Lang::get(
                'message.notice.transfer_apply_regist_schedule.text',
                [
                    'targetDate' => CommonDateFormat::formatYmdDay($befSchedule->target_date),
                    'targetPeriodNo' => $befSchedule->period_no,
                    'transferDate' => CommonDateFormat::formatYmdDay($transferSchedule->target_date),
                    'transferPeriodNo' => $transferSchedule->period_no,
                    'roomName' => $campus_name,
                    'tutorName' => $tutor_name,
                    'studentName' => $student_name
                ]
            );
            // お知らせ種別（その他）
            $notice->notice_type = AppConst::CODE_MASTER_14_4;
            // 管理者ID
            $account = Auth::user();
            $notice->adm_id = $account->account_id;
            $notice->campus_cd = $account->campus_cd;
            // 保存
            $notice->save();

            // お知らせ宛先の登録（生徒）
            $noticeDestination = new NoticeDestination;
            // 先に登録したお知らせIDをセット
            $noticeDestination->notice_id = $notice->notice_id;
            // 宛先連番: 1
            $noticeDestination->destination_seq = 1;
            // 宛先種別（生徒）
            $noticeDestination->destination_type = AppConst::CODE_MASTER_15_2;
            // 生徒ID
            $noticeDestination->student_id = $befSchedule->student_id;
            // 保存
            $res = $noticeDestination->save();

            // お知らせ宛先の登録（講師）
            $noticeDestination = new NoticeDestination;
            // 先に登録したお知らせIDをセット
            $noticeDestination->notice_id = $notice->notice_id;
            // 宛先連番: 2
            $noticeDestination->destination_seq = 2;
            // 宛先種別（講師）
            $noticeDestination->destination_type = AppConst::CODE_MASTER_15_3;
            // 講師ID
            $noticeDestination->tutor_id = $befSchedule->tutor_id;
            // 保存
            $res = $noticeDestination->save();

            if ($request->filled('change_tid')) {
                // 講師変更の振替の場合
                // お知らせ宛先の登録（変更講師）
                $noticeDestination = new NoticeDestination;
                // 先に登録したお知らせIDをセット
                $noticeDestination->notice_id = $notice->notice_id;
                // 宛先連番: 3
                $noticeDestination->destination_seq = 3;
                // 宛先種別（講師）
                $noticeDestination->destination_type = AppConst::CODE_MASTER_15_3;
                // 変更講師ID
                $noticeDestination->tutor_id = $request->input('change_tid');
                // 保存
                $res = $noticeDestination->save();
            }

            // メール送信 ----------------------------------
            // save成功時のみ送信
            if ($res) {
                // 振替授業登録メッセージ
                $mail_body = [
                    'schedule_date_time' => $befSchedule->target_date->format('Y/m/d') .
                        ' ' . $befSchedule->period_no . '時限目',
                    'transfer_date_time' => $transferSchedule->target_date->format('Y/m/d') .
                        ' ' . $transferSchedule->period_no . '時限目',
                    'campus_name' => $campus_name,
                    'tutor_name' => $tutor_name,
                    'student_name' => $student_name
                ];
                // 送信先メールアドレス取得（生徒）
                $studentEmail = $this->mdlGetAccountMail($befSchedule->student_id, AppConst::CODE_MASTER_7_1);
                // 送信先メールアドレス取得（講師）
                $tutorEmail = $this->mdlGetAccountMail($befSchedule->tutor_id, AppConst::CODE_MASTER_7_2);
                $toEmails = [$studentEmail, $tutorEmail];
                if ($request->filled('change_tid')) {
                    // 講師変更の場合、変更講師を宛先に追加
                    $chgTutorEmail = $this->mdlGetAccountMail($request->input('change_tid'), AppConst::CODE_MASTER_7_2);
                    array_push($toEmails, $chgTutorEmail);
                }
                foreach ($toEmails as $email) {
                    Mail::to($email)->send(new TransferApplyRegistSchedule($mail_body));
                }
            }
        });
        return;
    }

    /**
     * 編集画面
     *
     * @param int transferApplyId 振替連絡Id
     * @return view
     */
    public function edit($transferApplyId)
    {
        // IDのバリデーション
        $this->validateIds($transferApplyId);

        // データを取得
        $tranApp = $this->fncTranGetEditTransferData($transferApplyId);

        // 講師リストを取得
        $tutorList = $this->mdlGetTutorList($tranApp['campus_cd']);

        return view('pages.admin.transfer_check-edit', [
            'editData' => $tranApp,
            'rules' => $this->rulesForInputEdit(null),
            'tutors' => $tutorList,
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
        Validator::make($request->all(), $this->rulesForInputEdit($request))->validate();
        // 関連チェック
        Validator::make($request->all(), $this->rulesForInputEditRelated($request));

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {
            // 振替依頼情報取得 -----------------------------------
            $transApp = TransferApplication::query()
                ->where('transfer_apply_id', $request->input('transfer_apply_id'))
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            // 振替前の授業情報取得 -------------------------------
            $befSchedule = Schedule::query()
                ->where('schedule_id', $transApp->schedule_id)
                // 教室管理者の場合、自分の教室コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            $befTutorId = $befSchedule->tutor_id;
            // スケジュール情報登録・更新 --------------------------

            // 振替授業情報設定
            $transferSchedule = $this->fncTranSetTrasferSchedule($request['transfer_kind'], $request, $befSchedule);

            if ($request->input('transfer_kind') == AppConst::CODE_MASTER_54_1) {
                // 振替時のテーブル更新処理
                // スケジュール情報登録 -----------------------------
                // 空きブース取得
                $boothCd = $this->fncScheSearchBooth(
                    $transferSchedule->campus_cd,
                    $transferSchedule->booth_cd,
                    $transferSchedule->target_date,
                    $transferSchedule->period_no,
                    $transferSchedule->how_to_kind,
                    null,
                    false
                );
                if (!$boothCd) {
                    // 空きなし時は不正な値としてエラーレスポンスを返却（事前にバリデーションを行っているため）
                    $this->illegalResponseErr();
                }
                // スケジュール情報登録
                $transferSchedule->course_kind = AppConst::CODE_MASTER_42_1;
                $newScheduleId = $this->fncScheCreateSchedule($transferSchedule, $transferSchedule['target_date'], $boothCd, AppConst::CODE_MASTER_32_2);

                // 振替前スケジュール 更新 ---------------------------
                $befSchedule->absent_status = AppConst::CODE_MASTER_35_5;   // 振替済
                $befSchedule->save();

                // 振替依頼情報 更新 ---------------------------
                $transApp->approval_status = AppConst::CODE_MASTER_3_5;   // 管理者対応済
                $transApp->transfer_schedule_id = $newScheduleId;
                $transApp->transfer_kind = AppConst::CODE_MASTER_54_1;    // 振替
                $transApp->comment = $request->input('comment'); // コメントを設定
                if ($request->filled('change_tid')) {
                    // 振替で講師変更の場合、変更講師IDを設定
                    $transApp->substitute_tutor_id = $request->input('change_tid');
                }
                $transApp->save();
            } else if ($request->input('transfer_kind') == AppConst::CODE_MASTER_54_2) {
                // 代講時のテーブル更新処理
                // スケジュール更新（代講授業） ----------------------
                $befSchedule->absent_status = AppConst::CODE_MASTER_35_0;   // 実施前・出席
                $befSchedule->substitute_kind = AppConst::CODE_MASTER_34_1; // 代講
                $befSchedule->tutor_id = $transferSchedule->tutor_id;       // 講師ID設定
                $befSchedule->absent_tutor_id = $transferSchedule->absent_tutor_id; // 欠席講師ID設定
                $befSchedule->save();

                // 振替依頼情報 更新 ---------------------------
                $transApp->approval_status = AppConst::CODE_MASTER_3_5;   // 管理者対応済
                $transApp->transfer_schedule_id = $befSchedule->schedule_id;
                $transApp->transfer_kind = AppConst::CODE_MASTER_54_2;    // 代講
                $transApp->comment = $request->input('comment'); // コメントを設定
                $transApp->substitute_tutor_id = $request->input('substitute_tid'); // 代講講師IDを設定
                $transApp->save();
            }

            // お知らせ通知＆メール送信用 --------------------------
            // 校舎名取得
            $campus_name = $this->mdlGetRoomName($befSchedule->campus_cd);
            // 生徒名取得
            $student_name = $this->mdlGetStudentName($befSchedule->student_id);
            // 講師名取得（授業実施者）
            $tutor_name = $this->mdlGetTeacherName($transferSchedule->tutor_id);
            if ($request->input('transfer_kind') == AppConst::CODE_MASTER_54_1 && $request->filled('change_tid')) {
                // 振替で講師変更の場合
                $tutor_name = $tutor_name . "（講師変更）";
            }

            // お知らせ通知 -----------------------------------
            // お知らせメッセージ登録
            $notice = new Notice;

            // タイトルと本文(Langから取得する)
            if ($request->input('transfer_kind') == AppConst::CODE_MASTER_54_1) {
                // 振替授業登録メッセージ
                $notice->title = Lang::get('message.notice.transfer_apply_regist_schedule.title');
                $notice->text = Lang::get(
                    'message.notice.transfer_apply_regist_schedule.text',
                    [
                        'targetDate' => CommonDateFormat::formatYmdDay($befSchedule->target_date),
                        'targetPeriodNo' => $befSchedule->period_no,
                        'transferDate' => CommonDateFormat::formatYmdDay($transferSchedule->target_date),
                        'transferPeriodNo' => $transferSchedule->period_no,
                        'roomName' => $campus_name,
                        'tutorName' => $tutor_name,
                        'studentName' => $student_name
                    ]
                );
            } else {
                // 代講授業登録メッセージ
                $notice->title = Lang::get('message.notice.substitute_regist_schedule.title');
                $notice->text = Lang::get(
                    'message.notice.substitute_regist_schedule.text',
                    [
                        'targetDate' => CommonDateFormat::formatYmdDay($befSchedule->target_date),
                        'targetPeriodNo' => $befSchedule->period_no,
                        'roomName' => $campus_name,
                        'tutorName' => $tutor_name,
                        'studentName' => $student_name
                    ]
                );
            }
            // お知らせ種別（その他）
            $notice->notice_type = AppConst::CODE_MASTER_14_4;
            // 管理者ID
            $account = Auth::user();
            $notice->adm_id = $account->account_id;
            $notice->campus_cd = $account->campus_cd;
            // 保存
            $notice->save();

            // お知らせ宛先の登録（生徒）
            $noticeDestination = new NoticeDestination;
            // 先に登録したお知らせIDをセット
            $noticeDestination->notice_id = $notice->notice_id;
            // 宛先連番: 1
            $noticeDestination->destination_seq = 1;
            // 宛先種別（生徒）
            $noticeDestination->destination_type = AppConst::CODE_MASTER_15_2;
            // 生徒ID
            $noticeDestination->student_id = $befSchedule->student_id;
            // 保存
            $res = $noticeDestination->save();

            // お知らせ宛先の登録（講師）
            $noticeDestination = new NoticeDestination;
            // 先に登録したお知らせIDをセット
            $noticeDestination->notice_id = $notice->notice_id;
            // 宛先連番: 2
            $noticeDestination->destination_seq = 2;
            // 宛先種別（講師）
            $noticeDestination->destination_type = AppConst::CODE_MASTER_15_3;
            // 講師ID
            $noticeDestination->tutor_id = $befTutorId;
            // 保存
            $res = $noticeDestination->save();

            if (($request->input('transfer_kind') == AppConst::CODE_MASTER_54_1 && $request->filled('change_tid'))
                || $request->input('transfer_kind') == AppConst::CODE_MASTER_54_2
            ) {
                // 講師変更の振替または代講の場合
                // お知らせ宛先の登録（代講・変更講師）
                $noticeDestination = new NoticeDestination;
                // 先に登録したお知らせIDをセット
                $noticeDestination->notice_id = $notice->notice_id;
                // 宛先連番: 3
                $noticeDestination->destination_seq = 3;
                // 宛先種別（講師）
                $noticeDestination->destination_type = AppConst::CODE_MASTER_15_3;
                // 変更講師IDまたは代講講師ID
                $noticeDestination->tutor_id = $transferSchedule->tutor_id;
                // 保存
                $res = $noticeDestination->save();
            }

            // メール送信 ----------------------------------
            // save成功時のみ送信
            if ($res) {

                if ($request->input('transfer_kind') == AppConst::CODE_MASTER_54_1) {
                    // 振替授業登録メッセージ
                    $mail_body = [
                        'schedule_date_time' => $befSchedule->target_date->format('Y/m/d') .
                            ' ' . $befSchedule->period_no . '時限目',
                        'transfer_date_time' => $transferSchedule->target_date->format('Y/m/d') .
                            ' ' . $transferSchedule->period_no . '時限目',
                        'campus_name' => $campus_name,
                        'tutor_name' => $tutor_name,
                        'student_name' => $student_name
                    ];
                    // 送信先メールアドレス取得（生徒）
                    $studentEmail = $this->mdlGetAccountMail($befSchedule->student_id, AppConst::CODE_MASTER_7_1);
                    // 送信先メールアドレス取得（講師）
                    $tutorEmail = $this->mdlGetAccountMail($befTutorId, AppConst::CODE_MASTER_7_2);
                    $toEmails = [$studentEmail, $tutorEmail];
                    if ($request->filled('change_tid')) {
                        // 講師変更の場合、変更講師を宛先に追加
                        $chgTutorEmail = $this->mdlGetAccountMail($request->input('change_tid'), AppConst::CODE_MASTER_7_2);
                        array_push($toEmails, $chgTutorEmail);
                    }
                    foreach ($toEmails as $email) {
                        Mail::to($email)->send(new TransferApplyRegistSchedule($mail_body));
                    }
                } else if ($request->input('transfer_kind') == AppConst::CODE_MASTER_54_2) {
                    // 代講授業登録メッセージ
                    $mail_body = [
                        'schedule_date_time' => $befSchedule->target_date->format('Y/m/d') .
                            ' ' . $befSchedule->period_no . '時限目',
                        'campus_name' => $campus_name,
                        'substitute_tutor_name' => $tutor_name,
                        'student_name' => $student_name
                    ];
                    // 送信先メールアドレス取得（生徒）
                    $studentEmail = $this->mdlGetAccountMail($befSchedule->student_id, AppConst::CODE_MASTER_7_1);
                    // 送信先メールアドレス取得（講師）
                    $tutorEmail = $this->mdlGetAccountMail($befTutorId, AppConst::CODE_MASTER_7_2);
                    // 代講の場合、代講講師を宛先に追加
                    $subTutorEmail = $this->mdlGetAccountMail($request->input('substitute_tid'), AppConst::CODE_MASTER_7_2);
                    $toEmails = [$studentEmail, $tutorEmail, $subTutorEmail];
                    foreach ($toEmails as $email) {
                        Mail::to($email)->send(new SubstituteRegistSchedule($mail_body));
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
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒
        $validationStudentList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('campus_cd')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            // 生徒リストを取得
            $list = $this->mdlGetStudentList($request['campus_cd']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 授業日・時限
        $validationLessonList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('student_id')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }

            // 振替対象授業日の範囲（当日も許可）
            $targetPeriod = $this->dtGetTargetDateFromTo(true);
            // 授業情報を取得
            $lessons = $this->fncTranGetTransferScheduleAdmin($targetPeriod['from_date'], $targetPeriod['to_date'], $request['student_id']);
            // プルダウン用にリスト作成
            $list = $this->mdlGetScheduleMasterList($lessons);

            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 時限
        $validationPeriodList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('campus_cd') || !$request->filled('target_date')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            // 時限リストを取得
            $list = $this->mdlGetPeriodListByDate($request['campus_cd'], $request['target_date']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 講師
        $validationTutorList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('campus_cd')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            // 講師リストを取得
            $list = $this->mdlGetTutorList($request['campus_cd']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += Schedule::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += Schedule::fieldRules('student_id', ['required', $validationStudentList]);
        $rules += Schedule::fieldRules('schedule_id', ['required', $validationLessonList]);
        $rules += Schedule::fieldRules('target_date', ['required']);
        $rules += Schedule::fieldRules('period_no', ['required', $validationPeriodList]);
        $rules += Schedule::fieldRules('start_time');
        // 講師ID 項目のバリデーションルールをベースにする
        $ruleTutorId = Schedule::getFieldRule('tutor_id');
        $rules += ['change_tid' => [$ruleTutorId, $validationTutorList]];

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

        // 振替前授業情報取得
        $schedule = Schedule::query()
            ->where('schedule_id', $request['schedule_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 振替授業情報設定
        $transferSchedule = $this->fncTranSetTrasferSchedule(AppConst::CODE_MASTER_54_1, $request, $schedule);

        // 独自バリデーション: 振替日の範囲チェック（管理者登録用）
        $validationTargetDate = function ($attribute, $value, $fail) use ($request) {
            $today = date('Y-m-d');
            // 当日より過去日の場合エラーとする
            if ($value < $today) {
                // 振替日範囲外エラー
                return $fail(Lang::get('validation.preferred_date_out_of_range'));
            }
        };
        // 独自バリデーション: 振替日・時限の関連チェック
        $validationDatePeriod = $this->fncTranGetValidateDatePeriod($transferSchedule, $schedule, $request);
        // 独自バリデーション: 変更時講師の重複チェック
        $validationChangeTutor = $this->fncTranGetValidateChangeTutor($transferSchedule, $schedule);
        // 独自バリデーション: 時限と開始時刻の相関チェック
        $validationPeriodStartTime = $this->fncTranGetValidatePeriodStartTime($transferSchedule);

        $rules += ['target_date' => [$validationTargetDate, $validationDatePeriod]];
        $rules += ['period_no' => [$validationDatePeriod]];
        if ($request->filled('start_time')) {
            $rules += ['start_time' => [$validationPeriodStartTime]];
        }
        if ($request->filled('change_tid')) {
            $rules += ['change_tid' => [$validationChangeTutor]];
        }

        return $rules;
    }

    /**
     * バリデーション(編集用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInputEdit(Request $request)
    {
        // リクエストデータチェック（項目チェック）
        $validator = Validator::make($request->all(), $this->rulesForInputEdit($request));
        if (count($validator->errors()) != 0) {
            // 項目チェックエラーがある場合はここでエラー情報を返す
            return $validator->errors();
        }
        // リクエストデータチェック（関連チェック追加）
        $validatorRerated = Validator::make($request->all(), $this->rulesForInputEditRelated($request));
        // 項目チェックエラー無し時は関連チェックを行い、結果を返す
        return $validatorRerated->errors();
    }

    /**
     * バリデーションルールを取得(編集用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInputEdit(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 時限
        $validationPeriodList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('campus_cd') || !$request->filled('target_date')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            // 時限リストを取得
            $list = $this->mdlGetPeriodListByDate($request['campus_cd'], $request['target_date']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 講師
        $validationTutorList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('campus_cd')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            // 講師リストを取得
            $list = $this->mdlGetTutorList($request['campus_cd']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 振替代講区分（ラジオ）
        $validationRadioTransferType = function ($attribute, $value, $fail) use ($request) {
            // ラジオの値のチェック
            if (
                $request->input('transfer_kind') != AppConst::CODE_MASTER_54_1 &&
                $request->input('transfer_kind') != AppConst::CODE_MASTER_54_2
            ) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 振替代講区分 (値のチェックも行う)
        $rules += TransferApplication::fieldRules('transfer_kind', ['required', $validationRadioTransferType]);
        $rules += TransferApplication::fieldRules('transfer_apply_id', ['required']);
        $rules += Schedule::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += TransferApplication::fieldRules('comment');

        // 振替を選択の場合 振替日・時限が必須
        $rules += Schedule::fieldRules('target_date', ['required_if:transfer_kind,' . AppConst::CODE_MASTER_54_1]);
        $rules += Schedule::fieldRules('period_no', ['required_if:transfer_kind,' . AppConst::CODE_MASTER_54_1, $validationPeriodList]);
        $rules += Schedule::fieldRules('start_time');
        // 講師ID 項目のバリデーションルールをベースにする
        $ruleTutorId = Schedule::getFieldRule('tutor_id');
        $rules += ['change_tid' => [$ruleTutorId, $validationTutorList]];

        // 代講を選択の場合 代講講師が必須
        $rules += ['substitute_tid' => ['required_if:transfer_kind,' . AppConst::CODE_MASTER_54_2, $ruleTutorId, 'different:tutor_id', $validationTutorList]];

        return $rules;
    }

    /**
     * バリデーションルールを取得(編集用・関連チェック)
     *
     * @return array ルール
     */
    private function rulesForInputEditRelated(?Request $request)
    {
        $rules = array();

        // 振替前授業情報取得
        $schedule = $this->fncTranGetScheduleByTranAppId($request['transfer_apply_id']);
        // 振替授業情報設定
        $transferSchedule = $this->fncTranSetTrasferSchedule($request['transfer_kind'], $request, $schedule);

        // 独自バリデーション: 振替日の範囲チェック
        $validationTargetDate = $this->fncTranGetValidateTargetDate($transferSchedule, $schedule);
        // 独自バリデーション: 振替日・時限の関連チェック
        $validationDatePeriod = $this->fncTranGetValidateDatePeriod($transferSchedule, $schedule, $request);
        // 独自バリデーション: 変更時講師の重複チェック
        $validationChangeTutor = $this->fncTranGetValidateChangeTutor($transferSchedule, $schedule);
        // 独自バリデーション: 時限と開始時刻の相関チェック
        $validationPeriodStartTime = $this->fncTranGetValidatePeriodStartTime($transferSchedule);

        if ($request->filled('transfer_kind') && $request['transfer_kind'] == AppConst::CODE_MASTER_54_1) {

            $rules += ['target_date' => [$validationTargetDate, $validationDatePeriod]];
            $rules += ['period_no' => [$validationDatePeriod]];
            if ($request->filled('start_time')) {
                $rules += ['start_time' => [$validationPeriodStartTime]];
            }
            if ($request->filled('change_tid')) {
                $rules += ['change_tid' => [$validationChangeTutor]];
            }
        } else if ($request->filled('transfer_kind') && $request['transfer_kind'] == AppConst::CODE_MASTER_54_2) {
            if ($request->filled('substitute_tid')) {
                $rules += ['substitute_tid' => [$validationChangeTutor]];
            }
        }
        return $rules;
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
        $this->validateIdsFromRequest($request, 'transfer_apply_id');

        // 削除前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForDelete($request))->validate();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            // 振替依頼情報取得
            $transApp = TransferApplication::where('transfer_apply_id', $request->input('transfer_apply_id'))
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            //----------------
            // スケジュール情報更新
            // （振替の情報をリセット）
            //----------------
            // 振替元スケジュール情報取得
            $schedule = Schedule::where('schedule_id', $transApp->schedule_id)
                // 教室管理者の場合、自分の教室コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                ->firstOrFail();

            // 出欠ステータス:実施前・出席
            $schedule->absent_status = AppConst::CODE_MASTER_35_0;
            // 振替依頼ID：null
            $schedule->transfer_id = null;
            // 更新
            $schedule->save();

            //----------------
            // 振替依頼情報削除
            //----------------
            // 振替依頼IDに紐づく日程情報を全て削除
            TransferApplicationDate::where('transfer_apply_id', $transApp->transfer_apply_id)
                ->delete();

            // 振替依頼情報の削除
            $transApp->delete();
        });

        return;
    }

    /**
     * バリデーション(削除用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForDelete(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForDelete($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(削除用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForDelete(?Request $request)
    {
        if (!$request) {
            return;
        }
        if (!$request->filled('transfer_apply_id')) {
            return;
        }

        // 振替依頼情報取得
        $transApp = TransferApplication::where('transfer_apply_id', $request['transfer_apply_id'])
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 振替元スケジュール情報取得
        $schedule = Schedule::where('schedule_id', $transApp->schedule_id)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            ->firstOrFail();

        $rules = array();

        // 独自バリデーション: 講師スケジュール重複チェック
        $validationDupTutor =  function ($attribute, $value, $fail) use ($schedule) {
            // 講師スケジュール重複チェック
            if (!$this->fncScheChkDuplidateTid(
                $schedule['target_date'],
                $schedule['start_time'],
                $schedule['end_time'],
                $schedule['tutor_id'],
                $schedule['schedule_id'],
                false
            )) {
                // 講師スケジュール重複エラー
                return false;
            }
            return true;
        };

        // 独自バリデーション: 生徒スケジュール重複チェック
        $validationDupStudent =  function ($attribute, $value, $fail) use ($schedule) {
            // 生徒スケジュール重複チェック
            if (!$this->fncScheChkDuplidateSid(
                $schedule['target_date'],
                $schedule['start_time'],
                $schedule['end_time'],
                $schedule['student_id'],
                $schedule['schedule_id'],
                false
            )) {
                // 生徒スケジュール重複エラー
                return false;
            }
            return true;
        };

        // 独自バリデーション: ブース空きチェック
        $validationDupBooth =  function ($attribute, $value, $fail) use ($schedule) {
            // ブース空きチェック
            if ($this->fncScheSearchBooth(
                $schedule['campus_cd'],
                $schedule['booth_cd'],
                $schedule['target_date'],
                $schedule['period_no'],
                $schedule['how_to_kind'],
                $schedule['schedule_id'],
                true
            ) == null) {
                // ブース空きなしエラー
                return false;
            }
            return true;
        };

        // 入力項目と紐づけないバリデーションは以下のように指定する
        // 振替取消時の講師・生徒・ブースの重複チェック
        Validator::extendImplicit('transfer_delete_duplicate_tutor', $validationDupTutor);
        Validator::extendImplicit('transfer_delete_duplicate_student', $validationDupStudent);
        Validator::extendImplicit('transfer_delete_duplicate_booth', $validationDupBooth);
        $rules += ['validate_schedule' => ['transfer_delete_duplicate_tutor', 'transfer_delete_duplicate_student', 'transfer_delete_duplicate_booth']];

        return $rules;
    }
}
