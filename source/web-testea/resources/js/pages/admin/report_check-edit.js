"use strict";

/*
 * 授業報告書登録・編集
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
                    if (self._isEmpty(this.form.sidKobetsu)) {
                        this.form.id = "";
                        Vue.set(this, "selectGetItem", {});
                        return;
                    }

                    // 生徒名のチェンジの場合はスケジュールをクリア
                    if (event && event.target.id == "sidKobetsu") {
                        this.form.id = "";
                        Vue.set(this, "selectGetItem", {});
                    }

                    // チェンジイベントを発生させる
                    self._selectChangeGet(
                        this,
                        {
                            // sidで送信する
                            sid: this.form.sidKobetsu,
                            id: this.form.id,
                            // ガード用にIDを送信する
                            reportId: this.form.report_id
                        },
                        this.option
                    );
                }
            }
        });
    }
}
