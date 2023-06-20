<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\ExtStudentKihon;
use App\Models\ExtRoom;
use App\Models\ExtGenericMaster;
use App\Models\Invoice;
use App\Models\Account;
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
        $statusGroup = array("在籍","見込客","退会処理中","退会済");

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
                $join->on('ext_student_kihon.sid', '=', 'account.account_id')
                    ->where('account.account_type', '=', AppConst::CODE_MASTER_7_1);
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
        // 学年リストを取得
        $classes = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList();

        return view('pages.admin.member_mng-input', [
            'classes' => $classes,
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
    public function create(Request $request)
    {
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

        // 学年リストを取得
        $classes = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);

        // 生徒情報を取得する
        $query = ExtStudentKihon::query();
        $student = $query
            ->select(
                'name',
                'cls_cd',
                'mailaddress1'
            )
            ->where('ext_student_kihon.sid', '=', $sid)
            ->firstOrFail();

        $editData = [
            'sid' => $sid,
            'name' => $student['name'],
            'cls_cd' => $student['cls_cd'],
            'email_student' => $student['mailaddress1'],
        ];

        return view('pages.admin.member_mng-input', [
            'classes' => $classes,
            'editData' => $editData,
            'rules' => $this->rulesForInput(null)
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
}
