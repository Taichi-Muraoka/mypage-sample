<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Report;
use App\Models\ExtStudentKihon;
use App\Models\ExtSchedule;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncReportTrait;
use Carbon\Carbon;

/**
 * 授業報告書 - コントローラ
 */
class ReportRegistController extends Controller
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

        return view('pages.tutor.report_regist', [
            'rules' => $this->rulesForSearch(),
            'rooms' => $rooms,
            'editData' => null
        ]);
    }

    /**
     * 生徒情報取得（教室リスト選択時）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 生徒情報
     */
    public function getDataSelectSearch(Request $request)
    {
        // $requestからidを取得し、検索結果を返却する
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // roomcdを取得
        $roomcd = $request->input('id');

        // $requestのroomcdから、生徒IDリストを取得し、検索結果を返却する。
        // 生徒リスト取得
        if ($roomcd == -1 || !filled($roomcd)) {
            // -1 または 空白の場合、自分の受け持ちの生徒だけに絞り込み
            $students = $this->mdlGetStudentListForT(null, $account_id);
        } else {
            $students = $this->mdlGetStudentListForT($roomcd, $account_id);
        }

        return [
            'selectItems' => $students
        ];
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
        $query = Report::query();

        // 教室コード選択による絞り込み条件
        // -1 は未選択状態のため、-1以外の場合に教室コードの絞り込みを行う
        if (isset($form['roomcd']) && filled($form['roomcd']) && $form['roomcd'] != -1) {
            // 検索フォームから取得（スコープ）
            $query->SearchRoom($form);
        }

        // 生徒IDの検索（スコープで指定する）
        $query->SearchSid($form);

        // 受け持ち生徒に限定するガードを掛ける
        $query->where($this->guardTutorTableWithSid());

        // 自分のアカウントIDでガードを掛ける（tid）
        $query->where($this->guardTutorTableWithTid());

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // データを取得
        $reports = $query
            ->select(
                'report_id as id',
                'lesson_date',
                'start_time',
                'room_names.room_name_full as room_name',
                'ext_student_kihon.name as sname',
                'r_minutes'
            )
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('report.roomcd', '=', 'room_names.code');
            })
            // 生徒名の取得
            ->sdLeftJoin(ExtStudentKihon::class, 'report.sid', '=', 'ext_student_kihon.sid')
            ->orderby('lesson_date', 'desc')
            ->orderby('start_time', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $reports);
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

        // 独自バリデーション: リストのチェック 教室
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 初期表示の時はエラーを発生させないようにする
            if ($value == -1) return;

            // 教室リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒名
        $validationStudentsList =  function ($attribute, $value, $fail) {

            // ログイン者の情報を取得する
            $account = Auth::user();
            $account_id = $account->account_id;
            $students = $this->mdlGetStudentListForT(null, $account_id);
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        $rules += Report::fieldRules('roomcd', [$validationRoomList]);
        $rules += Report::fieldRules('sid', [$validationStudentsList]);

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

        // クエリを作成
        $query = Report::query();

        // 受け持ち生徒に限定するガードを掛ける
        $query->where($this->guardTutorTableWithSid());

        // 自分のアカウントIDでガードを掛ける（tid）
        $query->where($this->guardTutorTableWithTid());

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // データを取得
        $report = $query
            // IDを指定
            ->where('report.report_id', $id)
            ->select(
                'lesson_date',
                'start_time',
                'room_names.room_name_full as room_name',
                'ext_student_kihon.name as sname',
                'r_minutes',
                'content',
                'homework',
                'teacher_comment',
                'parents_comment'
            )
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('report.roomcd', '=', 'room_names.code');
            })
            // 生徒名の取得
            ->sdLeftJoin(ExtStudentKihon::class, 'report.sid', '=', 'ext_student_kihon.sid')
            ->firstOrFail();

        return [
            'lesson_date' => $report->lesson_date,
            'start_time' => $report->start_time,
            'room_name' => $report->room_name,
            'sname' => $report->sname,
            'r_minutes' => $report->r_minutes,
            'content' => $report->content,
            'homework' => $report->homework,
            'teacher_comment' => $report->teacher_comment,
            'parents_comment' => $report->parents_comment
        ];
    }

    //==========================
    // 登録・編集・削除
    //==========================

    /**
     * 教室・生徒情報取得（スケジュールより）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 教室、生徒情報
     */
    public function getDataSelect(Request $request)
    {
        // $requestからidを取得し、検索結果を返却する
        // スケジュールIDは生徒IDの後に受け取れるのでsidのみ必須チェックする
        $this->validateIdsFromRequest($request, 'sid');

        // IDを取得
        $schedule_id = $request->input('id');
        $sid = $request->input('sid');

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------

        // 教師の担当している生徒の一覧を取得
        $students = $this->mdlGetStudentListForT(null, $account_id, AppConst::EXT_GENERIC_MASTER_101_900);

        // 生徒一覧にsidがあるかチェック
        $this->guardListValue($students, $sid);

        //---------------------------
        // スケジュールプルダウンの作成
        //---------------------------

        // 個別教室のスケジュールプルダウンメニューを作成
        $scheduleMaster = $this->getScheduleListReport($account_id, null, $sid);

        //---------------------------
        // 教室を返却する
        //---------------------------
        $room_name_full = null;
        if (filled($schedule_id)) {
            // idが指定されている場合のみ

            // [ガード] リスト自体を取得して、値が正しいかチェックする
            $this->guardListValue($scheduleMaster, $schedule_id);

            // 教室名取得のサブクエリ
            $room_names = $this->mdlGetRoomQuery();

            // $requestからidを取得し、検索結果を返却する。idはスケジュールID
            $query = ExtSchedule::query();
            $lesson = $query
                ->select(
                    'room_name_full'
                )
                // 教室名の取得
                ->leftJoinSub($room_names, 'room_names', function ($join) {
                    $join->on('ext_schedule.roomcd', '=', 'room_names.code');
                })
                // 自分のアカウントIDでガードを掛ける（tid）
                ->where($this->guardTutorTableWithTid())
                // キーの指定
                ->where('ext_schedule.id', '=', $schedule_id)
                ->firstOrFail();

            // 変数にセット
            $room_name_full = $lesson->room_name_full;
        }

        return [
            'selectItems' => $this->objToArray($scheduleMaster),
            'class_name' => $room_name_full
        ];
    }

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        // 教師の担当している生徒の一覧を取得(個別教室)
        // このプルダウン自体は登録には使わず、個別教室のスケジュールのプルダウンを作成するために使用される
        // 家庭教師以外
        $studentsKobetsu = $this->mdlGetStudentListForT(null, $account_id, AppConst::EXT_GENERIC_MASTER_101_900);

        // 家庭教師の受け持ち生徒名プルダウンメニューを作成
        $students = $this->mdlGetStudentListForT(AppConst::EXT_GENERIC_MASTER_101_900, $account_id);

        // 授業時間数のプルダウンメニューを作成
        $minutes = $this->getMenuOfMinutes();

        // テンプレートは編集と同じ
        return view('pages.tutor.report_regist-input', [
            'editData' => null,
            'rules' => $this->rulesForInput(null),
            'student_kobetsu_list' => $studentsKobetsu,
            'student_list' => $students,
            'minutes_list' => $minutes,
            'parents_comment' => null
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

        // 現在日時を取得
        $now = Carbon::now();

        // ログイン者の情報を取得する
        $account = Auth::user();
        $account_id = $account->account_id;

        if ($request->input('lesson_type') == AppConst::CODE_MASTER_8_1) {
            //---------------
            // 個別教室登録
            //---------------
            $id = $request->input('id');

            // スケジュールidから授業日・授業開始時間・教室・生徒を取得する。
            $query = ExtSchedule::query();
            $lesson = $query
                ->select(
                    'roomcd',
                    'lesson_date',
                    'start_time',
                    'sid'
                )
                ->where('id', '=', $id)
                ->where('tid', '=', $account_id)
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
            'lesson_type',
            'r_minutes',
            'content',
            'homework',
            'teacher_comment'
        );

        // 保存
        $report = new Report;
        $report->id = $id;
        $report->roomcd = $roomcd;
        $report->lesson_date = $lesson_date;
        $report->start_time = $start_time;
        $report->sid = $sid;
        $report->tid = $account_id;
        $report->regist_time = $now;
        $report->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int $reportId 授業報告書ID
     * @return view
     */
    public function edit($reportId)
    {

        // IDのバリデーション
        $this->validateIds($reportId);

        // 授業時間数のプルダウンメニューを作成
        $minutes = $this->getMenuOfMinutes();

        // クエリを作成
        $query = Report::query();

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // データを取得
        $report = $query
            // IDを指定
            ->where('report.report_id', $reportId)
            ->select(
                'report_id',
                'lesson_type',
                'lesson_date',
                'start_time',
                'room_names.room_name_full as class_name',
                'ext_student_kihon.name as student_name',
                'r_minutes',
                'content',
                'homework',
                'teacher_comment',
                'parents_comment'
            )
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('report.roomcd', '=', 'room_names.code');
            })
            // 生徒名の取得
            ->sdLeftJoin(ExtStudentKihon::class, 'report.sid', '=', 'ext_student_kihon.sid')
            // 受け持ち生徒に限定するガードを掛ける
            ->where($this->guardTutorTableWithSid())
            // 自分のアカウントIDでガードを掛ける（tid）
            ->where($this->guardTutorTableWithTid())
            ->firstOrFail();

        return view('pages.tutor.report_regist-input', [
            'editData' => $report,
            'rules' => $this->rulesForInput(null),
            'scheduleMaster' => null,
            'student_list' => null,
            'minutes_list' => $minutes,
            'parents_comment' => $report->parents_comment
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

        // 対象データを取得(IDでユニークに取る)
        $query = Report::query();

        // 対象データを取得(PKでユニークに取る)
        $report = $query
            ->where('report_id', $request->input('report_id'))
            // 受け持ち生徒に限定するガードを掛ける
            ->where($this->guardTutorTableWithSid())
            // 自分のアカウントIDでガードを掛ける（tid）
            ->where($this->guardTutorTableWithTid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // フォームから受け取った値を格納
        $form = $request->only(
            'r_minutes',
            'content',
            'homework',
            'teacher_comment'
        );

        // 保存
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

        // 対象データを取得(IDでユニークに取る)
        $query = Report::query();

        // 対象データを取得(PKでユニークに取る)
        $report = $query
            ->where('report_id', $request->input('report_id'))
            // 受け持ち生徒に限定するガードを掛ける
            ->where($this->guardTutorTableWithSid())
            // 自分のアカウントIDでガードを掛ける（tid）
            ->where($this->guardTutorTableWithTid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // Reportテーブルのdelete
        $report->delete();

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

            // 教師IDを取得
            $account = Auth::user();
            $tid = $account->account_id;

            $lesson_date = $request['lesson_date'];
            $start_time = $request['start_time'];

            // 対象データを取得(PKでユニークに取る)
            $exists = Report::where('tid', $tid)
                ->where('lesson_date', $lesson_date)
                ->where('start_time', $start_time)
                // 授業種別
                ->where('lesson_type', AppConst::CODE_MASTER_8_2)
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

            // sidの取得(チェックはvalidationSidListで行う)
            $sid = $request['sid'];

            // ログイン者の情報を取得する
            $account = Auth::user();
            $account_id = $account->account_id;

            // 個別教室のスケジュールプルダウンメニューを作成
            $scheduleMaster = $this->getScheduleListReport($account_id, null, $sid);
            if (!isset($scheduleMaster[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 家庭教師の受け持ち生徒名
        $validationStudentsList =  function ($attribute, $value, $fail) use ($request) {

            // ログイン者の情報を取得する
            $account = Auth::user();
            $account_id = $account->account_id;

            // 家庭教師の受け持ち生徒名プルダウンメニューを作成
            $students = $this->mdlGetStudentListForT(AppConst::EXT_GENERIC_MASTER_101_900, $account_id);
            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒ID(個別教室)
        $validationSidList =  function ($attribute, $value, $fail) {

            // ログイン者の生徒No.を取得する。
            $account = Auth::user();
            $account_id = $account->account_id;

            // 教師の担当している生徒の一覧を取得
            $students = $this->mdlGetStudentListForT(null, $account_id, AppConst::EXT_GENERIC_MASTER_101_900);

            if (!isset($students[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する

        // 新規登録の場合のみチェック
        if (isset($request['report_id']) && !filled($request['report_id'])) {

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
            // 授業種別が個別教室の場合、スケジュールIDの必須チェックと重複チェック
            $rules += ['id' =>  array_merge(
                $ruleId,
                ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_1, $validationDuplicateRegular, $validationScheduleMasterList]
            )];

            // 授業種別が家庭教師の場合、必須チェックと重複チェック
            $rules += Report::fieldRules('start_time', ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_2]);
            $rules += Report::fieldRules('sid', ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_2, $validationStudentsList]);
            $rules += ['lesson_date' =>  array_merge(
                $ruleLessonDate,
                ['required_if:lesson_type,' . AppConst::CODE_MASTER_8_2, $validationDuplicateHomeTeacher]
            )];
        }

        // 新規登録・更新両方でチェック
        // MEMO: 不正アクセス対策として、report_idもルールに追加する
        $rules += Report::fieldRules('report_id');
        $rules += Report::fieldRules('r_minutes', ['required', $validationMinutesList]);
        $rules += Report::fieldRules('content', ['required']);
        $rules += Report::fieldRules('homework');
        $rules += Report::fieldRules('teacher_comment', ['required']);

        return $rules;
    }
}
