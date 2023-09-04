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
            buttonText: {
                today:    '今日',
                month:    '月',
                week:     '週',
                day:      '日'
            },
            allDayText: "終日",
            //themeSystem: "bootstrap",
            locale: "ja",
            //height: 700,
            height: "auto",
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

    static createForRoom(initDate, eventFunc, eventClick, selectFunc, dateChangeFunc) {
        // 固定にした
        var calendarId = "calendar";

        var Calendar = FullCalendar.Calendar;
        var calendarEl = document.getElementById(calendarId);

        var calendar = new Calendar(calendarEl, {
            initialView: "resourceTimeGridDay",
            datesSet: dateChangeFunc,
            initialDate: initDate,
            customButtons: {
                datePickerButton: {
                    text: '日付選択',
                    click: function () {
                        //var currentDate = calendar.getDate();
                        //var newDate = moment(currentDate).add(7, 'days').format();
                        //calendar.gotoDate(newDate);

                        $(this).daterangepicker({
                                singleDatePicker: true,
                                locale: {
                                    format: "YYYY/MM/DD",
                                    applyLabel: "適用",
                                    cancelLabel: "キャンセル"
                                },
                                startDate: calendar.getDate(),
                                // カレンダーの範囲
                                minYear: new Date().getFullYear() - 2,
                                maxYear: new Date().getFullYear() + 5, // とりあえず5年後くらい
                                // 最初から自動で日付が入ってしまうので手動で格納
                                //autoUpdateInput: false,
                                // カレンダーのポップアップ位置を自動で調整
                                // 下の方にテキストボックスがあれば、カレンダーは上にポップアップされる
                                drops: "auto"
                            //}, function(start, end, label) {
                                //console.log(start);
                                //var currentDate = calendar.getDate();
                                //var newDate = moment(currentDate).add(7, 'days').format();
                                //calendar.gotoDate(newDate);
                        })
                        .on("apply.daterangepicker", function(ev, picker) {
                            // 適用ボタンクリックイベントで取得
                            var newDate = moment(picker.startDate).format();
                            calendar.gotoDate(newDate);
                        })
                        .on("cancel.daterangepicker", function(ev, picker) {
                            // キャンセルボタンはクリアとした
                            $(this).val("");
                        });

                        $(this).data('daterangepicker').show();
                    }
                }
            },
            headerToolbar: {
                left: "prev,next today",
                //left: "prev,next today datePickerButton",
                //left: "",
                center: "title",
                //right: ""
                //right: "datePickerButton timeGridWeek,resourceTimeGridDay"
                right: "datePickerButton"
            },
            buttonText: {
                today:    '今日',
                month:    '月',
                week:     '週',
                day:      '日'
            },
            //themeSystem: "bootstrap",
            locale: "ja",
            //height: 700,
            height: 1700,
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
                { id: "005", title: "E教室"},
                { id: "800", title: "面談ブース"},
                { id: "999", title: "未振替・振替中"},
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
            //themeSystem: "bootstrap",
            locale: "ja",
            //height: 700,
            height: 1700,
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
                { id: "005", title: "E教室"},
                //{ id: "800", title: "面談ブース"},
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
