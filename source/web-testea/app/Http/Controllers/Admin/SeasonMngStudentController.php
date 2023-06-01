<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\CodeMaster;
use App\Models\CourseApply;
use App\Models\ExtStudentKihon;
use App\Models\ExtRoom;
use App\Models\Notice;
use App\Models\NoticeDestination;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FuncCourseTrait;

/**
 * 特別期間講習 生徒提出スケジュール - コントローラ
 */
class SeasonMngStudentController extends Controller
{

    // 機能共通処理：コース変更・授業追加
    //use FuncCourseTrait;

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
        //$rooms = $this->mdlGetRoomList(false);

        // ステータスのプルダウン取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_2);

        return view('pages.admin.season_mng_student', [
            'statusList' => $statusList,
            //'rooms' => $rooms,
            'editData' => null,
            'rules' => $this->rulesForSearch()
        ]);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        return;
    }

    /**
     * バリデーション(検索用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForSearch(Request $request)
    {
        return;
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // ページネータで返却（モック用）
        return $this->getListAndPaginatorMock();
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch()
    {
        return;
    }

    /**
     * 生徒提出スケジュール詳細画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function detail($sid)
    {

        //==========================
        // モック用処理
        //==========================
        // 曜日の配列を取得 コードマスタより取得
        //$weekdayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // 時間帯
        $timeList = array(
            '1時限目','2時限目','3時限目','4時限目','5時限目','6時限目','7時限目',
        );

        // コロンを除いた値をIDとして扱う
        // 管理画面では送信しないが、教師画面と統一した
        $timeIdList = [];
        foreach ($timeList as $time) {
            //$timeId = str_replace(":", "", $time);
            $timeId = str_replace("時限目", "", $time);
            array_push($timeIdList, $timeId);
        }

        // 教師の空き時間を取得する
        //$weeklyShift = WeeklyShift::where('tid', $tid)
        //    ->get();

        // チェックボックスをセットするための値を生成
        // 例：['1_1030', '2_1030']
        $editData = [];
        //foreach ($weeklyShift as $ws) {
        //    // 配列に追加
        //    array_push($editData, $ws->weekdaycd . '_' . $ws->start_time->format('Hi'));
        //}
        // 教師名を取得する
        //$teacher = $this->getTeacherName($tid);

        return view('pages.admin.season_mng_student-detail', [
            //'weekdayList' => $weekdayList,
            'timeList' => $timeList,
            'timeIdList' => $timeIdList,
            'editData' => [
                'chkWs' => $editData
            ],
            //'extRirekisho' => $teacher,
        ]);
    }

    //==========================
    // 詳細
    //==========================

    /**
     * 生徒提出スケジュール詳細画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function plan($sid, $subjectId)
    {

        //==========================
        // モック用処理
        //==========================
        // 曜日の配列を取得 コードマスタより取得
        //$weekdayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // 時限リスト
        $timeList = array(
            '1時限目','2時限目','3時限目','4時限目','5時限目','6時限目','7時限目',
        );

        // 期間リスト（日付・曜日）
        $dayList = array(
            '03/27(月)','03/28(火)','03/29(水)','03/30(木)','03/31(金)','04/01(土)',
            '04/03(月)','04/04(火)','04/05(水)','04/06(木)','04/07(金)','04/08(土)'
        );

        // コロンを除いた値をIDとして扱う
        // 管理画面では送信しないが、教師画面と統一した
        $timeIdList = [];
        foreach ($timeList as $time) {
            $timeId = str_replace("時限目", "", $time);
            array_push($timeIdList, $timeId);
        }

        // 教師の空き時間を取得する
        //$weeklyShift = WeeklyShift::where('tid', $tid)
        //    ->get();

        // チェックボックスをセットするための値を生成（日index_時限）
        //// 例：['1_1030', '2_1030']
        // 例：['1_4', '2_1']
        $editData = [];
        //foreach ($weeklyShift as $ws) {
        //    // 配列に追加
        //    array_push($editData, $ws->weekdaycd . '_' . $ws->start_time->format('Hi'));
        //}
        array_push($editData, '1_1');
        array_push($editData, '1_2');
        array_push($editData, '1_3');
        array_push($editData, '1_4');
        array_push($editData, '1_5');
        array_push($editData, '1_6');
        array_push($editData, '1_7');
        array_push($editData, '4_1');
        array_push($editData, '5_1');
        array_push($editData, '6_1');
        array_push($editData, '6_2');
        array_push($editData, '6_3');
        array_push($editData, '6_4');
        array_push($editData, '6_5');
        array_push($editData, '6_6');
        array_push($editData, '6_7');
        array_push($editData, '11_7');
        // 教師名を取得する
        //$teacher = $this->getTeacherName($tid);

        return view('pages.admin.season_mng_student-plan', [
            //'weekdayList' => $weekdayList,
            'periodList' => $timeList,
            'periodIdList' => $timeIdList,
            'dayList' => $dayList,
            'editData' => [
                'chkWs' => $editData
            ],
            //'extRirekisho' => $teacher,
        ]);
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

        $editData = [
            'sid' => $sid
            //"record_kind" => 1
        ];

        // テンプレートは編集と同じ
        return view('pages.admin.season_mng_student-plan', [
            'editData' => $editData,
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
     * @param int $recordId 生徒カルテID
     * @return view
     */
    public function edit($recordId)
    {

        $editData = [
            "sid" => 1,
            "record_kind" => 1
        ];

        return view('pages.admin.season_mng_student-plan', [
            'editData' => $editData,
            'rules' => $this->rulesForInput(null),
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
     * 編集処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function updatePlan(Request $request)
    {

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
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInputPlan(Request $request)
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

}
