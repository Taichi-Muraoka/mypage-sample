"use strict";

/*
 * イベント申込者一覧
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
        // 検索一覧の表示
        var $vueSearchList = this.getVueSearchList({
            // 別画面でも検索を使用するのでURLを変更
            urlSuffix: "entry"
        });
        $vueSearchList.search();

        // Vue: モーダル 詳細
        this.getVueModal({
            // 別画面でもモーダルを使用するのでURLを変更
            urlSuffix: "entry"
        });

        // Vue: モーダル (スケジュール登録確認) 登録
        this.getVueModal({
            // IDを分けた
            id: "#modal-dtl-new",
            // 別画面でもモーダルを使用するのでURLを変更
            urlSuffix: "entry",
            // 完了処理後
            afterExec: () => {
                // フォームを再処理する
                // イベント申込者一覧のリストのステータスを更新。ページャは考慮せず1ページ目に戻るでよい
                $vueSearchList.search();
            }
        });

        // Vue: モーダル (ファイル出力確認・一括受付) 出力
        this.getVueModal({
            // IDを分けた
            id: "#modal-dtl-output",
            // 別画面でもモーダルを使用するのでURLを変更
            urlSuffix: "entry",
            // 完了処理後
            afterExec: () => {
                // フォームを再処理する
                // イベント申込者一覧のリストのステータスを更新。ページャは考慮せず1ページ目に戻るでよい
                $vueSearchList.search();
            }
        });
    }
}
