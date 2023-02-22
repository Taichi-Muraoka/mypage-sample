"use strict";

/*
 * カレンダー描画クラス
 */
export default class CalendarCom {
    /**
     * カレンダー生成
     *
     * @param data
     */
    static create(event, eventClick) {
        // 固定にした
        var calendarId = "calendar";

        var Calendar = FullCalendar.Calendar;
        var calendarEl = document.getElementById(calendarId);

        var calendar = new Calendar(calendarEl, {
            // v5対応
            initialView: "dayGridMonth",
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: "dayGridMonth,timeGridWeek,timeGridDay"
            },
            themeSystem: "bootstrap",
            locale: "ja",
            height: 700,
            // v5対応
            //plugins: ["bootstrap", "interaction", "dayGrid", "timeGrid"],
            selectable: false,
            selectMirror: false,
            navLinks: true,
            // v5対応
            //eventRender: function(info) {
                // ツールチップの表示も可能
                // $(info.el).tooltip({
                //     title: info.event.extendedProps.detail
                // });
            //},
            // データの読み込み処理。呼び出し元で定義する
            events: event,
            // クリックイベント
            eventClick: eventClick,
            editable: false,
            // v5対応（定義追加）
            eventDisplay: "block",
            // // 12aが表示されるのを防ぐ
            // timeFormat: "H(:mm)"
            // v5対応
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            slotMinTime: '08:00:00',
            slotMaxTime: '23:00:00',
            eventTextColor: "white",
        });

        calendar.render();
    }

    static createForRoom(initDate, eventFunc, eventClick, selectFunc) {
        // 固定にした
        var calendarId = "calendar";

        var Calendar = FullCalendar.Calendar;
        var calendarEl = document.getElementById(calendarId);

        var calendar = new Calendar(calendarEl, {
            initialView: "resourceTimeGridDay",
            initialDate: initDate,
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: ""
            },
            themeSystem: "bootstrap",
            locale: "ja",
            height: 700,
            dayMinWidth: 150,
            selectable: false,
            selectMirror: false,
            navLinks: true,
            // v5対応
            //eventRender: function(info) {
                // ツールチップの表示も可能
                // $(info.el).tooltip({
                //     title: info.event.extendedProps.detail
                // });
            //},
            // 左側のリソースの一覧
            resources: [
                { id: "000", title: "時間割" },
                { id: "001", title: "Aテーブル"},
                { id: "002", title: "Bテーブル"},
                { id: "003", title: "Cテーブル"},
                { id: "004", title: "Dテーブル"},
                { id: "005", title: "Eテーブル"},
                { id: "006", title: "Fテーブル"},
                { id: "007", title: "Gテーブル"},
                { id: "008", title: "Hテーブル"},
                { id: "009", title: "Iテーブル"},
                { id: "010", title: "Jテーブル"},
                { id: "999", title: "後日振替"},
            ],
            // データの読み込み処理。呼び出し元で定義する
            events: eventFunc,
            // クリックイベント。呼び出し元で定義する
            eventClick: eventClick,
            //editable: false,
            selectable: true,
            select: selectFunc,
            eventDisplay: "block",
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            slotMinTime: '08:00:00',
            slotMaxTime: '23:00:00',
            allDaySlot: false,
            slotDuration: '00:15:00',
            slotLabelFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            eventContent: function( info ) {
                return {html: info.event.title};
            },
            eventTextColor: "white",
        });

        calendar.render();
    }

    static createForDefaultWeek(n, curDate, eventFunc, eventClick, selectFunc) {
        // 固定にした
        var calendarId = "calendar" + n;

        var Calendar = FullCalendar.Calendar;
        var calendarEl = document.getElementById(calendarId);

        var calendar = new Calendar(calendarEl, {
            initialView: "resourceTimeGridDay",
            initialDate: curDate,
            headerToolbar: {
                left: "",
                center: "title",
                right: ""
            },
            // タイトルの書式
            titleFormat: function(date) {
                const weekNum = date.date.marker.getDay();
                const week = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][weekNum];
                return week;
            },
            themeSystem: "bootstrap",
            locale: "ja",
            height: 700,
            dayMinWidth: 150,
            selectable: false,
            selectMirror: false,
            navLinks: true,
            // 左側のリソースの一覧
            resources: [
                { id: "000", title: "時間割" },
                { id: "001", title: "Aテーブル"},
                { id: "002", title: "Bテーブル"},
                { id: "003", title: "Cテーブル"},
                { id: "004", title: "Dテーブル"},
                { id: "005", title: "Eテーブル"},
                { id: "006", title: "Fテーブル"},
                { id: "007", title: "Gテーブル"},
                { id: "008", title: "Hテーブル"},
                { id: "009", title: "Iテーブル"},
                { id: "010", title: "Jテーブル"},
                //{ id: "999", title: "後日振替"},
            ],
            // データの読み込み処理。呼び出し元で定義する
            events: eventFunc,
            // クリックイベント。呼び出し元で定義する
            eventClick: eventClick,
            //editable: false,
            selectable: true,
            select: selectFunc,
            eventDisplay: "block",
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            slotMinTime: '08:00:00',
            slotMaxTime: '23:00:00',
            allDaySlot: false,
            slotDuration: '00:15:00',
            slotLabelFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            eventContent: function( info ) {
                return {html: info.event.title};
            },
            eventTextColor: "white",
        });

        calendar.render();
    }

}
