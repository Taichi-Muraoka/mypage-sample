"use strict";

/*
 * 会員情報詳細
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

        // Vue: モーダル(規定情報)
        this.getVueModal({
            id: "#modal-dtl-regulation",
            // 別画面でもモーダルを使用するのでURLを変更
            urlSuffix: "detail",
        });

        this.getVueModal({
            id: "#modal-dtl-grades_mng",
            // 別画面でもモーダルを使用するのでURLを変更
            urlSuffix: "grades_mng"
        });

        // Vue: モーダル(家庭教師標準情報)
        //this.getVueModal({
        //    id: "#modal-dtl-tutor",
        //    // 別画面でもモーダルを使用するのでURLを変更
        //    urlSuffix: "detail",
        //});

        // Vue: モーダル(短期個別講習)
        //this.getVueModal({
        //    id: "#modal-dtl-course",
        //    // 別画面でもモーダルを使用するのでURLを変更
        //    urlSuffix: "detail",
        //});

    }
}
