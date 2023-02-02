<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libs\AuthEx;
use App\Models\ExtRoom;
use App\Models\ExtRirekisho;
use App\Models\TimesReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use App\Http\Controllers\Traits\FuncTimesTrait;
use App\Http\Controllers\Traits\GuardTrait;

/**
 * 回数報告 - コントローラ
 */
class TimesCheckController extends Controller
{

    // 機能共通処理：回数報告
    use FuncTimesTrait;

    // ガード共通処理
    use GuardTrait;

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

        // 実施月プルダウンの取得
        $reportDate = $this->getTimesDateList();

        return view('pages.admin.times_check', [
            'rules' => $this->rulesForSearch(),
            'reportDate' => $reportDate,
            'editData' => null,
            'rooms' => $rooms
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

        // クエリ作成
        $query = TimesReport::query();

        // 実施月の検索
        $query->SearchReportDate($form);

        // 教師名の検索(生徒基本情報参照)
        (new ExtRirekisho)->scopeSearchName($query, $form);

        // 教師の教室の検索(教師関連情報参照)
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、強制的に教室コードで検索する
            $account = Auth::user();
            $query->where('times_report.roomcd', '=', $account->roomcd);
        } else {
            // 管理者の場合検索フォームから取得
            if ($form['roomcd'] != '') {
                $query->where('times_report.roomcd', '=', $form['roomcd']);
            }
        }

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        // データを取得
        $timesReport = $query->select(
            'times_report_id',
            'times_report.tid',
            'regist_time',
            'ext_rirekisho.name',
            'report_date',
            'room_names.room_name',
            'times_report.created_at'
        )
            // 教師マスターとJOIN
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('times_report.tid', '=', 'ext_rirekisho.tid');
            })
            // 教室名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('times_report.roomcd', '=', 'room_names.code');
            })
            ->orderBy('regist_time', 'desc')
            ->orderBy('times_report.created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $timesReport);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getData(Request $request)
    {

        // tIDのバリデーション
        $this->validateIdsFromRequest($request, 'tid', 'times_report_id');

        // 日付のバリデーション
        $this->validateDatesFromRequest($request, 'start_month');

        // 日時を取得
        $startMonth = new Carbon($request['start_month']);

        // tidを取得
        $tid = $request['tid'];

        // idを取得
        $timesReportId = $request['times_report_id'];

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        // 回数報告書
        $query = TimesReport::query();
        $timesReport = $query->select(
            'regist_time',
            'ext_rirekisho.name',
            'report_date',
            'roomcd',
            'office_work',
            'other',
            'room_names.room_name'
        )
            ->where('times_report_id', $timesReportId)
            // 一応教師IDで絞る
            ->where('times_report.tid', $tid)
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('times_report.tid', '=', 'ext_rirekisho.tid');
            })
            // 教室名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('times_report.roomcd', '=', 'room_names.code');
            })
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            ->firstOrFail();

        // 生徒のレポートを取得
        $studentReport = $this->getStudentReportList($tid, $startMonth, $timesReport->roomcd);

        return [
            // 授業一覧
            'class' => $studentReport['reportList'],
            // 生徒一覧(回数)
            'student' => $studentReport['countList'],
            // 報告日
            'registTime' => $timesReport->regist_time,
            // 教師名
            'name' => $timesReport->name,
            // 実施月
            'reportDate' => $timesReport->report_date,
            // 教室
            'room_name' => $timesReport->room_name,
            // 事務作業
            'officeWork' => $timesReport->office_work,
            // その他
            'other' => $timesReport->other
        ];;
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

            // 教室リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 実施月
        $validationReportDateList =  function ($attribute, $value, $fail) {

            // 実施月プルダウンの取得
            $reportDate = $this->getTimesDateList();
            if (!isset($reportDate[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        $rules = array();

        $rules += TimesReport::fieldRules('report_date', [$validationReportDateList]);
        $rules += ExtRirekisho::fieldRules('name');
        $rules += ExtRoom::fieldRules('roomcd', [$validationRoomList]);

        return $rules;
    }

    //==========================
    // 編集
    //==========================

    /**
     * 編集画面
     *
     * @param int $timesReportId
     * @return view
     */
    public function edit($timesReportId)
    {
        // IDのバリデーション
        $this->validateIds($timesReportId);

        // 実施月プルダウンの取得
        $reportDate = $this->getTimesDateList();

        // 教師の所属教室のプルダウン取得
        $rooms = $this->mdlGetRoomList(false);

        // 回数報告書
        $query = TimesReport::query();
        $editData = $query->select(
            'times_report_id',
            'times_report.tid',
            'regist_time',
            'ext_rirekisho.name',
            'report_date',
            'roomcd',
            'office_work',
            'other'
        )
            ->where('times_report_id', $timesReportId)
            // 教師名の取得
            ->sdLeftJoin(ExtRirekisho::class, function ($join) {
                $join->on('times_report.tid', '=', 'ext_rirekisho.tid');
            })
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            ->firstOrFail();

        return view('pages.admin.times_check-edit', [
            'editData' => $editData,
            'rules' => $this->rulesForInput(null),
            'reportDate' => $reportDate,
            'rooms' => $rooms
        ]);
    }

    /**
     * 授業日時・生徒名・教科情報取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 授業日時、生徒名、教科情報
     */
    public function getDataSelect(Request $request)
    {

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'tid');

        // 日付のバリデーション
        $this->validateDatesFromRequest($request, 'reportDate');

        // $requestからidとreportDateを取得し、検索結果を返却する
        $tid = $request['tid'];
        $startMonth = $request['reportDate'];
        $roomcd = $request['roomcd'];

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------

        // 実施月プルダウンの取得
        $reportDate = $this->getTimesDateList();
        $this->guardListValue($reportDate, $startMonth);

        // 教師の所属教室のプルダウン取得
        $rooms = $this->mdlGetRoomList(false);
        $this->guardListValue($rooms, $roomcd);

        // 教師管理者の場合はPOSTされたroomcdが管理教室と一致するかチェックする。
        $this->guardRoomAdminRoomcd($roomcd);

        // 生徒のレポートを取得
        $studentReport = $this->getStudentReportList($tid, $startMonth, $roomcd);

        return [
            // 授業一覧
            'class' => $studentReport['reportList'],
            // 生徒一覧(回数)
            'student' => $studentReport['countList']
        ];
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

        // MEMO: 必ず登録する項目のみに絞る。
        $form = $request->only(
            'regist_time',
            'report_date',
            'roomcd',
            'office_work',
            'other'
        );

        // 対象データを取得(PKでユニークに取る)
        $timesReport = TimesReport::where('times_report_id', $request['times_report_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $timesReport->fill($form)->save();
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
        $this->validateIdsFromRequest($request, 'times_report_id');

        // Formを取得
        $form = $request->all();

        // 1件取得
        $leaveApply = TimesReport::where('times_report_id', $form['times_report_id'])
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 削除
        $leaveApply->delete();

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

        // 独自バリデーション: リストのチェック 実施月
        $validationReportDateList =  function ($attribute, $value, $fail) {

            // 実施月プルダウンの取得
            $reportDate = $this->getTimesDateList();
            if (!isset($reportDate[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 教室コード
        $validationRoomsList =  function ($attribute, $value, $fail) {

            // 教室プルダウンの取得
            $rooms = $this->mdlGetRoomList(false);

            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules = array();

        // 独自バリデーション: 重複チェック（実施月＆教室＆教師No.）
        $validationMonth = function ($attribute, $value, $fail) use ($request) {

            if (!$request) {
                return;
            }

            // tidを取得(リクエストではなくTimesReportから取得)
            $timesReport = TimesReport::where('times_report_id', '=', $request['times_report_id'])
                ->firstOrFail();

            // 対象データを取得(ユニークに取る)
            $exists = TimesReport::where('tid',  $timesReport->tid)
                ->where('report_date', $request->report_date)
                ->where('roomcd', $request->roomcd)
                // 変更時は自分のキー以外を検索
                ->whereNotIn('times_report_id', [$request['times_report_id']])
                ->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        $rules += TimesReport::fieldRules('times_report_id', ['required']);
        $rules += TimesReport::fieldRules('report_date', ['required', $validationMonth, $validationReportDateList]);
        $rules += TimesReport::fieldRules('regist_time', ['required']);
        $rules += TimesReport::fieldRules('office_work');
        $rules += TimesReport::fieldRules('other');
        $rules += TimesReport::fieldRules('roomcd', ['required', $validationMonth, $validationRoomsList]);

        return $rules;
    }
}
