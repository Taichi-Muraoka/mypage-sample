"use strict";

/*
 * レギュラースケジュール登録・編集
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
                // コースプルダウン変更用のプロパティを用意
                selectGetItemCourse: {},
                // 時限プルダウン変更用のプロパティを用意
                selectGetItemTimetable: {},
            },
            vueMethods: {
                // コースプルダウン変更
                selectChangeGetCourse: function (event) {
                    AjaxCom.getPromise()
                        .then(() => {
                            // 初期化
                            this.selectGetItemCourse = {};
                            this.form.course_kind = "";
                            // チェンジイベントを発生させる
                            var selected = this.form.course_cd;
                            self.selectChangeGetCallBack(
                                this,
                                selected,
                                // URLを分けた
                                {
                                    urlSuffix: "course"
                                },
                                // 受信後のコールバック
                                (data) => {
                                    // データをセット
                                    this.selectGetItemCourse = data;
                                    this.form.course_kind = data.course_kind;
                                }
                            );
                        })
                        .catch(AjaxCom.fail);
                },
                // 時限プルダウン変更
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
                                    period_no: periodNo
                                },
                                // URLを分けた
                                {
                                    urlSuffix: "timetable"
                                },
                                // 受信後のコールバック
                                (data) => {
                                    // データをセット
                                    this.selectGetItemTimetable = data;
                                    // 取得した開始時刻・終了時刻をセット
                                    this.form.start_time = data.start_time;
                                    this.form.end_time = data.end_time;
                                    // 選択された時限をhiddenにセット
                                    //this.form.period_no_bef = this.form.period_no;
                                }
                            );
                        })
                        .catch(AjaxCom.fail);
                },
            },
        });
    }
}
