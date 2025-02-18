"use strict";

/*
 * 振替スケジュール登録
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

        // 日付ピッカーイベント
        var datepickerOnChange = ($vue, id, value) => {
            // 初期化
            $vue.selectGetItemPeriods = {};
            var campusCd = $vue.form.campus_cd;
            var targetDate = value;
            // 時限プルダウンは動的に変わるので、選択値(selected)を退避し一旦クリアする
            $vue.form.period_no_bef = $vue.form.period_no;
            $vue.form.period_no = "";
            // チェンジイベントを発生させる
            self.selectChangeGetCallBack2(
                $vue,
                {
                    campus_cd: campusCd,
                    target_date: targetDate,
                },
                // URLを分けた
                {
                    urlSuffix: "calender",
                },
                // vueData指定
                'selectGetItemPeriods',
                // 受信後のコールバック
                (data) => {
                    // データをセット
                    $vue.selectGetItemPeriods = data.periods;
                    if (data.periods.length != 0 && data.periods.some(item => item.code == $vue.form.period_no_bef))  {
                        // 時限リストが取得できた場合 かつ 時限リストに存在する場合のみ
                        // 退避した時限(selected)をセット
                        $vue.form.period_no = $vue.form.period_no_bef;
                    } else {
                        // 退避した時限(selected)をセットできない場合、開始時刻もクリアする
                        $vue.form.period_no_bef = "";
                        $vue.form.start_time = "";
                    }
                }
            );
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            datepickerOnChange: datepickerOnChange,
            vueData: {
                // 振替日変更時 時限プルダウン用のプロパティを用意
                selectGetItemPeriods: {},
                // 生徒変更時 授業日プルダウン変更用のプロパティを用意
                selectGetItemSchedules: {},
            },
            vueMethods: {
                // 授業情報変更時の項目クリア処理
                lessonListReset: function () {
                    this.selectGetItem = {};
                    this.selectGetItemPeriods = {};
                    this.form.campus_cd = "";
                    this.form.target_date = "";
                    // datepickerのinputをクリア
                    $("#_target_date").val("");
                    this.form.period_no = "";
                    this.form.start_time = "";
                    this.form.change_tid = "";
                },
                // 生徒プルダウン変更イベント
                selectChangeStudent: function (event) {
                    // 初期化
                    this.selectGetItemSchedules = {};
                    this.form.schedule_id = "";
                    this.lessonListReset();

                    // チェンジイベントを発生させる
                    var selected = this.form.student_id;
                    self.selectChangeGet2(
                        this,
                        selected,
                        // URLを分けた
                        {
                            urlSuffix: "student",
                        },
                        // vueData指定
                        'selectGetItemSchedules',
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
