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

    //==========================
    // 詳細
    //==========================

    /**
     * 生徒提出スケジュール詳細画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function plan($sid)
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
            $timeId = str_replace(":", "", $time);
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
        array_push($editData, '1_1時限目');
        array_push($editData, '1_2時限目');
        array_push($editData, '1_3時限目');
        array_push($editData, '1_4時限目');
        array_push($editData, '1_5時限目');
        array_push($editData, '1_6時限目');
        array_push($editData, '1_7時限目');
        array_push($editData, '4_1時限目');
        array_push($editData, '5_1時限目');
        array_push($editData, '6_1時限目');
        array_push($editData, '6_2時限目');
        array_push($editData, '6_3時限目');
        array_push($editData, '6_4時限目');
        array_push($editData, '6_5時限目');
        array_push($editData, '6_6時限目');
        array_push($editData, '6_7時限目');
        array_push($editData, '11_7時限目');
        // 教師名を取得する
        //$teacher = $this->getTeacherName($tid);

        return view('pages.admin.season_mng_student-plan', [
            //'weekdayList' => $weekdayList,
            'timeList' => $timeList,
            'timeIdList' => $timeIdList,
            'editData' => [
                'chkWs' => $editData
            ],
            //'extRirekisho' => $teacher,
        ]);
    }


}
