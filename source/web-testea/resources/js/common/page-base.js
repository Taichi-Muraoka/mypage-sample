"use strict";

import PageModal from "../page-components/page-modal";
import PageModalSelectList from "../page-components/page-modal-select-list";
import PageModalForm from "../page-components/page-modal-form";
import PageSearchForm from "../page-components/page-search-form";
import PageSearchList from "../page-components/page-search-list";
import PageInputForm from "../page-components/page-input-form";
import PageEvent from "../page-components/page-event";

/**
 * ページのスーパークラス
 */
export default class PageBase {
    /**
     * コンストラクタ
     */
    constructor() {
        // 抽象メソッドの確認。定義を必須とした
        if (this.start === undefined) {
            throw new TypeError("Must override start method.");
        }
    }

    //--------------------------------------------
    // 共通処理
    //--------------------------------------------

    /**
     * 親ページへリダイレクト
     */
    redirectToParent() {
        // MEMO: bladeで@yield('parent_page')を指定する。
        location.href = appInfo.parent;
    }

    /**
     * 親ページへリダイレクト
     */
    redirectToParent2() {
        // MEMO: bladeで@yield('parent_page2')を指定する。
        location.href = appInfo.parent2;
    }

    //--------------------------------------------
    // カレンダー処理
    //--------------------------------------------

    // TODO: カレンダーは一個のクラスに入れておいて、関数を複数分けて取れるといい(別途対応)
    // calendarフォルダに、それぞれのクラス作って定義すればよい。それほど共通化する必要もないような

    /**
     * カレンダー処理
     */
    calendar() {
        // Vue: モーダル
        var $vueModal = this.getVueModal({ useShowEvent: false });

        // カレンダーの作成
        CalendarCom.create(
            //-----------------
            // 表示イベント
            //-----------------
            (info, successCallback, failureCallback) => {
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
                        //console.log(response);

                        // コールバックで更新(eventプロパティにセットする)
                        successCallback(response.data);

                        // カードのローディング終了
                        FormCom.loadingForCardOff(cardId);
                    })
                    .fail(AjaxCom.fail);
            },
            //-----------------
            // クリックイベント
            //-----------------
            (e) => {
                // モーダルの中身を更新

                $vueModal.item = Object.assign(
                    {
                        // ついでにIDも足しておく
                        id: e.event._def.publicId,
                    },
                    // 送信データがe.event.extendedPropsに入ってくるのでそれを参照する
                    e.event.extendedProps
                );

                // モーダルを開く
                $vueModal.show();
            }
        );
    }

    /**
     * カレンダー処理（defaultWeekカレンダー）
     */
    defaultWeekCalendar() {
        // Vue: モーダル
        var $vueModal = this.getVueModal({ useShowEvent: false });
        // カレンダーの作成
        // モック用に仮の日付を設定（日曜にする）
        var curDate = new Date("2023/03/19");

        for (var i = 1; i < 7; i++) {
            curDate.setDate(curDate.getDate() + 1);
            CalendarCom.createForDefaultWeek(
                //-----------------
                // カレンダーNo（月曜始まり）
                //-----------------
                i,
                //-----------------
                // 表示日付
                //-----------------
                curDate,
                //-----------------
                // 表示イベント
                //-----------------
                (info, successCallback, failureCallback) => {
                    // カレンダーのカードタグのID
                    var cardId = "#card-calendar";

                    $.when()
                        .then(() => {
                            // カードのローディング開始
                            FormCom.loadingForCardOn(cardId);

                            // カードカレンダーの中のHidden値を取得。会員管理のように子画面にカレンダーがある場合
                            //console.log("into calendar disp event CurDate");
                            //console.log(info.start.valueOf());
                            var formData = FormCom.getFormArrayData(cardId);
                            //console.log(formData);

                            // カレンダーの条件を送信
                            var sendData = Object.assign(formData, {
                                start: info.start.valueOf(),
                                end: info.end.valueOf(),
                                day: i,
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
                },
                //-----------------
                // クリックイベント
                //-----------------
                (e) => {
                    // 時間割のスケジュールはモーダル表示しない
                    if (e.event._def.resourceIds[0] !== "000") {
                        // モーダルの中身を更新
                        $vueModal.item = Object.assign(
                            {
                                // ついでにIDも足しておく
                                id: e.event._def.publicId,
                            },
                            // 送信データがe.event.extendedPropsに入ってくるのでそれを参照する
                            e.event.extendedProps
                        );

                        // モーダルを開く
                        $vueModal.show();
                    }
                },
                //-----------------
                // Viewエリアクリック
                //-----------------
                (info, successCallback, failureCallback) => {
                    //console.log(info);

                    // 詳細データを取得
                    var url =
                        UrlCom.getFuncUrl() +
                        "/new?" +
                        "roomcd=" +
                        "110" +
                        "&day=" +
                        moment(info.start).format("d") +
                        "&start_time=" +
                        moment(info.start).format("HHmm") +
                        "&end_time=" +
                        moment(info.end).format("HHmm");
                    location.href = url;
                }
            );
        }
    }

    //--------------------------------------------
    // モーダル処理
    //--------------------------------------------

    /*
     * モーダルのVue
     */
    getVueModal(option = {}) {
        const pageModal = new PageModal();
        return pageModal.getVueApp(option);
    }

    /*
     * 選択モーダル(検索リスト)のVue
     */
    getVueModalSelectList(option = {}) {
        const pageModalSelectList = new PageModalSelectList();
        return pageModalSelectList.getVueApp(option);
    }

    /*
     * モーダル(フォーム)のVue
     */
    getVueModalForm(option = {}) {
        const pageModalForm = new PageModalForm();
        return pageModalForm.getVueApp(option);
    }

    //--------------------------------------------
    // 一覧処理
    //--------------------------------------------

    /*
     * 検索フォームのVue
     */
    getVueSearchForm(option = {}) {
        const pageSearchForm = new PageSearchForm();
        return pageSearchForm.getVueApp(option);
    }

    /*
     * 検索結果一覧のVueインスタンスを取得
     */
    getVueSearchList(option = {}) {
        const pageSearchlist = new PageSearchList();
        return pageSearchlist.getVueApp(option);
    }

    //--------------------------------------------
    // 送信処理
    //--------------------------------------------

    /*
     * 入力フォームのVue
     */
    getVueInputForm(option = {}) {
        const pageInputForm = new PageInputForm();
        return pageInputForm.getVueApp(option);
    }

    /**
     * プルダウンの変更イベントで詳細を取得
     */
    selectChangeGet($vue, selected, option) {
        PageEvent.selectChangeGet($vue, selected, option);
    }

    /**
     * プルダウンの変更イベントで詳細を取得
     * コールバック用とした。selectGetItemは初期化しないのでcallbackで処理してもらう
     * 例：お知らせ登録
     */
    selectChangeGetCallBack($vue, selected, option, callback) {
        PageEvent.selectChangeGetCallBack($vue, selected, option, callback);
    }
}
