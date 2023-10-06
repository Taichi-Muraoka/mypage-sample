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
    // 振替承認ステータス
    //-----------

    /**
     * 振替承認ステータス
     */
    const CODE_MASTER_3 = 3;

    /**
     * 承認ステータス 管理者承認待ち
     */
    const CODE_MASTER_3_0 = 0;

    /**
     * 承認ステータス 承認待ち
     */
    const CODE_MASTER_3_1 = 1;

    /**
     * 承認ステータス 承認
     */
    const CODE_MASTER_3_2 = 2;

    /**
     * 承認ステータス 差戻し（日程不都合）
     */
    const CODE_MASTER_3_3 = 3;

    /**
     * 承認ステータス 差戻し（代講希望）
     */
    const CODE_MASTER_3_4 = 4;

    /**
     * 承認ステータス 管理者対応済
     */
    const CODE_MASTER_3_5 = 5;

    /**
     * 承認ステータス サブコード 生徒用
     */
    const CODE_MASTER_3_SUB_1 = 1;

    //-----------
    // 報告書承認ステータス
    //-----------

    /**
     * 報告書承認ステータス
     */
    const CODE_MASTER_4 = 4;

    /**
     * 承認ステータス 対象外
     */
    const CODE_MASTER_4_0 = 0;

    /**
     * 承認ステータス 承認待ち
     */
    const CODE_MASTER_4_1 = 1;

    /**
     * 承認ステータス 承認
     */
    const CODE_MASTER_4_2 = 2;

    /**
     * 承認ステータス 差戻し
     */
    const CODE_MASTER_4_3 = 3;

    /**
     * 承認ステータス サブコード 報告書用
     */
    const CODE_MASTER_4_SUB_1 = 1;

    //-----------
    // 登録ステータス
    //-----------

    /**
     * 登録ステータス
     */
    const CODE_MASTER_5 = 5;

    /**
     * 登録ステータス 未登録
     */
    const CODE_MASTER_5_0 = 0;

    /**
     * 登録ステータス 登録済
     */
    const CODE_MASTER_5_1 = 1;

    //-----------
    // 校舎コード
    //-----------

    /**
     * 校舎コード
     */
    const CODE_MASTER_6 = 6;

    /**
     * 校舎コード 本部
     */
    const CODE_MASTER_6_0 = '00';

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
     * 管理者
     */
    const CODE_MASTER_7_3 = 3;

    //-----------
    // ログインID種別
    //-----------

    /**
     * ログインID種別
     */
    const CODE_MASTER_8 = 8;

    /**
     * 生徒メールアドレス
     */
    const CODE_MASTER_8_1 = 1;

    /**
     * 保護者メールアドレス
     */
    const CODE_MASTER_8_2 = 2;

    //-----------
    // 可否フラグ
    //-----------

    /**
     * 可否フラグ
     */
    const CODE_MASTER_9 = 9;

    /**
     * 可
     */
    const CODE_MASTER_9_1 = 0;

    /**
     * 不可
     */
    const CODE_MASTER_9_2 = 1;

    //-----------
    // 生徒プラン種別
    //-----------

    /**
     * 生徒プラン種別
     */
    const CODE_MASTER_10 = 10;

    /**
     * 通常プラン
     */
    const CODE_MASTER_10_0 = 0;

    /**
     * ハイプラン
     */
    const CODE_MASTER_10_1 = 1;

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
    // 研修資料
    //-----------

    /**
     * 研修資料
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
    // 受験生フラグ
    //-----------

    /**
     * 受験生フラグ
     */
    const CODE_MASTER_13 = 13;

    /**
     * 非受験生
     */
    const CODE_MASTER_13_0 = 0;

    /**
     * 受験生
     */
    const CODE_MASTER_13_1 = 1;

    //-----------
    // お知らせ種別
    //-----------

    /**
     * お知らせ種別
     */
    const CODE_MASTER_14 = 14;

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

    /**
     * お知らせ種別 請求
     */
    const CODE_MASTER_14_8 = 8;

    /**
     * お知らせ種別 給与
     */
    const CODE_MASTER_14_9 = 9;

    /**
     * お知らせ種別 追加請求
     */
    const CODE_MASTER_14_10 = 10;

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

    /**
     * 宛先種別 個別（保護者メール）
     */
    const CODE_MASTER_15_4 = 4;

    //-----------
    // 曜日コード
    //-----------

    /**
     * 曜日コード
     */
    const CODE_MASTER_16 = 16;

    /**
     * 月
     */
    const CODE_MASTER_16_1 = 1;

    /**
     * 火
     */
    const CODE_MASTER_16_2 = 2;

    /**
     * 水
     */
    const CODE_MASTER_16_3 = 3;

    /**
     * 木
     */
    const CODE_MASTER_16_4 = 4;

    /**
     * 金
     */
    const CODE_MASTER_16_5 = 5;

    /**
     * 土
     */
    const CODE_MASTER_16_6 = 6;

    /**
     * 日
     */
    const CODE_MASTER_16_7 = 7;

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

    /**
     * 授業給（個別）
     */
    const CODE_MASTER_19_1 = 1;

    /**
     * 授業給（1対2）
     */
    const CODE_MASTER_19_2 = 2;

    /**
     * 授業給（1対3）
     */
    const CODE_MASTER_19_3 = 3;

    /**
     * 授業給（集団）
     */
    const CODE_MASTER_19_4 = 4;

    /**
     * 授業給（家庭教師）
     */
    const CODE_MASTER_19_5 = 5;

    /**
     * 授業給（演習）
     */
    const CODE_MASTER_19_6 = 6;

    /**
     * 授業給（ハイプラン）
     */
    const CODE_MASTER_19_7 = 7;

    /**
     * 授業給（インターン）
     */
    const CODE_MASTER_19_8 = 8;

    /**
     * 作業給
     */
    const CODE_MASTER_19_9 = 9;

    /**
     * 特別報酬
     */
    const CODE_MASTER_19_10 = 10;

    /**
     * ペナルティ
     */
    const CODE_MASTER_19_11 = 11;

    /**
     * 源泉計算用小計
     */
    const CODE_MASTER_19_12 = 12;

    /**
     * 交通費
     */
    const CODE_MASTER_19_13 = 13;

    /**
     * 経費精算
     */
    const CODE_MASTER_19_14 = 14;

    /**
     * 年末調整
     */
    const CODE_MASTER_19_15 = 15;

    /**
     * 源泉徴収月額
     */
    const CODE_MASTER_19_16 = 16;

    /**
     * 住民税
     */
    const CODE_MASTER_19_17 = 17;

    /**
     * 支払金額
     */
    const CODE_MASTER_19_18 = 18;

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
    // 支払方法
    //-----------

    /**
     * 支払方法
     */
    const CODE_MASTER_21 = 21;

    /**
     * 支払方法 口座引落
     */
    const CODE_MASTER_21_1 = 1;

    /**
     * 支払方法 振込
     */
    const CODE_MASTER_21_2 = 2;

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
    // バッチ種別
    //-----------

    /**
     * バッチ種別
     */
    const CODE_MASTER_23 = 23;

    /**
     * 生徒退会処理
     */
    const CODE_MASTER_23_1 = 1;

    /**
     * 講師退職処理
     */
    const CODE_MASTER_23_2 = 2;

    /**
     * 生徒休塾処理
     */
    const CODE_MASTER_23_3 = 3;

    /**
     * リマインドメール配信
     */
    const CODE_MASTER_23_4 = 4;

    /**
     * データベースバックアップ
     */
    const CODE_MASTER_23_5 = 5;

    /**
     * 生徒学年更新
     */
    const CODE_MASTER_23_11 = 11;

    /**
     * 振替残数リセット
     */
    const CODE_MASTER_23_12 = 12;

    /**
     * 保持期限超過データ削除
     */
    const CODE_MASTER_23_13 = 13;

    /**
     * 年次初期データ作成
     */
    const CODE_MASTER_23_14 = 14;

    /**
     * データ移行
     */
    const CODE_MASTER_23_21 = 21;

    //-----------
    // 集計処理状態
    //-----------

    /**
     * 集計処理状態
     */
    const CODE_MASTER_24 = 24;

    /**
     * 未処理
     */
    const CODE_MASTER_24_0 = 0;

    /**
     * 集計済
     */
    const CODE_MASTER_24_1 = 1;

    /**
     * 確定済
     */
    const CODE_MASTER_24_2 = 2;

    //-----------
    // 給与集計種別
    //-----------

    /**
     * 給与集計種別
     */
    const CODE_MASTER_25 = 25;

    /**
     * 授業給算出対象外
     */
    const CODE_MASTER_25_0 = 0;

    /**
     * 授業給（個別）
     */
    const CODE_MASTER_25_1 = 1;

    /**
     * 授業給（1対2）
     */
    const CODE_MASTER_25_2 = 2;

    /**
     * 授業給（1対3）
     */
    const CODE_MASTER_25_3 = 3;

    /**
     * 授業給（集団）
     */
    const CODE_MASTER_25_4 = 4;

    /**
     * 授業給（家庭教師）
     */
    const CODE_MASTER_25_5 = 5;

    /**
     * 授業給（演習）
     */
    const CODE_MASTER_25_6 = 6;

    /**
     * 授業給（ハイプラン）
     */
    const CODE_MASTER_25_7 = 7;

    /**
     * 事務作業
     */
    const CODE_MASTER_25_8 = 8;

    /**
     * 経費（源泉計算対象）
     */
    const CODE_MASTER_25_9 = 9;

    /**
     * 経費（源泉計算対象外）
     */
    const CODE_MASTER_25_10 = 10;

    //-----------
    // 請求種別
    //-----------

    /**
     * 請求種別
     */
    const CODE_MASTER_26 = 26;

    /**
     * 研修（本部）
     */
    const CODE_MASTER_26_11 = 11;

    /**
     * 研修（教室）
     */
    const CODE_MASTER_26_12 = 12;

    /**
     * 業務依頼（本部）
     */
    const CODE_MASTER_26_13 = 13;

    /**
     * 業務依頼（教室）
     */
    const CODE_MASTER_26_14 = 14;

    /**
     * 特別交通費
     */
    const CODE_MASTER_26_21 = 21;

    /**
     * 経費
     */
    const CODE_MASTER_26_22 = 22;

    /**
     * 生徒獲得
     */
    const CODE_MASTER_26_31 = 31;

    /**
     * その他
     */
    const CODE_MASTER_26_32 = 32;

    //-----------
    // 支払状況
    //-----------

    /**
     * 支払状況
     */
    const CODE_MASTER_27 = 27;

    /**
     * 未処理
     */
    const CODE_MASTER_27_0 = 0;

    /**
     * 支払済
     */
    const CODE_MASTER_27_1 = 1;

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

    //-----------
    // 講師会員ステータス
    //-----------

    /**
     * 講師会員ステータス
     */
    const CODE_MASTER_29 = 29;

    /**
     * 講師会員ステータス 在籍
     */
    const CODE_MASTER_29_1 = 1;

    /**
     * 講師会員ステータス 退職処理中
     */
    const CODE_MASTER_29_2 = 2;

    /**
     * 講師会員ステータス 退職済
     */
    const CODE_MASTER_29_3 = 3;

    //-----------
    // 性別コード
    //-----------

    /**
     * 性別コード
     */
    const CODE_MASTER_30 = 30;

    /**
     * 男性
     */
    const CODE_MASTER_30_1 = 1;

    /**
     * 女性
     */
    const CODE_MASTER_30_2 = 2;

    /**
     * 不明・その他
     */
    const CODE_MASTER_30_9 = 9;

    //-----------
    // 授業区分
    //-----------

    /**
     * 授業区分
     */
    const CODE_MASTER_31 = 31;

    /**
     * 通常授業
     */
    const CODE_MASTER_31_1 = 1;

    /**
     * 特別期間講習
     */
    const CODE_MASTER_31_2 = 2;

    /**
     * 追加授業
     */
    const CODE_MASTER_31_3 = 3;

    /**
     * 初回授業
     */
    const CODE_MASTER_31_4 = 4;

    /**
     * 体験授業1回目
     */
    const CODE_MASTER_31_5 = 5;

    /**
     * 体験授業2回目
     */
    const CODE_MASTER_31_6 = 6;

    /**
     * 体験授業3回目
     */
    const CODE_MASTER_31_7 = 7;

    //-----------
    // データ作成区分
    //-----------

    /**
     * データ作成区分
     */
    const CODE_MASTER_32 = 32;

    /**
     * 一括
     */
    const CODE_MASTER_32_0 = 0;

    /**
     * 個別
     */
    const CODE_MASTER_32_1 = 1;

    /**
     * 振替
     */
    const CODE_MASTER_32_2 = 2;

    //-----------
    // 通塾種別
    //-----------

    /**
     * 通塾種別
     */
    const CODE_MASTER_33 = 33;

    /**
     * 両者通塾
     */
    const CODE_MASTER_33_0 = 0;

    /**
     * 生徒オンライン
     */
    const CODE_MASTER_33_1 = 1;

    /**
     * 両者オンライン
     */
    const CODE_MASTER_33_2 = 2;

    /**
     * 講師オンライン
     */
    const CODE_MASTER_33_3 = 3;

    /**
     * 家庭教師
     */
    const CODE_MASTER_33_4 = 4;

    //-----------
    // 授業代講種別
    //-----------

    /**
     * 授業代講種別
     */
    const CODE_MASTER_34 = 34;

    /**
     * なし
     */
    const CODE_MASTER_34_0 = 0;

    /**
     * 代講
     */
    const CODE_MASTER_34_1 = 1;

    /**
     * 緊急代講
     */
    const CODE_MASTER_34_2 = 2;

    //-----------
    // 出欠ステータス
    //-----------

    /**
     * 出欠ステータス
     */
    const CODE_MASTER_35 = 35;

    /**
     * 実施前・出席
     */
    const CODE_MASTER_35_0 = 0;

    /**
     * 当日欠席（講師出勤あり）
     */
    const CODE_MASTER_35_1 = 1;

    /**
     * 当日欠席（講師出勤なし）
     */
    const CODE_MASTER_35_2 = 2;

    /**
     * 未振替
     */
    const CODE_MASTER_35_3 = 3;

    /**
     * 振替中
     */
    const CODE_MASTER_35_4 = 4;

    /**
     * 振替済
     */
    const CODE_MASTER_35_5 = 5;

    /**
     * 欠席（1対多授業）
     */
    const CODE_MASTER_35_6 = 6;

    /**
     * 振替リセット済
     */
    const CODE_MASTER_35_7 = 7;

    /**
     * 出欠ステータス サブコード 共通
     */
    const CODE_MASTER_35_SUB_0 = 0;

    /**
     * 出欠ステータス サブコード １対１のみ
     */
    const CODE_MASTER_35_SUB_1 = 1;

    /**
     * 出欠ステータス サブコード １対多のみ
     */
    const CODE_MASTER_35_SUB_2 = 2;

    //-----------
    // 仮登録フラグ
    //-----------

    /**
     * 仮登録フラグ
     */
    const CODE_MASTER_36 = 36;

    /**
     * 本登録
     */
    const CODE_MASTER_36_0 = 0;

    /**
     * 仮登録
     */
    const CODE_MASTER_36_1 = 1;

    //-----------
    // 時間割区分
    //-----------

    /**
     * 時間割区分
     */
    const CODE_MASTER_37 = 37;

    /**
     * 通常
     */
    const CODE_MASTER_37_0 = 0;

    /**
     * 特別期間
     */
    const CODE_MASTER_37_1 = 1;

    //-----------
    // 期間区分
    //-----------

    /**
     * 期間区分
     */
    const CODE_MASTER_38 = 38;

    /**
     * 通常
     */
    const CODE_MASTER_38_0 = 0;

    /**
     * 春期特別期間
     */
    const CODE_MASTER_38_1 = 1;

    /**
     * 夏期特別期間
     */
    const CODE_MASTER_38_2 = 2;

    /**
     * 冬期特別期間
     */
    const CODE_MASTER_38_3 = 3;

    /**
     * 休日
     */
    const CODE_MASTER_38_9 = 9;

    //-----------
    // 学校区分
    //-----------

    /**
     * 学校区分
     */
    const CODE_MASTER_39 = 39;

    /**
     * 小学校
     */
    const CODE_MASTER_39_1 = 1;

    /**
     * 中学校
     */
    const CODE_MASTER_39_2 = 2;

    /**
     * 高等学校
     */
    const CODE_MASTER_39_3 = 3;

    /**
     * その他
     */
    const CODE_MASTER_39_4 = 4;

    //-----------
    // 講師学校区分
    //-----------

    /**
     * 講師学校区分
     */
    const CODE_MASTER_40 = 40;

    /**
     * 大学
     */
    const CODE_MASTER_40_1 = 5;

    /**
     * 大学院
     */
    const CODE_MASTER_40_2 = 6;

    /**
     * その他
     */
    const CODE_MASTER_40_3 = 7;

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

    //-----------
    // コース種別
    //-----------

    /**
     * コース種別
     */
    const CODE_MASTER_42 = 42;

    /**
     * 授業単
     */
    const CODE_MASTER_42_1 = 1;

    /**
     * 授業複
     */
    const CODE_MASTER_42_2 = 2;

    /**
     * 面談
     */
    const CODE_MASTER_42_3 = 3;

    /**
     * その他
     */
    const CODE_MASTER_42_4 = 4;

    //-----------
    // 種別
    //-----------

    /**
     * 種別
     */
    const CODE_MASTER_43 = 43;

    /**
     * 模試
     */
    const CODE_MASTER_43_0 = 0;

    /**
     * 定期考査
     */
    const CODE_MASTER_43_1 = 1;

    /**
     * 通知票評定
     */
    const CODE_MASTER_43_2 = 2;

    //-----------
    // 学期
    //-----------

    /**
     * 学期
     */
    const CODE_MASTER_44 = 44;

    /**
     * 1学期（前期）
     */
    const CODE_MASTER_44_1 = 1;

    /**
     * 2学期（後期）
     */
    const CODE_MASTER_44_2 = 2;

    /**
     * 3学期
     */
    const CODE_MASTER_44_3 = 3;

    /**
     * 学年
     */
    const CODE_MASTER_44_4 = 4;

    //-----------
    // 定期試験種別
    //-----------

    /**
     * 定期試験種別
     */
    const CODE_MASTER_45 = 45;

    /**
     * 1学期（前期）中間考査
     */
    const CODE_MASTER_45_1 = 1;

    /**
     * 1学期（前期）末考査
     */
    const CODE_MASTER_45_2 = 2;

    /**
     * 2学期（後期）中間考査
     */
    const CODE_MASTER_45_3 = 3;

    /**
     * 2学期（後期）末考査
     */
    const CODE_MASTER_45_4 = 4;

    /**
     * 3学期中間考査
     */
    const CODE_MASTER_45_5 = 5;

    /**
     * 3学期末考査
     */
    const CODE_MASTER_45_6 = 6;

    /**
     * 学年末考査
     */
    const CODE_MASTER_45_7 = 7;

    //-----------
    // 連絡記録種別
    //-----------

    /**
     * 連絡記録種別
     */
    const CODE_MASTER_46 = 46;

    /**
     * 面談
     */
    const CODE_MASTER_46_1 = 1;

    /**
     * 電話
     */
    const CODE_MASTER_46_2 = 2;

    /**
     * メール
     */
    const CODE_MASTER_46_3 = 3;

    /**
     * LINE
     */
    const CODE_MASTER_46_4 = 4;

    /**
     * 退会
     */
    const CODE_MASTER_46_5 = 5;

    /**
     * その他
     */
    const CODE_MASTER_46_6 = 6;

    //-----------
    // コマ組み状態
    //-----------

    /**
     * コマ組み状態
     */
    const CODE_MASTER_47 = 47;

    /**
     * 未対応
     */
    const CODE_MASTER_47_0 = 0;

    /**
     * 対応中
     */
    const CODE_MASTER_47_1 = 1;

    /**
     * 対応済
     */
    const CODE_MASTER_47_2 = 2;

    //-----------
    // コマ組み確定処理状態
    //-----------

    /**
     * コマ組み確定処理状態
     */
    const CODE_MASTER_48 = 48;

    /**
     * 受付期間前
     */
    const CODE_MASTER_48_0 = 0;

    /**
     * 未確定
     */
    const CODE_MASTER_48_1 = 1;

    /**
     * 確定済
     */
    const CODE_MASTER_48_2 = 2;

    //-----------
    // 学校種
    //-----------

    /**
     * 学校種
     */
    const CODE_MASTER_49 = 49;

    /**
     * 幼稚園
     */
    const CODE_MASTER_49_1 = 1;

    /**
     * こども園
     */
    const CODE_MASTER_49_2 = 2;

    /**
     * 小学校
     */
    const CODE_MASTER_49_3 = 3;

    /**
     * 中学校
     */
    const CODE_MASTER_49_4 = 4;

    /**
     * 義務
     */
    const CODE_MASTER_49_5 = 5;

    /**
     * 高校
     */
    const CODE_MASTER_49_6 = 6;

    /**
     * 中等
     */
    const CODE_MASTER_49_7 = 7;

    /**
     * 特支・養護
     */
    const CODE_MASTER_49_8 = 8;

    /**
     * 専修
     */
    const CODE_MASTER_49_9 = 9;

    /**
     * 各種
     */
    const CODE_MASTER_49_10 = 10;

    /**
     * 大学
     */
    const CODE_MASTER_49_11 = 11;

    /**
     * 短大
     */
    const CODE_MASTER_49_12 = 12;

    /**
     * 高専
     */
    const CODE_MASTER_49_13 = 13;

    //-----------
    // 設置区分
    //-----------

    /**
     * 設置区分
     */
    const CODE_MASTER_50 = 50;

    /**
     * 国
     */
    const CODE_MASTER_50_1 = 1;

    /**
     * 公
     */
    const CODE_MASTER_50_2 = 2;

    /**
     * 私
     */
    const CODE_MASTER_50_3 = 3;

    //-----------
    // 本分校区分
    //-----------

    /**
     * 本分校区分
     */
    const CODE_MASTER_51 = 51;

    /**
     * 本
     */
    const CODE_MASTER_51_1 = 1;

    /**
     * 分
     */
    const CODE_MASTER_51_2 = 2;

    /**
     * 廃
     */
    const CODE_MASTER_51_9 = 9;

    //-----------
    // 合否種別
    //-----------

    /**
     * 合否種別
     */
    const CODE_MASTER_52 = 52;

    /**
     * 受験前
     */
    const CODE_MASTER_52_0 = 0;

    /**
     * 合格
     */
    const CODE_MASTER_52_1 = 1;

    /**
     * 合格（進学）
     */
    const CODE_MASTER_52_2 = 2;

    /**
     * 補欠合格
     */
    const CODE_MASTER_52_3 = 3;

    /**
     * 不合格
     */
    const CODE_MASTER_52_4 = 4;

    /**
     * 不受験
     */
    const CODE_MASTER_52_5 = 5;

    //-----------
    // 申請者種別
    //-----------

    /**
     * 申請者種別
     */
    const CODE_MASTER_53 = 53;

    /**
     * 生徒
     */
    const CODE_MASTER_53_1 = 1;

    /**
     * 講師
     */
    const CODE_MASTER_53_2 = 2;

    //-----------
    // 振替代講区分
    //-----------

    /**
     * 振替代講区分
     */
    const CODE_MASTER_54 = 54;

    /**
     * 振替
     */
    const CODE_MASTER_54_1 = 1;

    /**
     * 代講
     */
    const CODE_MASTER_54_2 = 2;

    //-----------
    // バッジ種別
    //-----------

    /**
     * バッジ種別
     */
    const CODE_MASTER_55 = 55;

    /**
     * 紹介
     */
    const CODE_MASTER_55_1 = 1;

    /**
     * 通塾
     */
    const CODE_MASTER_55_2 = 2;

    /**
     * 成績
     */
    const CODE_MASTER_55_3 = 3;

    /**
     * その他
     */
    const CODE_MASTER_55_4 = 4;

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
     * 給与表示グループ 源泉計算対象
     */
    const SALARY_GROUP_1 = 1;

    /**
     * 給与表示グループ 源泉計算対象外
     */
    const SALARY_GROUP_2 = 2;

    /**
     * 給与表示グループ 控除
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
     * バッチ種別 生徒退会処理
     */
    const BATCH_TYPE_1 = 1;

    /**
     * バッチ種別 講師退職処理
     */
    const BATCH_TYPE_2 = 2;

    /**
     * バッチ種別 生徒休塾処理
     */
    const BATCH_TYPE_3 = 3;

    /**
     * バッチ種別 リマインドメール配信
     */
    const BATCH_TYPE_4 = 4;

    /**
     * バッチ種別 データベースバックアップ
     */
    const BATCH_TYPE_5 = 5;

    /**
     * バッチ種別 生徒学年更新
     */
    const BATCH_TYPE_11 = 11;

    /**
     * バッチ種別 振替残数リセット
     */
    const BATCH_TYPE_12 = 12;

    /**
     * バッチ種別 保持期限超過データ削除
     */
    const BATCH_TYPE_13 = 13;

    /**
     * バッチ種別 年次初期データ作成
     */
    const BATCH_TYPE_14 = 14;

    /**
     * バッチ種別 データ移行
     */
    const BATCH_TYPE_21 = 21;
}
