"use strict";

/*
 * 超過勤務者一覧
 */
export default class AppClass extends PageBase {
    /**
     * コンストラクタ
     */
    constructor() {
        super();
    }

    /**
     * 開始処理
     */
    start() {
        // Vue: 検索フォーム
        this.getVueSearchForm({
            // 検索フォーム内にDatePickerを使用する場合
            useSearchDatePicker: true
        });
    }
}
