"use strict";

import PageComponentBase from "./page-component-base";

/**
 * ページコンポーネント: 検索結果一覧
 */
export default class PageSearchList extends PageComponentBase {
    /*
     * Vueインスタンスを取得
     */
    getVueApp(option = {}) {
        //--------------------
        // オプションの定義
        //--------------------

        // モックかどうか。通信処理は行われない
        if (option["isMock"] == undefined) {
            option["isMock"] = false;
        }

        // ターゲットのID
        if (option["id"] == undefined) {
            option["id"] = "#app-serch-list";
        }

        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (ValueCom.isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else if (option["urlSuffix"].charAt(0) == "_") {
            // 何もしない(vueのoptionを渡された場合)
        } else {
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // 検索完了後の処理(検索ボタンの活性のため)
        if (option["afterSearch"] == undefined) {
            option["afterSearch"] = () => {};
        }

        // Vueにmethodsを追加
        if (option["vueMethods"] == undefined) {
            option["vueMethods"] = {};
        }

        //--------------------
        // Vueの定義
        //--------------------

        // divのid
        const id = option["id"];

        // hidden値を取得するためにFormの値を取得
        const formData = FormCom.getFormArrayData(id);

        //--------------------
        // Vueの定義
        //--------------------

        const vueApp = {
            data() {
                return {
                    paginator: {},
                    elements: [],

                    // 検索フォームの条件(保持用)
                    searchForm: {},

                    // Hidden値などのformを保持。子画面で一覧がある場合など(請求情報一覧)
                    form: formData,

                    // ページ数を保持
                    page: 1,
                };
            },
            methods: Object.assign(
                {
                    //--------------------------
                    // 検索一覧クリア
                    //--------------------------
                    clear: function () {
                        // クリア
                        this.paginator = {};
                        this.elements = {};
                    },
                    //--------------------------
                    // 検索
                    //--------------------------
                    search: function (
                        $searchForm = {},
                        page = 1,
                        scroll = false
                    ) {
                        if (scroll) {
                            // モーダルかどうか判断
                            const parent =
                                $("#search-top").parent(".modal-body");
                            const speed = 300;

                            // 一覧のトップへ移動する
                            if (parent.length == 0) {
                                // モーダル以外
                                const position = $("#search-top").offset().top;
                                $("html, body").animate(
                                    { scrollTop: position },
                                    speed,
                                    "swing"
                                );
                            } else {
                                // モーダル内の相対位置を取得
                                const pos =
                                    document.getElementById(
                                        "search-top"
                                    ).offsetTop;
                                $(
                                    ".modal-dialog-scrollable .modal-body"
                                ).animate({ scrollTop: pos }, speed, "swing");
                            }
                        }

                        // ページ数を保持する
                        this.page = page;

                        AjaxCom.getPromise()
                            .then(() => {
                                // ローディング開始
                                FormCom.loadingForCardOn(id);

                                // フォームデータに加えてページも追加
                                const sendData = Object.assign(
                                    $searchForm,
                                    {
                                        page: page,
                                    },
                                    // hiddenを送信
                                    this.form
                                );

                                // 検索 (例：http://localhost:8000/sample と同じ階層を想定)
                                const url =
                                    UrlCom.getFuncUrl() +
                                    "/search" +
                                    option["urlSuffix"];

                                // モック時は送信しない
                                if (!option["isMock"]) {
                                    return axios.post(url, sendData);
                                } else {
                                    // ダミーウェイト
                                    return DummyCom.wait();
                                }
                            })
                            .then((response) => {
                                //console.log(response);

                                // モック時は処理しない
                                if (!option["isMock"]) {
                                    // ページャデータ
                                    const paginator = response.data.paginator;
                                    const elements = response.data.elements;

                                    // ページャをセット
                                    this.paginator = paginator;

                                    // 件数表示
                                    this.paginator.total =
                                        paginator.total + "件";

                                    // ページャーの表示
                                    this.elements = elements;

                                    // 検索した条件を保持しておく(ページャなど)
                                    //this.searchForm = $searchForm;
                                    // 上記のようにVueのプロパティをそのままセットすると、
                                    // リアルタイムで反映されてしまうので、以下のようにセットし直す
                                    this.searchForm = {};
                                    for (const [key, value] of Object.entries(
                                        $searchForm
                                    )) {
                                        this.searchForm[key] = value;
                                    }
                                }

                                // ローディング終了
                                FormCom.loadingForCardOff(id);

                                // 検索後の処理
                                option["afterSearch"]();
                            })
                            .catch(AjaxCom.fail);
                    },

                    //--------------------------
                    // ページャのリンクをクリック
                    //--------------------------
                    page_link: function (page) {
                        // 検索
                        this.search(this.searchForm, page, true);
                    },

                    //--------------------------
                    // 再描画
                    //--------------------------
                    // 検索条件・ページ数はそのままで再検索する
                    // 受付ボタンなどをクリックし、再度一覧を描画し直す際、
                    // 1ページ目に戻らず、そのままのページでかつ検索条件も同じとする
                    refresh: function () {
                        // 検索
                        this.search(this.searchForm, this.page, false);
                    },
                },
                option["vueMethods"]
            ),
        };

        return this.createComponent(vueApp, id);
    }
}
