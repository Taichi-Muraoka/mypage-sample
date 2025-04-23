"use strict";

/*
 * ダイアログの共通処理
 * bootboxを使用。jQuery依存
 */
export default class DialogCom {
    /**
     * コンストラクタ
     */
    constructor() {
        // 閉じるために変数に格納する
        this.dialogProgress = null;

        // アラートを複数あげないように対応する。(主にエラーを考慮)
        this.dialogAlert = null;
    }

    /**
     * 処理中ダイアログの表示
     * モーダルではなく、全画面を制御
     */
    // 閉じるために変数に格納する
    progressShow() {
        if (!this.dialogProgress) {
            this.dialogProgress = bootbox.dialog({
                message:
                    '<div class="overlay-spin"><i class="fas fa-2x fa-circle-notch fast-spin"></i></div>' +
                    '<div class="overlay-text">処理中..</div>',
                animate: false,
                centerVertical: true,
                closeButton: false,
                size: "small"
            });
        }
    }

    /**
     * 処理中ダイアログを閉じる
     */
    progressHide() {
        if (this.dialogProgress) {
            this.dialogProgress.modal("hide");
            this.dialogProgress = null;
        }
    }

    /**
     * アラートの表示
     */
    alert(msg, size, title) {
        // 既にアラートがある場合は、何もしない
        if (this.dialogAlert) {
            return;
        }

        if (!size) {
            size = "sm";
        }

        var deferred = new $.Deferred();
        var self = this;

        this.dialogAlert = bootbox.alert({
            title: title,
            size: size,
            centerVertical: true,
            message: msg,
            animate: false,
            closeButton: false,
            callback: function(result) {
                self.dialogAlert = null;
                // OK/Canceのフラグを返却
                deferred.resolve(result);
            }
        });

        return deferred.promise();
    }

    /**
     * 確認ダイアログの表示
     */
    confirm(msg, conf, size, cancelOff) {
        // 既にアラートがある場合は、何もしない
        if (this.dialogAlert) {
            return;
        }

        // confirmボタン
        if (!conf) {
            conf = {
                label: "OK"
            };
        }

        var cancel = {};
        if (!cancelOff) {
            cancel = {
                label: "キャンセル"
            };
        }

        if (!size) {
            size = "sm";
        }

        var deferred = new $.Deferred();
        var self = this;

        this.dialogAlert = bootbox.confirm({
            size: size,
            centerVertical: true,
            message: msg,
            animate: false,
            closeButton: false,
            swapButtonOrder: true,
            buttons: {
                confirm: conf,
                cancel: cancel
            },
            callback: function(result) {
                self.dialogAlert = null;
                // OK/Canceのフラグを返却
                deferred.resolve(result);
            }
        });

        return deferred.promise();
    }

    /**
     * 送信確認ダイアログの表示
     */
    confirmSend(msg) {
        if (!msg) {
            msg = "送信";
        }
        return this.confirm(msg + "してもよろしいですか？", {
            //label: msg
        });
    }

    // /**
    //  * 更新確認ダイアログの表示
    //  */
    // confirmUpd() {
    //     return this.confirm("保存してもよろしいですか？", {
    //         label: "保存"
    //     });
    // }

    /**
     * 削除確認ダイアログの表示
     */
    confirmDel() {
        return this.confirm("削除してもよろしいですか？", {
            //label: "削除",
            className: "btn-danger"
        });
    }

    /**
     * 成功ダイアログの表示
     */
    success(msg) {
        if (!msg) {
            msg = "完了";
        }
        msg = msg + "しました";
        return this.alert(msg);
    }
}
