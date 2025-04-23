"use strict";

/*
 * 給与明細書表示
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
        // Vue: 入力フォーム
        this.getVueInputForm();
    }
}
