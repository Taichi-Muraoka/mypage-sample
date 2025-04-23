"use strict";

/*
 * 研修受講
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
        // Vue: 入力フォーム
        this.getVueInputForm({
            // Vueにメソッド追加
            vueMethods: {
                // 動画の閲覧
                submitMovieBrowse: function(event) {
                    AjaxCom.getPromise()
                        .then(() => {
                            // 動画の閲覧を更新
                            var url = UrlCom.getFuncUrl() + "/movie_browse";

                            // 送信(Vueのフォームごと送信)
                            return axios.post(url, this.form);
                        })
                        .catch(AjaxCom.fail);
                }
            }
        });
    }
}
