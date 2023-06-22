<?php

namespace App\Consts;

/**
 * アプリケーションで使用する定数を定義したクラス
 */
class AppConst
{

    // MEMO:エイリアスに指定したので、useに入れなくても以下でも行けた。
    // VSCode上エラーになるので、可能な限りuseに指定する。
    // \AppConst::GENDER_MAN;

    //==========================
    // 汎用マスタ
    //==========================

    /**
     * コード区分 000
     */
    const EXT_GENERIC_MASTER_000 = '000';

    /**
     * コード区分 000 教室名
     */
    const EXT_GENERIC_MASTER_000_101 = '101';

    /**
     * コード区分 000 授業分類
     */
    const EXT_GENERIC_MASTER_000_109 = '109';

    /**
     * コード区分 000 学年
     */
    const EXT_GENERIC_MASTER_000_112 = '112';

    /**
     * コード区分 000 教科
     */
    const EXT_GENERIC_MASTER_000_114 = '114';

    /**
     * コード区分 101 家庭教師
     */
    const EXT_GENERIC_MASTER_101_900 = '900';

    /**
     * コード区分 102 請求情報
     */
    const EXT_GENERIC_MASTER_102 = '102';

    /**
     * コード区分 102 請求情報 振込
     */
    const EXT_GENERIC_MASTER_102_1 = '1';

    /**
     * コード区分 102 請求情報 カード
     */
    const EXT_GENERIC_MASTER_102_2 = '2';

    /**
     * コード区分 102 請求情報 持込
     */
    const EXT_GENERIC_MASTER_102_3 = '3';

    /**
     * コード区分 102 請求情報 郵貯
     */
    const EXT_GENERIC_MASTER_102_4 = '4';

    /**
     * コード区分 102 請求情報 七十七
     */
    const EXT_GENERIC_MASTER_102_5 = '5';

    /**
     * コード区分 102 請求情報 他行
     */
    const EXT_GENERIC_MASTER_102_6 = '6';

    /**
     * コード区分 102 請求情報 JC引落
     */
    const EXT_GENERIC_MASTER_102_7 = '7';

    /**
     * コード区分 109 レギュラー
     */
    const EXT_GENERIC_MASTER_109_0 = '0';

    /**
     * コード区分 109 個別講習
     */
    const EXT_GENERIC_MASTER_109_1 = '1';

    /**
     * コード区分 109 模擬試験
     */
    const EXT_GENERIC_MASTER_109_3 = '3';

    /**
     * コード区分 109 家庭教師
     */
    const EXT_GENERIC_MASTER_109_4 = '4';

    /**
     * コード区分 112 学年
     */
    const EXT_GENERIC_MASTER_112 = '112';

    /**
     * コード区分 114 教科
     */
    const EXT_GENERIC_MASTER_114 = '114';

    /**
     * コード区分 114 教科 小学
     */
    const EXT_GENERIC_MASTER_114_0 = '0';

    /**
     * コード区分 114 教科 小学 表示上限
     */
    const EXT_GENERIC_MASTER_114_0_MAX = '005';

    /**
     * コード区分 114 教科 中学
     */
    const EXT_GENERIC_MASTER_114_1 = '1';

    /**
     * コード区分 114 教科 中学 表示上限
     */
    const EXT_GENERIC_MASTER_114_1_MAX = '105';

    /**
     * コード区分 114 教科 高校
     */
    const EXT_GENERIC_MASTER_114_2 = '2';

    /**
     * コード区分 114 教科 高校 表示上限
     */
    const EXT_GENERIC_MASTER_114_2_MAX = '250';

    /**
     * コード区分 114 教科 一般
     */
    const EXT_GENERIC_MASTER_114_9 = '9';

    /**
     * コード区分 114 教科 一般 表示上限
     */
    const EXT_GENERIC_MASTER_114_9_MAX = '950';

    //==========================
    // コードマスタ
    //==========================

    // MEMO: コードマスタの値を定義する。
    // 定数名として、きちんと名前をつけるのが面倒なので、種別_コードとした。
    // PHPDoc書けば、名称が分かるので。

    //-----------
    // 状態
    //-----------

    /**
     * 状態
     */
    const CODE_MASTER_1 = 1;

    /**
     * 状態	未対応
     */
    const CODE_MASTER_1_0 = 0;

    /**
     * 状態 対応済み
     */
    const CODE_MASTER_1_1 = 1;

    //-----------
    // 変更状態
    //-----------

    /**
     * 変更状態
     */
    const CODE_MASTER_2 = 2;

    /**
     * 変更状態	未対応
     */
    const CODE_MASTER_2_0 = 0;

    /**
     * 変更状態 受付
     */
    const CODE_MASTER_2_1 = 1;

    /**
     * 変更状態	対応済
     */
    const CODE_MASTER_2_2 = 2;

    //-----------
    // 申込状態
    //-----------

    /**
     * 申込状態
     */
    const CODE_MASTER_3 = 3;

    /**
     * 申込状態	未対応
     */
    const CODE_MASTER_3_0 = 0;

    /**
     * 申込状態	受付済
     */
    const CODE_MASTER_3_1 = 1;

    //-----------
    // ギフトカード状態
    //-----------

    /**
     * ギフトカード状態
     */
    const CODE_MASTER_4 = 4;

    /**
     * ギフトカード状態 未使用
     */
    const CODE_MASTER_4_0 = 0;

    /**
     * ギフトカード状態 申請中
     */
    const CODE_MASTER_4_1 = 1;

    /**
     * ギフトカード状態 使用受付
     */
    const CODE_MASTER_4_2 = 2;

    //-----------
    // 退会状態
    //-----------

    /**
     * 退会状態
     */
    const CODE_MASTER_5 = 5;

    /**
     * 退会状態	未対応
     */
    const CODE_MASTER_5_0 = 0;

    /**
     * 退会状態	受付
     */
    const CODE_MASTER_5_1 = 1;

    /**
     * 退会状態	退会済
     */
    const CODE_MASTER_5_3 = 3;

    //-----------
    // 教室コード
    //-----------

    /**
     * 教室コード
     */
    const CODE_MASTER_6 = 6;

    /**
     * 本部の教室コード
     */
    const CODE_MASTER_6_0 = '0';

    /**
     * 教室コード（汎用マスタのコード区分）
     */
    const CODE_MASTER_6_0_101 = 101;

    //-----------
    // アカウント種類
    //-----------

    /**
     * アカウント種別
     */
    const CODE_MASTER_7 = 7;

    /**
     * 生徒
     */
    const CODE_MASTER_7_1 = 1;

    /**
     * 教師
     */
    const CODE_MASTER_7_2 = 2;

    /**
     * 事務局
     */
    const CODE_MASTER_7_3 = 3;

    //-----------
    // 授業種類
    //-----------

    /**
     * 授業種類
     */
    const CODE_MASTER_8 = 8;

    /**
     * 個別教室
     */
    const CODE_MASTER_8_1 = 1;

    /**
     * 家庭教師
     */
    const CODE_MASTER_8_2 = 2;

    //-----------
    // 試験種別
    //-----------

    /**
     * 試験種別
     */
    const CODE_MASTER_9 = 9;

    /**
     * 模試
     */
    const CODE_MASTER_9_1 = 1;

    /**
     * 定期考査
     */
    const CODE_MASTER_9_2 = 2;

    //-----------
    // 定期考査
    //-----------

    /**
     * 定期考査名
     */
    const CODE_MASTER_10 = 10;

    //-----------
    // 前回比
    //-----------

    /**
     * 前回比
     */
    const CODE_MASTER_11 = 11;

    //-----------
    // 研修
    //-----------

    /**
     * 研修
     */
    const CODE_MASTER_12 = 12;

    /**
     * 研修 資料
     */
    const CODE_MASTER_12_1 = 1;

    /**
     * 研修 動画
     */
    const CODE_MASTER_12_2 = 2;

    //-----------
    // コース変更種別
    //-----------

    /**
     * コース変更種別
     */
    const CODE_MASTER_13 = 13;

    /**
     * コース変更種別 （個別）短期講習申込
     */
    const CODE_MASTER_13_4 = 4;

    //-----------
    // お知らせ種別
    //-----------

    /**
     * お知らせ種別
     */
    const CODE_MASTER_14 = 14;

    /**
     * お知らせ種別 模試
     */
    const CODE_MASTER_14_1 = 1;

    /**
     * お知らせ種別 イベント
     */
    const CODE_MASTER_14_2 = 2;

    /**
     * お知らせ種別 短期個別講習
     */
    const CODE_MASTER_14_3 = 3;

    /**
     * お知らせ種別 その他
     */
    const CODE_MASTER_14_4 = 4;

    /**
     * お知らせ種別 面談
     */
    const CODE_MASTER_14_5 = 5;

    /**
     * お知らせ種別 特別期間講習
     */
    const CODE_MASTER_14_6 = 6;

    /**
     * お知らせ種別 成績登録
     */
    const CODE_MASTER_14_7 = 7;

    //-----------
    // 宛先種別
    //-----------

    /**
     * 宛先種別
     */
    const CODE_MASTER_15 = 15;

    /**
     * 宛先種別 グループ一斉
     */
    const CODE_MASTER_15_1 = 1;

    /**
     * 宛先種別 個別（生徒）
     */
    const CODE_MASTER_15_2 = 2;

    /**
     * 宛先種別 個別（教師）
     */
    const CODE_MASTER_15_3 = 3;

    //-----------
    // 曜日コード
    //-----------

    /**
     * 曜日コード
     */
    const CODE_MASTER_16 = 16;

    //-----------
    // 回答状態
    //-----------

    /**
     * 回答状態 未回答
     */
    const CODE_MASTER_17_0 = 0;

    /**
     * 回答状態種別
     */
    const CODE_MASTER_17 = 17;

    //-----------
    // 請求情報ファイル項目定義
    //-----------

    /**
     * 請求情報ファイル項目定義
     */
    const CODE_MASTER_18 = 18;

    /**
     * 請求情報ファイル項目定義 サブコード 個別教室
     */
    const CODE_MASTER_18_SUB_1 = 1;

    /**
     * 請求情報ファイル項目定義 サブコード 家庭教師
     */
    const CODE_MASTER_18_SUB_2 = 2;

    //-----------
    // 給与情報ファイル項目定義
    //-----------

    /**
     * 給与情報ファイル項目定義
     */
    const CODE_MASTER_19 = 19;

    //-----------
    // 取込状態
    //-----------

    /**
     * 取込状態
     */
    const CODE_MASTER_20 = 20;

    /**
     * 取込状態 取込未
     */
    const CODE_MASTER_20_0 = 0;

    /**
     * 取込状態 取込済
     */
    const CODE_MASTER_20_1 = 1;

    //-----------
    // スケジュール種別
    //-----------

    /**
     * スケジュール種別
     */
    const CODE_MASTER_21 = 21;

    /**
     * スケジュール種別 個別授業
     */
    const CODE_MASTER_21_1 = 1;

    /**
     * スケジュール種別 短期講習
     */
    const CODE_MASTER_21_2 = 2;

    /**
     * スケジュール種別 模試
     */
    const CODE_MASTER_21_3 = 3;

    /**
     * スケジュール種別 イベント
     */
    const CODE_MASTER_21_4 = 4;

    /**
     * スケジュール種別 休業日
     */
    const CODE_MASTER_21_5 = 5;

    /**
     * スケジュール種別 打ち合せ
     */
    const CODE_MASTER_21_6 = 6;

    //-----------
    // バッチ状態
    //-----------

    /**
     * バッチ状態
     */
    const CODE_MASTER_22 = 22;

    /**
     * バッチ状態 正常終了
     */
    const CODE_MASTER_22_0 = 0;

    /**
     * バッチ状態 異常終了
     */
    const CODE_MASTER_22_1 = 1;

    /**
     * バッチ状態 実行中
     */
    const CODE_MASTER_22_99 = 99;

    //==========================
    // アカウント情報
    //==========================

    // MEMO: アカウント情報の値を定義する。
    // 定数名として、きちんと名前をつけるのが面倒なので、種別_コードとした。
    // PHPDoc書けば、名称が分かるので。

    //-----------
    // パスワード初期化
    //-----------

    /**
     * パスワード初期化	不要
     */
    const ACCOUNT_PWRESET_0 = 0;

    /**
     * パスワード初期化	必要
     */
    const ACCOUNT_PWRESET_1 = 1;

    //==========================
    // お知らせグループ情報
    //==========================

    // MEMO: お知らせグループ情報の種別。

    /**
     * グループ種類	生徒
     */
    const NOTICE_GROUP_TYPE_1 = 1;

    /**
     * グループ種類	教師
     */
    const NOTICE_GROUP_TYPE_2 = 2;

    /**
     * お知らせグループID 教師
     */
    const NOTICE_GROUP_ID_15 = 15;

    //==========================
    // カレンダー
    //==========================

    // MEMO: カレンダーの作成区分コード / 出欠・振替コード

    /**
     * 作成区分 一括作成
     */
    const CREATE_KIND_CD_1 = 1;

    /**
     * 作成区分 後日振替
     */
    const CREATE_KIND_CD_2 = 2;

    /**
     * 作成区分 増コマまたは分割振替
     */
    const CREATE_KIND_CD_3 = 3;

    /**
     * 出欠・振替コード 後日振替
     */
    const ATD_STATUS_CD_2 = 2;

    /**
     * 振替区分コード 振替日設定済
     */
    const TRANSEFER_KIND_CD_1 = 1;

    //==========================
    // 給与情報明細
    //==========================

    // MEMO: 給与表示グループ

    /**
     * 給与表示グループ 支給
     */
    const SALARY_GROUP_1 = 1;

    /**
     * 給与表示グループ 控除
     */
    const SALARY_GROUP_2 = 2;

    /**
     * 給与表示グループ その他
     */
    const SALARY_GROUP_3 = 3;

    /**
     * 給与表示グループ 合計
     */
    const SALARY_GROUP_4 = 4;

    //-----------
    // バッチ種別
    //-----------

    /**
     * バッチ種別 年次生徒情報
     */
    const BATCH_TYPE_1 = 1;

    /**
     * バッチ種別 年次スケジュール情報
     */
    const BATCH_TYPE_2 = 2;

    /**
     * バッチ種別 保存期間超過データ削除
     */
    const BATCH_TYPE_3 = 3;

    /**
     * バッチ種別 データベースバックアップ
     */
    const BATCH_TYPE_4 = 4;

    /**
     * バッチ種別 年次初期データ作成
     */
    const BATCH_TYPE_5 = 5;

    /**
     * バッチ種別 会員情報データ移行
     */
    const BATCH_TYPE_6 = 6;
}
