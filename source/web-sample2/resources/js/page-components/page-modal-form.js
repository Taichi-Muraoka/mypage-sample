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

        // モーダルボタンのID（モーダルフォームの切り分けに使用）
        if (option["buttonId"] == undefined) {
            option["buttonId"] = "#modal-buttons";
        }

        // 確定後
        if (option["afterOk"] == undefined) {
            option["afterOk"] = () => {};
        }

        // モーダル表示時
        if (option["onShowModal"] == undefined) {
            option["onShowModal"] = ($vue) => {};
        }

        // 更新処理を行うかどうか。
        if (option["execUpdate"] == undefined) {
            option["execUpdate"] = false;
        }

        // 更新処理後
        if (option["afterUpdate"] == undefined) {
            option["afterUpdate"] = () => {};
        }

        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (ValueCom.isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else {
            option["urlSuffix"] = "_" + option["urlSuffix"];
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
        //const elId = "#modal-buttons";
        const elId = option["buttonId"];

        // Vue: モーダル
        const self = this;
        const vueApp = {
            data() {
                return {
                    modal: null,
                    id: option["id"].replace("#", ""),
                    // モーダルを開いたときのボタンのdata属性を保持しておく
                    modalButtonData: null,
                    // モーダルの詳細データをセットする
                    item: {},
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
                    // モック時は処理しない
                    if (!option["isMock"]) {
                        // 一旦クリアする
                        this.item = {};
                    }

                    // 一旦エラーをクリア
                    this.vueInputForm.form_err.msg = {};
                    this.vueInputForm.form_err.class = {};

                    // クリックされたボタンを取得
                    var button = $(event.relatedTarget);
                    const sendData = self.getDatasFromButton(button);
                    this.modalButtonData = sendData;

                    if (option["execUpdate"]) {
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
                                option["onShowModal"](this, this.item);
                            }
                        })
                        .then(() => {
                            // ローディング
                            //this.loadingForCardOff("#" + this.id);
                        })
                        .catch(AjaxCom.fail);
                    } else {
                        option["onShowModal"](this, this.modalButtonData);
                    }
                },
                //---------------------------
                // モーダルの確定ボタン押下時イベント
                //---------------------------
                modalOk() {
                    // フォームデータの更新処理有の場合
                    if (option["execUpdate"]) {
                        // フォーム更新処理
                        self._sendEdit(this, option);
                    } else {
                        // 更新処理がない場合はafterOkの処理を実行しフォームを閉じる
                        option["afterOk"](
                            this.modalButtonData,
                            this.vueInputForm.form
                        );
                        this.hide();
                    }
                },
            },
        };

        return this.createComponent(vueApp, elId);
    }

    /**
     * 送信用のフォームデータを取得
     *
     * @param vueインスタンス
     */
    _getSendFormData($vue) {
        // フォームの値を取得(アップロードファイルも取得)
        const formData = new FormData();

        // 通常の入力用
        for (const [key, inputElement] of Object.entries($vue.form)) {
            // フォームを追加
            formData.append(key, inputElement);
        }

        // ファイルのアップロード用
        var existFile = false;
        const files = $("input[type='file']").filter(function (idx, el) {
            return el.id.startsWith("file_");
        });

        if (files.length > 0) {
            for (var i = 0; i < files.length; i++) {
                // ファイルを取得
                const fileElement = files[i];
                var key = null;
                if(fileElement.files.length == 1) {
                    key = fileElement.id;
                } else {
                    key = fileElement.id + '[]';
                }

                // ファイルを取得(1つのinput fileで複数選択しないので0しか無いはず)
                for (var j = 0; j < fileElement.files.length; j++) {
                    // フラグ(アップロードするファイルがある場合)
                    existFile = true;

                    // ファイルを取得
                    const file = fileElement.files[j];

                    // MEMO: 複数ファイル選択に対応する場合は、j番目を追加しないといけないはず
                    formData.append(key, file);
                }
            }
        }

        // アップロード用のコンテンツタイプ
        var formHeader = {};
        if (existFile) {
            // これがないとコントローラへファイルがアップロードされない
            formHeader = {
                headers: { "Content-Type": "multipart/form-data" },
            };
        }

        return {
            data: formData,
            header: formHeader,
            // アップロードが含まれるかどうか
            upload: existFile,
        };
    }

    /**
     * 編集
     *
     * @param vueインスタンス
     */
    _sendEdit($vue, option) {
        // 送信フォームのデータを取得する
        var sendData = this._getSendFormData($vue.vueInputForm);

        const self = this;
        AjaxCom.getPromise()
            .then(() => {
                // バリデート(例：http://localhost:8000/sample/edit/1 と同じ階層を想定)
                var url =
                    UrlCom.getFuncUrl() + "/vd_exec" + option["urlSuffix"];
                // モック時は送信しない
                if (!option["isMock"]) {
                    // ファイルアップロード時は大きいファイルが想定されるのでローディングを表示
                    // 空き時間登録のチェックボックスが多くやや時間がかかるので強制的に表示する場合も想定
                    if (sendData.upload || option["progressShow"]) {
                        // ダイアログ
                        appDialogCom.progressShow();
                    }
                    // バリデーション
                    return axios.post(url, sendData.data, sendData.header);
                }
            })
            .then((response) => {
                // モック時はチェックしない
                if (!option["isMock"]) {
                    // ファイルアップロード時は大きいファイルが想定されるのでローディングを表示
                    if (sendData.upload || option["progressShow"]) {
                        // ダイアログ
                        appDialogCom.progressHide();
                    }

                    // バリデーション結果をチェック
                    if (!self.validateCheck($vue.vueInputForm, response)) {
                        // エラー箇所へのスクロールをしない
                        $vue.vueInputForm.afterValidate = false;
                        // 処理を抜ける
                        return AjaxCom.exit();
                    }
                }

                // 確認ダイアログ
                if (
                    Object.keys(response.data).length == 1 &&
                    response.data["confirm_modal_data"]
                ) {
                    // 確認モーダルダイアログ

                    // モーダルに表示したいデータをセットする
                    $vue.vueModal.item = response.data["confirm_modal_data"];

                    // モーダルを表示する
                    $vue.vueModal.show;

                    // 確認
                    return $vue.vueModal.confirm();
                } else {
                    // 通常の確認ダイアログ
                    return appDialogCom.confirmSend(option["confirmStrEdit"]);
                }
            })
            .then((flg) => {
                if (!flg) {
                    // いいえを押した場合
                    return AjaxCom.exit();
                }

                // ダイアログ
                appDialogCom.progressShow();

                // 編集
                var url = UrlCom.getFuncUrl() + "/exec_modal" + option["urlSuffix"];

                // モック時は送信しない
                if (!option["isMock"]) {
                    // 送信
                    return axios.post(url, sendData.data, sendData.header);
                } else {
                    // ダミーウェイト
                    return DummyCom.wait();
                }
            })
            .then((response) => {
                // ダイアログ
                appDialogCom.progressHide();

                // エラー応答の場合は、アラートを表示する
                if (
                    Object.keys(response.data).length == 1 &&
                    response.data["error"]
                ) {
                    appDialogCom.alert(response.data["error"]);
                    return AjaxCom.exit();
                }

                // 正常完了の場合は、モーダルを閉じる
                $vue.hide();
                // 完了メッセージ
                return appDialogCom.success(option["登録"]);
            })
            .then(
                // 後処理を実行する
                option["afterUpdate"]
            )
            .catch(AjaxCom.fail);
    }
}
