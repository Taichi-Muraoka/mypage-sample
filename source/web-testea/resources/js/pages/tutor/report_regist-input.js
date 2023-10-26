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
                selectGetItemUniL1_1: {},
                selectGetItemUniL1_2: {},
                selectGetItemUniL1_3: {},
                selectGetItemUniL2_1: {},
                selectGetItemUniL2_2: {},
                selectGetItemUniL2_3: {},
                // 宿題教材プルダウン変更用のプロパティを用意
                selectGetItemCatHomeL1: {},
                selectGetItemCatHomeL2: {},
                selectGetItemCatHomeL3: {},
                // 宿題単元分類プルダウン変更用のプロパティを用意
                selectGetItemUniHomeL1_1: {},
                selectGetItemUniHomeL1_2: {},
                selectGetItemUniHomeL1_3: {},
                selectGetItemUniHomeL2_1: {},
                selectGetItemUniHomeL2_2: {},
                selectGetItemUniHomeL2_3: {},
            },
            // Vueにメソッド追加
            vueMethods: {
                // この画面では複数のプルダウン選択があるので対応する
                // 授業情報リスト変更
                selectChangeGet: function (event) {

                    this.selectGetItem = {};
                    for (var i = 1;  i <= 2;  i++) {
                        // 教材プルダウンの初期化
                        this.form['lesson_text' + i] = "";
                        for (var j = 1;  j <= 3;  j++) {
                            // 単元分類プルダウン・単元プルダウンもクリア
                            this.form['lesson_category' + i + '_' + j] = "";
                            this.form['lesson_unit' + i + '_' + j] = "";
                            this['selectGetItemCatL' + i + '_' + j] = {};
                            this['selectGetItemUniL' + i + '_' + j] = {};
                        }
                    }
                    // チェンジイベントを発生させる
                     var selected = this.form.id;
                     self.selectChangeGet(
                         this,
                         selected,
                         this.option
                     );
                     for (var i = 1;  i <= 2;  i++) {
                        // 教材プルダウンの初期化
                        this.form['homework_text' + i] = "";
                        for (var j = 1;  j <= 3;  j++) {
                            // 単元分類プルダウン・単元プルダウンもクリア
                            this.form['homework_category' + i + '_' + j] = "";
                            this.form['homework_unit' + i + '_' + j] = "";
                            this['selectGetItemCatHomeL' + i + '_' + j] = {};
                            this['selectGetItemUniHomeL' + i + '_' + j] = {};
                        }
                    }
                    // チェンジイベントを発生させる
                     var selected = this.form.id;
                     self.selectChangeGet(
                         this,
                         selected,
                         this.option
                     );
                 },
                // 教材リスト変更
                selectChangeGetCat: function (event) {

                    if (!event || !event.target.id.startsWith('lesson_text')) {
                        return;
                    }
                    var selectkey = event.target.id.replace('lesson_text','');
                    for (var j = 1;  j <= 3;  j++) {
                        // 単元分類プルダウン・単元プルダウンを初期化
                        this.form['lesson_category' + selectkey + '_' + j] = "";
                        this.form['lesson_unit' + selectkey + '_' + j] = "";
                        this['selectGetItemUniL' + selectkey + '_' + j] = {};
                    }
                    this['selectGetItemCatL' + selectkey] = {};
                    // 取得情報格納用dataを設定
                    var itemDataName = "selectGetItemCatL" + selectkey;

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
                // 単元分類リスト変更
                selectChangeGetUni: function (event) {

                    if (!event || !event.target.id.startsWith('lesson_category')) {
                        return;
                    }
                    var selectkey = event.target.id.replace('lesson_category','');

                    // 単元プルダウンを初期化
                    this.form['lesson_unit' + selectkey] = "";
                    this['selectGetItemUniL' + selectkey] = {};
                    // 取得情報格納用dataを設定
                    var itemDataName = "selectGetItemUniL" + selectkey;
                    this['selectGetItemCatL' + selectkey] = {};

                    // 未選択となった場合は復帰
                    if (ValueCom.isEmpty(this.form[event.target.id])) {
                        return;
                    }
                    // チェンジイベントを発生させる
                    var selected = {
                        unit_category_cd: this.form[event.target.id]
                    };
                    // 取得情報格納用data指定とする
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
                // 宿題リスト変更
                selectChangeGetHome: function (event) {

                    this.selectGetItem = {};
                    for (var i = 1;  i <= 2;  i++) {
                        // 教材プルダウンの初期化
                        this.form['homework_text' + i] = "";
                        for (var j = 1;  j <= 3;  j++) {
                            // 単元分類プルダウン・単元プルダウンもクリア
                            this.form['homework_category' + i + '_' + j] = "";
                            this.form['homework_unit' + i + '_' + j] = "";
                            this['selectGetItemCatHomeL' + i + '_' + j] = {};
                            this['selectGetItemUniHomeL' + i + '_' + j] = {};
                        }
                    }
                    // チェンジイベントを発生させる
                     var selected = this.form.id;
                     self.selectChangeGet(
                         this,
                         selected,
                         this.option
                     );
                 },
                // 教材リスト変更
                selectChangeGetCatHome: function (event) {

                    if (!event || !event.target.id.startsWith('homework_text')) {
                        return;
                    }
                    var selectkey = event.target.id.replace('homework_text','');
                    for (var j = 1;  j <= 3;  j++) {
                        // 単元分類プルダウン・単元プルダウンを初期化
                        this.form['homework_category' + selectkey + '_' + j] = "";
                        this.form['homework_unit' + selectkey + '_' + j] = "";
                        this['selectGetItemUniHomeL' + selectkey + '_' + j] = {};
                    }
                    this['selectGetItemCatHomeL' + selectkey] = {};
                    // 取得情報格納用dataを設定
                    var itemDataName = "selectGetItemCatHomeL" + selectkey;

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
                // 単元分類リスト変更
                selectChangeGetUniHome: function (event) {

                    if (!event || !event.target.id.startsWith('homework_category')) {
                        return;
                    }
                    var selectkey = event.target.id.replace('homework_category','');

                    // 単元プルダウンを初期化
                    this.form['homework_unit' + selectkey] = "";
                    this['selectGetItemUniHomeL' + selectkey] = {};
                    // 取得情報格納用dataを設定
                    var itemDataName = "selectGetItemUniHomeL" + selectkey;
                    this['selectGetItemCatHomeL' + selectkey] = {};

                    // 未選択となった場合は復帰
                    if (ValueCom.isEmpty(this.form[event.target.id])) {
                        return;
                    }
                    // チェンジイベントを発生させる
                    var selected = {
                        unit_category_cd: this.form[event.target.id]
                    };
                    // 取得情報格納用data指定とする
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
            }
        });
    }
}
