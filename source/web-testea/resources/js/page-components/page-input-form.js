"use strict";

import PageComponentBase from "./page-component-base";
import PageModal from "../page-components/page-modal";
import PageEvent from "../page-components/page-event";

/**
 * ページコンポーネント: 入力フォーム
 */
export default class PageInputForm extends PageComponentBase {
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

        // 確認ダイアログの文言(新規登録)
        if (option["confirmStrNew"] == undefined) {
            option["confirmStrNew"] = "送信";
        }
        // 確認ダイアログの文言(更新)
        if (option["confirmStrEdit"] == undefined) {
            option["confirmStrEdit"] = "送信";
        }

        // 編集完了後の処理(登録・変更・削除後)
        if (option["afterEdit"] == undefined) {
            option["afterEdit"] = () => {};
        }

        // Vueにdataを追加
        if (option["vueData"] == undefined) {
            option["vueData"] = {};
        }

        // Vueにmethodsを追加
        if (option["vueMethods"] == undefined) {
            option["vueMethods"] = {};
        }

        // Vueのmountedイベントを呼ぶ
        // プルダウンのチェンジイベントを発生させる想定
        if (option["vueMounted"] == undefined) {
            option["vueMounted"] = ($vue, option) => {};
        }

        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (ValueCom.isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else {
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // モーダル確認ダイアログを使用するかどうか(使用する場合、モーダルIDを指定)
        if (ValueCom.isEmpty(option["confirmModal"])) {
            option["confirmModal"] = "";
        }

        // 送信時、処理中ダイアログを強制的に表示するかどうか
        if (option["progressShow"] == undefined) {
            option["progressShow"] = false;
        }

        // ライブラリを初期化するかどうか
        // 選択モーダル対応時に、フォームとモーダルの両方を呼ぶため
        // 制御できるようにした。(select2対応)
        // →最後に一回だけ呼べるようにしたいが、とりあえずフラグで制御
        if (option["useModalSelect"] == undefined) {
            option["useModalSelect"] = false;
        }

        // ターゲットのID
        if (option["id"] == undefined) {
            option["id"] = "#app-form";
        }

        //--------------------
        // Vueの定義
        //--------------------

        // フォームのID
        const formId = option["id"];

        // 編集時にformのvalueから値を取得するためformの定義を作成する。
        const formData = FormCom.getFormArrayData(formId);

        // Vue:フォーム
        const self = this;
        const vueApp = {
            // オプションでdataを追加する
            data() {
                return Object.assign(
                    {
                        // VueのIdを格納する
                        appId: formId,
                        // フォームインプット
                        //form: {},
                        form: formData,
                        // フォームエラー
                        form_err: {
                            msg: {},
                            class: {},
                        },

                        // プルダウン選択後の詳細を格納する
                        selectGetItem: {},

                        // 確認モーダル
                        vueModal: null,

                        // オプションを保持しておく
                        option: null,

                        // バリデーション後かどうか
                        afterValidate: false,
                        // エラーのキーを格納
                        validateErrKey: [],
                    },
                    option["vueData"]
                );
            },
            mounted() {
                // optionを保持
                this.option = option;

                // ライブラリの初期化
                if (!option["useModalSelect"]) {
                    self.initLibs(this, option);
                }else{
                    // 検索モーダル・フォームで使用するSelect2は、検索モーダル・フォームで初期化
                    // ※DatePickerありの入力フォームから呼び出される検索モーダルでDatePickerを使う場合は、要検討
                    self.initFileInput(this, option);
                    self.initDatePicker(this, option);
                }

                // 確認モーダルの表示用
                if (!ValueCom.isEmpty(option["confirmModal"])) {
                    const pageModal = new PageModal();
                    this.vueModal = pageModal.getVueApp({
                        useShowEvent: false,
                        id: option["confirmModal"],
                    });
                }

                // 呼び出し元のmouted処理を呼ぶ
                option["vueMounted"](this, option);
            },
            updated() {
                // バリデーションエラー時に、エラー箇所にスクロールする
                // これはVueの更新後じゃないと、エラーメッセージが表示されず、スクロール位置が取れないため
                if (this.afterValidate) {
                    // 一番上のエラーを探す
                    var errSpan = null;
                    var errSpans = $.find(".form-validation");
                    for (var i = 0; i < errSpans.length; i++) {
                        var dataId = $(errSpans[i]).attr("data-id");
                        if (this.validateErrKey.indexOf(dataId) >= 0) {
                            errSpan = errSpans[i];
                            break;
                        }
                    }

                    // スクロール
                    if (errSpan) {
                        // エラー箇所へ移動する
                        var position = $(errSpan).offset().top;
                        var speed = 300;
                        $("html, body").animate(
                            { scrollTop: position },
                            speed,
                            "swing"
                        );
                    }

                    // リセット
                    this.afterValidate = false;
                    this.validateErrKey = [];
                }

                // Vue更新後、ライブラリの初期化
                if (!option["useModalSelect"]) {
                    self.updatedLibs();
                }
            },
            // オプションでメソッドを追加する
            methods: Object.assign(
                {
                    // 送信ボタン
                    submitNew: function () {
                        // 新規登録処理
                        if (option["afterNew"]) {
                            option["afterEdit"] = option["afterNew"];
                        }
                        self._sendNew(this, option);
                    },
                    // 編集ボタン
                    submitEdit: function () {
                        // 変更処理
                        self._sendEdit(this, option);
                    },
                    // 削除ボタン
                    submitDelete: function () {
                        // 削除処理
                        self._sendDelete(this, option);
                    },
                    // バリデーションあり削除ボタン
                    submitValidationDelete: function () {
                        // 削除処理
                        self._sendValidationDelete(this, option);
                    },
                    // 承認送信ボタン
                    submitApproval: function () {
                        // 承認処理
                        self._sendApproval(this, option);
                    },
                    // プルダウンの変更イベントで詳細を取得
                    selectChangeGet: function (event) {
                        // プルダウン変更
                        // 選択された値(呼び出し元が直接呼ぶ可能性があるのでここでselectedを取るようにした)
                        var selected = event.target.value;
                        PageEvent.selectChangeGet(this, selected, option);
                    },
                    // ファイルアップロード(アップロード済みファイル削除。実際はhidden値を削除する)
                    fileUploadedDelete: function (event) {
                        var uploaded = $(event.target).parent().parent();
                        // hiddenを取得
                        var updHidden = uploaded
                            .find("input[type='hidden']")
                            .get(0);
                        // Vueのformから削除
                        this.form[updHidden.id] = "";
                        // divごと非表示にする
                        uploaded.remove();
                    },
                    // 選択モーダル入力の取り消しボタン処理
                    modalSelectClear: function (event) {
                        // クリックされたボタンを取得
                        var button = $(event.target);
                        // data属性を取得
                        const modalButtonData = self.getDatasFromButton(button);
                        const modalSelectId = modalButtonData.modalselectid;

                        // クリア
                        this.form["text_" + modalSelectId] = "";
                        this.form[modalSelectId] = "";
                    },
                },
                option["vueMethods"]
            ),
        };

        return this.createComponent(vueApp, formId);
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
                const key = fileElement.id;

                // ファイルを取得(1つのinput fileで複数選択しないので0しか無いはず)
                for (var i = 0; i < fileElement.files.length; i++) {
                    // フラグ(アップロードするファイルがある場合)
                    existFile = true;

                    // ファイルを取得
                    const file = fileElement.files[i];

                    // MEMO: 複数ファイル選択に対応する場合は、i番目を追加しないといけないはず
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
     * 新規登録
     *
     * @param vueインスタンス
     */
    _sendNew($vue, option) {
        // 送信フォームのデータを取得する
        var sendData = this._getSendFormData($vue);

        const self = this;
        AjaxCom.getPromise()
            .then(() => {
                // バリデート(例：http://localhost:8000/sample/new と同じ階層を想定)
                var url =
                    UrlCom.getFuncUrl() + "/vd_input" + option["urlSuffix"];

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
                    if (!self.validateCheck($vue, response)) {
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
                    $vue.vueModal.show();

                    // 確認
                    return $vue.vueModal.confirm();
                } else {
                    // 通常の確認ダイアログ
                    return appDialogCom.confirmSend(option["confirmStrNew"]);
                }
            })
            .then((flg) => {
                if (!flg) {
                    // いいえを押した場合
                    return AjaxCom.exit();
                }

                // ダイアログ
                appDialogCom.progressShow();

                // 新規登録
                var url = UrlCom.getFuncUrl() + "/create" + option["urlSuffix"];

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

                // 完了メッセージ
                return appDialogCom.success(option["confirmStrNew"]);
            })
            .then(
                // 後処理を実行する
                option["afterEdit"]
            )
            .catch(AjaxCom.fail);
    }

    /**
     * 編集
     *
     * @param vueインスタンス
     */
    _sendEdit($vue, option) {
        // 送信フォームのデータを取得する
        var sendData = this._getSendFormData($vue);

        const self = this;
        AjaxCom.getPromise()
            .then(() => {
                // バリデート(例：http://localhost:8000/sample/edit/1 と同じ階層を想定)
                var url =
                    UrlCom.getFuncUrl() + "/vd_input" + option["urlSuffix"];
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
                    if (!self.validateCheck($vue, response)) {
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
                    $vue.vueModal.show();

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
                var url = UrlCom.getFuncUrl() + "/update" + option["urlSuffix"];

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

                // 完了メッセージ
                return appDialogCom.success(option["confirmStrEdit"]);
            })
            .then(
                // 後処理を実行する
                option["afterEdit"]
            )
            .catch(AjaxCom.fail);
    }

    /**
     * 削除
     *
     * @param vueインスタンス
     */
    _sendDelete($vue, option) {
        AjaxCom.getPromise()
            .then((response) => {
                // 確認ダイアログ
                return appDialogCom.confirmDel();
            })
            .then((flg) => {
                if (!flg) {
                    // いいえを押した場合
                    return AjaxCom.exit();
                }

                // ダイアログ
                appDialogCom.progressShow();

                // 削除
                var url = UrlCom.getFuncUrl() + "/delete" + option["urlSuffix"];

                // モック時は送信しない
                if (!option["isMock"]) {
                    // 送信
                    return axios.post(url, $vue.form);
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

                // 完了メッセージ
                return appDialogCom.success("削除");
            })
            .then(
                // 後処理を実行する
                option["afterEdit"]
            )
            .catch(AjaxCom.fail);
    }

    /**
     * 削除(バリデーションあり)
     *
     * @param vueインスタンス
     */
    _sendValidationDelete($vue, option) {
        // 送信フォームのデータを取得する
        var sendData = this._getSendFormData($vue);

        const self = this;
        AjaxCom.getPromise()
            .then((response) => {
                // バリデート(例：http://localhost:8000/sample/edit/1 と同じ階層を想定)
                var url =
                    UrlCom.getFuncUrl() + "/vd_delete" + option["urlSuffix"];
                // モック時は送信しない
                if (!option["isMock"]) {
                    // バリデーション
                    return axios.post(url, sendData.data, sendData.header);
                }
            })
            .then((response) => {
                // モック時はチェックしない
                if (!option["isMock"]) {
                    // バリデーション結果をチェック
                    if (!self.validateCheck($vue, response)) {
                        // 処理を抜ける
                        return AjaxCom.exit();
                    }
                }

                // 確認ダイアログ
                return appDialogCom.confirmDel();
            })
            .then((flg) => {
                if (!flg) {
                    // いいえを押した場合
                    return AjaxCom.exit();
                }

                // ダイアログ
                appDialogCom.progressShow();

                // 削除
                var url = UrlCom.getFuncUrl() + "/delete" + option["urlSuffix"];

                // モック時は送信しない
                if (!option["isMock"]) {
                    // 送信
                    return axios.post(url, $vue.form);
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

                // 完了メッセージ
                return appDialogCom.success("削除");
            })
            .then(
                // 後処理を実行する
                option["afterEdit"]
            )
            .catch(AjaxCom.fail);
    }

    /**
     * 承認
     *
     * @param vueインスタンス
     */
    _sendApproval($vue, option) {
        // 送信フォームのデータを取得する
        var sendData = this._getSendFormData($vue);

        const self = this;
        AjaxCom.getPromise()
            .then(() => {
                // バリデート(例：http://localhost:8000/sample/edit/1 と同じ階層を想定)
                var url =
                    UrlCom.getFuncUrl() + "/vd_approval" + option["urlSuffix"];
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
                    if (!self.validateCheck($vue, response)) {
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
                    $vue.vueModal.show();

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
                var url = UrlCom.getFuncUrl() + "/update" + option["urlSuffix"];

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

                // 完了メッセージ
                return appDialogCom.success(option["confirmStrEdit"]);
            })
            .then(
                // 後処理を実行する
                option["afterEdit"]
            )
            .catch(AjaxCom.fail);
    }
}
