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
        var searchForm = this.getVueSearchForm();

        // Vue: モーダル
        this.getVueModal();

        // Vue: モーダル(承認)
        this.getVueModal({
            // IDを分けた
            id: "#modal-dtl-approval",

            // 完了処理後
            afterExec: () => {
                // 一覧を再表示する
                searchForm.vueSearchList.refresh();
            }
        });
    }
}
