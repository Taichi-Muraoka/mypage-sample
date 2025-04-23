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
            // license key for premium features
            schedulerLicenseKey: '0477382314-fcs-1699842877',
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
                center: "title",
                right: "datePickerButton",
            },
            buttonText: {
                today: "今日",
                month: "月",
                week: "週",
                day: "日",
            },
            // タイトルの書式
            titleFormat: function (date) {
                const year = date.date.year
                const month = date.date.month + 1;
                const day = date.date.day;
                const weekNum = date.date.marker.getDay();
                const week = [
                    "(日)",
                    "(月)",
                    "(火)",
                    "(水)",
                    "(木)",
                    "(金)",
                    "(土)"
                ][weekNum];
                const title = year + "年" + month + "月" + day + "日 " + week
                return title;
            },
            locale: "ja",
            contentHeight: "auto",
            stickyFooterScrollbar: true,
            stickyHeaderDates: true,
            dayMinWidth: 150,
            selectable: false,
            selectMirror: false,
            navLinks: true,
            // リソース（ブース）の読み込み処理
            resources: this._resourceFunc,
            // スケジュールデータの読み込み処理
            events: this._eventFunc,
            // イベントクリック
            eventClick: this._eventClick,
            selectable: true,
            // Viewエリアクリック
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
        this._calendar.refetchResources();
        this._calendar.refetchEvents();
    }

    /**
     * 日付変更時イベント
     *
     * @param dateInfo
     */
    _dateChangeFunc = (dateInfo) => {
        $("#target_date").val(dayjs(dateInfo.start).format("YYYY-MM-DD"));
    };

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
                $("#target_date").val(dayjs(info.start.valueOf()).format("YYYY-MM-DD"));
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
        // 時間割のスケジュールはモーダル表示しない
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
        // カレンダーのカードタグのID
        var cardId = "#card-calendar";
        var formData = FormCom.getFormArrayData(cardId);
        if (
            info.resource._resource.id !== "000" &&
            info.resource._resource.id !== "999"
        ) {
            var url =
                UrlCom.getFuncUrl() +
                "/new" +
                "/" +
                formData.campus_cd +
                "/" +
                dayjs(info.start).format("YYYYMMDD") +
                dayjs(info.start).format("HHmm") +
                "/" +
                info.resource._resource.id;
            location.href = url;
        }
    };

    /**
     * ボタンクリックイベント
     */
    _datePickerButtonClick = (e) => {
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
            })
            .on("apply.daterangepicker", function (ev, picker) {
                // 適用ボタンクリックイベントで取得
                var newDate = dayjs(picker.startDate).format();
                self._calendar.gotoDate(newDate);
            })
            .on("cancel.daterangepicker", function (ev, picker) {
                // キャンセルボタンはクリアとした
                $(this).val("");
            });

        $(button).data("daterangepicker").show();
    };
}
