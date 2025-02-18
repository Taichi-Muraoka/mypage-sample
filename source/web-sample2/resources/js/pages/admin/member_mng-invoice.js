"use strict";

/*
 * 請求情報一覧
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
        // Vue: 検索一覧
        var $vueSearchList = this.getVueSearchList({
            // 別画面でも検索を使用するのでURLを変更
            urlSuffix: "invoice"
        });
        $vueSearchList.search();
    }
}
