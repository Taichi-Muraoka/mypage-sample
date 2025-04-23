"use strict";

/*
 * 特別期間講習　日程連絡登録
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
            UrlCom.redirect(UrlCom.getFuncUrl());
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            // チェックボックスが多く送信ボタン押下時にラグがあるので
            // 処理中ダイアログを表示
            progressShow: true,
            // Vue関数
            vueMethods: {
                // 時間帯をクリック
                timeClick: function(event) {
                    // 時間帯のdataを取得
                    var time = $(event.target).attr("data-wt");

                    // クリックされた時間帯のチェックボックスにチェックを入れる
                    // nameは同じで配列としてセットする

                    var exist = this.form.chkWs.indexOf(time);
                    if (exist >= 0) {
                        this.form.chkWs.splice(exist, 1);
                    } else {
                        this.form.chkWs.push(time);
                    }
                }
            }
        });
    }
}
