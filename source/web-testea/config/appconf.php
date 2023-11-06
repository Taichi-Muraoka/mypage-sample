<?php

/*
|--------------------------------------------------------------------------
| アプリケーションに関する設定
|--------------------------------------------------------------------------
*/
return [

    //==========================
    // env
    //==========================

    /**
     * 欠席申請時の管理者のメールアドレス
     *
     * 未設定の場合はMAIL_FROM_ADDRESS（管理者想定）にする
     */
    "mail_absent_to_address" => env('MAIL_ABSENT_TO_ADDRESS', env('MAIL_FROM_ADDRESS')),

    //==========================
    // 画面共通
    //==========================

    /**
     * ページネータの1ページあたりの件数
     */
    "page_count" => 20,

    //==========================
    // ファイルのアップロード先
    //==========================

    /**
     * ファイルのアップロード先(ルート)
     */
    "upload_dir" => "file_upload/",

    /**
     * 汎用マスタファイル取込
     */
    "upload_dir_master_mng" =>  "file_upload/master_mng/",

    /**
     * 模試情報取込
     */
    "upload_dir_trial_mng" =>  "file_upload/trial_mng/",

    /**
     * 教師情報取込
     */
    "upload_dir_tutor_regist" =>  "file_upload/tutor_regist/",

    /**
     * 生徒情報取込
     */
    "upload_dir_member_import" =>  "file_upload/member_import/",

    /**
     * スケジュール情報取込
     */
    "upload_dir_schedule_import" =>  "file_upload/schedule_import/",

    /**
     * 研修資料
     */
    "upload_dir_training" =>  "file_upload/training/",

    /**
     * 給与情報取込
     */
    "upload_dir_salary_import" =>  "file_upload/salary_import/",

    /**
     * 請求情報取込
     */
    "upload_dir_invoice_import" =>  "file_upload/invoice_import/",

    /**
     * 年次学年情報取込
     */
    "upload_dir_all_member_import" =>  "file_upload/all_member_import/",

    /**
     * 年度スケジュール情報取込
     */
    "upload_dir_year_schedule_import" =>  "file_upload/year_schedule_import/",

    //==========================
    // アップロードファイル名
    //==========================

    /**
     * 汎用マスタファイル名
     */
    "upload_file_name_master_mng" =>  "汎用マスタ情報_",

    /**
     * 模試マスタファイル名
     */
    "upload_file_name_trial_mng" =>  "模試マスタ情報_",

    /**
     * 教師情報ファイル名
     */
    "upload_file_name_tutor_regist" =>  "教師情報_",

    /**
     * スケジュール情報ファイル名
     */
    "upload_file_name_schedule_import" =>  "スケジュール情報_",

    /**
     * 模試申込ファイル名
     */
    "upload_file_name_schedule_import_trial" =>  "模試申込_",

    /**
     * 入会者情報ファイル名（生徒）
     */
    "upload_file_name_member_import_enter" =>  "入会者情報_",

    /**
     * 授業追加・コース追加変更情報ファイル名（生徒）
     */
    "upload_file_name_member_import_course" =>  "コース変更_",

    /**
     * 短期講習申込情報ファイル名（生徒）
     */
    "upload_file_name_member_import_individual" =>  "短期講習申込_",

    /**
     * 年次学年情報ファイル名（生徒）
     */
    "upload_file_name_all_member_import_enter" =>  "年次学年情報_",

    /**
     * 年度スケジュール情報ファイル名（生徒）
     */
    "upload_file_name_year_schedule_import" =>  "年次スケジュール情報_",

    //==========================
    // 解凍されたCSVファイル名（連携テーブル名）
    //==========================

    /**
     * A04教室情報
     */
    "upload_file_csv_name_A04" =>  "a04room",

    /**
     * A05生徒基本情報
     */
    "upload_file_csv_name_A05" =>  "a05student_kihon",

    /**
     * A10個別規定情報
     */
    "upload_file_csv_name_A10" =>  "a10regular",

    /**
     * A11個別規定情報明細
     */
    "upload_file_csv_name_A11" =>  "a11regular_detail",

    /**
     * A30個別講習情報
     */
    "upload_file_csv_name_A30" =>  "a30extra_individual",

    /**
     * A31個別講習情報明細
     */
    "upload_file_csv_name_A31" =>  "a31extra_ind_detail",

    /**
     * A60家庭教師標準情報
     */
    "upload_file_csv_name_A60" =>  "a60home_teacher_std",

    /**
     * A61家庭教師標準情報詳細
     */
    "upload_file_csv_name_A61" =>  "a61home_teacher_std_detail",

    /**
     * T01スケジュール情報
     */
    "upload_file_csv_name_T01" =>  "t01schedule",

    //==========================
    // バッチ処理
    //==========================

    /**
     * データベースバックアップの保存世代数
     */
    "db_backup_generation" => 7,

    /**
     * データベースバックアップ先
     */
    "db_backup_dir" => 'db_backup/',

    //==========================
    // 画面ごと設定
    // マスタなどに持たないような設定
    //==========================

    /**
     * 空き時間登録の時間
     */
    'weekly_shift_time' => array(
        '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30',
        '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
        '18:00', '18:30', '19:00', '19:30', '20:00', '20:30'
    ),

    /**
     * 模試・イベント申込の参加人数
     */
    'event_members' => [
        1 => ["value" => 1],
        2 => ["value" => 2],
        3 => ["value" => 3],
        4 => ["value" => 4],
        5 => ["value" => 5],
    ],

    /**
     * 模試・イベント申込の参加人数
     */
    'period_no' => [
        1 => ["value" => '1時限'],
        2 => ["value" => '2時限'],
        3 => ["value" => '3時限'],
        4 => ["value" => '4時限'],
        5 => ["value" => '5時限'],
        6 => ["value" => '6時限'],
        7 => ["value" => '7時限'],
        8 => ["value" => '8時限'],
        9 => ["value" => '9時限'],
        10 => ["value" => '10時限'],
    ],

    /**
     * 教室カレンダー登録繰り返し回数
     */
    'repeat_times' => [
        1 => ["value" => '1'],
        2 => ["value" => '2'],
        3 => ["value" => '3'],
        4 => ["value" => '4'],
        5 => ["value" => '5'],
        6 => ["value" => '6'],
        7 => ["value" => '7'],
        8 => ["value" => '8'],
        9 => ["value" => '9'],
        10 => ["value" => '10'],
        11 => ["value" => '11'],
        12 => ["value" => '12'],
        13 => ["value" => '13'],
        14 => ["value" => '14'],
        15 => ["value" => '15'],
    ],

    /**
     * 授業時間チェック 開始時刻
     */
    'lesson_start_time_min' => '08:00:00',

    /**
     * 教室カレンダー 固定ブースエリア定義
     */
    'timetable_boothId' => '000',
    'timetable_booth' => [
        'id' => '000',
        'title' => '時間割'
    ],

    'transfer_boothId' => '999',
    'transfer_booth' => [
        'id' => '999',
        'title' => '未振替・振替中'
    ],

    /**
     * 会員一覧 通塾期間プルダウンリスト
     */
    // MEMO:'term'には通塾期間の検索用に月数範囲を指定（StudentViewモデルで使用）
    'enter_term' => [
        1 => [
            "term" => 1,
            "value" => "0～1ヶ月"
        ],
        2 => [
            "term" => [2, 3],
            "value" => "2～3ヶ月"
        ],
        3 => [
            "term" => [4, 6],
            "value" => "4～6ヶ月"
        ],
        4 => [
            "term" => [7, 12],
            "value" => "7ヶ月～1年"
        ],
        5 => [
            "term" => [13, 24],
            "value" => "1年～2年"
        ],
        6 => [
            "term" => [25, 36],
            "value" => "2年～3年"
        ],
        7 => [
            "term" => [37, 48],
            "value" => "3年～4年"
        ],
        8 => [
            "term" => [49, 60],
            "value" => "4年～5年"
        ],
        9 => [
            "term" => [61, 72],
            "value" => "5年～6年"
        ],
        10 => [
            "term" => [73, 84],
            "value" => "6年～7年"
        ],
        11 => [
            "term" => [85, 96],
            "value" => "7年～8年"
        ],
        12 => [
            "term" => 97,
            "value" => "8年～"
        ],
    ],

    /**
     * 削除アカウントのメールアドレスに付加する文字列
     */
    "delete_email_prefix" => 'DEL',
    "delete_email_suffix" => '@',

    /**
     * 削除アカウントのメールアドレスに付加する文字列の抽出条件
     * 末尾に「DELyyyymmddhhmmss@」
     */
    "delete_email_rule" => '/^.+(DEL[0-9]{14}@)$/',

    /**
     * 面談時間
     */
    "conference_time" => 60
];
