<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => '個別指導塾TESTEA',
    'title_prefix' => '',
    'title_postfix' => '- 個別指導塾TESTEA',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => true,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => 'TESTEA マイページ',
    'login_logo_img' => 'img/co_logo_board_ol.svg',  // ログイン画面のロゴ
    'logo_img' => 'img/cw_logo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'TESTEA',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => true,
        'img' => [
            'path' => 'img/cw_logo.png',
            'alt' => 'TESTEA',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => false,
        'img' => [
            'path' => 'img/cw_logo.png',
            'alt' => 'TESTEA Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    //'classes_brand_text' => '',
    'classes_brand_text' => 'text-sm',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    //'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar' => 'sidebar-light-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    //'sidebar_mini' => 'lg',
    'sidebar_mini' => 'true',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    // サイドバーのClass
    'sidebar_nav_classes' => 'text-sm',

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => '/',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => '',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Mix option for the admin panel.
    |
    | For detailed instructions you can look the laravel mix section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        //----------------
        // 生徒向け
        //----------------
        [
            'text' => 'お知らせ',
            'route'  => 'notice',
            'icon' => 'fas fa-exclamation-circle',
            'can'  => 'student',
            'active' => ['notice/*']
        ],
        [
            'text' => 'カレンダー',
            'route'  => 'calendar',
            'icon' => 'far fa-calendar-alt',
            'can'  => 'student',
            'active' => ['calendar/*']
        ],
        [
            'text' => '問い合わせ',
            'route'  => 'contact',
            'icon' => 'far fa-envelope',
            'can'  => 'student',
            'active' => ['contact/*']
        ],
        [
            'header' => '生徒向け',
            'can'  => 'student',
        ],
        [
            'text' => '授業報告書',
            'route'  => 'report',
            'icon' => 'fas fa-chalkboard-teacher',
            'can'  => 'student',
            'active' => ['report/*']
        ],
        [
            'text' => '欠席申請',
            'route'  => 'absent',
            'icon' => 'far fa-times-circle',
            'can'  => 'student',
            'active' => ['absent/*']
        ],
        [
            'text' => '振替授業調整',
            'route'  => 'transfer_student',
            'icon' => 'fas fa-money-check',
            'can'  => 'student',
            'active' => ['transfer_student/*']
        ],
        [
            'text' => '生徒成績',
            'route'  => 'grades',
            'icon' => 'fas fa-chart-line',
            'can'  => 'student',
            'active' => ['grades/*']
        ],
        [
            'text' => '模試・イベント申込',
            'route'  => 'event',
            'icon' => 'fas fa-edit',
            'can'  => 'student',
            'active' => ['event/*']
        ],

        [
            'header' => '契約',
            'can'  => 'student',
        ],
        [
            'text' => '契約内容',
            'route'  => 'agreement',
            'icon' => 'fas fa-file-contract',
            'can'  => 'student',
            'active' => ['agreement/*']
        ],
        [
            'text' => 'コース変更・授業追加申請',
            'route'  => 'course',
            'icon' => 'fas fa-file-signature',
            'can'  => 'student',
            'active' => ['course/*']
        ],
        [
            'text' => '面談日程連絡',
            'route'  => 'conference',
            'icon' => 'fas fa-file-signature',
            'can'  => 'student',
            'active' => ['conference/*']
        ],
        [
            'text' => '請求情報',
            'route'  => 'invoice',
            'icon' => 'fas fa-file',
            'can'  => 'student',
            'active' => ['invoice/*']
        ],

        [
            'header' => 'ギフトカード',
            'can'  => 'student',
        ],
        [
            'text' => 'ギフトカード',
            'route'  => 'card',
            'icon' => 'fas fa-gift',
            'can'  => 'student',
            'active' => ['card/*']
        ],

        [
            'header' => 'アカウント設定',
            'can'  => 'student',
        ],
        [
            'text' => 'パスワード変更',
            'route'  => 'password_change',
            'icon' => 'fas fa-user-cog',
            'can'  => 'student',
            'active' => ['password_change/*']
        ],
        [
            'text' => '退会申請',
            'route'  => 'leave',
            'icon' => 'fas fa-user-minus',
            'can'  => 'student',
            'active' => ['leave/*']
        ],

        //----------------
        // 教師
        //----------------
        [
            'text' => 'お知らせ',
            'route'  => 'notice',
            'icon' => 'fas fa-exclamation-circle',
            'can'  => 'tutor',
            'active' => ['notice/*']
        ],
        [
            'text' => 'カレンダー',
            'route'  => 'calendar',
            'icon' => 'far fa-calendar-alt',
            'can'  => 'tutor',
            'active' => ['calendar/*']
        ],
        [
            'header' => '教師向け',
            'can'  => 'tutor',
        ],
        [
            'text' => '授業報告書',
            'route'  => 'report_regist',
            'icon' => 'fas fa-chalkboard-teacher',
            'can'  => 'tutor',
            'active' => ['report_regist/*']
        ],
        [
            'text' => '空き時間',
            'route'  => 'weekly_shift',
            'icon' => 'fas fa-clock',
            'can'  => 'tutor',
            'active' => ['weekly_shift/*']
        ],
        [
            'text' => '回数報告',
            'route'  => 'times_regist',
            'icon' => 'fas fa-history',
            'can'  => 'tutor',
            'active' => ['times_regist/*']
        ],
        [
            'text' => '生徒成績',
            'route'  => 'grades_check',
            'icon' => 'fas fa-chart-line',
            'can'  => 'tutor',
            'active' => ['grades_check/*']
        ],
        [
            'text' => '給与明細',
            'route'  => 'salary',
            'icon' => 'fas fa-wallet',
            'can'  => 'tutor',
            'active' => ['salary/*']
        ],
        [
            'text' => '研修受講',
            'route'  => 'training',
            'icon' => 'fas fa-book-open',
            'can'  => 'tutor',
            'active' => ['training/*']
        ],
        // [
        //     // 個別指導向け
        //     'text' => '振替連絡',
        //     'route'  => 'transfer',
        //     'icon' => 'fas fa-money-check',
        //     'can'  => 'tutor',
        //     'active' => ['transfer/*']
        // ],
        [
            'text' => '振替授業調整',
            'route'  => 'transfer_tutor',
            'icon' => 'fas fa-money-check',
            'can'  => 'tutor',
            'active' => ['transfer_tutor/*']
        ],
        [
            'header' => 'アカウント設定',
            'can'  => 'tutor',
        ],
        [
            'text' => 'パスワード変更',
            'route'  => 'password_change',
            'icon' => 'fas fa-user-cog',
            'can'  => 'tutor',
            'active' => ['password_change/*']
        ],

        //----------------
        // 管理者
        //----------------
        [
            'text' => '会員管理',
            'icon' => 'fa fa-user-cog',
            'can'  => 'admin',
            // 以下、AppMenuFilterでカウントを出す場合の目印とした。textやrouteが変わった時にMenuFilterも直さないといけないのを防ぐ
            'menuid' => 'id_member',
            'submenu' => [
                [
                    'text' => '会員管理',
                    'route' => 'member_mng',
                    // サブメニューの場合、'member_mng/*'と指定するとうまくactiveにならないので以下にした
                    'active' => ['member_mng*']
                ],
                [
                    'text' => '生徒カルテ',
                    'route' => 'karte',
                    'active' => ['karte*']
                ],
                [
                    'text' => 'コース変更・授業追加',
                    'route' => 'course_mng',
                    'active' => ['course_mng*'],
                    'menuid' => 'id_course_accept',
                ],
                [
                    'text' => '会員情報取込',
                    'route'  => 'member_import',
                    'active' => ['member_import*']
                ],
                [
                    'text' => '退会申請受付',
                    'route'  => 'leave_accept',
                    'active' => ['leave_accept*'],
                    // 以下、AppMenuFilterでカウントを出す場合の目印とした。textやrouteが変わった時にMenuFilterも直さないといけないのを防ぐ
                    'menuid' => 'id_leave_accept',
                ],
            ]
        ],
        [
            'text' => '教師管理',
            'icon' => 'fa fa-user-tie',
            'can'  => 'admin',
            'submenu' => [
                [
                    'text' => '教師情報',
                    'route' => 'tutor_mng',
                    'active' => ['tutor_mng*']
                ],
                [
                    'text' => '教師登録',
                    'route' => 'tutor_regist',
                    'active' => ['tutor_regist*']
                ],
                [
                    'text' => '回数報告',
                    'route' => 'times_check',
                    'active' => ['times_check*']
                ],
            ]
        ],
        [
            'text' => '授業管理',
            'icon' => 'fa fa-chalkboard-teacher',
            'can'  => 'admin',
            'menuid' => 'id_lesson',
            'submenu' => [
                [
                    'text' => '欠席申請受付',
                    'route' => 'absent_accept',
                    'active' => ['absent_accept*'],
                    'menuid' => 'id_absent_accept',
                ],
                // [
                //     'text' => '振替連絡受付',
                //     'route' => 'transfer_accept',
                //     'active' => ['transfer_accept*'],
                //     'menuid' => 'id_transfer_accept',
                // ],
                [
                    'text' => '振替連絡受付',
                    'route' => 'transfer_regist',
                    'active' => ['transfer_regist*'],
                    'menuid' => 'id_transfer_regist',
                ],
                [
                    'text' => '授業報告',
                    'route' => 'report_check',
                    'active' => ['report_check*']
                ],
                [
                    'text' => '生徒成績',
                    'route' => 'grades_mng',
                    'active' => ['grades_mng*']
                ],
                [
                    'text' => 'スケジュール取込',
                    'route' => 'schedule_import',
                    'active' => ['schedule_import*']
                ],
            ]
        ],
        [
            'text' => '模試・イベント管理',
            'icon' => 'fa fa-edit',
            'can'  => 'admin',
            'menuid' => 'id_trial_event',
            'submenu' => [
                [
                    'text' => '模試管理',
                    'route' => 'trial_mng',
                    'active' => ['trial_mng*'],
                    'menuid' => 'id_trial_mng',
                ],
                [
                    'text' => 'イベント管理',
                    'route' => 'event_mng',
                    'active' => ['event_mng*'],
                    'menuid' => 'id_event_mng',
                ],
            ]
        ],
        [
            'text' => 'ギフトカード管理',
            'icon' => 'fa fas fa-gift',
            'can'  => 'admin',
            'menuid' => 'id_card',
            'submenu' => [
                [
                    'text' => 'ギフトカード管理',
                    'route' => 'card_mng',
                    'active' => ['card_mng*'],
                    'menuid' => 'id_card_mng'
                ],
            ]
        ],
        [
            'text' => '問い合わせ管理',
            'icon' => 'fa fa-envelope',
            'can'  => 'admin',
            'menuid' => 'id_contact',
            'submenu' => [
                [
                    'text' => '問い合わせ管理',
                    'route' => 'contact_mng',
                    'active' => ['contact_mng*'],
                    'menuid' => 'id_contact_mng',
                ],
            ]
        ],
        [
            'text' => '研修管理',
            'icon' => 'fa fa-book-open',
            'can'  => 'admin',
            'submenu' => [
                [
                    'text' => '研修管理',
                    'route' => 'training_mng',
                    'active' => ['training_mng*']
                ],
            ]
        ],
        [
            'text' => '請求情報管理',
            'icon' => 'fa fa-file',
            'can'  => 'admin',
            'submenu' => [
                [
                    'text' => '請求情報取込',
                    'route' => 'invoice_import',
                    'active' => ['invoice_import*']
                ],
            ]
        ],
        [
            'text' => '給与情報管理',
            'icon' => 'fa fa-wallet',
            'can'  => 'admin',
            'submenu' => [
                [
                    'text' => '給与情報取込',
                    'route' => 'salary_import',
                    'active' => ['salary_import*']
                ],
            ]
        ],
        [
            'text' => 'お知らせ管理',
            'icon' => 'fa fa-exclamation-circle',
            'can'  => 'admin',
            'submenu' => [
                [
                    'text' => 'お知らせ通知',
                    'route' => 'notice_regist',
                    'active' => ['notice_regist*']
                ],
                [
                    'text' => 'お知らせ定型文登録',
                    'route' => 'notice_template',
                    'active' => ['notice_template*']
                ],
            ]
        ],
        [
            'text' => '年次更新',
            'icon' => 'fa fa-calendar-check',
            'can'  => 'admin',
            'submenu' => [
                [
                    'text' => '学年情報取込',
                    'route' => 'all_member_import',
                    'active' => ['all_member_import*']
                ],
                [
                    'text' => '年度スケジュール取込',
                    'route' => 'year_schedule_import',
                    'active' => ['year_schedule_import*']
                ],
            ]
        ],
        [
            'text' => '休業日管理',
            'icon' => 'fa fa-calendar-alt',
            'can'  => 'admin',
            'submenu' => [
                [
                    'text' => '休業日登録',
                    'route' => 'room_holiday',
                    'active' => ['room_holiday*']
                ],
            ]
        ],
        [
            'text' => 'システム管理',
            'icon' => 'fa fa-cog',
            'can'  => 'admin',
            'submenu' => [
                [
                    'text' => 'マスタ管理',
                    'route' => 'master_mng',
                    'active' => ['master_mng*']
                ],
                [
                    'text' => '事務局アカウント管理',
                    'route' => 'account_mng',
                    'active' => ['account_mng*']
                ],
            ]
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
        // カスタムメニュー
        App\Filters\AppMenuFilter::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        // 'Datatables' => [
        //     'active' => false,
        //     'files' => [
        //         [
        //             'type' => 'js',
        //             'asset' => false,
        //             'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
        //         ],
        //         [
        //             'type' => 'js',
        //             'asset' => false,
        //             'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
        //         ],
        //         [
        //             'type' => 'css',
        //             'asset' => false,
        //             'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
        //         ],
        //     ],
        // ],

        // CSVダウンロード時に、SJISに変換するために使用した
        'encoding' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/encoding/encoding.min.js',
                ]
            ],
        ],

        'icheck' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/icheck-bootstrap/icheck-bootstrap.min.css',
                ]
            ],
        ],

        'moment' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/moment/moment.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/moment/locale/ja.js',
                ],
            ],
        ],

        'fullcalendar' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/fullcalendar/main.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/fullcalendar/locales/ja.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    //'location' => 'vendor/fullcalendar/main.min.css',
                    'location' => 'vendor/fullcalendar/main.css',
                ],
            ],
        ],

        'daterangepicker' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/daterangepicker/daterangepicker.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/daterangepicker/daterangepicker.css',
                ],
            ],
        ],

        'bootbox' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/bootbox/bootbox.min.js',
                ],
            ],
        ],

        'Select2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/select2/js/select2.full.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/select2/css/select2.min.css',
                ],
                [
                    'type' => 'css',
                    'asset' => true,
                    'location' => 'vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css',
                ],
            ],
        ],

        'bs-custom-file-input' => [
            // ファイル選択のカスタム用
            // https://github.com/Johann-S/bs-custom-file-input
            // https://blog1.mammb.com/entry/2019/12/11/090000
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'vendor/bs-custom-file-input/bs-custom-file-input.min.js',
                ]
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
