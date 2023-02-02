<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Report;
use App\Models\ExtRirekisho;
use App\Http\Controllers\Traits\FuncReportTrait;

/**
 * 授業報告書 - コントローラ
 */
class ReportController extends Controller
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

        return view('pages.student.report');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {

        // クエリを作成
        $query = Report::query();

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // データを取得
        $reports = $query
            ->select(
                'report_id as id',
                'lesson_date',
                'start_time',
                'room_names.room_name_full as room_name',
                'ext_rirekisho.name as tname',
                'r_minutes'
            )
            // 教室名の取得
            ->leftJoinSub($room_names, 'room_names', function ($join) {
                $join->on('report.roomcd', '=', 'room_names.code');
            })
            // 教師名の取得
            ->sdLeftJoin(ExtRirekisho::class, 'report.tid', '=', 'ext_rirekisho.tid')
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // ソート順
            ->orderby('lesson_date', 'desc')
            ->orderby('start_time', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $reports);
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

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // データを取得
        $report = $query
            ->select(
                'lesson_date',
                'start_time',
                'room_names.room_name_full as room_name',
                'ext_rirekisho.name as tname',
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
            // 教師名の取得
            ->sdLeftJoin(ExtRirekisho::class, 'report.tid', '=', 'ext_rirekisho.tid')
            // IDを指定
            ->where('report.report_id', $id)
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // 取得できないエラー
            ->firstOrFail();

        return [
            'lesson_date' => $report->lesson_date,
            'start_time' => $report->start_time,
            'room_name' => $report->room_name,
            'tname' => $report->tname,
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
     * 編集画面
     *
     * @param int $reportId 授業報告書ID
     * @return view
     */
    public function edit($reportId)
    {

        // IDのバリデーション
        $this->validateIds($reportId);

        // クエリを作成
        $query = Report::query();

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        // データを取得
        $report = $query
            ->select(
                'report_id',
                'lesson_type',
                'lesson_date',
                'start_time',
                'room_names.room_name_full as class_name',
                'ext_rirekisho.name as tname',
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
            // 教師名の取得
            ->sdLeftJoin(ExtRirekisho::class, 'report.tid', '=', 'ext_rirekisho.tid')
            // IDを指定
            ->where('report.report_id', $reportId)
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // 取得できないエラー
            ->firstOrFail();

        return view('pages.student.report-edit', [
            'editData' => $report,
            'rules' => $this->rulesForInput()
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

        // 対象データを取得(IDでユニークに取る)
        $query = Report::query();

        // 対象データを取得(PKでユニークに取る)
        $report = $query
            // キー
            ->where('report_id', $request->input('report_id'))
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // フォームから受け取った値を格納
        $form = $request->only(
            'parents_comment'
        );

        // 保存
        $report->fill($form)->save();

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

        // MEMO: 不正アクセス対策として、report_idもルールに追加する
        $rules += Report::fieldRules('report_id', ['required']);
        $rules += Report::fieldRules('parents_comment');

        return $rules;
    }
}
