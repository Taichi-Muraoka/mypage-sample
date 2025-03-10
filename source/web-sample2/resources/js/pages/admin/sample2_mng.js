"use strict";

/*
 * サンプル一覧（モーダル更新フォーム有）
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
            }
        );

        // 登録完了後イベント
        var afterUpdate = () => {
            // 一覧データ再表示
            searchForm.execSearch();
        };

        // Vue: モーダル
        this.getVueModal({
        });

        // Vue: 入力モーダル
        this.getVueModalForm({
            id: "#modal-dtl-input",
            buttonId: "#modal-input-buttons",
            // フォーム登録処理あり
            execUpdate: true,
            // モーダル表示時の処理
            onShowModal: function ($vue, item) {
                // 編集内容セット
                $vue.vueInputForm.form.sample_id = item.sample_id;
                $vue.vueInputForm.form.sample_title = item.sample_title;
                $vue.vueInputForm.form.sample_text = item.sample_text;
                $vue.vueInputForm.form.sample_state = item.sample_state;
            },
            // 登録完了後の処理
            afterUpdate: afterUpdate,
        });
    }
}
