"use strict";

/*
 * 教室カレンダー欠席登録
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

        // 編集完了後は一覧へ戻る
        var afterEdit = () => {
            UrlCom.redirect(UrlCom.getFuncUrl());
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            // 別画面でも更新・削除を使用するのでURLを変更
            urlSuffix: "absent",
        });
    }
}
