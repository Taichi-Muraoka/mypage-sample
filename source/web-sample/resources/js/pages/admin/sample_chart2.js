"use strict";

import DailyChart from "../../chart/daily-chart";
import MonthlyChart from "../../chart/monthly-chart";
//import HourlyChart from "../../chart/hourly-chart";

/*
 * サンプル一覧
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

        // グラフ表示
        let dispChart = ($vue) => {
            if ($vue.form.disp_mode !== $vue.form.bef_disp_mode) {
                if ($vue.chart && $vue.chart._chart) {
                    // 既存のチャートインスタンスがあるなら破棄
                    $vue.chart._chart.destroy();
                }
                // チャートインスタンス生成（新規または切り替え時）
                if ($vue.form.disp_mode == 1) {
                    //月別の場合
                    $vue.chart = new MonthlyChart();
                } else {
                    //日別の場合
                    $vue.chart = new DailyChart();
                }
                $vue.form.bef_disp_mode = $vue.form.disp_mode;
            }
            // 描画処理
            $vue.chart.create($vue.canvasId, $vue.form);
        };

        // Vue: 検索フォーム
        this.getVueSearchForm({
            //initSearch: false, // 初期化時に検索しない
            afterSearchAddFunc: true, // 検索後処理を追加する
            afterSearchExec: dispChart, // 検索後処理を指定
            vueData: {
                chart: null,
                // canvasのID
                canvasId: "tempChart",
            },
            vueMounted: function ($vue, option) {
                $vue.form.bef_disp_mode = "";
            },
        });
    }
}
