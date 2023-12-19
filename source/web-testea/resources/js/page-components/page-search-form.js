"use strict";

import PageComponentBase from "./page-component-base";
import PageSearchList from "./page-search-list";

/**
 * ページコンポーネント: 検索フォーム
 */
export default class PageSearchForm extends PageComponentBase {
    /*
     * Vueインスタンスを取得
     */
    getVueApp(option = {}) {
        // ID
        const id = "#app-serch-form";

        // hidden値を取得するためにFormの値を取得
        const formData = FormCom.getFormArrayData(id);

        // 検索フォームをクリアする際の初期値
        // 一律空白でもよかったが、念のため初期値がある場合を考慮
        const formDataInit = FormCom.getFormArrayData(id);

        //--------------------
        // オプションの定義
        //--------------------

        // モックかどうか。通信処理は行われない
        if (option["isMock"] == undefined) {
            option["isMock"] = false;
        }

        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (ValueCom.isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else {
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // Vueのmountedイベントを呼ぶ
        // プルダウンのチェンジイベントを発生させる想定
        if (option["vueMounted"] == undefined) {
            option["vueMounted"] = ($vue, option) => {};
        }

        // Vueにmethodsを追加
        if (option["vueMethods"] == undefined) {
            option["vueMethods"] = {};
        }

        // Vue(List)にmethodsを追加
        if (option["vueSearchListMethods"] == undefined) {
            option["vueSearchListMethods"] = {};
        }

        // 初期時に検索を行うかどうか
        if (option["initSearch"] == undefined) {
            option["initSearch"] = true;
        }

        //--------------------
        // Vueの定義
        //--------------------

        const self = this;
        const vueApp = {
            data() {
                return {
                    // VueのIdを格納する
                    appId: id,
                    // フォームインプット
                    // 検索条件をはじめから選択する場合。(例：教室管理者の場合、プルダウンを教室のみにする)
                    form: formData,
                    // 検索後の検索条件を保持(ギフトカードの一覧出力)
                    formAfterSearch: {},
                    // オプションを保持しておく
                    option: null,
                    // フォームエラー
                    form_err: {
                        msg: {},
                        class: {},
                    },
                    // プルダウン選択後の詳細を格納する
                    selectGetItem: {},
                    // 検索一覧を保持する
                    vueSearchList: null,
                    // 検索ボタンの非活性
                    disabledBtnSearch: false,
                };
            },
            mounted() {
                // optionを保持
                this.option = option;

                // ライブラリの初期化
                self.initLibs(this, option);

                // afterSearch用
                const _self = this;

                // 一覧のVueインスタンスを保持
                const pageSearchlist = new PageSearchList();
                this.vueSearchList = pageSearchlist.getVueApp(
                    Object.assign(option, {
                        afterSearch: function () {
                            // 検索完了後は検索ボタンを活性化する
                            _self.disabledBtnSearch = false;
                        },
                        vueMethods: option["vueSearchListMethods"],
                    })
                );

                // 呼び出し元のmouted処理を呼ぶ
                option["vueMounted"](this, option);

                // 画面読み込み時に一覧を表示
                if (option["initSearch"]) {
                    this.execSearch();
                }
            },
            updated() {
                // Vue更新後、ライブラリの初期化
                self.updatedLibs(this);
            },
            methods: Object.assign(
                {
                    //-----------------------
                    // 検索条件クリア
                    //-----------------------
                    initSearchCond: function (event) {
                        for (const [key, value] of Object.entries(this.form)) {
                            // 検索フォームを初期化する
                            if (key in formDataInit) {
                                this.form[key] = formDataInit[key];
                            }
                        }
                    },
                    //-----------------------
                    // 検索結果クリア
                    //-----------------------
                    searchListClear: function () {
                        this.vueSearchList.clear();
                    },
                    //-----------------------
                    // 検索済み検索結果を取得
                    //-----------------------
                    getAfterSearchCond: function (event) {
                        return this.formAfterSearch;
                    },
                    //-----------------------
                    // 検索ボタンクリック
                    //-----------------------
                    btnSearch: function (event) {
                        this.execSearch();
                    },
                    //-----------------------
                    // 検索実行
                    //-----------------------
                    execSearch: function () {
                        // 検索ボタンを非活性にする
                        this.disabledBtnSearch = true;

                        // 検索処理
                        AjaxCom.getPromise()
                            .then(() => {
                                // バリデート
                                const url =
                                    UrlCom.getFuncUrl() +
                                    "/vd_search" +
                                    option["urlSuffix"];

                                // モック時は送信しない
                                if (!option["isMock"]) {
                                    return axios.post(url, this.form);
                                }
                            })
                            .then((response) => {
                                // モック時は処理しない
                                if (!option["isMock"]) {
                                    // バリデーション結果をチェック
                                    if (!self.validateCheck(this, response)) {
                                        // 検索完了後は検索ボタンを活性化する
                                        this.disabledBtnSearch = false;
                                        // 処理を抜ける
                                        return AjaxCom.exit();
                                    }
                                }

                                // 検索した条件を保持しておく
                                // 例：ギフトカード一覧の一括出力用
                                this.formAfterSearch = {};
                                for (const [key, value] of Object.entries(
                                    this.form
                                )) {
                                    this.formAfterSearch[key] = value;
                                }

                                // 検索実行
                                this.vueSearchList.search(this.form);
                            })
                            .catch(AjaxCom.fail);
                    },
                },
                option["vueMethods"]
            ),
        };

        return this.createComponent(vueApp, id);
    }
}
