<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use App\Http\Controllers\Traits\FuncInvoiceTrait;
use App\Http\Controllers\Traits\FuncMemberDetailTrait;
use App\Http\Controllers\Traits\FuncSchoolSearchTrait;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\Invoice;
use App\Models\Account;
use App\Models\MstCampus;
use App\Models\Student;
use App\Models\StudentCampus;
use App\Models\MstSchool;
use App\Models\MstSystem;
use App\Models\SeasonStudentRequest;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\CodeMaster;
use App\Models\MstGrade;
use App\Models\Badge;
use App\Models\Record;
use App\Models\RegularClass;
use App\Models\RegularClassMember;
use App\Mail\MypageGuideToStudent;
use App\Mail\MypageGuideRejoinToStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * 会員管理 - コントローラ
 */
class MemberMngController extends Controller
{
    // 機能共通処理：カレンダー
    use FuncCalendarTrait;

    // 機能共通処理：請求書
    use FuncInvoiceTrait;

    // 機能共通処理：生徒カルテ
    use FuncMemberDetailTrait;

    // 機能共通処理：学校検索モーダル
    use FuncSchoolSearchTrait;

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
        // 学年リストを取得
        $gradeList = $this->mdlGetGradeList();
        // 会員ステータスリストを取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_28);
        // 通塾期間リスト appconfに定義
        $enterTermList = config('appconf.enter_term');

        return view('pages.admin.member_mng', [
            'rules' => $this->rulesForSearch(null),
            'rooms' => $rooms,
            'gradeList' => $gradeList,
            'statusList' => $statusList,
            'enterTermList' => $enterTermList,
            'editData' => null,
        ]);
    }

    /**
     * 検索結果取得(一覧と一覧出力CSV用)
     * 検索結果一覧を表示するとのCSVのダウンロードが同じため共通化
     *
     * @param mixed $form 検索フォーム
     */
    private function getSearchResult($form)
    {
        // クエリを作成
        $query = Student::query();

        // 校舎の検索
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithSid());
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchRoom($form);
        }

        // 学年の検索
        $query->SearchGradeCd($form);

        // 生徒IDの検索
        $query->SearchStudentId($form);

        // 生徒名の検索
        $query->SearchName($form);

        // 会員ステータスの検索
        // 配列としてリクエストされた会員ステータスが、各ステータスコードと合致するように整形
        $group = [];
        foreach ($form['status_groups'] as $val) {
            $group[$val] = $val;
        }
        $query->SearchStuStatus($form, $group);

        // 通塾期間の検索
        $query->SearchEnterTerm($form);

        // 通塾バッジ数集計のサブクエリ
        $badge_count_query = Badge::query();
        $badge_count_query->select('badges.student_id')
            ->selectRaw('COUNT(badge_type) as badge_count')
            ->where('badges.badge_type', AppConst::CODE_MASTER_55_2)
            ->groupBy('badges.student_id');

        // 通塾期間の月数取得のサブクエリ
        $enter_term_query = $this->mdlGetStudentEnterTermQuery();

        // 会員情報取得
        $students = $query
            ->select(
                'students.student_id',
                'students.name',
                'students.grade_cd',
                // 学年マスタの名称
                'mst_grades.name as grade_name',
                'students.stu_status',
                // コードマスタの名称（会員ステータス）
                'mst_codes.name as stu_status_name',
                'students.enter_date',
                // 通塾期間の月数
                'enter_term_query.enter_term',
                // 通塾バッジ数
                'badge_count_query.badge_count'
            )
            // 通塾期間の月数の取得
            ->leftJoinSub($enter_term_query, 'enter_term_query', function ($join) {
                $join->on('students.student_id', '=', 'enter_term_query.student_id');
            })
            // 通塾バッジ数の取得
            ->leftJoinSub($badge_count_query, 'badge_count_query', function ($join) {
                $join->on('students.student_id', '=', 'badge_count_query.student_id');
            })
            // 学年マスタの名称を取得
            ->sdLeftJoin(MstGrade::class, 'students.grade_cd', '=', 'mst_grades.grade_cd')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('students.stu_status', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_28);
            })
            ->orderBy('students.student_id', 'asc');

        return $students;
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

        // 検索結果を取得
        $students = $this->getSearchResult($form);

        // ページネータで返却
        return $this->getListAndPaginator($request, $students);
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
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch(?Request $request)
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

        // 独自バリデーション: リストのチェック 学年
        $validationGradeList =  function ($attribute, $value, $fail) {
            // 学年リストを取得
            $gradeList = $this->mdlGetGradeList();
            if (!isset($gradeList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: チェックボックス 会員ステータス
        $validationStatusList =  function ($attribute, $value, $fail) use ($request) {
            // 会員ステータスリスト
            $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_28);

            // 配列としてリクエストされた会員ステータスが、各ステータスコードと合致するように整形
            $group = [];
            foreach ($request->status_groups as $val) {
                $group[$val] = $val;
            }

            // ステータスコードとインデックスを合わせるため整形
            $statusGroup = [];
            foreach ($statusList as $status) {
                $statusGroup[$status->code] = $status->code;
            }

            foreach ($group as $val) {
                if (!isset($statusGroup[$val])) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 独自バリデーション: リストのチェック 通塾期間
        $validationEnterTermList =  function ($attribute, $value, $fail) {
            // 通塾期間リストを取得
            $enterTermList = config('appconf.enter_term');
            if (!isset($enterTermList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += MstCampus::fieldRules('campus_cd', [$validationRoomList]);
        $rules += Student::fieldRules('grade_cd', [$validationGradeList]);
        $rules += Student::fieldRules('student_id');
        $rules += Student::fieldRules('name');
        $rules += Student::fieldRules('stu_status', [$validationStatusList]);
        $rules += ['enter_term' => [$validationEnterTermList]];

        return $rules;
    }

    /**
     * 詳細取得（CSV出力の確認モーダル用）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        // ここでの処理は特になし
        return [];
    }

    /**
     * モーダル処理（CSV出力）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function execModal(Request $request)
    {
        //--------------
        // 一覧出力
        //--------------
        // formを取得
        $form = $request->all();

        // 検索結果を取得
        $students = $this->getSearchResult($form)
            // 結果を取得
            ->get();

        //---------------------
        // CSV出力内容を配列に保持
        //---------------------
        $arrayCsv = [];

        // ヘッダ
        $arrayCsv[] = Lang::get(
            'message.file.member_output.header'
        );

        // 会員情報詳細
        foreach ($students as $data) {

            // 入会日・通塾期間をフォーマットする
            if (isset($data->enter_date)) {
                $formatEnterDate = $data->enter_date->format('Y/m/d');
                $formatEnterTerm = floor($data->enter_term / 12) . '年' . floor($data->enter_term % 12) . 'ヶ月';
            } else {
                // 見込客は入会日がNULLなので空欄を表示する
                $formatEnterDate = '';
                $formatEnterTerm = '';
            }

            // 一行出力
            $arrayCsv[] = [
                $data->student_id,
                $data->name,
                $data->grade_name,
                $formatEnterDate,
                $formatEnterTerm,
                $data->badge_count,
                $data->stu_status_name,
            ];
        }

        //---------------------
        // ファイル名の取得と出力
        //---------------------
        $filename = Lang::get(
            'message.file.member_output.name',
            [
                'outputDate' => date("Ymd")
            ]
        );

        // ファイルダウンロードヘッダーの指定
        $this->fileDownloadHeader($filename, true);

        // CSVを出力する
        $this->outputCsv($arrayCsv);

        return;
    }

    //==========================
    // 会員情報詳細
    //==========================

    /**
     * 詳細画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function detail($sid)
    {
        // IDのバリデーション
        $this->validateIds($sid);

        // 教室管理者の場合、自分の校舎コードの生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // 生徒カルテ情報を取得 FuncMemberDetailTrait
        $student = $this->getMemberDetail($sid);

        return view('pages.admin.member_mng-detail', $student);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getDataDetail(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request);

        // 生徒ID取得
        $sid = $request->input('sid');

        // 教室管理者の場合、自分の校舎コードの生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // モーダルによって処理を行う
        $modal = $request->input('target');

        // 詳細表示するデータID取得
        $id = $request->input('id');

        switch ($modal) {
            case "#modal-dtl-record":
                // 連絡記録情報を取得する
                return $this->getModalRecord($id);

            case "#modal-dtl-room_calendar":
                // 授業情報を取得する
                return $this->getModalSchedule($id);

            case "#modal-dtl-desired":
                // 受験校情報を取得する
                return $this->getModalEntranceExam($id);

            case "#modal-dtl-grades_mng":
                // 成績情報を取得する
                return $this->getModalScore($id);

            default:
                // 該当しない場合
                $this->illegalResponseErr();
        }
    }

    //==========================
    // カレンダー
    //==========================

    /**
     * カレンダー
     *
     * @param int $sid 生徒Id
     * @return view
     */
    public function calendar($sid)
    {

        // IDのバリデーション
        $this->validateIds($sid);

        // 教室管理者の場合、自校舎の生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // 生徒名を取得する
        $student_name = $this->mdlGetStudentName($sid);

        return view('pages.admin.member_mng-calendar', [
            'name' => $student_name,
            // カレンダー用にIDを渡す
            'editData' => [
                'sid' => $sid
            ]
        ]);
    }

    /**
     * カレンダー取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return int 生徒Id
     */
    public function getCalendar(Request $request)
    {

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForCalendar())->validate();

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'sid');

        $sid = $request->input('sid');

        // 教室管理者の場合、自校舎の生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        return $this->getStudentCalendar($request, $sid);
    }

    //==========================
    // 請求情報
    //==========================

    /**
     * 請求情報一覧
     *
     * @param int $sid 生徒Id
     * @return view
     */
    public function invoice($sid)
    {
        // IDのバリデーション
        $this->validateIds($sid);

        // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // 生徒名を取得する
        $student_name = $this->mdlGetStudentName($sid);

        return view('pages.admin.member_mng-invoice', [
            'name' => $student_name,
            // 検索用にIDを渡す
            'editData' => [
                'sid' => $sid
            ]
        ]);
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function searchInvoice(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'sid');

        // IDを取得
        $sid = $request->input('sid');

        // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // クエリを作成
        $query = Invoice::query();

        // データを取得
        $invoices = $query
            ->select(
                'invoice_date'
            )
            ->where('student_id', $sid)
            ->orderby('invoice_date', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $invoices, function ($items) use ($sid) {
            // 請求年月を年月yyyymmで渡す
            foreach ($items as $item) {
                $item['id'] = $sid;
                $item['date'] = $item->invoice_date->format('Ym');
            }

            return $items;
        });
    }

    //==========================
    // 請求詳細
    //==========================

    /**
     * 詳細画面
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @param int $sid 生徒Id
     * @param string date 日付
     * @return view
     */
    public function detailInvoice($sid, $date)
    {
        // IDのバリデーション
        $this->validateIds($sid, $date);

        // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // データの取得
        $dtlData = $this->getInvoiceDetail($date, $sid);

        return view('pages.admin.member_mng-invoice_detail', [
            'invoice_import' => $dtlData['invoice_import'],
            'invoice' => $dtlData['invoice'],
            'invoice_detail' => $dtlData['invoice_detail'],
            // PDF用にIDを渡す
            'editData' => [
                'sid' => $sid,
                'date' => $date
            ]
        ]);
    }

    /**
     * PDF出力
     *
     * @param int $sid 生徒Id
     * @param date $date 日付
     * @return void
     */
    public function pdf($sid, $date)
    {
        // IDのバリデーション
        $this->validateIds($sid, $date);

        // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // データの取得
        $dtlData = $this->getInvoiceDetail($date, $sid);

        $pdfData = [
            'invoice_import' => $dtlData['invoice_import'],
            'invoice' => $dtlData['invoice'],
            'invoice_detail' => $dtlData['invoice_detail'],
        ];

        // 請求書PDFの出力(管理画面でも使用するので共通化)
        $this->outputPdfInvoice($pdfData);

        // 特になし
        return;
    }

    //==========================
    // 会員登録・編集
    //==========================

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        // 学年設定年度の初期表示用データセット（システムマスタ「現年度」）
        $currentYear = MstSystem::select('value_num')
            ->where('key_id', AppConst::SYSTEM_KEY_ID_1)
            ->whereNotNull('value_num')
            ->firstOrFail();

        $editData = [
            'grade_year' => $currentYear->value_num
        ];

        // 校舎チェックボックスリストを取得
        $rooms = $this->getCampusGroup();
        // 学年リストを取得
        $gradeList = $this->mdlGetGradeList();
        // 受験生フラグリストを取得
        $jukenFlagList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_13);
        // ログインID種別リストを取得
        $loginKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_8);
        // 会員ステータスリストを取得（サブコード1のデータに絞る）
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_28, [AppConst::CODE_MASTER_28_SUB_1]);

        // 学校検索モーダル用のデータ渡し
        // 学校種リストを取得
        $schoolKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_49);
        // 設置区分リストを取得
        $establishKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_50);

        return view('pages.admin.member_mng-input', [
            'editData' => $editData,
            'editDataCampus' => null,
            'rules' => $this->rulesForInput(null),
            'rooms' => $rooms,
            'gradeList' => $gradeList,
            'jukenFlagList' => $jukenFlagList,
            'loginKindList' => $loginKindList,
            'statusList' => $statusList,
            // 学校検索モーダル用のバリデーションルール
            'rulesSchool' => $this->rulesForSearchSchool(),
            'schoolKindList' => $schoolKindList,
            'establishKindList' => $establishKindList,
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

        // 見込客・在籍 共通保存項目
        $form = $request->only(
            'name',
            'name_kana',
            'rooms',
            'birth_date',
            'grade_cd',
            'grade_year',
            'is_jukensei',
            'school_cd_e',
            'school_cd_j',
            'school_cd_h',
            'tel_stu',
            'tel_par',
            'email_stu',
            'email_par',
            'stu_status',
            'lead_id',
            'storage_link',
            'memo',
        );

        // 会員ステータス「在籍」の場合の登録項目
        if ($request['stu_status'] == AppConst::CODE_MASTER_28_1) {
            // 以下項目を追加
            $form += [
                'login_kind' => $request['login_kind'],
                'enter_date' => $request['enter_date'],
            ];
        }

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request, $form) {
            //-------------------------
            // 生徒情報の登録
            //-------------------------
            $student = new Student;
            // 保存
            $student->fill($form)->save();

            //-------------------------
            // 生徒所属情報の登録
            //-------------------------
            // 所属校舎を配列にする
            $campuses = explode(",", $request['rooms']);

            foreach ($campuses as $campus) {
                // 所属校舎分データ作成
                $stuCampus = new StudentCampus;
                $stuCampus->student_id = $student->student_id;
                $stuCampus->campus_cd = $campus;

                // 保存
                $stuCampus->save();
            }

            // 会員ステータス「見込客」 で登録の場合はこの時点で処理終了
            if ($student->stu_status == AppConst::CODE_MASTER_28_0) {
                return;
            }

            // 会員ステータス「在籍」 で登録の場合は以下の処理も行う
            //-------------------------
            // アカウント情報の登録
            //-------------------------
            // 生徒メールか保護者メールか判別
            $email = null;
            if ($student->login_kind == AppConst::CODE_MASTER_8_1) {
                $email = $student->email_stu;
            }
            if ($student->login_kind == AppConst::CODE_MASTER_8_2) {
                $email = $student->email_par;
            }

            $account = new Account;
            $account->account_id = $student->student_id;
            // アカウント種類：生徒
            $account->account_type = AppConst::CODE_MASTER_7_1;
            $account->email = $email;
            // 仮パスワードはハッシュ化したメールアドレスとする
            $account->password = Hash::make($email);
            // パスワードリセット：不要
            $account->password_reset = AppConst::ACCOUNT_PWRESET_0;
            // プラン種類：通常
            $account->plan_type = AppConst::CODE_MASTER_10_0;
            // ログイン可否：可
            $account->login_flg = AppConst::CODE_MASTER_9_0;

            // 保存
            $account->save();

            //-------------------------
            // 特別期間講習管理 生徒連絡情報の登録
            //-------------------------
            // 対象年度の特別期間×対象校舎分を登録する（当年度春期～翌年度春期）

            // 特別期間コードリスト取得
            $seasonCodes = $this->mdlFormatSeasonCd();

            foreach ($seasonCodes as $seasonCd) {
                // 所属校舎配列は生徒所属登録時の$campusesより転用
                foreach ($campuses as $campus) {
                    $seasonStuReq = new SeasonStudentRequest;
                    $seasonStuReq->student_id = $student->student_id;
                    $seasonStuReq->season_cd = $seasonCd;
                    $seasonStuReq->campus_cd = $campus;
                    // 生徒登録状態：未登録
                    $seasonStuReq->regist_status = AppConst::CODE_MASTER_5_0;
                    // コマ組み状態：未対応
                    $seasonStuReq->plan_status = AppConst::CODE_MASTER_47_0;

                    // 保存
                    $seasonStuReq->save();
                }
            }

            //-------------------------
            // メール送信
            //-------------------------
            // マイページ案内のメール送信 テンプレートは別ファイル
            Mail::to($email)->send(new MypageGuideToStudent());
        });

        return;
    }

    /**
     * 編集画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function edit($sid)
    {
        // IDのバリデーション
        $this->validateIds($sid);

        // 校舎チェックボックスリストを取得
        $rooms = $this->getCampusGroup();
        // 学年リストを取得
        $gradeList = $this->mdlGetGradeList();
        // 受験生フラグリストを取得
        $jukenFlagList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_13);
        // ログインID種別リストを取得
        $loginKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_8);
        // 会員ステータスリストを取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_28);

        // 生徒情報を取得する
        $query = Student::query();
        $student = $query
            ->select(
                'student_id',
                'students.name',
                'name_kana',
                'grade_cd',
                'grade_year',
                'birth_date',
                'school_cd_e',
                'school_cd_j',
                'school_cd_h',
                // 学校マスタの名称（小中高）
                // 画面表示用に、学校名はtext_xxxのように指定する
                'mst_schools_e.name as text_school_cd_e',
                'mst_schools_j.name as text_school_cd_j',
                'mst_schools_h.name as text_school_cd_h',
                'is_jukensei',
                'tel_stu',
                'tel_par',
                'email_stu',
                'email_par',
                'login_kind',
                'stu_status',
                'enter_date',
                'leave_date',
                'recess_start_date',
                'recess_end_date',
                'lead_id',
                'storage_link',
                'memo',
            )
            // 所属学校（小）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'students.school_cd_e', '=', 'mst_schools_e.school_cd', 'mst_schools_e')
            // 所属学校（中）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'students.school_cd_j', '=', 'mst_schools_j.school_cd', 'mst_schools_j')
            // 所属学校（高）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'students.school_cd_h', '=', 'mst_schools_h.school_cd', 'mst_schools_h')
            ->where('student_id', '=', $sid)
            // 教室管理者の場合、自教室の生徒のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithSid())
            ->firstOrFail();

        // 生徒所属校舎を取得する
        $query = StudentCampus::query();
        $studentCampus = $query
            ->select(
                'campus_cd',
            )
            ->where('student_id', '=', $sid)
            ->get();

        // 取得した校舎コードを配列で渡す：['01','02']
        $editDataCampus = [];
        foreach ($studentCampus as $campus) {
            // 配列に追加
            array_push($editDataCampus, $campus->campus_cd);
        }

        // 学校検索モーダル用のデータ渡し
        // 学校種リストを取得
        $schoolKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_49);
        // 設置区分リストを取得
        $establishKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_50);

        return view('pages.admin.member_mng-input', [
            'editData' => $student,
            // MEMO:チェックボックス反映のためnameを指定する
            'editDataCampus' => [
                'rooms' => $editDataCampus
            ],
            'rules' => $this->rulesForInput(null),
            'rooms' => $rooms,
            'gradeList' => $gradeList,
            'jukenFlagList' => $jukenFlagList,
            'loginKindList' => $loginKindList,
            'statusList' => $statusList,
            // 学校検索モーダル用のバリデーションルール
            'rulesSchool' => $this->rulesForSearchSchool(),
            'schoolKindList' => $schoolKindList,
            'establishKindList' => $establishKindList,
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

        // 全会員ステータス 共通保存項目
        $form = $request->only(
            'name',
            'name_kana',
            'rooms',
            'birth_date',
            'grade_cd',
            'grade_year',
            'is_jukensei',
            'school_cd_e',
            'school_cd_j',
            'school_cd_h',
            'tel_stu',
            'tel_par',
            'email_stu',
            'email_par',
            'stu_status',
            'lead_id',
            'storage_link',
            'memo',
        );

        // 会員ステータス「見込客」以外 共通保存項目（「退会済」はこの項目で確定 leave_date上書きしない）
        if ($request['stu_status'] != AppConst::CODE_MASTER_28_0) {
            $form += [
                'login_kind' => $request['login_kind'],
                'enter_date' => $request['enter_date'],
            ];
        }

        // 会員ステータス「在籍」追加項目
        if ($request['stu_status'] == AppConst::CODE_MASTER_28_1) {
            $form += [
                // 休塾開始日、休塾終了日、退会日をクリアする(NULL)
                'recess_start_date' => null,
                'recess_end_date' => null,
                'leave_date' => null,
            ];
        }

        // 会員ステータス「休塾予定」「休塾」追加項目
        if ($request['stu_status'] == AppConst::CODE_MASTER_28_2 || $request['stu_status'] == AppConst::CODE_MASTER_28_3) {
            $form += [
                'recess_start_date' => $request['recess_start_date'],
                'recess_end_date' => $request['recess_end_date'],
                // 「退会処理中」→「休塾予定」に変更の場合もあるため、退会日をクリアする(NULL)
                'leave_date' => null,
            ];
        }

        // 会員ステータス「退会処理中」追加項目
        if ($request['stu_status'] == AppConst::CODE_MASTER_28_4) {
            $form += [
                'leave_date' =>  $request['leave_date'],
                // 休塾開始日、休塾終了日の更新なし（休塾履歴がある場合はデータが残る）
            ];
        }

        DB::transaction(function () use ($request, $form) {
            // MEMO:会員ステータスなど、変更前と変更後を比較して処理する内容がある関係上、生徒情報の更新は最後にした
            // 変更前の値を$student、変更後の値を$request として記述する

            // 既存の生徒情報を取得
            $student = Student::where('student_id', $request['student_id'])
                // 教室管理者の場合、自校舎の生徒のみにガードを掛ける
                ->where($this->guardRoomAdminTableWithSid())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            // 既存のアカウント情報を取得（見込客はアカウント情報が無いのでエラー出力なし）
            $account = Account::where('account_id', $request['student_id'])
                ->where('account_type', AppConst::CODE_MASTER_7_1)
                ->first();

            // メールアドレス生徒／保護者分岐
            // 1.アカウント情報作成 2.アカウント情報更新 3.メール送信 に使用
            $email = null;
            if ($request['login_kind'] == AppConst::CODE_MASTER_8_1) {
                $email = $request['email_stu'];
            }
            if ($request['login_kind'] == AppConst::CODE_MASTER_8_2) {
                $email = $request['email_par'];
            }

            //-------------------------
            // 生徒所属情報の登録
            //-------------------------
            // forceDelete・insertとする

            // 既存データ削除
            StudentCampus::where('student_id', $request['student_id'])
                ->forceDelete();

            // 選択した所属校舎コードを配列にする
            $campuses = explode(",", $request['rooms']);

            // 新規データ作成
            foreach ($campuses as $campus) {
                $stuCampus = new StudentCampus;
                $stuCampus->student_id = $request['student_id'];
                $stuCampus->campus_cd = $campus;

                // 保存
                $stuCampus->save();
            }

            //-------------------------
            // 特別期間講習管理 生徒連絡情報の登録
            //-------------------------
            // 所属校舎の、当年度春期～翌年度春期の特別期間講習管理 生徒連絡情報のレコードが無い場合に作成する

            // ステータス「在籍」で変更した場合に行う。
            if ($request['stu_status'] == AppConst::CODE_MASTER_28_1) {

                // 特別期間コードリスト取得（where用）
                $seasonCodes = $this->mdlFormatSeasonCd();

                // 所属校舎の当年度春期～翌年度春期の特別期間講習管理 生徒連絡情報のレコードが有るか確認
                foreach ($seasonCodes as $seasonCd) {
                    foreach ($campuses as $campus) {
                        // 生徒ID、特別期間コード、選択した校舎 で絞り込む
                        $existsSeasonStuReq = SeasonStudentRequest::where('student_id', $request['student_id'])
                            ->where('season_cd', $seasonCd)
                            ->where('campus_cd', $campus)
                            ->exists();

                        // 無ければ新規登録
                        if (!$existsSeasonStuReq) {
                            $seasonStuReq = new SeasonStudentRequest;
                            $seasonStuReq->student_id = $request['student_id'];
                            $seasonStuReq->season_cd = $seasonCd;
                            $seasonStuReq->campus_cd = $campus;
                            // 生徒登録状態：未登録
                            $seasonStuReq->regist_status = AppConst::CODE_MASTER_5_0;
                            // コマ組み状態：未対応
                            $seasonStuReq->plan_status = AppConst::CODE_MASTER_47_0;

                            // 保存
                            $seasonStuReq->save();
                        }
                    }
                }
            }

            //-------------------------
            // アカウント情報の登録
            //-------------------------
            // 「見込客」→「在籍」に変更時、アカウント情報を新規登録（insert）する。

            if ($student->stu_status == AppConst::CODE_MASTER_28_0 && $request['stu_status'] == AppConst::CODE_MASTER_28_1) {
                $account = new Account;
                $account->account_id = $request['student_id'];
                // アカウント種類：生徒
                $account->account_type = AppConst::CODE_MASTER_7_1;
                // $emailは上部に記載
                $account->email = $email;
                // 仮パスワードはハッシュ化したメールアドレスとする
                $account->password = Hash::make($email);
                // パスワードリセット：不要
                $account->password_reset = AppConst::ACCOUNT_PWRESET_0;
                // プラン種類：通常
                $account->plan_type = AppConst::CODE_MASTER_10_0;
                // ログイン可否：可
                $account->login_flg = AppConst::CODE_MASTER_9_0;

                // 保存
                $account->save();
            }

            //-------------------------
            // アカウント情報の更新 メールアドレス・ログイン可否の変更
            //-------------------------
            // 1.ログインID種別を変更した時
            // 2.ログインID種別で選択されている方のメールアドレスが変更された時（アカウント情報のメールアドレスが、生徒メールにも保護者メールにも一致しない時）
            // 3.会員ステータス「退会済」から「在籍」に変更した時
            // のいずれかの場合に行う。

            // 「見込客」はアカウント情報を持たないため、この処理からは外す（エラー防止）
            // 「見込客」→「在籍」に変更時も、この処理は行わない（アカウント新規作成を上記で行うため）
            if (
                $request['stu_status'] != AppConst::CODE_MASTER_28_0
                && !($student->stu_status == AppConst::CODE_MASTER_28_0 && $request['stu_status'] == AppConst::CODE_MASTER_28_1)
            ) {
                if (
                    $student->login_kind != $request['login_kind']
                    || ($account->email != $request['email_stu'] && $account->email != $request['email_par'])
                    || ($student->stu_status == AppConst::CODE_MASTER_28_5 && $request['stu_status'] == AppConst::CODE_MASTER_28_1)
                ) {
                    // メールアドレス更新 $emailは上部に記載
                    $account->email = $email;

                    // 3.会員ステータス「退会済」から「在籍」に変更した時、ログイン可にする
                    if ($student->stu_status == AppConst::CODE_MASTER_28_5 && $request['stu_status'] == AppConst::CODE_MASTER_28_1) {
                        // ログイン可否：可
                        $account->login_flg = AppConst::CODE_MASTER_9_0;
                    }

                    // 保存
                    $account->save();
                }
            }

            //-------------------------
            // 休塾期間中のスケジュール削除、受講生徒情報削除
            //-------------------------
            // 会員ステータス「休塾予定」「休塾」で変更した場合に行う。

            if ($request['stu_status'] == AppConst::CODE_MASTER_28_2 || $request['stu_status'] == AppConst::CODE_MASTER_28_3) {

                // 1.スケジュール情報 削除
                Schedule::where('student_id', $request['student_id'])
                    ->where('target_date', '>=', $request['recess_start_date'])
                    ->where('target_date', '<=', $request['recess_end_date'])
                    ->delete();

                // 2.受講生徒情報 削除
                ClassMember::where('class_members.student_id', $request['student_id'])
                    // スケジュール情報とJOIN
                    ->sdLeftJoin(Schedule::class, 'schedules.schedule_id', '=', 'class_members.schedule_id')
                    ->where('target_date', '>=', $request['recess_start_date'])
                    ->where('target_date', '<=', $request['recess_end_date'])
                    ->delete();
            }

            //-------------------------
            // 退会日変更時のスケジュール削除、受講生徒情報削除
            //-------------------------
            // 会員ステータス「退会処理中」で変更した場合に行う。

            if ($request['stu_status'] == AppConst::CODE_MASTER_28_4) {

                // 1.スケジュール情報 削除
                Schedule::where('student_id', $request['student_id'])
                    ->where('target_date', '>', $request['leave_date'])
                    ->delete();

                // 2.受講生徒情報 削除
                ClassMember::where('class_members.student_id', $request['student_id'])
                    // スケジュール情報とJOIN
                    ->sdLeftJoin(Schedule::class, 'schedules.schedule_id', '=', 'class_members.schedule_id')
                    ->where('target_date', '>', $request['leave_date'])
                    ->delete();
            }

            //-------------------------
            // メール送信
            //-------------------------
            // 1.会員ステータス「見込客」→「在籍」に変更した場合、初期マイページ案内のメールを送信する
            // 2.会員ステータス「退会済」→「在籍」に変更した場合、再入会マイページ案内のメールを送信する

            // 1.会員ステータス「見込客」→「在籍」に変更した場合
            if ($student->stu_status == AppConst::CODE_MASTER_28_0 && $request['stu_status'] == AppConst::CODE_MASTER_28_1) {
                // メール送信 $emailは上部に記載
                Mail::to($email)->send(new MypageGuideToStudent());
            }

            // 2.会員ステータス「退会済」→「在籍」に変更した場合
            if ($student->stu_status == AppConst::CODE_MASTER_28_5 && $request['stu_status'] == AppConst::CODE_MASTER_28_1) {
                Mail::to($email)->send(new MypageGuideRejoinToStudent());
            }

            //-------------------------
            // 生徒情報の更新
            //-------------------------
            // 会員ステータスごとに$formで絞ったデータを上書き保存
            $student->fill($form)->save();
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
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: チェックボックス 所属校舎
        $validationCampusGroupList =  function ($attribute, $value, $fail) use ($request) {
            // requestされた校舎コードを配列にする
            $inputCampusGroup = explode(",", $request->rooms);
            // 全校舎リスト
            $campusGroups = $this->getCampusGroup();

            // 配列にしたグループの整形
            $group = [];
            foreach ($inputCampusGroup as $val) {
                $group[$val] = $val;
            }

            // 校舎コードとインデックスを合わせるため整形
            $campusGroup = [];
            foreach ($campusGroups as $campusGroups) {
                $campusGroup[$campusGroups->code] = $campusGroups->code;
            }

            foreach ($group as $val) {
                if (!isset($campusGroup[$val])) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 独自バリデーション: リストのチェック 学年
        $validationGradeList =  function ($attribute, $value, $fail) {
            // 学年リストを取得
            $grades = $this->mdlGetGradeList(false);
            if (!isset($grades[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 受験生フラグ
        $validationJukenFlagList =  function ($attribute, $value, $fail) {
            // 受験生フラグリストを取得
            $jukenFlagList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_13);
            if (!isset($jukenFlagList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ログインID種別
        $validationLoginKindList =  function ($attribute, $value, $fail) {
            // ログインID種別リストを取得
            $loginKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_8);
            if (!isset($loginKindList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 会員ステータス
        $validationStatusList =  function ($attribute, $value, $fail) {
            // 会員ステータスリストを取得
            $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_28);
            if (!isset($statusList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: メールアドレス重複チェック ログインID種別=生徒の場合
        $validationEmailStu = function ($attribute, $value, $fail) use ($request) {
            // ログインID種別が生徒でない場合、または、見込客の場合は重複チェックしない
            if ($request['login_kind'] != AppConst::CODE_MASTER_8_1 || $request['stu_status'] == AppConst::CODE_MASTER_28_0) {
                return;
            }

            // 対象データを取得
            $studentEmail = Account::where('email', $request['email_stu']);

            // 変更時は自分のキー以外を検索
            if (filled($request['student_id'])) {
                $studentEmail->where(function ($query) use ($request) {
                    // アカウント種類=生徒 かつ 自分のID以外を検索
                    $query->where('account_type', AppConst::CODE_MASTER_7_1)
                        ->where('account_id', '!=', $request['student_id'])
                        // または、アカウント種類=生徒以外で検索
                        ->orWhere('account_type', '!=', AppConst::CODE_MASTER_7_1);
                });
            }

            $exists = $studentEmail->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_email'));
            }
        };

        // 独自バリデーション: メールアドレス重複チェック ログインID種別=保護者の場合
        $validationEmailPar = function ($attribute, $value, $fail) use ($request) {
            // ログインID種別が保護者でない場合、または、見込客の場合は重複チェックしない
            if ($request['login_kind'] != AppConst::CODE_MASTER_8_2 || $request['stu_status'] == AppConst::CODE_MASTER_28_0) {
                return;
            }

            // 対象データを取得
            $parEmail = Account::where('email', $request['email_par']);

            // 変更時は自分のキー以外を検索
            if (filled($request['student_id'])) {
                $parEmail->where(function ($query) use ($request) {
                    // アカウント種類=生徒 かつ 自分のID以外を検索
                    $query->where('account_type', AppConst::CODE_MASTER_7_1)
                        ->where('account_id', '!=', $request['student_id'])
                        // または、アカウント種類=生徒以外で検索
                        ->orWhere('account_type', '!=', AppConst::CODE_MASTER_7_1);
                });
            }

            $exists = $parEmail->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_email'));
            }
        };

        // 独自バリデーション: 会員ステータス「休塾予定」の場合、休塾開始日はシステム日付より未来日
        $validationRecessStartDateCaseProspect = function ($attribute, $value, $fail) use ($request) {
            // 休塾開始日の数値が現在日時の数値を下回っていないかチェック
            if (strtotime($request['recess_start_date']) < strtotime('now')) {
                // 下回っていた（未来日でない）場合エラー
                return $fail(Lang::get('validation.after_tomorrow'));
            }
        };

        // 独自バリデーション: 会員ステータス「休塾」の場合、休塾開始日はシステム日付以前（当日含む）
        $validationRecessStartDateCaseExecution = function ($attribute, $value, $fail) use ($request) {
            // 現在日時の数値が休塾開始日の数値を下回っていないかチェック
            if (strtotime('now') <= strtotime($request['recess_start_date'])) {
                // 下回っていた（システム日付以前でない）場合エラー
                return $fail(Lang::get('validation.before_or_equal_today',));
            }
        };

        // 独自バリデーション: 休塾終了日は休塾開始日より未来日とする
        $validationRecessDate = function ($attribute, $value, $fail) use ($request) {
            // 休塾終了日の数値が休塾開始日の数値を下回っていないかチェック
            if (strtotime($request['recess_end_date']) <= strtotime($request['recess_start_date'])) {
                // 下回っていた（休塾開始日より未来日でない）場合エラー
                return $fail(Lang::get('validation.after',));
            }
        };

        // 独自バリデーション: 会員ステータス「退会処理中」の場合、退会日はシステム日付より未来日
        $validationLeaveDateProspect = function ($attribute, $value, $fail) use ($request) {
            // 休塾開始日の数値が現在日時の数値を下回っていないかチェック
            if (strtotime($request['leave_date']) < strtotime('now')) {
                // 下回っていた（未来日でない）場合エラー
                return $fail(Lang::get('validation.after_tomorrow'));
            }
        };

        // 独自バリデーション: 退会日は入会日以降
        $validationLeaveDateAfterEnterDate = function ($attribute, $value, $fail) use ($request) {
            // 退会日の数値が入会日の数値を下回っていないかチェック
            if (strtotime($request['leave_date']) < strtotime($request['enter_date'])) {
                // 下回っていた（入会日以降でない）場合エラー
                return $fail(Lang::get('validation.student_leave_after_or_equal_enter_date'));
            }
        };

        // 独自バリデーション: 会員ステータス「見込客」への更新は不可
        $validationStatusLead = function ($attribute, $value, $fail) use ($request) {
            // 対象データを取得（編集中のデータ）
            $student = Student::where('student_id', $request['student_id'])
                ->first();

            // 登録時：新規登録時は対象データが無いためチェックしない
            // 新規登録時にバリデーションエラーが出ないよう設定した
            if (empty($student)) {
                return;
            }

            // 編集時：編集前のデータが会員ステータス「見込客」の場合はチェックしない
            if (isset($student) && $student->stu_status == AppConst::CODE_MASTER_28_0) {
                return;
            }

            // リクエストデータが「見込客」を選択していたらエラー
            if ($request['stu_status'] == AppConst::CODE_MASTER_28_0) {
                return $fail(Lang::get('validation.status_cannot_change'));
            }
        };

        // 独自バリデーション: 会員ステータス「休塾予定」への更新は不可
        $validationStatusRecessProspect = function ($attribute, $value, $fail) use ($request) {
            // 対象データを取得（編集中のデータ）
            $student = Student::where('student_id', $request['student_id'])
                ->first();

            // 編集前のデータが会員ステータス「在籍」「休塾予定」「退会処理中」の場合はチェックしない
            if (isset($student) && ($student->stu_status == AppConst::CODE_MASTER_28_1 || $student->stu_status == AppConst::CODE_MASTER_28_2 || $student->stu_status == AppConst::CODE_MASTER_28_4)) {
                return;
            }

            // リクエストデータが「休塾予定」を選択していたらエラー
            if ($request['stu_status'] == AppConst::CODE_MASTER_28_2) {
                return $fail(Lang::get('validation.status_cannot_change'));
            }
        };

        // 独自バリデーション: 会員ステータス「休塾」への更新は不可
        $validationStatusRecessExecution = function ($attribute, $value, $fail) use ($request) {
            // 対象データを取得（編集中のデータ）
            $student = Student::where('student_id', $request['student_id'])
                ->first();

            // 編集前のデータが会員ステータス「休塾」の場合はチェックしない
            if (isset($student) && $student->stu_status == AppConst::CODE_MASTER_28_3) {
                return;
            }

            // リクエストデータが「休塾」を選択していたらエラー
            if ($request['stu_status'] == AppConst::CODE_MASTER_28_3) {
                return $fail(Lang::get('validation.status_cannot_change'));
            }
        };

        // 独自バリデーション: 会員ステータス「退会処理中」への更新は不可（退会登録画面を経由させる）
        $validationStatusChangeLeaveProspect = function ($attribute, $value, $fail) use ($request) {
            // 対象データを取得（編集中のデータ）
            $student = Student::where('student_id', $request['student_id'])
                ->first();

            // 編集前のデータが会員ステータス「退会処理中」の場合はチェックしない
            if (isset($student) && $student->stu_status == AppConst::CODE_MASTER_28_4) {
                return;
            }

            // リクエストデータが「退会処理中」を選択していたらエラー
            if ($request['stu_status'] == AppConst::CODE_MASTER_28_4) {
                return $fail(Lang::get('validation.status_cannot_change_leave'));
            }
        };

        // 独自バリデーション: 会員ステータス「退会」への更新は不可（退会登録画面を経由させる）
        $validationStatusChangeLeaveExecution = function ($attribute, $value, $fail) use ($request) {
            // 対象データを取得（編集中のデータ）
            $student = Student::where('student_id', $request['student_id'])
                ->first();

            // 編集前のデータが会員ステータス「退会」の場合はチェックしない
            if (isset($student) && $student->stu_status == AppConst::CODE_MASTER_28_5) {
                return;
            }

            // リクエストデータが「退会処理中」を選択していたらエラー
            if ($request['stu_status'] == AppConst::CODE_MASTER_28_5) {
                return $fail(Lang::get('validation.status_cannot_change_leave'));
            }
        };

        // 全会員ステータス共通バリデーション
        // 必須：生徒名、生徒名かな、所属校舎、生年月日、学年、学年設定年度、受験生フラグ、保護者電話番号、会員ステータス
        $rules += Student::fieldRules('name', ['required']);
        $rules += Student::fieldRules('name_kana', ['required']);
        $rules += ['rooms' => ['required', $validationCampusGroupList]];
        $rules += Student::fieldRules('birth_date', ['required']);
        $rules += Student::fieldRules('grade_cd', ['required', $validationGradeList]);
        $rules += Student::fieldRules('grade_year', ['required']);
        $rules += Student::fieldRules('is_jukensei', ['required', $validationJukenFlagList]);
        $rules += Student::fieldRules('tel_par', ['required']);
        // 会員ステータス「見込客」以外 → 見込客 への更新は不可
        // 会員ステータス「在籍」「休塾予定」「退会処理中」以外 → 休塾予定 への更新は不可
        // 会員ステータス「休塾」以外 → 休塾 への更新は不可
        // 会員ステータス「退会処理中」以外 → 退会処理中 への更新は不可（退会登録画面を経由させる）
        // 会員ステータス「退会済」以外 → 退会 への更新は不可（退会登録画面・日次バッチを経由させる）
        $rules += Student::fieldRules('stu_status', ['required', $validationStatusList, $validationStatusLead, $validationStatusRecessProspect, $validationStatusRecessExecution, $validationStatusChangeLeaveProspect, $validationStatusChangeLeaveExecution]);

        // 電話番号形式チェック 保護者電話番号は上記でバリデーション済みのため記載省略
        $rules += Student::fieldRules('tel_stu');
        // 外部サービス顧客ID形式チェック
        $rules += Student::fieldRules('lead_id');
        // ストレージURL 形式・字数ルール適用
        $rules += Student::fieldRules('storage_link', ['nullable', 'url']);
        // メモ 字数ルール適用
        $rules += Student::fieldRules('memo');

        // 会員ステータス「見込客」以外で登録する場合
        // 必須：ログインID種別、入会日
        if ($request && $request['stu_status'] != AppConst::CODE_MASTER_28_0) {
            $rules += Student::fieldRules('login_kind', ['required', $validationLoginKindList]);
            $rules += Student::fieldRules('enter_date', ['required']);
        }
        // MEMO:メールアドレス字数制限を初期表示時から有効にするためif外に記述
        // ログインID種別=生徒なら生徒メールアドレス必須、重複チェック
        $rules += Student::fieldRules('email_stu', ['required_if:login_kind,' . AppConst::CODE_MASTER_8_1, $validationEmailStu]);
        // ログインID種別=保護者なら保護者メールアドレス必須、重複チェック
        $rules += Student::fieldRules('email_par', ['required_if:login_kind,' . AppConst::CODE_MASTER_8_2, $validationEmailPar]);

        // 会員ステータス「休塾予定」「休塾」の場合
        // 必須：休塾開始日、休塾終了日
        // 休塾開始日はシステム日付より未来日とする（システム日付＜休塾開始日）
        // 休塾終了日は休塾開始日より未来日とする（休塾開始日＜休塾終了日）
        if ($request && $request['stu_status'] == AppConst::CODE_MASTER_28_2) {
            $rules += Student::fieldRules('recess_start_date', ['required', $validationRecessStartDateCaseProspect]);
            $rules += Student::fieldRules('recess_end_date', ['required', $validationRecessDate]);
        }

        // 会員ステータス「休塾」の場合
        // 必須：休塾開始日、休塾終了日
        // 休塾開始日はシステム日付以前とする（休塾開始日≦システム日付）
        // 休塾終了日は休塾開始日より未来日とする（休塾開始日＜休塾終了日）
        if ($request && $request['stu_status'] == AppConst::CODE_MASTER_28_3) {
            $rules += Student::fieldRules('recess_start_date', ['required', $validationRecessStartDateCaseExecution]);
            $rules += Student::fieldRules('recess_end_date', ['required', $validationRecessDate]);
        }

        // 会員ステータス「退会処理中」の場合
        // 必須：退会日
        // 退会日はシステム日付より未来日、入会日以降とする（システム日付＜退会日、入会日＜＝退会日）
        if ($request && $request['stu_status'] == AppConst::CODE_MASTER_28_4) {
            $rules += Student::fieldRules('leave_date', ['required', $validationLeaveDateProspect, $validationLeaveDateAfterEnterDate]);
        }

        return $rules;
    }

    /**
     * 退会登録画面
     *
     * @return view
     */
    public function leaveEdit($sid)
    {
        // IDのバリデーション
        $this->validateIds($sid);

        // 教室管理者の場合、自分の校舎コードの生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // 会員ステータス「見込客」「退会処理中」「退会済」の場合は退会登録画面を表示しない
        $student = Student::where('student_id', $sid)
            ->first();
        if ($student->stu_status == AppConst::CODE_MASTER_28_0 || $student->stu_status == AppConst::CODE_MASTER_28_4 || $student->stu_status == AppConst::CODE_MASTER_28_5) {
            return $this->illegalResponseErr();
        }

        // 生徒名の取得
        $student_name = $this->mdlGetStudentName($sid);

        // editDataセット 「対応日」は現在日時を設定
        $editData = [
            'student_id' => $sid,
            'student_name' => $student_name,
            'received_date' => date('Y/m/d')
        ];

        return view('pages.admin.member_mng-leave', [
            'editData' => $editData,
            'rules' => $this->rulesForInput(null)
        ]);
    }

    /**
     * 登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function leaveUpdate(Request $request)
    {
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInputLeave($request))->validate();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {
            //-------------------------
            // 連絡記録情報の登録
            //-------------------------
            // ログイン中管理者のID・所属校舎を取得
            $account = Auth::user();
            $adm_id = $account->account_id;
            $campus_cd = $account->campus_cd;

            // 保存データセット
            $record = new Record;
            $record->student_id = $request['student_id'];
            // ログイン中管理者の校舎をセット
            $record->campus_cd = $campus_cd;
            // 記録種別「退会」
            $record->record_kind = AppConst::CODE_MASTER_46_5;
            // 対応日は画面入力値
            $record->received_date = $request['received_date'];
            // 対応時刻は00:00固定
            $record->received_time =  '00:00';
            // 登録日時は現在日時
            $record->regist_time =  Carbon::now();
            $record->adm_id = $adm_id;
            $record->memo = $request['memo'];
            // 保存
            $record->save();

            //-------------------------
            // 生徒情報の更新
            //-------------------------
            // 対象データを取得
            $student = Student::where('student_id', $request['student_id'])
                // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
                ->where($this->guardRoomAdminTableWithSid())
                // 会員ステータス「見込客」「退会処理中」「退会済」は除外する
                ->whereNotIn('stu_status', [AppConst::CODE_MASTER_28_0, AppConst::CODE_MASTER_28_4, AppConst::CODE_MASTER_28_5])
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            // 会員ステータスを「退会処理中」にセット
            $student->stu_status = AppConst::CODE_MASTER_28_4;
            // 退会日をセット
            $student->leave_date = $request['leave_date'];
            // 更新
            $student->save();

            //-------------------------
            // スケジュール削除
            //-------------------------
            // スケジュール情報 削除
            Schedule::where('student_id', $request['student_id'])
                ->where('target_date', '>', $request['leave_date'])
                ->delete();

            // 受講生徒情報 削除
            ClassMember::where('class_members.student_id', $request['student_id'])
                // スケジュール情報とJOIN
                ->sdLeftJoin(Schedule::class, 'schedules.schedule_id', '=', 'class_members.schedule_id')
                ->where('target_date', '>', $request['leave_date'])
                ->delete();

            // レギュラー授業情報 削除
            RegularClass::where('student_id', $request['student_id'])
                ->delete();

            // レギュラー受講生徒情報 削除
            RegularClassMember::where('student_id', $request['student_id'])
                ->delete();
        });

        return;
    }

    /**
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInputLeave(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInputLeave($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInputLeave(?Request $request)
    {
        // 独自バリデーション: 退会日はシステム日付より未来日
        $validationLeaveDateAfterToday = function ($attribute, $value, $fail) use ($request) {
            // 退会日の数値が現在日時の数値を下回っていないかチェック
            if (strtotime($request['leave_date']) < strtotime('now')) {
                // 下回っていた（未来日でない）場合エラー
                return $fail(Lang::get('validation.after_tomorrow'));
            }
        };

        // 独自バリデーション: 退会日は入会日以降
        $validationLeaveDateAfterEnterDate = function ($attribute, $value, $fail) use ($request) {

            // 入会日を取得
            $student = Student::select('enter_date')
                ->where('student_id', $request['student_id'])
                ->first();

            // 退会日の数値が入会日の数値を下回っていないかチェック
            if (strtotime($request['leave_date']) < strtotime($student['enter_date'])) {
                // 下回っていた（入会日以降でない）場合エラー
                return $fail(Lang::get('validation.student_leave_after_or_equal_enter_date'));
            }
        };

        $rules = array();

        $rules += Student::fieldRules('leave_date', ['required', $validationLeaveDateAfterToday, $validationLeaveDateAfterEnterDate]);
        $rules += Record::fieldRules('memo', ['required']);
        $rules += Record::fieldRules('received_date', ['required']);

        return $rules;
    }

    //==========================
    // 学校検索
    //==========================

    /**
     * 検索結果取得(学校検索)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function searchSchool(Request $request)
    {
        // 検索結果を取得
        $schoolList = $this->getSchoolList($request);

        // ページネータで返却
        return $this->getListAndPaginator($request, $schoolList);
    }

    /**
     * バリデーション(学校検索用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForSearchSchool(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForSearchSchool());
        return $validator->errors();
    }

    //==========================
    // クラス内共通処理
    //==========================

    // MEMO:保留 他画面で同様の機能が必要であれば共通化する
    /**
     * 校舎チェックボックスリストの取得
     *
     * @return array 校舎リスト
     */
    private function getCampusGroup()
    {
        return MstCampus::select(
            'mst_campuses.campus_cd as code',
            'name as value',
            'disp_order'
        )
            // 非表示フラグの条件を付加
            ->where('is_hidden', AppConst::CODE_MASTER_11_1)
            ->orderBy('disp_order')
            ->get();
    }
}
