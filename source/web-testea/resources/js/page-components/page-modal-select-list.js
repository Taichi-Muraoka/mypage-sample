"use strict";

import PageComponentBase from "./page-component-base";
import PageSearchForm from "../page-components/page-search-form";

/**
 * ページコンポーネント: 選択モーダル(検索リスト)
 */
export default class PageModalSelectList extends PageComponentBase {
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

        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (ValueCom.isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else {
            option["urlSuffix"] = option["urlSuffix"];
        }

        // 選択後
        if (option["afterSelected"] == undefined) {
            option["afterSelected"] = () => {};
        }

        //--------------------
        // Vueの定義
        //--------------------

        // モーダルボタン インスタンス(modal-bodyは対象外)
        // TODO: 同一画面で複数の選択画面が必要であれば検討
        const id = "#modal-buttons";

        const self = this;
        const vueApp = {
            data() {
                return {
                    modal: null,
                    id: option["id"].replace("#", ""),
                    // モーダルを開いたときのボタンのdata属性を保持しておく
                    modalButtonData: null,
                    // Vue検索インスタンス
                    vueSearchForm: null,
                };
            },
            mounted() {
                // モーダルを保持
                this.modal = $(document.getElementById(this.id));

                // モーダル表示イベント
                this.modal.on("show.bs.modal", this.onShowModal);

                // モーダルボタンのdata属性
                const vueModal = this;

                // Vue: 検索フォーム
                const pageSearchForm = new PageSearchForm();
                this.vueSearchForm = pageSearchForm.getVueApp({
                    urlSuffix: option["urlSuffix"],
                    initSearch: false, // 初期化時に検索しない
                    vueSearchListMethods: {
                        selectedData: function (event) {
                            // クリックされたボタンを取得(各リストのdata属性)
                            var button = $(event.target);
                            const selectedDatas =
                                self.getDatasFromButton(button);
                            option["afterSelected"](
                                vueModal.modalButtonData,
                                selectedDatas
                            );
                        },
                    },
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

                    // 検索条件・一覧をクリアする
                    this.vueSearchForm.initSearchCond();
                    this.vueSearchForm.searchListClear();

                    // 検索する
                    this.vueSearchForm.execSearch();
                },
            },
        };

        return this.createComponent(vueApp, id);
    }
}
