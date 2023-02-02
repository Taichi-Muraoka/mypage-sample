"use strict";

/*
 * ギフトカード付与一覧
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

        // Vue: モーダル(詳細)
        this.getVueModal();

        // Vue: モーダル(受付)
        this.getVueModal({
            id: "#modal-dtl-acceptance",

            // 完了処理後
            afterExec: () => {
                // 一覧を再表示する
                searchForm.vueSearchList.refresh();
            }
        });

        // Vue: モーダル(一覧出力)
        this.getVueModal({
            id: "#modal-dtl-output",

            // exec送信時に、検索フォームを送信する
            // 検索条件を返す関数を指定する。
            addSendData: searchForm.getAfterSearchCond,

            // 完了処理後
            afterExec: () => {
                // CSVのダウンロードなので不要
            }
        });
    }
}
