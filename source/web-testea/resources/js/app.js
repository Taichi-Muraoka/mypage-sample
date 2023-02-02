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

import CalendarCom from "./common/calendar";
window.CalendarCom = CalendarCom;

import ModalCom from "./common/modal";
window.ModalCom = ModalCom;
// モーダル初期化(複数モーダルを開く対応など)
ModalCom.init();

// vue
import Vue from "vue";
window.Vue = Vue;

// axios
import axios from "axios";
window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

//---------------------------
// 共通初期化処理
//---------------------------

// Vueとの相性が良くないので、Vueの方で呼ぶ
// daterangepickerも同様
//$(".select2").select2({});

// Vueのselect2対応。v-selectを作成し、チェンジイベントを拾い、値をセット。
Vue.directive("select", {
    bind: function(el, binding, vnode) {
        $(el)
            .select2()
            .on("select2:select", e => {
                el.dispatchEvent(new Event("change", { target: e.target }));
            });
    }
});

// http または httpsから始まる文字列をリンクにするコンポーネント
// 参考：https://www.softel.co.jp/blogs/tech/archives/6514
// 使い方：<autolink :text="変数名"></autolink>
Vue.component("autolink", {
    props: ["text"],
    render: function(createElement) {
        // 変数が空の場合は処理なし
        if (!this.text) {
            return;
        }

        var a = this.text.split(
            /(https?:\/\/[\w!?:\/\+\-_~=;\.,*&@#$%\(\)\'\[\]]+)/i
        );
        var vnodes = a.map(function(x, i) {
            if (i % 2) {
                return createElement("a", { attrs: { href: x } }, x);
            } else {
                return this._v(x);
                //return createElement('span', {}, x)
            }
        }, this);
        return createElement("span", vnodes);
    }
});

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
        if(path === `./${appInfo.view}.js`) {
            modules[path]()
            .then(PageClass => {
                // クラスを生成
                let obj = new PageClass.default();
                obj.start();
            })
            .catch(err => {
                // ファイルが存在しないエラーもあるので基本的に拾わない
                // ログの出力だけする
                console.log(err);
            });
        }
    }
}