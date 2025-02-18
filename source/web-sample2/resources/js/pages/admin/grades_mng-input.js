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

        var afterEdit = () => {
            // 本画面は三階層目なので二階層目に戻る(親画面)
            self.redirectToParent2();
        };
        var afterNew = () => {
            // 新規登録の場合は、生徒カルテ画面（二階層目）に戻る
            self.redirectToParent();
        };

        this.getVueInputForm({
            afterEdit: afterEdit,
            afterNew: afterNew,
            // 別画面でも更新・削除を使用するのでURLを変更
            urlSuffix: "grades_mng",

            vueData: {
                // プロパティを用意
                selectGetItem: {},
            },
            vueMethods: {
                // 変更イベント
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
                                // URLを分けた
                                {
                                    urlSuffix: "grades",
                                },
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
