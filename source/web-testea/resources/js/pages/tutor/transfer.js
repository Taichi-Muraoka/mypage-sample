"use strict";

/*
 * 振替連絡
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
            // Vueにメソッド追加
            vueMethods: {
                // この画面では複数のプルダウン選択があるので対応する
                selectChangeGetMulti: function(event) {
                    // 生徒名が無ければクリア
                    if (self._isEmpty(this.form.sid)) {
                        this.form.id = "";
                        Vue.set(this, "selectGetItem", {});
                        return;
                    }

                    // 生徒名のチェンジの場合はスケジュールをクリア
                    if (event && event.target.id == "sid") {
                        this.form.id = "";
                        Vue.set(this, "selectGetItem", {});
                    }

                    // チェンジイベントを発生させる
                    self._selectChangeGet(
                        this,
                        {
                            sid: this.form.sid,
                            id: this.form.id
                        },
                        this.option
                    );
                }
            }
        });
    }
}
