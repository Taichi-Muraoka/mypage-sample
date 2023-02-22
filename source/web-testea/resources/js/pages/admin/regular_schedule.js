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
        // Vue: 入力フォーム
        this.getVueInputForm({
            vueMounted: function($vue, option) {
                // カレンダー表示
                var curDate = null;
                if (!self._isEmpty($vue.form.curDate)) {
                    curDate = $vue.form.curDate;
                }

                self.defaultWeekCalendar();
            },
            vueMethods: {
                // 教室プルダウン変更イベント
                selectChangeRoom: function(event) {
                    // カレンダー再表示
                    var curDate = null;
                    if (!self._isEmpty(this.form.curDate)) {
                        curDate = this.form.curDate;
                    }
                    self.defaultWeekCalendar();
                }
            }
        });
    }
}
