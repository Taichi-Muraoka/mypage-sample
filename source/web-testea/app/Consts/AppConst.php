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
    // コードマスタ
    //==========================

    // MEMO: コードマスタの値を定義する。
    // 定数名として、きちんと名前をつけるのが面倒なので、種別_コードとした。
    // PHPDoc書けば、名称が分かるので。

    //-----------
    // 状態
    //-----------

    /**
     * 申請ステータス
     */
    const CODE_MASTER_1 = 1;

    /**
     * 申請ステータス 未対応
     */
    const CODE_MASTER_1_0 = 0;

    /**
     * 申請ステータス 対応済
     */
    const CODE_MASTER_1_1 = 1;

    //-----------
    // 承認ステータス
    //-----------

    /**
     * 承認ステータス
     */
    const CODE_MASTER_2 = 2;

    /**
     * 承認ステータス 承認待ち
     */
    const CODE_MASTER_2_0 = 1;

    /**
     * 承認ステータス 承認
     */
    const CODE_MASTER_2_1 = 2;

    /**
     * 承認ステータス 差戻し
     */
    const CODE_MASTER_2_2 = 3;

    //-----------
    // 校舎コード
    //-----------

    /**
     * 校舎コード
     */
    const CODE_MASTER_6 = 6;

    /**
     * 校舎の教室コード
     */
    const CODE_MASTER_6_0 = '0';

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
     * 講師
     */
    const CODE_MASTER_7_2 = 2;

    /**
     * 事務局
     */
    const CODE_MASTER_7_3 = 3;

    //-----------
    // 可否フラグ
    //-----------

    /**
     * 非表示フラグ
     */
    const CODE_MASTER_9 = 9;

    /**
     * 非表示フラグ 可
     */
    const CODE_MASTER_9_1 = 0;

    /**
     * 非表示フラグ 不可
     */
    const CODE_MASTER_9_2 = 1;

    //-----------
    // 非表示フラグ
    //-----------

    /**
     * 非表示フラグ
     */
    const CODE_MASTER_11 = 11;

    /**
     * 非表示フラグ 表示
     */
    const CODE_MASTER_11_1 = 0;

    /**
     * 非表示フラグ 非表示
     */
    const CODE_MASTER_11_2 = 1;

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
     * 回答状態種別
     */
    const CODE_MASTER_17 = 17;

    /**
     * 回答状態 未回答
     */
    const CODE_MASTER_17_0 = 0;

    /**
     * 回答状態 回答済
     */
    const CODE_MASTER_17_1 = 1;

    //-----------
    // 請求情報ファイル項目定義
    //-----------

    /**
     * 請求情報ファイル項目定義
     */
//    const CODE_MASTER_18 = 18;


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

    //-----------
    // 生徒会員ステータス
    //-----------

    /**
     * 生徒会員ステータス
     */
    const CODE_MASTER_28 = 28;

    /**
     * 生徒会員ステータス 見込客
     */
    const CODE_MASTER_28_0 = 0;

    /**
     * 生徒会員ステータス 在籍
     */
    const CODE_MASTER_28_1 = 1;

    /**
     * 生徒会員ステータス 休塾予定
     */
    const CODE_MASTER_28_2 = 2;

    /**
     * 生徒会員ステータス 休塾
     */
    const CODE_MASTER_28_3 = 3;

    /**
     * 生徒会員ステータス 退会処理中
     */
    const CODE_MASTER_28_4 = 4;

    /**
     * 生徒会員ステータス 退会済
     */
    const CODE_MASTER_28_5 = 5;

    /**
     * 生徒会員ステータス
     */
    const CODE_MASTER_29 = 29;

    /**
     * 生徒会員ステータス 在籍
     */
    const CODE_MASTER_29_1 = 1;

    /**
     * 生徒会員ステータス 退会処理中
     */
    const CODE_MASTER_29_2 = 2;

    /**
     * 生徒会員ステータス 退会
     */
    const CODE_MASTER_29_3 = 3;

    //-----------
    // 時間割区分
    //-----------

    /**
     * 時間割区分
     */
    const CODE_MASTER_37 = 37;

    //-----------
    // 期間区分
    //-----------

    /**
     * 期間区分
     */
    const CODE_MASTER_38 = 38;


    //-----------
    // 用途種別
    //-----------

    /**
     * 用途種別
     */
    const CODE_MASTER_41 = 41;

    /**
     * 用途種別 授業用
     */
    const CODE_MASTER_41_1 = 1;

    /**
     * 用途種別 オンライン専用
     */
    const CODE_MASTER_41_2 = 2;

    /**
     * 用途種別 面談用
     */
    const CODE_MASTER_41_3 = 3;

    /**
     * 用途種別 両者オンライン（システム登録用）
     */
    const CODE_MASTER_41_4 = 4;

    /**
     * 用途種別 家庭教師（システム登録用）
     */
    const CODE_MASTER_41_5 = 5;

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
     * グループ種類	講師
     */
    const NOTICE_GROUP_TYPE_2 = 2;

    /**
     * お知らせグループID 講師
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
