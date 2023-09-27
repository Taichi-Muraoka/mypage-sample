"use strict";

/*
 * 欠席申請編集
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
        // 編集完了後は一覧へ戻る
        var afterEdit = () => {
            UrlCom.redirect(UrlCom.getFuncUrl());
        };
        // Vue: 入力フォーム
        this.getVueInputForm({
            afterEdit: afterEdit,
            vueMounted: function($vue, option) {
                // 編集時、プルダウンチェンジイベントを発生させる。
                $vue.selectChangeGetMulti();
            },
            vueMethods: {
                // 複数値送信対応
                selectChangeGetMulti: function(event) {
                    // スケジュールIDがあれば送信
                    var selected = null;
                    if (!ValueCom.isEmpty(this.form.id)) {
                        selected = {
                            id: this.form.id,
                            // ガード用に欠席申請IDを送信する
                            absentApplyId: this.form.absent_apply_id
                        };
                    }

                    // チェンジイベントを発生させる
                    self.selectChangeGet(this, selected, this.option);
                }
            }
        });
    }
}
