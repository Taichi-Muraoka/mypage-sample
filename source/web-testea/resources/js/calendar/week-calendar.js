"use strict";

import PageModal from "../page-components/page-modal";

/*
 * defaultWeekカレンダー
 */
export default class WeekCalendar {
    // private: モーダル
    _vueModal = null;

    // カレンダー(一週間分配列で保持)
    _calendar = [];

    /**
     * コンストラクタ
     */
    constructor() {
        // モーダル
        const pageModal = new PageModal();
        this._vueModal = pageModal.getVueApp({ useShowEvent: false });
    }

    /*
     * 一週間分作成
     */
    createWeek() {
        // モック用に仮の日付を設定（日曜にする）
        var curDate = new Date("2000/01/02");

        for (var i = 1; i < 7; i++) {
            curDate.setDate(curDate.getDate() + 1);
            const calendar = this.create(i, curDate);
            this._calendar.push(calendar);
        }
    }

    /*
     * 作成
     */
    create(idx, curDate) {
        var calendarId = "calendar" + idx;
        var Calendar = FullCalendar.Calendar;
        var calendarEl = document.getElementById(calendarId);
        const self = this;

        var calendar = new Calendar(calendarEl, {
            initialView: "resourceTimeGridDay",
            initialDate: curDate,
            customButtons: {
                topLinkButton: {
                    text: "上部に戻る",
                    click: this._topLinkButtonClick,
                },
            },
            headerToolbar: {
                left: "",
                center: "title",
                right: "topLinkButton",
            },
            // タイトルの書式
            titleFormat: function (date) {
                const weekNum = date.date.marker.getDay();
                const week = [
                    "Sunday",
                    "Monday",
                    "Tuesday",
                    "Wednesday",
                    "Thursday",
                    "Friday",
                    "Saturday",
                ][weekNum];
                return week;
            },
            locale: "ja",
            //height: 1700,
            contentHeight: "auto",
            stickyFooterScrollbar: true,
            stickyHeaderDates: true,
            dayMinWidth: 150,
            selectable: false,
            selectMirror: false,
            navLinks: true,
            // リソース（ブース）の読み込み処理
            resources: this._resourceFunc,
            // データの読み込み処理
            events: function (info, successCallback, failureCallback) {
                self._eventFunc(idx, info, successCallback, failureCallback);
            },
            // クリックイベント
            eventClick: this._eventClick,
            //editable: false,
            selectable: true,
            select: this._selectFunc,
            eventDisplay: "block",
            eventTimeFormat: {
                hour: "2-digit",
                minute: "2-digit",
                meridiem: false,
            },
            slotMinTime: "08:00:00",
            slotMaxTime: "23:00:00",
            allDaySlot: false,
            slotDuration: "00:15:00",
            slotLabelFormat: {
                hour: "2-digit",
                minute: "2-digit",
                meridiem: false,
            },
            eventContent: function (info) {
                return { html: info.event.title };
            },
            eventTextColor: "white",
        });

        calendar.render();

        return calendar;
    }

    /*
     * 再描画
     */
    refetchEvents() {
        // 全曜日分を配列で描画
        for (var i = 0; i < 6; i++) {
            this._calendar[i].refetchResources();
            this._calendar[i].refetchEvents();
        }
    }

    /**
     * リソース表示
     *
     * @param info
     * @param successCallback
     * @param failureCallback
     */
    _resourceFunc = (info, successCallback, failureCallback) => {
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
                });
                //console.log(sendData);

                // 詳細データを取得
                var url = UrlCom.getFuncUrl() + "/get_booth";
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
     * イベント表示
     *
     * @param idx
     * @param info
     * @param successCallback
     * @param failureCallback
     */
    _eventFunc = (idx, info, successCallback, failureCallback) => {
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
                    day: idx,
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
        // 時間割のスケジュールはモーダル表示しない
        if (e.event._def.resourceIds[0] !== "000") {
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
        }
    };

    /**
     * Viewエリアクリック
     *
     * @param info
     */
    _selectFunc = (info) => {
        // カレンダーのカードタグのID
        var cardId = "#card-calendar";
        var formData = FormCom.getFormArrayData(cardId);
        if (info.resource._resource.id !== "000") {
            // 詳細データを取得
            var url =
                UrlCom.getFuncUrl() +
                "/new" +
                "/" +
                formData.campus_cd +
                "/" +
                moment(info.start).format("d") +
                moment(info.start).format("HHmm") +
                "/" +
                info.resource._resource.id;
            location.href = url;
        }
    };

    /**
     * ボタンクリックイベント
     */
    _topLinkButtonClick = (e) => {
        location.href = "#top";
    };
}
