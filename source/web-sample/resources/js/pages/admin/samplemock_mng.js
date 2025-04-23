"use strict";

/*
 * サンプル一覧
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

        // Vue: モーダル
        this.getVueModal();
    }
}
