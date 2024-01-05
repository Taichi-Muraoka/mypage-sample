"use strict";

/*
 * 成績事例検索一覧
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
        var searchForm = this.getVueSearchForm({
            // 初期検索を行わない
            initSearch: false,
            vueData: {
                // 学校区分プルダウン変更用のプロパティを用意
                selectGetItemGrade: {},
                // 種別プルダウン変更用のプロパティを用意
                selectGetItemExam: {},
            },
            vueMethods: {
                // 学校区分プルダウン選択時、学年リストを取得
                selectChangeGetGrade: function (event) {
                    AjaxCom.getPromise()
                        .then(() => {
                            // 初期化
                            this.selectGetItemGrade = {};
                            this.form.grade_cd = "";
                            // チェンジイベントを発生させる
                            var schoolKind = this.form.school_kind;
                            self.selectChangeGetCallBack(
                                this,
                                schoolKind,
                                // URLを分けた
                                {
                                    urlSuffix: "grade",
                                },
                                // 受信後のコールバック
                                (data) => {
                                    // データをセット
                                    this.selectGetItemGrade = data.gradeList;
                                }
                            );
                        })
                        .catch(AjaxCom.fail);
                },
                // 種別プルダウン選択時、定期考査リストまたは学期リストを取得
                selectChangeGetExam: function (event) {
                    AjaxCom.getPromise()
                        .then(() => {
                            // 初期化
                            this.selectGetItemExam = {};
                            this.form.exam_cd = "";
                            // チェンジイベントを発生させる
                            var examType = this.form.exam_type;
                            self.selectChangeGetCallBack(
                                this,
                                examType,
                                // URLを分けた
                                {
                                    urlSuffix: "exam",
                                },
                                // 受信後のコールバック
                                (data) => {
                                    // データをセット
                                    this.selectGetItemExam = data.examList;
                                }
                            );
                        })
                        .catch(AjaxCom.fail);
                },
            },
        });

        // Vue: モーダル(一覧出力)
        this.getVueModal({
            // exec送信時に、検索フォームを送信する
            // 検索条件を返す関数を指定する。
            addSendData: searchForm.getAfterSearchCond,

            // 完了処理後
            afterExec: () => {
                // CSVのダウンロードなので不要
            },
        });
    }
}
