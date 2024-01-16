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
        // Vue: モーダル
        // this.getVueModal();

        // 検索一覧の表示
        var $vueSearchList = this.getVueSearchList({
            // 別画面でも検索を使用するのでURLを変更
            urlSuffix: "detail"
        });
        $vueSearchList.search();

        // Vue: モーダル 詳細
        this.getVueModal({
            // IDを分けた
            id: "#modal-dtl",
            // 別画面でもモーダルを使用するのでURLを変更
            urlSuffix: "detail"
        });

        // Vue: モーダル(集計)
        this.getVueModal({
            // 別画面でもモーダルを使用するのでURLを変更
            // urlSuffix: "detail-calc",
            
            // IDを分けた
            id: "#modal-dtl-calc",

            // 完了処理後
            // afterExec: afterExec
            afterExec: () => {
                // 一覧を再表示する
                searchForm.vueSearchList.refresh();
            }
        });

        // Vue: モーダル(確定)
        this.getVueModal({
            // IDを分けた
            id: "#modal-dtl-confirm",

            // 完了処理後
            // afterExec: afterExec
            afterExec: () => {
                // 一覧を再表示する
                searchForm.vueSearchList.refresh();
            }
        });

        // Vue: モーダル(確定)
        this.getVueModal({
            // IDを分けた
            id: "#modal-dtl-output",

            // 完了処理後
            // afterExec: afterExec
            afterExec: () => {
                // 一覧を再表示する
                searchForm.vueSearchList.refresh();
            }
        });
    }
}
