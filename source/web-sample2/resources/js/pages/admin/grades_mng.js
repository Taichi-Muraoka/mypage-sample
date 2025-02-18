"use strict";

/*
 * 生徒成績一覧
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
            urlSuffix: "grades_mng"
        });

        // 検索一覧の表示
        var $vueSearchList = this.getVueSearchList({
            // 別画面でも検索を使用するのでURLを変更
            urlSuffix: "grades_mng"
        });
        $vueSearchList.search();
    }
}
