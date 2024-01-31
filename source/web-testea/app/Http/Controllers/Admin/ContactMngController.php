<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\AdminUser;
use App\Consts\AppConst;
use App\Models\CodeMaster;
use App\Models\Contact;
use App\Models\Student;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncContactTrait;
use Illuminate\Support\Facades\Auth;

/**
 * 問い合わせ管理 - コントローラ
 */
class ContactMngController extends Controller
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

        // 教室リストを取得（＋本部）
        $rooms = $this->mdlGetRoomList();

        // 生徒リストを取得
        $studentList = $this->mdlGetStudentList();

        // 回答状態取得
        $contactState = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_17);

        return view('pages.admin.contact_mng', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'editData' => null,
            'students' => $studentList,
            'contactState' => $contactState
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

        // formを取得
        $form = $request->all();

        // 宛先（教室）名取得(JOIN)
        $rooms = $this->mdlGetRoomQuery();

        // クエリを作成
        $query = Contact::query();

        // MEMO: 一覧検索の条件はスコープで指定する
        // 問い合わせの宛先の教室の検索
        $query->SearchCampusCd($form);

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // 問い合わせのステータスの検索
        $query->SearchContactStates($form);

        // 生徒IDの検索
        $query->SearchStudentId($form);

        $contactList = $query
            ->select(
                'contact_id',
                'regist_time',
                'room.room_name',
                'students.name as sname',
                'title',
                'mst_codes.name as contact_state',
                'contacts.created_at'
            )
            // 生徒情報テーブルを結合
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('contacts.student_id', '=', 'students.student_id');
            })
            // 宛先と教室名の結合
            ->Leftjoinsub($rooms, 'room', function ($join) {
                $join->on('contacts.campus_cd', '=', 'room.code');
            })
            // 回答状態取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('contacts.contact_state', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_17);
            })
            ->orderBy('regist_time', 'desc')
            ->orderBy('contacts.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $contactList);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 検索結果
     */
    public function getData(Request $request)
    {
        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch())->validate();

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $id = $request->input('id');

        // クエリを作成
        $query = Contact::query();

        // 宛先（教室）名取得(JOIN)
        $rooms = $this->mdlGetRoomQuery();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        $contactList = $query
            ->select(
                'regist_time',
                'room_contacts.room_name',
                'students.name as sname',
                'title',
                'text',
                'answer_time',
                'room_admin.room_name as affiliation',
                'admin_users.name as answer_name',
                'answer_text',
                'mst_codes.name as contact_state'
            )
            ->where('contact_id', $id)
            // 宛先と教室名の結合
            ->leftJoinSub($rooms, 'room_contacts', function ($join) {
                $join->on('contacts.campus_cd', '=', 'room_contacts.code');
            })
            // 生徒名取得
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('contacts.student_id', '=', 'students.student_id');
            })
            // 回答者所属取得
            ->sdLeftJoin(AdminUser::class, 'contacts.adm_id', '=', 'admin_users.adm_id')
            ->leftJoinSub($rooms, 'room_admin', function ($join) {
                $join->on('admin_users.campus_cd', '=', 'room_admin.code');
            })
            // 回答状態取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('contacts.contact_state', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_17);
            })
            ->firstOrFail();

        return $contactList;
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {

        $rules = array();

        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 教室リストを取得（＋本部）
            $rooms = $this->mdlGetRoomList();
            if (!isset($rooms[$value])) {
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

        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {

            // 回答状態取得
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_17);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += Contact::fieldRules('campus_cd', [$validationRoomList]);
        $rules += Contact::fieldRules('contact_state', [$validationStateList]);
        $rules += Contact::fieldRules('student_id', [$validationStudentList]);

        return $rules;
    }

    //==========================
    // 編集
    //==========================

    /**
     * 編集画面
     *
     * @param int contactId 問い合わせID
     * @return view
     */
    public function edit($contactId)
    {
        // IDのバリデーション
        $this->validateIds($contactId);

        // 教室名を取得
        $roomList = $this->mdlGetRoomList();

        // 事務局アカウントプルダウンを取得
        $admList = $this->mdlGetOfficeList();

        // 回答状態取得
        $contactState = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_17);

        // 問い合わせ情報の取得
        $editData = Contact::select(
            'contact_id',
            'regist_time',
            'campus_cd',
            'title',
            'text',
            'adm_id',
            'answer_time',
            'answer_text',
            'contact_state',
            // 生徒名の取得
            'students.name'
        )
            // 生徒基本情報とJOIN
            ->sdLeftJoin(Student::class, function ($join) {
                $join->on('contacts.student_id', '=', 'students.student_id');
            })
            ->where('contact_id', $contactId)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 回答日が未入力時、当日の日付をセット
        if (!$editData->answer_time) {
            $today = date("Y-m-d");
            $editData->answer_time = $today;
        }

        // 回答者が未入力時、ログイン者をセット
        if (!$editData->adm_id) {
            // ログイン者のIDを取得
            $account = Auth::user();
            $editData->adm_id = $account->account_id;
        }

        return view('pages.admin.contact_mng-edit', [
            'editData' => $editData,
            'rules' => $this->rulesForInput(),
            'roomList' => $roomList,
            'contactState' => $contactState,
            'admList' => $admList
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
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // 保存する項目のみに絞る
        $form = $request->only(
            'contact_id',
            'regist_time',
            'campus_cd',
            'title',
            'text',
            'adm_id',
            'answer_time',
            'answer_text',
            'contact_state'
        );

        // 対象データを取得(IDでユニークに取る)
        $contact = Contact::where('contact_id', $form['contact_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 登録
        $contact->fill($form)->save();
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
        $this->validateIdsFromRequest($request, 'contact_id');

        // Formを取得
        $form = $request->all();

        // 削除対象データの取得
        $contact = Contact::where('contact_id', $form['contact_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            ->firstOrFail();

        // 削除
        $contact->delete();
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

        $rules = array();

        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 教室名を取得
            $rooms = $this->mdlGetRoomList();
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ステータス
        $validationStateList =  function ($attribute, $value, $fail) {

            // 回答状態取得
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_17);

            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 回答者
        $validationAdmList =  function ($attribute, $value, $fail) {

            // 事務局アカウントプルダウンを取得
            $adm = $this->mdlGetOfficeList();
            if (!isset($adm[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += Contact::fieldRules('regist_time', ['required']);
        $rules += Contact::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += Contact::fieldRules('title', ['required']);
        $rules += Contact::fieldRules('text', ['required']);
        $rules += Contact::fieldRules('adm_id', ['required_if:contact_state,' . AppConst::CODE_MASTER_17_1, $validationAdmList]);
        $rules += Contact::fieldRules('answer_time', ['required_if:contact_state,' . AppConst::CODE_MASTER_17_1]);
        $rules += Contact::fieldRules('answer_text', ['required_if:contact_state,' . AppConst::CODE_MASTER_17_1]);
        $rules += Contact::fieldRules('contact_state', ['required', $validationStateList]);

        return $rules;
    }
}
