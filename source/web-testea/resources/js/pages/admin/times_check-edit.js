"use strict";

/*
 * 回数報告編集
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
        // 編集完了後は一覧へ戻る
        var afterEdit = () => {
            UrlCom.redirect(self._getFuncUrl());
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            vueMounted: function($vue, option) {
                // 編集時、プルダウンチェンジイベントを発生させる。
                $vue.selectChangeGetMulti();
            },
            vueMethods: {
                // 複数値送信対応
                selectChangeGetMulti: function(event) {
                    // 実施月と教室があれば送信
                    var selected = null;
                    if (!self._isEmpty(this.form.report_date) && !self._isEmpty(this.form.roomcd)) {
                        selected = {
                            reportDate: this.form.report_date,
                            tid: this.form.tid,
                            roomcd: this.form.roomcd
                        };
                    }

                    // 実施月が無ければ実行しない
                    if (self._isEmpty(this.form.report_date)) {
                        this.form.report_date = "";
                        Vue.set(this, "selectGetItem", {});
                        return;
                    }

                    // 教室が無ければ実行しない
                    if (self._isEmpty(this.form.roomcd)) {
                        this.form.roomcd = "";
                        Vue.set(this, "selectGetItem", {});
                        return;
                    }

                    // 実施月のチェンジの場合は教室をクリア
                    if (event && event.target.id == "report_date") {
                        this.form.roomcd = "";
                        Vue.set(this, "selectGetItem", {});
                        return;
                    }

                    // チェンジイベントを発生させる
                    self._selectChangeGet(this, selected, this.option);
                }
            }
        });
    }
}
