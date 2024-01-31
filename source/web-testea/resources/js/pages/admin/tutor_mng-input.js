"use strict";

/*
 * 教師登録・編集
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
        const self = this;

        // 編集完了後は一覧へ戻る
        var afterNew = () => {
            // 新規登録の場合は、会員一覧に戻る
            UrlCom.redirect(UrlCom.getFuncUrl());
        };
        var afterEdit = () => {
            // 編集の場合は、講師詳細画面（二階層目）に戻る
            self.redirectToParent();
        };

        // Vue: 入力フォーム
        const vueForm = this.getVueInputForm({
            afterEdit: afterEdit,
            afterNew: afterNew,
        });

        // 選択後イベント
        var afterSelected = (modalButtonData, selectedDatas) => {
            // selectedDatasは「選択」ボタンのvueDataAttrにセットされている値

            // モーダルを起動したボタンのID取得
            const modalSelectId = modalButtonData.modalselectid;

            // 学校名と学校コードを更新
            // 学校名
            vueForm.form["text_" + modalSelectId] = selectedDatas["school_name"];
            // 学校コード
            vueForm.form[modalSelectId] = selectedDatas["school_cd"];
        };

        // Vue: 選択モーダル(学校検索)
        this.getVueModalSelectList({
            urlSuffix: "school",
            afterSelected: afterSelected,
        });
    }
}
