"use strict";

/*
 * コースマスタ管理
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
        var $vueSearchList = this.getVueSearchList();
        $vueSearchList.search();

    }
}
