"use strict";

import WeekCalendar from "../../calendar/week-calendar";

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
        const self = this;

        // Vue: 入力フォーム
        this.getVueInputForm({
            vueData: {
                // カレンダー
                calendar: null,
            },
            vueMounted: function ($vue, option) {
                // カレンダー表示
                $vue.calendar = new WeekCalendar();
                $vue.calendar.createWeek();
            },
            vueMethods: {
                // 教室プルダウン変更イベント
                selectChangeRoom: function (event) {
                    // カレンダー再表示
                    this.calendar.refetchEvents();
                },
            },
        });
    }
}
