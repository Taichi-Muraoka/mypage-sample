"use strict";

/*
 * 面談日程連絡受付一覧
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
        
        // Vue: モーダル
        this.getVueModal();

        // Vue: 検索フォーム
        this.getVueSearchForm({
            // 検索フォーム内にDatePickerを使用する場合
            useSearchDatePicker: true,
            // 画面読み込み時
            vueMounted: function ($vue, option) {
                // 初期表示時に、生徒プルダウンを初期化する。
                // -1の場合、自分の受け持ちの生徒だけに絞り込み
                $vue.selectChangeGetRoom();
            },
            // Vueにメソッド追加
            vueMethods: {
                // 教室プルダウン変更イベント
                selectChangeGetRoom: function (event) {
                    // 生徒プルダウンをクリア
                    this.form.student_id = "";
                    this.selectGetItem = {};

                    // チェンジイベントを発生させる
                    var selected = this.form.campus_cd;
                    self.selectChangeGet(
                        this,
                        selected,
                        // URLを検索用とする
                        {
                            urlSuffix: "search",
                        }
                    );
                },
            },
        });
    }
}
