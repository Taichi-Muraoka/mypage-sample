"use strict";

/*
 * バッジ付与一覧
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
        //this.getVueSearchForm();
        // 検索一覧の表示
        var $vueSearchList = this.getVueSearchList({
            // 別画面でも検索を使用するのでURLを変更
            urlSuffix: "badge"
        });
        $vueSearchList.search();
    }
}
