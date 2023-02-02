"use strict";

/*
 * 会員情報取込
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
        // 同じページを表示
        var afterEdit = () => {
            UrlCom.redirect(self._getFuncUrl());
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            // 送信時、確認モーダルを使用する
            confirmModal: "#modal-dtl"
        });
    }
}
