"use strict";

/*
 * 請求情報取込一覧
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

        // 完了後は一覧へ戻る
        var afterExec = () => {
            UrlCom.redirect(UrlCom.getFuncUrl());
        };

        // Vue: モーダル(バッチ実行)
        this.getVueModal({
            // IDを分けた
            id: "#modal-dtl-mail",

            // Exec前のValidate処理あり
            execValidate: true,

            // 完了処理後
            afterExec: afterExec
        });

        // 検索一覧の表示
        var $vueSearchList = this.getVueSearchList();
        $vueSearchList.search();
    }
}
