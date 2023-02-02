"use strict";

/*
 * 非同期通信用共通クラス
 */
export default class AjaxCom {
    /**
     * 非同期処理 処理Exit
     *
     * Connect.failにてエラーとしない
     */
    static exit() {
        return new $.Deferred().reject(true);
    }

    /**
     * 非同期処理 失敗時処理
     *
     * @param result
     */
    static fail(result) {
        if (result === true) {
            // trueの場合は、何もしない
            return;
        }

        if (!result.response) {
            // responseがない場合は、通信の結果ではなく普通のJSのエラー
            // 開発時くらい。とりあえずエラーの表示
            // あとは読み込み中に別画面開いた際のError: Request aborted。これも無視で良い。
            AjaxCom.err(result);
            return;
        }

        // F5や画面遷移時のエラー。これは本当のエラーではないので無視
        if (result.response.readyState == 0 || result.response.status == 0) {
            return;
        }

        // 認証エラーの場合(考えられるのはセッション切れ)
        // laravelの場合、csrfの対応が先に入るようで419を拾う
        // 場合によっては401のケースもあった
        if (
            result.response.status == 401 ||
            result.response.status == 403 ||
            result.response.status == 419
        ) {
            // ログアウトフォームを呼び出す
            // セッション切れ以外の場合/homeへの無限ループになる可能性があるので
            //$("#logout-form").submit();
            // ルートへ遷移し、結果的に/loginへ飛ばすようにする
            UrlCom.redirect(appInfo.root);
            return;
        }

        // エラーの表示
        // URL先がないなどの、アクセスできなかったエラー
        AjaxCom.err(result, true);
    }

    /**
     * エラーの表示
     *
     * @param  result
     */
    static err(result, priAlert = false) {
        // とりあえずConsole.log出す。
        console.log("===Connect.fail===");
        console.log(result);

        if (priAlert) {
            // ローディングされてたら一旦閉じる
            appDialogCom.progressHide();

            //alert("通信に失敗しました");
            // ダイアログでの表示に変更。alertと違いブロッキングではないが、
            // Promiseのfailで呼ばれるので、特に問題ないはず

            if (result.response.status == 413) {
                // エラーコード413はファイルアップロードサイズ超過エラー
                // PHP側の処理が行われず、応答結果としてエラーが返るのでここで拾う
                appDialogCom.alert(
                    "最大ファイルアップロードサイズを超えたため、アップロードできませんでした。",
                    "md"
                );
            } else {
                // 何らかのエラー
                appDialogCom.alert(
                    "エラーが発生しました。<br>" +
                        "一度ページの再読み込みを行ってください。<br>" +
                        "それでも解決しない場合は、システム管理者へご連絡ください。",
                    "md"
                );
            }
        }
    }
}
