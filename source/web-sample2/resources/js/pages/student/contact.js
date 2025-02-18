"use strict";

/*
 * 問い合わせ
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

        // 検索一覧の表示
        var $vueSearchList = this.getVueSearchList();
        $vueSearchList.search();
    }
}
