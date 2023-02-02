"use strict";

/*
 * 回数報告
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
        // 編集完了後は同じ画面へ
        var afterEdit = () => {
            UrlCom.redirect(self._getFuncUrl());
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            vueMethods: {
                // この画面では複数のプルダウン選択があるので対応する
                selectChangeGetMulti: function(event) {
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
                    self._selectChangeGet(
                        this,
                        {
                            id: this.form.report_date,
                            roomcd: this.form.roomcd
                        },
                        this.option
                    );
                }
            }
        });
    }
}
