"use strict";

/*
 * お知らせ定型文一覧
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

        // Vue: 詳細モーダル
        this.getVueModal();
    }
}
