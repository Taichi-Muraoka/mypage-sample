<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\ExtStudentKihon;
use App\Models\ExtRirekisho;
use App\Models\CodeMaster;
use App\Models\ExtSchedule;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncReportTrait;

/**
 * 授業報告 - コントローラ
 */
class ReportCheckController extends Controller
{

    // 機能共通処理：授業報告
    use FuncReportTrait;

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

        return view('pages.admin.report_check', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'classes' => $classes,
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
        $validator = Validator::make($request->all(), $this->rulesForSearch());
        return $validator->errors();
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array  検索結果
     */
    public function search(Request $request)
    {
        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch())->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = Report::query();

        // 教室の検索
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchRoom($form);
        }

        // 学年の検索(生徒基本情報参照)
        (new ExtStudentKihon)->scopeSearchCls($query, $form);

        // 生徒名の検索(生徒基本情報参照)
        $key['name'] = $form['sname'];
        (new ExtStudentKihon)->scopeSearchName($query, $key);

        // 教師名の検索(教師履歴書情報参照)
        $key['name'] = $form['tname'];
        (new ExtRirekisho)->scopeSearchName($query, $key);

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // データを取得
        $reports = $query
            ->select(
                'report_id as id',
                'regist_time',
                'lesson_date',
                'start_time',
                'room_names.room_name as room_name',
                'ext_student_kihon.name as sname',
                'ext_rirekisho.name as tname',
                'r_minutes',
                'report.created_at'
            )
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('report.roomcd', '=', 'room_names.code');
            })
            // 生徒名の取得
            ->sdLeftJoin(ExtStudentKihon::class, 'report.sid', '=', 'ext_student_kihon.sid')
            // 教師名の取得
            ->sdLeftJoin(ExtRirekisho::class, 'report.tid', '=', 'ext_rirekisho.tid')
            // 登録日の降順
            ->orderBy('report.regist_time', 'desc')
            ->orderBy('report.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $reports);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        // // IDのバリデーション
        // $this->validateIdsFromRequest($request, 'id');

        // // IDを取得
        // $id = $request->input('id');

        // // クエリを作成
        // $query = Report::query();

        // // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        // $query->where($this->guardRoomAdminTableWithRoomCd());

        // // 教室名取得のサブクエリ
        // $room_names = $this->mdlGetRoomQuery();

        // // データを取得
        // $report = $query
        //     // IDを指定
        //     ->where('report.report_id', $id)
        //     ->select(
        //         'regist_time',
        //         'ext_rirekisho.name as tname',
        //         'mst_codes.name as lesson_type_name',
        //         'lesson_date',
        //         'start_time',
        //         'room_names.room_name as room_name',
        //         'ext_student_kihon.name as sname',
        //         'r_minutes',
        //         'content',
        //         'homework',
        //         'teacher_comment',
        //         'parents_comment'
        //     )
        //     // 教室名の取得
        //     ->leftJoinSub($room_names, 'room_names', function ($join) {
        //         $join->on('report.roomcd', '=', 'room_names.code');
        //     })
        //     // 生徒名の取得
        //     ->sdLeftJoin(ExtStudentKihon::class, 'report.sid', '=', 'ext_student_kihon.sid')
        //     // 教師名の取得
        //     ->sdLeftJoin(ExtRirekisho::class, 'report.tid', '=', 'ext_rirekisho.tid')
        //     // 授業種別名の取得
        //     ->sdLeftJoin(CodeMaster::class, function ($join) {
        //         $join->on('report.lesson_type', '=', 'mst_codes.code')
        //             ->where('mst_codes.data_type', AppConst::CODE_MASTER_8);
        //     })
        //     ->firstOrFail();

        // return [
        //     'regist_time' => $report->regist_time,
        //     'tname' => $report->tname,
        //     'lesson_type_name' => $report->lesson_type_name,
        //     'lesson_date' => $report->lesson_date,
        //     'start_time' => $report->start_time,
        //     'room_name' => $report->room_name,
        //     'sname' => $report->sname,
        //     'r_minutes' => $report->r_minutes,
        //     'content' => $report->content,
        //     'homework' => $report->homework,
        //     'teacher_comment' => $report->teacher_comment,
        //     'parents_comment' => $report->parents_comment
        // ];
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return mixed ルール
     */
    private function rulesForSearch()
    {

        $rules = array();

        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 教室リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 学年
        $validationClassesList =  function ($attribute, $value, $fail) {

            // 学年リストを取得
            $classes = $this->mdlMenuFromExtGenericMaster(AppConst::EXT_GENERIC_MASTER_112);
            if (!isset($classes[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += Report::fieldRules('roomcd', [$validationRoomList]);
        $rules += ExtStudentKihon::fieldRules('cls_cd', [$validationClassesList]);
        $ruleSname = ExtStudentKihon::getFieldRule('name');
        $rules += ['sname' => $ruleSname];
        $ruleTname = ExtRirekisho::getFieldRule('name');
        $rules += ['tname' => $ruleTname];
        return $rules;
    }

    //==========================
    // 編集
    //==========================

    /**
     * 編集画面
     *
     * @param int reportId 授業報告書ID
     * @return view
     */
    public function edit($reportId)
    {

        // // IDのバリデーション
        // $this->validateIds($reportId);

        // // クエリを作成
        // $query = Report::query();

        // // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        // $query->where($this->guardRoomAdminTableWithRoomCd());

        // // データを取得
        // $report = $query
        //     // IDを指定
        //     ->where('report.report_id', $reportId)
        //     ->select(
        //         'report_id',
        //         'regist_time',
        //         'lesson_type',
        //         'id',
        //         'id as _id', // hiddenに退避
        //         'lesson_date',
        //         'start_time',
        //         'report.sid',
        //         'report.tid',
        //         'ext_rirekisho.name as tname',
        //         'r_minutes',
        //         'content',
        //         'homework',
        //         'teacher_comment',
        //         'parents_comment'
        //     )
        //     // 教師名の取得
        //     ->sdLeftJoin(ExtRirekisho::class, 'report.tid', '=', 'ext_rirekisho.tid')
        //     ->firstOrFail();

        // if ($report->lesson_type == AppConst::CODE_MASTER_8_1) {
        //     // 個別教室の場合、生徒IDをセットする
        //     $report['sidKobetsu'] = $report->sid;
        //     $report->sid = null;
        // }

        // // 教師の担当している生徒の一覧を取得(個別教室)
        // // このプルダウン自体は登録には使わず、個別教室のスケジュールのプルダウンを作成するために使用される
        // // 家庭教師以外
        // $studentsKobetsu = $this->mdlGetStudentListForT(null, $report->tid, AppConst::EXT_GENERIC_MASTER_101_900);

        // // 家庭教師の受け持ち生徒名プルダウンメニューを作成
        // $students = $this->mdlGetStudentListForT(AppConst::EXT_GENERIC_MASTER_101_900, $report->tid);

        // // 授業時間数のプルダウンメニューを作成
        // $minutes = $this->getMenuOfMinutes();

        return view('pages.admin.report_check-edit', [
            'editData' => null,
            'rules' => $this->rulesForInput(null),
            'student_kobetsu_list' => null,
            'student_list' => null,
            'minutes_list' => null
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

        // 登録者の教師ID取得
        $tid = $request->input('tid');

        // 対象データを取得(PKでユニークに取る)
        $query = Report::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        $report = $query
            ->where('report_id', $request->input('report_id'))
            // 登録者の教師IDでも絞り込み
            ->where('tid', $tid)
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        if ($request->input('lesson_type') == AppConst::CODE_MASTER_8_1) {
            //---------------
            // 個別教室登録
            //---------------
            $id = $request->input('id');

            // スケジュールidから授業日・授業開始時間・教室・生徒を取得する。
            $query = ExtSchedule::query();

            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());

            $lesson = $query
                ->select(
                    'roomcd',
                    'lesson_date',
                    'start_time',
                    'sid'
                )
                ->where('id', '=', $id)
                // 登録者の教師IDでも絞り込み
                ->where('tid', '=', $tid)
                ->firstOrFail();

            $roomcd = $lesson->roomcd;
            $lesson_date = $lesson->lesson_date;
            $start_time = $lesson->start_time;
            $sid = $lesson->sid;
        } elseif ($request->input('lesson_type') == AppConst::CODE_MASTER_8_2) {
            //---------------
            // 家庭教師登録
            //---------------
            $id = null;
            $roomcd = AppConst::EXT_GENERIC_MASTER_101_900;
            $lesson_date = $request->input('lesson_date');
            $start_time = $request->input('start_time');
            $sid = $request->input('sid');
        } else {
            $this->illegalResponseErr();
        }

        // フォームから受け取った値を格納
        $form = $request->only(
            'regist_time',
            'lesson_type',
            'r_minutes',
            'content',
            'homework',
            'teacher_comment',
            'parents_comment'
        );

        // 保存
        $report->id = $id;
        $report->roomcd = $roomcd;
        $report->lesson_date = $lesson_date;
        $report->start_time = $start_time;
        $report->sid = $sid;
        $report->fill($form)->save();

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
        $this->validateIdsFromRequest($request, 'report_id');
        $report_id = $request->input('report_id');
        $tid = $request->input('tid');

        // 対象データを取得(PKでユニークに取る)
        $query = Report::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        $report = $query
            ->where('report_id', $report_id)
            // 登録者の教師IDでも絞り込み
            ->where('tid', $tid)
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // Reportテーブルのdelete
        $report->delete();

        return;
    }

    /**
     * 教室・生徒情報取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 教師、生徒情報
     */
    public function getDataSelect(Request $request)
    {

        // IDのバリデーション
        // スケジュールIDは生徒IDの後に受け取れるのでsidのみ必須チェックする
        $this->validateIdsFromRequest($request, 'reportId', 'sid');

        // IDを取得
        $reportId =  $request->input('reportId');
        $schedule_id = $request->input('id');
        $sid = $request->input('sid');

        // reportIdを取得してtidを取得する
        $tid = $this->getTidFormReport($reportId);

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------

        // 教師の担当している生徒の一覧を取得(家庭教師は除く)
        $students = $this->mdlGetStudentListForT(null, $tid, AppConst::EXT_GENERIC_MASTER_101_900);

        // 生徒一覧にsidがあるかチェック
        $this->guardListValue($students, $sid);

        //---------------------------
        // スケジュールプルダウンの作成
        //---------------------------

        // 教師に紐づくスケジュールを取得
        // 教室管理者の場合、自分の教室コードのスケジュールのみにガードを掛ける
        if (AuthEx::isRoomAdmin()) {
            $account = Auth::user();
            $myRoomCd = $account->roomcd;
        } else {
            $myRoomCd = null;
        }

        // 個別教室のスケジュールプルダウンメニューを作成
        $scheduleMaster = $this->getScheduleListReport($tid, $myRoomCd, $sid);

        //---------------------------
        // 教室を返却する
        //---------------------------
        $room_name = null;
        if (filled($schedule_id)) {
            // idが指定されている場合のみ

            // [ガード] スケジュールIDがプルダウンの中にあるかチェック
            $this->guardListValue($scheduleMaster, $schedule_id);

            // スケジュールの取得(ガードはこの中でも掛ける)
            $lesson = $this->mdlGetScheduleDtl($schedule_id);

            // 変数にセット
            $room_name = $lesson->room_name;
        }

        return [
            'selectItems' => $this->objToArray($scheduleMaster),
            'class_name' => $room_name
        ];
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

        // 独自バリデーション: 重複チェック(個別教室登録用)
        $validationDuplicateRegular = function ($attribute, $value, $fail) use ($request) {

            if (!isset($request['id'])) {
                // requiredでチェックするのでreturn
                return;
            }
            if ($request['lesson_type'] != AppConst::CODE_MASTER_8_1) {
                // 種別で判断
                return;
            }

            // 対象データを取得(PKでユニークに取る)
            // スケジュールID
            $exists = Report::where('id', $request['id'])
                // 授業種別
                ->where('lesson_type', AppConst::CODE_MASTER_8_1)
                // 更新中のキー以外を検索
                ->where('report_id', '!=', $request['report_id'])
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // 独自バリデーション: 重複チェック(家庭教師登録用)
        $validationDuplicateHomeTeacher = function ($attribute, $value, $fail) use ($request) {

            if (!isset($request['lesson_date'])) {
                // requiredでチェックするのでreturn
                return;
            }
            if ($request['lesson_type'] != AppConst::CODE_MASTER_8_2) {
                // 種別で判断
                return;
            }

            // 授業日・開始時刻が現在日付時刻以前の授業のみ登録可とする
            $lesson_datetime = $request['lesson_date'] . " " . $request['start_time'];
            $today = date("Y/m/d H:i");

            if (strtotime($lesson_datetime) > strtotime($today)) {
                // 日時チェックエラー
                return $fail(Lang::get('validation.before_today'));
            }

            // 対象データを取得(PKでユニークに取る)
            $exists = Report::where('tid', $request['tid'])
                ->where('lesson_date', $request['lesson_date'])
                ->where('start_time', $request['start_time'])
                // 授業種別
                ->where('lesson_type', AppConst::CODE_MASTER_8_2)
                // 更新中のキー以外を検索
                ->where('report_id', '!=', $request['report_id'])
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        // 独自バリデーション: 授業種別（ラジオ）
        $validationRadioLessonType = function ($attribute, $value, $fail) use ($request) {

            // ラジオの値のチェック
            if (
                $request['lesson_type'] != AppConst::CODE_MASTER_8_1 &&
                $request['lesson_type'] != AppConst::CODE_MASTER_8_2
            ) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
            // 教室管理者の場合、ラジオの値の変更は不可とする
            if (AuthEx::isRoomAdmin()) {
                $account = Auth::user();
                if (
                    $account->roomcd == AppConst::EXT_GENERIC_MASTER_101_900 &&
                    $request->input('lesson_type') == AppConst::CODE_MASTER_8_1
                ) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                } else if (
                    $account->roomcd != AppConst::EXT_GENERIC_MASTER_101_900 &&
                    $request->input('lesson_type') == AppConst::CODE_MASTER_8_2
                ) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 独自バリデーション: リストのチェック 授業時間
        $validationMinutesList =  function ($attribute, $value, $fail) {

            // 授業時間数のプルダウンメニューを作成
            $minutes = $this->getMenuOfMinutes();
            if (!isset($minutes[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 個別教室のスケジュール
        $validationScheduleMasterList =  function ($attribute, $value, $fail) use ($request) {

            if (!isset($request)) return;

            // reportIdを取得してtidを取得する
            $tid = $this->getTidFormReport($request['report_id']);

            // 教師に紐づくスケジュールを取得
            // 教室管理者の場合、自分の教室コードのスケジュールのみにガードを掛ける
            if (AuthEx::isRoomAdmin()) {
                $account = Auth::user();
                $myRoomCd = $account->roomcd;
            } else {
                $myRoomCd = null;
            }

            // 個別教室のスケジュールプルダウンメニューを作成
            $scheduleMaster = $this->getScheduleListReport($tid, $myRoomCd);

            if (!isset($scheduleMaster[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 家庭教師の受け持ち生徒名
        $validationStudentsList =  function ($attribute, $value, $fail) use ($request) {

            if (!isset($request)) return;

            // reportIdを取得してtidを取得する
            $tid = $this->getTidFormReport($request['report_id']);

            // 家庭教師の受け持ち生徒名プルダウンメニューを作成
            $students = $this->mdlGetStudentListForT(AppConst::EXT_GENERIC_MASTER_101_900, $tid);
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒ID(個別教室)
        $validationSidList =  function ($attribute, $value, $fail) use ($request) {

            // reportIdを取得してtidを取得する
            $tid = $this->getTidFormReport($request['report_id']);

            // 教師の担当している生徒の一覧を取得
            $students = $this->mdlGetStudentListForT(null, $tid, AppConst::EXT_GENERIC_MASTER_101_900);

            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する

        // 個別教室の生徒ID
        $ruleSid = ExtSchedule::getFieldRule('sid');
        $rules += ['sidKobetsu' =>  array_merge(
            $ruleSid,
            ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_1, $validationSidList]
        )];

        // 項目のバリデーションルールをベースにする
        $ruleId = Report::getFieldRule('id');
        $ruleLessonDate = Report::getFieldRule('lesson_date');
        $rules += Report::fieldRules('lesson_type', ['required', $validationRadioLessonType]);
        $rules += Report::fieldRules('regist_time', ['required']);
        $rules += Report::fieldRules('start_time', ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_2]);
        $rules += Report::fieldRules('sid', ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_2, $validationStudentsList]);

        // 授業種別が個別教室の場合、スケジュールIDの必須チェックと重複チェック
        $rules += ['id' =>  array_merge(
            $ruleId,
            ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_1, $validationDuplicateRegular, $validationScheduleMasterList]
        )];

        // 授業種別が家庭教師の場合、必須チェックと重複チェック
        $rules += ['lesson_date' =>  array_merge(
            $ruleLessonDate,
            ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_2, $validationDuplicateHomeTeacher]
        )];

        // MEMO: 不正アクセス対策として、report_idもルールに追加する
        $rules += Report::fieldRules('report_id', ['required']);
        $rules += Report::fieldRules('r_minutes', ['required', $validationMinutesList]);
        $rules += Report::fieldRules('content', ['required']);
        $rules += Report::fieldRules('homework');
        $rules += Report::fieldRules('teacher_comment', ['required']);
        $rules += Report::fieldRules('parents_comment');

        return $rules;
    }

    /**
     * レポートIDから教師IDを取得
     * 
     * @param int $transferApplyId 振替ID
     */
    private function getTidFormReport($reportId)
    {
        // クエリを作成
        $query = Report::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // データを取得
        $report = $query
            // IDを指定
            ->where('report.report_id', $reportId)
            ->select(
                'report.tid',
            )
            ->firstOrFail();

        return $report->tid;
    }
}
