"use strict";

/*
 * 振替情報編集・スケジュール登録
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
            $vue.selectGetItemDate = {};
            var campusCd = $vue.form.campus_cd;
            var targetDate = value;
            // 時限プルダウンは動的に変わるので、一旦クリアする
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
                    $vue.form.start_time = "";
                }
            );
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            // vd_input_editとなるようにURL指定
            urlSuffix: "edit",
            datepickerOnChange: datepickerOnChange,
            vueData: {
                // 振替日変更時 時限プルダウン用のプロパティを用意
                selectGetItemPeriods: {},
            },
        });
    }
}
