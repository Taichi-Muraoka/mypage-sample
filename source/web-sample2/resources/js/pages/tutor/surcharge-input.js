"use strict";

/*
 * 追加請求登録・編集
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

            vueData: {
                // プロパティを用意
                selectGetItem: {},
            },
            vueMethods: {
                // 請求種別サブコード取得イベント
                selectChangeGet: function (event) {
                    AjaxCom.getPromise()
                        .then(() => {
                            // 初期化
                            this.selectGetItem = {};
                            this.form.sub_code = null;

                            // チェンジイベントを発生させる
                            var selected = this.form.surcharge_kind;
                            self.selectChangeGetCallBack(
                                this,
                                selected,
                                this.option,
                                // 受信後のコールバック
                                (data) => {
                                    // データをセット
                                    this.selectGetItem = data;
                                    this.form.sub_code = data.subCode;
                                }
                            );
                        })
                        .catch(AjaxCom.fail);
                },
            },
        });
    }
}
