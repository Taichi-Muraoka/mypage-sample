<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use App\Models\Student;
use App\Models\Tutor;
use App\Models\MstCampus;
use App\Models\MstCourse;
use App\Models\Schedule;
use App\Models\ClassMember;
use App\Models\RegularClass;
use App\Models\RegularClassMember;
use App\Models\BatchMng;
use Carbon\Carbon;
use App\Consts\AppConst;
use App\Http\Controllers\Traits\CtrlCsvTrait;
use App\Http\Controllers\Traits\CtrlFileTrait;
use App\Http\Controllers\Traits\CtrlModelTrait;
use App\Http\Controllers\Traits\FuncScheduleTrait;
use App\Exceptions\ReadDataValidateException;

/**
 * スケジュール情報取込処理（データ移行用） - バッチ処理
 */
class ScheduleDataImport extends Command
{
    // CSV共通処理
    use CtrlCsvTrait;
    use CtrlFileTrait;
    // モデル共通処理
    use CtrlModelTrait;
    // 機能共通処理：スケジュール関連
    use FuncScheduleTrait;

    /**
     * スケジュールデータ種別
     */
    const KIND_SCHE_S = 1;
    const KIND_SCHE_D = 2;
    const KIND_REGU_S = 3;
    const KIND_REGU_D = 4;

    /**
     * 現行DB 授業ステータス
     */
    const POS_CLASS_STATUS_TRAN = 2;
    const POS_CLASS_STATUS_FST1 = 3;
    const POS_CLASS_STATUS_FST2 = 4;
    const POS_CLASS_STATUS_TRY1 = 5;
    const POS_CLASS_STATUS_TRY2 = 6;
    const POS_CLASS_STATUS_TRY3 = 7;
    const POS_CLASS_STATUS_FST3 = 8;
    const POS_CLASS_STATUS_ADD = 9;

    /**
     * 現行DB 未振替ブース（最小値）
     */
    const POS_BOOTH_TRAN_MIN = 996;

    /**
     * 登録ユーザーID（１固定とする）
     */
    const BATCH_USER = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:scheduleDataImport {kind} {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return int
     */
    public function handle()
    {
        try {

            // スケジュール種別を受け取る
            $kind = $this->argument("kind");
            // CSVファイルのパスを受け取る
            $path = $this->argument("path");

            // スケジュール種別範囲外の場合はここで終了
            if ($kind < self::KIND_SCHE_S || $kind > self::KIND_REGU_D) {
                Log::info("Batch scheduleDataImport param err, KIND: {$kind}");
                return 1;
            }

            Log::info("Batch scheduleDataImport Start, KIND: {$kind} PATH: {$path}");

            $now = Carbon::now();
            $datas = [];

            // バッチ管理テーブルにレコード作成
            $batchMng = new BatchMng;
            $batchMng->batch_type = AppConst::BATCH_TYPE_21;
            $batchMng->start_time = $now;
            $batchMng->batch_state = AppConst::CODE_MASTER_22_99;
            $batchMng->adm_id = null;
            $batchMng->save();

            $batch_id = $batchMng->batch_id;

            try {
                // 入力ファイル名のチェック
                // .csvファイルではない場合にエラーとする
                if (strrchr($path, '.') != '.csv') {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(ファイル名不正)");
                }
                // ファイルが存在しない場合にエラーとする
                if (!file_exists($path)) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file') . "(ファイルパス・ファイル名不正)");
                }
                // CSVデータの読み込み・バリデーション
                $datas = $this->readData($path, $kind);

                if (empty($datas)) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(データ件数不正)");
                }
            } catch (ReadDataValidateException  $e) {
                // 通常は事前にバリデーションするのでここはありえないのでエラーとする
                throw $e;
            }

            // トランザクション(例外時は自動的にロールバック)
            DB::transaction(function () use ($batch_id, $kind, $datas) {

                $scheduleCount = 0;

                // 登録処理
                // データ種別に応じて、対象データを登録する
                if ($kind == self::KIND_SCHE_S) {
                    // スケジュールデータ（１対１授業）登録
                    foreach ($datas as $data) {
                        $this->regScheduleData($data);
                    }
                    $scheduleCount = count($datas);
                } else if ($kind == self::KIND_SCHE_D) {
                    // スケジュールデータ（１対多授業）登録
                    $collectData = collect($datas);
                    // startとendと講師IDの組み合わせで値が重複しないデータを取得
                    $lessonDatas = $collectData->unique(function ($item) {
                        return $item['start'] . " " . $item['end'] . " " . $item['teaID'];
                    });

                    foreach ($lessonDatas as $lessonData) {
                        Log::info("--------------------------------");
                        Log::info("ID:" .  $lessonData['ID'] . " start:" .  $lessonData['start'] . " end:" . $lessonData['end'] . " teaID:" . $lessonData['teaID']);
                        // schedulesの登録
                        $scheduleId = $this->regScheduleData($lessonData);

                        $members = $collectData->where('start', $lessonData['start'])
                            ->where('end', $lessonData['end'])
                            ->where('teaID', $lessonData['teaID']);
                        // class_membersの登録
                        $this->regScheduleMemberData($scheduleId, $members);
                    }
                    $scheduleCount = count($lessonDatas);
                } else if ($kind == self::KIND_REGU_S) {
                    // レギュラーデータ（１対１授業）登録
                    foreach ($datas as $data) {
                        $this->regRegularData($data);
                    }
                    $scheduleCount = count($datas);
                } else if ($kind == self::KIND_REGU_D) {
                    // レギュラーデータ（１対多授業）登録
                    $collectData = collect($datas);
                    // startとendと講師IDの組み合わせで値が重複しないデータを取得
                    $lessonDatas = $collectData->unique(function ($item) {
                        return $item['start'] . " " . $item['end'] . " " . $item['teaID'];
                    });
                    foreach ($lessonDatas as $lessonData) {
                        Log::info("--------------------------------");
                        Log::info("ID:" .  $lessonData['ID'] . " start:" .  $lessonData['start'] . " end:" . $lessonData['end'] . " teaID:" . $lessonData['teaID']);
                        // regular_classesの登録
                        $regularId = $this->regRegularData($lessonData);

                        $members = $collectData->where('start', $lessonData['start'])
                            ->where('end', $lessonData['end'])
                            ->where('teaID', $lessonData['teaID']);
                        // regular_class_membersの登録
                        foreach ($members as $member) {
                            Log::info("ID:" .  $member['ID'] . " stuID:" . $member['stuID']);
                        }
                        $this->regRegularMemberData($regularId, $members);
                    }
                    $scheduleCount = count($lessonDatas);
                }

                // バッチ管理テーブルのレコードを更新：正常終了
                $end = Carbon::now();
                BatchMng::where('batch_id', '=', $batch_id)
                    ->update([
                        'end_time' => $end,
                        'batch_state' => AppConst::CODE_MASTER_22_0,
                        'updated_at' => $end
                    ]);

                // 登録データ数カウント
                Log::info("Insert {$scheduleCount} schedule data. scheduleDataImport Succeeded.");
            });
        } catch (\Exception  $e) {
            // バッチ管理テーブルのレコードを更新：異常終了
            $end = Carbon::now();
            BatchMng::where('batch_id', '=', $batch_id)
                ->update([
                    'end_time' => $end,
                    'batch_state' => AppConst::CODE_MASTER_22_1,
                    'updated_at' => $end
                ]);
            // この時点では補足できないエラーとして、詳細は返さずエラーとする
            Log::error($e);
        }
        // 念のため明示的に捨てる
        $datas = null;
        return 0;
    }

    /**
     * スケジュールデータ登録
     *
     * @param $data
     */
    private function regScheduleData($data)
    {
        // 時間（分）の算出
        $start = Carbon::createFromTimestamp($data['start']);
        $end = Carbon::createFromTimestamp($data['end']);
        $minutes = $start->diffInMinutes($end);
        // 時限の取得
        $periodNo = $this->fncScheGetPeriodTimeForBatch($data['school_code'], AppConst::CODE_MASTER_37_0, $start->toTimeString());

        $schedule = new Schedule;
        $schedule['campus_cd'] = $data['school_code'];
        $schedule['target_date'] = $start->toDateString();
        $schedule['period_no'] = $periodNo;
        $schedule['start_time'] = $start->toTimeString();
        $schedule['end_time'] = $end->toTimeString();
        $schedule['minutes'] = $minutes;
        // ブースコードの設定
        if ($data['classroom'] >= self::POS_BOOTH_TRAN_MIN) {
            // 旧ブースが未振替用の場合
            // ブースマスタから対象校舎のブースを取得し、先頭ブースを設定
            $arrMstBooths = $this->fncScheGetBoothFromMst($data['school_code'], $data['how_to']);
            $schedule['booth_cd'] = $arrMstBooths[0];
        } else {
            // 上記以外の場合、元データのブースコードを設定する
            $schedule['booth_cd'] = $data['classroom'];
        }
        $schedule['course_cd'] = $data['course'];
        $schedule['tutor_id'] = $data['teaID'] == 0 ? null : $data['teaID'];
        if ($data['course_kind'] != AppConst::CODE_MASTER_42_2) {
            $schedule['student_id'] = $data['stuID'] == 0 ? null : $data['stuID'];
        }
        $schedule['subject_cd'] = $data['subject'] == 0 ? null : $data['subject'];
        // データ作成種別の設定
        if ($data['status'] == self::POS_CLASS_STATUS_TRAN) {
            // 旧ステータスが振替の場合、作成種別を振替とする
            $schedule['create_kind'] = AppConst::CODE_MASTER_32_2;
        } else {
            // 上記以外の場合、作成種別を個別とする
            $schedule['create_kind'] = AppConst::CODE_MASTER_32_1;
        }
        // 授業区分の設定
        switch ($data['status']) {
            case self::POS_CLASS_STATUS_TRAN:
                // 振替の場合、通常授業
                $schedule['lesson_kind'] = AppConst::CODE_MASTER_31_1;
                break;
            case self::POS_CLASS_STATUS_FST1:
            case self::POS_CLASS_STATUS_FST2:
            case self::POS_CLASS_STATUS_FST3:
                // 初回授業の場合、初回授業
                $schedule['lesson_kind'] = AppConst::CODE_MASTER_31_4;
                break;
            case self::POS_CLASS_STATUS_TRY1:
                // 体験授業1回目の場合、体験授業1回目
                $schedule['lesson_kind'] = AppConst::CODE_MASTER_31_5;
                break;
            case self::POS_CLASS_STATUS_TRY2:
                // 体験授業2回目の場合、体験授業2回目
                $schedule['lesson_kind'] = AppConst::CODE_MASTER_31_6;
                break;
            case self::POS_CLASS_STATUS_TRY3:
                // 体験授業3回目の場合、体験授業3回目
                $schedule['lesson_kind'] = AppConst::CODE_MASTER_31_7;
                break;
            case self::POS_CLASS_STATUS_ADD:
                // 追加授業の場合、追加授業
                $schedule['lesson_kind'] = AppConst::CODE_MASTER_31_3;
                break;
            default:
                // その他の場合、通常授業に寄せる
                $schedule['lesson_kind'] = AppConst::CODE_MASTER_31_1;
        }
        $schedule['how_to_kind'] = $data['how_to'];
        $schedule['substitute_kind'] = $data['substitute'];
        // 欠席講師IDの設定
        $schedule['absent_tutor_id'] = $data['emID'] == 0 ? null : $data['emID'];
        // 出欠ステータスの設定
        if ($data['classroom'] >= self::POS_BOOTH_TRAN_MIN) {
            // 旧ブースが未振替用の場合、出欠ステータスを未振替とする
            $schedule['absent_status'] = AppConst::CODE_MASTER_35_3;
        } else {
            // 上記以外の場合、元データの出欠ステータスを設定
            $schedule['absent_status'] = $data['absent_on_the_day'];
        }
        $schedule['tentative_status'] = AppConst::CODE_MASTER_36_0;
        $schedule['memo'] = "移行前ID:" . $data['ID'] . "\n" . $data['comment'];
        $schedule['adm_id'] = self::BATCH_USER;

        $schedule->save();
        return $schedule->schedule_id;
    }

    /**
     * 受講生徒データ登録（１対多授業のみ）
     *
     * @param $scheduleId
     * @param $members
     */
    private function regScheduleMemberData($scheduleId, $memberDatas)
    {
        foreach ($memberDatas as $memberData) {
            Log::info("ID:" .  $memberData['ID'] . " stuID:" . $memberData['stuID']);
            // class_membersテーブルへのinsert
            $classmember = new ClassMember;
            $classmember->schedule_id = $scheduleId;
            $classmember->student_id = $memberData['stuID'];
            $classmember->absent_status = $memberData['absent_on_the_day'];
            // 登録
            $classmember->save();
        }
    }

    /**
     * レギュラーデータ登録
     *
     * @param $data
     */
    private function regRegularData($data)
    {
        // 時間（分）の算出
        $start = Carbon::createFromTimestamp($data['start']);
        $end = Carbon::createFromTimestamp($data['end']);
        $minutes = $start->diffInMinutes($end);
        // 時限の取得
        $periodNo = $this->fncScheGetPeriodTimeForBatch($data['school_code'], AppConst::CODE_MASTER_37_0, $start->toTimeString());

        $schedule = new RegularClass;
        $schedule['campus_cd'] = $data['school_code'];
        $schedule['day_cd'] = $start->dayOfWeek;
        $schedule['period_no'] = $periodNo;
        $schedule['start_time'] = $start->toTimeString();
        $schedule['end_time'] = $end->toTimeString();
        $schedule['minutes'] = $minutes;
        $schedule['booth_cd'] = $data['classroom'];
        $schedule['course_cd'] = $data['course'];
        $schedule['tutor_id'] = $data['teaID'] == 0 ? null : $data['teaID'];
        if ($data['course_kind'] != AppConst::CODE_MASTER_42_2) {
            $schedule['student_id'] = $data['stuID'] == 0 ? null : $data['stuID'];
        }
        $schedule['subject_cd'] = $data['subject'] == 0 ? null : $data['subject'];
        $schedule['how_to_kind'] = $data['how_to'];

        $schedule->save();
        return $schedule->regular_class_id;
    }

    /**
     * レギュラー受講生徒データ登録（１対多授業のみ）
     *
     * @param $regularId
     * @param $members
     */
    private function regRegularMemberData($regularId, $memberDatas)
    {
        foreach ($memberDatas as $memberData) {
            Log::info("ID:" .  $memberData['ID'] . " stuID:" . $memberData['stuID']);
            // regular_class_membersテーブルへのinsert
            $classmember = new RegularClassMember;
            $classmember->regular_class_id = $regularId;
            $classmember->student_id = $memberData['stuID'];
            // 登録
            $classmember->save();
        }
    }

    /**
     * アップロードされたファイルを読み込む
     * バリデーションも行う
     *
     * @param $path
     * @param $kind
     * @return array データ
     */
    private function readData($path, $kind)
    {
        $csvHeaders = [
            'ID',
            'school_code',
            'start',
            'end',
            'course',
            'subject',
            'classroom',
            'teaID',
            'stuID',
            'status',
            'substitute',
            'emID',
            'absent_on_the_day',
            'how_to',
            'comment'
        ];

        // CSVのデータをリストで保持
        $datas = [];
        $headers = [];

        // コース種別取得
        $mstCourse = MstCourse::select(
            'course_cd',
            'course_kind'
        )
            ->get()->keyBy('course_cd');

        // CSV読み込み
        $file = $this->readCsv($path, "sjis");

        // 1行ずつ取得
        foreach ($file as $i => $line) {
            if ($i === 0) {
                //-------------
                // ヘッダ行
                //-------------
                $headers = $line;

                // [バリデーション] ヘッダが想定通りかチェック
                if ($headers !== $csvHeaders) {
                    throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                        . "(ヘッダ行不正)");
                }
                continue;
            }
            //-------------
            // データ行
            //-------------
            // [バリデーション] データ行の列の数のチェック
            if (count($line) !== count($csvHeaders)) {
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "(データ列数不正)");
            }

            // headerをもとに、値をセットしたオブジェクトを生成
            $values = array_combine($headers, $line);

            // 取得したコース種別を追加で設定
            $values['course_kind'] = $mstCourse[$values['course']]->course_kind;

            // [バリデーション] データ行の値のチェック
            $validator = Validator::make($values, $this->rulesForInput($values));
            if ($validator->fails()) {
                Log::info($validator->errors());
                $errCol = "";
                if ($validator->errors()->has('ID')) {
                    $errCol = "ID=" . $values['ID'];
                } else if ($validator->errors()->has('school_code')) {
                    $errCol = "school_code=" . $values['school_code'];
                } else if ($validator->errors()->has('start')) {
                    $errCol = "start=" . $values['start'];
                } else if ($validator->errors()->has('end')) {
                    $errCol = "end=" . $values['end'];
                } else if ($validator->errors()->has('course')) {
                    $errCol = "course=" . $values['course'];
                } else if ($validator->errors()->has('subject')) {
                    $errCol = "subject=" . $values['subject'];
                } else if ($validator->errors()->has('classroom')) {
                    $errCol = "classroom=" . $values['classroom'];
                } else if ($validator->errors()->has('teaID')) {
                    $errCol = "teaID=" . $values['teaID'];
                } else if ($validator->errors()->has('stuID')) {
                    $errCol = "stuID=" . $values['stuID'];
                } else if ($validator->errors()->has('status')) {
                    $errCol = "status=" . $values['status'];
                } else if ($validator->errors()->has('substitute')) {
                    $errCol = "substitute=" . $values['substitute'];
                } else if ($validator->errors()->has('emID')) {
                    $errCol = "emID=" . $values['emID'];
                } else if ($validator->errors()->has('absent_on_the_day')) {
                    $errCol = "absent_on_the_day=" . $values['absent_on_the_day'];
                } else if ($validator->errors()->has('how_to')) {
                    $errCol = "how_to=" . $values['how_to'];
                } else if ($validator->errors()->has('comment')) {
                    $errCol = "comment=" . $values['comment'];
                }
                throw new ReadDataValidateException(Lang::get('validation.invalid_file')
                    . "データ項目不正( " . $i + 1 . "行目 ID=" . $values['ID'] . ", "
                    . "エラー項目：" . $errCol . " )");
            }

            // 対象外データを読み飛ばす
            $start = Carbon::createFromTimestamp($values['start']);
            if ($kind == self::KIND_SCHE_S) {
                // スケジュールデータ（１対１授業）
                if ($start < '1971-01-01' || $values['course_kind'] == AppConst::CODE_MASTER_42_2) {
                    Log::info("SKIP ID=" . $values['ID']);
                    continue;
                }
            } else if ($kind == self::KIND_SCHE_D) {
                // スケジュールデータ（１対多授業）
                if ($start < '1971-01-01' || $values['course_kind'] != AppConst::CODE_MASTER_42_2) {
                    Log::info("SKIP ID=" . $values['ID']);
                    continue;
                }
            } else if ($kind == self::KIND_REGU_S) {
                // レギュラーデータ（１対１授業）
                if (
                    $start >= '1971-01-01'
                    || ($values['course_kind'] != AppConst::CODE_MASTER_42_1 && $values['course_kind'] != AppConst::CODE_MASTER_42_4)
                ) {
                    Log::info("SKIP ID=" . $values['ID']);
                    continue;
                }
            } else if ($kind == self::KIND_REGU_D) {
                // レギュラーデータ（１対多授業）
                if ($start >= '1971-01-01' || $values['course_kind'] != AppConst::CODE_MASTER_42_2) {
                    Log::info("SKIP ID=" . $values['ID']);
                    continue;
                }
            }

            foreach ($values as $key => $val) {
                // 空白はnullに変換
                if ($values[$key] === '') {
                    $values[$key] = null;
                }
            }

            // リストに保持
            $datas[] = $values;
        }

        return $datas;
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput(array $data)
    {
        $rules = array();

        // 独自バリデーション: リストのチェック 校舎
        $validationRoomList =  function ($attribute, $value, $fail) {

            // 校舎マスタより校舎情報を取得（ログイン情報がないので、共通処理は使わない）
            $rooms = MstCampus::query()
                ->select('mst_campuses.campus_cd as code', 'name as value', 'disp_order')
                // 非表示フラグの条件を付加
                ->where('is_hidden', AppConst::CODE_MASTER_11_1)
                // 校舎リストを取得
                ->get()->keyBy('code');

            if (!isset($rooms[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        // 独自バリデーション: リストのチェック コース
        $validationCourseList =  function ($attribute, $value, $fail) {
            // コースリストを取得
            $list = $this->mdlGetCourseList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        // 独自バリデーション: リストのチェック 科目
        $validationSubjectList =  function ($attribute, $value, $fail) {

            if (intval($value) == 0) {
                // 初期値の場合はチェックしない
                return;
            }
            // 科目リストを取得
            $list = $this->mdlGetSubjectList();
            if (!isset($list[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        // 独自バリデーション: リストのチェック 講師
        $validationTutorList =  function ($attribute, $value, $fail) {

            if (intval($value) == 0) {
                // 初期値の場合はチェックしない
                return;
            }
            // 講師リストを取得（ログイン情報がないので、共通処理は使わない）
            $list = Tutor::query()
                ->select('tutor_id as id')
                ->get()->keyBy('id');
            if (!isset($list[intval($value)])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        // 独自バリデーション: リストのチェック 生徒
        $validationStudentList =  function ($attribute, $value, $fail) {

            // 生徒リストを取得（ログイン情報がないので、共通処理は使わない）
            $list = Student::query()
                ->select('student_id as id')
                ->get()->keyBy('id');
            if (!isset($list[intval($value)])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };
        // 独自バリデーション: リストのチェック 授業代講区分
        $validationSubstituteKindList =  function ($attribute, $value, $fail) {

            // リストを取得し存在チェック
            $list = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_34);
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

        $rules += ['ID' => ['required', 'integer', 'min:1']];
        $rules += ['school_code' => array_merge(Schedule::getFieldRule('campus_cd'), ['required', $validationRoomList])];
        // 開始・終了日時はunixtimeで設定されている
        $rules += ['start' => ['required', 'date_format:U']];
        $rules += ['end' => ['required', 'date_format:U']];
        $rules += ['course' => array_merge(Schedule::getFieldRule('course_cd'), ['required', $validationCourseList])];
        $rules += ['subject' => ['required', $validationSubjectList]];
        $rules += ['classroom' => array_merge(Schedule::getFieldRule('booth_cd'), ['required'])];
        $rules += ['teaID' => array_merge(Schedule::getFieldRule('tutor_id'), ['required', $validationTutorList])];
        $rules += ['stuID' => array_merge(Schedule::getFieldRule('student_id'), ['required', $validationStudentList])];
        $rules += ['status' => ['required', 'integer', 'min:0', 'max:9']];
        $rules += ['substitute' => array_merge(Schedule::getFieldRule('substitute_kind'), [$validationSubstituteKindList])];
        $rules += ['emID' => array_merge(Schedule::getFieldRule('tutor_id'), ['required', $validationTutorList])];
        $rules += ['absent_on_the_day' => ['required', 'integer', 'min:0', 'max:2']];
        $rules += ['how_to' => array_merge(Schedule::getFieldRule('how_to_kind'), ['required', $validationHowToKindList])];
        $rules += ['comment' => ['string', 'max:1000']];

        return $rules;
    }
}
