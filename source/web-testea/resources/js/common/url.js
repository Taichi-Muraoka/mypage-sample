"use strict";

/*
 * URL処理クラス
 */
export default class UrlCom {
    /**
     * リダイレクト
     */
    static redirect(path) {
        location.href = path;
    }

    /**
     * 機能URLを取得
     * http://localhost/xxxxx/xxxx/
     * http://localhost/xxxxx/xxxx/xxx/xx/
     *
     * ↓以下の一階層目のURLを取得(ここが一覧だったりするので)
     * http://localhost/xxxxx/
     */
    static getFuncUrl() {
        // 現在のURL
        var loc = window.location.href;

        // appInfoのrootにはアプリのルートがあるのでそれを利用する
        // appInfo
        //   root: "http://localhost:8000"

        // 先頭部分を削除
        var url = loc.replace(appInfo.root + "/", "");

        // /まで取得
        var func = "";
        if (url.indexOf("/") < 0) {
            func = url;
        } else {
            func = url.substring(0, url.indexOf("/"));
        }

        return appInfo.root + "/" + func;
    }
}
