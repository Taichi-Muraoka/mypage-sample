<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\Invoice;
use App\Models\Account;
use App\Models\MstCampus;
use App\Models\Student;
use App\Models\StudentCampus;
use App\Models\MstSchool;
use App\Models\CodeMaster;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use App\Http\Controllers\Traits\FuncInvoiceTrait;
use App\Http\Controllers\Traits\FuncAgreementTrait;
use Illuminate\Support\Facades\Lang;

/**
 * 会員管理 - コントローラ
 */
class MemberMngController extends Controller
{

    // 機能共通処理：カレンダー
    use FuncCalendarTrait;

    // 機能共通処理：請求書
    use FuncInvoiceTrait;

    // 機能共通処理：契約内容
    use FuncAgreementTrait;

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
        $classes = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

        // 入会状況チェックボックス
        $statusGroup = array("在籍","見込客","休塾処理中","休塾","退会処理中","退会済");

        return view('pages.admin.member_mng', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'classes' => $classes,
            'editData' => null,
            'statusGroup' => $statusGroup,
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

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = ExtStudentKihon::query();

        // 生徒の教室の検索(生徒基本情報参照)
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithSid());
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchRoom($form);
        }

        // 生徒名の検索(生徒基本情報参照)
        $query->searchName($form);

        // 学年の検索(生徒基本情報参照)
        $query->searchCls($form);

        // 生徒No.の検索(生徒基本情報参照)
        $query->searchSid($form);

        $students = $query
            ->select(
                'sid',
                'name',
                'mailaddress1',
                'ext_generic_master.name1 AS cls_name',
                'enter_date'
            )
            ->sdLeftJoin(ExtGenericMaster::class, function ($join) {
                $join->on('ext_student_kihon.cls_cd', '=', 'ext_generic_master.code')
                    ->where('ext_generic_master.codecls', '=', AppConst::EXT_GENERIC_MASTER_000_112);
            })
            // アカウントテーブルとJOIN（退会生徒非表示対応）
            ->sdJoin(Account::class, function ($join) {
                $join->on('ext_student_kihon.sid', '=', 'accounts.account_id')
                    ->where('accounts.account_type', '=', AppConst::CODE_MASTER_7_1);
            })
            ->orderBy('ext_student_kihon.sid', 'asc');

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

        // 独自バリデーション: リストのチェック 教室名
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 教室リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 学年
        $validationClasseList =  function ($attribute, $value, $fail) {

            // 学年リストを取得
            $classe = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

            if (!isset($classe[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += ExtStudentKihon::fieldRules('sid');
        $rules += ExtStudentKihon::fieldRules('name');
        $rules += ExtStudentKihon::fieldRules('cls_cd', [$validationClasseList]);
        $rules += ExtRoom::fieldRules('roomcd', [$validationRoomList]);

        return $rules;
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

        // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // 生徒の契約内容を取得
        $agreement = $this->getStudentAgreement($sid);

        return view('pages.admin.member_mng-detail', $agreement);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getDataDetail(Request $request)
    {
        //==========================
        // モック用処理
        //==========================
        return;

        //==========================
        // 本番処理
        //==========================
        // // IDのバリデーション
        // $this->validateIdsFromRequest($request, 'sid', 'seq', 'roomcd');

        // // 生徒ID
        // $sid = $request->input('sid');

        // // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
        // $this->guardRoomAdminSid($sid);

        // $roomcd = $request->input('roomcd');
        // $seq = $request->input('seq');

        // // モーダルによって処理を行う
        // $modal = $request->input('target');

        // switch ($modal) {
        //     case "#modal-dtl-regulation":

        //         // 規定情報を取得する
        //         return $this->getStudentRegular($sid, $roomcd, $seq);

        //     case "#modal-dtl-tutor":

        //         // 家庭教師標準情報を取得する
        //         return $this->getStudentHomeTeacherStd($sid, $roomcd, $seq);

        //     case "#modal-dtl-course":

        //         // 短期個別講習を取得する
        //         return $this->getStudentExtraIndividual($sid, $roomcd, $seq);

        //         case "#modal-dtl-grades_mng":

        //             // 家庭教師標準情報を取得する
        //             return $this->getStudentHomeTeacherStd($sid, $roomcd, $seq);
    
        //         default:
        //         // 該当しない場合
        //         $this->illegalResponseErr();
        // }
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

        // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        // 生徒名を取得する
        $student = $this->getStudentName($sid);

        return view('pages.admin.member_mng-calendar', [
            'name' => $student->name,
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

        // 教室管理者の場合、自分の教室コードの生徒のみにガードを掛ける
        $this->guardRoomAdminSid($sid);

        return $this->getStudentCalendar($request, $sid);
    }

    //==========================
    // 授業スケジュール登録
    //==========================

    /**
     * 登録画面
     *
     * @param int  $sid 生徒ID
     * @return view
     */
    public function calendarNew($sid)
    {

        // IDのバリデーション
        $this->validateIds($sid);

        // 生徒名を取得する

        // 生徒のidを渡しておく
        $editData = [
            'sid' => $sid
        ];

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList();

        return view('pages.admin.member_mng-calendar-input', [
            'name' => null,
            'rooms' => $rooms,
            'editData' => $editData
        ]);
    }

    /**
     * 編集画面
     *
     * @param int $sid 生徒ID
     * @param int $scheduleId スケジュールID
     * @return view
     */
    public function calendarEdit($sid, $scheduleId)
    {

        // IDのバリデーション
        $this->validateIds($sid, $scheduleId);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList();

        $editData = [
            'sid' => $sid
        ];

        return view('pages.admin.member_mng-calendar-input', [
            'name' => null,
            'rooms' => $rooms,
            'editData' => $editData,
            //'rules' => $this->rulesForInput(null)
        ]);
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
        $student = $this->getStudentName($sid);

        return view('pages.admin.member_mng-invoice', [
            'name' => $student->name,
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
            ->where('sid', $sid)
            ->orderby('invoice_date', 'desc')
            // 個別教室・家庭教師両方の場合もあるため、１つにまとめる
            ->distinct();

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

        // TODO: 個別・家庭教師両方の場合、重複する明細をどのように表示するか？顧客確認中

        // データの取得
        $dtlData = $this->getInvoiceDetail($date, $sid);

        return view('pages.admin.member_mng-invoice_detail', [
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
            'editData' => null,
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
        $this->debug($request);
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

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
            'login_kind',
            'stu_status',
            'enter_date',
            'lead_id',
            'storage_link',
            'memo',
        );

        //-------------------------
        // 生徒情報の登録
        //-------------------------
        $student = new Student;

        // 保存
        // $student->fill($form)->save();

        //-------------------------
        // 生徒所属情報の登録
        //-------------------------

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
        $this->debug($request);
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
        $rules += Student::fieldRules('birth_date');
        return $rules;
    }

    /**
     * 退会登録画面
     *
     * @return view
     */
    public function leaveEdit()
    {

        return view('pages.admin.member_mng-leave', [
            'editData' => null,
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
        $rules = array();

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
        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearchSchool())->validate();

        // formを取得
        $form = $request->all();

        // クエリ作成
        $query = MstSchool::query();

        // 学校種の絞り込み条件
        $query->SearchSchoolKind($form);

        // 設置区分の絞り込み条件
        $query->SearchEstablishKind($form);

        // 学校コードの絞り込み条件
        $query->SearchSchoolCd($form);

        // 学校名の絞り込み条件
        $query->SearchSchoolName($form);

        // バッジ一覧取得
        $schoolList = $query
            ->select(
                'mst_schools.school_cd',
                'mst_schools.school_kind_cd',
                // コードマスタの名称(学校種コード)
                'mst_codes_49.name as school_kind_name',
                'mst_schools.establish_kind',
                // コードマスタの名称(設置区分)
                'mst_codes_50.name as establish_name',
                'mst_schools.name as school_name',
            )
            // コードマスターとJOIN（学校種コード）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_schools.school_kind_cd', '=', 'mst_codes_49.code')
                    ->where('mst_codes_49.data_type', AppConst::CODE_MASTER_49);
            }, 'mst_codes_49')
            // コードマスターとJOIN（設置区分）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_schools.establish_kind', '=', 'mst_codes_50.code')
                    ->where('mst_codes_50.data_type', AppConst::CODE_MASTER_50);
            }, 'mst_codes_50')
            ->orderby('mst_schools.school_cd');

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

    /**
     * バリデーションルールを取得(学校検索用)
     *
     * @return array ルール
     */
    private function rulesForSearchSchool()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 学校種
        $validationSchoolKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $schoolKinds = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_49);
            if (!isset($schoolKinds[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 設置区分
        $validationEstablishKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $establish = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_50);
            if (!isset($establish[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += MstSchool::fieldRules('school_kind_cd', [$validationSchoolKindList]);
        $rules += MstSchool::fieldRules('establish_kind', [$validationEstablishKindList]);
        // 学校コードまたは学校名のどちらか必須
        // 学校名はid名がテーブル項目名と異なるためルールを継承するかたちで記述した
        $ruleName = MstSchool::getFieldRule('name');
        $rules += ['school_name' =>  array_merge($ruleName, ['required_without_all:school_cd'])];
        $rules += MstSchool::fieldRules('school_cd', ['required_without_all:school_name']);

        return $rules;
    }

    //==========================
    // クラス内共通処理
    //==========================

    /**
     * 生徒名の取得
     *
     * @param int $sid 生徒Id
     * @return object
     */
    private function getStudentName($sid)
    {
        // 生徒名を取得する
        $query = ExtStudentKihon::query();
        $student = $query
            ->select(
                'name'
            )
            ->where('ext_student_kihon.sid', '=', $sid)
            ->firstOrFail();

        return $student;
    }

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
