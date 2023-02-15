"use strict";

/*
 * 振替調整一覧
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
        // 検索一覧の表示
        var $vueSearchList = this.getVueSearchList();
        $vueSearchList.search();

        // Vue: モーダル
        this.getVueModal();

        // Vue: モーダル(受付)
        this.getVueModal({
            id: "#modal-dtl-attendance",

            // 完了処理後
            afterExec: () => {
                // 一覧を再表示する
                searchForm.vueSearchList.refresh();
            }
        });
    }
}
