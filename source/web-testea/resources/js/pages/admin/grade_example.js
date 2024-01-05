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
            // 検索完了後の実行ボタン制御あり
            afterSearchBtnListExec: true,
            // SearchList側に追加するvueData
            vueSearchListData: {
                // CSV出力ボタンの非活性状態（初期表示時disable）
                disabledBtnListExec: true,
            },
            vueData: {
                // 学校区分プルダウン変更用のプロパティを用意
                selectGetItemGrade: {},
                // 種別プルダウン変更用のプロパティを用意
                selectGetItemExam: {},
            },
            vueMethods: {
                // 学校区分プルダウン選択時、学年リストを取得
                selectChangeGetGrade: function (event) {
                    // 初期化
                    this.selectGetItemGrade = {};
                    this.form.grade_cd = "";
                    // チェンジイベントを発生させる
                    var schoolKind = this.form.school_kind;
                    self.selectChangeGet2(
                        this,
                        schoolKind,
                        // URLを分けた
                        {
                            urlSuffix: "grade",
                        },
                        // vueData指定
                        'selectGetItemGrade',
                    );
                },
                // 種別プルダウン選択時、定期考査リストまたは学期リストを取得
                selectChangeGetExam: function (event) {
                    // 初期化
                    this.selectGetItemExam = {};
                    this.form.exam_cd = "";
                    // チェンジイベントを発生させる
                    var examType = this.form.exam_type;
                    self.selectChangeGet2(
                        this,
                        examType,
                        // URLを分けた
                        {
                            urlSuffix: "exam",
                        },
                        // vueData指定
                        'selectGetItemExam',
                    );
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
