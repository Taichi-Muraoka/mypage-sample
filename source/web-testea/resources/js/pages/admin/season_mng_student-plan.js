"use strict";

/*
 * 特別期間講習コマ組み
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
            //UrlCom.redirect(UrlCom.getFuncUrl());
            self.redirectToParent();
        };

        // Vue: 入力フォーム
        const vueForm = this.getVueInputForm({
            afterEdit: afterEdit,
            // 別画面でも更新・削除を使用するのでURLを変更
            urlSuffix: "plan",
            // 選択モーダルを使用する場合
            useModalSelect: true,
        });

        // 確定後イベント
        var afterOk = (modalButtonData, formDatas) => {
            // formDatasはformの値が格納されている

            // Hidden名
            const chkPlanId = modalButtonData.chk_plan_id;

            // 講師名
            vueForm.form["hd_text_" + chkPlanId] = formDatas["tname"];
            // ID
            vueForm.form["hd_" + chkPlanId] = formDatas["tid"];
        };

        // Vue: 講師選択モーダル
        this.getVueModalForm({
            afterOk: afterOk,
            onShowModal: function ($vue, modalButtonData) {
                // クリア
                $vue.vueInputForm.form.tid = "";
                $vue.vueInputForm.form.tname = "";

                // Hidden名
                const chkPlanId = modalButtonData.chk_plan_id;

                // 選択済み値
                const selectedValue = vueForm.form["hd_" + chkPlanId];

                // プルダウンを取得
                $vue.vueInputForm.selectGetItem = {};
                self.selectChangeGetCallBack(
                    $vue.vueInputForm,
                    {
                        // TODO: サンプル(日付・時限単位で設定)。どういう持ち方にするかは要検討
                        chk_plan_id: chkPlanId,
                    },
                    {
                        urlSuffix: "tutor",
                    },
                    // 受信後のコールバック
                    (data) => {
                        $vue.vueInputForm.selectGetItem = data;

                        for (const [key, value] of Object.entries(
                            $vue.vueInputForm.selectGetItem
                        )) {
                            if (selectedValue == value.id) {
                                // プルダウンに存在。IDと講師名を初期化
                                $vue.vueInputForm.form.tid = value.id;
                                $vue.vueInputForm.form.tname = value.value;
                                break;
                            }
                        }
                    }
                );
            },
            vueInputFormMethods: {
                // プルダウンチェンジイベント
                selectChange: function (event) {
                    // 講師名を取得しHiddenに退避する
                    const idx = event.target.selectedIndex;
                    if (ValueCom.isEmpty(event.target.options[idx].value)) {
                        // 未選択
                        this.form.tname = "";
                    } else {
                        const name = event.target.options[idx].text;
                        this.form.tname = name;
                    }
                },
            },
        });
    }
}
