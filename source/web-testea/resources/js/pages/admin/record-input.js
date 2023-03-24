"use strict";

/*
 * 生徒カルテ登録・編集
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
            //UrlCom.redirect(self._getFuncUrl());
            // 本画面は三階層目なので二階層目に戻る(親画面)
            self.redirectToParent2();
        };

        // Vue: 入力フォーム
        //this.getVueInputForm({
        //    afterEdit: afterEdit,
        //});
        this.getVueInputForm({
            afterEdit: afterEdit,
            // 別画面でも更新・削除を使用するのでURLを変更
            urlSuffix: "record",
        });

    }
}
