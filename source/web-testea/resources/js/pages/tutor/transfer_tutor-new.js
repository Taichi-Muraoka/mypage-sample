"use strict";

/*
 * 振替調整登録
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
            UrlCom.redirect(UrlCom.getFuncUrl());
        };

        // 日付ピッカーイベント
        var datepickerOnChange = ($vue, id, value) => {
            var no = (id.replace('preferred_date', '')).replace('_calender', '');
            // 初期化
            switch (no) {
                case "1":
                    $vue.selectGetItemPeriod1 = {};
                    break;
                case "2":
                    $vue.selectGetItemPeriod2 = {};
                    break;
                case "3":
                    $vue.selectGetItemPeriod3 = {};
                    break;
            }
            var campusCd = $vue.form.campus_cd;
            var targetDate = value;
            // 時限プルダウンは動的に変わるので、一旦クリアする
            $vue.form.period_no = "";
            $vue.form['preferred_date' + no + '_period'] = "";
            // チェンジイベントを発生させる
            self.selectChangeGetCallBack(
                $vue,
                {
                    campus_cd: campusCd,
                    target_date: targetDate,
                },
                // $vue.option,
                // URLを分けた
                {
                    urlSuffix: "calender",
                },
                // 受信後のコールバック
                (data) => {
                    // データをセット
                    switch (no) {
                        case "1":
                            $vue.selectGetItemPeriod1 = data.periods;
                            break;
                        case "2":
                            $vue.selectGetItemPeriod2 = data.periods;
                            break;
                        case "3":
                            $vue.selectGetItemPeriod3 = data.periods;
                            break;
                    }
                }
            );
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterNew: afterNew,
            datepickerOnChange: datepickerOnChange,

            vueData: {
                // プルダウン変更用のプロパティを用意
                selectGetItemSchedule: {},
                selectGetItemPeriod1: {},
                selectGetItemPeriod2: {},
                selectGetItemPeriod3: {}
            },
            vueMethods: {
                // 生徒プルダウン変更イベント
                selectChangeStudent: function (event) {
                    // 未選択となった場合は項目リセット
                    if (ValueCom.isEmpty(this.form['student_id'])) {
                        document.getElementById('campus_name').innerText = "";
                        document.getElementById('course_name').innerText = "";
                        document.getElementById('subject_name').innerText = "";
                        document.getElementById('preferred_range').innerText = "";
                        document.getElementById('monthly_message').innerHTML = "";

                        this.form.schedule_id = "";
                        this.form.campus_cd = "";
                        this.form.course_cd = "";
                        this.form.subject_cd = "";
                        this.form.monthly_count = "";

                        for (var i = 1; i <= 3; i++) {
                            // 希望日のフォームをクリア
                            this.form['preferred_date' + i + '_calender'] = "";
                            this.form['preferred_date' + i + '_period'] = "";
                            // datepickerのinputをクリア
                            $('#_preferred_date' + i + '_calender').val("");
                        }
                    }

                    AjaxCom.getPromise()
                        .then(() => {
                            // 初期化
                            document.getElementById('campus_name').innerText = "";
                            document.getElementById('course_name').innerText = "";
                            document.getElementById('subject_name').innerText = "";
                            document.getElementById('preferred_range').innerText = "";
                            document.getElementById('monthly_message').innerHTML = "";

                            this.form.schedule_id = "";
                            this.form.campus_cd = "";
                            this.form.course_cd = "";
                            this.form.subject_cd = "";
                            this.form.monthly_count = "";

                            for (var i = 1; i <= 3; i++) {
                                // 希望日のフォームをクリア
                                this.form['preferred_date' + i + '_calender'] = "";
                                this.form['preferred_date' + i + '_period'] = "";
                                // datepickerのinputをクリア
                                $('#_preferred_date' + i + '_calender').val("");
                            }

                            // チェンジイベントを発生させる
                            var selected = this.form.student_id;
                            self.selectChangeGetCallBack(
                                this,
                                selected,
                                // URLを分けた
                                {
                                    urlSuffix: "student",
                                },
                                // 受信後のコールバック
                                (data) => {
                                    // データをセット
                                    this.selectGetItemSchedule = data.lessons;
                                    this.form.monthly_count = data.monthly_count;
                                    document.getElementById('monthly_message').innerHTML = "今月 <b>" + data.monthly_count + "</b> 回目の振替調整です。管理者承認が発生します。";
                                }
                            );
                        })
                        .catch(AjaxCom.fail);
                },
                // 授業日・時限プルダウン変更イベント
                selectChangeSchedule: function (event) {
                    // 未選択となった場合は項目リセット
                    if (ValueCom.isEmpty(this.form['schedule_id'])) {
                        document.getElementById('campus_name').innerText = "";
                        document.getElementById('course_name').innerText = "";
                        document.getElementById('subject_name').innerText = "";
                        document.getElementById('preferred_range').innerText = "";

                        this.form.campus_cd = "";
                        this.form.course_cd = "";
                        this.form.subject_cd = "";

                        for (var i = 1; i <= 3; i++) {
                            // 希望日のフォームをクリア
                            this.form['preferred_date' + i + '_calender'] = "";
                            this.form['preferred_date' + i + '_period'] = "";
                            // datepickerのinputをクリア
                            $('#_preferred_date' + i + '_calender').val("");
                        }
                    }

                    AjaxCom.getPromise()
                        .then(() => {
                            // 初期化
                            document.getElementById('campus_name').innerText = "";
                            document.getElementById('course_name').innerText = "";
                            document.getElementById('subject_name').innerText = "";
                            document.getElementById('preferred_range').innerText = "";

                            this.form.campus_cd = "";
                            this.form.course_cd = "";
                            this.form.subject_cd = "";

                            for (var i = 1; i <= 3; i++) {
                                // 希望日のフォームをクリア
                                this.form['preferred_date' + i + '_calender'] = "";
                                this.form['preferred_date' + i + '_period'] = "";
                                // datepickerのinputをクリア
                                $('#_preferred_date' + i + '_calender').val("");
                            }

                            // チェンジイベントを発生させる
                            var selected = this.form.schedule_id;
                            self.selectChangeGetCallBack(
                                this,
                                selected,
                                // URLを分けた
                                {
                                    urlSuffix: "schedule",
                                },
                                // 受信後のコールバック
                                (data) => {
                                    // データをセット
                                    this.selectGetItemFreeSchedule = data.candidates;

                                    document.getElementById('campus_name').innerText = data.campus_name;
                                    document.getElementById('course_name').innerText = data.course_name;
                                    document.getElementById('subject_name').innerText = data.subject_name;
                                    document.getElementById('preferred_range').innerText = "振替希望日は " + data.preferred_from + " ～ " + data.preferred_to + " の範囲で指定してください。";
                                    this.form.campus_cd = data.campus_cd;
                                    this.form.course_cd = data.course_cd;
                                    this.form.subject_cd = data.subject_cd;
                                }
                            );
                        })
                        .catch(AjaxCom.fail);
                },
            }
        });
    }
}
