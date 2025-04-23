<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Libs\CommonDateFormat;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;
use App\Consts\AppConst;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncContactTrait;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\ContactToOffice;

/**
 * 問い合わせ - コントローラ
 */
class ContactController extends Controller
{

    // 機能共通処理：問い合わせ
    use FuncContactTrait;

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

        return view('pages.student.contact');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // 宛先（教室）名取得(JOIN)
        $rooms = $this->mdlGetRoomQuery();

        // クエリ作成
        $query = Contact::query();
        $contactList = $query
            ->select(
                'contact_id',
                'regist_time',
                'title',
                'answer_time',
                'admin_users.name',
                'rooms.room_name',
                'contacts.created_at'
            )
            // 管理者名を取得
            ->sdLeftJoin(AdminUser::class, 'contacts.adm_id', '=', 'admin_users.adm_id')
            // 教室名を取得
            ->leftJoinSub($rooms, 'rooms', function ($join) {
                $join->on('contacts.campus_cd', '=', 'rooms.code');
            })
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            ->orderBy('regist_time', 'desc')
            ->orderBy('contacts.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $contactList);
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

        // 宛先（教室）名取得(JOIN)
        $rooms = $this->mdlGetRoomQuery();

        // IDを取得
        $id = $request->input('id');

        // クエリ作成
        $query = Contact::query();

        // データを取得
        $contactList = $query
            ->select(
                'regist_time',
                'title',
                'text',
                'answer_time',
                'admin_users.name',
                'answer_text',
                'roomContact.room_name',
                'roomAdmin.room_name as affiliation'
            )
            // キー項目
            ->where('contact_id', $id)
            ->sdLeftJoin(AdminUser::class, 'contacts.adm_id', '=', 'admin_users.adm_id')
            // 教室・所属の取得
            ->leftJoinSub($rooms, 'roomContact', function ($join) {
                $join->on('contacts.campus_cd', '=', 'roomContact.code');
            })
            ->leftJoinSub($rooms, 'roomAdmin', function ($join) {
                $join->on('admin_users.campus_cd', '=', 'roomAdmin.code');
            })
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            ->firstOrFail();

        return $contactList;
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
        // 教室取得
        $rooms = $this->mdlGetRoomList();

        return view('pages.student.contact-new', [
            'rules' => $this->rulesForInput(),
            'rooms' => $rooms
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
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // 生徒IDを取得
        $account = Auth::user();
        $sid = $account->account_id;

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request, $sid) {

            // フォームから受け取った値を格納
            $form = $request->only(
                // roomcdのガードのチェックはvalidationRoomListのバリデーションで行っている
                'campus_cd',
                'title',
                'text'
            );

            // 本日の日時
            $now = Carbon::now();

            // 保存
            $contact = new Contact;
            $contact->student_id = $sid;
            $contact->regist_time = $now;
            $contact->contact_state = AppConst::CODE_MASTER_17_0;
            $res = $contact->fill($form)->save();

            //-------------------------
            // メール送信
            //-------------------------
            // save成功時のみ送信
            if ($res) {
                // 校舎名取得
                $campus_name = $this->mdlGetRoomName($form['campus_cd'], true);
                // 生徒名取得
                $student_name = $this->mdlGetStudentName($sid);

                // 送信先メールアドレス取得
                if ($form['campus_cd'] == AppConst::CODE_MASTER_6_0) {
                    // 本部宛の場合は本部用メールアドレスを取得（envから）
                    $campusEmail = config('appconf.mail_honbu_address');
                } else {
                    $campusEmail = $this->mdlGetCampusMail($form['campus_cd']);
                }
                $this->debug($campusEmail);
                $mail_body = [
                    'student_name' => $student_name,
                    'contact_date' => CommonDateFormat::formatYmdDay($contact->regist_time),
                    'campus_name' => $campus_name,
                    'title' => $form['title'],
                    'text' => $form['text']
                ];

                Mail::to($campusEmail)->send(new ContactToOffice($mail_body));
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
        $validator = Validator::make($request->all(), $this->rulesForInput());
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput()
    {

        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 宛先（教室プルダウンメニュー)
            $rooms = $this->mdlGetRoomList();
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        $rules += Contact::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += Contact::fieldRules('title', ['required']);
        $rules += Contact::fieldRules('text', ['required']);

        return $rules;
    }
}
