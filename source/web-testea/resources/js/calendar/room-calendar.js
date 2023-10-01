"use strict";

import PageModal from "../page-components/page-modal";

/*
 * 教室カレンダー
 */
export default class RoomCalendar {
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
    create(initDateText) {
        var initDate;
        if (!ValueCom.isEmpty(initDateText)) {
            initDate = new Date(initDateText);
        }

        // ID
        var calendarId = "calendar";

        var Calendar = FullCalendar.Calendar;
        var calendarEl = document.getElementById(calendarId);

        this._calendar = new Calendar(calendarEl, {
            initialView: "resourceTimeGridDay",
            datesSet: this._dateChangeFunc,
            initialDate: initDate,
            customButtons: {
                datePickerButton: {
                    text: "日付選択",
                    click: this._datePickerButtonClick,
                },
            },
            headerToolbar: {
                left: "prev,next today",
                //left: "prev,next today datePickerButton",
                //left: "",
                center: "title",
                //right: ""
                //right: "datePickerButton timeGridWeek,resourceTimeGridDay"
                right: "datePickerButton",
            },
            buttonText: {
                today: "今日",
                month: "月",
                week: "週",
                day: "日",
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
                { id: "001", title: "Aテーブル" },
                { id: "002", title: "Bテーブル" },
                { id: "003", title: "Cテーブル" },
                { id: "004", title: "Dテーブル" },
                { id: "005", title: "E教室" },
                { id: "800", title: "面談ブース" },
                { id: "999", title: "未振替・振替中" },
            ],
            // データの読み込み処理
            events: this._eventFunc,
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

        this._calendar.render();
    }

    /*
     * 再描画
     */
    refetchEvents() {
        this._calendar.refetchEvents();
    }

    /**
     * 日付変更時イベント
     *
     * @param dateInfo
     */
    _dateChangeFunc = (dateInfo) => {
        //console.log("date change!!");
        //console.log(dateInfo.start);
        $("#curDate").val(dateInfo.start);
        //console.log($('#curDate').val());
    };

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
                //console.log("loadingForCardOn");
                // カードカレンダーの中のHidden値を取得。会員管理のように子画面にカレンダーがある場合
                var formData = FormCom.getFormArrayData(cardId);

                // カレンダーの条件を送信
                var sendData = Object.assign(formData, {
                    start: info.start.valueOf(),
                    end: info.end.valueOf(),
                    curDate: info.start.valueOf(),
                });

                // 詳細データを取得
                var url = UrlCom.getFuncUrl() + "/get_calendar";
                return axios.post(url, sendData);
            })
            .then((response) => {
                //console.log(response.data);

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
        //if (e.event._def.resourceIds[0] !== '000' && e.event._def.resourceIds[0] !== '800') {
        if (e.event._def.resourceIds[0] !== "000") {
            // モーダルの中身を更新
            this._vueModal.item = Object.assign(
                {
                    // ついでにIDも足しておく
                    id: e.event._def.publicId,
                    title: e.event._def.title,
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
        //console.log(info);
        if (
            info.resource._resource.id !== "000" &&
            info.resource._resource.id !== "800"
        ) {
            // 登録画面に遷移
            //var url = UrlCom.getFuncUrl() + "/new?"
            //        + "roomcd=" + "110"
            //        + "&date=" + moment(info.start).format("YYYYMMDD")
            //        + "&start_time=" + moment(info.start).format("HHmm")
            //        + "&end_time=" + moment(info.end).format("HHmm");
            var url =
                UrlCom.getFuncUrl() +
                "/new" +
                "/" +
                "110" +
                "/" +
                moment(info.start).format("YYYYMMDD") +
                "/" +
                moment(info.start).format("HHmm") +
                "/" +
                moment(info.end).format("HHmm");
            location.href = url;
        }
    };

    /**
     * ボタンクリックイベント
     */
    _datePickerButtonClick = (e) => {
        //var currentDate = calendar.getDate();
        //var newDate = moment(currentDate).add(7, 'days').format();
        //calendar.gotoDate(newDate);

        const button = e.target;
        const self = this;

        $(button)
            .daterangepicker({
                singleDatePicker: true,
                locale: {
                    format: "YYYY/MM/DD",
                    applyLabel: "適用",
                    cancelLabel: "キャンセル",
                },
                startDate: this._calendar.getDate(),
                // カレンダーの範囲
                minYear: new Date().getFullYear() - 2,
                maxYear: new Date().getFullYear() + 5, // とりあえず5年後くらい
                // 最初から自動で日付が入ってしまうので手動で格納
                //autoUpdateInput: false,
                // カレンダーのポップアップ位置を自動で調整
                // 下の方にテキストボックスがあれば、カレンダーは上にポップアップされる
                drops: "auto",
                //}, function(start, end, label) {
                //console.log(start);
                //var currentDate = calendar.getDate();
                //var newDate = moment(currentDate).add(7, 'days').format();
                //calendar.gotoDate(newDate);
            })
            .on("apply.daterangepicker", function (ev, picker) {
                // 適用ボタンクリックイベントで取得
                var newDate = moment(picker.startDate).format();
                self._calendar.gotoDate(newDate);
            })
            .on("cancel.daterangepicker", function (ev, picker) {
                // キャンセルボタンはクリアとした
                $(this).val("");
            });

        $(button).data("daterangepicker").show();
    };
}
