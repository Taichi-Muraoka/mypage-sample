"use strict";

/*
 * お知らせ
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

        // Vue: モーダル(イベント)
        this.getVueModal({ id: "#modal-dtl-event" });
        // Vue: モーダル(個別講習)
        this.getVueModal({ id: "#modal-dtl-course" });
        // Vue: モーダル(欠席申請)
        this.getVueModal({ id: "#modal-dtl-absent" });
        // Vue: モーダル(面談日程連絡)
        this.getVueModal({ id: "#modal-dtl-conference" });
    }
}
