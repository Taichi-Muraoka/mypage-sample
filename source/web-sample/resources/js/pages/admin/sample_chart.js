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
        this.getVueInputForm({
            vueData: {
                chart: null,
                // canvasのID
                canvasId: "tempChart",
            },
            vueMounted: function ($vue, option) {
                //月別の場合
                //$vue.chart = new MonthlyChart();
                //日別の場合
                $vue.chart = new DailyChart();
                $vue.chart.create($vue.canvasId, $vue.form);
            },
        });
    }
}
