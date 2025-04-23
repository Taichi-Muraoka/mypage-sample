"use strict";

/*
 * 講師退職登録
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

        // 完了後は講師詳細画面（二階層目）に戻る
        var afterEdit = () => {
            self.redirectToParent();
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            // vd_input_leaveとなるようにURL指定
            urlSuffix: "leave",
        });
    }
}
