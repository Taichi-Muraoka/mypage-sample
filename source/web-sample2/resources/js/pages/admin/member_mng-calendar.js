"use strict";

import DefaultCalendar from "../../calendar/default-calendar";

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
        // カレンダー表示
        const calendar = new DefaultCalendar();
        calendar.create();
    }
}
