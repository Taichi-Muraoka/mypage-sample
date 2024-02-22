"use strict";

/*
 * 授業報告書一覧
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
        const self = this;
        
        // Vue: モーダル
        this.getVueModal();

        // 完了後は一覧へ戻る
        var afterExec = () => {
            UrlCom.redirect(UrlCom.getFuncUrl());
        };

        // Vue: モーダル(承認)
        this.getVueModal({
            // IDを分けた
            id: "#modal-dtl-approval",

            // 完了処理後
            afterExec: afterExec
        });

        // Vue: 検索フォーム
        this.getVueSearchForm();
    }
}
