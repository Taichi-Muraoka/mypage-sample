"use strict";

import PageComponentBase from "./page-component-base";

/**
 * ページコンポーネント: モーダル
 */
export default class PageModal extends PageComponentBase {
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
            option["id"] = "#modal-dtl";
        }

        // 表示イベントを取得するかどうか
        // 例えばカレンダー画面ではモーダル時に非同期通信しないため
        if (option["useShowEvent"] == undefined) {
            option["useShowEvent"] = true;
        }

        // 登録完了後の処理
        if (option["afterExec"] == undefined) {
            option["afterExec"] = () => {};
        }

        // Vueにdataを追加
        if (option["vueData"] == undefined) {
            option["vueData"] = {};
        }

        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (ValueCom.isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else {
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // Modalのshowやexecで送信するデータを追加する
        // ギフトカード一覧の一覧出力で検索フォームを送信するため
        if (option["addSendData"] == undefined) {
            // 都度、最新の値を取るため、関数を指定する。
            option["addSendData"] = () => {};
        }

        // Exec処理前にバリデーションを行うかどうか。
        if (option["execValidate"] == undefined) {
            option["execValidate"] = false;
        }


        //--------------------
        // Vueの定義
        //--------------------

        // divのid
        const id = option["id"];

        //--------------------
        // Vueの定義
        //--------------------

        const self = this;
        const vueApp = {
            data() {
                return Object.assign(
                    {
                        modal: null,
                        id: option["id"].replace("#", ""),
                        // モーダルの詳細データをセットする
                        item: {},
                        // モーダルを開いたときのdata属性を保持しておく
                        sendData: null,

                        // 確認ダイアログ用
                        deferred: null,
                        isConfirm: false,
                    },
                    option["vueData"]
                );
            },
            mounted() {
                // モーダルを保持
                //this.modal = $(this.$refs[this.id]);
                // $refsが参照できないので以下に変更
                this.modal = $("#" + this.id);

                if (option["useShowEvent"]) {
                    // モーダル表示イベント
                    this.modal.on("show.bs.modal", this.onShowModal);
                }
            },
            methods: {
                //---------------------------
                // モーダルを表示する
                //---------------------------
                show() {
                    this.modal.modal();
                },
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
                    // モック時は処理しない
                    if (!option["isMock"]) {
                        // 一旦クリアする
                        this.item = {};
                    }

                    // クリックされたボタンを取得
                    const button = $(event.relatedTarget);
                    // data属性をすべて取得し送信する
                    const sendData = self.getDatasFromButton(button);

                    // また、確認モーダルの場合、OKボタン押下時にもこのIDは使用するためthisに保持しておく
                    this.sendData = Object.assign(
                        sendData,
                        // 送信データを追加する
                        option["addSendData"]()
                    );

                    AjaxCom.getPromise()
                        .then(() => {
                            // ローディング
                            //FormCom.loadingForCardOn("#" + this.id + " .modal-content");

                            // 詳細データを取得
                            const url =
                                UrlCom.getFuncUrl() +
                                "/get_data" +
                                option["urlSuffix"];

                            // モック時は送信しない
                            if (!option["isMock"]) {
                                return axios.post(url, sendData);
                            }
                        })
                        .then((response) => {
                            // データを取得する

                            // モック時は処理しない
                            if (!option["isMock"]) {
                                // itemにレスポンス結果を格納
                                this.item = response.data;
                            }
                        })
                        .then(() => {
                            // ローディング
                            //this.loadingForCardOff("#" + this.id);
                        })
                        .catch(AjaxCom.fail);
                },
                //---------------------------
                // モーダル確認
                // 送信フォーム内(getVueInputForm)から呼ばれる
                //---------------------------
                confirm() {
                    // プロミスで待つ。modalOkで解除する
                    this.deferred = new $.Deferred();
                    this.isConfirm = true;

                    return this.deferred.promise();
                },
                //---------------------------
                // 確認モーダル時
                // モーダルのOKボタン押下時イベント
                //---------------------------
                modalOk() {
                    if (this.isConfirm) {
                        //-----------------------------
                        // 確認ダイアログ時。(confirm中)
                        //-----------------------------
                        this.isConfirm = false;
                        // モーダルを閉じる
                        this.hide();
                        // プロミスを返却
                        this.deferred.resolve("result");
                    } else {
                        // チェック処理有の場合
                        if (option["execValidate"]) {
                            const self = this;
                            AjaxCom.getPromise()
                            .then(() => {
                                var url =
                                    UrlCom.getFuncUrl() + "/vd_exec" + option["urlSuffix"];
                                // モック時は送信しない
                                if (!option["isMock"]) {
                                    // バリデーション
                                    var sendData = {};
                                    return axios.post(url, sendData);
                                }
                            })
                            .then((response) => {
                                // モック時はチェックしない
                                if (!option["isMock"]) {
                                    // バリデーション結果をチェック
                                    if (!(response.data.length <= 0)) {
                                        if (
                                            response.data.hasOwnProperty("validate_mail")
                                        ) {
                                            // メール送信処理中の場合、アラートダイアログを表示
                                            appDialogCom.alert("メール一括送信処理中です。時間を空けて再実行してください。");
                                        } else {
                                            // このルートは現状通らない。
                                            // 今後パターンが増えた場合のルートとして仮のエラーメッセージを設定しておく。
                                            appDialogCom.alert("エラーが発生しました。");
                                        }
                                        // モーダルを閉じる
                                        this.hide();
                                        // 処理を抜ける
                                        return AjaxCom.exit();
                                    }
                                }
                            })
                            .then(() => {
                                // モーダルを閉じる
                                this.hide();
                                // ダイアログ
                                appDialogCom.progressShow();
                                // モーダル処理を行う
                                const url =
                                    UrlCom.getFuncUrl() +
                                    "/exec_modal" +
                                    option["urlSuffix"];
                                // モック時は送信しない
                                if (!option["isMock"]) {
                                    // 送信
                                    return axios.post(url, this.sendData);
                                } else {
                                    // ダミーウェイト
                                    return DummyCom.wait();
                                }
                            })
                            .then((response) => {
                                // ダイアログ
                                appDialogCom.progressHide();
                                //-----------------------------
                                // 完了メッセージ
                                //-----------------------------
                                return appDialogCom.success();
                            })
                            .then(
                                // 後処理を実行する
                                option["afterExec"]
                            )
                            .catch(AjaxCom.fail);
                        } else {
                        //-----------------------------
                        // exec処理を呼ぶ
                        //-----------------------------
                        AjaxCom.getPromise()
                            .then(() => {
                                // モーダルを閉じる
                                this.hide();

                                // ダイアログ
                                appDialogCom.progressShow();

                                // モーダル処理を行う
                                const url =
                                    UrlCom.getFuncUrl() +
                                    "/exec_modal" +
                                    option["urlSuffix"];

                                // モック時は送信しない
                                if (!option["isMock"]) {
                                    // 送信
                                    return axios.post(url, this.sendData);
                                } else {
                                    // ダミーウェイト
                                    return DummyCom.wait();
                                }
                            })
                            .then((response) => {
                                // ダイアログ
                                appDialogCom.progressHide();

                                // dataがあれば、ファイルのダウンロードとした
                                // ギフトカードの一覧出力くらい？
                                if (!(response.data.length <= 0)) {
                                    //-----------------------------
                                    // ファイルのダウンロード
                                    //-----------------------------

                                    // サーバーからSJISで応答してもここでUTF-8に変換されるため、
                                    // 手動で変換する。axiosがUTF-8にしてるっぽい
                                    // content-typeのcharsetを見てsjisか判断する
                                    const contentType =
                                        response.headers["content-to-sjis"];

                                    var fileURL = null;
                                    if (
                                        contentType &&
                                        contentType.indexOf(
                                            "charset=Shift_JIS"
                                        ) >= 0
                                    ) {
                                        //------------------
                                        // SJIS
                                        // エンコードする
                                        //------------------

                                        let resData = response.data;
                                        var codeRes =
                                            Encoding.stringToCode(resData);
                                        var arrRes = Encoding.convert(
                                            codeRes,
                                            "SJIS",
                                            "UNICODE"
                                        );
                                        var u8a = new Uint8Array(arrRes);
                                        var blob = new Blob([u8a], {
                                            type: "text/csv;charset=sjis;",
                                        });
                                        fileURL =
                                            window.URL.createObjectURL(blob);
                                    } else {
                                        //------------------
                                        // 通常通りUTF-8
                                        //------------------

                                        fileURL = window.URL.createObjectURL(
                                            new Blob([response.data])
                                        );
                                    }

                                    // aタグを作成
                                    const fileLink =
                                        document.createElement("a");
                                    // ファイル名の取得
                                    const contentDisposition =
                                        response.headers["content-disposition"];
                                    let fileName = contentDisposition.substring(
                                        contentDisposition.indexOf("''") + 2,
                                        contentDisposition.length
                                    );
                                    //デコードするとスペースが"+"になるのでスペースへ置換します
                                    fileName = decodeURI(fileName).replace(
                                        /\+/g,
                                        " "
                                    );
                                    fileLink.href = fileURL;
                                    fileLink.setAttribute("download", fileName);
                                    document.body.appendChild(fileLink);
                                    // クリックしてダウンロード
                                    fileLink.click();
                                    return;
                                } else {
                                    //-----------------------------
                                    // 完了メッセージ
                                    //-----------------------------
                                    return appDialogCom.success();
                                }
                            })
                            .then(
                                // 後処理を実行する
                                option["afterExec"]
                            )
                            .catch(AjaxCom.fail);
                        }
                    }
                },
            },
        };

        return this.createComponent(vueApp, id);
    }
}
