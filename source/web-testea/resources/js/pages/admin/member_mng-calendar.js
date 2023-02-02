"use strict";

/*
 * カレンダー
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
        // カレンダーの処理
        this.calendar();
    }
}
