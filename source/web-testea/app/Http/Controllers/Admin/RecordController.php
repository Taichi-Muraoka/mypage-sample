<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Record;
use App\Models\CodeMaster;
use App\Models\Student;
use App\Models\AdminUser;
use App\Models\ExtSchedule;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;
//use App\Http\Controllers\Traits\FuncReportTrait;
use Carbon\Carbon;

/**
 * 生徒カルテ - コントローラ
 */
class RecordController extends Controller
{

    // 機能共通処理：

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
     * @param int $sid 生徒ID
     * @return view
     */
    public function index($sid)
    {
        // IDのバリデーション
        $this->validateIds($sid);

        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 生徒名を取得する
        $student = $this->mdlGetStudentName($sid);

        return view('pages.admin.record', [
            'rules' => $this->rulesForSearch(),
            'student_name' => $student,
            'sid' => $sid,
            'rooms' => $rooms,
            'editData' => [
                'student_id' => $sid
            ]
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

        // クエリ作成
        $query = Record::query(); 

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // 生徒IDの検索（スコープで指定する）
        $query->SearchSid($form);

        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        $record = $query->select(
            'records.record_id',
            'records.student_id',
            // 生徒情報の名前
            'students.name as student_name',
            'records.campus_cd',
            // 校舎名
            'campus_names.room_name as campus_name',
            'records.record_kind',
            // コードマスタの名称（記録種別）
            'mst_codes.name as kind_name',
            'records.adm_id',
            // 管理者の名前
            'admin_users.name as admin_name',
            'records.received_date',
            'records.received_time',
            'records.regist_time',
            'records.memo'
        )
        // 校舎名の取得
        ->leftJoinSub($campus_names, 'campus_names', function ($join) {
            $join->on('records.campus_cd', '=', 'campus_names.code');
        })
        // 生徒名を取得
        ->sdLeftJoin(Student::class, 'records.student_id', '=', 'students.student_id')
        // コードマスターとJOIN
        ->sdLeftJoin(CodeMaster::class, function ($join) {
            $join->on('records.record_kind', '=', 'mst_codes.code')
                ->where('data_type', AppConst::CODE_MASTER_46);
        })
        // 管理者名を取得
        ->sdLeftJoin(AdminUser::class, 'records.adm_id', '=', 'admin_users.adm_id')
        ->orderby('records.regist_time', 'desc');

        // ページネータで返却（モック用）
        return $this->getListAndPaginator($request, $record);
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

        return $rules;
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

        // IDを取得
        $id = $request->input('id');

        $this->debug($id);

        // クエリを作成
        $query = Record::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        $record = $query
            // IDを指定
            ->where('records.record_id', $id)
            // データを取得
            ->select(
                'records.record_id',
            'records.student_id',
            // 生徒情報の名前
            'students.name as student_name',
            'records.campus_cd',
            // 校舎名
            'campus_names.room_name as campus_name',
            'records.record_kind',
            // コードマスタの名称（記録種別）
            'mst_codes.name as kind_name',
            'records.adm_id',
            // 管理者の名前
            'admin_users.name as admin_name',
            'records.received_date',
            'records.received_time',
            'records.regist_time',
            'records.memo'
        )
        // 校舎名の取得
        ->leftJoinSub($campus_names, 'campus_names', function ($join) {
            $join->on('records.campus_cd', '=', 'campus_names.code');
        })
        // 生徒名を取得
        ->sdLeftJoin(Student::class, 'records.student_id', '=', 'students.student_id')
        // コードマスターとJOIN
        ->sdLeftJoin(CodeMaster::class, function ($join) {
            $join->on('records.record_kind', '=', 'mst_codes.code')
                ->where('data_type', AppConst::CODE_MASTER_46);
        })
        // 管理者名を取得
        ->sdLeftJoin(AdminUser::class, 'records.adm_id', '=', 'admin_users.adm_id')
        ->firstOrFail();

        return $record;
    }

    //==========================
    // 登録・編集・削除
    //==========================

    /**
     * 登録画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function new($sid)
    {
        // IDのバリデーション
        $this->validateIds($sid);

        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 連絡記録種別を取得
        $recordKind = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_46);

        // 生徒名を取得する
        $student = $this->mdlGetStudentName($sid);

        // ログインユーザ
        $account = Auth::user();

        // 校舎名取得
        if ($account->campus_cd == "00") {
            $campus_name = "本部管理者";
        }
        else {
            $campus_name = $this->mdlGetRoomName($account->campus_cd);
        }

        $editData = [
            'sid' => $sid,
            'campus_cd' => $account->campus_cd,
            'student_id' => $sid,
            'adm_id' => $account->account_id
        ];

        // テンプレートは編集と同じ
        return view('pages.admin.record-input', [
            'editData' => $editData,
            'student_name' => $student,
            'campus_name' => $campus_name,
            'manager_name' => $account->name,
            'recordKind' => $recordKind,
            'rooms' => $rooms,
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

        $record = new Record;

        $record->student_id = $request['student_id'];
        $record->campus_cd = $request['campus_cd'];
        $record->record_kind = $request['record_kind'];
        $record->received_date = $request['received_date'];
        $record->received_time = $request['received_time'];
        $record->regist_time = now();
        $record->adm_id = $request['adm_id'];
        $record->memo = $request['memo'];

        $record->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int $recordId 生徒カルテID
     * @return view
     */
    public function edit($recordId)
    {
        // IDのバリデーション
        $this->validateIds($recordId);

        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // 連絡記録種別を取得
        $recordKind = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_46);

        // クエリを作成(PKでユニークに取る)
        $record = Record::where('record_id', $recordId)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 生徒名を取得する
        $student = $this->mdlGetStudentName($record->student_id);

        // ログインユーザ
        $account = Auth::user();

        // 校舎名取得
        if ($account->campus_cd == "00") {
            $campus_name = "本部管理者";
        }
        else {
            $campus_name = $this->mdlGetRoomName($account->campus_cd);
        }

        return view('pages.admin.record-input', [
            'rules' => $this->rulesForSearch(),
            'student_name' => $student,
            'manager_name' => $account->name,
            'campus_name' => $campus_name,
            'recordKind' => $recordKind,
            'sid' => $record->student_id,
            'rooms' => $rooms,
            'editData' => $record
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

        // クエリを作成(PKでユニークに取る)
        $record = Record::where('record_id', $request['record_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        $record->campus_cd = $request['campus_cd'];
        $record->record_kind = $request['record_kind'];
        $record->received_date = $request['received_date'];
        $record->received_time = $request['received_time'];
        $record->regist_time = now();
        $record->memo = $request['memo'];

        // 更新
        $record->save();

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
        $this->validateIdsFromRequest($request, 'record_id');

        // Formを取得
        $form = $request->all();

        // 対象データを取得(IDでユニークに取る)
        $record = Record::where('record_id', $form['record_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 削除
        $record->delete();

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

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 記録種別
        $validationKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $states = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_46);
            if (!isset($states[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 重複チェック
        $validationKey = function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            // 対象データを取得(UNIQUEキーで取得)
            $record = Record::where('campus_cd', $request['campus_cd'])
                ->where('timetable_kind', $request['timetable_kind'])->where('period_no', $request['period_no']);

            // 変更時は自分のキー以外を検索
            if (filled($request['timetable_id'])) {
                $record->where('timetable_id', '!=', $request['timetable_id']);
            }

            $exists = $record->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += Record::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += Record::fieldRules('record_kind', ['required', $validationKindList]);
        $rules += Record::fieldRules('memo', ['required']);
        $rules += Record::fieldRules('received_date', ['required']);
        $rules += Record::fieldRules('received_time', ['required']);

        return $rules;
    }
}
