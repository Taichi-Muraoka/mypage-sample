"use strict";

/*
 * 授業単元マスタ登録・編集
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
        var afterEdit = () => {
            UrlCom.redirect(UrlCom.getFuncUrl());
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,

            vueData: {
                // 単元分類プルダウン変更用のプロパティを用意
                selectGetItemCategory: {},
            },
            // 画面読み込み時
            vueMounted: function ($vue, option) {
                // 編集画面表示時に、単元分類リスト設定処理
                $vue.selectChangeGetInit();
            },
            vueMethods: {
                // 編集画面表示時の単元分類リスト設定
                selectChangeGetInit: function () {
                    // 新規画面（未選択）の場合何もしない
                    if (ValueCom.isEmpty(this.form["unit_id"])) {
                        return;
                    }

                    // 編集対象データ($editData)に基づいた単元分類プルダウンのvalue初期表示・リスト絞り込み
                    this.form.unit_category_cd = this.form._unit_category_cd;
                    this.selectChangeGetCategory();
                },
                // 学年または教材科目プルダウン選択時、単元分類リストを取得
                selectChangeGetCategory: function (event) {
                    // 初期化
                    this.selectGetItemCategory = {};

                    // チェンジイベントを発生させる
                    var gradeCd = this.form.grade_cd;
                    var tSubjectCd = this.form.t_subject_cd;

                    self.selectChangeGet2(
                        this,
                        {
                            grade_cd: gradeCd,
                            t_subject_cd: tSubjectCd,
                        },
                        // URLを分けた
                        {
                            urlSuffix: "category",
                        },
                        // vueData指定
                        "selectGetItemCategory"
                    );
                },
            },
        });
    }
}
