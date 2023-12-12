<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//===============================================
// リダイレクト
//===============================================

use App\Http\Controllers\HomeController;

// 権限によってリダイレクト
// ここではログインユーザが取れずコントローラで制御した
// Route::redirect('/', '/member_mng');
Route::get('/', [HomeController::class, 'index'])->name('home');

//===============================================
// 認証機能
//===============================================

// 会員登録は無効
Auth::routes(['register' => false]);

//===============================================
// マイページ共通
//===============================================

use App\Http\Controllers\MypageCommon\NoticeController;
use App\Http\Controllers\MypageCommon\CalendarController;
use App\Http\Controllers\MypageCommon\PasswordChangeController;

Route::group(['middleware' => ['auth', 'can:mypage-common']], function () {

    //---------------------
    // お知らせ
    //---------------------

    // 一覧
    Route::get('/notice', [NoticeController::class, 'index'])->name('notice');

    // 検索結果取得
    Route::post('/notice/search', [NoticeController::class, 'search'])->name('notice-search');

    // 詳細取得用
    Route::post('/notice/get_data', [NoticeController::class, 'getData'])->name('notice-get_data');

    //---------------------
    // カレンダー
    //---------------------

    // カレンダー画面表示
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');

    // 詳細取得用
    Route::post('/calendar/get_calendar', [CalendarController::class, 'getCalendar'])->name('calendar-get_calendar');

    //---------------------
    // パスワード変更
    //---------------------

    // 新規登録画面
    Route::get('/password_change', [PasswordChangeController::class, 'index'])->name('password_change');

    // 編集処理
    Route::post('/password_change/update', [PasswordChangeController::class, 'update'])->name('password_change-update');

    // バリデーション(登録用)
    Route::post('/password_change/vd_input', [PasswordChangeController::class, 'validationForInput'])->name('password_change-vd_input');
});

//===============================================
// 生徒向け
//===============================================

use App\Http\Controllers\Student\ContactController;
use App\Http\Controllers\Student\TransferStudentController;
use App\Http\Controllers\Student\ExtraLessonController;
use App\Http\Controllers\Student\GradesController;
use App\Http\Controllers\Student\ConferenceController;
use App\Http\Controllers\Student\SeasonStudentController;
use App\Http\Controllers\Student\AbsentController;
use App\Http\Controllers\Student\AgreementController;
use App\Http\Controllers\Student\InvoiceController;

Route::group(['middleware' => ['auth', 'can:student']], function () {

    //---------------------
    // 問い合わせ
    //---------------------

    // 一覧
    Route::get('/contact', [ContactController::class, 'index'])->name('contact');

    // 検索結果取得
    Route::post('/contact/search', [ContactController::class, 'search'])->name('contact-search');

    // 詳細取得用
    Route::post('/contact/get_data', [ContactController::class, 'getData'])->name('contact-get_data');

    // 問い合わせ登録
    Route::get('/contact/new', [ContactController::class, 'new'])->name('contact-new');

    // 新規登録処理
    Route::post('/contact/create', [ContactController::class, 'create'])->name('contact-create');

    // バリデーション(登録用)
    Route::post('/contact/vd_input', [ContactController::class, 'validationForInput'])->name('contact-vd_input');

    //---------------------
    // 振替調整
    //---------------------

    // 一覧
    Route::get('/transfer_student', [TransferStudentController::class, 'index'])->name('transfer_student');

    // 検索結果取得
    Route::post('/transfer_student/search', [TransferStudentController::class, 'search'])->name('transfer_student-search');

    // 詳細取得用
    Route::post('/transfer_student/get_data', [TransferStudentController::class, 'getData'])->name('transfer_student-get_data');

    // 振替希望日登録
    Route::get('/transfer_student/new', [TransferStudentController::class, 'new'])->name('transfer_student-new');

    // 振替希望日プルダウンを選択された際に授業情報を返却する
    Route::post('/transfer_student/get_data_select_schedule', [TransferStudentController::class, 'getDataSelectSchedule'])->name('transfer_student-get_data_select_schedule');

    // 振替希望日フリー入力した際に時限情報を返却する
    Route::post('/transfer_student/get_data_select_calender', [TransferStudentController::class, 'getDataSelectCalender'])->name('transfer_student-get_data_select_calender');

    // 新規登録処理
    Route::post('/transfer_student/create', [TransferStudentController::class, 'create'])->name('transfer_student-create');

    // 振替日承認
    Route::get('/transfer_student/edit/{transferId}', [TransferStudentController::class, 'edit'])->name('transfer_student-edit');

    // 編集処理
    Route::post('/transfer_student/update', [TransferStudentController::class, 'update'])->name('transfer_student-update');

    // バリデーション(登録用)
    Route::post('/transfer_student/vd_input', [TransferStudentController::class, 'validationForInput'])->name('transfer_student-vd_input');

    // バリデーション(承認用)
    Route::post('/transfer_student/vd_approval', [TransferStudentController::class, 'validationForApproval'])->name('transfer_student-vd_approval');

    //---------------------
    // 追加授業依頼 モック
    //---------------------

    // 申請
    Route::get('/extra_lesson', [ExtraLessonController::class, 'index'])->name('extra_lesson');

    // 新規登録処理
    Route::post('/extra_lesson/create', [ExtraLessonController::class, 'create'])->name('extra_lesson-create');

    // バリデーション(登録用)
    Route::post('/extra_lesson/vd_input', [ExtraLessonController::class, 'validationForInput'])->name('extra_lesson-vd_input');

    //---------------------
    // 生徒成績
    //---------------------
    // 一覧
    Route::get('/grades', [GradesController::class, 'index'])->name('grades');

    // 検索結果取得
    Route::post('/grades/search', [GradesController::class, 'search'])->name('grades-search');

    // 詳細取得用
    Route::post('/grades/get_data', [GradesController::class, 'getData'])->name('grades-get_data');

    // 試験種別が選択された際に成績表示欄数を返却する
    Route::post('/grades/get_data_select', [GradesController::class, 'getDataSelect'])->name('grades-get_data_select');

    // 生徒成績登録
    Route::get('/grades/new', [GradesController::class, 'new'])->name('grades-new');

    // 新規登録処理
    Route::post('/grades/create', [GradesController::class, 'create'])->name('grades-create');

    // 生徒成績編集
    Route::get('/grades/edit/{gradesId}', [GradesController::class, 'edit'])->name('grades-edit');

    // 編集処理
    Route::post('/grades/update', [GradesController::class, 'update'])->name('grades-update');

    // バリデーション(登録用)
    Route::post('/grades/vd_input', [GradesController::class, 'validationForInput'])->name('grades-vd_input');

    // 削除処理
    Route::post('/grades/delete', [GradesController::class, 'delete'])->name('grades-delete');

    //---------------------
    // 面談日程調整
    //---------------------

    // 申請
    Route::get('/conference', [ConferenceController::class, 'index'])->name('conference');

    // 申請(直接ID付きで選択された状態にする)
    Route::get('/conference/{scheduleId}', [ConferenceController::class, 'direct'])->name('conference-direct');

    // 授業日時プルダウンを選択された際に教室・講師の情報を返却する →不要？

    // 新規登録処理
    Route::post('/conference/create', [ConferenceController::class, 'create'])->name('conference-create');

    // バリデーション(登録用)
    Route::post('/conference/vd_input', [ConferenceController::class, 'validationForInput'])->name('conference-vd_input');

    //---------------------
    // 特別期間講習日程連絡
    //---------------------

    // 日程連絡一覧
    Route::get('/season_student', [SeasonStudentController::class, 'index'])->name('season_student');

    // 検索結果取得
    Route::post('/season_student/search', [SeasonStudentController::class, 'search'])->name('season_student-search');

    // 提出スケジュール詳細
    Route::get('/season_student/detail/{seasonStudentId}', [SeasonStudentController::class, 'detail'])->name('season_student-detail');

    // 日程登録画面
    Route::get('/season_student/edit/{seasonStudentId}', [SeasonStudentController::class, 'edit'])->name('season_student-edit');

    // 新規登録処理
    Route::post('/season_student/update', [SeasonStudentController::class, 'update'])->name('season_student-update');

    // バリデーション(登録用)
    Route::post('/season_student/vd_input', [SeasonStudentController::class, 'validationForInput'])->name('season_student-vd_input');

    //---------------------
    // 欠席申請
    //---------------------

    // 申請
    Route::get('/absent', [AbsentController::class, 'index'])->name('absent');

    // 申請(直接ID付きで選択された状態にする)
    Route::get('/absent/{scheduleId}', [AbsentController::class, 'direct'])->name('absent-direct');

    // 授業日時プルダウンを選択された際に教室・講師の情報を返却する
    Route::post('/absent/get_data_select', [AbsentController::class, 'getDataSelect'])->name('absent-get_data_select');

    // 新規登録処理
    Route::post('/absent/create', [AbsentController::class, 'create'])->name('absent-create');

    // バリデーション(登録用)
    Route::post('/absent/vd_input', [AbsentController::class, 'validationForInput'])->name('absent-vd_input');

    //---------------------
    // 生徒情報
    //---------------------

    // 一覧
    Route::get('/agreement', [AgreementController::class, 'index'])->name('agreement');

    // 詳細取得用
    Route::post('/agreement/get_data', [AgreementController::class, 'getData'])->name('agreement-get_data');

    //---------------------
    // 請求情報
    //---------------------

    // 一覧
    Route::get('/invoice', [InvoiceController::class, 'index'])->name('invoice');

    // 検索結果取得
    Route::post('/invoice/search', [InvoiceController::class, 'search'])->name('invoice-search');

    // 詳細画面
    Route::get('/invoice/detail/{date}', [InvoiceController::class, 'detail'])->name('invoice-detail');

    // PDF出力
    Route::get('/invoice/pdf/{date}', [InvoiceController::class, 'pdf'])->name('invoice-pdf');
});

//===============================================
// 講師向け(共通)
//===============================================

use App\Http\Controllers\Tutor\ReportRegistController;
use App\Http\Controllers\Tutor\WeeklyShiftController;
use App\Http\Controllers\Tutor\TransferTutorController;
use App\Http\Controllers\Tutor\GradesCheckController;
use App\Http\Controllers\Tutor\SeasonTutorController;
use App\Http\Controllers\Tutor\SurchargeController;
use App\Http\Controllers\Tutor\SalaryController;
use App\Http\Controllers\Tutor\TrainingController;

Route::group(['middleware' => ['auth', 'can:tutor']], function () {

    //---------------------
    // 授業報告書
    //---------------------

    // 一覧
    Route::get('/report_regist', [ReportRegistController::class, 'index'])->name('report_regist');

    // 詳細取得用
    Route::post('/report_regist/get_data', [ReportRegistController::class, 'getData'])->name('report_regist-get_data');

    // 検索結果取得
    Route::post('/report_regist/search', [ReportRegistController::class, 'search'])->name('report_regist-search');

    // バリデーション(検索用)
    Route::post('/report_regist/vd_search', [ReportRegistController::class, 'validationForSearch'])->name('report_regist-vd_search');

    // 教室選択プルダウンを選択された際に生徒プルダウンの情報を返却する
    Route::post('/report_regist/get_data_select_search', [ReportRegistController::class, 'getDataSelectSearch'])->name('report_regist-get_data_select_search');

    // 授業リストを選択された際に授業情報・教材リストを返却する
    Route::post('/report_regist/get_data_select', [ReportRegistController::class, 'getDataSelect'])->name('report_regist-get_data_select');

    // 教材リストを選択された際に単元分類リストを返却する
    Route::post('/report_regist/get_data_select_text', [ReportRegistController::class, 'getDataSelectText'])->name('report_regist-get_data_select_text');

    // 単元分類リストを選択された際に単元リストを返却する
    Route::post('/report_regist/get_data_select_category', [ReportRegistController::class, 'getDataSelectCategory'])->name('report_regist-get_data_select_category');

    // 授業報告書登録
    Route::get('/report_regist/new', [ReportRegistController::class, 'new'])->name('report_regist-new');

    // 新規登録処理
    Route::post('/report_regist/create', [ReportRegistController::class, 'create'])->name('report_regist-create');

    // 授業報告書編集
    Route::get('/report_regist/edit/{reportId}', [ReportRegistController::class, 'edit'])->name('report_regist-edit');

    // 編集処理
    Route::post('/report_regist/update', [ReportRegistController::class, 'update'])->name('report_regist-update');

    // バリデーション(登録用)
    Route::post('/report_regist/vd_input', [ReportRegistController::class, 'validationForInput'])->name('report_regist-vd_input');

    // 削除処理
    Route::post('/report_regist/delete', [ReportRegistController::class, 'delete'])->name('report_regist-delete');

    //---------------------
    // 空き時間
    //---------------------

    // 登録
    Route::get('/weekly_shift', [WeeklyShiftController::class, 'index'])->name('weekly_shift');

    // 編集処理
    Route::post('/weekly_shift/update', [WeeklyShiftController::class, 'update'])->name('weekly_shift-update');

    // バリデーション(登録用)
    Route::post('/weekly_shift/vd_input', [WeeklyShiftController::class, 'validationForInput'])->name('weekly_shift-vd_input');

    //---------------------
    // 振替調整
    //---------------------

    // 一覧
    Route::get('/transfer_tutor', [TransferTutorController::class, 'index'])->name('transfer_tutor');

    // 検索結果取得
    Route::post('/transfer_tutor/search', [TransferTutorController::class, 'search'])->name('transfer_tutor-search');

    // バリデーション(検索用)
    Route::post('/transfer_tutor/vd_search', [TransferTutorController::class, 'validationForSearch'])->name('transfer_tutor-vd_search');

    // 詳細取得用
    Route::post('/transfer_tutor/get_data', [TransferTutorController::class, 'getData'])->name('transfer_tutor-get_data');

    // 校舎選択プルダウンを選択された際に生徒プルダウンの情報を返却する
    Route::post('/transfer_tutor/get_data_select_search', [TransferTutorController::class, 'getDataSelectSearch'])->name('transfer_tutor-get_data_select_search');

    // 振替希望日登録
    Route::get('/transfer_tutor/new', [TransferTutorController::class, 'new'])->name('transfer_tutor-new');

    // 新規登録処理
    Route::post('/transfer_tutor/create', [TransferTutorController::class, 'create'])->name('transfer_tutor-create');

    // 生徒選択プルダウンを選択された際に授業日・時限プルダウンの情報を返却する
    Route::post('/transfer_tutor/get_data_select_student', [TransferTutorController::class, 'getDataSelectStudentSchedule'])->name('transfer_tutor-get_data_select_student');

    // 授業日・時限選択プルダウンを選択された際に授業情報を返却する
    Route::post('/transfer_tutor/get_data_select_schedule', [TransferTutorController::class, 'getDataSelectSchedule'])->name('transfer_tutor-get_data_select_schedule');

    // 振替希望日カレンダー入力した際に時限情報を返却する
    Route::post('/transfer_tutor/get_data_select_calender', [TransferTutorController::class, 'getDataSelectCalender'])->name('transfer_tutor-get_data_select_calender');

    // 振替日承認
    Route::get('/transfer_tutor/edit/{transferId}', [TransferTutorController::class, 'edit'])->name('transfer_tutor-edit');

    // 編集処理
    Route::post('/transfer_tutor/update', [TransferTutorController::class, 'update'])->name('transfer_tutor-update');

    // バリデーション(登録用)
    Route::post('/transfer_tutor/vd_input', [TransferTutorController::class, 'validationForInput'])->name('transfer_tutor-vd_input');

    // バリデーション(承認用)
    Route::post('/transfer_tutor/vd_approval', [TransferTutorController::class, 'validationForApproval'])->name('transfer_tutor-vd_approval');

    //---------------------
    // 生徒成績
    //---------------------

    // 一覧
    Route::get('/grades_check', [GradesCheckController::class, 'index'])->name('grades_check');

    // 詳細取得用
    Route::post('/grades_check/get_data', [GradesCheckController::class, 'getData'])->name('grades_check-get_data');

    // バリデーション(検索用)
    Route::post('/grades_check/vd_search', [GradesCheckController::class, 'validationForSearch'])->name('grades_check-vd_search');

    // 検索結果取得
    Route::post('/grades_check/search', [GradesCheckController::class, 'search'])->name('grades_check-search');

    // 教室選択プルダウンを選択された際に生徒プルダウンの情報を返却する
    Route::post('/grades_check/get_data_select', [GradesCheckController::class, 'getDataSelect'])->name('grades_check-get_data_select');

    //---------------------
    // 特別期間講習日程連絡
    //---------------------

    // 日程連絡一覧
    Route::get('/season_tutor', [SeasonTutorController::class, 'index'])->name('season_tutor');

    // 検索結果取得
    Route::post('/season_tutor/search', [SeasonTutorController::class, 'search'])->name('season_tutor-search');

    // 提出スケジュール詳細
    Route::get('/season_tutor/detail/{seasonTutorId}', [SeasonTutorController::class, 'detail'])->name('season_tutor-detail');

    // 日程登録画面
    Route::get('/season_tutor/new/{seasonCd}', [SeasonTutorController::class, 'new'])->name('season_tutor-new');

    // 新規登録処理
    Route::post('/season_tutor/create', [SeasonTutorController::class, 'create'])->name('season_tutor-create');

    // バリデーション(登録用)
    Route::post('/season_tutor/vd_input', [SeasonTutorController::class, 'validationForInput'])->name('season_tutor-vd_input');

    //---------------------
    // 追加請求申請 モック
    //---------------------

    // 一覧
    Route::get('/surcharge', [SurchargeController::class, 'index'])->name('surcharge');

    // 検索結果取得
    Route::post('/surcharge/search', [SurchargeController::class, 'search'])->name('surcharge-search');

    // 詳細取得用
    Route::post('/surcharge/get_data', [SurchargeController::class, 'getData'])->name('surcharge-get_data');

    // 請求種別プルダウン選択時にサブコードを返却する
    Route::post('/surcharge/get_data_select', [SurchargeController::class, 'getDataSelect'])->name('surcharge-get_data_select');

    // 新規登録
    Route::get('/surcharge/new', [SurchargeController::class, 'new'])->name('surcharge-new');

    // 新規登録処理
    Route::post('/surcharge/create', [SurchargeController::class, 'create'])->name('surcharge-create');

    // 編集
    Route::get('/surcharge/edit/{surchargeId}', [SurchargeController::class, 'edit'])->name('surcharge-edit');

    // 編集処理
    Route::post('/surcharge/update', [SurchargeController::class, 'update'])->name('surcharge-update');

    // バリデーション(登録用)
    Route::post('/surcharge/vd_input', [SurchargeController::class, 'validationForInput'])->name('surcharge-vd_input');

    // 削除処理
    Route::post('/surcharge/delete', [SurchargeController::class, 'delete'])->name('surcharge-delete');
    //---------------------
    // 給与明細
    //---------------------

    // 一覧
    Route::get('/salary', [SalaryController::class, 'index'])->name('salary');

    // 検索結果取得
    Route::post('/salary/search', [SalaryController::class, 'search'])->name('salary-search');

    // 詳細画面
    Route::get('/salary/detail/{date}', [SalaryController::class, 'detail'])->name('salary-detail');

    // PDF出力
    Route::get('/salary/pdf/{date}', [SalaryController::class, 'pdf'])->name('salary-pdf');

    //---------------------
    // 研修受講
    //---------------------

    // 一覧
    Route::get('/training', [TrainingController::class, 'index'])->name('training');

    // 検索結果取得
    Route::post('/training/search', [TrainingController::class, 'search'])->name('training-search');

    // 受講詳細
    Route::get('/training/detail/{trnId}', [TrainingController::class, 'detail'])->name('training-detail');

    // 動画の閲覧履歴を更新
    Route::post('/training/movie_browse', [TrainingController::class, 'movieBrowse'])->name('training-movie_browse');

    // 資料のダウンロード
    Route::get('/training/download/{trnId}', [TrainingController::class, 'download'])->name('training-download');
});

//===============================================
// 管理者向け
//===============================================

use App\Http\Controllers\Admin\RoomCalendarController;
use App\Http\Controllers\Admin\RegularScheduleController;
use App\Http\Controllers\Admin\ReportCheckController;
use App\Http\Controllers\Admin\StudentClassController;
use App\Http\Controllers\Admin\TransferCheckController;
use App\Http\Controllers\Admin\TransferRequiredController;
use App\Http\Controllers\Admin\AbsentAcceptController;
use App\Http\Controllers\Admin\ExtraLessonMngController;
use App\Http\Controllers\Admin\MemberMngController;
use App\Http\Controllers\Admin\RecordController;
use App\Http\Controllers\Admin\DesiredMngController;
use App\Http\Controllers\Admin\GradesMngController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\ConferenceAcceptController;
use App\Http\Controllers\Admin\GiveBadgeController;
use App\Http\Controllers\Admin\TutorMngController;
use App\Http\Controllers\Admin\TutorClassController;
use App\Http\Controllers\Admin\TutorAssignController;
use App\Http\Controllers\Admin\ContactMngController;
use App\Http\Controllers\Admin\NoticeRegistController;
use App\Http\Controllers\Admin\NoticeTemplateController;
use App\Http\Controllers\Admin\SeasonMngController;
use App\Http\Controllers\Admin\SeasonMngStudentController;
use App\Http\Controllers\Admin\SeasonMngTutorController;
use App\Http\Controllers\Admin\GradeExampleController;
use App\Http\Controllers\Admin\TrainingMngController;
use App\Http\Controllers\Admin\SurchargeAcceptController;
use App\Http\Controllers\Admin\OvertimeController;
use App\Http\Controllers\Admin\SalaryCalculationController;
use App\Http\Controllers\Admin\SalaryImportController;
use App\Http\Controllers\Admin\InvoiceImportController;
use App\Http\Controllers\Admin\AccountMngController;
use App\Http\Controllers\Admin\MasterMngCampusController;
use App\Http\Controllers\Admin\MasterMngBoothController;
use App\Http\Controllers\Admin\MasterMngTimetableController;
use App\Http\Controllers\Admin\MasterMngCourseController;
use App\Http\Controllers\Admin\MasterMngSubjectController;
use App\Http\Controllers\Admin\MasterMngGradeSubjectController;
use App\Http\Controllers\Admin\MasterMngTextController;
use App\Http\Controllers\Admin\MasterMngCategoryController;
use App\Http\Controllers\Admin\MasterMngUnitController;
use App\Http\Controllers\Admin\MasterMngSystemController;
use App\Http\Controllers\Admin\AllMemberImportController;
use App\Http\Controllers\Admin\TransferResetController;
use App\Http\Controllers\Admin\DataResetController;
use App\Http\Controllers\Admin\ImportSchoolCodeController;
use App\Http\Controllers\Admin\YearScheduleImportController;

Route::group(['middleware' => ['auth', 'can:admin']], function () {

    //---------------------
    // 教室カレンダー
    //---------------------

    // 教室カレンダー
    Route::get('/room_calendar', [RoomCalendarController::class, 'calendar'])->name('room_calendar');

    // カレンダー - 詳細取得用
    Route::post('/room_calendar/get_calendar', [RoomCalendarController::class, 'getCalendar'])->name('room_calendar-get_calendar');

    // カレンダー - ブース情報取得用
    Route::post('/room_calendar/get_booth', [RoomCalendarController::class, 'getBooth'])->name('room_calendar-get_booth');

    // 教室カレンダー登録画面
    Route::get('/room_calendar/new/{campusCd}/{datetimeStr}/{boothCd}', [RoomCalendarController::class, 'new'])->name('room_calendar-new');

    // 新規登録処理
    Route::post('/room_calendar/create', [RoomCalendarController::class, 'create'])->name('room_calendar-create');

    // 教室カレンダー編集画面
    Route::get('/room_calendar/edit/{scheduleId}', [RoomCalendarController::class, 'edit'])->name('room_calendar-edit');

    // 教室カレンダーコピー登録画面
    Route::get('/room_calendar/copy/{scheduleId}', [RoomCalendarController::class, 'copy'])->name('room_calendar-copy');

    // 編集処理
    Route::post('/room_calendar/update', [RoomCalendarController::class, 'update'])->name('room_calendar-update');

    // コピー処理
    Route::post('/room_calendar/copy_create', [RoomCalendarController::class, 'copyCreate'])->name('room_calendar-copy_create');

    // コースプルダウンを選択された際にコース情報を返却する
    Route::post('/room_calendar/get_data_select_course', [RoomCalendarController::class, 'getDataSelectCourse'])->name('room_calendar-get_data_select_course');

    // 時限プルダウンを選択された際に時間割の情報を返却する
    Route::post('/room_calendar/get_data_select_timetable', [RoomCalendarController::class, 'getDataSelectTimetable'])->name('notice_regist-get_data_select_timetable');

    // 日付を変更された際に時限プルダウンを返却する
    Route::post('/room_calendar/get_data_select', [RoomCalendarController::class, 'getDataSelect'])->name('room_calendar-get_data_select');

    // バリデーション(登録用)
    Route::post('/room_calendar/vd_input', [RoomCalendarController::class, 'validationForInput'])->name('room_calendar-vd_input');

    // 削除処理
    Route::post('/room_calendar/delete', [RoomCalendarController::class, 'delete'])->name('room_calendar-delete');

    // 教室カレンダー欠席登録画面（集団のみ）
    Route::get('/room_calendar/absent/{scheduleId}', [RoomCalendarController::class, 'absent'])->name('room_calendar-absent');

    // バリデーション(欠席登録用)
    Route::post('/room_calendar/vd_input_absent', [RoomCalendarController::class, 'validationForInputAbsent'])->name('room_calendar-vd_input_absent');

    // 編集処理(欠席登録用)
    Route::post('/room_calendar/update_absent', [RoomCalendarController::class, 'updateAbsent'])->name('room_calendar-update_absent');

    // 教室カレンダー（登録後の遷移）※試作中
    Route::get('/room_calendar/{campusCd}/{dateStr}', [RoomCalendarController::class, 'calendarBack'])->name('room_calendar-back');

    //---------------------
    // レギュラーカレンダー
    //---------------------

    // defaultWeekカレンダー
    Route::get('/regular_schedule', [RegularScheduleController::class, 'calendar'])->name('regular_schedule');

    // defaultWeekカレンダー - 詳細取得用
    Route::post('/regular_schedule/get_calendar', [RegularScheduleController::class, 'getCalendar'])->name('regular_schedule-get_calendar');

    // defaultWeekカレンダー - ブース情報取得用
    Route::post('/regular_schedule/get_booth', [RegularScheduleController::class, 'getBooth'])->name('regular_schedule-get_booth');

    // コースプルダウンを選択された際にコース情報を返却する
    Route::post('/regular_schedule/get_data_select_course', [RegularScheduleController::class, 'getDataSelectCourse'])->name('regular_schedule-get_data_select_course');

    // 時限プルダウンを選択された際に時間割の情報を返却する
    Route::post('/regular_schedule/get_data_select_timetable', [RegularScheduleController::class, 'getDataSelectTimetable'])->name('regular_schedule-get_data_select_timetable');

    // defaultWeekカレンダー登録画面
    Route::get('/regular_schedule/new/{campusCd}/{daytimeStr}/{boothCd}', [RegularScheduleController::class, 'new'])->name('regular_schedule-new');

    // defaultWeekカレンダー編集画面
    Route::get('/regular_schedule/edit/{regularClassId}', [RegularScheduleController::class, 'edit'])->name('regular_schedule-edit');

    // defaultWeekカレンダーコピー登録画面
    Route::get('/regular_schedule/copy/{regularClassId}', [RegularScheduleController::class, 'copy'])->name('regular_schedule-copy');

    // バリデーション(登録用)
    Route::post('/regular_schedule/vd_input', [RegularScheduleController::class, 'validationForInput'])->name('regular_schedule-vd_input');

    // 新規登録処理
    Route::post('/regular_schedule/create', [RegularScheduleController::class, 'create'])->name('regular_schedule-create');

    // 編集処理
    Route::post('/regular_schedule/update', [RegularScheduleController::class, 'update'])->name('regular_schedule-update');

    // 削除処理
    Route::post('/regular_schedule/delete', [RegularScheduleController::class, 'delete'])->name('regular_schedule-delete');

    // バリデーション(一括登録用)
    Route::post('/regular_schedule/vd_input_bulk', [RegularScheduleController::class, 'validationForInputBulk'])->name('regular_schedule-vd_input_bulk');

    // 一括登録処理
    Route::post('/regular_schedule/create_bulk', [RegularScheduleController::class, 'createBulk'])->name('regular_schedule-create_bulk');

    //---------------------
    // 授業報告書
    //---------------------

    // 一覧画面
    Route::get('/report_check', [ReportCheckController::class, 'index'])->name('report_check');

    // バリデーション(検索用)
    Route::post('/report_check/vd_search', [ReportCheckController::class, 'validationForSearch'])->name('report_check-vd_search');

    // 検索結果取得
    Route::post('/report_check/search', [ReportCheckController::class, 'search'])->name('report_check-search');

    // 教室選択プルダウンを選択された際に生徒プルダウンの情報を返却する
    Route::post('/report_check/get_data_select_search', [ReportCheckController::class, 'getDataSelectSearch'])->name('report_check-get_data_select_search');

    // 詳細取得用
    Route::post('/report_check/get_data', [ReportCheckController::class, 'getData'])->name('report_check-get_data');

    // 授業報告編集
    Route::get('/report_check/edit/{reportId}', [ReportCheckController::class, 'edit'])->name('report_check-edit');

    // カレンダーを選択された際に教室・講師の情報を返却する
    Route::post('/report_check/get_data_select', [ReportCheckController::class, 'getDataSelect'])->name('report_check-get_data_select');

    // 編集処理
    Route::post('/report_check/update', [ReportCheckController::class, 'update'])->name('report_check-update');

    // モーダル処理
    Route::post('/report_check/exec_modal', [ReportCheckController::class, 'execModal'])->name('report_check-exec_modal');

    // バリデーション(登録用)
    Route::post('/report_check/vd_input', [ReportCheckController::class, 'validationForInput'])->name('report_check-vd_input');

    // 削除処理
    Route::post('/report_check/delete', [ReportCheckController::class, 'delete'])->name('report_check-delete');

    //---------------------
    // 授業情報検索
    //---------------------

    // 一覧画面
    Route::get('/student_class', [StudentClassController::class, 'index'])->name('student_class');

    // バリデーション(検索用)
    Route::post('/student_class/vd_search', [StudentClassController::class, 'validationForSearch'])->name('student_class-vd_search');

    // 検索結果取得
    Route::post('/student_class/search', [StudentClassController::class, 'search'])->name('student_class-search');

    // 詳細取得用
    Route::post('/student_class/get_data', [StudentClassController::class, 'getData'])->name('student_class-get_data');

    //---------------------
    // 振替調整一覧
    //---------------------

    // 一覧画面
    Route::get('/transfer_check', [TransferCheckController::class, 'index'])->name('transfer_check');

    // バリデーション(検索用)
    Route::post('/transfer_check/vd_search', [TransferCheckController::class, 'validationForSearch'])->name('transfer_check-vd_search');

    // 検索結果取得
    Route::post('/transfer_check/search', [TransferCheckController::class, 'search'])->name('transfer_check-search');

    // 詳細取得用
    Route::post('/transfer_check/get_data', [TransferCheckController::class, 'getData'])->name('transfer_check-get_data');

    // モーダル処理
    Route::post('/transfer_check/exec_modal', [TransferCheckController::class, 'execModal'])->name('transfer_check-exec_modal');

    // 振替調整登録画面
    Route::get('/transfer_check/new', [TransferCheckController::class, 'new'])->name('transfer_check-new');

    // 振替連絡編集
    Route::get('/transfer_check/edit/{transferApplyId}', [TransferCheckController::class, 'edit'])->name('transfer_check-edit');

    // // カレンダーを選択された際に教室・講師の情報を返却する
    // Route::post('/transfer_check/get_data_select', [TransferCheckController::class, 'getDataSelect'])->name('transfer_check-get_data_select');

    // 編集処理
    Route::post('/transfer_check/update', [TransferCheckController::class, 'update'])->name('transfer_check-update');

    // バリデーション(登録用)
    Route::post('/transfer_check/vd_input', [TransferCheckController::class, 'validationForInput'])->name('transfer_check-vd_input');

    //---------------------
    // 要振替授業管理
    //---------------------

    // 一覧画面
    Route::get('/transfer_required', [TransferRequiredController::class, 'index'])->name('transfer_required');

    // バリデーション(検索用)
    Route::post('/transfer_required/vd_search', [TransferRequiredController::class, 'validationForSearch'])->name('transfer_required-vd_search');

    // 検索結果取得
    Route::post('/transfer_required/search', [TransferRequiredController::class, 'search'])->name('transfer_required-search');

    //----------------------
    // 欠席申請受付
    //----------------------

    // 一覧画面
    Route::get('/absent_accept', [AbsentAcceptController::class, 'index'])->name('absent_accept');

    // バリデーション(検索用)
    Route::post('/absent_accept/vd_search', [AbsentAcceptController::class, 'validationForSearch'])->name('absent_accept-vd_search');

    // 検索結果取得
    Route::post('/absent_accept/search', [AbsentAcceptController::class, 'search'])->name('absent_accept-search');

    // 詳細取得用
    Route::post('/absent_accept/get_data', [AbsentAcceptController::class, 'getData'])->name('absent_accept-get_data');

    // モーダル処理
    Route::post('/absent_accept/exec_modal', [AbsentAcceptController::class, 'execModal'])->name('absent_accept-exec_modal');

    // 欠席申請編集
    Route::get('/absent_accept/edit/{absentApplyId}', [AbsentAcceptController::class, 'edit'])->name('absent_accept-edit');

    // 授業日時プルダウンを選択された際に教室・講師の情報を返却する
    Route::post('/absent_accept/get_data_select', [AbsentAcceptController::class, 'getDataSelect'])->name('absent_accept-get_data_select');

    // 編集処理
    Route::post('/absent_accept/update', [AbsentAcceptController::class, 'update'])->name('absent_accept-update');

    // バリデーション(登録用)
    Route::post('/absent_accept/vd_input', [AbsentAcceptController::class, 'validationForInput'])->name('absent_accept-vd_input');

    // 削除処理
    Route::post('/absent_accept/delete', [AbsentAcceptController::class, 'delete'])->name('absent_accept-delete');

    //---------------------
    // 追加授業申請受付
    //---------------------

    // 一覧画面
    Route::get('/extra_lesson_mng', [ExtraLessonMngController::class, 'index'])->name('extra_lesson_mng');

    // バリデーション(検索用)
    Route::post('/extra_lesson_mng/vd_search', [ExtraLessonMngController::class, 'validationForSearch'])->name('extra_lesson_mng-vd_search');

    // 検索結果取得
    Route::post('/extra_lesson_mng/search', [ExtraLessonMngController::class, 'search'])->name('extra_lesson_mng-search');

    // 詳細取得用
    Route::post('/extra_lesson_mng/get_data', [ExtraLessonMngController::class, 'getData'])->name('extra_lesson_mng-get_data');

    // 校舎ダウンを選択された際に時限リスト・講師リストを返却する
    Route::post('/extra_lesson_mng/get_data_select_list', [ExtraLessonMngController::class, 'getDataSelectList'])->name('extra_lesson_mng-get_data_select_list');

    // 時限プルダウンを選択された際に時間割の情報を返却する
    Route::post('/extra_lesson_mng/get_data_select_timetable', [ExtraLessonMngController::class, 'getDataSelectTimetable'])->name('extra_lesson_mng-get_data_select_timetable');

    // 日付を変更された際に時限プルダウンを返却する
    Route::post('/extra_lesson_mng/get_data_select', [ExtraLessonMngController::class, 'getDataSelect'])->name('extra_lesson_mng-get_data_select');

    // 新規登録
    Route::get('/extra_lesson_mng/new/{sid}/{campusCd}', [ExtraLessonMngController::class, 'new'])->name('extra_lesson_mng-new');

    // 新規登録処理
    Route::post('/extra_lesson_mng/create', [ExtraLessonMngController::class, 'create'])->name('extra_lesson_mng-create');

    // 編集画面
    Route::get('/extra_lesson_mng/edit/{extraId}', [ExtraLessonMngController::class, 'edit'])->name('extra_lesson_mng-edit');

    // 編集処理
    Route::post('/extra_lesson_mng/update', [ExtraLessonMngController::class, 'update'])->name('extra_lesson_mng-update');

    // バリデーション(登録用)
    Route::post('/extra_lesson_mng/vd_input', [ExtraLessonMngController::class, 'validationForInput'])->name('extra_lesson_mng-vd_input');

    // 削除処理
    Route::post('/extra_lesson_mng/delete', [ExtraLessonMngController::class, 'delete'])->name('extra_lesson_mng-delete');

    //---------------------
    // 会員管理 一覧・生徒カルテ・カレンダー表示・請求書表示
    //---------------------

    // 会員一覧
    Route::get('/member_mng', [MemberMngController::class, 'index'])->name('member_mng');

    // バリデーション(検索用)
    Route::post('/member_mng/vd_search', [MemberMngController::class, 'validationForSearch'])->name('member_mng-vd_search');

    // 検索結果取得
    Route::post('/member_mng/search', [MemberMngController::class, 'search'])->name('member_mng-search');

    // 会員情報詳細（生徒カルテ）
    Route::get('/member_mng/detail/{sid}', [MemberMngController::class, 'detail'])->name('member_mng-detail');

    // 会員情報詳細 - 詳細取得用
    Route::post('/member_mng/get_data_detail', [MemberMngController::class, 'getDataDetail'])->name('member_mng-get_data_detail');

    // 詳細取得用（CSV出力確認モーダル）
    Route::post('/member_mng/get_data', [MemberMngController::class, 'getData'])->name('member_mng-get_data');

    // モーダル処理（CSV出力）
    Route::post('/member_mng/exec_modal', [MemberMngController::class, 'execModal'])->name('member_mng-exec_modal');

    // カレンダー
    Route::get('/member_mng/calendar/{sid}', [MemberMngController::class, 'calendar'])->name('member_mng-calendar');

    // カレンダー - 詳細取得用
    Route::post('/member_mng/get_calendar', [MemberMngController::class, 'getCalendar'])->name('member_mng-get_calendar');

    // 請求情報一覧
    Route::get('/member_mng/invoice/{sid}', [MemberMngController::class, 'invoice'])->name('member_mng-invoice');

    // 請求情報一覧 - 詳細画面
    Route::get('/member_mng/invoice/{sid}/detail/{date}', [MemberMngController::class, 'detailInvoice'])->name('member_mng-invoice_detail');

    // 請求情報一覧 - 検索結果取得
    Route::post('/member_mng/search_invoice', [MemberMngController::class, 'searchInvoice'])->name('member_mng-search_invoice');

    // PDF出力
    Route::get('/member_mng/invoice/{sid}/pdf/{date}', [MemberMngController::class, 'pdf'])->name('member_mng-pdf_invoice');

    // バリデーション(学校-検索用)
    Route::post('/member_mng/vd_search_school', [MemberMngController::class, 'validationForSearchSchool'])->name('member_mng-vd_search_school');

    // 学校-検索結果取得
    Route::post('/member_mng/search_school', [MemberMngController::class, 'searchSchool'])->name('member_mng-search_school');

    //---------------------
    // 会員管理 生徒登録・編集・退会
    //---------------------

    // 新規登録画面
    Route::get('/member_mng/new', [MemberMngController::class, 'new'])->name('member_mng-new');

    // 新規登録処理
    Route::post('/member_mng/create', [MemberMngController::class, 'create'])->name('member_mng-create');

    // 更新画面
    Route::get('/member_mng/edit/{sid}', [MemberMngController::class, 'edit'])->name('member_mng-edit');

    // 編集処理
    Route::post('/member_mng/update', [MemberMngController::class, 'update'])->name('member_mng-update');

    // バリデーション(登録用)
    Route::post('/member_mng/vd_input', [MemberMngController::class, 'validationForInput'])->name('member_mng-vd_input');

    // 退会登録画面
    Route::get('/member_mng/leave/edit/{sid}', [MemberMngController::class, 'leaveEdit'])->name('member_mng-leave-edit');

    // 編集処理
    Route::post('/member_mng/leave/update', [MemberMngController::class, 'leaveUpdate'])->name('member_mng-leave-update');

    // バリデーション(登録用)
    Route::post('/member_mng/leave/vd_input', [MemberMngController::class, 'validationForInputLeave'])->name('member_mng-leave-vd_input');

    //---------------------
    // 連絡記録
    //---------------------

    // 連絡記録一覧
    Route::get('/member_mng/record/{sid}', [RecordController::class, 'index'])->name('record');

    // 詳細取得用
    Route::post('/member_mng/get_data_record', [RecordController::class, 'getData'])->name('record-get_data');

    // 検索結果取得
    Route::post('/member_mng/search_record', [RecordController::class, 'search'])->name('record-search');

    // バリデーション(検索用)
    Route::post('/member_mng/vd_search_record', [RecordController::class, 'validationForSearch'])->name('record-vd_search');

    // 連絡記録登録画面
    Route::get('/member_mng/record/{sid}/new', [RecordController::class, 'new'])->name('record-new');

    // 新規登録処理
    Route::post('/member_mng/create_record', [RecordController::class, 'create'])->name('record-create');

    // 連絡記録編集画面
    Route::get('/member_mng/record/edit/{recordId}', [RecordController::class, 'edit'])->name('record-edit');

    // 編集処理
    Route::post('/member_mng/update_record', [RecordController::class, 'update'])->name('record-update');

    // バリデーション(登録用)
    Route::post('/member_mng/vd_input_record', [RecordController::class, 'validationForInput'])->name('record-vd_input');

    // 削除処理
    Route::post('/member_mng/delete_record', [RecordController::class, 'delete'])->name('record-delete');

    //---------------------
    // 受験校管理
    //---------------------

    // 受験校管理一覧
    Route::get('/member_mng/desired_mng/{sid}', [DesiredMngController::class, 'index'])->name('desired_mng');

    // 詳細取得用
    Route::post('/member_mng/get_data_desired_mng', [DesiredMngController::class, 'getData'])->name('desired_mng-get_data');

    // 検索結果取得
    Route::post('/member_mng/search_desired_mng', [DesiredMngController::class, 'search'])->name('desired_mng-search');

    // バリデーション(検索用)
    Route::post('/member_mng/vd_search_desired_mng', [DesiredMngController::class, 'validationForSearch'])->name('desired_mng-vd_search');

    // 受験校登録画面
    Route::get('/member_mng/desired_mng/{sid}/new', [DesiredMngController::class, 'new'])->name('desired_mng-new');

    // 新規登録処理
    Route::post('/member_mng/create_desired_mng', [DesiredMngController::class, 'create'])->name('desired_mng-create');

    // 受験校編集画面
    Route::get('/member_mng/desired_mng/edit/{desiredId}', [DesiredMngController::class, 'edit'])->name('desired_mng-edit');

    // 編集処理
    Route::post('/member_mng/update_desired_mng', [DesiredMngController::class, 'update'])->name('desired_mng-update');

    // バリデーション(登録用)
    Route::post('/member_mng/vd_input_desired_mng', [DesiredMngController::class, 'validationForInput'])->name('desired_mng-vd_input');

    // 削除処理
    Route::post('/member_mng/delete_desired_mng', [DesiredMngController::class, 'delete'])->name('desired_mng-delete');

    //---------------------
    // 生徒成績
    //---------------------

    // 一覧画面
    Route::get('/member_mng/grades_mng/{sid}', [GradesMngController::class, 'index'])->name('grades_mng');

    // 検索結果取得
    Route::post('/member_mng/search_grades_mng', [GradesMngController::class, 'search'])->name('grades_mng-search');

    // 詳細取得用
    Route::post('/member_mng/get_data_grades_mng', [GradesMngController::class, 'getData'])->name('grades_mng-get_data');

    // 試験種別が選択された際に成績表示欄数を返却する
    Route::post('/member_mng/get_data_select_grades', [GradesMngController::class, 'getDataSelect'])->name('grades_mng-get_data_select');

    // 登録画面
    Route::get('/member_mng/grades_mng/{sid}/new', [GradesMngController::class, 'new'])->name('grades_mng-new');

    // 新規登録処理
    Route::post('/member_mng/create_grades_mng', [GradesMngController::class, 'create'])->name('grades_mng-create');

    // 生徒成績編集
    Route::get('/member_mng/grades_mng/edit/{gradesId}', [GradesMngController::class, 'edit'])->name('grades_mng-edit');

    // 編集処理
    Route::post('/member_mng/update_grades_mng', [GradesMngController::class, 'update'])->name('grades_mng-update');

    // バリデーション(登録用)
    Route::post('/member_mng/vd_input_grades_mng', [GradesMngController::class, 'validationForInput'])->name('grades_mng-vd_input');

    // 削除処理
    Route::post('/member_mng/delete_grades_mng', [GradesMngController::class, 'delete'])->name('grades_mng-delete');

    //---------------------
    // バッジ付与管理（生徒カルテ）
    //---------------------

    // バッジ一覧
    Route::get('/member_mng/badge/{sid}', [BadgeController::class, 'index'])->name('badge');

    // 検索結果取得
    Route::post('/member_mng/search_badge', [BadgeController::class, 'search'])->name('badge-search');

    // バッジ種別プルダウンを選択された際にコードマスタ汎用項目1の情報を返却する
    Route::post('/member_mng/get_data_select_badge', [BadgeController::class, 'getDataSelectTemplate'])->name('badge-get_data_select');

    // 登録画面
    Route::get('/member_mng/badge/{sid}/new', [BadgeController::class, 'new'])->name('badge-new');

    // 新規登録処理
    Route::post('/member_mng/create_badge', [BadgeController::class, 'create'])->name('badge-create');

    // 編集画面
    Route::get('/member_mng/badge/edit/{badgeId}', [BadgeController::class, 'edit'])->name('badge-edit');

    // 編集処理
    Route::post('/member_mng/update_badge', [BadgeController::class, 'update'])->name('badge-update');

    // バリデーション(登録用)
    Route::post('/member_mng/vd_input_badge', [BadgeController::class, 'validationForInput'])->name('badge-vd_input');

    // 削除処理
    Route::post('/member_mng/delete_badge', [BadgeController::class, 'delete'])->name('badge-delete');

    //---------------------
    // 面談日程連絡一覧
    //---------------------

    // 一覧画面
    Route::get('/conference_accept', [ConferenceAcceptController::class, 'index'])->name('conference_accept');

    // バリデーション(検索用)
    Route::post('/conference_accept/vd_search', [ConferenceAcceptController::class, 'validationForSearch'])->name('conference_accept-vd_search');

    // 教室選択プルダウンを選択された際に生徒プルダウンの情報を返却する
    Route::post('/conference_accept/get_data_select_search', [ConferenceAcceptController::class, 'getDataSelectSearch'])->name('conference_accept-get_data_select_search');

    // 検索結果取得
    Route::post('/conference_accept/search', [ConferenceAcceptController::class, 'search'])->name('conference_accept-search');

    // // 詳細取得用
    Route::post('/conference_accept/get_data', [ConferenceAcceptController::class, 'getData'])->name('conference_accept-get_data');

    // // モーダル処理
    // Route::post('/conference_accept/exec_modal', [ConferenceAcceptController::class, 'execModal'])->name('conference_accept-exec_modal');

    // 追加登録画面
    Route::get('/conference_accept/new', [ConferenceAcceptController::class, 'new'])->name('conference_accept-new');

    // 教室選択プルダウンを選択された際に生徒プルダウン・ブースプルダウンの情報を返却する
    Route::post('/conference_accept/get_data_select_new', [ConferenceAcceptController::class, 'getDataSelectNew'])->name('conference_accept-get_data_select_new');

    // 登録処理
    Route::post('/conference_accept/create', [ConferenceAcceptController::class, 'create'])->name('conference_accept-create');

    // 面談編集
    Route::get('/conference_accept/edit/{conferenceId}', [ConferenceAcceptController::class, 'edit'])->name('conference_accept-edit');

    // 編集処理
    Route::post('/conference_accept/update', [ConferenceAcceptController::class, 'update'])->name('conference_accept-update');

    // バリデーション(登録用)
    Route::post('/conference_accept/vd_input', [ConferenceAcceptController::class, 'validationForInput'])->name('conference_accept-vd_input');

    //---------------------
    // バッジ付与一覧（CSVダウンロード）
    //---------------------

    // バッジ付与一覧
    Route::get('/give_badge', [GiveBadgeController::class, 'index'])->name('give_badge');

    // バリデーション(検索用)
    Route::post('/give_badge/vd_search', [GiveBadgeController::class, 'validationForSearch'])->name('give_badge-vd_search');

    // 検索結果取得
    Route::post('/give_badge/search', [GiveBadgeController::class, 'search'])->name('give_badge-search');

    // 詳細取得用（CSV出力）
    Route::post('/give_badge/get_data', [GiveBadgeController::class, 'getData'])->name('give_badge-get_data');

    // モーダル処理（CSV出力）
    Route::post('/give_badge/exec_modal', [GiveBadgeController::class, 'execModal'])->name('give_badge-exec_modal');

    //---------------------
    // 講師管理 登録・編集・詳細・所属・カレンダー・空き時間・給与表示
    //---------------------

    // 講師一覧
    Route::get('/tutor_mng', [TutorMngController::class, 'index'])->name('tutor_mng');

    // バリデーション(検索用)
    Route::post('/tutor_mng/vd_search', [TutorMngController::class, 'validationForSearch'])->name('tutor_mng-vd_search');

    // 検索結果取得
    Route::post('/tutor_mng/search', [TutorMngController::class, 'search'])->name('tutor_mng-search');

    // 講師情報詳細
    Route::get('/tutor_mng/detail/{tid}', [TutorMngController::class, 'detail'])->name('tutor_mng-detail');

    // 給料明細一覧
    Route::get('/tutor_mng/salary/{tid}', [TutorMngController::class, 'salary'])->name('tutor_mng-salary');

    // 給料明細一覧 - 検索結果取得
    Route::post('/tutor_mng/search_salary', [TutorMngController::class, 'searchSalary'])->name('tutor_mng-search_salary');

    // 給料明細一覧 - 詳細画面
    Route::get('/tutor_mng/salary/{tid}/detail/{date}', [TutorMngController::class, 'detailSalary'])->name('tutor_mng-detail_salary');

    // PDF出力
    Route::get('/tutor_mng/salary/{tid}/pdf/{date}', [TutorMngController::class, 'pdf'])->name('tutor_mng-pdf_salary');

    // 講師空き時間
    Route::get('/tutor_mng/weekly_shift/{tid}', [TutorMngController::class, 'weeklyShift'])->name('tutor_mng-weekly_shift');

    // 講師カレンダー
    Route::get('/tutor_mng/calendar/{tid}', [TutorMngController::class, 'calendar'])->name('tutor_mng-calendar');

    // 詳細取得用
    Route::post('/tutor_mng/get_calendar', [TutorMngController::class, 'getCalendar'])->name('tutor_mng-get_calendar');

    // 講師 新規登録
    Route::get('/tutor_mng/new', [TutorMngController::class, 'new'])->name('tutor_mng-new');

    // 講師 新規登録処理
    Route::post('/tutor_mng/create', [TutorMngController::class, 'create'])->name('tutor_mng-create');

    // 講師 編集
    Route::get('/tutor_mng/edit/{tid}', [TutorMngController::class, 'edit'])->name('tutor_mng-edit');

    // 講師 編集処理
    Route::post('/tutor_mng/update', [TutorMngController::class, 'update'])->name('tutor_mng-update');

    // バリデーション(登録用)（講師登録）
    Route::post('/tutor_mng/vd_input', [TutorMngController::class, 'validationForInput'])->name('tutor_mng-vd_input');

    // バリデーション(学校-検索用)
    Route::post('/tutor_mng/vd_search_school', [TutorMngController::class, 'validationForSearchSchool'])->name('tutor_mng-vd_search_school');

    // 学校-検索結果取得
    Route::post('/tutor_mng/search_school', [TutorMngController::class, 'searchSchool'])->name('tutor_mng-search_school');

    // 退職登録画面
    Route::get('/tutor_mng/leave/edit/{tid}', [TutorMngController::class, 'leaveEdit'])->name('tutor_mng-leave-edit');

    // 退職処理
    Route::post('/tutor_mng/update_leave', [TutorMngController::class, 'leaveUpdate'])->name('tutor_mng-leave-update');

    // バリデーション（退職登録用）
    Route::post('/tutor_mng/vd_input_leave', [TutorMngController::class, 'leaveValidationForInput'])->name('tutor_mng-leave-vd_input');

    // 所属登録
    Route::get('/tutor_mng/campus/new/{tid}', [TutorMngController::class, 'campusNew'])->name('tutor_mng-campus-new');

    // 所属登録処理
    Route::post('/tutor_mng/create_campus', [TutorMngController::class, 'campusCreate'])->name('tutor_mng-campus-create');

    // 所属編集
    Route::get('/tutor_mng/campus/edit/{tutorCampusId}', [TutorMngController::class, 'campusEdit'])->name('tutor_mng-campus-edit');

    // 所属編集処理
    Route::post('/tutor_mng/update_campus', [TutorMngController::class, 'campusUpdate'])->name('tutor_mng-campus-update');

    // バリデーション(登録用)（所属登録）
    Route::post('/tutor_mng/vd_input_campus', [TutorMngController::class, 'campusValidationForInput'])->name('tutor_mng-campus-vd_input');

    // 所属削除処理
    Route::post('/tutor_mng/delete_campus', [TutorMngController::class, 'campusDelete'])->name('tutor_mng-campus-delete');

    //---------------------
    // 講師授業集計
    //---------------------
    // 一覧画面
    Route::get('/tutor_class', [TutorClassController::class, 'index'])->name('tutor_class');

    // バリデーション(検索用)
    Route::post('/tutor_class/vd_search', [TutorClassController::class, 'validationForSearch'])->name('tutor_class-vd_search');

    // 検索結果取得
    Route::post('/tutor_class/search', [TutorClassController::class, 'search'])->name('tutor_class-search');

    // 詳細取得用
    Route::post('/tutor_class/get_data', [TutorClassController::class, 'getData'])->name('tutor_class-get_data');

    //---------------------
    // 空き講師検索
    //---------------------
    // 一覧画面
    Route::get('/tutor_assign', [TutorAssignController::class, 'index'])->name('tutor_assign');

    // バリデーション(検索用)
    Route::post('/tutor_assign/vd_search', [TutorAssignController::class, 'validationForSearch'])->name('tutor_assign-vd_search');

    // 検索結果取得
    Route::post('/tutor_assign/search', [TutorAssignController::class, 'search'])->name('tutor_assign-search');

    // 詳細取得用
    Route::post('/tutor_assign/get_data', [TutorAssignController::class, 'getData'])->name('tutor_assign-get_data');

    //---------------------
    // 問い合わせ管理
    //---------------------

    // 一覧画面
    Route::get('/contact_mng', [ContactMngController::class, 'index'])->name('contact_mng');

    // バリデーション(検索用)
    Route::post('/contact_mng/vd_search', [ContactMngController::class, 'validationForSearch'])->name('contact_mng-vd_search');

    // 検索結果取得
    Route::post('/contact_mng/search', [ContactMngController::class, 'search'])->name('contact_mng-search');

    // 詳細取得用
    Route::post('/contact_mng/get_data', [ContactMngController::class, 'getData'])->name('contact_mng-get_data');

    // 問い合わせ管理 変更
    Route::get('/contact_mng/edit/{contactId}', [ContactMngController::class, 'edit'])->name('contact_mng-edit');

    // 編集処理
    Route::post('/contact_mng/update', [ContactMngController::class, 'update'])->name('contact_mng-update');

    // バリデーション(登録用)
    Route::post('/contact_mng/vd_input', [ContactMngController::class, 'validationForInput'])->name('contact_mng-vd_input');

    // 削除処理
    Route::post('/contact_mng/delete', [ContactMngController::class, 'delete'])->name('contact_mng-delete');

    //----------------------
    // お知らせ通知
    //----------------------

    // 一覧画面
    Route::get('/notice_regist', [NoticeRegistController::class, 'index'])->name('notice_regist');

    // バリデーション(検索用)
    Route::post('/notice_regist/vd_search', [NoticeRegistController::class, 'validationForSearch'])->name('notice_regist-vd_search');

    // 検索結果取得
    Route::post('/notice_regist/search', [NoticeRegistController::class, 'search'])->name('notice_regist-search');

    // 詳細取得用
    Route::post('/notice_regist/get_data', [NoticeRegistController::class, 'getData'])->name('notice_regist-get_data');

    // お知らせ登録
    Route::get('/notice_regist/new', [NoticeRegistController::class, 'new'])->name('notice_regist-new');

    // 新規登録処理
    Route::post('/notice_regist/create', [NoticeRegistController::class, 'create'])->name('notice_regist-create');

    // お知らせ詳細
    Route::get('/notice_regist/detail/{noticeId}', [NoticeRegistController::class, 'detail'])->name('notice_regist-detail');

    // 定型文選択プルダウンを選択された際にタイトル・内容の情報を返却する
    Route::post('/notice_regist/get_data_select_template', [NoticeRegistController::class, 'getDataSelectTemplate'])->name('notice_regist-get_data_select_template');

    // 宛先種別プルダウンを選択
    Route::post('/notice_regist/get_data_select', [NoticeRegistController::class, 'getDataSelect'])->name('notice_regist-get_data_select');

    // バリデーション(登録用)
    Route::post('/notice_regist/vd_input', [NoticeRegistController::class, 'validationForInput'])->name('notice_regist-vd_input');

    // 削除処理
    Route::post('/notice_regist/delete', [NoticeRegistController::class, 'delete'])->name('notice_regist-delete');

    //----------------------
    // お知らせ定型文登録
    //----------------------

    // 一覧画面
    Route::get('/notice_template', [NoticeTemplateController::class, 'index'])->name('notice_template');

    // 検索結果取得
    Route::post('/notice_template/search', [NoticeTemplateController::class, 'search'])->name('notice_template-search');

    // 詳細取得用
    Route::post('/notice_template/get_data', [NoticeTemplateController::class, 'getData'])->name('notice_template-get_data');

    // お知らせ定型文登録
    Route::get('/notice_template/new', [NoticeTemplateController::class, 'new'])->name('notice_template-new');

    // 新規登録処理
    Route::post('/notice_template/create', [NoticeTemplateController::class, 'create'])->name('notice_template-create');

    // お知らせ定型文編集
    Route::get('/notice_template/edit/{templateId}', [NoticeTemplateController::class, 'edit'])->name('notice_template-edit');

    // 編集処理
    Route::post('/notice_template/update', [NoticeTemplateController::class, 'update'])->name('notice_template-update');

    // バリデーション(登録用)
    Route::post('/notice_template/vd_input', [NoticeTemplateController::class, 'validationForInput'])->name('notice_template-vd_input');

    // 削除処理
    Route::post('/notice_template/delete', [NoticeTemplateController::class, 'delete'])->name('notice_template-delete');

    //---------------------
    // 特別期間講習管理
    //---------------------

    // 講習情報一覧
    Route::get('/season_mng', [SeasonMngController::class, 'index'])->name('season_mng');

    // 実行結果取得
    Route::post('/season_mng/search', [SeasonMngController::class, 'search'])->name('season_mng-search');

    // 詳細取得用
    Route::post('/season_mng/get_data', [SeasonMngController::class, 'getData'])->name('season_mng-get_data');

    // モーダル処理
    Route::post('/season_mng/exec_modal', [SeasonMngController::class, 'execModal'])->name('season_mng-exec_modal');

    // 受付期間登録画面
    Route::get('/season_mng/edit/{seasonMngId}', [SeasonMngController::class, 'edit'])->name('season_mng-edit');

    // 編集処理
    Route::post('/season_mng/update', [SeasonMngController::class, 'update'])->name('season_mng-update');

    // バリデーション(登録用)
    Route::post('/season_mng/vd_input', [SeasonMngController::class, 'validationForInput'])->name('season_mng-vd_input');

    //---------------------
    // 特別期間講習管理（生徒・講師）
    //---------------------

    // 生徒日程一覧
    Route::get('/season_mng_student', [SeasonMngStudentController::class, 'index'])->name('season_mng_student');

    // バリデーション(検索用)
    Route::post('/season_mng_student/vd_search', [SeasonMngStudentController::class, 'validationForSearch'])->name('season_mng_student-vd_search');

    // 検索結果取得
    Route::post('/season_mng_student/search', [SeasonMngStudentController::class, 'search'])->name('season_mng_student-search');

    // 生徒日程詳細
    Route::get('/season_mng_student/detail/{seasonStudentId}', [SeasonMngStudentController::class, 'detail'])->name('season_mng_student-detail');

    // 生徒日程詳細 編集処理（ステータス更新）
    Route::post('/season_mng_student/update', [SeasonMngStudentController::class, 'update'])->name('season_mng_student-update');

    // 生徒日程詳細 - バリデーション(ステータス更新用)
    Route::post('/season_mng_student/vd_input', [SeasonMngStudentController::class, 'validationForInput'])->name('season_mng_student-vd_input');

    // 生徒科目別コマ組み
    Route::get('/season_mng_student/detail/{seasonStudentId}/plan/{subjectCd}', [SeasonMngStudentController::class, 'plan'])->name('season_mng_student-plan');

    // 生徒科目別コマ組み登録処理
    Route::post('/season_mng_student/create_plan', [SeasonMngStudentController::class, 'createPlan'])->name('season_mng_student-create_plan');

    // 生徒科目別コマ組み編集 - バリデーション(登録用)
    Route::post('/season_mng_student/vd_input_plan', [SeasonMngStudentController::class, 'validationForInputPlan'])->name('season_mng_student-vd_input_plan');

    // 生徒科目別コマ組み - 講師一覧
    Route::post('/season_mng_student/get_data_select_tutor', [SeasonMngStudentController::class, 'getDataSelectTutor'])->name('season_mng_student-get_data_select_tutor');

    // 講師日程一覧
    Route::get('/season_mng_tutor', [SeasonMngTutorController::class, 'index'])->name('season_mng_tutor');

    // バリデーション(検索用)
    Route::post('/season_mng_tutor/vd_search', [SeasonMngTutorController::class, 'validationForSearch'])->name('season_mng_tutor-vd_search');

    // 検索結果取得
    Route::post('/season_mng_tutor/search', [SeasonMngTutorController::class, 'search'])->name('season_mng_tutor-search');

    // 講師日程詳細
    Route::get('/season_mng_tutor/detail/{seasonTutorId}', [SeasonMngTutorController::class, 'detail'])->name('season_mng_tutor-detail');

    //---------------------
    // 成績情報出力
    //---------------------

    // 一覧画面
    Route::get('/grade_example', [GradeExampleController::class, 'index'])->name('grade_example');

    // バリデーション(検索用)
    Route::post('/grade_example/vd_search', [GradeExampleController::class, 'validationForSearch'])->name('grade_example-vd_search');

    // 検索結果取得
    Route::post('/grade_example/search', [GradeExampleController::class, 'search'])->name('tgrade_example-search');

    // 詳細取得用
    Route::post('/grade_example/get_data', [GradeExampleController::class, 'getData'])->name('grade_example-get_data');

    //---------------------
    // 研修管理
    //---------------------

    // 一覧画面
    Route::get('/training_mng', [TrainingMngController::class, 'index'])->name('training_mng');

    // バリデーション(検索用)
    Route::post('/training_mng/vd_search', [TrainingMngController::class, 'validationForSearch'])->name('training_mng-vd_search');

    // 検索結果取得
    Route::post('/training_mng/search', [TrainingMngController::class, 'search'])->name('training_mng-search');

    // 研修教材登録
    Route::get('/training_mng/new', [TrainingMngController::class, 'new'])->name('training_mng-new');

    // 新規登録処理
    Route::post('/training_mng/create', [TrainingMngController::class, 'create'])->name('training_mng-create');

    // 研修教材編集
    Route::get('/training_mng/edit/{trnId}', [TrainingMngController::class, 'edit'])->name('training_mng-edit');

    // 編集処理
    Route::post('/training_mng/update', [TrainingMngController::class, 'update'])->name('training_mng-update');

    // バリデーション(登録用)
    Route::post('/training_mng/vd_input', [TrainingMngController::class, 'validationForInput'])->name('training_mng-vd_input');

    // 削除処理
    Route::post('/training_mng/delete', [TrainingMngController::class, 'delete'])->name('training_mng-delete');

    // 研修閲覧状況確認
    Route::get('/training_mng/state/{trnId}', [TrainingMngController::class, 'state'])->name('training_mng-state');

    // 検索結果取得
    Route::post('/training_mng/search_state', [TrainingMngController::class, 'searchState'])->name('training_mng-search_state');

    //---------------------
    // 追加請求申請受付
    //---------------------

    // 一覧画面
    Route::get('/surcharge_accept', [SurchargeAcceptController::class, 'index'])->name('surcharge_accept');

    // バリデーション(検索用)
    Route::post('/surcharge_accept/vd_search', [SurchargeAcceptController::class, 'validationForSearch'])->name('surcharge_accept-vd_search');

    // 検索結果取得
    Route::post('/surcharge_accept/search', [SurchargeAcceptController::class, 'search'])->name('surcharge_accept-search');

    // 詳細取得用
    Route::post('/surcharge_accept/get_data', [SurchargeAcceptController::class, 'getData'])->name('surcharge_accept-get_data');

    // モーダル処理
    Route::post('/surcharge_accept/exec_modal', [SurchargeAcceptController::class, 'execModal'])->name('surcharge_accept-exec_modal');

    // 編集
    Route::get('/surcharge_accept/edit/{surchargeId}', [SurchargeAcceptController::class, 'edit'])->name('surcharge_accept-edit');

    // 編集処理
    Route::post('/surcharge_accept/update', [SurchargeAcceptController::class, 'update'])->name('surcharge_accept-update');

    // バリデーション(登録用)
    Route::post('/surcharge_accept/vd_input', [SurchargeAcceptController::class, 'validationForInput'])->name('surcharge_accept-vd_input');

    // 削除処理
    Route::post('/surcharge_accept/delete', [SurchargeAcceptController::class, 'delete'])->name('surcharge_accept-delete');

    //---------------------
    // 超過勤務者一覧
    //---------------------

    // 超過勤務者一覧
    Route::get('/overtime', [OvertimeController::class, 'index'])->name('overtime');

    // バリデーション(検索用)
    Route::post('/overtime/vd_search', [OvertimeController::class, 'validationForSearch'])->name('overtime-vd_search');

    // 検索結果取得
    Route::post('/overtime/search', [OvertimeController::class, 'search'])->name('overtime-search');

    //});

    //===============================================
    // 以下は全体管理者のみアクセス可とする
    //===============================================

    //Route::group(['middleware' => ['auth', 'can:allAdmin']], function () {

    //---------------------
    // 給与算出
    //---------------------

    // 給与算出一覧
    Route::get('/salary_calculation', [SalaryCalculationController::class, 'index'])->name('salary_calculation');

    // 検索結果取得
    Route::post('/salary_calculation/search', [SalaryCalculationController::class, 'search'])->name('salary_calculation-search');

    // 給与算出情報一覧（対象月の詳細）
    Route::get('/salary_calculation/detail/{date}', [SalaryCalculationController::class, 'detail'])->name('salary_calculation-detail');

    // 給与算出情報一覧 - 検索結果取得
    Route::post('/salary_calculation/search_detail', [SalaryCalculationController::class, 'searchDetail'])->name('salary_calculation-search_detail');

    // 給与算出情報一覧 - 詳細取得用
    Route::post('/salary_calculation/get_data_detail', [SalaryCalculationController::class, 'getDataDetail'])->name('salary_calculation-get_data_detail');

    //----------------------
    // 給与情報取込
    //----------------------

    // 一覧画面
    Route::get('/salary_import', [SalaryImportController::class, 'index'])->name('salary_import');

    // 検索結果取得
    Route::post('/salary_import/search', [SalaryImportController::class, 'search'])->name('salary_import-search');

    // 給与情報取込画面
    Route::get('/salary_import/import/{salaryDate}', [SalaryImportController::class, 'import'])->name('salary_import-import');

    // 新規登録処理
    Route::post('/salary_import/create', [SalaryImportController::class, 'create'])->name('salary_import-create');

    // バリデーション(登録用)
    Route::post('/salary_import/vd_input', [SalaryImportController::class, 'validationForInput'])->name('salary_import-vd_input');

    //---------------------
    // 請求情報取込
    //---------------------

    // 一覧画面
    Route::get('/invoice_import', [InvoiceImportController::class, 'index'])->name('invoice_import');

    // 検索結果取得
    Route::post('/invoice_import/search', [InvoiceImportController::class, 'search'])->name('invoice_import-search');

    // 取込画面
    Route::get('/invoice_import/import/{invoiceDate}', [InvoiceImportController::class, 'import'])->name('invoice_import-import');

    // 新規登録処理
    Route::post('/invoice_import/create', [InvoiceImportController::class, 'create'])->name('invoice_import-create');

    // バリデーション(登録用)
    Route::post('/invoice_import/vd_input', [InvoiceImportController::class, 'validationForInput'])->name('invoice_import-vd_input');

    //---------------------
    // 管理者アカウント管理
    //---------------------

    // 一覧画面
    Route::get('/account_mng', [AccountMngController::class, 'index'])->name('account_mng');

    // バリデーション(検索用)
    Route::post('/account_mng/vd_search', [AccountMngController::class, 'validationForSearch'])->name('account_mng-vd_search');

    // 検索結果取得
    Route::post('/account_mng/search', [AccountMngController::class, 'search'])->name('account_mng-search');

    // 登録画面
    Route::get('/account_mng/new', [AccountMngController::class, 'new'])->name('account_mng-new');

    // 新規登録処理
    Route::post('/account_mng/create', [AccountMngController::class, 'create'])->name('account_mng-create');

    // 編集画面
    Route::get('/account_mng/edit/{admId}', [AccountMngController::class, 'edit'])->name('account_mng-edit');

    // 編集処理
    Route::post('/account_mng/update', [AccountMngController::class, 'update'])->name('account_mng-update');

    // バリデーション(登録用)
    Route::post('/account_mng/vd_input', [AccountMngController::class, 'validationForInput'])->name('account_mng-vd_input');

    // 削除処理
    Route::post('/account_mng/delete', [AccountMngController::class, 'delete'])->name('account_mng-delete');

    //---------------------
    // 校舎マスタ
    //---------------------
    // 一覧
    Route::get('/master_mng_campus', [MasterMngCampusController::class, 'index'])->name('master_mng_campus');

    // 検索結果取得
    Route::post('/master_mng_campus/search', [MasterMngCampusController::class, 'search'])->name('master_mng_campus-search');

    // 詳細取得用
    //Route::post('/master_mng_campus/get_data', [MasterMngCampusController::class, 'getData'])->name('master_mng_campus-get_data');

    // 登録
    Route::get('/master_mng_campus/new', [MasterMngCampusController::class, 'new'])->name('master_mng_campus-new');

    // 登録処理
    Route::post('/master_mng_campus/create', [MasterMngCampusController::class, 'create'])->name('master_mng_campus-create');

    // 編集
    Route::get('/master_mng_campus/edit/{campusId}', [MasterMngCampusController::class, 'edit'])->name('master_mng_campus-edit');

    // 編集処理
    Route::post('/master_mng_campus/update', [MasterMngCampusController::class, 'update'])->name('master_mng_campus-update');

    // バリデーション(登録用)
    Route::post('/master_mng_campus/vd_input', [MasterMngCampusController::class, 'validationForInput'])->name('master_mng_campus-vd_input');

    // 削除処理
    Route::post('/master_mng_campus/delete', [MasterMngCampusController::class, 'delete'])->name('master_mng_campus-delete');

    //---------------------
    // ブースマスタ
    //---------------------
    // 一覧
    Route::get('/master_mng_booth', [MasterMngBoothController::class, 'index'])->name('master_mng_booth');

    // バリデーション(検索用)
    Route::post('/master_mng_booth/vd_search', [MasterMngBoothController::class, 'validationForSearch'])->name('master_mng_booth-vd_search');

    // 検索結果取得
    Route::post('/master_mng_booth/search', [MasterMngBoothController::class, 'search'])->name('master_mng_booth-search');

    // 詳細取得用
    //Route::post('/master_mng_booth/get_data', [MasterMngBoothController::class, 'getData'])->name('master_mng_booth-get_data');

    // 登録
    Route::get('/master_mng_booth/new', [MasterMngBoothController::class, 'new'])->name('master_mng_booth-new');

    // 登録処理
    Route::post('/master_mng_booth/create', [MasterMngBoothController::class, 'create'])->name('master_mng_booth-create');

    // 編集
    Route::get('/master_mng_booth/edit/{boothId}', [MasterMngBoothController::class, 'edit'])->name('master_mng_booth-edit');

    // 編集処理
    Route::post('/master_mng_booth/update', [MasterMngBoothController::class, 'update'])->name('master_mng_booth-update');

    // バリデーション(登録用)
    Route::post('/master_mng_booth/vd_input', [MasterMngBoothController::class, 'validationForInput'])->name('master_mng_booth-vd_input');

    // 削除処理
    Route::post('/master_mng_booth/delete', [MasterMngBoothController::class, 'delete'])->name('master_mng_booth-delete');

    //---------------------
    // 時間割マスタ
    //---------------------
    // 一覧
    Route::get('/master_mng_timetable', [MasterMngTimetableController::class, 'index'])->name('master_mng_timetable');

    // バリデーション(検索用)
    Route::post('/master_mng_timetable/vd_search', [MasterMngTimetableController::class, 'validationForSearch'])->name('master_mng_timetable-vd_search');

    // 検索結果取得
    Route::post('/master_mng_timetable/search', [MasterMngTimetableController::class, 'search'])->name('master_mng_timetable-search');

    // 詳細取得用
    //Route::post('/master_mng_timetable/get_data', [MasterMngTimetableController::class, 'getData'])->name('master_mng_timetable-get_data');

    // 登録
    Route::get('/master_mng_timetable/new', [MasterMngTimetableController::class, 'new'])->name('master_mng_timetable-new');

    // 登録処理
    Route::post('/master_mng_timetable/create', [MasterMngTimetableController::class, 'create'])->name('master_mng_timetable-create');

    // 編集
    Route::get('/master_mng_timetable/edit/{timetableId}', [MasterMngTimetableController::class, 'edit'])->name('master_mng_timetable-edit');

    // 編集処理
    Route::post('/master_mng_timetable/update', [MasterMngTimetableController::class, 'update'])->name('master_mng_timetable-update');

    // バリデーション(登録用)
    Route::post('/master_mng_timetable/vd_input', [MasterMngTimetableController::class, 'validationForInput'])->name('master_mng_timetable-vd_input');

    // 削除処理
    Route::post('/master_mng_timetable/delete', [MasterMngTimetableController::class, 'delete'])->name('master_mng_timetable-delete');

    //---------------------
    // コースマスタ
    //---------------------
    // 一覧
    Route::get('/master_mng_course', [MasterMngCourseController::class, 'index'])->name('master_mng_course');

    // 検索結果取得
    Route::post('/master_mng_course/search', [MasterMngCourseController::class, 'search'])->name('master_mng_course-search');

    // 詳細取得用
    //Route::post('/master_mng_course/get_data', [MasterMngCourseController::class, 'getData'])->name('master_mng_course-get_data');

    // 登録
    Route::get('/master_mng_course/new', [MasterMngCourseController::class, 'new'])->name('master_mng_course-new');

    // 登録処理
    Route::post('/master_mng_course/create', [MasterMngCourseController::class, 'create'])->name('master_mng_course-create');

    // 編集
    Route::get('/master_mng_course/edit/{courseId}', [MasterMngCourseController::class, 'edit'])->name('master_mng_course-edit');

    // 編集処理
    Route::post('/master_mng_course/update', [MasterMngCourseController::class, 'update'])->name('master_mng_course-update');

    // バリデーション(登録用)
    Route::post('/master_mng_course/vd_input', [MasterMngCourseController::class, 'validationForInput'])->name('master_mng_course-vd_input');

    // 削除処理
    Route::post('/master_mng_course/delete', [MasterMngCourseController::class, 'delete'])->name('master_mng_course-delete');

    // バリデーション(削除用)
    Route::post('/master_mng_course/vd_delete', [MasterMngCourseController::class, 'validationForDelete'])->name('master_mng_course-vd_delete');

    //---------------------
    // 授業科目マスタ
    //---------------------
    // 一覧
    Route::get('/master_mng_subject', [MasterMngSubjectController::class, 'index'])->name('master_mng_subject');

    // 検索結果取得
    Route::post('/master_mng_subject/search', [MasterMngSubjectController::class, 'search'])->name('master_mng_subject-search');

    // 詳細取得用
    //Route::post('/master_mng_subject/get_data', [MasterMngSubjectController::class, 'getData'])->name('master_mng_subject-get_data');

    // 登録
    Route::get('/master_mng_subject/new', [MasterMngSubjectController::class, 'new'])->name('master_mng_subject-new');

    // 登録処理
    Route::post('/master_mng_subject/create', [MasterMngSubjectController::class, 'create'])->name('master_mng_subject-create');

    // 編集
    Route::get('/master_mng_subject/edit/{subjectId}', [MasterMngSubjectController::class, 'edit'])->name('master_mng_subject-edit');

    // 編集処理
    Route::post('/master_mng_subject/update', [MasterMngSubjectController::class, 'update'])->name('master_mng_subject-update');

    // バリデーション(登録用)
    Route::post('/master_mng_subject/vd_input', [MasterMngSubjectController::class, 'validationForInput'])->name('master_mng_subject-vd_input');

    // 削除処理
    Route::post('/master_mng_subject/delete', [MasterMngSubjectController::class, 'delete'])->name('master_mng_subject-delete');

    //---------------------
    // 成績科目マスタ
    //---------------------
    // 一覧
    Route::get('/master_mng_grade_subject', [MasterMngGradeSubjectController::class, 'index'])->name('master_mng_grade_subject');

    // 検索結果取得
    Route::post('/master_mng_grade_subject/search', [MasterMngGradeSubjectController::class, 'search'])->name('master_mng_grade_subject-search');

    // 詳細取得用
    //Route::post('/master_mng_grade_subject/get_data', [MasterMngGradeSubjectController::class, 'getData'])->name('master_mng_grade_subject-get_data');

    // 登録
    Route::get('/master_mng_grade_subject/new', [MasterMngGradeSubjectController::class, 'new'])->name('master_mng_grade_subject-new');

    // 登録処理
    Route::post('/master_mng_grade_subject/create', [MasterMngGradeSubjectController::class, 'create'])->name('master_mng_grade_subject-create');

    // 編集
    Route::get('/master_mng_grade_subject/edit/{gradeSubjectId}', [MasterMngGradeSubjectController::class, 'edit'])->name('master_mng_grade_subject-edit');

    // 編集処理
    Route::post('/master_mng_grade_subject/update', [MasterMngGradeSubjectController::class, 'update'])->name('master_mng_grade_subject-update');

    // バリデーション(登録用)
    Route::post('/master_mng_grade_subject/vd_input', [MasterMngGradeSubjectController::class, 'validationForInput'])->name('master_mng_grade_subject-vd_input');

    // 削除処理
    Route::post('/master_mng_grade_subject/delete', [MasterMngGradeSubjectController::class, 'delete'])->name('master_mng_grade_subject-delete');

    //---------------------
    // 授業教材マスタ
    //---------------------
    // 一覧
    Route::get('/master_mng_text', [MasterMngTextController::class, 'index'])->name('master_mng_text');

    // バリデーション(検索用)
    Route::post('/master_mng_text/vd_search', [MasterMngTextController::class, 'validationForSearch'])->name('master_mng_text-vd_search');

    // 検索結果取得
    Route::post('/master_mng_text/search', [MasterMngTextController::class, 'search'])->name('master_mng_text-search');

    // 詳細取得用
    //Route::post('/master_mng_text/get_data', [MasterMngTextController::class, 'getData'])->name('master_mng_text-get_data');

    // 登録
    Route::get('/master_mng_text/new', [MasterMngTextController::class, 'new'])->name('master_mng_text-new');

    // 登録処理
    Route::post('/master_mng_text/create', [MasterMngTextController::class, 'create'])->name('master_mng_text-create');

    // 編集
    Route::get('/master_mng_text/edit/{textId}', [MasterMngTextController::class, 'edit'])->name('master_mng_text-edit');

    // 編集処理
    Route::post('/master_mng_text/update', [MasterMngTextController::class, 'update'])->name('master_mng_text-update');

    // バリデーション(登録用)
    Route::post('/master_mng_text/vd_input', [MasterMngTextController::class, 'validationForInput'])->name('master_mng_text-vd_input');

    // 削除処理
    Route::post('/master_mng_text/delete', [MasterMngTextController::class, 'delete'])->name('master_mng_text-delete');

    // バリデーション(削除用)
    Route::post('/master_mng_text/vd_delete', [MasterMngTextController::class, 'validationForDelete'])->name('master_mng_text-vd_delete');

    //---------------------
    // 授業単元分類マスタ
    //---------------------
    // 一覧
    Route::get('/master_mng_category', [MasterMngCategoryController::class, 'index'])->name('master_mng_category');

    // バリデーション(検索用)
    Route::post('/master_mng_category/vd_search', [MasterMngCategoryController::class, 'validationForSearch'])->name('master_mng_category-vd_search');

    // 検索結果取得
    Route::post('/master_mng_category/search', [MasterMngCategoryController::class, 'search'])->name('master_mng_category-search');

    // 詳細取得用
    //Route::post('/master_mng_category/get_data', [MasterMngCategoryController::class, 'getData'])->name('master_mng_category-get_data');

    // 登録
    Route::get('/master_mng_category/new', [MasterMngCategoryController::class, 'new'])->name('master_mng_category-new');

    // 登録処理
    Route::post('/master_mng_category/create', [MasterMngCategoryController::class, 'create'])->name('master_mng_category-create');

    // 編集
    Route::get('/master_mng_category/edit/{unitId}', [MasterMngCategoryController::class, 'edit'])->name('master_mng_category-edit');

    // 編集処理
    Route::post('/master_mng_category/update', [MasterMngCategoryController::class, 'update'])->name('master_mng_category-update');

    // バリデーション(登録用)
    Route::post('/master_mng_category/vd_input', [MasterMngCategoryController::class, 'validationForInput'])->name('master_mng_category-vd_input');

    // 削除処理
    Route::post('/master_mng_category/delete', [MasterMngCategoryController::class, 'delete'])->name('master_mng_category-delete');

    //---------------------
    // 授業単元マスタ
    //---------------------
    // 一覧
    Route::get('/master_mng_unit', [MasterMngUnitController::class, 'index'])->name('master_mng_unit');

    // バリデーション(検索用)
    Route::post('/master_mng_unit/vd_search', [MasterMngUnitController::class, 'validationForSearch'])->name('master_mng_unit-vd_search');

    // 検索結果取得
    Route::post('/master_mng_unit/search', [MasterMngUnitController::class, 'search'])->name('master_mng_unit-search');

    // 詳細取得用
    //Route::post('/master_mng_unit/get_data', [MasterMngUnitController::class, 'getData'])->name('master_mng_unit-get_data');

    // 登録
    Route::get('/master_mng_unit/new', [MasterMngUnitController::class, 'new'])->name('master_mng_unit-new');

    // 登録処理
    Route::post('/master_mng_unit/create', [MasterMngUnitController::class, 'create'])->name('master_mng_unit-create');

    // 編集
    Route::get('/master_mng_unit/edit/{unitId}', [MasterMngUnitController::class, 'edit'])->name('master_mng_unit-edit');

    // 編集処理
    Route::post('/master_mng_unit/update', [MasterMngUnitController::class, 'update'])->name('master_mng_unit-update');

    // バリデーション(登録用)
    Route::post('/master_mng_unit/vd_input', [MasterMngUnitController::class, 'validationForInput'])->name('master_mng_unit-vd_input');

    // 削除処理
    Route::post('/master_mng_unit/delete', [MasterMngUnitController::class, 'delete'])->name('master_mng_unit-delete');

    //---------------------
    // システムマスタ モック
    //---------------------
    // 一覧
    Route::get('/master_mng_system', [MasterMngSystemController::class, 'index'])->name('master_mng_system');

    // 検索結果取得
    Route::post('/master_mng_system/search', [MasterMngSystemController::class, 'search'])->name('master_mng_system-search');

    // 編集
    Route::get('/master_mng_system/edit/{systemId}', [MasterMngSystemController::class, 'edit'])->name('master_mng_system-edit');

    // 編集処理
    Route::post('/master_mng_system/update', [MasterMngSystemController::class, 'update'])->name('master_mng_system-update');

    // バリデーション(登録用)
    Route::post('/master_mng_system/vd_input', [MasterMngSystemController::class, 'validationForInput'])->name('master_mng_system-vd_input');

    //----------------------
    // 生徒学年情報更新
    //----------------------

    // 取込
    Route::get('/all_member_import', [AllMemberImportController::class, 'index'])->name('all_member_import');

    // 取込処理
    Route::post('/all_member_import/create', [AllMemberImportController::class, 'create'])->name('all_member_import-create');

    // バリデーション(取込用)
    Route::post('/all_member_import/vd_input', [AllMemberImportController::class, 'validationForInput'])->name('all_member_import-vd_input');

    // 検索結果取得
    Route::post('/all_member_import/search', [AllMemberImportController::class, 'search'])->name('all_member_import-search');

    //----------------------
    // 振替残数リセット処理
    //----------------------

    // 取込
    Route::get('/transfer_reset', [TransferResetController::class, 'index'])->name('transfer_reset');

    // 取込処理
    Route::post('/transfer_reset/create', [TransferResetController::class, 'create'])->name('transfer_reset-create');

    // バリデーション(取込用)
    Route::post('/transfer_reset/vd_input', [TransferResetController::class, 'validationForInput'])->name('transfer_reset-vd_input');

    // 検索結果取得
    Route::post('/transfer_reset/search', [TransferResetController::class, 'search'])->name('transfer_reset-search');

    //----------------------
    // 保持期限超過データ削除管理
    //----------------------

    // 取込
    Route::get('/data_reset', [DataResetController::class, 'index'])->name('data_reset');

    // 取込処理
    Route::post('/data_reset/create', [DataResetController::class, 'create'])->name('data_reset-create');

    // バリデーション(取込用)
    Route::post('/data_reset/vd_input', [DataResetController::class, 'validationForInput'])->name('data_reset-vd_input');

    // 検索結果取得
    Route::post('/data_reset/search', [DataResetController::class, 'search'])->name('data_reset-search');

    //---------------------
    // 学校コード取込
    //---------------------

    // 取込画面
    Route::get('/import_school_code', [ImportSchoolCodeController::class, 'index'])->name('import_school_code');

    // 新規登録処理
    Route::post('/import_school_code/create', [ImportSchoolCodeController::class, 'create'])->name('import_school_code-create');

    // バリデーション(登録用)
    Route::post('/import_school_code/vd_input', [ImportSchoolCodeController::class, 'validationForInput'])->name('import_school_code-vd_input');

    //----------------------
    // 年度スケジュール取込
    //----------------------

    // 取込一覧
    Route::get('/year_schedule_import', [YearScheduleImportController::class, 'index'])->name('year_schedule_import');

    // 取り込み
    Route::get('/year_schedule_import/import/{YearScheduleDate}', [YearScheduleImportController::class, 'import'])->name('year_schedule_import-import');

    // 取込処理
    Route::post('/year_schedule_import/create', [YearScheduleImportController::class, 'create'])->name('year_schedule_import-create');

    // バリデーション(取込用)
    Route::post('/year_schedule_import/vd_input', [YearScheduleImportController::class, 'validationForInput'])->name('year_schedule_import-vd_input');

    // 検索結果取得
    Route::post('/year_schedule_import/search', [YearScheduleImportController::class, 'search'])->name('year_schedule_import-search');
});
