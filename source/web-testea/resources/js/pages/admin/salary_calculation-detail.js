"use strict";

/*
 * 給与算出情報一覧
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
            // 別画面でも検索を使用するのでURLを変更
            urlSuffix: "detail"
        });
        $vueSearchList.search();

        // Vue: モーダル 詳細
        this.getVueModal({
            // 別画面でもモーダルを使用するのでURLを変更
            urlSuffix: "detail"
        });

    }
}
