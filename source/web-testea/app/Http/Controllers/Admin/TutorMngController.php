<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use App\Models\Account;
use App\Models\WeeklyShift;
use App\Models\TutorSchedule;
use App\Models\ExtRirekisho;
use App\Models\Salary;
use App\Consts\AppConst;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use App\Http\Controllers\Traits\FuncSalaryTrait;
use App\Http\Controllers\Traits\FuncAgreementTrait;
use App\Models\TimesReport;
use App\Models\TrainingBrowse;
use App\Models\NoticeDestination;
use App\Models\TutorRelate;

/**
 * 教師情報 - コントローラ
 */
class TutorMngController extends Controller
{

    // 機能共通処理：カレンダー
    use FuncCalendarTrait;

    // 機能共通処理：給与
    use FuncSalaryTrait;

    // 機能共通処理：空き時間
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
        return view('pages.admin.tutor_mng', [
            'rules' => $this->rulesForSearch()
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
        // MEMO: 教室管理者でも全て見れるのでガードは不要

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearch())->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = ExtRirekisho::query();

        // tidの検索
        $query->SearchTid($form);

        // 教師名の検索
        $query->SearchName($form);

        // データを取得
        $extRirekisho = $query
            ->select(
                'tid',
                'name',
                // メールアドレス
                'account.email',
            )
            // アカウントテーブルをLeftJOIN ->JOINとする（削除教師非表示対応）
            ->sdJoin(Account::class, function ($join) {
                $join->on('ext_rirekisho.tid', '=', 'account.account_id')
                    ->where('account.account_type', AppConst::CODE_MASTER_7_2);
            })
            ->orderby('tid');

        // ページネータで返却
        return $this->getListAndPaginator($request, $extRirekisho);
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

        $rules += ExtRirekisho::fieldRules('tid');
        $rules += ExtRirekisho::fieldRules('name');

        return $rules;
    }

    //==========================
    // 教師情報詳細
    //==========================

    /**
     * 詳細画面
     *
     * @param int $tid 教師ID
     * @return view
     */
    public function detail($tid)
    {

        // MEMO: 教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIds($tid);

        // 教師名を取得する
        $extRirekisho = ExtRirekisho::select(
            'tid',
            'name',
            // メールアドレス
            'account.email',
        )
            // アカウントテーブルをLeftJOIN
            ->sdLeftJoin(Account::class, function ($join) {
                $join->on('ext_rirekisho.tid', '=', 'account.account_id')
                    ->where('account.account_type', AppConst::CODE_MASTER_7_2);
            })
            // IDを指定
            ->where('tid', $tid)
            // MEMO: 取得できない場合はエラーとする
            ->firstOrFail();

        return view('pages.admin.tutor_mng-detail', [
            // 削除用にIDを渡す
            'editData' => [
                'tid' => $tid
            ],
            'extRirekisho' => $extRirekisho,
        ]);
    }

    /**
     * 削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function deleteDetail(Request $request)
    {
        // MEMO: 教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'tid');

        // Formを取得
        $form = $request->all();

        // ext_rirekishoテーブルより対象データを取得(PKでユニークに取る)
        $rirekisho = ExtRirekisho::where('tid', $form['tid'])
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // accountテーブルより対象データを取得(PKでユニークに取る)
        $account = Account::where('account_id', $form['tid'])
            ->where('account_type', AppConst::CODE_MASTER_7_2)
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($rirekisho, $account) {

            // お知らせ宛先情報削除
            $noticeExists = NoticeDestination::where('tid', $rirekisho->tid)
                ->exists();
            // 対象教師のデータがあれば削除
            if ($noticeExists) {
                NoticeDestination::where('tid', $rirekisho->tid)->delete();
            }

            // 回数報告情報削除
            $TimesReportExists = TimesReport::where('tid', $rirekisho->tid)
                ->exists();
            // 対象教師のデータがあれば削除
            if ($TimesReportExists) {
                TimesReport::where('tid', $rirekisho->tid)->delete();
            }

            // 研修閲覧情報削除
            $trainingExists = TrainingBrowse::where('tid', $rirekisho->tid)
                ->exists();
            // 対象教師のデータがあれば削除
            if ($trainingExists) {
                TrainingBrowse::where('tid', $rirekisho->tid)->delete();
            }

            // 教師スケジュール情報削除
            $tScheduleExists = TutorSchedule::where('tid', $rirekisho->tid)
                ->exists();
            // 対象教師のデータがあれば削除
            if ($tScheduleExists) {
                TutorSchedule::where('tid', $rirekisho->tid)->delete();
            }

            // 空き時間情報削除
            $weeklyShiftExists = WeeklyShift::where('tid', $rirekisho->tid)
                ->exists();
            // 対象教師のデータがあれば削除
            if ($weeklyShiftExists) {
                weeklyShift::where('tid', $rirekisho->tid)->delete();
            }

            // 教師関連情報削除
            $tutorRelateExists = TutorRelate::where('tid', $rirekisho->tid)
                ->exists();
            // 対象教師のデータがあれば削除
            if ($tutorRelateExists) {
                TutorRelate::where('tid', $rirekisho->tid)->delete();
            }

            // アカウント情報削除
            // accountテーブルのdeleteを行う前に、emailを更新する（「DEL年月日時分秒@」を付加）
            $delStr = config('appconf.delete_email_prefix') . date("YmdHis") . config('appconf.delete_email_suffix');
            $account->email = $account->email . $delStr;
            $account->save();
            // accountテーブルのdelete
            $account->delete();
        });

        return;
    }

    //==========================
    // 給料明細
    //==========================

    /**
     * 一覧
     *
     * @param int $tid 教師ID
     * @return view
     */
    public function salary($tid)
    {

        // MEMO: 教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIds($tid);

        // 教師名を取得する
        $teacher = $this->getTeacherName($tid);

        return view('pages.admin.tutor_mng-salary', [
            'teacher_name' => $teacher->name,
            // 検索用にIDを渡す
            'editData' => [
                'tid' => $tid
            ]
        ]);
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function searchSalary(Request $request)
    {
        // MEMO: 教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション（教師No.）
        $this->validateIdsFromRequest($request, 'tid');

        // 給与明細を取得する
        $query = Salary::query();
        $salarys = $query
            ->select(
                'salary_date',
            )
            ->where('tid', '=', $request->input('tid'))
            ->orderBy('salary_date', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $salarys, function ($items) {
            foreach ($items as $item) {
                $item['tid'] = $item->salary_date->format('Ym');
            }

            return $items;
        });
    }

    //==========================
    // 給料明細
    //==========================

    /**
     * 詳細画面
     *
     * @param int $tid 教師No.
     * @param date $date 年月（YYYYMM）
     * @return view
     */
    public function detailSalary($tid, $date)
    {
        // MEMO: 教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIds($tid, $date);

        // データの取得
        $dtlData = $this->getSalaryDetail($tid, $date);

        return view('pages.admin.tutor_mng-salary_detail', [
            'salary' => $dtlData['salary'],
            'salary_detail_1' => $dtlData['salary_detail_1'],
            'salary_detail_2' => $dtlData['salary_detail_2'],
            'salary_detail_3' => $dtlData['salary_detail_3'],
            'salary_detail_4' => $dtlData['salary_detail_4'],
            // PDF用にIDを渡す
            'editData' => [
                'tid' => $tid,
                'date' => $date
            ]
        ]);
    }

    /**
     * PDF出力
     *
     * @param int $tid 教師No.
     * @param date $date 年月（YYYYMM）
     * @return void
     */
    public function pdf($tid, $date)
    {
        // MEMO: 教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIds($tid, $date);

        // データの取得
        $dtlData = $this->getSalaryDetail($tid, $date);

        // 給与PDFの出力(管理画面でも使用するので共通化)
        $this->outputPdfSalary($dtlData);

        // 特になし
        return;
    }

    //==========================
    // 教師空き時間
    //==========================

    /**
     * 空き時間画面
     *
     * @param int $tid 教師ID
     * @return view
     */
    public function weeklyShift($tid)
    {
        // MEMO: 教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIds($tid);

        // 曜日の配列を取得 コードマスタより取得
        $weekdayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // 時間帯 コードマスタにないのでappconfに定義した。
        $timeList = config('appconf.weekly_shift_time');

        // コロンを除いた値をIDとして扱う
        // 管理画面では送信しないが、教師画面と統一した
        $timeIdList = [];
        foreach ($timeList as $time) {
            $timeId = str_replace(":", "", $time);
            array_push($timeIdList, $timeId);
        }

        // 教師の空き時間を取得する
        $weeklyShift = WeeklyShift::where('tid', $tid)
            ->get();

        // チェックボックスをセットするための値を生成
        // 例：['1_1030', '2_1030']
        $editData = [];
        foreach ($weeklyShift as $ws) {
            // 配列に追加
            array_push($editData, $ws->weekdaycd . '_' . $ws->start_time->format('Hi'));
        }

        // 教師名を取得する
        $teacher = $this->getTeacherName($tid);

        return view('pages.admin.tutor_mng-weekly_shift', [
            'weekdayList' => $weekdayList,
            'timeList' => $timeList,
            'timeIdList' => $timeIdList,
            'editData' => [
                'chkWs' => $editData
            ],
            'extRirekisho' => $teacher,
        ]);
    }

    //==========================
    // 教師カレンダー
    //==========================

    /**
     * カレンダー画面
     *
     * @param int $tid 教師ID
     * @return view
     */
    public function calendar($tid)
    {
        // MEMO: 教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIds($tid);

        // 教師名を取得する
        $teacher = $this->getTeacherName($tid);

        return view('pages.admin.tutor_mng-calendar', [
            'name' => $teacher->name,
            // カレンダー用にIDを渡す
            'editData' => [
                'tid' => $tid
            ]
        ]);
    }

    /**
     * カレンダー取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 教師ID
     */
    public function getCalendar(Request $request)
    {

        // MEMO: 教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'tid');

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForCalendar())->validate();

        $tid = $request->input('tid');

        return $this->getTutorCalendar($request, $tid);
    }

    //==========================
    // 教師打ち合わせ予定
    //==========================

    /**
     * 登録画面
     *
     * @param int  $tid 教師ID
     * @return view
     */
    public function new($tid)
    {

        // IDのバリデーション
        $this->validateIds($tid);

        // 教師名を取得する
        $teacher = $this->getTeacherName($tid);

        // 教師のidを渡しておく
        $editData = [
            'tid' => $tid
        ];

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList();

        return view('pages.admin.tutor_mng-calendar-input', [
            'name' => $teacher->name,
            'rooms' => $rooms,
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
    public function create(Request $request)
    {

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // フォームから受け取った値を格納
        $form = $request->only(
            'tid',
            'start_date',
            'start_time',
            'end_time',
            'title',
            // 教室管理者の場合の教室コードのチェックはバリデーション(validationRoomList)で行っている
            'roomcd'
        );

        // 保存
        $tutorSchedule = new TutorSchedule;
        $tutorSchedule->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int $tid 教師ID
     * @param int $tutorScheduleId スケジュールID
     * @return view
     */
    public function edit($tid, $tutorScheduleId)
    {

        // IDのバリデーション
        $this->validateIds($tid, $tutorScheduleId);

        // 打ち合わせのcidでデータ取得
        $query = TutorSchedule::query();
        $editData = $query
            ->select(
                'tutor_schedule_id',
                'tid',
                'title',
                'roomcd',
                'start_date',
                'start_time',
                'end_time'
            )
            ->where('tutor_schedule.tutor_schedule_id', '=', $tutorScheduleId)
            // 教師IDも一応指定する
            ->where('tutor_schedule.tid', '=', $tid)
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 教師名を取得する
        $teacher = $this->getTeacherName($tid);

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList();

        return view('pages.admin.tutor_mng-calendar-input', [
            'name' => $teacher->name,
            'rooms' => $rooms,
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

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'tutor_schedule_id');

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // フォームから受け取った値を格納
        $form = $request->only(
            'start_date',
            'start_time',
            'end_time',
            'title',
            'roomcd'
        );

        // 保存
        $tutorSchedule = TutorSchedule::where('tutor_schedule_id', $request['tutor_schedule_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        $tutorSchedule->fill($form)->save();

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
        $this->validateIdsFromRequest($request, 'tutor_schedule_id');

        $form = $request->only(
            'tutor_schedule_id'
        );

        // 削除対象データの取得
        $tutor_schedule = TutorSchedule::where('tutor_schedule_id', $form['tutor_schedule_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            ->firstOrFail();

        // 削除
        $tutor_schedule->delete();
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

        // 独自バリデーション: リストのチェック ステータス
        $validationRoomList =  function ($attribute, $value, $fail) {

            $rooms = $this->mdlGetRoomList();
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション：時間の大小チェック
        $validationEndTime = function ($attribute, $value, $fail) use ($request) {
            if (!$request || !(isset($request['start_time']) || isset($request['end_time']))) {
                return;
            }

            // 時刻がゼロ埋めで来ないケースもあるので、strtotimeで時刻に変換
            // 1:3 とか 20:00 という文字列も変換できた。(年月日は本日になる)
            $start = strtotime($request['start_time']);
            $end = strtotime($request['end_time']);
            if (!$start || !$end) {
                // 時刻の形式チェックは別で行うので、時刻として取れない場合はここでは無視
                return;
            }

            // 終了時刻が開始時刻より前の場合
            if ($start >= $end) {
                return $fail(Lang::get('validation.after_time'));
            }
        };

        // 独自バリデーション：開催日が現在日以降かチェックする
        $validationStartDate = function ($attribute, $value, $fail) use ($request) {
            if (!$request || !(isset($request['start_date']))) {
                return;
            }

            $today = date("Y-m-d");
            $start_date = $request['start_date'];

            // 開催日が現在日より前の場合
            if (strtotime($start_date) < strtotime($today)) {
                return $fail(Lang::get('validation.after_or_equal_today'));
            }
        };

        // 独自バリデーション: 予定の重なりチェック
        $validationSchedulesOverlap = function ($attribute, $value, $fail) use ($request) {

            if (!isset($request['tid']) || !isset($request['start_date']) || !isset($request['start_time']) || !isset($request['end_time'])) {
                // requiredでチェックするのでreturn
                return;
            }

            $req_start_time = date('H:i', strtotime($request['start_time']));
            $req_end_time = date('H:i', strtotime($request['end_time']));

            $exists = TutorSchedule::where('tid', $request['tid'])
                ->where('start_date', $request['start_date'])
                ->where(function ($orQuery) use ($req_start_time, $req_end_time) {
                    $orQuery
                        // 開始時刻が別の予定と重なる場合
                        ->where(function ($start) use ($req_start_time) {
                            $start
                                ->where('start_time', '<=', $req_start_time)
                                ->where('end_time', '>', $req_start_time);
                        })
                        // 終了時刻が別の予定と重なる場合
                        ->orWhere(function ($end) use ($req_end_time) {
                            $end
                                ->where('start_time', '<', $req_end_time)
                                ->where('end_time', '>=', $req_end_time);
                        })
                        // 開始から終了までが別の予定をまたぐ場合またはピッタリ同じ場合
                        ->orWhere(function ($over) use ($req_start_time, $req_end_time) {
                            $over
                                ->where('start_time', '>=', $req_start_time)
                                ->where('end_time', '<=', $req_end_time);
                        });
                })
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.schedules_overlap'));
            }
        };

        $rules += TutorSchedule::fieldRules('tutor_schedule_id');
        $rules += TutorSchedule::fieldRules('tid', ['required']);
        $rules += TutorSchedule::fieldRules('start_date', ['required', $validationStartDate]);
        $rules += TutorSchedule::fieldRules('start_time', ['required', $validationSchedulesOverlap]);
        $rules += TutorSchedule::fieldRules('end_time', ['required', $validationEndTime, $validationSchedulesOverlap]);
        $rules += TutorSchedule::fieldRules('title', ['required']);
        $rules += TutorSchedule::fieldRules('roomcd', ['required', $validationRoomList]);

        return $rules;
    }

    //==========================
    // クラス内共通処理
    //==========================

    /**
     * 教師名の取得
     *
     * @param int $tid 教師ID
     * @return object
     */
    private function getTeacherName($tid)
    {
        // 教師名を取得する
        $query = ExtRirekisho::query();
        $teacher = $query
            ->select(
                'name'
            )
            ->where('ext_rirekisho.tid', '=', $tid)
            ->firstOrFail();

        return $teacher;
    }
}
