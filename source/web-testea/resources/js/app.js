/*
 * アプリケーション用JS
 * メイン処理
 */
"use strict";

//---------------------------
// モジュール読み込み
//---------------------------

// common

// モック用ダミー
import DummyCom from "./common/dummy";
window.DummyCom = DummyCom;

import UrlCom from "./common/url";
window.UrlCom = UrlCom;

import PageBase from "./common/page-base";
window.PageBase = PageBase;

import AjaxCom from "./common/ajax";
window.AjaxCom = AjaxCom;

import DialogCom from "./common/dialog";
// ダイアログのインスタンス
window.appDialogCom = new DialogCom();

import ModalCom from "./common/modal";
window.ModalCom = ModalCom;
// モーダル初期化(複数モーダルを開く対応など)
ModalCom.init();

import FormCom from "./common/form";
window.FormCom = FormCom;

import ValueCom from "./common/value";
window.ValueCom = ValueCom;

// axios
import axios from "axios";
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

//---------------------------
// 各ページ処理
//---------------------------

// ログイン系の画面は無視する。
if (appInfo.view.indexOf("auth") !== 0) {
    // 各ページのJSを読み込み(テンプレートパスと同様)
    //import("./" + appInfo.view)
    // Vite対応
    const modules = import.meta.glob("./**/*.js");
    for (const path in modules) {
        if (path === `./${appInfo.view}.js`) {
            modules[path]()
                .then((PageClass) => {
                    // クラスを生成
                    let obj = new PageClass.default();
                    obj.start();
                })
                .catch((err) => {
                    // ファイルが存在しないエラーもあるので基本的に拾わない
                    // ログの出力だけする
                    console.log(err);
                });
        }
    }
}
