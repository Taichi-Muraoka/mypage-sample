"use strict";

/*
 * 講師所属校舎登録・編集
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
        var afterNew = () => {
            self.redirectToParent();
        };

        var afterEdit = () => {
            self.redirectToParent();
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            afterNew: afterNew,
            // vd_input_campusとなるようにURL指定
            urlSuffix: "campus",
        });
    }
}
