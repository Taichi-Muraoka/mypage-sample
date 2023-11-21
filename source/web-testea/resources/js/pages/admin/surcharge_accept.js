"use strict";

/*
 * 追加請求申請受付
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
        var searchForm = this.getVueSearchForm({
            // 検索フォーム内にDatePickerを使用する場合
            useSearchDatePicker: true
        });

        // Vue: モーダル
        this.getVueModal();

        // Vue: モーダル(受付)
        this.getVueModal({
            id: "#modal-dtl-acceptance",

            // 完了処理後
            afterExec: () => {
                // 一覧を再表示する
                searchForm.vueSearchList.refresh();
            }
        });
    }
}