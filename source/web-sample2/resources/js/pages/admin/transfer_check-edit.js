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
