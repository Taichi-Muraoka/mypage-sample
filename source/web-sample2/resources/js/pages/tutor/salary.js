"use strict";

/*
 * 給与明細一覧
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
        // 検索一覧の表示
        var $vueSearchList = this.getVueSearchList();
        $vueSearchList.search();
    }
}
