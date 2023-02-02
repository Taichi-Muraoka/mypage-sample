"use strict";

/*
 * 模試申込者一覧
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
        var $vueSearchList = this.getVueSearchList({
            // URLを変更
            urlSuffix: "state",
        });
        $vueSearchList.search();
    }
}
