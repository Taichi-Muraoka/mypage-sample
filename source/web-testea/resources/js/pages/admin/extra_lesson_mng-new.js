"use strict";

/*
 * 追加授業スケジュール登録
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
                // 時限プルダウン変更用のプロパティを用意
                selectGetItemTimetable: {},
            },
            vueMethods: {
                // 時限プルダウン変更 開始時刻・終了時刻を取得
                selectChangeGetTimetable: function (event) {
                    AjaxCom.getPromise()
                        .then(() => {
                            // 時限が未選択の場合はスキップ
                            if (ValueCom.isEmpty(this.form.period_no)) {
                                return AjaxCom.exit();
                            }
                        })
                        .then(() => {
                            // 初期化
                            this.selectGetItemTimetable = {};
                            // チェンジイベントを発生させる
                            var campusCd = this.form.campus_cd;
                            var periodNo = this.form.period_no;
                            self.selectChangeGetCallBack(
                                this,
                                {
                                    campus_cd: campusCd,
                                    period_no: periodNo,
                                },
                                // URLを分けた
                                {
                                    urlSuffix: "timetable",
                                },
                                // 受信後のコールバック
                                (data) => {
                                    // データをセット
                                    this.selectGetItemTimetable = data;
                                    // 取得した開始時刻・終了時刻をセット
                                    this.form.start_time = data.start_time;
                                    this.form.end_time = data.end_time;
                                }
                            );
                        })
                        .catch(AjaxCom.fail);
                },
            },
        });
    }
}
