"use strict";

/*
 * フォーム処理クラス
 */
export default class FormCom {
    /**
     * 指定されたIDのForm値を配列で取得
     * Vueのdataプロパティにセットするために使用
     */
    static getFormArrayData(formId) {
        var inputVals = {};
        $(formId)
            .find("input,select,textarea")
            .each(function (index, element) {
                // ID
                var id = $(element).attr("id");
                // name
                var name = $(element).attr("name");

                if (!ValueCom.isEmpty(id) && !$(element).is(":disabled")) {
                    if ($(element).is(":checkbox")) {
                        // チェックボックスの場合(同じnameが複数ある想定)
                        // arrayで渡す
                        if (!inputVals[name]) {
                            // nameが同じ場合、配列で扱う
                            inputVals[name] = [];
                        }

                        if ($(element).is(":checked")) {
                            // チェックされた値を取得
                            inputVals[name].push($(element).val());
                        }
                    } else if ($(element).is(":radio")) {
                        // ラジオの場合。選択されているものを取得

                        if ($(element).is(":checked")) {
                            inputVals[name] = $(element).val();
                        }
                    } else if ($(element).is(":file")) {
                        // file選択のinputは無視する。
                    } else {
                        inputVals[id] = $(element).val();
                    }
                }
            });

        return inputVals;
    }

    /**
     * カード用読み込み中 開始
     *
     * @param id
     */
    static loadingForCardOn(id) {
        $(id).append(
            '<div class="overlay"><i class="fas fa-4x fa-circle-notch fast-spin"></i></div>'
        );
    }

    /**
     * カード用読み込み中 終了
     *
     * @param id
     */
    static loadingForCardOff(id) {
        // アニメーション
        $(id + " div.overlay")
            .fadeOut("fast")
            .queue(function () {
                this.remove();
            });
    }
}
