"use strict";

/*
 * 受験校一覧
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
        this.getVueModal({
            // 別画面でもモーダルを使用するのでURLを変更
            urlSuffix: "desired_mng",
        });

        // 検索一覧の表示
        var $vueSearchList = this.getVueSearchList({
            // 別画面でも検索を使用するのでURLを変更
            urlSuffix: "desired_mng",
        });
        $vueSearchList.search();
    }
}
