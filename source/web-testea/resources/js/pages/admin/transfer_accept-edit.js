"use strict";

/*
 * 振替連絡編集
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
                // プルダウンが動的になるので、退避したものをセットする
                $vue.form.id = $vue.form._id;

                // 編集時、プルダウンチェンジイベントを発生させる。
                $vue.selectChangeGetMulti();
            },
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
                            id: this.form.id,
                            // ガード用にIDを送信する
                            transferApplyId: this.form.transfer_apply_id
                        },
                        this.option
                    );
                }
            }
        });
    }
}
