"use strict";

/*
 * 生徒成績登録・編集
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
                // 成績入力欄数変更イベント
                selectChangeGetCount: function (event) {
                    AjaxCom.getPromise()
                        .then(() => {
                            // 初期化
                            this.selectGetItem = {};
                            this.form.display_count = null;

                            // チェンジイベントを発生させる
                            self.selectChangeGetCallBack(
                                this,
                                {
                                    exam_type: this.form.exam_type,
                                    school_kind: this.form.school_kind,
                                },
                                this.option,
                                // 受信後のコールバック
                                (data) => {
                                    // データをセット
                                    this.selectGetItem = data;
                                    this.form.display_count = data.count;
                                }
                            );
                        })
                        .catch(AjaxCom.fail);
                },
            }
        });
    }
}
