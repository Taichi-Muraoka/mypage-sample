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
use App\Models\MstSchool;
use App\Models\CodeMaster;

/**
 * 講師情報 - コントローラ
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
        // 入会状況チェックボックス
        $statusGroup = array("在籍","退職処理中","退職");

        return view('pages.admin.tutor_mng', [
            'rules' => $this->rulesForSearch(),
            'statusGroup' => $statusGroup,
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
                'accounts.email',
            )
            // アカウントテーブルをLeftJOIN ->JOINとする（削除教師非表示対応）
            ->sdJoin(Account::class, function ($join) {
                $join->on('ext_rirekisho.tid', '=', 'accounts.account_id')
                    ->where('accounts.account_type', AppConst::CODE_MASTER_7_2);
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

        // // MEMO: 教室管理者でも全て見れるのでガードは不要

        // // IDのバリデーション
        // $this->validateIds($tid);

        // // 教師名を取得する
        // $extRirekisho = ExtRirekisho::select(
        //     'tid',
        //     'name',
        //     // メールアドレス
        //     'accounts.email',
        // )
        //     // アカウントテーブルをLeftJOIN
        //     ->sdLeftJoin(Account::class, function ($join) {
        //         $join->on('ext_rirekisho.tid', '=', 'accounts.account_id')
        //             ->where('accounts.account_type', AppConst::CODE_MASTER_7_2);
        //     })
        //     // IDを指定
        //     ->where('tid', $tid)
        //     // MEMO: 取得できない場合はエラーとする
        //     ->firstOrFail();

        return view('pages.admin.tutor_mng-detail', [
            // 削除用にIDを渡す
            'editData' => [
                'tid' => null
            ],
            'extRirekisho' => null,
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
        //==========================
        // 既存処理
        //==========================
        // // MEMO: 教室管理者でも全て見れるのでガードは不要

        // // IDのバリデーション
        // $this->validateIds($tid);

        // // 曜日の配列を取得 コードマスタより取得
        // $weekdayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // // 時間帯 コードマスタにないのでappconfに定義した。
        // $timeList = config('appconf.weekly_shift_time');

        // // コロンを除いた値をIDとして扱う
        // // 管理画面では送信しないが、教師画面と統一した
        // $timeIdList = [];
        // foreach ($timeList as $time) {
        //     $timeId = str_replace(":", "", $time);
        //     array_push($timeIdList, $timeId);
        // }

        // // 教師の空き時間を取得する
        // $weeklyShift = WeeklyShift::where('tid', $tid)
        //     ->get();

        // // チェックボックスをセットするための値を生成
        // // 例：['1_1030', '2_1030']
        // $editData = [];
        // foreach ($weeklyShift as $ws) {
        //     // 配列に追加
        //     array_push($editData, $ws->weekdaycd . '_' . $ws->start_time->format('Hi'));
        // }

        // // 教師名を取得する
        // $teacher = $this->getTeacherName($tid);

        // return view('pages.admin.tutor_mng-weekly_shift', [
        //     'weekdayList' => $weekdayList,
        //     'timeList' => $timeList,
        //     'timeIdList' => $timeIdList,
        //     'editData' => [
        //         'chkWs' => $editData
        //     ],
        //     'extRirekisho' => $teacher,
        // ]);

        //==========================
        // モック用処理
        //==========================
        // 曜日の配列を取得 コードマスタより取得
        $weekdayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // 時間帯
        $timeList = array(
            '1時限目','2時限目','3時限目','4時限目','5時限目','6時限目','7時限目',
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

        // チェックボックスをセットするための値を生成
        // 例：['1_1030', '2_1030']
        $editData = [];
        //foreach ($weeklyShift as $ws) {
        //    // 配列に追加
        //    array_push($editData, $ws->weekdaycd . '_' . $ws->start_time->format('Hi'));
        //}
        array_push($editData, '4_5');
        array_push($editData, '4_6');
        array_push($editData, '4_7');

        // レギュラー授業情報を取得し、チェックボックスをセットするための値を生成
        // 曜日コード_時限No 例：['1_1', '2_1']
        $regularData = [];
        array_push($regularData, '1_5');
        array_push($regularData, '2_5');
        array_push($regularData, '2_6');
        array_push($regularData, '6_4');
        array_push($regularData, '6_5');
        array_push($regularData, '6_6');
        array_push($regularData, '6_7');

        // 教師名を取得する
        $teacher = $this->getTeacherName($tid);

        return view('pages.admin.tutor_mng-weekly_shift', [
            'weekdayList' => $weekdayList,
            'timeList' => $timeList,
            'timeIdList' => $timeIdList,
            'editData' => [
                'chkWs' => $editData
            ],
            'exceptData' => $regularData,
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
    // 教師登録・編集
    //==========================

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        // 教科グループチェックボックス
        $subjectGroup = array(
            "英語",
            "数学",
            "算数",
            "国語",
            "古文・漢文",
            "理科",
            "化学",
            "物理",
            "生物",
            "地学",
            "社会",
            "世界史",
            "日本史",
            "地理",
            "政治経済",
            "算数・国語",
            "数学・理科",
            "英語・数学",
            "理科・社会",
            "英語・国語",
            "国語・社会",
            "算数・理科"
        );

        // 学校検索モーダル用のデータ渡し
        // 学校種リストを取得
        $schoolKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_49);

        // 設置区分リストを取得
        $establishKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_50);

        // テンプレートは編集と同じ
        return view('pages.admin.tutor_mng-input', [
            'editData' => null,
            'rules' => $this->rulesForInput(null),
            'subjectGroup' => $subjectGroup,

            // 学校検索モーダル用のバリデーションルール
            'rulesSchool' => $this->rulesForSearchSchool(),
            'schoolKindList' => $schoolKindList,
            'establishKindList' => $establishKindList,
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
     * 編集画面,
     *
     * @param int $tid 教師ID
     * @return void
     */
    public function edit($tid)
    {
        // 教科グループチェックボックス
        $subjectGroup = array(
            "英語",
            "数学",
            "算数",
            "国語",
            "古文・漢文",
            "理科",
            "化学",
            "物理",
            "生物",
            "地学",
            "社会",
            "世界史",
            "日本史",
            "地理",
            "政治経済",
            "算数・国語",
            "数学・理科",
            "英語・数学",
            "理科・社会",
            "英語・国語",
            "国語・社会",
            "算数・理科"
        );

        // 学校検索モーダル用のデータ渡し
        // 学校種リストを取得
        $schoolKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_49);

        // 設置区分リストを取得
        $establishKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_50);

        return view('pages.admin.tutor_mng-input', [
            'editData' => null,
            'rules' => $this->rulesForInput(null),
            'subjectGroup' => $subjectGroup,

            // 学校検索モーダル用のバリデーションルール
            'rulesSchool' => $this->rulesForSearchSchool(),
            'schoolKindList' => $schoolKindList,
            'establishKindList' => $establishKindList,
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
     * バリデーション(登録用)（教師登録）
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
     * バリデーションルールを取得(登録用)（教師登録）
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        $rules = array();

        return $rules;
    }

    //==========================
    // 退職処理
    //==========================
    /**
     * 退職登録画面
     *
     * @return view
     */
    public function leaveEdit()
    {

        return view('pages.admin.tutor_mng-leave', [
            'editData' => null,
            'rules' => $this->rulesForInput(null)
        ]);
    }

    /**
     * 退職登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function leaveUpdate(Request $request)
    {
        return;
    }

    //==========================
    // 所属登録・編集
    //==========================

    /**
     * 登録画面
     *
     * @return view
     */
    public function campusNew()
    {
        return view('pages.admin.tutor_mng-campus-input', [
            'classes' => null,
            'editData' => null,
            'rules' => $this->rulesForInput(null)
        ]);
    }

    /**
     * 登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function campusCreate(Request $request)
    {
        return;
    }

    /**
     * 編集画面
     *
     * @param int $sid 生徒ID
     * @return view
     */
    public function campusEdit($sid)
    {
        return view('pages.admin.tutor_mng-campus-input', [
            'editData' => null,
            'rules' => $this->rulesForInput(null)
        ]);
    }

    /**
     * 編集処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function campusUpdate(Request $request)
    {
        return;
    }

    /**
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function campusValidationForInput(Request $request)
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
    private function campusRulesForInput(?Request $request)
    {
        $rules = array();

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

    //==========================
    // 学校検索
    //==========================

    /**
     * 検索結果取得(学校検索)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function searchSchool(Request $request)
    {
        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForSearchSchool())->validate();

        // formを取得
        $form = $request->all();

        // クエリ作成
        $query = MstSchool::query();

        // 学校種の絞り込み条件
        $query->SearchSchoolKind($form);

        // 設置区分の絞り込み条件
        $query->SearchEstablishKind($form);

        // 学校コードの絞り込み条件
        $query->SearchSchoolCd($form);

        // 学校名の絞り込み条件
        $query->SearchSchoolName($form);

        // バッジ一覧取得
        $schoolList = $query
            ->select(
                'mst_schools.school_cd',
                'mst_schools.school_kind_cd',
                // コードマスタの名称(学校種コード)
                'mst_codes_49.name as school_kind_name',
                'mst_schools.establish_kind',
                // コードマスタの名称(設置区分)
                'mst_codes_50.name as establish_name',
                'mst_schools.name as school_name',
            )
            // コードマスターとJOIN（学校種コード）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_schools.school_kind_cd', '=', 'mst_codes_49.code')
                    ->where('mst_codes_49.data_type', AppConst::CODE_MASTER_49);
            }, 'mst_codes_49')
            // コードマスターとJOIN（設置区分）
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('mst_schools.establish_kind', '=', 'mst_codes_50.code')
                    ->where('mst_codes_50.data_type', AppConst::CODE_MASTER_50);
            }, 'mst_codes_50')
            ->orderby('mst_schools.school_cd');

        // ページネータで返却
        return $this->getListAndPaginator($request, $schoolList);
    }

    /**
     * バリデーション(学校検索用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForSearchSchool(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForSearchSchool());
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(学校検索用)
     *
     * @return array ルール
     */
    private function rulesForSearchSchool()
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 学校種
        $validationSchoolKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $schoolKinds = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_49);
            if (!isset($schoolKinds[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 設置区分
        $validationEstablishKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $establish = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_50);
            if (!isset($establish[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += MstSchool::fieldRules('school_kind_cd', [$validationSchoolKindList]);
        $rules += MstSchool::fieldRules('establish_kind', [$validationEstablishKindList]);
        // 学校コードまたは学校名のどちらか必須
        $rules += MstSchool::fieldRules('school_cd', ['required_without_all:name']);
        $rules += MstSchool::fieldRules('name', ['required_without_all:school_cd']);

        return $rules;
    }
}
