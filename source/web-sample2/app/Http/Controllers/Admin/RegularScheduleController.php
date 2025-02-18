<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Consts\AppConst;
use App\Libs\AuthEx;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\RegularClass;
use App\Models\RegularClassMember;
use App\Models\MstBooth;
use App\Models\MstCourse;
use App\Http\Controllers\Traits\FuncCalendarTrait;
use App\Http\Controllers\Traits\FuncScheduleTrait;
use Illuminate\Support\Facades\Lang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ReadDataValidateException;

/**
 * レギュラースケジュール - コントローラ
 */
class RegularScheduleController extends Controller
{

    // 機能共通処理：カレンダー
    use FuncCalendarTrait;
    // 機能共通処理：スケジュール関連
    use FuncScheduleTrait;

    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
    }

    //==========================
    // カレンダー
    //==========================

    /**
     * カレンダー初期画面
     *
     * @return view
     */
    public function calendar()
    {

        // 教室リストを取得
        $rooms = $this->mdlGetRoomList(false);

        // セッションからデータを取得（取得後キー削除）
        $sessionCampus = session()->pull('session_campus_cd', null);

        // セッションデータのチェック
        if (!is_null($sessionCampus)) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $this->guardRoomAdminRoomcd($sessionCampus);
        }

        return view('pages.admin.regular_schedule', [
            'rules' => $this->rulesForInputBulk(null),
            'rooms' => $rooms,
            'editData' => [
                'campus_cd' => $sessionCampus,
            ]
        ]);
    }

    /**
     * ブース情報取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return object ブース情報
     */
    public function getBooth(Request $request)
    {

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $campusCd = $request->input('campus_cd');
        $this->guardRoomAdminRoomcd($campusCd);

        // クエリを作成
        $query = MstBooth::query();

        // 校舎の絞り込み条件
        if (AuthEx::isRoomAdmin()) {
            // 教室管理者の場合、自分の教室コードのみにガードを掛ける
            $query->where($this->guardRoomAdminTableWithRoomCd());
        }
        $query->where('campus_cd', $campusCd);

        // データを取得
        $mstBooth = $query
            ->select(
                'mst_booths.booth_cd as id',
                'mst_booths.name as title',
                'mst_booths.disp_order',
            )
            // 面談用を除外
            ->where('usage_kind', '<>', AppConst::CODE_MASTER_41_3)
            ->orderby('disp_order')
            ->get();

        // 固定の仮ブース情報を付加
        $mstBooth->prepend(config('appconf.timetable_booth'));

        return $mstBooth;
    }

    /**
     * カレンダー取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return int 生徒Id
     */
    public function getCalendar(Request $request)
    {

        // バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForCalendar())->validate();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $campusCd = $request->input('campus_cd');
        $this->guardRoomAdminRoomcd($campusCd);

        $roomcd = $request->input('roomcd');

        return $this->getRegularCalendar($request, $roomcd, false);
    }

    //==========================
    // レギュラースケジュール登録
    //==========================

    /**
     * 新規登録画面
     *
     * @param string $campusCd 校舎コード
     * @param string $daytimeStr 曜日時刻文字列(曜日コード+時分)
     * @param string $boothCd ブースコード
     * @return view
     */
    public function new($campusCd, $daytimeStr, $boothCd)
    {
        // パラメータ取得・日時切り分け
        $day = substr($daytimeStr, 0, 1);
        $time = substr($daytimeStr, 1, 2) . ':' . substr($daytimeStr, 3, 2);

        $param = [
            'campus_cd' => $campusCd,
            'day_cd' => $day,
            'start_time' => $time,
            'booth_cd' => $boothCd,
        ];

        // パラメータのバリデーション
        $this->validateFromParam($param, $this->rulesForNew($param));

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $this->guardRoomAdminRoomcd($campusCd);

        // 教室名を取得する
        $roomName = $this->mdlGetRoomName($campusCd);

        // ブースリストを取得（面談用を除外）
        $booths = $this->mdlGetBoothList($campusCd, null, AppConst::CODE_MASTER_41_3);

        // コースリストを取得（面談を除外）
        $courses = $this->mdlGetCourseList(null, AppConst::CODE_MASTER_42_3);

        // 曜日リストを取得
        $dayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // 時限リストを取得（校舎・時間割区分から）
        $periods = $this->mdlGetPeriodListByKind($campusCd, AppConst::CODE_MASTER_37_0);

        // 科目リストを取得
        $subjects = $this->mdlGetSubjectList();

        // 生徒リストを取得
        $students = $this->mdlGetStudentList($campusCd);

        // 講師リストを取得
        $tutors = $this->mdlGetTutorList($campusCd);

        // 指定時刻から、対応する時限の情報を取得（通常期間）
        // MEMO:パラメータにはカレンダーのセルの開始時刻が設定されているが、
        // 時限の終了時刻とすぐ下のセルの開始時刻が等しくなるため、1min加算して判定する
        $targetTime = date('H:i', strtotime($time . "+1 min"));
        $periodInfo = $this->fncScheGetPeriodTime($campusCd, AppConst::CODE_MASTER_37_0, $targetTime);

        if (isset($periodInfo)) {
            $periodNo = $periodInfo->period_no;
            $startTime = $periodInfo->start_time;
            $endTime = $periodInfo->end_time;
        } else {
            $periodNo = null;
            $startTime = null;
            $endTime = null;
        }

        // 通塾種別リストを取得
        $howToKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_33);

        // 初期表示データをセット
        $editData = [
            'campus_cd' => $campusCd,
            'name' => $roomName,
            'day_cd' => $day,
            'booth_cd' => $boothCd,
            'course_kind' => AppConst::CODE_MASTER_42_1,
            'period_no' => $periodNo,
            'start_time' => $startTime,
            'end_time' => $endTime,
            // スケジュール更新区分=NEW
            'kind' => AppConst::SCHEDULE_KIND_NEW
        ];

        // 登録画面表示時の校舎コードをセッションに保存
        session(['session_campus_cd' => $campusCd]);

        return view('pages.admin.regular_schedule-input', [
            'rules' => $this->rulesForInput(null),
            'booths' => $booths,
            'courses' => $courses,
            'dayList' => $dayList,
            'periods' => $periods,
            'tutors' => $tutors,
            'students' => $students,
            'subjects' => $subjects,
            'howToKindList' => $howToKindList,
            'editData' => $editData
        ]);
    }

    /**
     * コース情報取得（コースプルダウン選択）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array コース情報
     */
    public function getDataSelectCourse(Request $request)
    {
        // コースコードは文字列型なのでidチェックはしない

        // コースコードを取得
        $courseCd = $request->input('id');

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------

        // コースプルダウンを取得
        $courses = $this->mdlGetCourseList();

        // [ガード] コースコードがプルダウンの中にあるかチェック
        $this->guardListValue($courses, $courseCd);

        //---------------------------
        // コース情報（コース種別）を返却する
        //---------------------------
        // コース情報を取得
        $course = $this->fncScheGetCourseInfo($courseCd);

        return [
            'course_kind' => $course->course_kind
        ];
    }

    /**
     * 時間割情報取得（時限プルダウン選択時）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 時間割情報
     */
    public function getDataSelectTimetable(Request $request)
    {
        // 時限のバリデーション
        $this->validateIdsFromRequest($request, 'period_no');

        // 校舎コード・時限を取得
        $campusCd = $request->input('campus_cd');
        $periodNo = $request->input('period_no');

        //------------------------
        // [ガード] リスト自体を取得して、
        // 値が正しいかチェックする
        //------------------------

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $this->guardRoomAdminRoomcd($campusCd);

        // 時限リストを取得
        $periods = $this->mdlGetPeriodListByKind($campusCd, AppConst::CODE_MASTER_37_0);

        // [ガード] 時限がプルダウンの中にあるかチェック
        $this->guardListValue($periods, $periodNo);

        //---------------------------
        // 時間割情報（開始時刻・終了時刻）を返却する
        //---------------------------

        // 時間割区分（通常）・指定時限から、対応する時間割情報を取得
        $periodInfo = $this->fncScheGetTimetableByPeriod($campusCd, AppConst::CODE_MASTER_37_0, $periodNo);
        $startTime = $periodInfo->start_time;
        $endTime = $periodInfo->end_time;

        return [
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i')
        ];
    }

    /**
     * バリデーションルールを取得(登録画面パラメータ用)
     *
     * @param array $param パラメータ
     * @return array ルール
     */
    private function rulesForNew($param)
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

        // 独自バリデーション: リストのチェック 曜日コード
        $validationDayList =  function ($attribute, $value, $fail) use ($param) {

            // 曜日コードリストを取得
            $dayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);
            if (!isset($dayList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ブース
        $validationBoothList =  function ($attribute, $value, $fail) use ($param) {

            // ブースリストを取得（面談用を除外）
            $booths = $this->mdlGetBoothList($param['campus_cd'], null, AppConst::CODE_MASTER_41_3);
            if (!isset($booths[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += RegularClass::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += RegularClass::fieldRules('day_cd', ['required', $validationDayList]);
        $rules += RegularClass::fieldRules('start_time', ['required']);
        $rules += RegularClass::fieldRules('booth_cd', ['required', $validationBoothList]);

        return $rules;
    }

    /**
     * 編集画面
     *
     * @param int $regularClassId レギュラー授業ID
     * @return view
     */
    public function edit($regularClassId)
    {
        // IDのバリデーション
        $this->validateIds($regularClassId);

        // クエリを作成
        $query = RegularClass::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        // データを取得
        $regularClass = $query
            ->select(
                'regular_class_id',
                'campus_cd',
                'room_names.room_name as name',
                'day_cd',
                'period_no',
                'start_time',
                'end_time',
                'booth_cd',
                'regular_classes.course_cd',
                'mst_courses.course_kind',
                'mst_courses.name as course_name',
                'student_id',
                'regular_classes.tutor_id',
                'subject_cd',
                'how_to_kind'
            )
            // 校舎名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('regular_classes.campus_cd', '=', 'room_names.code');
            })
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('regular_classes.course_cd', '=', 'mst_courses.course_cd');
            })
            // IDを指定
            ->where('regular_class_id', $regularClassId)
            ->firstOrFail();

        // データを取得（受講生徒情報）
        $regularClassMembers = RegularClassMember::query()
            ->select(
                'student_id',
            )
            // レギュラー授業IDを指定
            ->where('regular_class_id', $regularClassId)
            ->get();

        // 取得データを配列->カンマ区切り文字列に変換しセット
        $arrClassMembers = [];
        if (count($regularClassMembers) > 0) {
            foreach ($regularClassMembers as $classMember) {
                array_push($arrClassMembers, $classMember['student_id']);
            }
        }
        $regularClass['class_member_id'] = implode(',', $arrClassMembers);

        $campusCd = $regularClass['campus_cd'];

        // ブースリストを取得（面談用を除外）
        $booths = $this->mdlGetBoothList($campusCd, null, AppConst::CODE_MASTER_41_3);

        // 曜日リストを取得
        $dayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // 時限リストを取得（校舎・時間割区分から）
        $periods = $this->mdlGetPeriodListByKind($campusCd, AppConst::CODE_MASTER_37_0);

        // 科目リストを取得
        $subjects = $this->mdlGetSubjectList();

        // 生徒リストを取得
        $students = $this->mdlGetStudentList($campusCd);

        // 講師リストを取得
        $tutors = $this->mdlGetTutorList($campusCd);

        // 通塾種別リストを取得
        $howToKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_33);

        // スケジュール更新区分=UPDATE
        $regularClass['kind'] = AppConst::SCHEDULE_KIND_UPD;

        // 編集画面表示時の校舎コードをセッションに保存
        session(['session_campus_cd' => $campusCd]);

        return view('pages.admin.regular_schedule-input', [
            'rules' => $this->rulesForInput(null),
            'booths' => $booths,
            'courses' => null,
            'periods' => $periods,
            'tutors' => $tutors,
            'students' => $students,
            'subjects' => $subjects,
            'dayList' => $dayList,
            'howToKindList' => $howToKindList,
            'editData' => $regularClass
        ]);
    }

    /**
     * コピー登録画面
     *
     * @param int $regularClassId レギュラー授業ID
     * @return view
     */
    public function copy($regularClassId)
    {
        // IDのバリデーション
        $this->validateIds($regularClassId);

        // クエリを作成
        $query = RegularClass::query();

        // 教室管理者の場合、自分の教室コードのみにガードを掛ける
        $query->where($this->guardRoomAdminTableWithRoomCd());

        // 教室名取得のサブクエリ
        $room = $this->mdlGetRoomQuery();

        // データを取得
        $regularClass = $query
            ->select(
                'regular_class_id',
                'campus_cd',
                'room_names.room_name as name',
                'day_cd',
                'period_no',
                'start_time',
                'end_time',
                'booth_cd',
                'regular_classes.course_cd',
                'mst_courses.course_kind',
                'mst_courses.name as course_name',
                'student_id',
                'regular_classes.tutor_id',
                'subject_cd',
                'how_to_kind'
            )
            // 校舎名の取得
            ->leftJoinSub($room, 'room_names', function ($join) {
                $join->on('regular_classes.campus_cd', '=', 'room_names.code');
            })
            // コース名の取得
            ->sdLeftJoin(MstCourse::class, function ($join) {
                $join->on('regular_classes.course_cd', '=', 'mst_courses.course_cd');
            })
            // IDを指定
            ->where('regular_class_id', $regularClassId)
            ->firstOrFail();

        // データを取得（受講生徒情報）
        $regularClassMembers = RegularClassMember::query()
            ->select(
                'student_id',
            )
            // レギュラー授業IDを指定
            ->where('regular_class_id', $regularClassId)
            ->get();

        // 取得データを配列->カンマ区切り文字列に変換しセット
        $arrClassMembers = [];
        if (count($regularClassMembers) > 0) {
            foreach ($regularClassMembers as $classMember) {
                array_push($arrClassMembers, $classMember['student_id']);
            }
        }
        $regularClass['class_member_id'] = implode(',', $arrClassMembers);

        $campusCd = $regularClass['campus_cd'];

        // ブースリストを取得（面談用を除外）
        $booths = $this->mdlGetBoothList($campusCd, null, AppConst::CODE_MASTER_41_3);

        // 曜日リストを取得
        $dayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);

        // 時限リストを取得（校舎・時間割区分から）
        $periods = $this->mdlGetPeriodListByKind($campusCd, AppConst::CODE_MASTER_37_0);

        // 科目リストを取得
        $subjects = $this->mdlGetSubjectList();

        // 生徒リストを取得
        $students = $this->mdlGetStudentList($campusCd);

        // 講師リストを取得
        $tutors = $this->mdlGetTutorList($campusCd);

        // 通塾種別リストを取得
        $howToKindList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_33);

        // スケジュール更新区分=COPY
        $regularClass['kind'] = AppConst::SCHEDULE_KIND_CPY;

        // コピー登録画面表示時の校舎コードをセッションに保存
        session(['session_campus_cd' => $campusCd]);

        return view('pages.admin.regular_schedule-input', [
            'rules' => $this->rulesForInput(null),
            'booths' => $booths,
            'courses' => null,
            'periods' => $periods,
            'tutors' => $tutors,
            'students' => $students,
            'subjects' => $subjects,
            'dayList' => $dayList,
            'howToKindList' => $howToKindList,
            'editData' => $regularClass
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
        // 登録前バリデーション（関連チェック）。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInputRelated($request))->validate();

        $form = $request->only(
            'campus_cd',
            'day_cd',
            'period_no',
            'start_time',
            'end_time',
            'booth_cd',
            'course_cd',
            'course_kind',
            'student_id',
            'class_member_id',
            'tutor_id',
            'subject_cd',
            'how_to_kind'
        );

        if ($request['kind'] == AppConst::SCHEDULE_KIND_CPY) {
            // コピー登録の場合
            // ブースのチェック・空きブース取得
            $booth = $this->fncScheSearchBoothRegular(
                $form['campus_cd'],
                $form['booth_cd'],
                $form['day_cd'],
                $form['period_no'],
                $form['how_to_kind'],
                null,
                false
            );
            if (!$booth) {
                // エラー時。エラー時は不正な値としてエラーレスポンスを返却
                $this->illegalResponseErr();
            }
        } else {
            // 新規登録処理の場合
            $booth = $request['booth_cd'];
        }

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($form, $booth) {

            // レギュラー授業情報登録（講師空き時間情報登録も合わせて行う）
            $this->fncScheCreateRegular($form, $booth);
        });
        return;
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
        // 登録前バリデーション（関連チェック）。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInputRelated($request))->validate();

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            $form = $request->only(
                'regular_class_id',
                'campus_cd',
                'day_cd',
                'period_no',
                'start_time',
                'end_time',
                'booth_cd',
                'course_cd',
                'course_kind',
                'student_id',
                'class_member_id',
                'tutor_id',
                'subject_cd',
                'how_to_kind'
            );

            // 時間（分）の算出
            $start = Carbon::createFromTimeString($form['start_time']);
            $end = Carbon::createFromTimeString($form['end_time']);
            $minutes = $start->diffInMinutes($end);

            // スケジュール情報更新（UPDATE）
            // 対象データを取得(IDでユニークに取る)
            $regularClass = RegularClass::where('regular_class_id', $form['regular_class_id'])
                // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            $regularClass->campus_cd = $form['campus_cd'];
            $regularClass->day_cd = $form['day_cd'];
            $regularClass->period_no = $form['period_no'];
            $regularClass->subject_cd = $form['subject_cd'];
            $regularClass->start_time = $form['start_time'];
            $regularClass->end_time = $form['end_time'];
            $regularClass->minutes = $minutes;
            $regularClass->booth_cd = $form['booth_cd'];
            $regularClass->course_cd = $form['course_cd'];
            if ($form['course_kind'] != AppConst::CODE_MASTER_42_2) {
                // １対多以外の場合 生徒IDを設定
                $regularClass->student_id = $form['student_id'];
            }
            if ($form['course_kind'] == AppConst::CODE_MASTER_42_1 || $form['course_kind'] == AppConst::CODE_MASTER_42_2) {
                // 授業の場合 講師IDを設定
                $regularClass->tutor_id = $form['tutor_id'];
            }
            $regularClass->how_to_kind = $form['how_to_kind'];
            // 更新
            $regularClass->save();

            // レギュラー受講生徒情報更新（コース種別が授業複の場合のみ）
            if ($form['course_kind'] == AppConst::CODE_MASTER_42_2) {

                // 既存データを取得
                $regularClassMembers = RegularClassMember::query()
                    ->select(
                        'student_id',
                    )
                    // スケジュールIDを指定
                    ->where('regular_class_id', $form['regular_class_id'])
                    ->get();

                // 変更前の生徒リスト（テーブルから取得）
                $arrMembersBef = [];
                if (count($regularClassMembers) > 0) {
                    foreach ($regularClassMembers as $classMember) {
                        array_push($arrMembersBef, $classMember['student_id']);
                    }
                }

                // 変更後の生徒リスト（requestから取得）
                // カンマ区切りで入ってくるので配列に格納
                $arrMembersAft = explode(",", $form['class_member_id']);

                // レギュラー受講生徒情報削除処理
                // 変更前の生徒 - 変更後の生徒 で差分を取得
                $arrMembersDel = array_diff($arrMembersBef, $arrMembersAft);

                if (count($arrMembersDel) > 0) {
                    // 対象データを削除
                    RegularClassMember::where('regular_class_id', $form['regular_class_id'])
                        // 削除対象の生徒リストを指定
                        ->whereIn('student_id', $arrMembersDel)
                        // 物理削除
                        ->forceDelete();
                }

                // レギュラー受講生徒情報追加処理
                // 変更後の生徒 - 変更前の生徒 で差分を取得
                $arrMembersAdd = array_diff($arrMembersAft, $arrMembersBef);

                foreach ($arrMembersAdd as $member) {
                    // レギュラー受講生徒情報テーブルへのinsert
                    $regularClassMember = new RegularClassMember;
                    $regularClassMember->regular_class_id = $form['regular_class_id'];
                    $regularClassMember->student_id = $member;
                    // 登録
                    $regularClassMember->save();
                }
            }
        });
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
        // リクエストデータチェック（項目チェック）
        $validator = Validator::make($request->all(), $this->rulesForInput($request));
        if (count($validator->errors()) != 0) {
            // 項目チェックエラーがある場合はここでエラー情報を返す
            return $validator->errors();
        }
        // リクエストデータチェック（関連チェック追加）
        $validatorRerated = Validator::make($request->all(), $this->rulesForInputRelated($request));
        // 項目チェックエラー無し時は関連チェックを行い、結果を返す
        return $validatorRerated->errors();
    }

    /**
     * バリデーションルールを取得(登録用・項目チェック)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInput(?Request $request)
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

        // 独自バリデーション: リストのチェック コース
        $validationCourseList =  function ($attribute, $value, $fail) {

            // コースリストを取得
            $courses = $this->mdlGetCourseList(false);
            if (!isset($courses[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック ブース
        $validationBoothList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('campus_cd')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            // ブースリストを取得
            $list = $this->mdlGetBoothList($request['campus_cd']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 曜日コード
        $validationDayList =  function ($attribute, $value, $fail) {

            // 曜日コードリストを取得
            $dayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);
            if (!isset($dayList[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 時限
        $validationPeriodList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('course_kind')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            // 時限リストを取得
            $list = $this->mdlGetPeriodListByKind($request['campus_cd'], AppConst::CODE_MASTER_37_0);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 講師
        $validationTutorList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('campus_cd')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            // 講師リストを取得
            $list = $this->mdlGetTutorList($request['campus_cd']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒
        $validationStudentList =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('campus_cd')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            // 生徒リストを取得
            $list = $this->mdlGetStudentList($request['campus_cd']);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 生徒（複数選択）
        $validationStudentListMulti =  function ($attribute, $value, $fail) use ($request) {

            if (!$request->filled('campus_cd')) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            $list = $this->mdlGetStudentList($request['campus_cd']);
            // カンマ区切りで入ってくるので分割してチェック
            foreach (explode(",", $value) as $member) {
                // 生徒リストを取得
                if (!isset($list[$member])) {
                    // 不正な値エラー
                    return $fail(Lang::get('validation.invalid_input'));
                }
            }
        };

        // 独自バリデーション: リストのチェック 科目
        $validationSubjectList =  function ($attribute, $value, $fail) {

            // 科目リストを取得
            $list = $this->mdlGetSubjectList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: リストのチェック 通塾種別
        $validationHowToKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_33);
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += RegularClass::fieldRules('campus_cd', ['required', $validationRoomList]);
        $rules += RegularClass::fieldRules('course_cd', ['required', $validationCourseList]);
        $rules += ['course_kind' => ['required']];
        $rules += RegularClass::fieldRules('booth_cd', ['required', $validationBoothList]);
        $rules += RegularClass::fieldRules('day_cd', ['required', $validationDayList]);
        $rules += RegularClass::fieldRules('period_no', ['required', $validationPeriodList]);
        $rules += RegularClass::fieldRules('start_time', ['required']);
        $rules += RegularClass::fieldRules('end_time', ['required']);
        $rules += RegularClass::fieldRules('how_to_kind', ['required', $validationHowToKindList]);
        if (
            $request && $request->filled('course_kind') &&
            ($request['course_kind'] == AppConst::CODE_MASTER_42_1 || $request['course_kind'] == AppConst::CODE_MASTER_42_2)
        ) {
            // コース種別が授業の場合のみチェック
            $rules += RegularClass::fieldRules('tutor_id', ['required', $validationTutorList]);
            // コース種別が授業の場合、教科をチェック（必須あり）
            $rules += RegularClass::fieldRules('subject_cd', ['required', $validationSubjectList]);
        } else {
            // コース種別が自習の場合、教科をチェック（必須なし）
            $rules += RegularClass::fieldRules('subject_cd', [$validationSubjectList]);
        }
        if ($request && $request->filled('course_kind') && $request['course_kind'] == AppConst::CODE_MASTER_42_1) {
            // コース種別が授業単の場合、生徒（単数指定）をチェック（必須あり）
            $rules += RegularClass::fieldRules('student_id', ['required', $validationStudentList]);
        } else if ($request && $request->filled('course_kind') && $request['course_kind'] == AppConst::CODE_MASTER_42_2) {
            // コース種別が授業複の場合、受講生徒（複数指定）をチェック
            $rules += ['class_member_id' => ['required', $validationStudentListMulti]];
        } else {
            // コース種別が自習の場合、生徒（単数指定）をチェック（必須なし）
            $rules += RegularClass::fieldRules('student_id', [$validationStudentList]);
        }

        return $rules;
    }

    /**
     * バリデーションルールを取得(登録用・関連チェック)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInputRelated(?Request $request)
    {
        $rules = array();

        // 独自バリデーション: ブース重複チェック
        $validationDupBooth =  function ($attribute, $value, $fail) use ($request) {

            if (
                !$request->filled('campus_cd') || !$request->filled('booth_cd')
                || !$request->filled('day_cd') || !$request->filled('period_no')
                || !$request->filled('how_to_kind')
            ) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }

            $regularClassId = null;
            if ($request['kind'] == AppConst::SCHEDULE_KIND_UPD && $request->filled('regular_class_id')) {
                // 更新の場合のみ、スケジュールIDをセット（除外用）
                $regularClassId = $request['regular_class_id'];
            }
            if ($request['kind'] == AppConst::SCHEDULE_KIND_CPY) {
                // コピー登録の場合、空きブース検索ありとする
                $checkOnly = false;
            } else {
                // 新規登録・更新の場合、空きブース検索なしとする
                $checkOnly = true;
            }
            // ブース重複チェック
            $booth = $this->fncScheSearchBoothRegular(
                $request['campus_cd'],
                $request['booth_cd'],
                $request['day_cd'],
                $request['period_no'],
                $request['how_to_kind'],
                $regularClassId,
                $checkOnly
            );
            if (!$booth) {
                // ブース空きなしエラー
                return $fail(Lang::get('validation.duplicate_booth'));
            }
        };

        // 独自バリデーション: 時限と開始時刻の相関チェック
        $validationPeriodStartTime =  function ($attribute, $value, $fail) use ($request) {

            if (
                !$request->filled('campus_cd') || !$request->filled('period_no')
                || !$request->filled('start_time')
            ) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            // 時限と開始時刻の相関チェック
            $chk = $this->fncScheChkStartTime(
                $request['campus_cd'],
                AppConst::CODE_MASTER_37_0,
                $request['period_no'],
                $request['start_time']
            );
            if (!$chk) {
                // 開始時刻範囲エラー
                return $fail(Lang::get('validation.out_of_range_period'));
            }
        };

        // 独自バリデーション: 生徒スケジュール重複チェック
        $validationDupStudent =  function ($attribute, $value, $fail) use ($request) {
            $kind = $request['kind'];
            return $this->fncScheValidateStudentRegular($request, $kind, $attribute, $value, $fail);
        };

        // 独自バリデーション: 生徒スケジュール重複チェック（複数指定）
        $validationDupStudentMulti =  function ($attribute, $value, $fail) use ($request) {
            $kind = $request['kind'];
            return $this->fncScheValidateStudentRegular($request, $kind, $attribute, $value, $fail);
        };

        // 独自バリデーション: 講師スケジュール重複チェック
        $validationDupTutor =  function ($attribute, $value, $fail) use ($request) {
            $kind = $request['kind'];
            return $this->fncScheValidateTutorRegular($request, $kind, $attribute, $value, $fail);
        };

        // 関連チェックは項目チェックと分けて行う
        // ブース重複チェック
        $rules += ['booth_cd' => [$validationDupBooth]];
        // 時限と開始時刻の相関チェック
        $rules += ['start_time' => [$validationPeriodStartTime]];
        // 講師のスケジュール重複チェック
        if (
            $request->filled('course_kind') &&
            ($request['course_kind'] == AppConst::CODE_MASTER_42_1 || $request['course_kind'] == AppConst::CODE_MASTER_42_2)
        ) {
            // コース種別が授業の場合のみチェック
            $rules += ['tutor_id' => [$validationDupTutor]];
        }
        // 生徒のスケジュール重複チェック
        if ($request->filled('course_kind') && $request['course_kind'] != AppConst::CODE_MASTER_42_2) {
            // コース種別が授業複以外の場合のみ、生徒（単数指定）をチェック
            $rules += ['student_id' => [$validationDupStudent]];
        }
        if ($request->filled('course_kind') && $request['course_kind'] == AppConst::CODE_MASTER_42_2) {
            // コース種別が授業複の場合のみ、受講生徒（複数指定）をチェック
            $rules += ['class_member_id' => [$validationDupStudentMulti]];
        }

        return $rules;
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
        $this->validateIdsFromRequest($request, 'regular_class_id');

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request) {

            // Formを取得
            $form = $request->only(
                'regular_class_id',
                'course_kind'
            );

            // 対象データを取得(IDでユニークに取る)
            $regularClass = RegularClass::where('regular_class_id', $form['regular_class_id'])
                // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                // 該当データがない場合はエラーを返す
                ->firstOrFail();

            // 受講生徒情報削除（コース種別が授業複の場合のみ）
            if ($form['course_kind'] == AppConst::CODE_MASTER_42_2) {
                // 削除
                RegularClassMember::where('regular_class_id', $form['regular_class_id'])
                    ->delete();
            }

            // スケジュール情報削除
            $regularClass->delete();
        });
        return;
    }

    //==========================
    // 一括登録処理
    //==========================

    /**
     * 登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function createBulk(Request $request)
    {
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInputBulk($request))->validate();

        // 登録データの関連バリデーション + 登録データを$regDatasにセット
        try {
            $regDatas = $this->validateScheduleRelated($request);
        } catch (ReadDataValidateException  $e) {
            // 通常は事前にバリデーションするため、ここはありえないのでエラーとする
            return $this->responseErr();
        }

        // トランザクション(例外時は自動的にロールバック)
        DB::transaction(function () use ($request, $regDatas) {

            //------------------------
            // 対象期間スケジュール削除（一括登録データのみ）
            //------------------------
            // 対象期間内のスケジュール情報（授業複）取得
            $schedules = Schedule::select(
                'schedules.schedule_id',
                'mst_courses.course_kind',
            )
                // コース情報の取得
                ->sdLeftJoin(MstCourse::class, function ($join) {
                    $join->on('schedules.course_cd', 'mst_courses.course_cd');
                })
                // 校舎・対象期間で絞り込み
                ->where('campus_cd', $request['campus_cd'])
                ->whereBetween('target_date', [$request['date_from'], $request['date_to']])
                // コース種別で絞り込み（授業複）
                ->where('course_kind', AppConst::CODE_MASTER_42_2)
                // データ作成区分で絞り込み（一括登録）
                ->where('create_kind', AppConst::CODE_MASTER_32_0)
                // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                ->get();

            // 受講生徒情報削除
            foreach ($schedules as $schedule) {
                ClassMember::where('schedule_id', $schedule['schedule_id'])
                    ->forceDelete();
            }

            // 対象期間内のスケジュール情報削除
            Schedule::where('campus_cd', $request['campus_cd'])
                // 校舎・対象期間で絞り込み
                ->whereBetween('target_date', [$request['date_from'], $request['date_to']])
                // データ作成区分で絞り込み（一括登録）
                ->where('create_kind', AppConst::CODE_MASTER_32_0)
                // 教室管理者の場合、自分の校舎コードのみにガードを掛ける
                ->where($this->guardRoomAdminTableWithRoomCd())
                ->forceDelete();

            //------------------------
            // スケジュール登録
            //------------------------
            foreach ($regDatas as $regData) {
                // スケジュール情報登録
                $this->fncScheCreateSchedule($regData, $regData['target_date'], $regData['booth_cd'], AppConst::CODE_MASTER_32_0);
            }
        });

        return;
    }

    /**
     * バリデーション(一括登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInputBulk(Request $request)
    {
        // リクエストデータチェック（項目チェック）
        $validator = Validator::make($request->all(), $this->rulesForInputBulk($request));
        if (count($validator->errors()) != 0) {
            // 項目チェックエラーがある場合はここでエラー情報を返す
            return $validator->errors();
        }

        // 登録データの関連バリデーション
        try {
            $datas = $this->validateScheduleRelated($request);
        } catch (ReadDataValidateException $e) {
            // 入力項目とは別のバリデーションエラーとして返却
            return ['validate_msg_area' => [$e->getMessage()]];
        }
    }

    /**
     * バリデーションルールを取得(一括登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array ルール
     */
    private function rulesForInputBulk(?Request $request)
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

        // 独自バリデーション: 一括登録期間チェック
        $validationDateTerm =  function ($attribute, $value, $fail) use ($request) {

            if (
                !$request->filled('campus_cd') || !$request->filled('date_from')
                || !$request->filled('date_to')
            ) {
                // 検索項目がrequestにない場合はチェックしない（他項目でのエラーを拾う）
                return;
            }
            if (strtotime($request['date_from']) > strtotime($request['date_to'])) {
                // FromがTo以降の場合、ここでは検出せずスキップする
                return;
            }

            // 一括登録期間チェック（通常期間内か）
            $chk = $this->fncScheChkScheduleTerm($request['campus_cd'], $request['date_from'], $request['date_to']);
            if (!$chk) {
                // 期間指定エラー
                return $fail(Lang::get('validation.out_of_range_term'));
            }
        };

        // MEMO: テーブルの項目の定義は、モデルの方で定義する。(型とサイズ)
        // その他を第二引数で指定する
        $rules += Schedule::fieldRules('campus_cd', [$validationRoomList]);

        // 日付 項目のバリデーションルールをベースにする
        $ruleTargetDate = Schedule::getFieldRule('target_date');

        // FromとToの大小チェックバリデーションを追加(Fromが存在する場合のみ)
        $validateFromTo = [];
        $keyFrom = 'date_from';
        if (isset($request[$keyFrom]) && filled($request[$keyFrom])) {
            $validateFromTo = ['after_or_equal:' . $keyFrom];
        }

        // 日付From・Toのバリデーションの設定
        $rules += ['date_from' => array_merge(['required'], $ruleTargetDate, [$validationDateTerm])];
        $rules += ['date_to' => array_merge(['required'], $ruleTargetDate, $validateFromTo)];
        return $rules;
    }

    /**
     * スケジュール登録データの関連バリデーション
     * 登録データの設定も行う
     * バリデーションエラー時はException発生し、処理を継続しない
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 登録データ（スケジュール情報）
     */
    private function validateScheduleRelated(?Request $request)
    {
        $regDatas = [];

        // 曜日リストを取得
        $dayList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_16);
        // 曜日毎にループ
        foreach ($dayList as $dayCd => $data) {
            // レギュラースケジュール情報の取得
            $regularClasses = $this->fncScheGetRegularSchedule($dayCd, $request['campus_cd']);

            // 対象期間内・指定曜日の授業日リストを取得
            $targetDates = $this->fncScheGetScheduleDateByDayCd($request['campus_cd'], $dayCd, $request['date_from'], null, $request['date_to']);
            // レギュラー授業毎にループ
            foreach ($regularClasses as $regular) {

                // １対他授業の場合、レギュラー受講生徒情報の取得
                if ($regular['course_kind'] == AppConst::CODE_MASTER_42_2) {
                    $classMembers = RegularClassMember::query()
                        ->select(
                            'regular_class_members.student_id',
                            'students.name as student_name'
                        )
                        // 生徒名の取得
                        ->sdLeftJoin(Student::class, function ($join) {
                            $join->on('regular_class_members.student_id', 'students.student_id');
                        })
                        // レギュラー授業IDを指定
                        ->where('regular_class_id', $regular['regular_class_id'])
                        ->get();

                    // 取得データを配列->カンマ区切り文字列に変換しセット（データ登録共通化のため整形）
                    $arrClassMembers = [];
                    if (count($classMembers) > 0) {
                        foreach ($classMembers as $classMember) {
                            array_push($arrClassMembers, $classMember['student_id']);
                        }
                    }
                    $regular['class_member_id'] = implode(',', $arrClassMembers);
                }

                // 対象日付毎にスケジュールデータ作成
                foreach ($targetDates as $targetDate) {
                    // スケジュール情報セット
                    $scheduleData = $regular->toArray();
                    // 対象日付をセット
                    $scheduleData['target_date'] = $targetDate;

                    // 講師スケジュール重複チェック
                    $chk = $this->fncScheChkDuplidateTid(
                        $targetDate,
                        $scheduleData['start_time'],
                        $scheduleData['end_time'],
                        $scheduleData['tutor_id'],
                        null,
                        true
                    );
                    if (!$chk) {
                        // 講師スケジュール重複
                        throw new ReadDataValidateException(Lang::get('validation.duplicate_tutor')
                            . "(" . $targetDate .  " " . $scheduleData['period_no'] . "限 " . $scheduleData['tutor_name'] . ")");
                    }

                    // 生徒のスケジュール重複チェック
                    if ($scheduleData['course_kind'] != AppConst::CODE_MASTER_42_2) {
                        // 生徒スケジュール重複チェック
                        $chk = $this->fncScheChkDuplidateSid(
                            $targetDate,
                            $scheduleData['start_time'],
                            $scheduleData['end_time'],
                            $scheduleData['student_id'],
                            null,
                            true
                        );
                        if (!$chk) {
                            // 生徒スケジュール重複
                            throw new ReadDataValidateException(Lang::get('validation.duplicate_student')
                                . "(" . $targetDate .  " " . $scheduleData['period_no'] . "限 " . $scheduleData['student_name'] . ")");
                        }
                    } else {
                        // １対多授業の場合
                        foreach ($classMembers as $classMember) {
                            // 生徒スケジュール重複チェック（一人ずつ）
                            $chk = $this->fncScheChkDuplidateSid(
                                $targetDate,
                                $scheduleData['start_time'],
                                $scheduleData['end_time'],
                                $classMember['student_id'],
                                null,
                                true
                            );
                            if (!$chk) {
                                // 生徒スケジュール重複
                                throw new ReadDataValidateException(Lang::get('validation.duplicate_student')
                                    . "(" . $targetDate .  " " . $scheduleData['period_no'] . "限 " . $classMember['student_name'] . ")");
                            }
                        }
                    }
                    // ブースのチェック・空きブース取得（一括登録用）
                    $booth = $this->fncScheSearchBoothBulk(
                        $request['campus_cd'],
                        $scheduleData['booth_cd'],
                        $targetDate,
                        $dayCd,
                        $scheduleData['period_no'],
                        $scheduleData['how_to_kind'],
                        $scheduleData['regular_class_id']
                    );
                    if (!$booth) {
                        // ブース重複
                        throw new ReadDataValidateException(Lang::get('validation.duplicate_booth')
                            . "(" . $targetDate .  " " . $scheduleData['period_no'] . "限 " . $scheduleData['booth_name'] . ")");
                    }
                    // 取得ブースをセット
                    $scheduleData['booth_cd'] = $booth;

                    // スケジュール情報格納
                    array_push($regDatas, $scheduleData);
                }
            }
        }
        return $regDatas;
    }
}
