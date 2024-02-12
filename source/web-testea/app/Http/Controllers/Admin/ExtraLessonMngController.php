<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncScheduleTrait;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Libs\CommonDateFormat;
use App\Models\CodeMaster;
use App\Models\ExtraClassApplication;
use App\Models\MstCampus;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\Notice;
use App\Models\NoticeDestination;
use App\Mail\ExtraLessonAcceptToStudent;
use App\Mail\ExtraLessonAcceptToTutor;
use App\Models\StudentCampus;

/**
 * 追加授業申請受付 - コントローラ
 */
class ExtraLessonMngController extends Controller
{
    // 機能共通処理：スケジュール関連
    use FuncScheduleTrait;

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
        // ステータスのプルダウン取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);
        // 生徒リストを取得
        $studentList = $this->mdlGetStudentList();

        return view('pages.admin.extra_lesson_mng', [
            'rooms' => $rooms,
            'statusList' => $statusList,
            'studentList' => $studentList,
            'editData' => null,
            'rules' => $this->rulesForSearch()
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

        $form = $request->all();

        // クエリを作成
        $query = ExtraClassApplication::query();

        // 校舎の検索
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        } else {
            // 本部管理者の場合検索フォームから取得
            $query->SearchCampusCd($form);
        }

        // ステータスの検索
        $query->SearchStatus($form);
        // 生徒の検索
        $query->SearchStudentId($form);

        // データの取得
        $extraClasses = $query
            ->select(
                'extra_class_applications.extra_apply_id',
                'extra_class_applications.student_id',
                'extra_class_applications.campus_cd',
                'extra_class_applications.status',
                'extra_class_applications.apply_date',
                // 校舎名
                'mst_campuses.name as campus_name',
                // 生徒名
                'students.name as student_name',
                // コードマスタの名称（ステータス）
                'mst_codes.name as status_name'
            )
            // 校舎マスタとJOIN
            ->sdLeftJoin(MstCampus::class, function ($join) {
                $join->on('extra_class_applications.campus_cd', '=', 'mst_campuses.campus_cd');
            })
            // 生徒情報とJOIN
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('extra_class_applications.student_id', '=', 'students.student_id');
            })
            // コードマスタとJOIN ステータス
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('extra_class_applications.status', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_1);
            })
            ->orderby('apply_date', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $extraClasses);
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

        $rules += ExtraClassApplication::fieldRules('campus_cd', [$validationRoomList]);
        $rules += ExtraClassApplication::fieldRules('status', [$validationStateList]);
        $rules += ExtraClassApplication::fieldRules('student_id', [$validationStudentList]);

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
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'extra_apply_id');

        // データを取得
        $query = ExtraClassApplication::query();
        $extraClass = $query
            ->select(
                'extra_class_applications.extra_apply_id',
                'extra_class_applications.student_id',
                'extra_class_applications.campus_cd',
                'extra_class_applications.status',
                'extra_class_applications.request',
                'extra_class_applications.apply_date',
                'extra_class_applications.admin_comment',
                // 校舎名
                'mst_campuses.name as campus_name',
                // 生徒名
                'students.name as student_name',
                // コードマスタの名称（ステータス）
                'mst_codes.name as status_name'
            )
            // 校舎マスタとJOIN
            ->sdLeftJoin(MstCampus::class, function ($join) {
                $join->on('extra_class_applications.campus_cd', '=', 'mst_campuses.campus_cd');
            })
            // 生徒情報とJOIN
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('extra_class_applications.student_id', '=', 'students.student_id');
            })
            // コードマスタとJOIN ステータス
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('extra_class_applications.status', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_1);
            })
            ->where('extra_apply_id', $request['extra_apply_id'])
            ->firstOrFail();

        return $extraClass;
    }

    //==========================
    // 登録・編集・削除
    //==========================

    /**
     * 時間割情報取得（時限プルダウン選択時）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 時間割情報
     */
    public function getDataSelectTimetable(Request $request)
    {
        // 時限のバリデーション
        $this->validateIdsFromRequest($request, 'period_no');
        // 日付のバリデーション
        $this->validateDatesFromRequest($request, 'target_date');

        // 校舎コード・日付・時限を取得
        $campusCd = $request->input('campus_cd');
        $targetDate = $request->input('target_date');
        $periodNo = $request->input('period_no');

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------
        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $this->guardRoomAdminRoomcd($campusCd);

        // 時限リストを取得（校舎・日付から）
        $periods = $this->mdlGetPeriodListByDate($campusCd, $targetDate);

        // [ガード] 時限がプルダウンの中にあるかチェック
        $this->guardListValue($periods, $periodNo);

        //---------------------------
        // 時間割情報（開始時刻・終了時刻）を返却する
        //---------------------------
        // 日付から時間割区分を取得
        $timetableKind = $this->fncScheGetTimeTableKind($campusCd, $targetDate);

        // 時間割区分・指定時限から、対応する時間割情報を取得
        $periodInfo = $this->fncScheGetTimetableByPeriod($campusCd, $timetableKind, $periodNo);
        $startTime = $periodInfo->start_time;
        $endTime = $periodInfo->end_time;

        return [
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i'),
            'timetable_kind' => $timetableKind
        ];
    }

    /**
     * 時限情報取得（日付ピッカー変更）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 時限情報
     */
    public function getDataSelect(Request $request)
    {
        // 日付のバリデーション
        if ($request->input('target_date')) {
            $this->validateDatesFromRequest($request, 'target_date');
        }

        // 校舎コード・日付を取得
        $campusCd = $request->input('campus_cd');
        $targetDate = $request->input('target_date');

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $this->guardRoomAdminRoomcd($campusCd);

        //---------------------------
        // 時限リストを返却する
        //---------------------------
        // 時限リストを取得（校舎・日付から）
        $periods = $this->mdlGetPeriodListByDate($campusCd, $targetDate);
        // 日付から時間割区分を取得
        $timetableKind = null;
        if (count($periods) > 0) {
            $timetableKind = $this->fncScheGetTimeTableKind($campusCd, $targetDate);
        }

        return [
            'selectItems' => $this->objToArray($periods),
            'timetable_kind' => $timetableKind
        ];
    }

    /**
     * 登録画面
     * @param int $applyId 追加授業依頼ID
     * @return view
     */
    public function new($applyId)
    {
        // IDのバリデーション
        $this->validateIds($applyId);

        // 対象データを取得
        $query = ExtraClassApplication::query();
        $extraClass = $query
            ->select(
                'extra_class_applications.student_id',
                'extra_class_applications.campus_cd',
            )
            ->where('extra_apply_id', $applyId)
            // ステータス「未対応」のみ表示可能
            ->where('status', AppConst::CODE_MASTER_1_0)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 対象データより生徒ID・校舎コードを取得
        $sid = $extraClass->student_id;
        $campusCd = $extraClass->campus_cd;

        // 生徒名を取得する
        $studentName = $this->mdlGetStudentName($sid);
        // 校舎名を取得する
        $campusName = $this->mdlGetRoomName($campusCd);

        // コースリストを取得 コース種別=授業単に絞る
        $courses = $this->mdlGetCourseList(AppConst::CODE_MASTER_42_1);
        // 講師リストを取得 講師所属校舎に絞る
        $tutors = $this->mdlGetTutorList($campusCd);
        // 教科リストを取得
        $subjects = $this->mdlGetSubjectList();
        // 通塾種別リストを取得
        $howToKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_33);

        return view('pages.admin.extra_lesson_mng-new', [
            'rules' => $this->rulesForInput(null),
            'studentName' => $studentName,
            'campusName' => $campusName,
            'courses' => $courses,
            'tutors' => $tutors,
            'subjects' => $subjects,
            'howToKindList' => $howToKindList,
            'editData' => [
                'student_id' => $sid,
                'campus_cd' => $campusCd,
            ],
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
        // 登録前バリデーション（関連チェック）。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInputRelated($request))->validate();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            // スケジュール情報をセット
            $scheduleData = $request->only(
                'campus_cd',
                'target_date',
                'period_no',
                'start_time',
                'end_time',
                'course_cd',
                'student_id',
                'tutor_id',
                'subject_cd',
                'how_to_kind',
                'memo',
            );
            // 内部で設定する固定値
            $scheduleData['course_kind'] = AppConst::CODE_MASTER_42_1;
            $scheduleData['lesson_kind'] = AppConst::CODE_MASTER_31_3;
            $scheduleData['tentative_status'] = AppConst::CODE_MASTER_36_0;

            //------------------------
            // 空きブース検索
            //------------------------
            // ブースマスタから対象校舎のブースを取得
            $boothFirst = null;
            $arrMstBooths = $this->fncScheGetBoothFromMst($scheduleData['campus_cd'], $scheduleData['how_to_kind']);
            if ($arrMstBooths) {
                $boothFirst = $arrMstBooths[0];
            }
            // 空きブース取得
            $booth = $this->fncScheSearchBooth(
                $scheduleData['campus_cd'],
                $boothFirst,
                $scheduleData['target_date'],
                $scheduleData['period_no'],
                $scheduleData['how_to_kind'],
                null,
                false
            );
            if (!$booth) {
                // 空きなし時は不正な値としてエラーレスポンスを返却（事前にバリデーションを行っているため）
                $this->illegalResponseErr();
            }

            //------------------------
            // スケジュール登録
            //------------------------
            $this->fncScheCreateSchedule($scheduleData, $scheduleData['target_date'], $booth, AppConst::CODE_MASTER_32_1);

            //-------------------------
            // お知らせメッセージの登録（生徒）
            //-------------------------
            $notice = new Notice;

            // タイトルと本文(Langから取得する)
            $notice->title = Lang::get('message.notice.extra_lesson_accept_student.title');
            $notice->text = Lang::get(
                'message.notice.extra_lesson_accept_student.text',
                [
                    'targetDate' => CommonDateFormat::formatYmdDay($scheduleData['target_date']),
                    'periodNo' => $scheduleData['period_no'],
                    'campusName' => $this->mdlGetRoomName($scheduleData['campus_cd']),
                    'tutorName' => $this->mdlGetTeacherName($scheduleData['tutor_id']),
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
            $noticeDestination->student_id = $scheduleData['student_id'];
            // 保存
            $noticeDestination->save();

            //-------------------------
            // お知らせメッセージの登録(講師)
            //-------------------------
            $notice = new Notice;

            // タイトルと本文(Langから取得する)
            $notice->title = Lang::get('message.notice.extra_lesson_accept_tutor.title');
            $notice->text = Lang::get(
                'message.notice.extra_lesson_accept_tutor.text',
                [
                    'targetDate' => CommonDateFormat::formatYmdDay($scheduleData['target_date']),
                    'periodNo' => $scheduleData['period_no'],
                    'campusName' => $this->mdlGetRoomName($scheduleData['campus_cd']),
                    'studentName' => $this->mdlGetStudentName($scheduleData['student_id'])
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
            $noticeDestination->tutor_id = $scheduleData['tutor_id'];
            // 保存
            $noticeDestination->save();

            //-------------------------
            // メール送信(生徒)
            //-------------------------
            // メール本文をセット
            $mail_body = [
                'targetDate' => CommonDateFormat::formatYmdDay($scheduleData['target_date']),
                'periodNo' => $scheduleData['period_no'],
                'campusName' => $this->mdlGetRoomName($scheduleData['campus_cd']),
                'tutorName' => $this->mdlGetTeacherName($scheduleData['tutor_id'])
            ];

            // 生徒のメールアドレスを取得
            $email = $this->mdlGetAccountMail($scheduleData['student_id'], AppConst::CODE_MASTER_7_1);

            // メール送信
            Mail::to($email)->send(new ExtraLessonAcceptToStudent($mail_body));

            //-------------------------
            // メール送信(講師)
            //-------------------------
            // メール本文をセット
            $mail_body = [
                'targetDate' => CommonDateFormat::formatYmdDay($scheduleData['target_date']),
                'periodNo' => $scheduleData['period_no'],
                'campusName' => $this->mdlGetRoomName($scheduleData['campus_cd']),
                'studentName' => $this->mdlGetStudentName($scheduleData['student_id'])
            ];

            // 講師のメールアドレスを取得
            $email = $this->mdlGetAccountMail($scheduleData['tutor_id'], AppConst::CODE_MASTER_7_2);

            // メール送信
            Mail::to($email)->send(new ExtraLessonAcceptToTutor($mail_body));
        });

        return;
    }

    /**
     * 編集画面
     *
     * @param int $applyId 追加授業依頼ID
     * @return view
     */
    public function edit($applyId)
    {
        // IDのバリデーション
        $this->validateIds($applyId);

        // 対象データを取得
        $query = ExtraClassApplication::query();
        $extraClass = $query
            ->select(
                'extra_class_applications.extra_apply_id',
                'extra_class_applications.status',
                'extra_class_applications.request',
                'extra_class_applications.admin_comment',
                // 校舎名
                'mst_campuses.name as campus_name',
                // 生徒名
                'students.name as student_name',
            )
            // 校舎マスタとJOIN
            ->sdLeftJoin(MstCampus::class, function ($join) {
                $join->on('extra_class_applications.campus_cd', '=', 'mst_campuses.campus_cd');
            })
            // 生徒情報とJOIN
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('extra_class_applications.student_id', '=', 'students.student_id');
            })
            ->where('extra_apply_id', $applyId)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // ステータスのプルダウン取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_1);

        return view('pages.admin.extra_lesson_mng-edit', [
            'editData' => $extraClass,
            'statusList' => $statusList,
            'rules' => $this->rulesForInput(null),
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
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // 変更する項目のみに絞る
        $form = $request->only(
            'status',
            'admin_comment',
        );

        // 対象データを取得
        $extraClass = ExtraClassApplication::where('extra_apply_id', $request['extra_apply_id'])
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $extraClass->fill($form)->save();

        return;
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
        $this->validateIdsFromRequest($request, 'extra_apply_id');

        // 対象データを取得
        $extraClass = ExtraClassApplication::where('extra_apply_id', $request['extra_apply_id'])
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 削除
        $extraClass->delete();

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

        // スケジュール登録データの関連バリデーション
        // MEMO:依頼編集画面はこのバリデーションは飛ばすよう、$requestにstatusが有るか無いか判定する
        if (!isset($request['status'])) {
            $validatorRerated = Validator::make($request->all(), $this->rulesForInputRelated($request));
            // 項目チェックエラー無し時は関連チェックを行い、結果を返す
            return $validatorRerated->errors();
        }
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        // 独自バリデーション: リストのチェック 時限
        $validationPeriodList =  function ($attribute, $value, $fail) use ($request) {
            // 時限リストを取得（校舎・日付から）
            $list = $this->mdlGetPeriodListByDate($request['campus_cd'], $request['target_date']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック コース
        $validationCourseList =  function ($attribute, $value, $fail) {
            // コースリストを取得
            $list = $this->mdlGetCourseList(AppConst::CODE_MASTER_42_1);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 講師
        $validationTutorList =  function ($attribute, $value, $fail) use ($request) {
            // 講師リストを取得
            $list = $this->mdlGetTutorList($request['campus_cd']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 教科
        $validationSubjectList =  function ($attribute, $value, $fail) {
            // 教科リストを取得
            $list = $this->mdlGetSubjectList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 通塾種別
        $validationHowToKindList =  function ($attribute, $value, $fail) {
            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_33);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        // 追加授業スケジュール登録画面のバリデーション
        if ($request && !isset($request['status'])) {
            $rules += Schedule::fieldRules('target_date', ['required']);
            $rules += Schedule::fieldRules('period_no', ['required', $validationPeriodList]);
            $rules += Schedule::fieldRules('start_time', ['required']);
            $rules += Schedule::fieldRules('end_time', ['required']);
            $rules += Schedule::fieldRules('course_cd', ['required', $validationCourseList]);
            $rules += Schedule::fieldRules('tutor_id', ['required', $validationTutorList]);
            $rules += Schedule::fieldRules('subject_cd', ['required', $validationSubjectList]);
            $rules += Schedule::fieldRules('how_to_kind', ['required', $validationHowToKindList]);
            $rules += Schedule::fieldRules('memo');
        }

        // 追加授業依頼編集画面のバリデーション
        if ($request && isset($request['status'])) {
            $rules += ExtraClassApplication::fieldRules('status', ['required']);
            $rules += ExtraClassApplication::fieldRules('admin_comment');
        }

        return $rules;
    }

    /**
     * バリデーションルールを取得(登録用・関連チェック)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInputRelated(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: 生徒スケジュール重複チェック
        $validationDupStudent =  function ($attribute, $value, $fail) use ($request) {
            return $this->fncScheValidateStudent($request, AppConst::SCHEDULE_KIND_NEW, $attribute, $value, $fail);
        };

        // 独自バリデーション: 講師スケジュール重複チェック
        $validationDupTutor =  function ($attribute, $value, $fail) use ($request) {
            return $this->fncScheValidateTutor($request, AppConst::SCHEDULE_KIND_NEW, $attribute, $value, $fail);
        };

        // 独自バリデーション: 時限と開始時刻の相関チェック
        $validationPeriodStartTime =  function ($attribute, $value, $fail) use ($request) {
            return $this->fncScheValidatePeriodStartTime($request, $attribute, $value, $fail);
        };

        // 独自バリデーション: 空きブースチェック
        $validationDupBooth =  function ($attribute, $value, $fail) use ($request) {

            if (
                !$request->filled('campus_cd') || !$request->filled('target_date')
                || !$request->filled('period_no') || !$request->filled('how_to_kind')
            ) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return true;
            }

            // ブースマスタから対象校舎のブースを取得
            $boothFirst = null;
            $arrMstBooths = $this->fncScheGetBoothFromMst($request['campus_cd'], $request['how_to_kind']);
            if ($arrMstBooths) {
                $boothFirst = $arrMstBooths[0];
            }
            // ブース重複チェック（空きブース検索あり）
            $booth = $this->fncScheSearchBooth(
                $request['campus_cd'],
                $boothFirst,
                $request['target_date'],
                $request['period_no'],
                $request['how_to_kind'],
                null,
                false
            );
            if (!$booth) {
                // ブース空きなしエラー
                return false;
            }
            return true;
        };

        $rules += ['student_id' => [$validationDupStudent]];
        $rules += ['tutor_id' => [$validationDupTutor]];
        $rules += ['start_time' => [$validationPeriodStartTime]];
        // 空きブースチェック
        Validator::extendImplicit('duplicate_booth', $validationDupBooth);
        $rules += ['booth_cd' => ['duplicate_booth']];
        return $rules;
    }
}
