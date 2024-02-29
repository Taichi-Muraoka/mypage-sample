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

    /**
     * パスワードリセット用URL（新規入会時のメールに記載）
     *
     * 未設定の場合はAPP_URLにする
     */
    "url_password_reset" => env('URL_PASSWORD_RESET', env('APP_URL')),

    /**
     * ログイン用URL
     *
     * 未設定の場合はAPP_URLにする
     */
    "url_login" => env('URL_LOGIN', env('APP_URL')),

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
     * 年度スケジュール情報取込
     */
    "upload_dir_year_schedule_import" =>  "file_upload/year_schedule_import/",

    /**
     * 学校コード取込
     */
    "upload_dir_school_code_import" =>  "file_upload/school_code_import/",

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

    /**
     * ダウンロードファイルの格納先(ルート)
     */
    "download_dir" => "file_download/",

    /**
     * 保持期限超過データバックアップ先
     */
    "download_dir_exceeding_data_backup" => "file_download/exceeding_data_backup/",

    /**
     * 保持期限超過データバックアップZIPファイル名
     */
    "download_file_name_exceeding_data_backup" => "保持期限超過データ削除バックアップ_",

    /**
     * 振替残数リセットバックアップ先
     */
    "download_dir_transfer_reset_backup" => "file_download/transfer_reset_backup/",

    //==========================
    // 画面ごと設定
    // マスタなどに持たないような設定
    //==========================

    /**
     * 時限リスト（時間割マスタ用）
     */
    'period_no' => [
        1 => ["value" => '1限'],
        2 => ["value" => '2限'],
        3 => ["value" => '3限'],
        4 => ["value" => '4限'],
        5 => ["value" => '5限'],
        6 => ["value" => '6限'],
        7 => ["value" => '7限'],
        8 => ["value" => '8限'],
        9 => ["value" => '9限'],
        10 => ["value" => '10限'],
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
    // MEMO:'term'には通塾期間の検索用に月数範囲を指定（Studentモデルで使用）
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
    "conference_time" => 60,

    /**
     * 給与明細表示
     * item_name
     */
    "subtotal_withholding" => '源泉計算用小計',
];
