"use strict";

/**
 * 日別チャート
 */
export default class DailyChart {

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
                { term: "0:00", temp: 1.0 },
                { term: "1:00", temp: 1.1 },
                { term: "2:00", temp: 1.2 },
                { term: "3:00", temp: 1.0 },
                { term: "4:00", temp: 1.3 },
                { term: "5:00", temp: 1.2 },
                { term: "6:00", temp: 1.1 },
                { term: "7:00", temp: 1.3 },
                { term: "8:00", temp: 1.5 },
                { term: "9:00", temp: 1.2 },
                { term: "10:00", temp: 2.0 },
                { term: "11:00", temp: 1.6 },
                { term: "12:00", temp: 1.4 },
                { term: "13:00", temp: 1.2 },
                { term: "14:00", temp: 1.1 },
                { term: "15:00", temp: 1.2 },
                { term: "16:00", temp: 1.0 },
                { term: "17:00", temp: 1.3 },
                { term: "18:00", temp: 1.2 },
                { term: "19:00", temp: 1.1 },
                { term: "20:00", temp: 1.3 },
                { term: "21:00", temp: 1.5 },
                { term: "22:00", temp: 1.2 },
                { term: "23:00", temp: 1.0 },
            ]
            // datas に サーバ側からのデータが入ってくる想定だが
            // 仮で上記のサンプルデータに置き換え
            datas.data = sampleData;
            datas.deviceName = '冷蔵庫1';

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
        let url = window.location.href + "/daily_graph";
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
