"use strict";

/*
 * ギフトカード付与
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
            urlSuffix: "new",
            // Vueにメソッド追加
            vueMethods: {
                // 教室プルダウン変更イベント
                selectChangeGetRoom: function(event) {
                    // 生徒プルダウンをクリア
                    this.form.sid = "";
                    Vue.set(this, "selectGetItem", {});

                    // チェンジイベントを発生させる
                    var selected = this.form.roomcd;
                    self._selectChangeGet(this, selected, this.option);
                }
            }
        });
    }
}
