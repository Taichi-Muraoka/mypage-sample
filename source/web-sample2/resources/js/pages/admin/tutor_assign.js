"use strict";

/*
 * 空き講師検索
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
        // Vue: モーダル
        this.getVueModal();

        // Vue: 検索フォーム
        this.getVueSearchForm();
    }
}
