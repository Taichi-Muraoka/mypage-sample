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
        const self = this;

        // 編集完了後は一覧へ戻る
        var afterEdit = () => {
            UrlCom.redirect(UrlCom.getFuncUrl());
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,

            // Vueにメソッド追加
            
            vueMounted: function($vue, option) {
                // 編集時、プルダウンチェンジイベントを発生させる。
                // 該当のプルダウンの値を取得しチェンジイベントを直接呼ぶ
                var selected = $vue.form.id;
                self.selectChangeGet($vue, selected, option);
            },
            // Vueにメソッド追加
            // vueMethods: {
            //     // 授業・時限プルダウン変更イベント
            //     selectChangeGet: function (event) {
            //         // チェンジイベントを発生させる
            //         var selected = this.form.id;
            //         self.selectChangeGet(
            //             this,
            //             selected,
            //         );
            //     },
            // },
        });
    }
}
