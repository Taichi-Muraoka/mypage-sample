<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Mail\NoticeRegistToStudent;
use App\Mail\NoticeRegistToTutor;
use App\Mail\NoticeRegistToParent;
use App\Libs\AuthEx;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\AdminUser;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\Notice;
use App\Models\NoticeDestination;
use App\Models\NoticeGroup;
use App\Models\NoticeTemplate;
use App\Models\Record;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Traits\FuncNoticeTrait;

/**
 * お知らせ通知 - コントローラ
 */
class NoticeRegistController extends Controller
{

    // 機能共通処理：お知らせ
    use FuncNoticeTrait;

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
        $rooms = $this->mdlGetRoomList(true);

        // 宛先種別プルダウンを作成
        $destination_types = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_15);

        // お知らせ種別プルダウンを作成
        $notice_type_list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_14);

        return view('pages.admin.notice_regist', [
            'rules' => $this->rulesForSearch(null),
            'rooms' => $rooms,
            'destination_types' => $destination_types,
            'notice_type_list' => $notice_type_list,
            'editData' => null
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

        // クエリを作成(主テーブルはお知らせとした)
        $query = Notice::query();

        // 校舎の検索
        if (AuthEx::isRoomAdmin()) {
            // 校舎管理者の場合、自分の校舎コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchCampusCd($form);
        }

        // 宛先種別(お知らせ宛先参照)
        (new NoticeDestination)->scopeSearchType($query, $form);

        // お知らせ種別絞り込み
        $query->SearchNoticeType($form);

        // タイトル(お知らせ)
        $query->SearchTitle($form);

        // 校舎名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // クエリ作成
        $notices = $query
            ->distinct()
            ->select(
                'notices.notice_id as id',
                'notices.regist_time as date',
                'notices.title',
                'mst_codes1.name as type_name',
                'room_name',
                'notice_destinations.destination_type as destination_type',
                'mst_codes2.name as notice_type_name',
            )
            // お知らせ宛先
            ->sdLeftJoin(NoticeDestination::class, function ($join) {
                // 1件取得
                $join->on('notice_destinations.notice_id', '=', 'notices.notice_id');
            })
            // 宛先種別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_codes1.code', '=', 'notice_destinations.destination_type')
                    ->where('mst_codes1.data_type', '=', AppConst::CODE_MASTER_15);
            }, 'mst_codes1')
            // お知らせ種別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_codes2.code', '=', 'notices.notice_type')
                    ->where('mst_codes2.data_type', '=', AppConst::CODE_MASTER_14);
            }, 'mst_codes2')
            // 校舎名取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('notices.campus_cd', '=', 'room_names.code');
            })
            ->orderBy('notices.regist_time', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $notices);
    }

    /**
     * 詳細画面
     *
     * @param integer $noticeId お知らせID
     * @return view
     */
    public function detail($noticeId)
    {
        // IDのバリデーション
        $this->validateIds($noticeId);

        // 校舎管理者の場合、見れていいidかチェックする
        if (AuthEx::isRoomAdmin()) {
            $notice = Notice::where('notice_id', $noticeId)
                // 校舎管理者の場合、自分の校舎コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                ->firstOrFail();
        }

        // 校舎名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // クエリを作成
        $query = Notice::query();
        $notice = $query
            ->select(
                'notices.notice_id as id',
                'notices.regist_time as regist_time',
                'notices.title',
                'notices.text',
                'mst_codes1.name as type_name',
                'room_name',
                'notice_destinations.destination_type as destination_type',
                'mst_codes2.name as notice_type_name',
                'admin_users.name as sender'
            )
            // お知らせ宛先
            ->sdLeftJoin(NoticeDestination::class, function ($join) {
                // 1件取得
                $join->on('notice_destinations.notice_id', '=', 'notices.notice_id');
            })
            // 宛先種別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_codes1.code', '=', 'notice_destinations.destination_type')
                    ->where('mst_codes1.data_type', '=', AppConst::CODE_MASTER_15);
            }, 'mst_codes1')
            // お知らせ種別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_codes2.code', '=', 'notices.notice_type')
                    ->where('mst_codes2.data_type', '=', AppConst::CODE_MASTER_14);
            }, 'mst_codes2')
            // 校舎名取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('notices.campus_cd', '=', 'room_names.code');
            })
            // 送信者
            ->sdLeftJoin(AdminUser::class, function ($join) {
                $join->on('admin_users.adm_id', '=', 'notices.adm_id');
            })
            // IDで絞り込み
            ->where('notices.notice_id', '=', $noticeId)
            ->firstOrFail();

        // 宛先名の取得
        $query = NoticeDestination::query();
        $destination_names = $query
            ->distinct()
            ->select(
                'students.name as student_name',
                'tutors.name as teacher_name',
                'notice_destinations.notice_group_id',
                'group_name'
            )
            // 生徒名の取得
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('students.student_id', '=', 'notice_destinations.student_id');
            })
            // 講師名の取得
            ->sdLeftJoin(Tutor::class, function ($join) {
                $join->on('tutors.tutor_id', '=', 'notice_destinations.tutor_id');
            })
            // お知らせグループ
            ->sdLeftJoin(NoticeGroup::class, function ($join) {
                $join->on('notice_groups.notice_group_id', '=', 'notice_destinations.notice_group_id');
            })
            ->where('notice_destinations.notice_id', '=', $noticeId)
            ->orderBy('notice_destinations.notice_group_id', 'asc')
            ->get();

        return view('pages.admin.notice_regist-detail', [
            'notice' => $notice,
            'destination_names' => $destination_names,
            'editData' => [
                'noticeId' => $notice->id
            ]
        ]);
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch(?Request $request)
    {

        $rules = array();

        // 独自バリデーション: リストのチェック 校舎
        $validationCampusList =  function ($attribute, $value, $fail) use($request) {
            
            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(true);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 宛先種別
        $validationDestinationTypesList =  function ($attribute, $value, $fail) {

            // 宛先種別プルダウンを作成
            $destination_types = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_15);
            if (!isset($destination_types[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック お知らせ種別
        $validationNoticeTypesList =  function ($attribute, $value, $fail) {

            // 宛先種別プルダウンを作成
            $notice_types = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_14);
            if (!isset($notice_types[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += Notice::fieldRules('campus_cd', [$validationCampusList]);
        $rules += NoticeDestination::fieldRules('destination_type', [$validationDestinationTypesList]);
        $rules += Notice::fieldRules('notice_type', [$validationNoticeTypesList]);
        $rules += Notice::fieldRules('title');

        return $rules;
    }

    //==========================
    // 登録・削除
    //==========================

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {

        // 定型文のプルダウン取得
        $templates = $this->getMenuOfNoticeTemplate();

        // 宛先グループチェックボックス
        $noticeGroup = $this->getMenuOfNoticeGroup();

        // 宛先種別プルダウンを作成
        $destination_types = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_15);

        return view('pages.admin.notice_regist-new', [
            'rules' => $this->rulesForInput(null),
            'templates' => $templates,
            'editData' => null,
            'noticeGroup' => $noticeGroup,
            'destination_types' => $destination_types
        ]);
    }

    /**
     * タイトル・内容情報取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed タイトル、内容等取得
     */
    public function getDataSelectTemplate(Request $request)
    {

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // 定型文ID
        $id = $request->input('id');

        // 定型文を取得
        $query = NoticeTemplate::query();
        $template = $query
            ->select(
                'title',
                'text',
                'notice_type',
                'mst_codes.name as notice_type_name',
            )
            ->where('template_id', '=', $id)
            // お知らせ種別
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_codes.code', '=', 'notice_templates.notice_type')
                    ->where('mst_codes.data_type', '=', AppConst::CODE_MASTER_14);
            })
            ->firstOrFail();

        return [
            'title' => $template->title,
            'text' => $template->text,
            'notice_type' => $template->notice_type,
            'notice_type_name' => $template->notice_type_name,
        ];
    }

    /**
     * 宛先種別プルダウンを選択
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 講師、生徒、校舎情報取得
     */
    public function getDataSelect(Request $request)
    {
        // MEMO: 不正アクセス防止のため宛先種別のバリデーションを入れる
        $this->validateIdsFromRequest($request, 'destinationType');

        $destination_type = '';
        $rooms = [];
        $students = [];
        $teachers = [];

        if ($request->filled('destinationType')) {

            // 宛先種別
            $destination_type = $request->input('destinationType');
            if (!($destination_type == AppConst::CODE_MASTER_15_1 ||
                $destination_type == AppConst::CODE_MASTER_15_2 ||
                $destination_type == AppConst::CODE_MASTER_15_3 ||
                $destination_type == AppConst::CODE_MASTER_15_4)) {
                return [
                    'rooms' => [],
                    'students' => [],
                    'teachers' => []
                ];
            }
        }

        // 校舎のプルダウンリストを取得
        $rooms = $this->mdlGetRoomList(false);

        if ($destination_type == AppConst::CODE_MASTER_15_2 || $destination_type == AppConst::CODE_MASTER_15_4) {

            if ($request->filled('campus_cd_student')) {

                $campus_cd = $request->input('campus_cd_student');

                // 校舎が選択されている場合
                $students = $this->mdlGetStudentList($campus_cd);
            }
        } elseif ($destination_type == AppConst::CODE_MASTER_15_3) {

            // 講師リスト取得
            $teachers = $this->mdlGetTutorList();
        }

        return [
            'rooms' => $this->objToArray($rooms),
            'students' => $this->objToArray($students),
            'teachers' => $this->objToArray($teachers),
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

        // ログイン者のidを取得する。
        $account = Auth::user();
        $adm_id = $account->account_id;

        // 定型文よりお知らせ種別の取得
        $notice_type = NoticeTemplate::select('notice_type')
            ->where('template_id', '=', $request->input('template_id'))
            ->firstOrFail()
            ->notice_type;

        // 必要な要素のみ選択する
        $form = $request->only(
            'title',
            'text'
        );

        $campus_cd = $request->input('campus_cd_group');
        if (AuthEx::isRoomAdmin()) {
            // 校舎管理者の場合、強制的に校舎コードを指定する
            $campus_cd = Auth::user()->campus_cd;
        }

        // 宛先種別により保存内容を分岐
        $destination_type = $request->input('destination_type');
        $destinations = [];
        $records = [];

        switch ($destination_type) {
                // グループ一斉
            case AppConst::CODE_MASTER_15_1:

                // グループを配列にする
                $notice_groups = explode(",", $request->input('notice_groups'));
                // 配列を昇順に並び替える
                sort($notice_groups);

                // 校舎コード指定の指定がある場合
                if ($request->filled('campus_cd_group') || AuthEx::isRoomAdmin()) {

                    $seq = 1;
                    for ($i = 0; $i < count($notice_groups); $i++) {
                        $destination = [
                            'destination_seq' => $seq,
                            'destination_type' => AppConst::CODE_MASTER_15_1,
                            'student_id' => null,
                            'tutor_id' => null,
                            'notice_group_id' => $notice_groups[$i],
                            'campus_cd' => $campus_cd
                        ];

                        // グループが講師の場合はnull
                        if ($notice_groups[$i] == AppConst::NOTICE_GROUP_ID_16) {
                            $destination['campus_cd'] = null;
                        }

                        array_push($destinations, $destination);
                        $seq++;
                    }
                    // 校舎コード指定の指定がない場合（全校舎対象）
                } else {

                    // // 全ての校舎コードを取得する。ただし除外対象の校舎コードを除く
                    $rooms = $this->mdlGetRoomList(true);

                    // 校舎コードの配列
                    $room_codes = [];
                    foreach ($rooms as $room) {
                        array_push($room_codes, $room->code);
                    }

                    // 全て校舎コード×全てのグループで配列を作る
                    $seq = 1;
                    $tutor_flg = false;
                    for ($i = 0; $i < count($room_codes); $i++) {
                        $destination = [
                            'destination_type' => AppConst::CODE_MASTER_15_1,
                            'campus_cd' => $room_codes[$i],
                            'student_id' => null,
                            'tutor_id' => null
                        ];

                        for ($j = 0; $j < count($notice_groups); $j++) {
                            $destination['destination_seq'] = $seq;
                            $destination['notice_group_id'] = $notice_groups[$j];

                            // グループに講師が含まれる場合、フラグのみ立てておく
                            if ($destination['notice_group_id'] == AppConst::NOTICE_GROUP_ID_16) {
                                $tutor_flg = true;
                                continue;
                            }
                            array_push($destinations, $destination);
                            $seq++;
                        }
                    }
                    if ($tutor_flg) {
                        $destination = [
                            'destination_type' => AppConst::CODE_MASTER_15_1,
                            'campus_cd' => null,
                            'student_id' => null,
                            'tutor_id' => null,
                            'destination_seq' => $seq,
                            'notice_group_id' => AppConst::NOTICE_GROUP_ID_16
                        ];
                        array_push($destinations, $destination);
                    }
                }

                break;
                // 個別（生徒）
            case AppConst::CODE_MASTER_15_2:

                $destinations = [
                    [
                        'destination_seq' => 1,
                        'destination_type' => AppConst::CODE_MASTER_15_2,
                        'student_id' => $request->input('student_id'),
                        'tutor_id' => null,
                        'notice_group_id' => null,
                        'campus_cd' => null
                    ]
                ];

                break;
                // 個別（講師）
            case AppConst::CODE_MASTER_15_3:

                $destinations = [
                    [
                        'destination_seq' => 1,
                        'destination_type' => AppConst::CODE_MASTER_15_3,
                        'student_id' => null,
                        'tutor_id' => $request->input('tutor_id'),
                        'notice_group_id' => null,
                        'campus_cd' => null
                    ]
                ];

                break;
                // 個別（保護者） 
            case AppConst::CODE_MASTER_15_4:

                $destinations = [
                    [
                        'destination_seq' => 1,
                        'destination_type' => AppConst::CODE_MASTER_15_4,
                        'student_id' => $request->input('student_id'),
                        'tutor_id' => null,
                        'notice_group_id' => null,
                        'campus_cd' => null
                    ]
                ];

                $records = [
                        'student_id' => $request->input('student_id'),
                        'campus_cd' => $account->campus_cd,
                        'record_kind' => AppConst::CODE_MASTER_46_3,
                        'received_date' => now()->format('Y-m-d'),
                        'received_time' => now()->format('H:i:00'),
                        'regist_time' => now(),
                        'adm_id' => $adm_id,
                        'memo' => $request->input('text')
                ];
                break;
            default:
                break;
        }

        // 保存内容
        $notice = new Notice;
        $notice->notice_type = $notice_type;
        $notice->adm_id = $adm_id;
        $notice->campus_cd = $account->campus_cd;

        DB::transaction(function () use ($form, $notice, $destinations, $records, $destination_type) {

            // お知らせ情報保存
            $res = $notice->fill($form)->save();
            $notice_id = $notice->notice_id;

            // お知らせ対象が保護者の場合連絡記録に保存
            if ($destination_type == AppConst::CODE_MASTER_15_4 && $records != null) {
                $record = new Record;
                $record->fill($records)->save();
                $record->save();
            }

            // お知らせ宛先情報保存
            for ($i = 0; $i < count($destinations); $i++) {
                $destinations[$i]['notice_id'] = $notice_id;
            }

            foreach ($destinations as $data) {
                $notice_destination = new NoticeDestination;
                $notice_destination->notice_id = $data['notice_id'];
                $notice_destination->destination_seq = $data['destination_seq'];
                $notice_destination->destination_type = $data['destination_type'];
                $notice_destination->student_id = $data['student_id'];
                $notice_destination->tutor_id = $data['tutor_id'];
                $notice_destination->notice_group_id = $data['notice_group_id'];
                $notice_destination->campus_cd = $data['campus_cd'];
                $res = $notice_destination->save();
            }

            // save成功時のみ送信
            if ($res) {
                switch ($destination_type) {
                    // グループ一斉
                    case AppConst::CODE_MASTER_15_1:
                        // グループ一斉はメール送信なし
                        break;
                    // 個別（生徒）
                    case AppConst::CODE_MASTER_15_2:
                        // 生徒情報からログイン種別とメールアドレスを取得
                        $student = Student::select(
                                'login_kind',
                                'email_stu',
                                'email_par'
                            )
                            ->where('student_id', $notice_destination->student_id)
                            ->firstOrFail();
                        // ログイン種別が生徒なら生徒のメールアドレスに
                        // 保護者なら保護者のメールアドレスに送信する
                        if ($student->login_kind == AppConst::CODE_MASTER_8_1) {
                            $email = $student->email_stu;
                        }
                        else if ($student->login_kind == AppConst::CODE_MASTER_8_2) {
                            $email = $student->email_par;
                        }
                        else {
                            $email = null;
                        }
                        $subject = $notice->title;
                        $mail_body = [
                            'text' => $notice->text,
                        ];
                        // メール送信
                        Mail::to($email)->send(new NoticeRegistToStudent($mail_body, $subject));
                        break;
                    // 個別（講師）
                    case AppConst::CODE_MASTER_15_3:
                        // 講師情報からメールアドレスを取得
                        $tutor = Tutor::select(
                                'email'
                            )
                            ->where('tutor_id', $notice_destination->tutor_id)
                            ->firstOrFail();
                        $email = $tutor->email;
                        $subject = $notice->title;
                        $mail_body = [
                            'text' => $notice->text,
                        ];
                        // メール送信
                        Mail::to($email)->send(new NoticeRegistToTutor($mail_body, $subject));
                        break;
                    // 個別（保護者）
                    case AppConst::CODE_MASTER_15_4:
                        // 生徒情報から保護者のメールアドレスを取得
                        $student = Student::select(
                                'email_par'
                            )
                            ->where('student_id', $notice_destination->student_id)
                            ->firstOrFail();
                        $email = $student->email_par;
                        $subject = $notice->title;
                        $mail_body = [
                            'text' => $notice->text,
                        ];
                        // メール送信
                        Mail::to($email)->send(new NoticeRegistToParent($mail_body, $subject));
                        break;
                    default:
                        break;
                }
            }
        });

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
        $this->validateIdsFromRequest($request, 'noticeId');

        $noticeId = $request->input('noticeId');

        DB::transaction(function () use ($noticeId) {
            // 削除
            NoticeDestination::where('notice_id', '=', $noticeId)->delete();
            Notice::where('notice_id', '=', $noticeId)->delete();
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

        // 独自バリデーション: リストのチェック 定型文
        $validationTemplatesList =  function ($attribute, $value, $fail) {

            // 定型文のプルダウン取得
            $templates = $this->getMenuOfNoticeTemplate();

            if (!isset($templates[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 宛先
        $validationDestinationTypesList =  function ($attribute, $value, $fail) {

            // 宛先種別プルダウンを作成
            $destination_types = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_15);
            if (!isset($destination_types[$value])) {
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

        // 独自バリデーション: リストのチェック 生徒
        $validationStudentList =  function ($attribute, $value, $fail) use ($request) {

            if (!isset($request->campus_cd_student)) return;
            // 生徒リスト取得
            $students = $this->mdlGetStudentList($request->campus_cd_student);
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 講師
        $validationTeacherList =  function ($attribute, $value, $fail) {

            // 講師リスト取得
            $teachers = $this->mdlGetTutorList();
            if (!isset($teachers[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: チェックボックス 宛先グループ
        $validationNoticeGroupList =  function ($attribute, $value, $fail) use ($request) {

            // グループを配列にする
            $inputNoticeGroup = explode(",", $request->notice_groups);

            // 宛先グループチェックボックス
            $noticeGroups = $this->getMenuOfNoticeGroup();

            // 配列にしたグループの整形
            $group = [];
            foreach ($inputNoticeGroup as $val) {
                $group[$val] = $val;
            }
            // IDとインデックスを合わせるため整形
            // MEMO:他への影響を考えgetMenuOfNoticeGroupの処理にkeyByをつけた修正をしない
            $noticeGroup = [];
            foreach ($noticeGroups as $noticeGroups) {
                $noticeGroup[$noticeGroups->notice_group_id] = $noticeGroups->notice_group_id;
            }

            foreach ($group as $val) {
                if (!isset($noticeGroup[$val])) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 独自バリデーション： 保護者のメールアドレスの有無
        $validationEmailPar = function ($attribute, $value, $fail) use ($request) {
            // 生徒情報取得
            $student = Student::query()
                ->where('student_id', '=', $request['student_id'])
                ->firstOrFail();
            // 保護者のメールアドレスがなかった場合エラーを返す
            if ($student->email_par == null) {
                return $fail('保護者のメールアドレスが登録されていません');
            }
            else {
                return;
            }
        };

        // 必須要素の分岐用
        $campus_cd_group_required = '';
        $campus_cd_student_required = '';

        if ($request != null) {
            if ($request->filled('template_id') && $request->filled('destination_type')) {
                $destination_type = $request->input('destination_type');

                // 宛先種別ごとのバリデーションルール
                if ($destination_type == AppConst::CODE_MASTER_15_1) {
                    // チェックボックスのバリデーション
                    $rules += ['notice_groups' => ['required', $validationNoticeGroupList]];
                    // 校舎管理者の場合
                    if (AuthEx::isRoomAdmin()) {
                        $campus_cd_group_required = 'required';
                        // 宛先が講師のみの場合は、校舎は必須としない
                        $notice_groups = $request->input('notice_groups');
                        if ($notice_groups === (string) AppConst::NOTICE_GROUP_ID_16) {
                            $campus_cd_group_required = null;
                        }
                    }
                } elseif ($destination_type == AppConst::CODE_MASTER_15_2) {
                    // 生徒No.のバリデーション
                    $rules += NoticeDestination::fieldRules('student_id', ['required', $validationStudentList]);
                    // 校舎管理者の場合
                    if (AuthEx::isRoomAdmin()) {
                        $campus_cd_student_required = 'required';
                    }
                } elseif ($destination_type == AppConst::CODE_MASTER_15_3) {
                    // 講師No.のバリデーション
                    $rules += NoticeDestination::fieldRules('tutor_id', ['required', $validationTeacherList]);
                } elseif ($destination_type == AppConst::CODE_MASTER_15_4) {
                    // 校舎管理者の場合
                    if (AuthEx::isRoomAdmin()) {
                        $campus_cd_student_required = 'required';
                    }
                    // 生徒No.のバリデーション
                    $rules += NoticeDestination::fieldRules('student_id', ['required', $validationStudentList, $validationEmailPar]);
                } else {
                    $this->illegalResponseErr();
                }
            }
        }

        $rules += NoticeTemplate::fieldRules('template_id', ['required', $validationTemplatesList]);
        $rules += Notice::fieldRules('title', ['required']);
        $rules += Notice::fieldRules('text', ['required']);
        $rules += NoticeDestination::fieldRules('destination_type', ['required', $validationDestinationTypesList]);
        $rules += ['campus_cd_group' => [$campus_cd_group_required, $validationRoomList]];
        $rules += ['campus_cd_student' => [$campus_cd_student_required, $validationRoomList]];

        return $rules;
    }
}
