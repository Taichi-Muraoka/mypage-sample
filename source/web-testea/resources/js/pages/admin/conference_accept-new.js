"use strict";

/*
 * 面談追加登録
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

            // 画面読み込み時
            vueMounted: function ($vue, option) {
                // 初期表示時に、生徒プルダウンを初期化する。
                // -1の場合、自分の受け持ちの生徒だけに絞り込み
                $vue.selectChangeGet();
            },
            // Vueにメソッド追加
            vueMethods: {
                // 教室プルダウン変更イベント
                selectChangeGet: function (event) {
                    // 生徒プルダウンをクリア
                    this.form.student_id = "";
                    this.selectGetItem = {};
                    // ブースプルダウンをクリア
                    this.form.booth_id = "";
                    this.selectGetList = {};

                    // チェンジイベントを発生させる
                    var selected = this.form.campus_cd;
                    self.selectChangeGet(
                        this,
                        selected,
                        // URLを検索用とする
                        {
                            urlSuffix: "new",
                        }
                    );
                },
            },
        });
    }
}
