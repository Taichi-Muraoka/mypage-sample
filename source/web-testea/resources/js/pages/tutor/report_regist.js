"use strict";

/*
 * 授業報告書一覧
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
        const self = this;
        
        // Vue: モーダル
        this.getVueModal();

        // Vue: 検索フォーム
        this.getVueSearchForm();
    }
}
