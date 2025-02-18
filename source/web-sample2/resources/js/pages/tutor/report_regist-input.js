"use strict";

/*
 * 授業報告書登録・編集
 */
export default class AppClass extends PageBase {
    /**
     * コンストラクタ
     */
    constructor() {
        super();
    }

    /**
     * 開始処理
     */
    start() {
        const self = this;
        const subCodes = ['L1', 'L2', 'H1', 'H2'];

        // 編集完了後は一覧へ戻る
        var afterEdit = () => {
            UrlCom.redirect(UrlCom.getFuncUrl());
        };

        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            vueData: {
                // 授業情報プルダウン変更用のプロパティは既存のものを使用

                // 教材プルダウン変更用のプロパティを用意
                selectGetItemCatL1: {},
                selectGetItemCatL2: {},
                selectGetItemCatL3: {},
                // 単元分類プルダウン変更用のプロパティを用意
                selectGetItemUni1_L1: {},
                selectGetItemUni2_L1: {},
                selectGetItemUni3_L1: {},
                selectGetItemUni1_L2: {},
                selectGetItemUni2_L2: {},
                selectGetItemUni3_L2: {},
                // 宿題教材プルダウン変更用のプロパティを用意
                selectGetItemCatH1: {},
                selectGetItemCatH2: {},
                selectGetItemCatH3: {},
                // 宿題単元分類プルダウン変更用のプロパティを用意
                selectGetItemUni1_H1: {},
                selectGetItemUni2_H1: {},
                selectGetItemUni3_H1: {},
                selectGetItemUni1_H2: {},
                selectGetItemUni2_H2: {},
                selectGetItemUni3_H2: {},
            },
            // 画面読み込み時
            vueMounted: function($vue, option) {
                // 初期表示時に、授業情報リスト設定処理
                $vue.selectChangeGetInit();
            },
            // Vueにメソッド追加
            vueMethods: {
                // 授業情報変更時の項目クリア処理
                lessonListReset: function () {
                    this.form.monthly_goal = "";
                    for (var subCode of subCodes) {
                        // 教材項目の初期化
                        this.selectGetItem = {};
                        this.form['text_cd_' + subCode] = "";
                        this.form['bef_text_cd_' + subCode] = "";
                        this.form['text_page_' + subCode] = "";
                        this.form['text_name_' + subCode] = "";
                        this['selectGetItemCat' + subCode] = {};
                        for (var j = 1; j <= 3; j++) {
                            // 単元分類項目の初期化
                            this.form['unit_category_cd' + j +  '_' + subCode] = "";
                            this.form['bef_unit_category_cd' + j + '_' + subCode] = "";
                            this.form['category_name' + j + '_' + subCode] = "";
                            // 単元項目の初期化
                            this['selectGetItemUni' + j +  '_' + subCode] = {};
                            this.form['unit_cd' + j +  '_' + subCode] = "";
                            this.form['bef_unit_cd' + j + '_' + subCode] = "";
                            this.form['unit_name' + j + '_' + subCode] = "";
                        }
                    }
                },
                // 授業情報リスト変更
                selectChangeGet: function (event) {
                    // 授業情報変更時の項目クリア処理
                    this.lessonListReset();
                    // チェンジイベントを発生させる
                    var selected = this.form.id;
                    self.selectChangeGetCallBack(
                        this,
                        selected,
                        this.option,
                        // 受信後のコールバック
                        (data) => {
                            // データをセット
                            this.selectGetItem = data;
                            // 教材リストが取得できた場合のみ、hiddenの教材コードをセット
                            if (data.selectItems.length != 0) {
                                for (var subCode of subCodes) {
                                    this.form['text_cd_' + subCode] = this.form['bef_text_cd_' + subCode];
                                }
                            }

                            // 前回授業が存在する場合、前回登録情報を設定
                            if (data.last_data.length != 1 && data.last_data['lesson_report_id'] == null)
                            {
                                // 今月の目標を設定
                                if (data.last_data['monthly_goal'] != null) {
                                    this.form.monthly_goal = data.last_data['monthly_goal'];
                                }
                                for (var subCode of subCodes) {
                                    if (subCode == 'H1') {
                                        break;
                                    }
                                    // 教材情報を設定
                                    if (data.last_data['text_cd_' + subCode] != null) {
                                        this.form['bef_text_cd_' + subCode] = data.last_data['text_cd_' + subCode];
                                        this.form['text_cd_' + subCode] = this.form['bef_text_cd_' + subCode];
                                    }
                                    if (data.last_data['text_page_' + subCode] != null) {
                                        this.form['text_page_' + subCode] = data.last_data['text_page_' + subCode];
                                    }
                                    if (data.last_data['text_name_' + subCode] != null) {
                                        this.form['text_name_' + subCode] = data.last_data['text_name_' + subCode];
                                    }
                                    for (var j = 1; j <= 3; j++) {
                                        // 単元分類情報を設定
                                        if (data.last_data['unit_category_cd' + j + '_' + subCode] != null) {
                                            this.form['bef_unit_category_cd' + j + '_' + subCode] = data.last_data['unit_category_cd' + j + '_' + subCode];
                                            this.form['unit_category_cd' + j + '_' + subCode] = this.form['bef_unit_category_cd' + j + '_' + subCode];
                                        }
                                        if (data.last_data['category_name' + j + '_' + subCode] != null) {
                                            this.form['category_name' + j + '_' + subCode] = data.last_data['category_name' + j + '_' + subCode];
                                        }
                                        // 単元情報を設定
                                        if (data.last_data['unit_cd' + j + '_' + subCode] != null) {
                                            this.form['bef_unit_cd' + j + '_' + subCode] = data.last_data['unit_cd' + j + '_' + subCode];
                                            this.form['unit_cd' + j + '_' + subCode] = this.form['bef_unit_cd' + j + '_' + subCode];
                                        }
                                        if (data.last_data['unit_name' + j + '_' + subCode] != null) {
                                            this.form['unit_name' + j + '_' + subCode] = data.last_data['unit_name' + j + '_' + subCode];
                                        }
                                    }
                                    // 単元分類リスト取得処理実行
                                    this.selectChangeGetCatInit(subCode);
                                }
                            }
                        }
                    );
                },
                // 画面初期表示時の授業情報リスト設定
                selectChangeGetInit: function () {
                    // 新規画面（未選択）の場合何もしない
                    if (ValueCom.isEmpty(this.form['id'])) {
                        return;
                    }
                    this.selectGetItem = {};
                    // チェンジイベントを発生させる
                    var selected = this.form.id;
                    // チェンジイベントを発生させる
                    self.selectChangeGetCallBack(
                        this,
                        selected,
                        this.option,
                        // 受信後のコールバック
                        (data) => {
                            // データをセット
                            this.selectGetItem = data;
                            // 教材リストが取得できた場合のみ、hiddenの教材コードをセット
                            if (data.selectItems.length != 0) {
                                for (var subCode of subCodes) {
                                    this.form['text_cd_' + subCode] = this.form['bef_text_cd_' + subCode];
                                    // 単元分類リスト取得処理実行
                                    this.selectChangeGetCatInit(subCode);
                                }
                            }
                        }
                    );
                },
                // 教材リスト変更時の単元分類リスト設定
                selectChangeGetCat: function (event) {
                    if (!event || !event.target.id.startsWith('text_cd_')) {
                        return;
                    }
                    var selectkey = event.target.id.replace('text_cd_','');
                    for (var j = 1;  j <= 3;  j++) {
                        // 単元分類プルダウン・単元プルダウンを初期化
                        this.form['unit_category_cd'  + j +  '_' + selectkey] = "";
                        this.form['unit_cd'  + j +  '_' + selectkey] = "";
                        this['selectGetItemUni'  + j +  '_' + selectkey] = {};
                    }
                    this['selectGetItemCat' + selectkey] = {};
                    // 取得情報格納用dataを設定
                    var itemDataName = "selectGetItemCat" + selectkey;

                    // 未選択となった場合は復帰
                    if (ValueCom.isEmpty(this.form[event.target.id])) {
                        return;
                    }

                    // チェンジイベントを発生させる
                    var selected = {
                        text_cd: this.form[event.target.id]
                    };
                    // 取得情報格納用data指定とする
                    self.selectChangeGet2(
                        this,
                        selected,
                        // URLを分けた
                        {
                            urlSuffix: "text"
                        },
                        itemDataName
                    );
                },
                // 単元分類リスト設定（画面初期表示時）
                selectChangeGetCatInit: function (subCode) {
                    var targetCd = this.form['bef_text_cd_' + subCode];
                    // 未選択の場合は何もせず復帰
                    if (ValueCom.isEmpty(targetCd)) {
                        return;
                    }
                    this['selectGetItemCat' + subCode] = {};
                    // 取得情報格納用dataを設定
                    var itemDataName = "selectGetItemCat" + subCode;

                    // チェンジイベントを発生させる
                    var selected = {
                        text_cd: targetCd
                    };
                    // チェンジイベント（取得情報格納用data指定）
                    self.selectChangeGetCallBack2(
                        this,
                        selected,
                        // URLを分けた
                        {
                            urlSuffix: "text"
                        },
                        itemDataName,
                        // 受信後のコールバック
                        (data) => {
                            // データをセット
                            this[itemDataName] = data;
                            // 単元分類リストが取得できた場合のみ、hiddenの単元分類コードをセット
                            if (data.selectItems.length != 0) {
                                for (var j = 1;  j <= 3;  j++) {
                                    this.form['unit_category_cd'  + j +  '_' + subCode] = this.form['bef_unit_category_cd'  + j +  '_' + subCode];
                                    this.selectChangeGetUniInit(subCode, j);
                                }
                            }
                        }
                    );
                },
                // 単元分類リスト変更時の単元リスト設定
                selectChangeGetUni: function (event) {

                    if (!event || !event.target.id.startsWith('unit_category_cd')) {
                        return;
                    }
                    var selectkey = event.target.id.replace('unit_category_cd','');

                    // 単元プルダウンを初期化
                    this.form['unit_cd' + selectkey] = "";
                    this['selectGetItemUni' + selectkey] = {};
                    // 取得情報格納用dataを設定
                    var itemDataName = "selectGetItemUni" + selectkey;

                    // 未選択となった場合は復帰
                    if (ValueCom.isEmpty(this.form[event.target.id])) {
                        return;
                    }
                    // チェンジイベントを発生させる
                    var selected = {
                        unit_category_cd: this.form[event.target.id]
                    };
                    // チェンジイベント（取得情報格納用data指定）
                    self.selectChangeGet2(
                        this,
                        selected,
                        // URLを分けた
                        {
                            urlSuffix: "category"
                        },
                        itemDataName
                    );
                },
                // 単元リスト設定（画面初期表示時）
                selectChangeGetUniInit: function (subCode, j) {

                    var targetCd = this.form['unit_category_cd'  + j +  '_' + subCode];
                    // 未選択の場合は何もせず復帰
                    if (ValueCom.isEmpty(targetCd)) {
                        return;
                    }

                    // 単元プルダウンを初期化
                    this['selectGetItemUni' + j + '_' + subCode] = {};
                    // 取得情報格納用dataを設定
                    var itemDataName = "selectGetItemUni" + j + '_' + subCode;

                    // チェンジイベントを発生させる
                    var selected = {
                        unit_category_cd: targetCd
                    };
                    // チェンジイベント（取得情報格納用data指定）
                    self.selectChangeGetCallBack2(
                        this,
                        selected,
                        // URLを分けた
                        {
                            urlSuffix: "category"
                        },
                        itemDataName,
                        // 受信後のコールバック
                        (data) => {
                            // データをセット
                            this[itemDataName] = data;
                            // 単元リストが取得できた場合のみ、hiddenの単元コードをセット
                            if (data.selectItems.length != 0) {
                                this.form['unit_cd' + j + '_' + subCode] = this.form['bef_unit_cd' + j + '_' + subCode];
                            }
                        }
                    );
                },
            }
        });
    }
}
