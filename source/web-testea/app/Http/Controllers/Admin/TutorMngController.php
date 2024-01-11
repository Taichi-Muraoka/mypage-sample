<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use App\Http\Controllers\Traits\FuncSalaryTrait;
use App\Http\Controllers\Traits\FuncTutorDetailTrait;
use App\Http\Controllers\Traits\FuncSchoolSearchTrait;
use App\Http\Controllers\Traits\FuncWeeklyShiftTrait;
use App\Consts\AppConst;
use App\Models\MstTutorGrade;
use App\Models\MstSchool;
use App\Models\MstCampus;
use App\Models\MstSubject;
use App\Models\MstSystem;
use App\Models\Account;
use App\Models\Salary;
use App\Models\CodeMaster;
use App\Models\Tutor;
use App\Models\TutorSubject;
use App\Models\TutorCampus;
use App\Libs\AuthEx;
use App\Mail\MypageGuideToTutor;
use App\Mail\MypageGuideRejoinToTutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * 講師情報 - コントローラ
 */
class TutorMngController extends Controller
{
    // 機能共通処理：講師詳細情報
    use FuncTutorDetailTrait;

    // 機能共通処理：カレンダー
    use FuncCalendarTrait;

    // 機能共通処理：給与
    use FuncSalaryTrait;

    // 機能共通処理：空き時間
    use FuncWeeklyShiftTrait;

    // 機能共通処理：学校検索モーダル
    use FuncSchoolSearchTrait;

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
        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);
        // 学年リストを取得
        $gradeList = $this->mdlGetTutorGradeList();
        // ステータスリストを取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_29);

        return view('pages.admin.tutor_mng', [
            'rules' => $this->rulesForSearch(null),
            'rooms' => $rooms,
            'gradeList' => $gradeList,
            'statusList' => $statusList,
            'editData' => null,
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
        Validator::make($request->all(), $this->rulesForSearch($request))->validate();

        // formを取得
        $form = $request->all();

        // クエリを作成
        $query = Tutor::query();

        // 校舎の検索
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の校舎の講師のみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithTid());
        } else {
            // 管理者の場合検索フォームから取得
            $query->SearchRoom($form);
        }

        // 講師ステータスの検索
        // 配列としてリクエストされた講師ステータスが、各ステータスコードと合致するように整形
        $group = [];
        foreach ($form['status_groups'] as $val) {
            $group[$val] = $val;
        }
        $query->SearchTutorStatus($form, $group);

        // 講師IDの検索
        $query->SearchTutorId($form);

        // 講師名の検索
        $query->SearchName($form);

        // 学年の検索
        $query->SearchGradeCd($form);

        // ベース給の検索
        $query->SearchHourlyBaseWage($form);

        // 勤続期間の月数取得のサブクエリ
        $enter_term_query = $this->mdlGetTutorEnterTermQuery();

        // データを取得
        $tutors = $query
            ->select(
                'tutors.tutor_id',
                'tutors.name',
                'tutors.grade_cd',
                // 学年の名称
                'mst_tutor_grades.name as grade_name',
                'tutors.hourly_base_wage',
                'tutors.tutor_status',
                // コードマスタの名称（講師ステータス）
                'mst_codes.name as status_name',
                // 勤続期間の月数
                'enter_term_query.enter_term',
            )
            // 勤続期間の月数の取得
            ->leftJoinSub($enter_term_query, 'enter_term_query', function ($join) {
                $join->on('tutors.tutor_id', '=', 'enter_term_query.tutor_id');
            })
            // 講師学年マスタの名称を取得
            ->sdLeftJoin(MstTutorGrade::class, 'tutors.grade_cd', '=', 'mst_tutor_grades.grade_cd')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('tutors.tutor_status', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_29);
            })
            ->orderBy('tutors.tutor_id', 'asc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $tutors);
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
        $validator = Validator::make($request->all(), $this->rulesForSearch($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(検索用)
     *
     * @return array ルール
     */
    private function rulesForSearch(?Request $request)
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

        // 独自バリデーション: チェックボックス 講師ステータス
        $validationStatusList =  function ($attribute, $value, $fail) use ($request) {
            // 講師ステータスリスト
            $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_29);

            // 配列としてリクエストされた講師ステータスが、各ステータスコードと合致するように整形
            $group = [];
            foreach ($request->status_groups as $val) {
                $group[$val] = $val;
            }

            // ステータスコードとインデックスを合わせるため整形
            $statusGroup = [];
            foreach ($statusList as $statusList) {
                $statusGroup[$statusList->code] = $statusList->code;
            }

            foreach ($group as $val) {
                if (!isset($statusGroup[$val])) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 独自バリデーション: リストのチェック 講師学年
        $validationGradeList =  function ($attribute, $value, $fail) {
            // 講師学年リストを取得
            $gradeList = $this->mdlGetTutorGradeList();
            if (!isset($gradeList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        $rules += MstCampus::fieldRules('campus_cd', [$validationRoomList]);
        $rules += ['status_groups' => [$validationStatusList]];
        $rules += Tutor::fieldRules('tutor_id');
        $rules += Tutor::fieldRules('name');
        $rules += Tutor::fieldRules('grade_cd', [$validationGradeList]);
        $rules += Tutor::fieldRules('hourly_base_wage');

        return $rules;
    }

    //==========================
    // 講師情報詳細
    //==========================

    /**
     * 詳細画面
     *
     * @param int $tid 講師ID
     * @return view
     */
    public function detail($tid)
    {
        // IDのバリデーション
        $this->validateIds($tid);

        // 教室管理者の場合、自校舎の講師のみにガードを掛ける
        $this->guardRoomAdminTid($tid);

        // 講師情報を取得 FuncTutorDetailTrait
        $tutor = $this->getTutorDetail($tid);

        return view('pages.admin.tutor_mng-detail', $tutor);
    }

    //==========================
    // 給料明細
    //==========================

    /**
     * 一覧
     *
     * @param int $tid 講師ID
     * @return view
     */
    public function salary($tid)
    {
        // IDのバリデーション
        $this->validateIds($tid);

        // 教室管理者の場合、自校舎の講師のみにガードを掛ける
        $this->guardRoomAdminTid($tid);

        // 講師名を取得する
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
        // IDのバリデーション（講師No.）
        $this->validateIdsFromRequest($request, 'tid');

        // 教室管理者の場合、自校舎の講師のみにガードを掛ける
        $this->guardRoomAdminTid($request->input('tid'));

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
     * @param int $tid 講師No.
     * @param date $date 年月（YYYYMM）
     * @return view
     */
    public function detailSalary($tid, $date)
    {
        // IDのバリデーション
        $this->validateIds($tid, $date);

        // 教室管理者の場合、自校舎の講師のみにガードを掛ける
        $this->guardRoomAdminTid($tid);

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
     * @param int $tid 講師No.
     * @param date $date 年月（YYYYMM）
     * @return void
     */
    public function pdf($tid, $date)
    {
        // MEMO: 教室管理者でも全て見れるのでガードは不要

        // IDのバリデーション
        $this->validateIds($tid, $date);

        // 教室管理者の場合、自校舎の講師のみにガードを掛ける
        $this->guardRoomAdminTid($tid);

        // データの取得
        $dtlData = $this->getSalaryDetail($tid, $date);

        // 給与PDFの出力(管理画面でも使用するので共通化)
        $this->outputPdfSalary($dtlData);

        // 特になし
        return;
    }

    //==========================
    // 講師空き時間
    //==========================

    /**
     * 空き時間画面
     *
     * @param int $tid 講師ID
     * @return view
     */
    public function weeklyShift($tid)
    {
        // IDのバリデーション
        $this->validateIds($tid);

        // 教室管理者の場合、自校舎の講師のみにガードを掛ける
        $this->guardRoomAdminTid($tid);

        // 曜日の配列を取得 コードマスタより取得
        $weekdayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // 時限リストを取得（講師ID・時間割区分から）
        $periodList = $this->mdlGetPeriodListForTutor($tid, AppConst::CODE_MASTER_37_0);

        // 講師の空き時間を取得する（緑色表示用）
        // 曜日コード_時限No 例：['1_1', '2_1']
        $chkData = $this->fncWksfGetFreePeriod($tid);

        // レギュラー授業情報を取得する（黒色表示用）
        // 曜日コード_時限No 例：['1_1', '2_1']
        $regularData = $this->fncWksfGetRegularClass($tid);


        // 講師名を取得する
        $tutor_name = $this->mdlGetTeacherName($tid);

        return view('pages.admin.tutor_mng-weekly_shift', [
            'weekdayList' => $weekdayList,
            'periodList' => $periodList,
            'editData' => [
                'chkWs' => $chkData
            ],
            'exceptData' => $regularData,
            'tutor_name' => $tutor_name,
        ]);
    }

    //==========================
    // 講師カレンダー
    //==========================

    /**
     * カレンダー画面
     *
     * @param int $tid 講師ID
     * @return view
     */
    public function calendar($tid)
    {
        // IDのバリデーション
        $this->validateIds($tid);

        // 教室管理者の場合、自校舎の講師のみにガードを掛ける
        $this->guardRoomAdminTid($tid);

        // 講師名を取得する
        $tutor_name = $this->mdlGetTeacherName($tid);

        return view('pages.admin.tutor_mng-calendar', [
            'name' => $tutor_name,
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
     * @return array 講師ID
     */
    public function getCalendar(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'tid');

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForCalendar())->validate();

        $tid = $request->input('tid');

        // 教室管理者の場合、自校舎の講師のみにガードを掛ける
        $this->guardRoomAdminTid($tid);

        return $this->getTutorCalendar($request, $tid);
    }

    //==========================
    // 講師登録・編集
    //==========================
    /**
     * 授業教科プルダウンメニューのリストを取得
     * CtrlModelTraitの関数だとkeyByが配列処理の邪魔をするためここに定義
     */
    protected function getSubjectList()
    {
        $query = MstSubject::query();

        // プルダウンリストを取得する
        return $query->select('subject_cd as code', 'name as value')
            ->orderby('subject_cd')
            ->get();
    }

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        // 教室管理者の場合、新規登録画面は表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 学年設定年度の初期表示用データセット（システムマスタ「現年度」）
        $currentYear = MstSystem::select('value_num')
            ->where('key_id', AppConst::SYSTEM_KEY_ID_1)
            ->first();

        // 性別リストを取得
        $genderList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_30);
        // 学年リストを取得
        $gradeList = $this->mdlGetTutorGradeList();
        // 担当教科リストを取得
        $subjectGroup = $this->getSubjectList();

        // 学校検索モーダル用のデータ渡し
        // 学校種リストを取得
        $schoolKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_49);
        // 設置区分リストを取得
        $establishKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_50);

        return view('pages.admin.tutor_mng-input', [
            'rules' => $this->rulesForInput(null),
            'genderList' => $genderList,
            'gradeList' => $gradeList,
            'subjectGroup' => $subjectGroup,
            'editData' => [
                'grade_year' => $currentYear->value_num
            ],
            'editDataSubject' => null,
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
        // 教室管理者の場合、新規登録は行なわない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // 保存する項目のみに絞る
        $form = $request->only(
            'name',
            'name_kana',
            'tel',
            'email',
            'address',
            'birth_date',
            'gender_cd',
            'grade_cd',
            'grade_year',
            'school_cd_j',
            'school_cd_h',
            'school_cd_u',
            'hourly_base_wage',
            'enter_date',
            'memo',
        );

        // 以下項目を追加 ステータス「在籍」
        $form += [
            'tutor_status' => AppConst::CODE_MASTER_29_1
        ];

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request, $form) {
            //-------------------------
            // 講師情報の登録
            //-------------------------
            $tutor = new Tutor;
            // 保存
            $tutor->fill($form)->save();

            //-------------------------
            // 担当教科情報の登録
            //-------------------------
            // 教科選択は必須ではないので、チェックがある場合のみ保存処理を行う
            if (!empty($request['subject_groups'])) {
                // 選択した教科を配列にする
                $subjects = explode(",", $request['subject_groups']);

                foreach ($subjects as $subject) {
                    // 選択した分データ作成
                    $tutorSubject = new TutorSubject;
                    $tutorSubject->tutor_id = $tutor->tutor_id;
                    $tutorSubject->subject_cd = $subject;
                    // 保存
                    $tutorSubject->save();
                }
            }

            //-------------------------
            // アカウント情報の登録
            //-------------------------
            $account = new Account;
            $account->account_id = $tutor->tutor_id;
            // アカウント種類：講師
            $account->account_type = AppConst::CODE_MASTER_7_2;
            $account->email = $tutor->email;
            // 仮パスワードはハッシュ化したメールアドレスとする
            $account->password = Hash::make($tutor->email);
            // パスワードリセット：不要
            $account->password_reset = AppConst::ACCOUNT_PWRESET_0;
            // プラン種類：通常
            $account->plan_type = AppConst::CODE_MASTER_10_0;
            // ログイン可否：可
            $account->login_flg = AppConst::CODE_MASTER_9_1;
            // 保存
            $account->save();

            //-------------------------
            // メール送信
            //-------------------------
            // マイページ案内のメール送信 テンプレートは別ファイル
            Mail::to($tutor->email)->send(new MypageGuideToTutor());
        });

        return;
    }

    /**
     * 編集画面
     *
     * @param int $tid 講師ID
     * @return void
     */
    public function edit($tid)
    {
        // IDのバリデーション
        $this->validateIds($tid);

        // 教室管理者の場合、自校舎の講師のみにガードを掛ける
        $this->guardRoomAdminTid($tid);

        // 講師情報を取得する
        $query = Tutor::query();
        $tutor = $query
            ->select(
                'tutors.tutor_id',
                'tutors.name',
                'tutors.name_kana',
                'tutors.tel',
                'tutors.email',
                'tutors.address',
                'tutors.birth_date',
                'tutors.gender_cd',
                'tutors.grade_cd',
                'tutors.grade_year',
                'tutors.school_cd_j',
                'tutors.school_cd_h',
                'tutors.school_cd_u',
                // 画面表示用に、学校名はtext_xxxのように指定する
                'mst_schools_j.name as text_school_cd_j',
                'mst_schools_h.name as text_school_cd_h',
                'mst_schools_u.name as text_school_cd_u',
                'tutors.hourly_base_wage',
                'tutors.tutor_status',
                'tutors.enter_date',
                'tutors.leave_date',
                'tutors.memo',
            )
            // 出身学校（中）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'tutors.school_cd_j', '=', 'mst_schools_j.school_cd', 'mst_schools_j')
            // 出身学校（高）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'tutors.school_cd_h', '=', 'mst_schools_h.school_cd', 'mst_schools_h')
            // 所属学校（大）の学校名の取得
            ->sdLeftJoin(MstSchool::class, 'tutors.school_cd_u', '=', 'mst_schools_u.school_cd', 'mst_schools_u')
            ->where('tutor_id', '=', $tid)
            ->firstOrFail();

        // 講師担当教科を取得する
        $query = TutorSubject::query();
        $tutorSubject = $query
            ->select('subject_cd')
            ->where('tutor_id', '=', $tid)
            ->get();

        // 取得した教科コードを配列で渡す：['01','02']
        $editDataSubject = [];
        foreach ($tutorSubject as $subject) {
            // 配列に追加
            array_push($editDataSubject, $subject->subject_cd);
        }

        // 性別リストを取得
        $genderList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_30);
        // 学年リストを取得
        $gradeList = $this->mdlGetTutorGradeList();
        // 担当教科リストを取得
        $subjectGroup = $this->getSubjectList();
        // 講師ステータスリストを取得
        $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_29);

        // 学校検索モーダル用のデータ渡し
        // 学校種リストを取得
        $schoolKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_49);
        // 設置区分リストを取得
        $establishKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_50);

        return view('pages.admin.tutor_mng-input', [
            'rules' => $this->rulesForInput(null),
            'genderList' => $genderList,
            'gradeList' => $gradeList,
            'subjectGroup' => $subjectGroup,
            'statusList' => $statusList,
            'editData' => $tutor,
            'editDataSubject' => [
                'subject_groups' => $editDataSubject
            ],
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
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput($request))->validate();

        // 全会員ステータス 共通保存項目
        $form = $request->only(
            'name',
            'name_kana',
            'tel',
            'email',
            'address',
            'birth_date',
            'gender_cd',
            'grade_cd',
            'grade_year',
            'school_cd_j',
            'school_cd_h',
            'school_cd_u',
            'hourly_base_wage',
            'tutor_status',
            'enter_date',
            'leave_date',
            'memo',
        );

        // ステータス「在籍」の場合は退職日をクリアする(NULL)
        if ($request['tutor_status'] == AppConst::CODE_MASTER_29_1) {
            $form['leave_date'] = NULL;
        }

        DB::transaction(function () use ($request, $form) {

            // 既存の講師情報を取得
            $tutor = Tutor::where('tutor_id', $request['tutor_id'])
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            // 既存のアカウント情報を取得
            $account = Account::where('account_id', $request['tutor_id'])
                ->where('account_type', AppConst::CODE_MASTER_7_2)
                ->firstOrFail();

            //-------------------------
            // 担当教科情報の登録
            //-------------------------
            // forceDelete・insertとする
            // 既存データ削除
            TutorSubject::where('tutor_id', $request['tutor_id'])
                ->forceDelete();

            // 教科選択がある場合のみ保存処理を行う
            if (!empty($request['subject_groups'])) {
                // 選択した教科を配列にする
                $subjects = explode(",", $request['subject_groups']);

                foreach ($subjects as $subject) {
                    // 選択した分データ作成
                    $tutorSubject = new TutorSubject;
                    $tutorSubject->tutor_id = $tutor->tutor_id;
                    $tutorSubject->subject_cd = $subject;
                    // 保存
                    $tutorSubject->save();
                }
            }
            //-------------------------
            // アカウント情報の更新 メールアドレス・ログイン可否の変更
            //-------------------------
            // 1.メールアドレスが変更された時
            // 2.ステータス「退職済」から「在籍」に変更した時
            // のいずれかの場合に行う。

            if ($account->email != $request['email']) {
                // メールアドレス更新
                $account->email = $request['email'];
                // 保存
                $account->save();
            }

            if ($tutor->tutor_status == AppConst::CODE_MASTER_29_3 && $request['tutor_status'] == AppConst::CODE_MASTER_29_1) {
                // ログイン可否：可
                $account->login_flg = AppConst::CODE_MASTER_9_1;
                // 保存
                $account->save();

                //-------------------------
                // メール送信
                //-------------------------
                // ステータス「退職済」→「在籍」に変更した場合、再入会マイページ案内のメールを送信する
                Mail::to($request['email'])->send(new MypageGuideRejoinToTutor());
            }

            //-------------------------
            // 講師情報の更新
            //-------------------------
            $tutor->fill($form)->save();
        });

        return;
    }

    /**
     * バリデーション(登録用)（講師登録）
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
     * バリデーションルールを取得(登録用)（講師登録）
     *
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: チェックボックス 担当教科
        $validationSubjectGroup =  function ($attribute, $value, $fail) use ($request) {
            // requestされた教科コードを配列にする
            $inputSubjectGroup = explode(",", $request->subject_groups);
            // 全教科リスト
            $AllSubject = $this->getSubjectList();

            // 配列にしたグループの整形
            $group = [];
            foreach ($inputSubjectGroup as $val) {
                $group[$val] = $val;
            }

            // 教科コードとインデックスを合わせるため整形
            $subjectGroup = [];
            foreach ($AllSubject as $subject) {
                $subjectGroup[$subject->code] = $subject->code;
            }

            foreach ($group as $val) {
                if (!isset($subjectGroup[$val])) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 独自バリデーション: リストのチェック 性別
        $validationGenderList =  function ($attribute, $value, $fail) {
            // 性別リストを取得
            $genderList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_30);
            if (!isset($genderList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 学年
        $validationGradeList =  function ($attribute, $value, $fail) {
            // 学年リストを取得
            $grades = $this->mdlGetTutorGradeList(false);
            if (!isset($grades[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 講師ステータス
        $validationStatusList =  function ($attribute, $value, $fail) {
            // 会員ステータスリストを取得
            $statusList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_29);
            if (!isset($statusList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: メールアドレス重複チェック
        $validationEmail = function ($attribute, $value, $fail) use ($request) {
            if (!$request) {
                return;
            }

            // 対象データを取得
            $email = Account::where('email', $request['email']);

            // 変更時は自分のキー以外を検索
            if (filled($request['tutor_id'])) {
                $email->where('account_id', '!=', $request['tutor_id']);
            }

            $exists = $email->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_email'));
            }
        };

        // 独自バリデーション: 会員ステータス「退職処理中」への更新は不可（退職登録画面を経由させる）
        $validationStatusChangeLeaveProspect = function ($attribute, $value, $fail) use ($request) {
            // 対象データを取得（編集中のデータ）
            $tutor = Tutor::where('tutor_id', $request['tutor_id'])
                ->first();

            // 編集前のデータが「退職処理中」の場合はチェックしない
            if (isset($tutor) && $tutor->tutor_status == AppConst::CODE_MASTER_29_2) {
                return;
            }

            // リクエストデータが「退職処理中」を選択していたらエラー
            if ($request['tutor_status'] == AppConst::CODE_MASTER_29_2) {
                return $fail(Lang::get('validation.status_cannot_change_retirement'));
            }
        };

        // 独自バリデーション: 会員ステータス「退職済」への更新は不可（退職登録画面を経由させる）
        $validationStatusChangeLeaveExecution = function ($attribute, $value, $fail) use ($request) {
            // 対象データを取得（編集中のデータ）
            $tutor = Tutor::where('tutor_id', $request['tutor_id'])
                ->first();

            // 編集前のデータが「退職済」の場合はチェックしない
            if (isset($tutor) && $tutor->tutor_status == AppConst::CODE_MASTER_29_3) {
                return;
            }

            // リクエストデータが「退職済」を選択していたらエラー
            if ($request['tutor_status'] == AppConst::CODE_MASTER_29_3) {
                return $fail(Lang::get('validation.status_cannot_change_retirement'));
            }
        };

        // 独自バリデーション: 会員ステータス「退職処理中」の場合、退職日はシステム日付より未来日
        $validationLeaveDateProspect = function ($attribute, $value, $fail) use ($request) {
            // 退職日の数値が現在日時の数値を下回っていないかチェック
            if (strtotime($request['leave_date']) < strtotime('now')) {
                // 下回っていた（未来日でない）場合エラー
                return $fail(Lang::get('validation.after_tomorrow'));
            }
        };

        // 独自バリデーション: 会員ステータス「退職済」の場合、退職日はシステム日付以前（当日含む）
        $validationLeaveDateExecution = function ($attribute, $value, $fail) use ($request) {
            // 現在日時の数値が退職日の数値を下回っていないかチェック
            if (strtotime('now') <= strtotime($request['leave_date'])) {
                // 下回っていた（システム日付以前でない）場合エラー
                return $fail(Lang::get('validation.before_or_equal_today',));
            }
        };

        // 全ステータス共通バリデーション
        // 必須：名前、名前かな、電話番号、メールアドレス、生年月日、性別、学年、学年設定年度、授業時給（ベース給）
        $rules += Tutor::fieldRules('name', ['required']);
        $rules += Tutor::fieldRules('name_kana', ['required']);
        $rules += Tutor::fieldRules('tel', ['required']);
        $rules += Tutor::fieldRules('email', ['required', $validationEmail]);
        $rules += Tutor::fieldRules('birth_date', ['required']);
        $rules += Tutor::fieldRules('gender_cd', ['required', $validationGenderList]);
        $rules += Tutor::fieldRules('grade_cd', ['required', $validationGradeList]);
        $rules += Tutor::fieldRules('grade_year', ['required']);
        $rules += Tutor::fieldRules('hourly_base_wage', ['required']);
        // 担当教科のバリデーション
        $rules += ['subject_groups' => [$validationSubjectGroup]];

        // 編集画面の場合
        // 必須：講師ステータス
        // ステータス「退職処理中」以外 → 退職処理中 への更新は不可（退職登録画面を経由させる）
        // ステータス「退職済」以外 → 退職済 への更新は不可（退職登録画面・日次バッチを経由させる）
        if ($request && isset($request['tutor_status'])) {
            $rules += Tutor::fieldRules('tutor_status', ['required', $validationStatusList, $validationStatusChangeLeaveProspect, $validationStatusChangeLeaveExecution]);
        }

        // ステータス「退職処理中」の場合
        // 必須：退職日
        // 退職日はシステム日付より未来日とする（システム日付＜退職日）
        if ($request && $request['tutor_status'] == AppConst::CODE_MASTER_29_2) {
            $rules += Tutor::fieldRules('leave_date', ['required', $validationLeaveDateProspect]);
        }

        // ステータス「退職済」の場合
        // 必須：退職日
        // 退職日はシステム日付以前とする（退職日＜＝システム日付）
        if ($request && $request['tutor_status'] == AppConst::CODE_MASTER_29_3) {
            $rules += Tutor::fieldRules('leave_date', ['required', $validationLeaveDateExecution]);
        }

        return $rules;
    }

    //==========================
    // 退職処理
    //==========================
    /**
     * 退職登録画面
     * @param int $tid 講師ID
     * @return view
     */
    public function leaveEdit($tid)
    {
        // IDのバリデーション
        $this->validateIds($tid);

        // 教室管理者の場合、自分の校舎コードの講師のみにガードを掛ける
        $this->guardRoomAdminTid($tid);

        // 講師名の取得
        $tname = $this->mdlGetTeacherName($tid);

        // 講師ステータス「在籍」以外の場合はエラーを表示する
        $tutor = Tutor::where('tutor_id', $tid)
            ->first();

        if ($tutor->tutor_status != AppConst::CODE_MASTER_29_1) {
            return $this->illegalResponseErr();
        }

        return view('pages.admin.tutor_mng-leave', [
            'tname' => $tname,
            'editData' => [
                'tutor_id' => $tid
            ],
            'rules' => $this->leaveRulesForInput(null)
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
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->leaveRulesForInput($request))->validate();

        // 必要項目を取得
        $form = $request->only(
            'leave_date',
        );

        // 講師ステータスを「退職処理中」にする
        $form += [
            'tutor_status' => AppConst::CODE_MASTER_29_2
        ];

        // 対象データを取得(IDでユニークに取る)
        $tutor = Tutor::where('tutor_id', $request->tutor_id)
            // 教室管理者の場合、自分の校舎の講師のみにガードを掛ける
            ->where($this->guardRoomAdminTableWithTid())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $tutor->fill($form)->save();

        return;
    }

    /**
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function leaveValidationForInput(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->leaveRulesForInput($request));
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function leaveRulesForInput(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: 退職日はシステム日付より未来日
        $validationLeaveDate = function ($attribute, $value, $fail) use ($request) {
            // 退職日の数値が現在日時の数値を下回っていないかチェック
            if (strtotime($request['leave_date']) < strtotime('now')) {
                // 下回っていた（未来日でない）場合エラー
                return $fail(Lang::get('validation.after_tomorrow'));
            }
        };

        $rules += Tutor::fieldRules('leave_date', ['required', $validationLeaveDate]);

        return $rules;
    }

    //==========================
    // 所属登録・編集
    //==========================

    /**
     * 登録画面
     * @param int $tid 講師ID
     * @return view
     */
    public function campusNew($tid)
    {
        // 教室管理者の場合、新規登録画面は表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // IDのバリデーション
        $this->validateIds($tid);

        // 既に3つ所属校舎がある場合は登録画面を表示しない
        $tutorCampus = TutorCampus::where('tutor_id', $tid)
            ->get();

        if (3 <= count($tutorCampus)) {
            return $this->illegalResponseErr();
        }

        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        return view('pages.admin.tutor_mng-campus-input', [
            'rooms' => $rooms,
            'editData' => [
                'tutor_id' => $tid
            ],
            'rules' => $this->campusRulesForInput(null)
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
        // 教室管理者の場合、新規登録は行なわない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->campusRulesForInput($request))->validate();

        $form = $request->only(
            'tutor_id',
            'campus_cd',
            'travel_cost',
        );

        $tutorCampus = new TutorCampus;
        // 登録
        $tutorCampus->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int $tutorCampusId 講師所属校舎ID
     * @return view
     */
    public function campusEdit($tutorCampusId)
    {
        // IDのバリデーション
        $this->validateIds($tutorCampusId);

        // 講師所属情報を取得
        $tutorCampus = TutorCampus::where('tutor_campus_id', $tutorCampusId)
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 校舎リストを取得
        $rooms = $this->mdlGetRoomList(false);

        return view('pages.admin.tutor_mng-campus-input', [
            'rooms' => $rooms,
            'editData' => $tutorCampus,
            'rules' => $this->campusRulesForInput(null)
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
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->campusRulesForInput($request))->validate();

        $form = $request->only(
            'campus_cd',
            'travel_cost',
        );

        // 対象データを取得(IDでユニークに取る)
        $tutorCampus = TutorCampus::where('tutor_campus_id', $request->tutor_campus_id)
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 更新
        $tutorCampus->fill($form)->save();

        return;
    }

    /**
     * 削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function campusDelete(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'tutor_campus_id');

        // 対象データを取得(IDでユニークに取る)
        $tutorCampus = TutorCampus::where('tutor_campus_id', $request->tutor_campus_id)
            // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
            ->where($this->guardRoomAdminTableWithRoomCd())
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 削除
        $tutorCampus->delete();

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
        $validator = Validator::make($request->all(), $this->campusRulesForInput($request));
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

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {
            // 校舎リストを取得
            $rooms = $this->mdlGetRoomList(false);
            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 所属校舎重複チェック
        $validationDupCampus = function ($attribute, $value, $fail) use ($request) {
            if (!$request) {
                return;
            }

            // 講師所属校舎を取得
            $tutorCampus = TutorCampus::where('tutor_id', $request['tutor_id'])
                ->where('campus_cd', $request['campus_cd']);

            // 変更時は自分のキー以外を検索
            if (filled($request['tutor_campus_id'])) {
                $tutorCampus->where('tutor_campus_id', '!=', $request['tutor_campus_id']);
            }

            $exists = $tutorCampus->exists();

            if ($exists) {
                // 登録済みエラー
                return $fail(Lang::get('validation.duplicate_data'));
            }
        };

        $rules += TutorCampus::fieldRules('campus_cd', ['required', $validationRoomList, $validationDupCampus]);
        $rules += TutorCampus::fieldRules('travel_cost', ['required']);

        return $rules;
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
        // 検索結果を取得
        $schoolList = $this->getSchoolList($request);

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
}
