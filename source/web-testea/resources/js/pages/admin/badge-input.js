"use strict";

/*
 * バッジ付与登録・編集
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
            // 編集の場合は、一覧画面（三階層目）に戻る
            self.redirectToParent2();
        };
        var afterNew = () => {
            // 新規登録の場合は、生徒カルテ画面（二階層目）に戻る
            self.redirectToParent();
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            afterNew: afterNew,
            // 別画面でも更新・削除を使用するのでURLを変更
            urlSuffix: "badge",

            vueData: {
                // 定型文プルダウン変更用のプロパティを用意
                selectGetItemTemplate: {},
            },
            vueMethods: {
                // 定型文プルダウン変更イベント
                selectChangeGetTemplate: function (event) {
                    AjaxCom.getPromise()
                        // .then(() => {
                        //     // 入力中の場合は確認する
                        //     if (
                        //         !ValueCom.isEmpty(this.form.title) ||
                        //         !ValueCom.isEmpty(this.form.text)
                        //     ) {
                        //         return appDialogCom.confirm(
                        //             "入力内容がクリアされますがよろしいですか？",
                        //             null,
                        //             "normal"
                        //         );
                        //     } else {
                        //         return true;
                        //     }
                        // })
                        .then(() => {
                            // if (!flg) {
                            //     // いいえを押した場合
                            //     return AjaxCom.exit();
                            // }

                            // 初期化
                            this.selectGetItemTemplate = {};
                            this.form.reason = "";
                            // this.form.title = "";
                            // this.form.text = "";

                            // チェンジイベントを発生させる
                            var selected = this.form.badge_type;
                            self.selectChangeGetCallBack(
                                this,
                                selected,
                                // URLを分けた
                                {
                                    urlSuffix: "badge",
                                },
                                // 受信後のコールバック
                                (data) => {
                                    // データをセット
                                    this.selectGetItemTemplate = data;
                                    this.form.reason = data.reason;
                                    // this.form.title = data.title;
                                    // this.form.text = data.text;
                                }
                            );
                        })
                        .catch(AjaxCom.fail);
                },
            }
        });

    }
}
