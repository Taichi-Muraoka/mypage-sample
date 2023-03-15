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
            customButtons: {
                datePickerButton: {
                    text: 'datepicker',
                    click: function () {
    
                        var $btnCustom = $('.fc-datePickerButton-button'); // name of custom  button in the generated code
                        $btnCustom.after('<input type="hidden" id="hiddenDate" class="datepicker"/>');
    
                        $("#hiddenDate").datepicker({
                            showOn: "button",
    
                            dateFormat:"yy-mm-dd",
                            onSelect: function (dateText, inst) {
                                $('#calendar').fullCalendar('gotoDate', dateText);
                            },
                        });
    
                        var $btnDatepicker = $(".ui-datepicker-trigger"); // name of the generated datepicker UI 
                        //Below are required for manipulating dynamically created datepicker on custom button click
                        $("#hiddenDate").show().focus().hide();
                        $btnDatepicker.trigger("click"); //dynamically generated button for datepicker when clicked on input textbox
                        $btnDatepicker.hide();
                        $btnDatepicker.remove();
                        $("input.datepicker").not(":first").remove();//dynamically appended every time on custom button click
    
                    }
                }
            },
            headerToolbar: {
                left: "prev,next today",
                center: "title",
                right: ""
                //right: "datePickerButton,timeGridWeek,resourceTimeGridDay"
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
                { id: "005", title: "E教室"},
                { id: "800", title: "面談ブース"},
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
