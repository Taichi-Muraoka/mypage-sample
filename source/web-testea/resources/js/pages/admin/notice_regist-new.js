"use strict";

/*
 * お知らせ登録
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
                // 定型文プルダウン変更用のプロパティを用意
                selectGetItemTemplate: {},
            },
            vueMethods: {
                // 定型文プルダウン変更イベント
                selectChangeGetTemplate: function (event) {
                    AjaxCom.getPromise()
                        .then(() => {
                            // 入力中の場合は確認する
                            if (
                                !ValueCom.isEmpty(this.form.title) ||
                                !ValueCom.isEmpty(this.form.text)
                            ) {
                                return appDialogCom.confirm(
                                    "入力内容がクリアされますがよろしいですか？",
                                    null,
                                    "normal"
                                );
                            } else {
                                return true;
                            }
                        })
                        .then((flg) => {
                            if (!flg) {
                                // いいえを押した場合
                                return AjaxCom.exit();
                            }

                            // 初期化
                            this.selectGetItemTemplate = {};
                            this.form.title = "";
                            this.form.text = "";

                            // チェンジイベントを発生させる
                            var selected = this.form.template_id;
                            self.selectChangeGetCallBack(
                                this,
                                selected,
                                // URLを分けた
                                {
                                    urlSuffix: "template",
                                },
                                // 受信後のコールバック
                                (data) => {
                                    // データをセット
                                    this.selectGetItemTemplate = data;
                                    this.form.title = data.title;
                                    this.form.text = data.text;
                                }
                            );
                        })
                        .catch(AjaxCom.fail);
                },
                // 宛先種別
                selectChangeGetMulti: function (event) {
                    var destinationType = this.form.destination_type;
                    var roomcdStudent = this.form.roomcd_student;

                    // 生徒プルダウンは動的に変わるので、一旦クリアする
                    this.form.sid = "";

                    // チェンジイベントを発生させる
                    self.selectChangeGet(
                        this,
                        {
                            destinationType: destinationType,
                            roomcdStudent: roomcdStudent,
                        },
                        this.option
                    );
                },
            },
        });
    }
}
