"use strict";

/*
 * 生徒情報
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
        this.getVueModal();
    }
}
