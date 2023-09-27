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
        const self = this;

        // Vue: 入力フォーム
        this.getVueInputForm({
            vueMounted: function ($vue, option) {
                // カレンダー表示
                var curDate = null;
                self.roomCalendar(curDate);
            },
            vueMethods: {
                // 教室プルダウン変更イベント
                selectChangeRoom: function (event) {
                    // カレンダー再表示
                    //console.log("room change!!");
                    // form再読み込み
                    this.form = FormCom.getFormArrayData(this.appId);
                    var curDate = null;
                    if (!ValueCom.isEmpty(this.form.curDate)) {
                        curDate = this.form.curDate;
                    }
                    self.roomCalendar(curDate);
                },
            },
        });
    }
}
