"use strict";

/**
 * ページコンポーネント: イベント
 * イベントは複数回呼ばれるのでstaticとした
 */
export default class PageEvent {
    /**
     * プルダウンの変更イベントで詳細を取得
     */
    static selectChangeGet($vue, selected, option) {
        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (ValueCom.isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else if (option["urlSuffix"].charAt(0) == "_") {
            // 何もしない(vueのoptionを渡された場合)
        } else {
            // 直接指定された場合
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // objectかどうかチェック
        var sendData = {};
        if (selected instanceof Object) {
            // オブジェクトの場合
            sendData = selected;
        } else {
            // 空白なら終了
            if (ValueCom.isEmpty(selected)) {
                $vue.selectGetItem = {};
                return;
            }
            // IDをつけて送信する
            sendData = {
                id: selected,
            };
        }

        // 選択されたプルダウンのIDをもとにデータを取得
        AjaxCom.getPromise()
            .then(() => {
                // ローディング開始
                FormCom.loadingForCardOn($vue.appId);

                // 予め用意されたプルダウンの選択なのでバリデーションは不要とする
                // ただし、コントローラではバリデーションのチェックは行う必要がある。
                // ハンドリングの必要がないということ
                var url =
                    UrlCom.getFuncUrl() +
                    "/get_data_select" +
                    option["urlSuffix"];

                // 選択された値を送信する
                return axios.post(url, sendData);
            })
            .then((response) => {
                // selectGetItemにレスポンス結果を格納
                $vue.selectGetItem = response.data;

                // 入力エリアのエラーは一旦クリアする
                $vue.form_err.msg = {};
                $vue.form_err.class = {};

                // ローディング終了
                FormCom.loadingForCardOff($vue.appId);
            })
            .catch(AjaxCom.fail);
    }

    /**
     * プルダウンの変更イベントで詳細を取得（レスポンス結果格納エリア指定）
     * $vue.selectGetItemは使用しない
     */
    static selectChangeGet2($vue, selected, option, itemData) {
        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (ValueCom.isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else if (option["urlSuffix"].charAt(0) == "_") {
            // 何もしない(vueのoptionを渡された場合)
        } else {
            // 直接指定された場合
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // objectかどうかチェック
        var sendData = {};
        if (selected instanceof Object) {
            // オブジェクトの場合
            sendData = selected;
        } else {
            // 空白なら終了
            if (ValueCom.isEmpty(selected)) {
                $vue[itemData] = {};
                return;
            }
            // IDをつけて送信する
            sendData = {
                id: selected,
            };
        }

        // 選択されたプルダウンのIDをもとにデータを取得
        AjaxCom.getPromise()
            .then(() => {
                // ローディング開始
                FormCom.loadingForCardOn($vue.appId);

                // 予め用意されたプルダウンの選択なのでバリデーションは不要とする
                // ただし、コントローラではバリデーションのチェックは行う必要がある。
                // ハンドリングの必要がないということ
                var url =
                    UrlCom.getFuncUrl() +
                    "/get_data_select" +
                    option["urlSuffix"];

                // 選択された値を送信する
                return axios.post(url, sendData);
            })
            .then((response) => {
                // selectGetItemにレスポンス結果を格納
                $vue[itemData] = response.data;

                // 入力エリアのエラーは一旦クリアする
                $vue.form_err.msg = {};
                $vue.form_err.class = {};

                // ローディング終了
                FormCom.loadingForCardOff($vue.appId);
            })
            .catch(AjaxCom.fail);
    }

    /**
     * プルダウンの変更イベントで詳細を取得
     * コールバック用とした。selectGetItemは初期化しないのでcallbackで処理してもらう
     * 例：お知らせ登録
     */
    static selectChangeGetCallBack($vue, selected, option, callback) {
        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (ValueCom.isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else if (option["urlSuffix"].charAt(0) == "_") {
            // 何もしない(vueのoptionを渡された場合)
        } else {
            // 直接指定された場合
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // objectかどうかチェック
        var sendData = {};
        if (selected instanceof Object) {
            // オブジェクトの場合
            sendData = selected;
        } else {
            // 空白なら終了
            if (ValueCom.isEmpty(selected)) {
                $vue.selectGetItem = {};
                return;
            }
            // IDをつけて送信する
            sendData = {
                id: selected,
            };
        }

        // 選択されたプルダウンのIDをもとにデータを取得
        AjaxCom.getPromise()
            .then(() => {
                // ローディング開始
                FormCom.loadingForCardOn($vue.appId);

                // 予め用意されたプルダウンの選択なのでバリデーションは不要とする
                // ただし、コントローラではバリデーションのチェックは行う必要がある。
                // ハンドリングの必要がないということ
                var url =
                    UrlCom.getFuncUrl() +
                    "/get_data_select" +
                    option["urlSuffix"];

                // 選択された値を送信する
                return axios.post(url, sendData);
            })
            .then((response) => {
                // コールバック
                callback(response.data);

                // 入力エリアのエラーは一旦クリアする
                $vue.form_err.msg = {};
                $vue.form_err.class = {};

                // ローディング終了
                FormCom.loadingForCardOff($vue.appId);
            })
            .catch(AjaxCom.fail);
    }

    /**
     * プルダウンの変更イベントで詳細を取得（レスポンス結果格納エリア指定）
     * コールバック用とした。$vue.selectGetItemは使用しない
     */
    static selectChangeGetCallBack2($vue, selected, option, itemData, callback) {
        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (ValueCom.isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else if (option["urlSuffix"].charAt(0) == "_") {
            // 何もしない(vueのoptionを渡された場合)
        } else {
            // 直接指定された場合
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // objectかどうかチェック
        var sendData = {};
        if (selected instanceof Object) {
            // オブジェクトの場合
            sendData = selected;
        } else {
            // 空白なら終了
            if (ValueCom.isEmpty(selected)) {
                $vue[itemData] = {};
                return;
            }
            // IDをつけて送信する
            sendData = {
                id: selected,
            };
        }

        // 選択されたプルダウンのIDをもとにデータを取得
        AjaxCom.getPromise()
            .then(() => {
                // ローディング開始
                FormCom.loadingForCardOn($vue.appId);

                // 予め用意されたプルダウンの選択なのでバリデーションは不要とする
                // ただし、コントローラではバリデーションのチェックは行う必要がある。
                // ハンドリングの必要がないということ
                var url =
                    UrlCom.getFuncUrl() +
                    "/get_data_select" +
                    option["urlSuffix"];

                // 選択された値を送信する
                return axios.post(url, sendData);
            })
            .then((response) => {
                // コールバック
                callback(response.data);

                // 入力エリアのエラーは一旦クリアする
                $vue.form_err.msg = {};
                $vue.form_err.class = {};

                // ローディング終了
                FormCom.loadingForCardOff($vue.appId);
            })
            .catch(AjaxCom.fail);
    }
}
