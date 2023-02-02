"use strict";

/*
 * 模試申込編集
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
            // 本画面は三階層目なので二階層目に戻る(親画面)
            self.redirectToParent();
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            // 別画面でもモーダルを使用するのでURLを変更
            urlSuffix: "entry"
        });
    }
}
