"use strict";

/*
 * 会員登録・編集
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
        // 編集完了後は一覧へ戻る
        var afterNew = () => {
            // 新規登録の場合は、会員一覧に戻る
            UrlCom.redirect(self._getFuncUrl());
        };
        var afterEdit = () => {
            // 編集の場合は、生徒カルテ画面（二階層目）に戻る
            self.redirectToParent();
        };

        // Vue: 入力フォーム
        const vueForm = this.getVueInputForm({
            afterEdit: afterEdit,
            afterNew: afterNew,
            // 選択モーダルを使用する場合
            useModalSelect: true,
        });

        // 選択後イベント
        var afterSelected = (modalButtonData, selectedDatas) => {
            // selectedDatasは「選択」ボタンのvueDataAttrにセットされている値

            // モーダルを起動したボタンのID取得
            const modalSelectId = modalButtonData.modalselectid;

            // 学校名とIDを更新
            // 名称
            Vue.set(
                vueForm.form,
                "text_" + modalSelectId,
                selectedDatas["school_name"]
            );
            // ID
            Vue.set(vueForm.form, modalSelectId, selectedDatas["school_id"]);
        };

        // Vue: 選択モーダル(学校検索)
        this.getVueModalSelectList({
            urlSuffix: "school",
            afterSelected: afterSelected,
        });
    }
}
