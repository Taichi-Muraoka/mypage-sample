"use strict";

/**
 * 月別チャート
 */
export default class MonthlyChart {

    _chart = null;
    // canvasのID
    _canvasId = null;

    /**
     * コンストラクタ
     */
    constructor() {
    }

    /*
     * 作成
     */
    create(canvasId, formData) {
        this._canvasId = canvasId;
        const config = function(datas) {
            // 仮データ
            const sampleData = [
                { term: "2025/03/01", temp: 1.0 },
                { term: "2025/03/02", temp: 1.1 },
                { term: "2025/03/03", temp: 1.2 },
                { term: "2025/03/04", temp: 1.0 },
                { term: "2025/03/05", temp: 1.3 },
                { term: "2025/03/06", temp: 1.2 },
                { term: "2025/03/07", temp: 1.1 },
                { term: "2025/03/08", temp: 1.3 },
                { term: "2025/03/09", temp: 1.5 },
                { term: "2025/03/10", temp: 1.2 },
            ]
            // datas に サーバ側からのデータが入ってくる想定だが
            // 仮で上記のサンプルデータに置き換え
            datas.data = sampleData;
            datas.deviceName = '冷蔵庫2';

            // y軸：温度
            let temps = datas.data.map(x => x.temp);
            // x軸：期間
            let terms = datas.data.map(x => x.term);
            // 機器名
            let device = datas.deviceName;
            return {
                type: "line",
                data: {
                    labels: terms,
                    datasets:[
                        {
                            label: device,
                            borderColor:"rgba(0,0,255,1)",
                            pointBackgroundColor:"rgba(0,0,255,1)",
                            lineTension: 0,
                            data: temps
                        }
                    ],
                },
                options: {
                    plugins: {
                        //title: {
                        //    display: true,
                        //    text: '温度グラフ表示',
                        //    font: {size: 20}
                        //}
                    },
                    scales: {
                        y: {
                            suggestedMax: 3.0,
                            suggestedMin: 0.0,
                            ticks: {
                                stepSize: 0.1,
                                callback: value => `${value} ℃`
                            },
                            title: {
                                display: true,
                                text: '温度'
                            }
                        },
                        x: {
                            //ticks: {
                            //    callback: value => `${value} 時`
                            //},
                            title: {
                                display: true,
                                text: '期間'
                            }
                        }
                    },
                }
            }
        };
        // データを取得
        let url = window.location.href + "/monthly_graph";
        axios.post(url, formData)
            .then(response => {
                const chartDatas = response.data;
                // Chartを描画
                const ctx = document.getElementById(this._canvasId);
                if (this._chart) {
                    // チャートが存在していれば初期化
                    this._chart.destroy();
                    this._chart = null;
                }
                this._chart = new Chart(ctx, config(chartDatas));
            })
            .catch(AjaxCom.fail);
    }
}
