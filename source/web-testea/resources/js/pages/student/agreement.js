"use strict";

/*
 * 契約内容
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
        // Vue: モーダル(バッジ付与情報)
        this.getVueModal({
            id: "#modal-dtl-badge",
        });
        // Vue: モーダル(規定情報)
        //this.getVueModal({
        //    id: "#modal-dtl-regulation",
        //});

        // Vue: モーダル(家庭教師標準情報)
        //this.getVueModal({
        //    id: "#modal-dtl-tutor",
        //});

        // Vue: モーダル(短期個別講習)
        //this.getVueModal({
        //    id: "#modal-dtl-course",
        //});
    }
}
