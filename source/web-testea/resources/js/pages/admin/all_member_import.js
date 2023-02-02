"use strict";

/*
 * 学年情報取込
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
            afterEdit: afterEdit
        });

        // 検索一覧の表示
        var $vueSearchList = this.getVueSearchList();
        $vueSearchList.search();
    }
}
