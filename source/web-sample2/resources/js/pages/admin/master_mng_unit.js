"use strict";

/*
 * 授業単元マスタ管理
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

        // Vue: 検索フォーム
        this.getVueSearchForm({
            vueData: {
                // 単元分類プルダウン変更用のプロパティを用意
                selectGetItemCategory: {},
            },
            vueMethods: {
                // 学年または教材科目プルダウン選択時、単元分類リストを取得
                selectChangeGetCategory: function (event) {
                    // 初期化
                    this.selectGetItemCategory = {};
                    this.form.unit_category_cd = "";

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
