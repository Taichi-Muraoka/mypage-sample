"use strict";

/*
 * 欠席申請
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
            UrlCom.redirect(UrlCom.getFuncUrl());
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            vueMounted: function($vue, option) {
                // 編集時、プルダウンチェンジイベントを発生させる。
                // 該当のプルダウンの値を取得しチェンジイベントを直接呼ぶ
                var selected = $vue.form.id;
                self.selectChangeGet($vue, selected, option);
            }
        });
    }
}
