"use strict";

/*
 * ダミークラス
 */
export default class DummyCom {
    /**
     * ダミー用のWait モック用
     */
    static wait() {
        var time = 300;
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                resolve(`wait: ${time}`);
            }, time);
        });
    }
}
