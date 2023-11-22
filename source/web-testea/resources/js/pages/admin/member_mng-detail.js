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
        // Vue: モーダル(連絡記録)
        this.getVueModal({
            id: "#modal-dtl-record",
            // get_data_detailとなるようURLを変更
            urlSuffix: "detail",
        });

        // Vue: モーダル(授業情報)
        this.getVueModal({
            id: "#modal-dtl-room_calendar",
            urlSuffix: "detail",
        });

        // Vue: モーダル(受験校情報)
        this.getVueModal({
            id: "#modal-dtl-desired",
            urlSuffix: "detail",
        });

        // Vue: モーダル(成績情報)
        this.getVueModal({
            id: "#modal-dtl-grades_mng",
            urlSuffix: "detail",
        });
    }
}
