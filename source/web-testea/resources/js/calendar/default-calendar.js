"use strict";

import PageModal from "../page-components/page-modal";

/*
 * 標準カレンダー
 */
export default class DefaultCalendar {
    // private: モーダル
    _vueModal = null;

    // カレンダー
    _calendar = null;

    /**
     * コンストラクタ
     */
    constructor() {
        // モーダル
        const pageModal = new PageModal();
        this._vueModal = pageModal.getVueApp({ useShowEvent: false });
    }

    /*
     * 作成
     */
    create() {
        // 固定にした
        var calendarId = "calendar";

        var Calendar = FullCalendar.Calendar;
        var calendarEl = document.getElementById(calendarId);

        this._calendar = new Calendar(calendarEl, {
            // license key for premium features
            schedulerLicenseKey: '0477382314-fcs-1699842877',
            // v5対応
            initialView: "dayGridMonth",
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay",
            },
            buttonText: {
                today: "今日",
                month: "月",
                week: "週",
                day: "日",
            },
            // view毎のカスタマイズ
            views: {
                dayGridMonth: {
                    // 月カレンダーの日付表示変更（数字のみとする）
                    dayCellContent: function(arg){
                        return arg.date.getDate();
                    }
                },
                timeGridWeek: {
                    // 週カレンダーの日ヘッダ表示変更（曜日のみとする）
                    dayHeaderFormat: function (date) {
                        const day = date.date.day;
                        const weekNum = date.date.marker.getDay();
                        const week = ['日', '月', '火', '水', '木', '金', '土'][weekNum];
                        return week;
                    }
                }
            },
            // 終日スロットは非表示
            allDaySlot: false,
            locale: "ja",
            height: "auto",
            selectable: false,
            selectMirror: false,
            navLinks: true,
            // データの読み込み処理。呼び出し元で定義する
            events: this._eventFunc,
            // クリックイベント
            eventClick: this._eventClick,
            editable: false,
            // v5対応（定義追加）
            eventDisplay: "block",
            // v5対応
            eventTimeFormat: {
                hour: "2-digit",
                minute: "2-digit",
                meridiem: false,
            },
            slotMinTime: "08:00:00",
            slotMaxTime: "23:00:00",
            eventTextColor: "white",
        });

        this._calendar.render();
    }

    /**
     * 表示イベント
     *
     * @param info
     * @param successCallback
     * @param failureCallback
     */
    _eventFunc = (info, successCallback, failureCallback) => {
        // カレンダーのカードタグのID
        var cardId = "#card-calendar";

        $.when()
            .then(() => {
                // カードのローディング開始
                FormCom.loadingForCardOn(cardId);

                // カードカレンダーの中のHidden値を取得。会員管理のように子画面にカレンダーがある場合
                var formData = FormCom.getFormArrayData(cardId);

                // カレンダーの条件を送信
                var sendData = Object.assign(formData, {
                    start: info.start.valueOf(),
                    end: info.end.valueOf(),
                });

                // 詳細データを取得
                var url = UrlCom.getFuncUrl() + "/get_calendar";
                return axios.post(url, sendData);
            })
            .then((response) => {
                // コールバックで更新(eventプロパティにセットする)
                successCallback(response.data);

                // カードのローディング終了
                FormCom.loadingForCardOff(cardId);
            })
            .fail(AjaxCom.fail);
    };

    /**
     * イベントクリックイベント
     *
     * @param e
     */
    _eventClick = (e) => {
        // モーダルの中身を更新
        this._vueModal.item = Object.assign(
            {
                // ついでにIDも足しておく
                id: e.event._def.publicId,
            },
            // 送信データがe.event.extendedPropsに入ってくるのでそれを参照する
            e.event.extendedProps
        );

        // モーダルを開く
        this._vueModal.show();
    };
}
