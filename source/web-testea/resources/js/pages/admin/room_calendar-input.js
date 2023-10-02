"use strict";

/*
 * 教室カレンダー登録・編集
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
            //UrlCom.redirect(UrlCom.getFuncUrl());
            // 教室カレンダー表示ができるまでの暫定表示
            UrlCom.redirect(appInfo.root);
        };

        // 日付ピッカーイベント
        var datepickerOnChange = (id, value) => {
            console.log(id, value);
            //AjaxCom.getPromise()
            //.then(() => {
            //    // 初期化
            //    this.selectGetItemDate = {};
            //    var campusCd = this.form.campus_cd;
            //    var targetDate = this.form._target_date;
            //    var periodNo = this.form.period_no_bef;
            //    // 時限プルダウンは動的に変わるので、一旦クリアする
            //    //this.form.period_no = '';
            //    // チェンジイベントを発生させる
            //    self.selectChangeGetCallBack(
            //        this,
            //        {
            //            campus_cd: campusCd,
            //            target_date: targetDate,
            //            period_no: periodNo
            //        },
            //        this.option,
            //        // 受信後のコールバック
            //        (data) => {
            //            // データをセット
            //            this.selectGetItemDate = data;
            //            // 時限リストが取得できた場合のみ、時限(selected)をセット
            //            if (data.selectItems.length != 0) {
            //                this.form.period_no = data.period_no;
            //            }
            //        }
            //    );
            //})
            //.catch(AjaxCom.fail);
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            datepickerOnChange: datepickerOnChange,
            vueData: {
                // コースプルダウン変更用のプロパティを用意
                selectGetItemCourse: {},
                // 時限プルダウン変更用のプロパティを用意
                selectGetItemTimetable: {},
                // 日付変更用のプロパティを用意
                selectGetItemDate: {},
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
                            // 初期化
                            this.selectGetItemTimetable = {};
                            //this.form.period_no = "";
                            // チェンジイベントを発生させる
                            var campusCd = this.form.campus_cd;
                            var targetDate = this.form.target_date;
                            var periodNo = this.form.period_no;
                            self.selectChangeGetCallBack(
                                this,
                                {
                                    campus_cd: campusCd,
                                    target_date: targetDate,
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
                                    this.form.period_no_bef = this.form.period_no;
                                }
                            );
                        })
                        .catch(AjaxCom.fail);
                },
            },
            vueMounted: function($vue, option) {
                // プルダウンが動的になるので、退避したものをセットする
                $vue.form.period_no = $vue.form.period_no_bef;

                // 編集時、プルダウンチェンジイベントを発生させる。
                //$vue.selectChangeGetDate();
            }
        });
    }
}
