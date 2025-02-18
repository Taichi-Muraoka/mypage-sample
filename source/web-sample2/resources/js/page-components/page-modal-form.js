"use strict";

import PageComponentBase from "./page-component-base";
import PageInputForm from "../page-components/page-input-form";

/**
 * ページコンポーネント: モーダル(フォーム)
 */
export default class PageModalForm extends PageComponentBase {
    /*
     * Vueインスタンスを取得
     */
    getVueApp(option = {}) {
        //--------------------
        // オプションの定義
        //--------------------

        // モーダルのID
        if (option["id"] == undefined) {
            option["id"] = "#modal-dtl";
        }

        // 確定後
        if (option["afterOk"] == undefined) {
            option["afterOk"] = () => {};
        }

        // モーダル表示時
        if (option["onShowModal"] == undefined) {
            option["onShowModal"] = ($vue) => {};
        }

        // Vueにmethodsを追加
        if (option["vueInputFormMethods"] == undefined) {
            option["vueInputFormMethods"] = {};
        }

        //--------------------
        // Vueの定義
        //--------------------

        // モーダルボタン インスタンス(modal-bodyは対象外)
        // TODO: 同一画面で複数の選択画面が必要であれば検討
        const elId = "#modal-buttons";

        // Vue: モーダル
        const self = this;
        const vueApp = {
            data() {
                return {
                    modal: null,
                    id: option["id"].replace("#", ""),
                    // モーダルを開いたときのボタンのdata属性を保持しておく
                    modalButtonData: null,
                    // Vueフォームインスタンス
                    vueInputForm: null,
                };
            },
            mounted() {
                // モーダルを保持
                this.modal = $(document.getElementById(this.id));

                // モーダル表示イベント
                this.modal.on("show.bs.modal", this.onShowModal);

                // Vue: フォーム
                const pageInputForm = new PageInputForm();
                this.vueInputForm = pageInputForm.getVueApp({
                    id: "#app-form-modal",
                    vueMethods: option["vueInputFormMethods"],
                });
            },
            methods: {
                //---------------------------
                // モーダル閉じる
                //---------------------------
                hide() {
                    this.modal.modal("hide");
                },
                //---------------------------
                // モーダルが表示されるイベント
                //---------------------------
                onShowModal(event) {
                    // クリックされたボタンを取得
                    var button = $(event.relatedTarget);
                    const sendData = self.getDatasFromButton(button);
                    this.modalButtonData = sendData;

                    option["onShowModal"](this, this.modalButtonData);
                },
                //---------------------------
                // モーダルの確定ボタン押下時イベント
                //---------------------------
                modalOk() {
                    option["afterOk"](
                        this.modalButtonData,
                        this.vueInputForm.form
                    );
                    this.hide();
                },
            },
        };

        return this.createComponent(vueApp, elId);
    }
}
