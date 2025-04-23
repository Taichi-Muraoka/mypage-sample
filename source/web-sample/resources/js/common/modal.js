"use strict";

/*
 * モーダルの共通処理
 * bootstrapのモーダルの制御
 */
export default class ModalCom {
    /**
     * モーダル共通の初期化
     * 必ず一度は呼ぶ
     * インスタンスごとに呼ぶ必要がないので、staticにし一度だけ呼んでもらう
     */
    static init() {
        // モーダルを複数開く対応(モーダル上でのbootbox)
        // 以下を入れるだけで、とりあえず、全てのモーダルに対応できそう
        //http://sissoko.hatenablog.com/entry/2019/03/24/103235
        $(document).on("show.bs.modal", ".modal", function() {
            var zIndex = 1040 + 10 * $(".modal:visible").length;
            $(this).css("z-index", zIndex);
            setTimeout(function() {
                $(".modal-backdrop")
                    .not(".modal-stack")
                    .css("z-index", zIndex - 1)
                    .addClass("modal-stack");
            }, 0);
        });
        $(document).on("hidden.bs.modal", ".modal", function() {
            $(".modal:visible").length &&
                $(document.body).addClass("modal-open");
        });
    }
}
