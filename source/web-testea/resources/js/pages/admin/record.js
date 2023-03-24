"use strict";

/*
 * 生徒カルテ一覧
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
        //this.getVueModal();
        this.getVueModal({
            // 別画面でもモーダルを使用するのでURLを変更
            urlSuffix: "record"
        });

        // Vue: 検索フォーム
        //this.getVueSearchForm();
        // 検索一覧の表示
        var $vueSearchList = this.getVueSearchList({
            // 別画面でも検索を使用するのでURLを変更
            urlSuffix: "record"
        });
        $vueSearchList.search();
    }
}
