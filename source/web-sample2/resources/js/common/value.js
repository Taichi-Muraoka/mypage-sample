"use strict";

/*
 * 値処理クラス
 */
export default class ValueCom {
    /**
     * 空白チェック
     *
     * @param val チェック文字列
     */
    static isEmpty(val) {
        return !val ? (!(val === 0 || val === false) ? true : false) : false;
    }
}
