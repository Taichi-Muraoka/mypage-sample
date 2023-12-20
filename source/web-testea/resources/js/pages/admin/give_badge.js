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
        var searchForm = this.getVueSearchForm();

        // Vue: モーダル(一覧出力)
        this.getVueModal({
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
