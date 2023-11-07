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
     */
    selectChangeGet2($vue, selected, option, dataName) {
        PageEvent.selectChangeGet2($vue, selected, option, dataName);
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
