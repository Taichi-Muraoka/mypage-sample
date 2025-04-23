"use strict";

/*
 * 事務局アカウント管理
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
