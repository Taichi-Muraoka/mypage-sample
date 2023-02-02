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
}
