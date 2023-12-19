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
        });

        // 確定後イベント
        var afterOk = (modalButtonData, formDatas) => {
            // formDatasはformの値が格納されている

            // Hidden名
            const datePeriodKey = modalButtonData.date_period_key;

            // 講師名
            vueForm.form["sel_tname_" + datePeriodKey] = formDatas["tname"];
            // 講師ID
            vueForm.form["sel_tid_" + datePeriodKey] = formDatas["tutor_id"];
        };

        // Vue: 講師選択モーダル
        this.getVueModalForm({
            afterOk: afterOk,
            onShowModal: function ($vue, modalButtonData) {
                // クリア
                $vue.vueInputForm.form.tutor_id = "";
                $vue.vueInputForm.form.tname = "";
                // ボタン押下のセルのID
                const datePeriodKey = modalButtonData.date_period_key;
                // 校舎コード
                const campusCd = vueForm.form["campus_cd"];
                // 科目コード
                const subjectCd = vueForm.form["subject_cd"];

                // 選択済み値
                const selectedValue = vueForm.form["sel_tid_" + datePeriodKey];

                // 講師プルダウンを取得
                $vue.vueInputForm.selectGetItem = {};
                self.selectChangeGetCallBack(
                    $vue.vueInputForm,
                    {
                        // 講師リスト取得に必要な項目をセット
                        date_period_key: datePeriodKey,
                        campus_cd: campusCd,
                        subject_cd: subjectCd,
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
                                // 選択済み値がプルダウンに存在する場合、選択値をセット
                                $vue.vueInputForm.form.tutor_id = value.id;
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
