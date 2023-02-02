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
        });

        calendar.render();
    }
}
