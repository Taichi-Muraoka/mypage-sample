"use strict";

/*
 * 講師授業集計
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
        // Vue: モーダル
        this.getVueModal();

        // Vue: 検索フォーム
        this.getVueSearchForm(
            {
                initSearch: false,
                
                // 検索フォーム内にDatePickerを使用する場合
                useSearchDatePicker: true,
            }
        );
    }
}
