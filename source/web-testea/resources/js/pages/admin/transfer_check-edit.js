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
                    $vue.selectGetItemDate = data.periods;
                    // 時限リストが取得できた場合のみ、時限(selected)をセット
                    //if (data.selectItems.length != 0) {
                    //    $vue.form.period_no = $vue.form.period_no_bef;
                    //}
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
                // 日付変更用のプロパティを用意
                selectGetItemDate: {},
            },
        });
    }
}
