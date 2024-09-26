<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncAbsentTrait;
use App\Consts\AppConst;
use App\Models\AbsentApplication;
use App\Models\Account;
use App\Models\MstCampus;
use App\Models\Tutor;
use App\Models\Notice;
use App\Models\NoticeDestination;
use App\Models\ClassMember;
use App\Mail\AbsentApplyToTeacher;
use App\Mail\AbsentApplyToStudent;
use App\Libs\CommonDateFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/**
 * 欠席申請受付 - コントローラ
 */
class AbsentAcceptController extends Controller
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
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);
        // 生徒リストを取得
        $studentList = $this->mdlGetStudentList();
        // 講師リストを取得
        $tutorList = $this->mdlGetTutorList();

        return view('pages.admin.absent_accept', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'statusList' => $statusList,
            'studentList' => $studentList,
            'tutorList' => $tutorList
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
        $validator = Validator::make($request->all(), $this->rulesForSearch());
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
        Validator::make($request->all(), $this->rulesForSearch())->validate();

        $form = $request->all();

        // クエリを作成
        $query = AbsentApplication::query();

        // 校舎の検索
        $query->SearchCampusCd($form);
        // ステータスの検索
        $query->SearchStatus($form);
        // 生徒の検索
        $query->SearchStudentId($form);
        // 講師の検索
        $query->SearchTutorId($form);

        // データの取得
        $absentList = $this->getAbsentApplyDetail($query)
            ->orderby('absent_applications.apply_date', 'desc')
            ->orderby('schedules.target_date', 'asc')
            ->orderby('schedules.period_no', 'asc')
            ->orderby('schedules.campus_cd', 'asc')
            ->orderby('absent_applications.absent_apply_id', 'asc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $absentList);
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
        $validationRoomList =  function ($attribute, $value, $fail) {
            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {
            // ステータスリストを取得
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒
        $validationStudentList =  function ($attribute, $value, $fail) {
            // 生徒リストを取得
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

        $rules += MstCampus::fieldRules('campus_cd', [$validationRoomList]);
        $rules += AbsentApplication::fieldRules('status', [$validationStateList]);
        $rules += AbsentApplication::fieldRules('student_id', [$validationStudentList]);
        $rules += Tutor::fieldRules('tutor_id', [$validationTutorList]);

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
        // MEMO:データ取得は詳細モーダル・受付モーダル共通

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'absent_apply_id');

        // クエリを作成
        $query = AbsentApplication::query();

        // 欠席申請詳細を取得
        $absentApply = $this->getAbsentApplyDetail($query)
            ->where('absent_applications.absent_apply_id', '=', $request->absent_apply_id)
            ->firstOrFail();

        return $absentApply;
    }

    /**
     * 受付処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function execModal(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'absent_apply_id');

        // モーダルによって処理を行う
        $modal = $request->input('target');

        switch ($modal) {
            case "#modal-dtl-acceptance":
                //--------
                // 受付
                //--------
                // トランザクション(例外時は自動的にロールバック)
                DB::transaction(function () use ($request) {
                    //-------------------------
                    // 欠席申請情報のステータス更新
                    //-------------------------
                    // 対象データ取得
                    // お知らせメッセージで使用するデータも併せて取得する
                    $query = AbsentApplication::query();
                    $absentApply = $this->getAbsentApplyDetail($query)
                        ->where('absent_apply_id', $request->absent_apply_id)
                        // ステータス「未対応」のみ
                        ->where('status', AppConst::CODE_MASTER_1_0)
                        ->firstOrFail();

                    // ステータスを「対応済」に変更する
                    $absentApply->status = AppConst::CODE_MASTER_1_1;
                    // 保存
                    $absentApply->save();

                    //-------------------------
                    // 出欠ステータスの更新
                    //-------------------------
                    // 対象授業・対象生徒を絞り込み
                    $classMember = ClassMember::where('schedule_id', $absentApply->schedule_id)
                        ->where('student_id', $absentApply->student_id)
                        ->firstOrFail();

                    // 出欠ステータスを「欠席（1対多授業）」に変更する
                    $classMember->absent_status = AppConst::CODE_MASTER_35_6;
                    // 保存
                    $classMember->save();

                    //-------------------------
                    // お知らせメッセージの登録（生徒）
                    //-------------------------
                    $notice = new Notice;

                    // タイトルと本文(Langから取得する)
                    $notice->title = Lang::get('message.notice.absent_apply_accept_student.title');
                    $notice->text = Lang::get(
                        'message.notice.absent_apply_accept_student.text',
                        [
                            'targetDate' => CommonDateFormat::formatYmdDay($absentApply->target_date),
                            'periodNo' => $absentApply->period_no,
                            'campusName' => $absentApply->campus_name
                        ]
                    );

                    // お知らせ種別 その他
                    $notice->notice_type = AppConst::CODE_MASTER_14_4;
                    // 送信元情報
                    $account = Auth::user();
                    $notice->adm_id = $account->account_id;
                    $notice->campus_cd = $account->campus_cd;
                    // 保存
                    $notice->save();

                    //-------------------------
                    // お知らせ宛先の登録（生徒）
                    //-------------------------
                    $noticeDestination = new NoticeDestination();

                    // 先に登録したお知らせIDをセット
                    $noticeDestination->notice_id = $notice->notice_id;
                    // 宛先連番: 1固定
                    $noticeDestination->destination_seq = 1;
                    // 宛先種別（生徒）
                    $noticeDestination->destination_type = AppConst::CODE_MASTER_15_2;
                    // 生徒ID
                    $noticeDestination->student_id = $absentApply->student_id;
                    // 保存
                    $noticeDestination->save();

                    //-------------------------
                    // お知らせメッセージの登録(講師)
                    //-------------------------
                    $notice = new Notice;

                    // タイトルと本文(Langから取得する)
                    $notice->title = Lang::get('message.notice.absent_apply_accept_tutor.title');
                    $notice->text = Lang::get(
                        'message.notice.absent_apply_accept_tutor.text',
                        [
                            'studentName' => $absentApply->student_name,
                            'targetDate' => CommonDateFormat::formatYmdDay($absentApply->target_date),
                            'periodNo' => $absentApply->period_no,
                            'campusName' => $absentApply->campus_name
                        ]
                    );

                    // お知らせ種別 その他
                    $notice->notice_type = AppConst::CODE_MASTER_14_4;
                    // 送信元情報
                    $notice->adm_id = $account->account_id;
                    $notice->campus_cd = $account->campus_cd;
                    // 保存
                    $notice->save();

                    //-------------------------
                    // お知らせ宛先の登録(講師)
                    //-------------------------
                    $noticeDestination = new NoticeDestination();
                    // 先に登録したお知らせIDをセット
                    $noticeDestination->notice_id = $notice->notice_id;
                    // 宛先連番: 1固定
                    $noticeDestination->destination_seq = 1;
                    // 宛先種別（講師）
                    $noticeDestination->destination_type = AppConst::CODE_MASTER_15_3;
                    // 講師ID
                    $noticeDestination->tutor_id = $absentApply->tutor_id;
                    // 保存
                    $noticeDestination->save();

                    //-------------------------
                    // メール送信(生徒)
                    //-------------------------
                    // メール本文をセット
                    $mail_body = [
                        'targetDate' => CommonDateFormat::formatYmdDay($absentApply->target_date),
                        'periodNo' => $absentApply->period_no,
                        'campusName' => $absentApply->campus_name
                    ];

                    // 生徒のメールアドレスを取得
                    $studentAccount = Account::where('account_id', $absentApply->student_id)
                        // アカウント種別：生徒
                        ->where('account_type', AppConst::CODE_MASTER_7_1)
                        ->firstOrFail();

                    $email = $studentAccount->email;

                    // メール送信
                    Mail::to($email)->send(new AbsentApplyToStudent($mail_body));

                    //-------------------------
                    // メール送信(講師)
                    //-------------------------
                    // メール本文をセット
                    $mail_body = [
                        'studentName' => $absentApply->student_name,
                        'targetDate' => CommonDateFormat::formatYmdDay($absentApply->target_date),
                        'periodNo' => $absentApply->period_no,
                        'campusName' => $absentApply->campus_name
                    ];

                    // 講師のメールアドレスを取得
                    $tutorAccount = Account::where('account_id', $absentApply->tutor_id)
                        // アカウント種別：講師
                        ->where('account_type', AppConst::CODE_MASTER_7_2)
                        ->firstOrFail();

                    $email = $tutorAccount->email;

                    // メール送信
                    Mail::to($email)->send(new AbsentApplyToTeacher($mail_body));
                });
                return;

            default:
                // モーダルIDが該当しない場合
                $this->illegalResponseErr();
        }
    }
}
