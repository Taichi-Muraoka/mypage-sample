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
            var no = id.replace("preferred_date", "").replace("_calender", "");
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
            // 時限プルダウンは動的に変わるので、選択値(selected)を退避し一旦クリアする
            $vue.form['preferred_date' + no + '_period_bef'] = $vue.form['preferred_date' + no + '_period'];
            $vue.form['preferred_date' + no + '_period'] = "";
            // チェンジイベントを発生させる
            self.selectChangeGetCallBack(
                $vue,
                {
                    campus_cd: campusCd,
                    target_date: targetDate,
                },
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
                    if (data.periods.length != 0
                        && data.periods.some(item => item.code == $vue.form['preferred_date' + no + '_period_bef']))  {
                        // 時限リストが取得できた場合 かつ 時限リストに存在する場合のみ
                        // 退避した時限(selected)をセット
                        $vue.form['preferred_date' + no + '_period'] = $vue.form['preferred_date' + no + '_period_bef'];
                    } else {
                        // 退避した時限(selected)をセットできない場合、退避した時限をクリアする
                        $vue.form['preferred_date' + no + '_period_bef'] = "";
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
                selectGetItemPeriod3: {},
            },
            vueMethods: {
                // 授業情報変更時の項目クリア処理
                lessonListReset: function () {
                    this.selectGetItem = {};
                    this.selectGetItemPeriod1 = {};
                    this.selectGetItemPeriod2 = {};
                    this.selectGetItemPeriod3 = {};
                    this.form.campus_cd = "";
                    for (var i = 1; i <= 3; i++) {
                        // 希望日のフォームをクリア
                        this.form["preferred_date" + i + "_calender"] = "";
                        this.form["preferred_date" + i + "_period"] = "";
                        // datepickerのinputをクリア
                        $("#_preferred_date" + i + "_calender").val("");
                    }
                },
                // 生徒プルダウン変更イベント
                selectChangeStudent: function (event) {
                    // 初期化
                    this.selectGetItemSchedules = {};
                    this.form.schedule_id = "";
                    this.form.campus_cd = "";
                    this.form.monthly_count = "";
                    this.lessonListReset();

                    // チェンジイベントを発生させる
                    var selected = this.form.student_id;
                    self.selectChangeGetCallBack2(
                        this,
                        selected,
                        // URLを分けた
                        {
                            urlSuffix: "student",
                        },
                        // vueData指定
                        'selectGetItemSchedules',
                        // 受信後のコールバック
                        (data) => {
                            // データをセット
                            this.selectGetItemSchedule = data;
                            this.form.monthly_count = data.monthly_count;
                        }
                    );
                },
                // 授業日・時限プルダウン変更イベント
                selectChangeSchedule: function (event) {
                    // 初期化
                    this.lessonListReset();

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
                            this.selectGetItem = data;
                            this.form.campus_cd = data.campus_cd;
                        }
                    );
                },
            },
        });
    }
}
