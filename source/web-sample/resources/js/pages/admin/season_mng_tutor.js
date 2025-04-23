"use strict";

/*
 * 特別期間講習講師提出スケジュール一覧
 */
export default class AppClass extends PageBase {
    /**
     * コンストラクタ
     */
    constructor() {
        super();
    }

    /**
     * 開始処理
     */
    start() {
        // Vue: 検索フォーム
        this.getVueSearchForm();

    }
}
