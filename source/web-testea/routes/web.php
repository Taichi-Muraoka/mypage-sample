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
use App\Http\Controllers\Student\AbsentController;
use App\Http\Controllers\Student\ReportController;
use App\Http\Controllers\Student\GradesController;
use App\Http\Controllers\Student\EventController;
use App\Http\Controllers\Student\CardController;
use App\Http\Controllers\Student\AgreementController;
use App\Http\Controllers\Student\CourseController;
use App\Http\Controllers\Student\InvoiceController;
use App\Http\Controllers\Student\LeaveController;
use App\Http\Controllers\Student\ConferenceController;
use App\Http\Controllers\Student\TransferStudentController;
use App\Http\Controllers\Student\ExtraLessonController;
use App\Http\Controllers\Student\SeasonStudentController;

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
    // 欠席申請
    //---------------------

    // 申請
    Route::get('/absent', [AbsentController::class, 'index'])->name('absent');

    // 申請(直接ID付きで選択された状態にする)
    Route::get('/absent/{scheduleId}', [AbsentController::class, 'direct'])->name('absent-direct');

    // 授業日時プルダウンを選択された際に教室・教師の情報を返却する
    Route::post('/absent/get_data_select', [AbsentController::class, 'getDataSelect'])->name('absent-get_data_select');

    // 新規登録処理
    Route::post('/absent/create', [AbsentController::class, 'create'])->name('absent-create');

    // バリデーション(登録用)
    Route::post('/absent/vd_input', [AbsentController::class, 'validationForInput'])->name('absent-vd_input');

    //---------------------
    // 授業報告書
    //---------------------

    // 一覧
    Route::get('/report', [ReportController::class, 'index'])->name('report');

    // 検索結果取得
    Route::post('/report/search', [ReportController::class, 'search'])->name('report-search');

    // 詳細取得用
    Route::post('/report/get_data', [ReportController::class, 'getData'])->name('report-get_data');

    // 授業報告書 コメント登録
    Route::get('/report/edit/{reportId}', [ReportController::class, 'edit'])->name('report-edit');

    // バリデーション(登録用)
    Route::post('/report/vd_input', [ReportController::class, 'validationForInput'])->name('report-vd_input');

    // 編集処理
    Route::post('/report/update', [ReportController::class, 'update'])->name('report-update');

    //---------------------
    // 生徒成績
    //---------------------

    // 一覧
    Route::get('/grades', [GradesController::class, 'index'])->name('grades');

    // 検索結果取得
    Route::post('/grades/search', [GradesController::class, 'search'])->name('grades-search');

    // 詳細取得用
    Route::post('/grades/get_data', [GradesController::class, 'getData'])->name('grades-get_data');

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
    // 模試・イベント申込
    //---------------------

    // 申込
    Route::get('/event', [EventController::class, 'index'])->name('event');

    // 申込(直接ID付きで選択された状態にする)
    Route::get('/event/{type}/{tmidEventId}', [EventController::class, 'direct'])->name('event-direct');

    // 新規登録処理
    Route::post('/event/create', [EventController::class, 'create'])->name('event-create');

    // バリデーション(登録用)
    Route::post('/event/vd_input', [EventController::class, 'validationForInput'])->name('event-vd_input');

    //---------------------
    // ギフトカード
    //---------------------

    // 一覧
    Route::get('/card', [CardController::class, 'index'])->name('card');

    // 検索結果取得
    Route::post('/card/search', [CardController::class, 'search'])->name('card-search');

    // 詳細取得用
    Route::post('/card/get_data', [CardController::class, 'getData'])->name('card-get_data');

    // ギフトカード使用申請
    Route::get('/card/use/{cardId}', [CardController::class, 'use'])->name('card-use');

    // 編集処理
    Route::post('/card/update', [CardController::class, 'update'])->name('card-update');

    // バリデーション(登録用)
    Route::post('/card/vd_input', [CardController::class, 'validationForInput'])->name('card-vd_input');

    //---------------------
    // 契約内容
    //---------------------

    // 一覧
    Route::get('/agreement', [AgreementController::class, 'index'])->name('agreement');

    // 詳細取得用
    Route::post('/agreement/get_data', [AgreementController::class, 'getData'])->name('agreement-get_data');

    //---------------------
    // コース変更・授業追加申請
    //---------------------

    // 申請
    Route::get('/course', [CourseController::class, 'index'])->name('course');

    // お知らせからの短期講習申込
    Route::get('/course/short-term', [CourseController::class, 'direct'])->name('course-direct');

    // 編集処理
    Route::post('/course/update', [CourseController::class, 'update'])->name('course-update');

    // バリデーション(登録用)
    Route::post('/course/vd_input', [CourseController::class, 'validationForInput'])->name('course-vd_input');

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

    //---------------------
    // 退会
    //---------------------

    // 申請
    Route::get('/leave', [LeaveController::class, 'index'])->name('leave');

    // 退会処理
    Route::post('/leave/update', [LeaveController::class, 'update'])->name('leave-update');

    // バリデーション(退会用)
    Route::post('/leave/vd_input', [LeaveController::class, 'validationForInput'])->name('leave-vd_input');

    //---------------------
    // 面談日程調整
    //---------------------

    // 申請
    Route::get('/conference', [ConferenceController::class, 'index'])->name('conference');

    // 申請(直接ID付きで選択された状態にする)
    Route::get('/conference/{scheduleId}', [ConferenceController::class, 'direct'])->name('conference-direct');

    // 授業日時プルダウンを選択された際に教室・教師の情報を返却する →不要？

    // 新規登録処理
    Route::post('/conference/create', [ConferenceController::class, 'create'])->name('conference-create');

    // バリデーション(登録用)
    Route::post('/conference/vd_input', [ConferenceController::class, 'validationForInput'])->name('conference-vd_input');

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

    // 新規登録処理
    Route::post('/transfer_student/create', [TransferStudentController::class, 'create'])->name('transfer_student-create');

    // 振替日承認
    Route::get('/transfer_student/edit/{transferId}', [TransferStudentController::class, 'edit'])->name('transfer_student-edit');

    // 編集処理
    Route::post('/transfer_student/update', [TransferStudentController::class, 'update'])->name('transfer_student-update');

    // バリデーション(登録用)
    Route::post('/transfer_student/vd_input', [TransferStudentController::class, 'validationForInput'])->name('transfer_student-vd_input');

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
    // 特別期間講習日程連絡
    //---------------------

    // 日程連絡一覧
    Route::get('/season_student', [SeasonStudentController::class, 'index'])->name('season_student');

    // バリデーション(検索用)
    Route::post('/season_student/vd_search', [SeasonStudentController::class, 'validationForSearch'])->name('season_student-vd_search');

    // 検索結果取得
    Route::post('/season_student/search', [SeasonStudentController::class, 'search'])->name('season_student-search');

    // 提出スケジュール詳細
    Route::get('/season_student/detail/{sid}', [SeasonStudentController::class, 'detail'])->name('season_student-detail');

    // 日程登録画面
    Route::get('/season_student/new', [SeasonStudentController::class, 'new'])->name('season_student-new');

    // 新規登録処理
    Route::post('/season_student/create', [SeasonStudentController::class, 'create'])->name('season_student-create');

    // バリデーション(登録用)
    Route::post('/season_student/vd_input', [SeasonStudentController::class, 'validationForInput'])->name('season_student-vd_input');

});

//===============================================
// 教師向け(共通)
//===============================================

use App\Http\Controllers\Tutor\TransferController;
use App\Http\Controllers\Tutor\ReportRegistController;
use App\Http\Controllers\Tutor\WeeklyShiftController;
use App\Http\Controllers\Tutor\TimesRegistController;
use App\Http\Controllers\Tutor\GradesCheckController;
use App\Http\Controllers\Tutor\SalaryController;
use App\Http\Controllers\Tutor\TrainingController;
use App\Http\Controllers\Tutor\TransferTutorController;
use App\Http\Controllers\Tutor\AttendanceController;
use App\Http\Controllers\Tutor\SurchargeController;
use App\Http\Controllers\Tutor\SeasonTutorController;

Route::group(['middleware' => ['auth', 'can:tutor']], function () {

    //---------------------
    // 振替申請
    //---------------------

    // 申請
    Route::get('/transfer', [TransferController::class, 'index'])->name('transfer');

    // 新規登録処理
    Route::post('/transfer/create', [TransferController::class, 'create'])->name('transfer-create');

    // カレンダーを選択された際に教室・教師の情報を返却する
    Route::post('/transfer/get_data_select', [TransferController::class, 'getDataSelect'])->name('transfer-get_data_select');

    // バリデーション(登録用)
    Route::post('/transfer/vd_input', [TransferController::class, 'validationForInput'])->name('transfer-vd_input');

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

    // カレンダーを選択された際に教室・教師の情報を返却する
    Route::post('/report_regist/get_data_select', [ReportRegistController::class, 'getDataSelect'])->name('report_regist-get_data_select');

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
    // 回数報告
    //---------------------

    // 登録
    Route::get('/times_regist', [TimesRegistController::class, 'index'])->name('times_regist');

    // カレンダーを選択された際に詳細を表示
    Route::post('/times_regist/get_data_select', [TimesRegistController::class, 'getDataSelect'])->name('times_regist-get_data_select');

    // 新規登録処理
    Route::post('/times_regist/create', [TimesRegistController::class, 'create'])->name('times_regist-create');

    // バリデーション(登録用)
    Route::post('/times_regist/vd_input', [TimesRegistController::class, 'validationForInput'])->name('times_regist-vd_input');

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

    // 振替希望日登録
    Route::get('/transfer_tutor/new', [TransferTutorController::class, 'new'])->name('transfer_tutor-new');

    // 新規登録処理
    Route::post('/transfer_tutor/create', [TransferTutorController::class, 'create'])->name('transfer_tutor-create');

    // 振替日承認
    Route::get('/transfer_tutor/edit/{transferId}', [TransferTutorController::class, 'edit'])->name('transfer_tutor-edit');

    // 編集処理
    Route::post('/transfer_tutor/update', [TransferTutorController::class, 'update'])->name('transfer_tutor-update');

    // バリデーション(登録用)
    Route::post('/transfer_tutor/vd_input', [TransferTutorController::class, 'validationForInput'])->name('transfer_tutor-vd_input');

    //---------------------
    // 授業実施登録
    //---------------------

    // 一覧
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');

    // 検索結果取得
    Route::post('/attendance/search', [AttendanceController::class, 'search'])->name('attendance-search');

    // 詳細取得用
    Route::post('/attendance/get_data', [AttendanceController::class, 'getData'])->name('attendance-get_data');

    // モーダル処理
    Route::post('/attendance/exec_modal', [AttendanceController::class, 'execModal'])->name('attendance-exec_modal');

    //---------------------
    // 追加請求申請 モック
    //---------------------

    // 一覧
    Route::get('/surcharge', [SurchargeController::class, 'index'])->name('surcharge');

    // 検索結果取得
    Route::post('/surcharge/search', [SurchargeController::class, 'search'])->name('surcharge-search');

    // 詳細取得用
    Route::post('/surcharge/get_data', [SurchargeController::class, 'getData'])->name('surcharge-get_data');

    // 新規登録
    Route::get('/surcharge/new', [SurchargeController::class, 'new'])->name('surcharge-new');

    // 新規登録処理
    Route::post('/surcharge/create', [SurchargeController::class, 'create'])->name('surcharge-create');

    // バリデーション(登録用)
    Route::post('/surcharge/vd_input', [SurchargeController::class, 'validationForInput'])->name('surcharge-vd_input');

    //---------------------
    // 特別期間講習日程連絡
    //---------------------

    // 日程連絡一覧
    Route::get('/season_tutor', [SeasonTutorController::class, 'index'])->name('season_tutor');

    // バリデーション(検索用)
    Route::post('/season_tutor/vd_search', [SeasonTutorController::class, 'validationForSearch'])->name('season_tutor-vd_search');

    // 検索結果取得
    Route::post('/season_tutor/search', [SeasonTutorController::class, 'search'])->name('season_tutor-search');

    // 提出スケジュール詳細
    Route::get('/season_tutor/detail/{tid}', [SeasonTutorController::class, 'detail'])->name('season_tutor-detail');

    // 日程登録画面
    Route::get('/season_tutor/new', [SeasonTutorController::class, 'new'])->name('season_tutor-new');

    // 新規登録処理
    Route::post('/season_tutor/create', [SeasonTutorController::class, 'create'])->name('season_tutor-create');

    // バリデーション(登録用)
    Route::post('/season_tutor/vd_input', [SeasonTutorController::class, 'validationForInput'])->name('season_tutor-vd_input');

});

//===============================================
// 管理者向け
//===============================================

use App\Http\Controllers\Admin\MemberMngController;
use App\Http\Controllers\Admin\CourseMngController;
use App\Http\Controllers\Admin\MemberImportController;
use App\Http\Controllers\Admin\LeaveAcceptController;
use App\Http\Controllers\Admin\TutorMngController;
use App\Http\Controllers\Admin\TutorRegistController;
use App\Http\Controllers\Admin\TimesCheckController;
use App\Http\Controllers\Admin\AbsentAcceptController;
use App\Http\Controllers\Admin\TransferAcceptController;
use App\Http\Controllers\Admin\ReportCheckController;
use App\Http\Controllers\Admin\GradesMngController;
use App\Http\Controllers\Admin\ScheduleImportController;
use App\Http\Controllers\Admin\TrialMngController;
use App\Http\Controllers\Admin\EventMngController;
use App\Http\Controllers\Admin\CardMngController;
use App\Http\Controllers\Admin\ContactMngController;
use App\Http\Controllers\Admin\TrainingMngController;
use App\Http\Controllers\Admin\InvoiceImportController;
use App\Http\Controllers\Admin\SalaryImportController;
use App\Http\Controllers\Admin\NoticeRegistController;
use App\Http\Controllers\Admin\NoticeTemplateController;
use App\Http\Controllers\Admin\AllMemberImportController;
use App\Http\Controllers\Admin\YearScheduleImportController;
use App\Http\Controllers\Admin\RoomHolidayController;
use App\Http\Controllers\Admin\MasterMngController;
use App\Http\Controllers\Admin\AccountMngController;
use App\Http\Controllers\Admin\DataMngController;
use App\Http\Controllers\Admin\RecordController;
use App\Http\Controllers\Admin\BadgeController;
use App\Http\Controllers\Admin\TransferCheckController;
use App\Http\Controllers\Admin\ConferenceAcceptController;
use App\Http\Controllers\Admin\RoomCalendarController;
use App\Http\Controllers\Admin\ProspectController;
use App\Http\Controllers\Admin\SurchargeAcceptController;
use App\Http\Controllers\Admin\DesiredMngController;
use App\Http\Controllers\Admin\AgreementMngController;
use App\Http\Controllers\Admin\TransferRequiredController;
use App\Http\Controllers\Admin\GradeExampleController;
use App\Http\Controllers\Admin\ExtraLessonMngController;
use App\Http\Controllers\Admin\SalaryCalculationController;
use App\Http\Controllers\Admin\InvoiceCalculationController;
use App\Http\Controllers\Admin\SeasonMngStudentController;
use App\Http\Controllers\Admin\SeasonMngTutorController;
use App\Http\Controllers\Admin\SeasonPlanConfirmController;
use App\Http\Controllers\Admin\SeasonPlanController;
use App\Http\Controllers\Admin\SeasonscheduleController;
use App\Http\Controllers\Admin\ImportStudentController;
use App\Http\Controllers\Admin\ImportStudentscheduleController;
use App\Http\Controllers\Admin\ImportTutorController;
use App\Http\Controllers\Admin\TransferResetController;
use App\Http\Controllers\Admin\DataResetController;
use App\Http\Controllers\Admin\ImportSchoolCodeController;
use App\Http\Controllers\Admin\OvertimeController;
use App\Http\Controllers\Admin\GiveBadgeController;
use App\Http\Controllers\Admin\MasterMngCampusController;
use App\Http\Controllers\Admin\MasterMngBoothController;
use App\Http\Controllers\Admin\MasterMngSubjectController;
use App\Http\Controllers\Admin\MasterMngTimetableController;
use App\Http\Controllers\Admin\MasterMngCourseController;
use App\Http\Controllers\Admin\MasterMngSystemController;

Route::group(['middleware' => ['auth', 'can:admin']], function () {

    //---------------------
    // 会員管理
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

    //---------------------
    // 受験校管理 モック
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
    // 契約管理 モック
    //---------------------

    // 契約管理一覧
    Route::get('/member_mng/agreement_mng/{sid}', [AgreementMngController::class, 'index'])->name('agreement_mng');

    // 詳細取得用
    Route::post('/member_mng/get_data_agreement_mng', [AgreementMngController::class, 'getData'])->name('agreement_mng-get_data');

    // 検索結果取得
    Route::post('/member_mng/search_agreement_mng', [AgreementMngController::class, 'search'])->name('agreement_mng-search');

    // バリデーション(検索用)
    Route::post('/member_mng/vd_search_agreement_mng', [AgreementMngController::class, 'validationForSearch'])->name('agreement_mng-vd_search');

    // 契約登録画面
    Route::get('/member_mng/agreement_mng/{sid}/new', [AgreementMngController::class, 'new'])->name('agreement_mng-new');

    // 新規登録処理
    Route::post('/member_mng/create_agreement_mng', [AgreementMngController::class, 'create'])->name('agreement_mng-create');

    // 契約編集画面
    Route::get('/member_mng/agreement_mng/edit/{agreementId}', [AgreementMngController::class, 'edit'])->name('agreement_mng-edit');

    // 編集処理
    Route::post('/member_mng/update_agreement_mng', [AgreementMngController::class, 'update'])->name('agreement_mng-update');

    // バリデーション(登録用)
    Route::post('/member_mng/vd_input_agreement_mng', [AgreementMngController::class, 'validationForInput'])->name('agreement_mng-vd_input');

    // 削除処理
    Route::post('/member_mng/delete_agreement_mng', [AgreementMngController::class, 'delete'])->name('agreement_mng-delete');

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
    // バッジ管理
    //---------------------

    // バッジ一覧
    Route::get('/member_mng/badge/{sid}', [BadgeController::class, 'index'])->name('badge');

    // 詳細取得用
    Route::post('/member_mng/get_data_badge', [BadgeController::class, 'getData'])->name('badge-get_data');

    // 検索結果取得
    Route::post('/member_mng/search_badge', [BadgeController::class, 'search'])->name('badge-search');

    // バリデーション(検索用)
    Route::post('/member_mng/vd_search_badge', [BadgeController::class, 'validationForSearch'])->name('badge-vd_search');

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
    // 生徒成績
    //---------------------

    // 一覧画面
    Route::get('/member_mng/grades_mng/{sid}', [GradesMngController::class, 'index'])->name('grades_mng');

    // 検索結果取得
    Route::post('/member_mng/search_grades_mng', [GradesMngController::class, 'search'])->name('grades_mng-search');

    // 詳細取得用
    Route::post('/member_mng/get_data_grades_mng', [GradesMngController::class, 'getData'])->name('grades_mng-get_data');

    // 生徒成績編集
    Route::get('/member_mng/grades_mng/edit/{gradesId}', [GradesMngController::class, 'edit'])->name('grades_mng-edit');

    // 編集処理
    Route::post('/member_mng/update_grades_mng', [GradesMngController::class, 'update'])->name('grades_mng-update');

    // バリデーション(登録用)
    Route::post('/member_mng/vd_input_grades_mng', [GradesMngController::class, 'validationForInput'])->name('grades_mng-vd_input');

    // 削除処理
    Route::post('/member_mng/delete_grades_mng', [GradesMngController::class, 'delete'])->name('grades_mng-delete');

    //---------------------
    // 教室カレンダー
    //---------------------

    // 教室カレンダー（モック）
    Route::get('/room_calendar', [RoomCalendarController::class, 'calendar'])->name('room_calendar');

    // カレンダー - 詳細取得用（モック）
    Route::post('/room_calendar/get_calendar', [RoomCalendarController::class, 'getCalendar'])->name('room_calendar-get_calendar');

    // 教室カレンダー登録画面
    //Route::get('/room_calendar/new', [RoomCalendarController::class, 'new'])->name('room_calendar-new');
    Route::get('/room_calendar/new/{roomcd}/{date}/{startTime}/{endTime}', [RoomCalendarController::class, 'new'])->name('room_calendar-new');

    // 新規登録処理
    Route::post('/room_calendar/create', [RoomCalendarController::class, 'create'])->name('room_calendar-create');

    // 教室カレンダー編集画面
    Route::get('/room_calendar/edit/{scheduleId}', [RoomCalendarController::class, 'edit'])->name('room_calendar-edit');

    // 教室カレンダーコピー登録画面
    Route::get('/room_calendar/copy/{scheduleId}', [RoomCalendarController::class, 'copy'])->name('room_calendar-copy');

    // 編集処理
    Route::post('/room_calendar/update', [RoomCalendarController::class, 'update'])->name('room_calendar-update');

    // バリデーション(登録用)
    Route::post('/room_calendar/vd_input', [RoomCalendarController::class, 'validationForInput'])->name('room_calendar-vd_input');

    // 削除処理
    Route::post('/room_calendar/delete', [RoomCalendarController::class, 'delete'])->name('room_calendar-delete');

    //---------------------
    // レギュラーカレンダー
    //---------------------

    // defaultWeekカレンダー（モック）
    Route::get('/regular_schedule', [RoomCalendarController::class, 'defaultweek'])->name('regular_schedule');

    // defaultWeekカレンダー - 詳細取得用（モック）
    Route::post('/regular_schedule/get_calendar', [RoomCalendarController::class, 'getCalendarRegular'])->name('regular_schedule-get_calendar');

    // defaultWeekカレンダー登録画面
    Route::get('/regular_schedule/new', [RoomCalendarController::class, 'weekNew'])->name('regular_schedule-new');

    // defaultWeekカレンダー編集画面
    Route::get('/regular_schedule/edit/{scheduleId}', [RoomCalendarController::class, 'weekEdit'])->name('regular_schedule-edit');

    // defaultWeekカレンダーコピー登録画面
    Route::get('/regular_schedule/copy/{scheduleId}', [RoomCalendarController::class, 'weekCopy'])->name('regular_schedule-copy');

    // バリデーション(登録用)
    Route::post('/regular_schedule/vd_input', [RoomCalendarController::class, 'validationForInput'])->name('regular_schedule-vd_input');

    // 新規登録処理
    Route::post('/regular_schedule/create', [RoomCalendarController::class, 'create'])->name('regular_schedule-create');

    // 編集処理
    Route::post('/regular_schedule/update', [RoomCalendarController::class, 'update'])->name('regular_schedule-update');

    // バリデーション(登録用)
    Route::post('/regular_schedule/vd_input', [RoomCalendarController::class, 'validationForInput'])->name('regular_schedule-vd_input');

    // 削除処理
    Route::post('/regular_schedule/delete', [RoomCalendarController::class, 'delete'])->name('regular_schedule-delete');

    //---------------------
    // イベントカレンダー
    //---------------------

    // イベントカレンダー（仮）
    Route::get('/event_calendar', [RoomCalendarController::class, 'eventCalendar'])->name('event_calendar');

    // イベントカレンダー（仮） - 詳細取得用
    Route::post('/event_calendar/get_calendar', [RoomCalendarController::class, 'getCalendarEvent'])->name('event_calendar-get_calendar');

    //---------------------
    // コース変更・授業追加受付
    //---------------------

    // 一覧画面
    Route::get('/course_mng', [CourseMngController::class, 'index'])->name('course_mng');

    // バリデーション(検索用)
    Route::post('/course_mng/vd_search', [CourseMngController::class, 'validationForSearch'])->name('course_mng-vd_search');

    // 検索結果取得
    Route::post('/course_mng/search', [CourseMngController::class, 'search'])->name('course_mng-search');

    // 詳細取得用
    Route::post('/course_mng/get_data', [CourseMngController::class, 'getData'])->name('course_mng-get_data');

    // モーダル処理
    Route::post('/course_mng/exec_modal', [CourseMngController::class, 'execModal'])->name('course_mng-exec_modal');

    // 編集画面
    Route::get('/course_mng/edit/{changeId}', [CourseMngController::class, 'edit'])->name('course_mng-edit');

    // 編集処理
    Route::post('/course_mng/update', [CourseMngController::class, 'update'])->name('course_mng-update');

    // バリデーション(登録用)
    Route::post('/course_mng/vd_input', [CourseMngController::class, 'validationForInput'])->name('course_mng-vd_input');

    // 削除処理
    Route::post('/course_mng/delete', [CourseMngController::class, 'delete'])->name('course_mng-delete');

    //---------------------
    // 会員情報取込
    //---------------------

    // 登録
    Route::get('/member_import', [MemberImportController::class, 'index'])->name('member_import');

    // バリデーション(登録用)
    Route::post('/member_import/vd_input', [MemberImportController::class, 'validationForInput'])->name('member_import-vd_input');

    // 新規登録処理
    Route::post('/member_import/create', [MemberImportController::class, 'create'])->name('member_import-create');

    //---------------------
    // 退会申請受付
    //---------------------

    // 一覧画面
    Route::get('/leave_accept', [LeaveAcceptController::class, 'index'])->name('leave_accept');

    // バリデーション(検索用)
    Route::post('/leave_accept/vd_search', [LeaveAcceptController::class, 'validationForSearch'])->name('leave_accept-vd_search');

    // 検索結果取得
    Route::post('/leave_accept/search', [LeaveAcceptController::class, 'search'])->name('leave_accept-search');

    // 詳細取得用
    Route::post('/leave_accept/get_data', [LeaveAcceptController::class, 'getData'])->name('leave_accept-get_data');

    // モーダル処理
    Route::post('/leave_accept/exec_modal', [LeaveAcceptController::class, 'execModal'])->name('leave_accept-exec_modal');

    // 退会申請編集
    Route::get('/leave_accept/edit/{leaveApplyId}', [LeaveAcceptController::class, 'edit'])->name('leave_accept-edit');

    // 編集処理
    Route::post('/leave_accept/update', [LeaveAcceptController::class, 'update'])->name('leave_accept-update');

    // バリデーション(登録用)
    Route::post('/leave_accept/vd_input', [LeaveAcceptController::class, 'validationForInput'])->name('leave_accept-vd_input');

    // 削除処理
    Route::post('/leave_accept/delete', [LeaveAcceptController::class, 'delete'])->name('leave_accept-delete');

    //---------------------
    // 教師情報
    //---------------------

    // 教師一覧
    Route::get('/tutor_mng', [TutorMngController::class, 'index'])->name('tutor_mng');

    // バリデーション(検索用)
    Route::post('/tutor_mng/vd_search', [TutorMngController::class, 'validationForSearch'])->name('tutor_mng-vd_search');

    // 検索結果取得
    Route::post('/tutor_mng/search', [TutorMngController::class, 'search'])->name('tutor_mng-search');

    // 教師情報詳細
    Route::get('/tutor_mng/detail/{tid}', [TutorMngController::class, 'detail'])->name('tutor_mng-detail');

    // 教師情報詳細 - 削除処理
    Route::post('/tutor_mng/delete_detail', [TutorMngController::class, 'deleteDetail'])->name('tutor_mng-delete_detail');

    // 給料明細一覧
    Route::get('/tutor_mng/salary/{tid}', [TutorMngController::class, 'salary'])->name('tutor_mng-salary');

    // 給料明細一覧 - 検索結果取得
    Route::post('/tutor_mng/search_salary', [TutorMngController::class, 'searchSalary'])->name('tutor_mng-search_salary');

    // 給料明細一覧 - 詳細画面
    Route::get('/tutor_mng/salary/{tid}/detail/{date}', [TutorMngController::class, 'detailSalary'])->name('tutor_mng-detail_salary');

    // PDF出力
    Route::get('/tutor_mng/salary/{tid}/pdf/{date}', [TutorMngController::class, 'pdf'])->name('tutor_mng-pdf_salary');

    // 教師空き時間
    Route::get('/tutor_mng/weekly_shift/{tid}', [TutorMngController::class, 'weeklyShift'])->name('tutor_mng-weekly_shift');

    // 教師カレンダー
    Route::get('/tutor_mng/calendar/{tid}', [TutorMngController::class, 'calendar'])->name('tutor_mng-calendar');

    // 教師カレンダー 打ち合わせ新規登録
    Route::get('/tutor_mng/calendar/{tid}/new', [TutorMngController::class, 'calendarNew'])->name('tutor_mng-calendar-new');

    // 教師カレンダー 打ち合わせ新規登録処理
    Route::post('/tutor_mng/calendar/create', [TutorMngController::class, 'calendarCreate'])->name('tutor_mng-calendar-create');

    // 教師カレンダー 打ち合わせ編集
    Route::get('/tutor_mng/calendar/{tid}/edit/{tutorScheduleId}', [TutorMngController::class, 'calendarEdit'])->name('tutor_mng-calendar-edit');

    // 教師カレンダー 打ち合わせ編集処理
    Route::post('/tutor_mng/calendar/update', [TutorMngController::class, 'calendarUpdate'])->name('tutor_mng-calendar-update');

    // バリデーション(登録用)
    Route::post('/tutor_mng/calendar/vd_input', [TutorMngController::class, 'calendarValidationForInput'])->name('tutor_mng-calendar-vd_input');

    // 詳細取得用
    Route::post('/tutor_mng/get_calendar', [TutorMngController::class, 'getCalendar'])->name('tutor_mng-get_calendar');

    // 削除処理
    Route::post('/tutor_mng/delete', [TutorMngController::class, 'delete'])->name('tutor_mng-delete');

    // 教師 新規登録
    Route::get('/tutor_mng/new', [TutorMngController::class, 'new'])->name('tutor_mng-new');

    // 教師 新規登録処理
    Route::post('/tutor_mng/create', [TutorMngController::class, 'create'])->name('tutor_mng-create');

    // 教師 編集
    Route::get('/tutor_mng/edit/{tid}', [TutorMngController::class, 'edit'])->name('tutor_mng-edit');

    // 教師 編集処理
    Route::post('/tutor_mng/update', [TutorMngController::class, 'update'])->name('tutor_mng-update');

    // バリデーション(登録用)（教師登録）
    Route::post('/tutor_mng/vd_input', [TutorMngController::class, 'validationForInput'])->name('tutor_mng-vd_input');

    // 退職登録画面
    Route::get('/tutor_mng/leave/edit/{tid}', [TutorMngController::class, 'leaveEdit'])->name('tutor_mng-leave-edit');

    // 退職処理
    Route::post('/tutor_mng/leave/update', [TutorMngController::class, 'leaveUpdate'])->name('tutor_mng-leave-update');

    // 所属登録
    Route::get('/tutor_mng/campus/new', [TutorMngController::class, 'campusNew'])->name('tutor_mng-campus-new');

    // 所属登録処理
    Route::post('/tutor_mng/campus/create', [TutorMngController::class, 'campusCreate'])->name('tutor_mng-campus-create');

    // 所属編集
    Route::get('/tutor_mng/campus/edit/{tid}', [TutorMngController::class, 'campusEdit'])->name('tutor_mng-campus-edit');

    // 所属編集処理
    Route::post('/tutor_mng/campus/update', [TutorMngController::class, 'campusUpdate'])->name('tutor_mng-campus-update');

    // バリデーション(登録用)（所属登録）
    Route::post('/tutor_mng/campus/vd_input', [TutorMngController::class, 'campusValidationForInput'])->name('tutor_mng-campus-vd_input');

    // 所属削除処理
    Route::post('/tutor_mng/campus/delete', [TutorMngController::class, 'campusDelete'])->name('tutor_mng-campus-delete');

    //---------------------
    // 教師登録
    //---------------------

    // 登録
    Route::get('/tutor_regist', [TutorRegistController::class, 'index'])->name('tutor_regist');

    // バリデーション(登録用)
    Route::post('/tutor_regist/vd_input', [TutorRegistController::class, 'validationForInput'])->name('tutor_regist-vd_input');

    // 新規登録処理
    Route::post('/tutor_regist/create', [TutorRegistController::class, 'create'])->name('tutor_regist-create');

    //---------------------
    // 回数報告
    //---------------------

    // 一覧画面
    Route::get('/times_check', [TimesCheckController::class, 'index'])->name('times_check');

    // バリデーション(検索用)
    Route::post('/times_check/vd_search', [TimesCheckController::class, 'validationForSearch'])->name('times_check-vd_search');

    // 検索結果取得
    Route::post('/times_check/search', [TimesCheckController::class, 'search'])->name('times_check-search');

    // 詳細取得用
    Route::post('/times_check/get_data', [TimesCheckController::class, 'getData'])->name('times_check-get_data');

    // 回数報告編集
    Route::get('/times_check/edit/{timesReportId}', [TimesCheckController::class, 'edit'])->name('times_check-edit');

    // 授業日時プルダウンを選択された際に授業日時・生徒名・教科の情報を返却する
    Route::post('/times_check/get_data_select', [TimesCheckController::class, 'getDataSelect'])->name('times_check-get_data_select');

    // 編集処理
    Route::post('/times_check/update', [TimesCheckController::class, 'update'])->name('times_check-update');

    // バリデーション(登録用)
    Route::post('/times_check/vd_input', [TimesCheckController::class, 'validationForInput'])->name('times_check-vd_input');

    // 削除処理
    Route::post('/times_check/delete', [TimesCheckController::class, 'delete'])->name('times_check-delete');

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

    // 授業日時プルダウンを選択された際に教室・教師の情報を返却する
    Route::post('/absent_accept/get_data_select', [AbsentAcceptController::class, 'getDataSelect'])->name('absent_accept-get_data_select');

    // 編集処理
    Route::post('/absent_accept/update', [AbsentAcceptController::class, 'update'])->name('absent_accept-update');

    // バリデーション(登録用)
    Route::post('/absent_accept/vd_input', [AbsentAcceptController::class, 'validationForInput'])->name('absent_accept-vd_input');

    // 削除処理
    Route::post('/absent_accept/delete', [AbsentAcceptController::class, 'delete'])->name('absent_accept-delete');

    //---------------------
    // 振替連絡受付
    //---------------------

    // 一覧画面
    Route::get('/transfer_accept', [TransferAcceptController::class, 'index'])->name('transfer_accept');

    // バリデーション(検索用)
    Route::post('/transfer_accept/vd_search', [TransferAcceptController::class, 'validationForSearch'])->name('transfer_accept-vd_search');

    // 検索結果取得
    Route::post('/transfer_accept/search', [TransferAcceptController::class, 'search'])->name('transfer_accept-search');

    // 詳細取得用
    Route::post('/transfer_accept/get_data', [TransferAcceptController::class, 'getData'])->name('transfer_accept-get_data');

    // モーダル処理
    Route::post('/transfer_accept/exec_modal', [TransferAcceptController::class, 'execModal'])->name('transfer_accept-exec_modal');

    // 振替連絡編集
    Route::get('/transfer_accept/edit/{transferApplyId}', [TransferAcceptController::class, 'edit'])->name('transfer_accept-edit');

    // カレンダーを選択された際に教室・教師の情報を返却する
    Route::post('/transfer_accept/get_data_select', [TransferAcceptController::class, 'getDataSelect'])->name('transfer_accept-get_data_select');

    // 編集処理
    Route::post('/transfer_accept/update', [TransferAcceptController::class, 'update'])->name('transfer_accept-update');

    // バリデーション(登録用)
    Route::post('/transfer_accept/vd_input', [TransferAcceptController::class, 'validationForInput'])->name('transfer_accept-vd_input');

    // 削除処理
    Route::post('/transfer_accept/delete', [TransferAcceptController::class, 'delete'])->name('transfer_accept-delete');

    //---------------------
    // 授業報告
    //---------------------

    // 一覧画面
    Route::get('/report_check', [ReportCheckController::class, 'index'])->name('report_check');

    // バリデーション(検索用)
    Route::post('/report_check/vd_search', [ReportCheckController::class, 'validationForSearch'])->name('report_check-vd_search');

    // 検索結果取得
    Route::post('/report_check/search', [ReportCheckController::class, 'search'])->name('report_check-search');

    // 詳細取得用
    Route::post('/report_check/get_data', [ReportCheckController::class, 'getData'])->name('report_check-get_data');

    // 授業報告編集
    Route::get('/report_check/edit/{reportId}', [ReportCheckController::class, 'edit'])->name('report_check-edit');

    // カレンダーを選択された際に教室・教師の情報を返却する
    Route::post('/report_check/get_data_select', [ReportCheckController::class, 'getDataSelect'])->name('report_check-get_data_select');

    // 編集処理
    Route::post('/report_check/update', [ReportCheckController::class, 'update'])->name('report_check-update');

    // バリデーション(登録用)
    Route::post('/report_check/vd_input', [ReportCheckController::class, 'validationForInput'])->name('report_check-vd_input');

    // 削除処理
    Route::post('/report_check/delete', [ReportCheckController::class, 'delete'])->name('report_check-delete');

    //---------------------
    // 生徒成績
    //---------------------

    // 一覧画面
    //Route::get('/grades_mng', [GradesMngController::class, 'index'])->name('grades_mng');

    // バリデーション(検索用)
    //Route::post('/grades_mng/vd_search', [GradesMngController::class, 'validationForSearch'])->name('grades_mng-vd_search');

    // 検索結果取得
    //Route::post('/grades_mng/search', [GradesMngController::class, 'search'])->name('grades_mng-search');

    // 詳細取得用
    //Route::post('/grades_mng/get_data', [GradesMngController::class, 'getData'])->name('grades_mng-get_data');

    // 生徒成績編集
    //Route::get('/grades_mng/edit/{gradesId}', [GradesMngController::class, 'edit'])->name('grades_mng-edit');

    // 編集処理
    //Route::post('/grades_mng/update', [GradesMngController::class, 'update'])->name('grades_mng-update');

    // バリデーション(登録用)
    //Route::post('/grades_mng/vd_input', [GradesMngController::class, 'validationForInput'])->name('grades_mng-vd_input');

    // 削除処理
    //Route::post('/grades_mng/delete', [GradesMngController::class, 'delete'])->name('grades_mng-delete');

    //---------------------
    // スケジュール取込
    //---------------------

    // 取込
    Route::get('/schedule_import', [ScheduleImportController::class, 'index'])->name('schedule_import');

    // バリデーション(取込用)
    Route::post('/schedule_import/vd_input', [ScheduleImportController::class, 'validationForInput'])->name('schedule_import-vd_input');

    // 取込処理
    Route::post('/schedule_import/create', [ScheduleImportController::class, 'create'])->name('schedule_import-create');

    //---------------------
    // 模試管理
    //---------------------

    // 模試一覧
    Route::get('/trial_mng', [TrialMngController::class, 'index'])->name('trial_mng');

    // バリデーション(検索用)
    Route::post('/trial_mng/vd_search', [TrialMngController::class, 'validationForSearch'])->name('trial_mng-vd_search');

    // 検索結果取得
    Route::post('/trial_mng/search', [TrialMngController::class, 'search'])->name('trial_mng-search');

    // 詳細取得用
    Route::post('/trial_mng/get_data', [TrialMngController::class, 'getData'])->name('trial_mng-get_data');

    // 模試申込者一覧
    Route::get('/trial_mng/entry/{tmid}', [TrialMngController::class, 'entry'])->name('trial_mng-entry');

    // 模試申込者一覧 - 検索結果取得
    Route::post('/trial_mng/search_entry', [TrialMngController::class, 'searchEntry'])->name('trial_mng-search_entry');

    // 模試申込者一覧 - 詳細取得用
    Route::post('/trial_mng/get_data_entry', [TrialMngController::class, 'getDataEntry'])->name('trial_mng-get_data_entry');

    // モーダル処理
    Route::post('/trial_mng/exec_modal_entry', [TrialMngController::class, 'execModalEntry'])->name('trial_mng-exec_modal_entry');

    // 模試登録
    Route::get('/trial_mng/new', [TrialMngController::class, 'new'])->name('trial_mng-new');

    // バリデーション(登録用)
    Route::post('/trial_mng/vd_input', [TrialMngController::class, 'validationForInput'])->name('trial_mng-vd_input');

    // 新規登録処理
    Route::post('/trial_mng/create', [TrialMngController::class, 'create'])->name('trial_mng-create');

    // 模試申込者情報変更
    Route::get('/trial_mng/entry/{tmid}/edit/{trialApplyId}', [TrialMngController::class, 'entryEdit'])->name('trial_mng-entry-edit');

    // 編集処理
    Route::post('/trial_mng/update_entry', [TrialMngController::class, 'updateEntry'])->name('trial_mng-update_entry');

    // バリデーション(登録用)
    Route::post('/trial_mng/vd_input_entry', [TrialMngController::class, 'validationForInputEntry'])->name('trial_mng-vd_input_entry');

    // 削除処理
    Route::post('/trial_mng/delete_entry', [TrialMngController::class, 'deleteEntry'])->name('trial_mng-delete_entry');

    //---------------------
    // イベント管理
    //---------------------

    // イベント一覧
    Route::get('/event_mng', [EventMngController::class, 'index'])->name('event_mng');

    // バリデーション(検索用)
    Route::post('/event_mng/vd_search', [EventMngController::class, 'validationForSearch'])->name('event_mng-vd_search');

    // 検索結果取得
    Route::post('/event_mng/search', [EventMngController::class, 'search'])->name('event_mng-search');

    // 詳細取得用
    Route::post('/event_mng/get_data', [EventMngController::class, 'getData'])->name('event_mng-get_data');

    // イベント登録
    Route::get('/event_mng/new', [EventMngController::class, 'new'])->name('event_mng-new');

    // 新規登録処理
    Route::post('/event_mng/create', [EventMngController::class, 'create'])->name('event_mng-create');

    // イベント編集
    Route::get('/event_mng/edit/{eventId}', [EventMngController::class, 'edit'])->name('event_mng-edit');

    // 編集処理
    Route::post('/event_mng/update', [EventMngController::class, 'update'])->name('event_mng-update');

    // 削除処理
    Route::post('/event_mng/delete', [EventMngController::class, 'delete'])->name('event_mng-delete');

    // イベント申込者一覧
    Route::get('/event_mng/entry/{eventId}', [EventMngController::class, 'entry'])->name('event_mng-entry');

    // イベント申込者一覧 - 検索結果取得
    Route::post('/event_mng/search_entry', [EventMngController::class, 'searchEntry'])->name('event_mng-search_entry');

    // イベント申込者一覧 - 詳細取得用
    Route::post('/event_mng/get_data_entry', [EventMngController::class, 'getDataEntry'])->name('event_mng-get_data_entry');

    // モーダル処理
    Route::post('/event_mng/exec_modal_entry', [EventMngController::class, 'execModalEntry'])->name('event_mng-exec_modal_entry');

    // バリデーション(登録用)
    Route::post('/event_mng/vd_input', [EventMngController::class, 'validationForInput'])->name('event_mng-vd_input');

    // イベント申込編集画面
    Route::get('/event_mng/entry/{eventId}/edit/{cid}', [EventMngController::class, 'entryEdit'])->name('event_mng-entry-edit');

    // イベント申込編集 - 編集処理
    Route::post('/event_mng/update_entry', [EventMngController::class, 'updateEntry'])->name('event_mng-update_entry');

    // イベント申込編集 - 削除処理
    Route::post('/event_mng/delete_entry', [EventMngController::class, 'deleteEntry'])->name('event_mng-delete_entry');

    // イベント申込編集 - バリデーション(登録用)
    Route::post('/event_mng/vd_input_entry', [EventMngController::class, 'validationForInputEntry'])->name('event_mng-vd_input_entry');

    //---------------------
    // ギフトカード管理
    //---------------------

    // 一覧画面
    Route::get('/card_mng', [CardMngController::class, 'index'])->name('card_mng');

    // バリデーション(検索用)
    Route::post('/card_mng/vd_search', [CardMngController::class, 'validationForSearch'])->name('card_mng-vd_search');

    // 検索結果取得
    Route::post('/card_mng/search', [CardMngController::class, 'search'])->name('card_mng-search');

    // 詳細取得用
    Route::post('/card_mng/get_data', [CardMngController::class, 'getData'])->name('card_mng-get_data');

    // モーダル処理
    Route::post('/card_mng/exec_modal', [CardMngController::class, 'execModal'])->name('card_mng-exec_modal');

    // ギフトカード付与
    Route::get('/card_mng/new', [CardMngController::class, 'new'])->name('card_mng-new');

    // 新規登録処理
    Route::post('/card_mng/create_new', [CardMngController::class, 'create'])->name('card_mng-create_new');

    // 詳細取得用
    Route::post('/card_mng/get_data_select_new', [CardMngController::class, 'getDataSelectNew'])->name('card_mng-get_data_select_new');

    // ギフトカード付与情報編集
    Route::get('/card_mng/edit/{cardId}', [CardMngController::class, 'edit'])->name('card_mng-edit');

    // 編集処理
    Route::post('/card_mng/update_edit', [CardMngController::class, 'update'])->name('card_mng-update_edit');

    // バリデーション(登録用)
    Route::post('/card_mng/vd_input_new', [CardMngController::class, 'validationForInputNew'])->name('card_mng-vd_input_new');

    // // バリデーション(更新用)
    Route::post('/card_mng/vd_input_edit', [CardMngController::class, 'validationForInputEdit'])->name('card_mng-vd_input_edit');

    // 削除処理
    Route::post('/card_mng/delete_edit', [CardMngController::class, 'delete'])->name('card_mng-delete_edit');

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

    //----------------------
    // 学年情報取込
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
    // 年度スケジュール取込
    //----------------------

    // 取込
    Route::get('/year_schedule_import', [YearScheduleImportController::class, 'index'])->name('year_schedule_import');

    // 取込処理
    Route::post('/year_schedule_import/create', [YearScheduleImportController::class, 'create'])->name('year_schedule_import-create');

    // バリデーション(取込用)
    Route::post('/year_schedule_import/vd_input', [YearScheduleImportController::class, 'validationForInput'])->name('year_schedule_import-vd_input');

    // 検索結果取得
    Route::post('/year_schedule_import/search', [YearScheduleImportController::class, 'search'])->name('year_schedule_import-search');

    //----------------------
    // 休業日登録
    //----------------------

    // 一覧
    Route::get('/room_holiday', [RoomHolidayController::class, 'index'])->name('room_holiday');

    // バリデーション(検索用)
    Route::post('/room_holiday/vd_search', [RoomHolidayController::class, 'validationForSearch'])->name('room_holiday-vd_search');

    // 検索結果取得
    Route::post('/room_holiday/search', [RoomHolidayController::class, 'search'])->name('room_holiday-search');

    // 休業日登録
    Route::get('/room_holiday/new', [RoomHolidayController::class, 'new'])->name('room_holiday-new');

    // 新規登録処理
    Route::post('/room_holiday/create', [RoomHolidayController::class, 'create'])->name('room_holiday-create');

    // 休業日編集
    Route::get('/room_holiday/edit/{roomHolidayId}', [RoomHolidayController::class, 'edit'])->name('room_holiday-edit');

    // 編集処理
    Route::post('/room_holiday/update', [RoomHolidayController::class, 'update'])->name('room_holiday-update');

    // バリデーション(登録用)
    Route::post('/room_holiday/vd_input', [RoomHolidayController::class, 'validationForInput'])->name('room_holiday-vd_input');

    // 削除処理
    Route::post('/room_holiday/delete', [RoomHolidayController::class, 'delete'])->name('room_holiday-delete');

    //---------------------
    // マスタ管理
    //---------------------

    // 一覧画面
    Route::get('/master_mng', [MasterMngController::class, 'index'])->name('master_mng');

    // バリデーション(検索用)
    Route::post('/master_mng/vd_search', [MasterMngController::class, 'validationForSearch'])->name('master_mng-vd_search');

    // 検索結果取得
    Route::post('/master_mng/search', [MasterMngController::class, 'search'])->name('master_mng-search');

    // 取込画面
    Route::get('/master_mng/import', [MasterMngController::class, 'import'])->name('master_mng-import');

    // 新規登録処理
    Route::post('/master_mng/create', [MasterMngController::class, 'create'])->name('master_mng-create');

    // バリデーション(登録用)
    Route::post('/master_mng/vd_input', [MasterMngController::class, 'validationForInput'])->name('master_mng-vd_input');

    //---------------------
    // 校舎マスタ モック
    //---------------------
    // 一覧
    Route::get('/master_mng_campus', [MasterMngCampusController::class, 'index'])->name('master_mng_campus');

    // 詳細取得用
    Route::post('/master_mng_campus/get_data', [MasterMngCampusController::class, 'getData'])->name('master_mng_campus-get_data');

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
    // 指導ブースマスタ モック
    //---------------------
    // 一覧
    Route::get('/master_mng_booth', [MasterMngBoothController::class, 'index'])->name('master_mng_booth');

    // 詳細取得用
    Route::post('/master_mng_booth/get_data', [MasterMngBoothController::class, 'getData'])->name('master_mng_booth-get_data');

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
    // 授業科目マスタ モック
    //---------------------
    // 一覧
    Route::get('/master_mng_subject', [MasterMngSubjectController::class, 'index'])->name('master_mng_subject');

    // 詳細取得用
    Route::post('/master_mng_subject/get_data', [MasterMngSubjectController::class, 'getData'])->name('master_mng_subject-get_data');

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
    // 時間割マスタ モック
    //---------------------
    // 一覧
    Route::get('/master_mng_timetable', [MasterMngTimetableController::class, 'index'])->name('master_mng_timetable');

    // 詳細取得用
    Route::post('/master_mng_timetable/get_data', [MasterMngTimetableController::class, 'getData'])->name('master_mng_timetable-get_data');

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
    // コースマスタ モック
    //---------------------
    // 一覧
    Route::get('/master_mng_course', [MasterMngCourseController::class, 'index'])->name('master_mng_course');

    // 詳細取得用
    Route::post('/master_mng_course/get_data', [MasterMngCourseController::class, 'getData'])->name('master_mng_course-get_data');

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

    //---------------------
    // システムマスタ モック
    //---------------------
    // 一覧
    Route::get('/master_mng_system', [MasterMngSystemController::class, 'index'])->name('master_mng_system');

    // 詳細取得用
    Route::post('/master_mng_system/get_data', [MasterMngSystemController::class, 'getData'])->name('master_mng_system-get_data');

    // 編集
    Route::get('/master_mng_system/edit/{systemId}', [MasterMngSystemController::class, 'edit'])->name('master_mng_system-edit');

    // 編集処理
    Route::post('/master_mng_system/update', [MasterMngSystemController::class, 'update'])->name('master_mng_system-update');

    // バリデーション(登録用)
    Route::post('/master_mng_system/vd_input', [MasterMngSystemController::class, 'validationForInput'])->name('master_mng_system-vd_input');

    //---------------------
    // 事務局アカウント管理
    //---------------------

    // 一覧画面
    Route::get('/account_mng', [AccountMngController::class, 'index'])->name('account_mng');

    // バリデーション(検索用)
    Route::post('/account_mng/vd_search', [AccountMngController::class, 'validationForSearch'])->name('account_mng-vd_search');

    // 検索結果取得
    Route::post('/account_mng/search', [AccountMngController::class, 'search'])->name('account_mng-search');

    // 詳細取得用
    Route::post('/account_mng/get_data', [AccountMngController::class, 'getData'])->name('account_mng-get_data');

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
    // データ管理
    //---------------------

    Route::get('/data_mng', [DataMngController::class, 'index'])->name('data_mng');

    //---------------------
    // 生徒スケジュール登録 モック
    //---------------------

    // カレンダー
    //Route::get('/member_mng/calendar/{sid}', [MemberMngController::class, 'calendar'])->name('member_mng-calendar');

    // 生徒カレンダー スケジュール新規登録
    Route::get('/member_mng/calendar/{sid}/new', [MemberMngController::class, 'calendarNew'])->name('member_mng-calendar-new');

    // 新規登録処理
    //Route::post('/member_mng/create', [MemberMngController::class, 'create'])->name('member_mng-create');

    // 生徒カレンダー スケジュール更新
    Route::get('/member_mng/calendar/{sid}/edit/{ScheduleId}', [MemberMngController::class, 'calendarEdit'])->name('member_mng-calendar-edit');

    // 編集処理
    //Route::post('/member_mng/update', [MemberMngController::class, 'update'])->name('member_mng-update');

    // バリデーション(登録用)
    //Route::post('/member_mng/vd_input', [MemberMngController::class, 'validationForInput'])->name('member_mng-vd_input');

    // 詳細取得用
    //Route::post('/member_mng/get_calendar', [MemberMngController::class, 'getCalendar'])->name('member_mng-get_calendar');

    // 削除処理
    //Route::post('/member_mng/delete', [MemberMngController::class, 'delete'])->name('member_mng-delete');

    //---------------------
    // 会員管理 生徒登録 モック
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
    //Route::post('/member_mng/leave/vd_input', [MemberMngController::class, 'validationForInputLeave'])->name('member_mng-leave-vd_input');

    //---------------------
    // 管理者向け 振替調整一覧 モック
    //---------------------

    // 一覧画面
    Route::get('/transfer_check', [TransferCheckController::class, 'index'])->name('transfer_check');

    // バリデーション(検索用)
    Route::post('/transfer_check/vd_search', [TransferCheckController::class, 'validationForSearch'])->name('transfer_check-vd_search');

    // 検索結果取得
    Route::post('/transfer_check/search', [TransferCheckController::class, 'search'])->name('transfer_check-search');

    // 詳細取得用
    Route::post('/transfer_check/get_data', [TransferCheckController::class, 'getData'])->name('transfer_check-get_data');

    // // モーダル処理
    // Route::post('/transfer_check/exec_modal', [TransferCheckController::class, 'execModal'])->name('transfer_check-exec_modal');

    // 振替調整登録画面
    Route::get('/transfer_check/new', [TransferCheckController::class, 'new'])->name('transfer_check-new');

    // 振替連絡編集
    Route::get('/transfer_check/edit/{transferApplyId}', [TransferCheckController::class, 'edit'])->name('transfer_check-edit');

    // // カレンダーを選択された際に教室・教師の情報を返却する
    // Route::post('/transfer_check/get_data_select', [TransferCheckController::class, 'getDataSelect'])->name('transfer_check-get_data_select');

    // 編集処理
    Route::post('/transfer_check/update', [TransferCheckController::class, 'update'])->name('transfer_check-update');

    // バリデーション(登録用)
    Route::post('/transfer_check/vd_input', [TransferCheckController::class, 'validationForInput'])->name('transfer_check-vd_input');

    // // 削除処理
    // Route::post('/transfer_check/delete', [TransferCheckController::class, 'delete'])->name('transfer_check-delete');

    //---------------------
    // 管理者向け 面談日程連絡一覧 モック
    //---------------------

    // 一覧画面
    Route::get('/conference_accept', [ConferenceAcceptController::class, 'index'])->name('conference_accept');

    // バリデーション(検索用)
    Route::post('/conference_accept/vd_search', [ConferenceAcceptController::class, 'validationForSearch'])->name('conference_accept-vd_search');

    // 検索結果取得
    Route::post('/conference_accept/search', [ConferenceAcceptController::class, 'search'])->name('conference_accept-search');

    // // 詳細取得用
    Route::post('/conference_accept/get_data', [ConferenceAcceptController::class, 'getData'])->name('conference_accept-get_data');

    // // モーダル処理
    // Route::post('/conference_accept/exec_modal', [ConferenceAcceptController::class, 'execModal'])->name('conference_accept-exec_modal');

    // 振替連絡編集
    Route::get('/conference_accept/edit/{transferApplyId}', [ConferenceAcceptController::class, 'edit'])->name('conference_accept-edit');

    // // カレンダーを選択された際に教室・教師の情報を返却する
    // Route::post('/conference_accept/get_data_select', [ConferenceAcceptController::class, 'getDataSelect'])->name('conference_accept-get_data_select');

    // 編集処理
    Route::post('/conference_accept/update', [ConferenceAcceptController::class, 'update'])->name('conference_accept-update');

    // バリデーション(登録用)
    Route::post('/conference_accept/vd_input', [ConferenceAcceptController::class, 'validationForInput'])->name('conference_accept-vd_input');

    // // 削除処理
    // Route::post('/conference_accept/delete', [ConferenceAcceptController::class, 'delete'])->name('conference_accept-delete');

    //---------------------
    // 授業情報検索 モック
    //---------------------

    // 生徒授業情報一覧
    //Route::get('/student_class', [StudentClassController::class, 'index'])->name('student_class');
    Route::get('/student_class', function () {
        return view('pages.admin.student_class');
    })->name('student_class');

    //---------------------
    // 見込み客管理 モック
    //---------------------

    // 一覧画面
    Route::get('/prospect', [ProspectController::class, 'index'])->name('prospect');

    // バリデーション(検索用)
    Route::post('/prospect/vd_search', [ProspectController::class, 'validationForSearch'])->name('prospect-vd_search');

    // 検索結果取得
    Route::post('/prospect/search', [ProspectController::class, 'search'])->name('prospect-search');

    // 詳細取得用
    Route::post('/prospect/get_data', [ProspectController::class, 'getData'])->name('prospect-get_data');

    // 新規登録
    Route::get('/prospect/new', [ProspectController::class, 'new'])->name('prospect-new');

    // 新規登録処理
    Route::post('/prospect/create', [ProspectController::class, 'create'])->name('prospect-create');

    // 編集画面
    Route::get('/prospect/edit/{changeId}', [ProspectController::class, 'edit'])->name('prospect-edit');

    // 編集処理
    Route::post('/prospect/update', [ProspectController::class, 'update'])->name('prospect-update');

    // バリデーション(登録用)
    Route::post('/prospect/vd_input', [ProspectController::class, 'validationForInput'])->name('prospect-vd_input');

    // 削除処理
    Route::post('/prospect/delete', [ProspectController::class, 'delete'])->name('prospect-delete');

    //---------------------
    // 空き講師検索 モック
    //---------------------

    // 空き講師検索一覧
    Route::get('/tutor_assign', function () {
        return view('pages.admin.tutor_assign');
    })->name('tutor_assign');

    //---------------------
    // 講師授業時間 モック
    //---------------------

    // 講師授業時間一覧
    Route::get('/tutor_class', function () {
        return view('pages.admin.tutor_class');
    })->name('tutor_class');

    //---------------------
    // 追加請求申請受付 モック
    //---------------------

    // 一覧画面
    Route::get('/surcharge_accept', [SurchargeAcceptController::class, 'index'])->name('surcharge_accept');

    // バリデーション(検索用)
    Route::post('/surcharge_acceptg/vd_search', [SurchargeAcceptController::class, 'validationForSearch'])->name('surcharge_accept-vd_search');

    // 検索結果取得
    Route::post('/surcharge_accept/search', [SurchargeAcceptController::class, 'search'])->name('surcharge_accept-search');

    // 詳細取得用
    Route::post('/surcharge_accept/get_data', [SurchargeAcceptController::class, 'getData'])->name('surcharge_accept-get_data');

    // 編集
    Route::get('/surcharge_accept/edit/{gradesId}', [SurchargeAcceptController::class, 'edit'])->name('surcharge_accept-edit');

    // 編集処理
    Route::post('/surcharge_accept/update', [SurchargeAcceptController::class, 'update'])->name('surcharge_accept-update');

    // バリデーション(登録用)
    Route::post('/surcharge_accept/vd_input', [SurchargeAcceptController::class, 'validationForInput'])->name('surcharge_accept-vd_input');

    // 削除処理
    Route::post('/surcharge_accept/delete', [SurchargeAcceptController::class, 'delete'])->name('surcharge_accept-delete');

    //---------------------
    // 要振替授業管理 モック
    //---------------------

    // 一覧画面
    Route::get('/transfer_required', [TransferRequiredController::class, 'index'])->name('transfer_required');

    // バリデーション(検索用)
    Route::post('/transfer_required/vd_search', [TransferRequiredController::class, 'validationForSearch'])->name('transfer_required-vd_search');

    // 検索結果取得
    Route::post('/transfer_required/search', [TransferRequiredController::class, 'search'])->name('transfer_required-search');

    // 詳細取得用
    Route::post('/transfer_required/get_data', [TransferRequiredController::class, 'getData'])->name('transfer_required-get_data');

    //---------------------
    // 成績事例検索 モック
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

    // モーダル処理
    Route::post('/extra_lesson_mng/exec_modal', [ExtraLessonMngController::class, 'execModal'])->name('extra_lesson_mng-exec_modal');

    // 新規登録
    Route::get('/extra_lesson_mng/new', [ExtraLessonMngController::class, 'new'])->name('extra_lesson_mng-new');

    // 新規登録処理
    Route::post('/extra_lesson_mng/create', [ExtraLessonMngController::class, 'create'])->name('extra_lesson_mng-create');

    // 編集画面
    Route::get('/extra_lesson_mng/edit/{changeId}', [ExtraLessonMngController::class, 'edit'])->name('extra_lesson_mng-edit');

    // 編集処理
    Route::post('/extra_lesson_mng/update', [ExtraLessonMngController::class, 'update'])->name('extra_lesson_mng-update');

    // バリデーション(登録用)
    Route::post('/extra_lesson_mng/vd_input', [ExtraLessonMngController::class, 'validationForInput'])->name('extra_lesson_mng-vd_input');

    // 削除処理
    Route::post('/extra_lesson_mng/delete', [ExtraLessonMngController::class, 'delete'])->name('extra_lesson_mng-delete');

    //---------------------
    // 給与算出 モック
    //---------------------

    // 給与算出一覧
    Route::get('/salary_calculation', [SalaryCalculationController::class, 'index'])->name('salary_calculation');

    // 検索結果取得
    Route::post('/salary_calculation/search', [SalaryCalculationController::class, 'search'])->name('salary_calculation-search');

    // 給与算出情報一覧（対象月の詳細）
    Route::get('/salary_calculation/detail/{date}', [SalaryCalculationController::class, 'detail'])->name('salary_calculation-detail');

    //---------------------
    // 請求算出 モック
    //---------------------

    // 請求算出一覧
    Route::get('/invoice_calculation', [InvoiceCalculationController::class, 'index'])->name('invoice_calculation');

    // 検索結果取得
    Route::post('/invoice_calculation/search', [InvoiceCalculationController::class, 'search'])->name('invoice_calculation-search');

    // 請求算出情報一覧（対象月の詳細）
    Route::get('/invoice_calculation/detail/{date}', [InvoiceCalculationController::class, 'detail'])->name('invoice_calculation-detail');

    //---------------------
    // 特別期間講習管理（生徒・講師）
    //---------------------

    // 講師提出スケジュール一覧
    Route::get('/season_mng_tutor', [SeasonMngTutorController::class, 'index'])->name('season_mng_tutor');

    // バリデーション(検索用)
    Route::post('/season_mng_tutor/vd_search', [SeasonMngTutorController::class, 'validationForSearch'])->name('season_mng_tutor-vd_search');

    // 検索結果取得
    Route::post('/season_mng_tutor/search', [SeasonMngTutorController::class, 'search'])->name('season_mng_tutor-search');

    // 講師提出スケジュール詳細
    Route::get('/season_mng_tutor/detail/{tid}', [SeasonMngTutorController::class, 'detail'])->name('season_mng_tutor-detail');

    // 生徒提出スケジュール一覧
    Route::get('/season_mng_student', [SeasonMngStudentController::class, 'index'])->name('season_mng_student');

    // バリデーション(検索用)
    Route::post('/season_mng_student/vd_search', [SeasonMngStudentController::class, 'validationForSearch'])->name('season_mng_student-vd_search');

    // 検索結果取得
    Route::post('/season_mng_student/search', [SeasonMngStudentController::class, 'search'])->name('season_mng_student-search');

    // 生徒スケジュール詳細
    Route::get('/season_mng_student/detail/{sid}', [SeasonMngStudentController::class, 'detail'])->name('season_mng_student-detail');

    // 編集処理
    Route::post('/season_mng_student/update', [SeasonMngStudentController::class, 'update'])->name('season_mng_student-update');

    // 生徒スケジュールコマ組み編集 - バリデーション(登録用)
    Route::post('/season_mng_student/vd_input', [SeasonMngStudentController::class, 'validationForInput'])->name('season_mng_student-vd_input');

    // 生徒スケジュールコマ組み
    Route::get('/season_mng_student/detail/{sid}/plan/{subjectId}', [SeasonMngStudentController::class, 'plan'])->name('season_mng_student-plan');

    // 編集処理
    Route::post('/season_mng_student/update_plan', [SeasonMngStudentController::class, 'updatePlan'])->name('season_mng_student-update_plan');

    // 生徒スケジュールコマ組み編集 - バリデーション(登録用)
    Route::post('/season_mng_student/vd_input_plan', [SeasonMngStudentController::class, 'validationForInputPlan'])->name('season_mng_student-vd_input_plan');

    //---------------------
    // 特別期間講習 コマ組み確定
    //---------------------

    // コマ組み確定状況一覧
    Route::get('/season_plan_confirm', [SeasonPlanConfirmController::class, 'index'])->name('season_plan_confirm');

    // 実行結果取得
    Route::post('/season_plan_confirm/search', [SeasonPlanConfirmController::class, 'search'])->name('season_plan_confirm-search');

    // 詳細取得用
    Route::post('/season_plan_confirm/get_data', [SeasonPlanConfirmController::class, 'getData'])->name('SeasonPlanConfirmController-get_data');

    // モーダル処理
    Route::post('/season_plan_confirm/exec_modal', [SeasonPlanConfirmController::class, 'execModal'])->name('season_plan_confirm-exec_modal');

    //---------------------
    // 特別期間講習 自動コマ組み
    //---------------------

    // コマ組み状況一覧
    //Route::get('/season_plan', [SeasonPlanController::class, 'index'])->name('season_plan');

    // 実行結果取得
    //Route::post('/season_plan/search', [SeasonPlanController::class, 'search'])->name('season_plan-search');

    // 自動コマ組み実行画面
    //Route::get('/season_plan/autoexec/{id}', [SeasonPlanController::class, 'autoExec'])->name('season_plan-autoexec');

    // 実行結果取得
    //Route::post('/season_plan/search_autoexec', [SeasonPlanController::class, 'searchAutoExec'])->name('season_plan-search_autoexec');

    // モーダル処理
    //Route::post('/season_plan/exec_modal_exec', [SeasonPlanController::class, 'execModalExec'])->name('season_plan-exec_modal_entry');

    // アンマッチリストダウンロード
    //Route::get('/season_plan/download/{csvId}', [SeasonPlanController::class, 'download'])->name('season_plan-download');

    //---------------------
    // 特別期間講習 個別スケジュール登録
    //---------------------

    // 個別スケジュール登録画面
    Route::get('/season_schedule', [SeasonScheduleController::class, 'index'])->name('season_schedule');

    // 新規登録処理
    Route::post('/season_schedule/create', [SeasonScheduleController::class, 'create'])->name('season_schedule-create');

    // バリデーション(登録用)
    Route::post('/season_schedule/vd_input', [SeasonScheduleController::class, 'validationForInput'])->name('season_schedule-vd_input');

    //---------------------
    // 生徒一括取込 モック
    //---------------------

    // 取込画面
    Route::get('/import_student', [ImportStudentController::class, 'index'])->name('import_student');

    // 新規登録処理
    Route::post('/import_student/create', [ImportStudentController::class, 'create'])->name('import_student-create');

    // バリデーション(登録用)
    Route::post('/import_student/vd_input', [ImportStudentController::class, 'validationForInput'])->name('import_student-vd_input');

    //---------------------
    // 生徒スケジュール取込 モック
    //---------------------

    // 取込画面
    Route::get('/import_student_schedule', [ImportStudentScheduleController::class, 'index'])->name('import_student_schedule');

    // 新規登録処理
    Route::post('/import_student_schedule/create', [ImportStudentScheduleController::class, 'create'])->name('import_student_schedule-create');

    // バリデーション(登録用)
    Route::post('/import_student_schedule/vd_input', [ImportStudentScheduleController::class, 'validationForInput'])->name('import_student_schedule-vd_input');

    //---------------------
    // 講師一括取込 モック
    //---------------------

    // 取込画面
    Route::get('/import_tutor', [ImportTutorController::class, 'index'])->name('import_tutor');

    // 新規登録処理
    Route::post('/import_tutor/create', [ImportTutorController::class, 'create'])->name('import_tutor-create');

    // バリデーション(登録用)
    Route::post('/import_tutor/vd_input', [ImportTutorController::class, 'validationForInput'])->name('import_tutor-vd_input');

    //----------------------
    // 振替残数リセット処理 モック
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
    // 保持期限超過データ削除管理 モック
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
    // 学校コード取込 モック
    //---------------------

    // 取込画面
    Route::get('/import_school_code', [ImportSchoolCodeController::class, 'index'])->name('import_school_code');

    // 新規登録処理
    Route::post('/import_school_code/create', [ImportSchoolCodeController::class, 'create'])->name('import_school_code-create');

    // バリデーション(登録用)
    Route::post('/import_school_code/vd_input', [ImportSchoolCodeController::class, 'validationForInput'])->name('import_school_code-vd_input');

    //---------------------
    // 超過勤務者一覧 モック
    //---------------------

    // 超過勤務者一覧
    Route::get('/overtime', [OvertimeController::class, 'index'])->name('overtime');

    // バリデーション(検索用)
    Route::post('/overtime/vd_search', [OvertimeController::class, 'validationForSearch'])->name('overtime-vd_search');

    // 検索結果取得
    Route::post('/overtime/search', [OvertimeController::class, 'search'])->name('overtime-search');

    //---------------------
    // バッジ付与一覧 モック
    //---------------------

    // バッジ付与一覧
    Route::get('/give_badge', [GiveBadgeController::class, 'index'])->name('give_badge');

    // バリデーション(検索用)
    Route::post('/give_badge/vd_search', [GiveBadgeController::class, 'validationForSearch'])->name('give_badge-vd_search');

    // 検索結果取得
    Route::post('/give_badge/search', [GiveBadgeController::class, 'search'])->name('give_badge-search');

});
    //---------------------
    // 画面未作成のメニュー用（後で削除する）
    //---------------------

    // 準備中ページ
    Route::get('/under_construction', function () {
        return view('pages.mypage-common.under_construction');
    })->name('under_construction');

