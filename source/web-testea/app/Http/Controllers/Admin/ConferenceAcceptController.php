<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Mail\ConferenceAcceptToStudent;
use App\Models\Account;
use App\Models\CodeMaster;
use App\Models\MstBooth;
use App\Models\MstCourse;
use App\Models\Schedule;
use App\Models\AdminUser;
use App\Models\Student;
use App\Models\Conference;
use App\Models\ConferenceDate;
use App\Models\Notice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Models\NoticeDestination;
use App\Libs\AuthEx;
use App\Http\Controllers\Traits\FuncScheduleTrait;

/**
 * 面談日程連絡受付 - コントローラ
 */
class ConferenceAcceptController extends Controller
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
        // 校舎プルダウン
        $rooms = $this->mdlGetRoomList(false);

        // ステータスプルダウン
        $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_5);

        return view('pages.admin.conference_accept', [
            'rules' => $this->rulesForSearch(null),
            'rooms' => $rooms,
            'states' => $states,
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
            $students = $this->mdlGetStudentList();
        } else {
            $students = $this->mdlGetStudentList($campus_cd);
        }

        return [
            'selectItems' => $this->objToArray($students),
        ];
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
        $validator = Validator::make($request->all(), $this->rulesForSearch($request));
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
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = Conference::query();

        // 校舎コード選択による絞り込み条件
        // -1 は未選択状態のため、-1以外の場合に校舎コードの絞り込みを行う
        if (isset($form['campus_cd']) && filled($form['campus_cd']) && $form['campus_cd'] != -1) {
            // 検索フォームから取得（スコープ）
            $query->SearchCampusCd($form);
        }

        // ステータスの絞り込み条件
        $query->SearchStatus($form);

        // 生徒の絞り込み条件
        $query->SearchStudentId($form);

        // 連絡日の絞り込み条件
        $query->SearchConferenceDateFrom($form);
        $query->SearchConferenceDateTo($form);

        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        $conferenceList = $query
            ->select(
                'conferences.conference_id',
                'conferences.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'conferences.campus_cd',
                // 校舎の名称
                'campus_names.room_name as campus_name',
                'conferences.status',
                // コードマスタの名称（ステータス）
                'mst_codes.name as status_name',
                'conferences.conference_date',
                'conferences.apply_date',
                'conferences.start_time',
                'conferences.conference_schedule_id',
                'schedules.adm_id as adm_id',
                'admin_users.name as adm_name'
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('conferences.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'conferences.student_id', '=', 'students.student_id')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('conferences.status', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_5);
            })
            // スケジュール情報とJOIN
            ->sdLeftJoin(Schedule::class, 'conferences.conference_schedule_id', '=', 'schedules.schedule_id')
            // アカウントテーブルをLeftJOIN
            ->sdLeftJoin(AdminUser::class, 'schedules.adm_id', '=', 'admin_users.adm_id')
            ->orderBy('conferences.apply_date', 'desc')
            ->orderBy('conferences.conference_id', 'asc');

        return $this->getListAndPaginator($request, $conferenceList);
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return mixed ルール
     */
    private function rulesForSearch(?Request $request)
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

        // 独自バリデーション: リストのチェック ステータス
        $validationStatus =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_5);
            if (!isset($states[$value])) {
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

        // FromとToの大小チェックバリデーションを追加(Fromが存在する場合のみ)
        $ruleApplyuDate = Conference::getFieldRule('apply_date');
        // FromとToの大小チェックバリデーションを追加(Fromが存在する場合のみ)
        $validateFromTo = [];
        $keyFrom = 'apply_date_from';
        if (isset($request[$keyFrom]) && filled($request[$keyFrom])) {
            $validateFromTo = ['after_or_equal:' . $keyFrom];
        }

        $rules += Conference::fieldRules('campus_cd', [$validationCampusList]);
        $rules += Conference::fieldRules('student_id', [$validationStudentList]);
        $rules += Conference::fieldRules('status', [$validationStatus]);
        $rules += ['apply_date_from' => $ruleApplyuDate];
        $rules += ['apply_date_to' => array_merge($validateFromTo, $ruleApplyuDate)];

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
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $id = $request->input('id');

        // クエリを作成
        $queryConference = Conference::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $queryConference->where($this->guardRoomAdminTableWithRoomCd());

        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        $conference = $queryConference
            // IDを指定
            ->where('conferences.conference_id', $id)
            // データを取得
            ->select(
                'conferences.conference_id',
                'conferences.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'conferences.campus_cd',
                // 校舎名
                'campus_names.room_name as campus_name',
                'conferences.status',
                // コードマスタの名称（ステータス）
                'mst_codes.name as status',
                'conferences.comment',
                'conferences.apply_date',
                'conferences.conference_date',
                'conferences.start_time',
                'conferences.end_time',
                'conferences.conference_schedule_id',
                // 面談希望日時
                'conference_dates1.conference_date as conference_date1',
                'conference_dates1.start_time as start_time1',
                'conference_dates2.conference_date as conference_date2',
                'conference_dates2.start_time as start_time2',
                'conference_dates3.conference_date as conference_date3',
                'conference_dates3.start_time as start_time3',
                'schedules.booth_cd',
                // ブース名
                'mst_booths.name as booth_name',
                // 面談担当者ID
                'schedules.adm_id as adm_id',
                // 管理者メモ
                'schedules.memo as memo',
                // 面談担当者
                'admin_users.name as adm_name'
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('conferences.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'conferences.student_id', '=', 'students.student_id')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('conferences.status', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_5);
            })
            // 面談連絡情報とJOIN
            ->sdLeftJoin(ConferenceDate::class, function ($join) {
                $join->on('conferences.conference_id', '=', 'conference_dates1.conference_id')
                    ->where('conference_dates1.request_no', 1);
            }, 'conference_dates1')
            // 面談連絡情報とJOIN
            ->sdLeftJoin(ConferenceDate::class, function ($join) {
                $join->on('conferences.conference_id', '=', 'conference_dates2.conference_id')
                    ->where('conference_dates2.request_no', 2);
            }, 'conference_dates2')
            // 面談連絡情報とJOIN
            ->sdLeftJoin(ConferenceDate::class, function ($join) {
                $join->on('conferences.conference_id', '=', 'conference_dates3.conference_id')
                    ->where('conference_dates3.request_no', 3);
            }, 'conference_dates3')
            // スケジュール情報とJOIN
            ->sdLeftJoin(Schedule::class, 'conferences.conference_schedule_id', '=', 'schedules.schedule_id')
            // ブース名の取得
            ->sdLeftJoin(MstBooth::class, function ($join) {
                $join->on('schedules.campus_cd', 'mst_booths.campus_cd');
                $join->on('schedules.booth_cd', 'mst_booths.booth_cd');
            })
            // アカウントテーブルをLeftJOIN
            ->sdLeftJoin(AdminUser::class, 'schedules.adm_id', '=', 'admin_users.adm_id')
            ->firstOrFail();

        return $conference;
    }

    /**
     * 生徒情報とブース取得（校舎リスト選択時）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 生徒情報 ブース情報
     */
    public function getDataSelectNew(Request $request)
    {
        // campus_cdを取得
        $campus_cd = $request->input('id');

        // 生徒リスト取得
        if ($campus_cd == -1 || !filled($campus_cd)) {
            // -1 または 空白の場合、自分の受け持ちの生徒だけに絞り込み
            // 生徒リストを取得
            $students = $this->mdlGetStudentList();
        } else {
            $students = $this->mdlGetStudentList($campus_cd);
        }
        // ブースリスト取得
        if ($campus_cd == -1 || !filled($campus_cd)) {
            // -1 または 空白の場合、全面談ブース
            // ブースリストを取得
            $booths = $this->mdlGetBoothList(null, AppConst::CODE_MASTER_41_3);
        } else {
            $booths = $this->mdlGetBoothList($campus_cd, AppConst::CODE_MASTER_41_3);
        }

        return [
            'selectItems' => $this->objToArray($students),
            'selectLists' => $this->objToArray($booths)
        ];
    }

    //==========================
    // 登録・編集
    //==========================

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        // 校舎プルダウン
        $rooms = $this->mdlGetRoomList(false);

        return view('pages.admin.conference_accept-new', [
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

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {
            // フォームから受け取った値を格納
            $form = $request->only(
                'student_id',
                'campus_cd',
                'booth_cd',
                'target_date',
                'start_time',
                'memo',
            );

            // 管理者IDを取得（ログイン者）
            $account = Auth::user();
            $adm_id = $account->account_id;

            // コースコード取得
            $course = MstCourse::query()
                ->where('mst_courses.course_kind', '=', AppConst::CODE_MASTER_42_3)
                ->firstOrFail();

            // 保存
            $schedule = new Schedule;
            $schedule->course_cd = $course->course_cd;
            $schedule->end_time = $this->endTime($request['start_time']);
            $schedule->minites = config('appconf.conference_time');
            $schedule->create_kind = null;
            $schedule->lesson_kind = null;
            $schedule->adm_id = $adm_id;
            $schedule->fill($form)->save();

            // 入会済みの生徒のみ
            if ($schedule->student_id != null) {
                // お知らせメッセージ登録
                $notice = new Notice;

                // 校舎名取得
                $campus_name = $this->mdlGetRoomName($schedule->campus_cd);

                // タイトルと本文(Langから取得する)
                $notice->title = Lang::get('message.notice.conference_accept.title');
                $notice->text = Lang::get(
                    'message.notice.conference_accept.text',
                    [
                        'conferenceDate' => $schedule->target_date->format('Y/m/d'),
                        'startTime' => $schedule->start_time->format('H:i'),
                        'roomName' => $campus_name
                    ]
                );

                // お知らせ種別（面談）
                $notice->notice_type = AppConst::CODE_MASTER_14_4;
                // 管理者ID
                $account = Auth::user();
                $notice->adm_id = $account->account_id;
                $notice->campus_cd = $account->campus_cd;

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
                $noticeDestination->student_id = $schedule->student_id;

                // 保存
                $res = $noticeDestination->save();

                // save成功時のみ送信
                if ($res) {
                    $studentAccount = Account::select('email')
                        ->where('account_id', $schedule->student_id)
                        ->where('account_type', AppConst::CODE_MASTER_7_1)
                        ->firstOrFail();

                    $mail_body = [
                        'conference_date' => $schedule->target_date->format('Y/m/d') .
                            ' ' . $schedule->start_time->format('H:i'),
                        'room_name' => $campus_name
                    ];

                    $email = $studentAccount->email;
                    Mail::to($email)->send(new ConferenceAcceptToStudent($mail_body));
                }
            }
        });

        return;
    }

    /**
     * 編集画面
     *
     * @param int conferenceAcceptId 面談日程連絡Id
     * @return view
     */
    public function edit($conferenceAcceptId)
    {
        // IDのバリデーション
        $this->validateIds($conferenceAcceptId);

        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        // 面談連絡情報取得
        $conference = Conference::where('conferences.conference_id', $conferenceAcceptId)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            ->select(
                'conferences.conference_id',
                'conferences.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'conferences.campus_cd',
                // 校舎の名称
                'campus_names.room_name as campus_name',
                'conferences.comment',
                // 面談希望日時
                'conference_dates1.conference_date as conference_date1',
                'conference_dates1.start_time as start_time1',
                'conference_dates2.conference_date as conference_date2',
                'conference_dates2.start_time as start_time2',
                'conference_dates3.conference_date as conference_date3',
                'conference_dates3.start_time as start_time3',
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('conferences.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'conferences.student_id', '=', 'students.student_id')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('conferences.status', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_5);
            })
            // 面談連絡情報とJOIN
            ->sdLeftJoin(ConferenceDate::class, function ($join) {
                $join->on('conferences.conference_id', '=', 'conference_dates1.conference_id')
                    ->where('conference_dates1.request_no', 1);
            }, 'conference_dates1')
            // 面談連絡情報とJOIN
            ->sdLeftJoin(ConferenceDate::class, function ($join) {
                $join->on('conferences.conference_id', '=', 'conference_dates2.conference_id')
                    ->where('conference_dates2.request_no', 2);
            }, 'conference_dates2')
            // 面談連絡情報とJOIN
            ->sdLeftJoin(ConferenceDate::class, function ($join) {
                $join->on('conferences.conference_id', '=', 'conference_dates3.conference_id')
                    ->where('conference_dates3.request_no', 3);
            }, 'conference_dates3')
            ->firstOrFail();

        // ブースリストを取得
        $booths = $this->mdlGetBoothList($conference['campus_cd'], 3);

        return view('pages.admin.conference_accept-edit', [
            'editData' => $conference,
            'rules' => $this->rulesForInput(null),
            'booths' => $booths,
            'campus_name' => $conference['campus_name'],
            'student_name' => $conference['student_name'],
            'conference_date1' => date('Y/m/d', strtotime($conference['conference_date1'])),
            'start_time1' => date('H:i', strtotime($conference['start_time1'])),
            'conference_date2' => $conference['conference_date2'],
            'start_time2' => date('H:i', strtotime($conference['start_time2'])),
            'conference_date3' => $conference['conference_date3'],
            'start_time3' => date('H:i', strtotime($conference['start_time3'])),
            'comment' => $conference['comment']
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

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {
            // フォームから受け取った値を格納
            $form = $request->only(
                'student_id',
                'campus_cd',
                'booth_cd',
                'target_date',
                'start_time',
                'memo',
            );

            // 管理者IDを取得（ログイン者）
            $account = Auth::user();
            $adm_id = $account->account_id;

            // コースコード取得
            $course = MstCourse::query()
                ->where('mst_courses.course_kind', '=', AppConst::CODE_MASTER_42_3)
                ->firstOrFail();

            // 保存
            $schedule = new Schedule;
            $schedule->course_cd = $course->course_cd;
            $schedule->end_time = $this->endTime($request['start_time']);
            $schedule->minites = config('appconf.conference_time');
            $schedule->create_kind = null;
            $schedule->lesson_kind = null;
            $schedule->adm_id = $adm_id;
            $schedule->fill($form)->save();

            // クエリを作成(PKでユニークに取る)
            $conference = Conference::where('conference_id', $request['conference_id'])
                // 教室管理者の場合、自分の教室コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            // 面談連絡情報更新
            $conference->conference_date = $request['target_date'];
            $conference->start_time = $request['start_time'];
            $conference->end_time = $this->endTime($request['start_time']);;
            $conference->status = AppConst::CODE_MASTER_5_1;
            $conference->conference_schedule_id = $schedule->schedule_id;
            $conference->save();

            // お知らせメッセージ登録
            $notice = new Notice;

            // 校舎名取得
            $campus_name = $this->mdlGetRoomName($conference->campus_cd);

            // タイトルと本文(Langから取得する)
            $notice->title = Lang::get('message.notice.conference_accept.title');
            $notice->text = Lang::get(
                'message.notice.conference_accept.text',
                [
                    'conferenceDate' => $conference->conference_date->format('Y/m/d'),
                    'startTime' => $conference->start_time->format('H:i'),
                    'roomName' => $campus_name
                ]
            );

            // お知らせ種別（面談）
            $notice->notice_type = AppConst::CODE_MASTER_14_4;
            // 管理者ID
            $account = Auth::user();
            $notice->adm_id = $account->account_id;
            $notice->campus_cd = $account->campus_cd;

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
            $noticeDestination->student_id = $conference->student_id;

            // 保存
            $res = $noticeDestination->save();

            // save成功時のみ送信
            if ($res) {
                $mail_body = [
                    'conference_date' => $conference->conference_date->format('Y/m/d') .
                        ' ' . $conference->start_time->format('H:i'),
                    'room_name' => $campus_name
                ];

                $studentAccount = Account::select('email')
                    ->where('account_id', $conference->student_id)
                    ->where('account_type', AppConst::CODE_MASTER_7_1)
                    ->firstOrFail();

                $email = $studentAccount->email;
                Mail::to($email)->send(new ConferenceAcceptToStudent($mail_body));
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

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);

            // 選択して下さいエラー
            if ($value == -1) {
                return $fail(Lang::get('validation.required'));
            }

            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ブース
        $validationBoothList =  function ($attribute, $value, $fail) use ($request) {

            // ブースリストを取得
            $list = $this->mdlGetBoothList($request['campus_cd']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒ID
        $validationStudentList =  function ($attribute, $value, $fail) use ($request) {

            // リストを取得し存在チェック
            $students = $this->mdlGetStudentList();
            if (!isset($students[$value])) {
                // 入会前の生徒の場合
                if ($request['student_id'] == 'null') {
                    return;
                }
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: ブース重複チェック（面談）
        $validationDupBoothConference =  function ($attribute, $value, $fail) use ($request) {

            // 終了時刻計算
            $end_time = $this->endTime($request['start_time']);
            $scheduleId = null;

            // ブースの重複チェック
            $booth = $this->fncScheSearchBoothForConference(
                $request['campus_cd'],
                $request['booth_cd'],
                $request['target_date'],
                $request['start_time'],
                $end_time,
                $scheduleId,
                true
            );
            if (!$booth) {
                // ブース空きなしエラー
                return $fail(Lang::get('validation.duplicate_booth'));
            }
        };

        // 独自バリデーション: 生徒スケジュール重複チェック
        $validationDupStudent =  function ($attribute, $value, $fail) use ($request) {

            // 終了時刻計算
            $end_time = $this->endTime($request['start_time']);
            $scheduleId = null;

            // 生徒スケジュール重複チェック
            $chk = $this->fncScheChkDuplidateSid(
                $request['target_date'],
                $request['start_time'],
                $end_time,
                $request['student_id'],
                $scheduleId
            );
            if (!$chk) {
                // 生徒スケジュール重複エラー
                return $fail(Lang::get('validation.duplicate_student'));
            }
        };

        // 独自バリデーション: 面談日が現在日付時刻以降のみ登録可とする
        $validationConferenceDateTime = function ($attribute, $value, $fail) use ($request) {

            $request_datetime = $request['target_date'] . " " . $request['start_time'];
            $today = date("Y/m/d H:i");

            if (strtotime($request_datetime) < strtotime($today)) {
                // 日時チェックエラー
                return $fail(Lang::get('現在日時より後の日時を指定してください。'));
            }
        };

        $rules += Conference::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += Conference::fieldRules('student_id', [$validationStudentList]);
        $rules += Schedule::fieldRules('booth_cd', ['required', $validationBoothList, $validationDupBoothConference]);
        $rules += Schedule::fieldRules('target_date', ['required']);
        $rules += Schedule::fieldRules('start_time', ['required', $validationConferenceDateTime, $validationDupStudent]);
        $rules += Schedule::fieldRules('memo');

        return $rules;
    }
    /**
     * 終了時刻
     *
     * @param $start_time
     * @return array ルール
     */
    private function endTime($start_time)
    {
        $end_time = date("H:i", strtotime($start_time) + 60 * config('appconf.conference_time'));

        return $end_time;
    }
}
