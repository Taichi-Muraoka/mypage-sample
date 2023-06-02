"use strict";

/*
 * 会員登録・編集
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
        var afterNew = () => {
            // 新規登録の場合は、会員一覧に戻る
            UrlCom.redirect(self._getFuncUrl());
        };
        var afterEdit = () => {
            // 編集の場合は、生徒カルテ画面（二階層目）に戻る
            self.redirectToParent();
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            afterNew: afterNew
        });
    }
}
