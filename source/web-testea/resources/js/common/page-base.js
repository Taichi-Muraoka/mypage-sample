"use strict";

/**
 * ページのスーパークラス
 */
export default class PageBase {
    //--------------------------------------------
    // コンストラクタ
    //--------------------------------------------

    /**
     * コンストラクタ
     */
    constructor() {
        // 抽象メソッドの確認。定義を必須とした
        if (this.start === undefined) {
            throw new TypeError("Must override start method.");
        }

        // このインスタンスを保持
        self = this;
    }

    //--------------------------------------------
    // 共通
    //--------------------------------------------

    /**
     * Promiseを取得
     */
    _getPromise() {
        return new Promise(resolve => {
            return resolve();
        });
    }

    /**
     * 空白チェック
     *
     * @param val チェック文字列
     */
    _isEmpty(val) {
        return !val ? (!(val === 0 || val === false) ? true : false) : false;
    }

    /**
     * 機能URLを取得
     * http://localhost/xxxxx/xxxx/
     * http://localhost/xxxxx/xxxx/xxx/xx/
     *
     * ↓以下の一階層目のURLを取得(ここが一覧だったりするので)
     * http://localhost/xxxxx/
     */
    _getFuncUrl() {
        // 現在のURL
        var loc = window.location.href;

        // appInfoのrootにはアプリのルートがあるのでそれを利用する
        // appInfo
        //   root: "http://localhost:8000"

        // 先頭部分を削除
        var url = loc.replace(appInfo.root + "/", "");

        // /まで取得
        var func = "";
        if (url.indexOf("/") < 0) {
            func = url;
        } else {
            func = url.substring(0, url.indexOf("/"));
        }

        return appInfo.root + "/" + func;
    }

    /**
     * カード用読み込み中 開始
     *
     * @param id
     */
    _loadingForCardOn(id) {
        $(id).append(
            '<div class="overlay"><i class="fas fa-4x fa-circle-notch fast-spin"></i></div>'
        );
    }

    /**
     * カード用読み込み中 終了
     *
     * @param id
     */
    _loadingForCardOff(id) {
        // アニメーション
        $(id + " div.overlay")
            .fadeOut("fast")
            .queue(function() {
                this.remove();
            });
    }

    /**
     * バリデーション結果をチェックする
     */
    _validateCheck($vue, response) {
        // 一旦エラーをクリア
        Vue.set($vue.form_err, "msg", {});
        Vue.set($vue.form_err, "class", {});

        // 確認モーダル用のデータの場合は無視する(confirm_modal_dataの1件だけある場合)
        if (
            Object.keys(response.data).length == 1 &&
            response.data["confirm_modal_data"]
        ) {
            // チェックしない
        }
        // エラーがあるかどうか
        else if (!(response.data.length <= 0)) {
            // バリデーションエラー
            for (const [key, value] of Object.entries(response.data)) {
                // vueにセット
                Vue.set($vue.form_err.msg, key, value);
                Vue.set($vue.form_err.class, key, true);

                // スクロール用にキーを格納する
                if ($vue.validateErrKey) {
                    $vue.validateErrKey.push(key);
                }
            }

            // Vueのupdatedでスクロールを行う(検索フォームは不要。入力フォームだけ)
            $vue.afterValidate = true;

            return false;
        }

        return true;
    }

    /**
     * ライブラリの初期化
     * Vueの初期化後じゃないとうまく読めない場合
     */
    _initLibs($vue, option = {}) {
        //---------------------
        // select2
        //---------------------

        $(".select2").select2({});

        //---------------------
        // bs-custom-file-input
        //---------------------

        // ファイル選択フォームのカスタマイズ
        // これがないと、ファイルを選択してもファイル名が表示されなかたりする
        bsCustomFileInput.init();

        // 取り消しボタンの挙動
        $(".inputFileReset").on("click", function(element) {
            bsCustomFileInput.destroy();

            // 同じform-group内のfileを取得
            var inputFile = $(element.target)
                .parent()
                .parent()
                .find("input[type='file']")
                .get(0);

            // ファイルをクリア
            inputFile.value = "";

            // var clone = inputFile.cloneNode(false);
            // inputFile.parentNode.replaceChilld(clone, inputFile);
            // IE対応のため上記を試したがreplaceChilldをVueが拾ってくれないので無視
            inputFile.dispatchEvent(new Event("change"));

            bsCustomFileInput.init();
        });

        //---------------------
        // datepicker
        //---------------------

        // locale
        var localeDate = {
            //format: "YYYY年MM月DD日",
            format: "YYYY/MM/DD",
            format2: "YYYY-MM-DD",
            applyLabel: "適用",
            // 今回はクリアボタンにする
            cancelLabel: "クリア"
        };

        // date
        $(".date-picker").each(function(index, element) {
            // _xxx がカレンダーinputなので先頭1文字削除し、本当のIDを取得
            var id = element.id.substr(1);

            // フォーマットを変更してセットする：2020年11月01日
            if (!self._isEmpty($(element).val())) {
                // フォーマットを明示的に指定する
                var dateVal = moment($(element).val(), 'YYYY/MM/DD');
                $(element).val(dateVal.format(localeDate.format));
                // hiddenも調整しておく
                Vue.set($vue.form, id, dateVal.format(localeDate.format2));
            }

            $(element)
                .daterangepicker(
                    {
                        singleDatePicker: true,
                        locale: localeDate,
                        showDropdowns: true,
                        // カレンダーの範囲
                        minYear: 2020,
                        maxYear: new Date().getFullYear() + 5, // とりあえず5年後くらい
                        // 最初から自動で日付が入ってしまうので手動で格納
                        autoUpdateInput: false,
                        // カレンダーのポップアップ位置を自動で調整
                        // 下の方にテキストボックスがあれば、カレンダーは上にポップアップされる
                        drops: "auto"
                    }
                    // 以下だと本日日付で適用ボタン押しても呼ばれない
                    // function(start, end, label) {}
                )
                .change(function(e) {
                    // 直接入力時のカレンダーのセット

                    // カレンダーの日付の取得
                    var input = $(e.target).val();
                    if (self._isEmpty(input)) {
                        // 空白の場合
                        $(this).val("");
                        Vue.set($vue.form, id, "");
                        return;
                    }

                    var calDate = new Date($(e.target).val());
                    if (calDate == "Invalid Date") {
                        // 一応エラーを拾う
                        // クリア扱いにした
                        $(this).val("");
                        Vue.set($vue.form, id, "");
                        // エラーダイアログ
                        appDialogCom.alert("正しい日付を入力してください");
                    } else {
                        // テキストボックスに表示用
                        // テキストボックスに表示(0埋めをしているだけ。無くても良いが一応)
                        $(e.target).val(
                            moment(calDate).format(localeDate.format)
                        );

                        // hiddenにセット
                        Vue.set(
                            $vue.form,
                            id,
                            moment(calDate).format(localeDate.format2)
                        );
                    }
                })
                .on("apply.daterangepicker", function(ev, picker) {
                    // 適用ボタンクリックイベントで取得
                    $(this).val(picker.startDate.format(localeDate.format));
                    // hiddenにセット
                    Vue.set(
                        $vue.form,
                        id,
                        picker.startDate.format(localeDate.format2)
                    );
                })
                .on("cancel.daterangepicker", function(ev, picker) {
                    // キャンセルボタンはクリアとした
                    $(this).val("");
                    Vue.set($vue.form, id, "");
                });
        });
    }

    /**
     * ライブラリの初期化
     * Vueの更新(updated)の際に呼ぶ
     * select2もそうだが、Vueの更新が終わった後に初期化が必要な処理
     */
    _updatedLibs() {
        // お知らせ管理で動的にプルダウン(select2)を変更しているが、
        // プルダウンを選択したり、再描画すると、うまく表示できないケースがある。
        // Vue上は正しく反映しているが、select2の描画がうまく行かないようだ。
        // Vueのupdatedイベントで呼んでもらう
        $(".select2").select2();
    }

    /**
     * Vueで使用する共通のフィルター
     */
    getFiltersCom() {
        return {
            // 年月日
            formatYmd: function(date) {
                if (self._isEmpty(date)) {
                    return "";
                } else {
                    return moment(date).format("YYYY/MM/DD");
                }
            },
            // 年月日 日時
            formatYmdHm: function(date) {
                if (self._isEmpty(date)) {
                    return "";
                } else {
                    return moment(date).format("YYYY/MM/DD HH:mm");
                }
            },
            // 年月日 日時（秒）
            formatYmdHms: function(date) {
                if (self._isEmpty(date)) {
                    return "";
                } else {
                    return moment(date).format("YYYY/MM/DD HH:mm:ss");
                }
            },
            // 年月
            formatYm: function(date) {
                if (self._isEmpty(date)) {
                    return "";
                } else {
                    return moment(date).format("YYYY/MM");
                }
            },
            // 時刻
            formatHm: function(date) {
                if (self._isEmpty(date)) {
                    return "";
                } else {
                    if (date.length == 8) {
                        // 16:00:00 のようなケースに対応
                        return moment(date, "HH:mm:ss").format("HH:mm");
                    } else {
                        // datetimeの場合：2020-11-20T07:00:00.000000Z
                        return moment(date).format("HH:mm");
                    }
                }
            },
            // 金額のカンマ区切り
            toLocaleString: function(numString) {
                if (self._isEmpty(numString)) {
                    return "";
                } else {
                    return Number(numString).toLocaleString();
                }
            },
            // YYYY年MM月
            formatYmString: function(date) {
                if (self._isEmpty(date)) {
                    return "";
                } else {
                    return moment(date).format("YYYY年MM月");
                }
            }
        };
    }

    /**
     * 親ページへリダイレクト
     */
    redirectToParent() {
        // MEMO: bladeで@yield('parent_page')を指定する。
        location.href = appInfo.parent;
    }

    //--------------------------------------------
    // カレンダー処理
    //--------------------------------------------

    /**
     * カレンダー処理
     */
    calendar() {
        // Vue: モーダル
        var $vueModal = this.getVueModal({ useShowEvent: false });

        // カレンダーの作成
        CalendarCom.create(
            //-----------------
            // 表示イベント
            //-----------------
            (info, successCallback, failureCallback) => {
                // カレンダーのカードタグのID
                var cardId = "#card-calendar";

                $.when()
                    .then(() => {
                        // カードのローディング開始
                        self._loadingForCardOn(cardId);

                        // カードカレンダーの中のHidden値を取得。会員管理のように子画面にカレンダーがある場合
                        var formData = this._getVueFormData(cardId);

                        // カレンダーの条件を送信
                        var sendData = Object.assign(formData, {
                            start: info.start.valueOf(),
                            end: info.end.valueOf()
                        });

                        // 詳細データを取得
                        var url = self._getFuncUrl() + "/get_calendar";
                        return axios.post(url, sendData);
                    })
                    .then(response => {
                        //console.log(response);

                        // コールバックで更新(eventプロパティにセットする)
                        successCallback(response.data);

                        // カードのローディング終了
                        self._loadingForCardOff(cardId);
                    })
                    .fail(AjaxCom.fail);
            },
            //-----------------
            // クリックイベント
            //-----------------
            e => {
                // モーダルの中身を更新

                Vue.set(
                    $vueModal,
                    "item",
                    Object.assign(
                        {
                            // ついでにIDも足しておく
                            id: e.event._def.publicId
                        },
                        // 送信データがe.event.extendedPropsに入ってくるのでそれを参照する
                        e.event.extendedProps
                    )
                );

                // モーダルを開く
                $vueModal.show();
            }
        );
    }

    //--------------------------------------------
    // モーダル処理
    //--------------------------------------------

    /*
     * モーダルのVue
     * bootstarapのモーダルなのでjQueryで制御
     */
    getVueModal(option = {}) {
        // 共通フィルター取得
        var filters = this.getFiltersCom();

        //--------------------
        // オプションの定義
        //--------------------

        // モックかどうか。通信処理は行われない
        if (option["isMock"] == undefined) {
            option["isMock"] = false;
        }

        // ターゲットのID
        if (option["id"] == undefined) {
            option["id"] = "#modal-dtl";
        }

        // 表示イベントを取得するかどうか
        // 例えばカレンダー画面ではモーダル時に非同期通信しないため
        if (option["useShowEvent"] == undefined) {
            option["useShowEvent"] = true;
        }

        // 登録完了後の処理
        if (option["afterExec"] == undefined) {
            option["afterExec"] = () => {};
        }

        // Vueにdataを追加
        if (option["vueData"] == undefined) {
            option["vueData"] = {};
        }

        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (self._isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else {
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // Modalのshowやexecで送信するデータを追加する
        // ギフトカード一覧の一覧出力で検索フォームを送信するため
        if (option["addSendData"] == undefined) {
            // 都度、最新の値を取るため、関数を指定する。
            option["addSendData"] = () => {};
        }

        //--------------------
        // Vueの定義
        //--------------------

        // Vue: モーダル
        return new Vue({
            el: option["id"],
            data: Object.assign(
                {
                    modal: null,
                    id: option["id"].replace("#", ""),
                    // モーダルの詳細データをセットする
                    item: {},
                    // モーダルを開いたときのdata属性を保持しておく
                    sendData: null,

                    // 確認ダイアログ用
                    deferred: null,
                    isConfirm: false
                },
                option["vueData"]
            ),
            mounted() {
                // モーダルを保持
                this.modal = $(this.$refs[this.id]);

                if (option["useShowEvent"]) {
                    // モーダル表示イベント
                    this.modal.on("show.bs.modal", this.onShowModal);
                }
            },
            // フィルターをセット
            filters: filters,
            methods: {
                //---------------------------
                // モーダルを表示する
                //---------------------------
                show() {
                    this.modal.modal();
                },
                //---------------------------
                // モーダル閉じる
                //---------------------------
                hide() {
                    this.modal.modal("hide");
                },
                //---------------------------
                // モーダルが表示されるイベント
                //---------------------------
                onShowModal(event) {
                    // モック時は処理しない
                    if (!option["isMock"]) {
                        // 一旦クリアする
                        Vue.set(this, "item", {});
                    }

                    // クリックされたボタンを取得
                    var button = $(event.relatedTarget);

                    // data属性をすべて取得し送信する
                    // $.dataはキャッシュするみたい。Vueで動的に変えたIdが取れなくなってしまう。
                    // 以下のattrで必ず取得するが、一気に取れないのでdataのキーでループ
                    var datas = $(button).data();
                    const dataKeys = Object.keys(datas);
                    var sendData = {};
                    for (var i = 0; i < dataKeys.length; i++) {
                        var key = dataKeys[i];
                        // 配列で保持 attrで取得
                        sendData[key] = $(button).attr("data-" + key);
                    }

                    // また、確認モーダルの場合、OKボタン押下時にもこのIDは使用するためthisに保持しておく
                    this.sendData = Object.assign(
                        sendData,
                        // 送信データを追加する
                        option["addSendData"]()
                    );

                    self._getPromise()
                        .then(() => {
                            // ローディング
                            //this.loadingForCardOn();

                            // 詳細データを取得
                            var url =
                                self._getFuncUrl() +
                                "/get_data" +
                                option["urlSuffix"];

                            // モック時は送信しない
                            if (!option["isMock"]) {
                                return axios.post(url, sendData);
                            }
                        })
                        .then(response => {
                            // データを取得する

                            // モック時は処理しない
                            if (!option["isMock"]) {
                                // itemにレスポンス結果を格納
                                Vue.set(this, "item", response.data);
                            }
                        })
                        .then(() => {
                            // ローディング
                            //this.loadingForCardOff();
                        })
                        .catch(AjaxCom.fail);
                },
                //---------------------------
                // モーダル確認
                // 送信フォーム内(getVueInputForm)から呼ばれる
                //---------------------------
                confirm() {
                    // プロミスで待つ。modalOkで解除する
                    this.deferred = new $.Deferred();
                    this.isConfirm = true;

                    return this.deferred.promise();
                },
                //---------------------------
                // 確認モーダル時
                // モーダルのOKボタン押下時イベント
                //---------------------------
                modalOk() {
                    if (this.isConfirm) {
                        //-----------------------------
                        // 確認ダイアログ時。(confirm中)
                        //-----------------------------
                        this.isConfirm = false;
                        // モーダルを閉じる
                        this.hide();
                        // プロミスを返却
                        this.deferred.resolve("result");
                    } else {
                        //-----------------------------
                        // exec処理を呼ぶ
                        //-----------------------------
                        self._getPromise()
                            .then(() => {
                                // モーダルを閉じる
                                this.hide();

                                // ダイアログ
                                appDialogCom.progressShow();

                                // モーダル処理を行う
                                var url =
                                    self._getFuncUrl() +
                                    "/exec_modal" +
                                    option["urlSuffix"];

                                // モック時は送信しない
                                if (!option["isMock"]) {
                                    // 送信
                                    return axios.post(url, this.sendData);
                                } else {
                                    // ダミーウェイト
                                    return DummyCom.wait();
                                }
                            })
                            .then(response => {
                                // ダイアログ
                                appDialogCom.progressHide();

                                // dataがあれば、ファイルのダウンロードとした
                                // ギフトカードの一覧出力くらい？
                                if (!(response.data.length <= 0)) {
                                    //-----------------------------
                                    // ファイルのダウンロード
                                    //-----------------------------

                                    // サーバーからSJISで応答してもここでUTF-8に変換されるため、
                                    // 手動で変換する。axiosがUTF-8にしてるっぽい
                                    // content-typeのcharsetを見てsjisか判断する
                                    const contentType =
                                        response.headers["content-to-sjis"];

                                    var fileURL = null;
                                    if (
                                        contentType &&
                                        contentType.indexOf(
                                            "charset=Shift_JIS"
                                        ) >= 0
                                    ) {
                                        //------------------
                                        // SJIS
                                        // エンコードする
                                        //------------------

                                        let resData = response.data;
                                        var codeRes = Encoding.stringToCode(
                                            resData
                                        );
                                        var arrRes = Encoding.convert(
                                            codeRes,
                                            "SJIS",
                                            "UNICODE"
                                        );
                                        var u8a = new Uint8Array(arrRes);
                                        var blob = new Blob([u8a], {
                                            type: "text/csv;charset=sjis;"
                                        });
                                        fileURL = window.URL.createObjectURL(
                                            blob
                                        );
                                    } else {
                                        //------------------
                                        // 通常通りUTF-8
                                        //------------------

                                        fileURL = window.URL.createObjectURL(
                                            new Blob([response.data])
                                        );
                                    }

                                    // aタグを作成
                                    const fileLink = document.createElement(
                                        "a"
                                    );
                                    // ファイル名の取得
                                    const contentDisposition =
                                        response.headers["content-disposition"];
                                    let fileName = contentDisposition.substring(
                                        contentDisposition.indexOf("''") + 2,
                                        contentDisposition.length
                                    );
                                    //デコードするとスペースが"+"になるのでスペースへ置換します
                                    fileName = decodeURI(fileName).replace(
                                        /\+/g,
                                        " "
                                    );
                                    fileLink.href = fileURL;
                                    fileLink.setAttribute("download", fileName);
                                    document.body.appendChild(fileLink);
                                    // クリックしてダウンロード
                                    fileLink.click();
                                    return;
                                } else {
                                    //-----------------------------
                                    // 完了メッセージ
                                    //-----------------------------
                                    return appDialogCom.success();
                                }
                            })
                            .then(
                                // 後処理を実行する
                                option["afterExec"]
                            )
                            .catch(AjaxCom.fail);
                    }
                },
                // ローディング開始
                loadingForCardOn() {
                    $("#" + this.id + " .modal-content").append(
                        '<div class="overlay"><i class="fas fa-4x fa-circle-notch fast-spin"></i></div>'
                    );
                },
                // ローディング終了
                loadingForCardOff() {
                    // アニメーション
                    $("#" + this.id + " div.overlay")
                        .fadeOut("fast")
                        .queue(function() {
                            this.remove();
                        });
                }
            }
        });
    }

    //--------------------------------------------
    // 一覧処理
    //--------------------------------------------

    /*
     * 検索フォームのVue
     */
    getVueSearchForm(option = {}) {
        // 共通フィルター取得
        var filters = this.getFiltersCom();

        // ID
        var id = "#app-serch-form";

        // hidden値を取得するためにFormの値を取得
        var formData = this._getVueFormData(id);

        //--------------------
        // オプションの定義
        //--------------------

        // モックかどうか。通信処理は行われない
        if (option["isMock"] == undefined) {
            option["isMock"] = false;
        }

        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (self._isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else {
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // Vueのmountedイベントを呼ぶ
        // プルダウンのチェンジイベントを発生させる想定
        if (option["vueMounted"] == undefined) {
            option["vueMounted"] = ($vue, option) => {};
        }

        // Vueにmethodsを追加
        if (option["vueMethods"] == undefined) {
            option["vueMethods"] = {};
        }

        //--------------------
        // Vueの定義
        //--------------------

        // Vue:フォーム
        return new Vue({
            el: id,
            data: {
                // VueのIdを格納する
                appId: id,
                // フォームインプット
                // 検索条件をはじめから選択する場合。(例：教室管理者の場合、プルダウンを教室のみにする)
                form: formData,
                // 検索後の検索条件を保持(ギフトカードの一覧出力)
                formAfterSearch: {},
                // オプションを保持しておく
                option: null,
                // フォームエラー
                form_err: {
                    msg: {},
                    class: {}
                },
                // プルダウン選択後の詳細を格納する
                selectGetItem: {},
                // 検索一覧を保持する
                vueSearchList: null,
                // 検索ボタンの非活性
                disabledBtnSearch: false
            },
            mounted() {
                // optionを保持
                this.option = option;

                // ライブラリの初期化
                self._initLibs(this, option);

                // afterSearch用
                var _self = this;

                // 一覧のVueインスタンスを保持
                this.vueSearchList = self.getVueSearchList(
                    Object.assign(option, {
                        afterSearch: function() {
                            // 検索完了後は検索ボタンを活性化する
                            _self.disabledBtnSearch = false;
                        }
                    })
                );

                // 呼び出し元のmouted処理を呼ぶ
                option["vueMounted"](this, option);

                // 画面読み込み時に一覧を表示
                this.execSearch();
            },
            updated() {
                // Vue更新後、ライブラリの初期化
                self._updatedLibs();
            },
            // フィルターをセット
            filters: filters,
            // オプションでメソッドを追加する
            methods: Object.assign(
                {
                    //-----------------------
                    // 検索済み検索結果を取得
                    //-----------------------
                    getAfterSearchCond: function(event) {
                        return this.formAfterSearch;
                    },
                    //-----------------------
                    // 検索ボタンクリック
                    //-----------------------
                    btnSearch: function(event) {
                        this.execSearch();
                    },
                    //-----------------------
                    // 検索実行
                    //-----------------------
                    execSearch: function() {
                        // 検索ボタンを非活性にする
                        this.disabledBtnSearch = true;

                        // 検索処理
                        self._getPromise()
                            .then(() => {
                                // バリデート
                                var url =
                                    self._getFuncUrl() +
                                    "/vd_search" +
                                    option["urlSuffix"];

                                // モック時は送信しない
                                if (!option["isMock"]) {
                                    return axios.post(url, this.form);
                                }
                            })
                            .then(response => {
                                // モック時は処理しない
                                if (!option["isMock"]) {
                                    // バリデーション結果をチェック
                                    if (!self._validateCheck(this, response)) {
                                        // 検索完了後は検索ボタンを活性化する
                                        this.disabledBtnSearch = false;
                                        // 処理を抜ける
                                        return AjaxCom.exit();
                                    }
                                }

                                // 検索した条件を保持しておく
                                // 例：ギフトカード一覧の一括出力用
                                Vue.set(this, "formAfterSearch", {});
                                for (const [key, value] of Object.entries(
                                    this.form
                                )) {
                                    Vue.set(this.formAfterSearch, key, value);
                                }

                                // 検索実行
                                this.vueSearchList.search(this.form);
                            })
                            .catch(AjaxCom.fail);
                    }
                },
                option["vueMethods"]
            )
        });
    }

    /*
     * 検索結果一覧のVueインスタンスを取得
     */
    getVueSearchList(option = {}) {
        // 共通フィルター取得
        var filters = this.getFiltersCom();

        //--------------------
        // オプションの定義
        //--------------------

        // モックかどうか。通信処理は行われない
        if (option["isMock"] == undefined) {
            option["isMock"] = false;
        }

        // ターゲットのID
        if (option["id"] == undefined) {
            option["id"] = "#app-serch-list";
        }

        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (self._isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else if (option["urlSuffix"].charAt(0) == "_") {
            // 何もしない(vueのoptionを渡された場合)
        } else {
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // 検索完了後の処理(検索ボタンの活性のため)
        if (option["afterSearch"] == undefined) {
            option["afterSearch"] = () => {};
        }

        //--------------------
        // Vueの定義
        //--------------------

        // divのid
        var id = option["id"];

        // hidden値を取得するためにFormの値を取得
        var formData = this._getVueFormData(id);

        return new Vue({
            el: id,
            data: {
                paginator: {},
                elements: [],

                // 検索フォームの条件(保持用)
                searchForm: {},

                // Hidden値などのformを保持。子画面で一覧がある場合など(請求情報一覧)
                form: formData,

                // ページ数を保持
                page: 1
            },
            // フィルターをセット
            filters: filters,
            methods: {
                //--------------------------
                // 検索
                //--------------------------
                search: function($searchForm = {}, page = 1, scroll = false) {
                    if (scroll) {
                        // 一覧のトップへ移動する
                        var position = $("#search-top").offset().top;
                        var speed = 300;
                        $("html, body").animate(
                            { scrollTop: position },
                            speed,
                            "swing"
                        );
                    }

                    // ページ数を保持する
                    this.page = page;

                    self._getPromise()
                        .then(() => {
                            // ローディング開始
                            self._loadingForCardOn(id);

                            // フォームデータに加えてページも追加
                            const sendData = Object.assign(
                                $searchForm,
                                {
                                    page: page
                                },
                                // hiddenを送信
                                this.form
                            );

                            // 検索 (例：http://localhost:8000/sample と同じ階層を想定)
                            var url =
                                self._getFuncUrl() +
                                "/search" +
                                option["urlSuffix"];

                            // モック時は送信しない
                            if (!option["isMock"]) {
                                return axios.post(url, sendData);
                            } else {
                                // ダミーウェイト
                                return DummyCom.wait();
                            }
                        })
                        .then(response => {
                            //console.log(response);

                            // モック時は処理しない
                            if (!option["isMock"]) {
                                // ページャデータ
                                var paginator = response.data.paginator;
                                var elements = response.data.elements;

                                // ページャをセット
                                Vue.set(this, "paginator", paginator);

                                // 件数表示
                                Vue.set(
                                    this.paginator,
                                    "total",
                                    paginator.total + "件"
                                );

                                // ページャーの表示
                                Vue.set(this, "elements", elements);

                                // 検索した条件を保持しておく(ページャなど)
                                //this.searchForm = $searchForm;
                                // 上記のようにVueのプロパティをそのままセットすると、
                                // リアルタイムで反映されてしまうので、以下のようにセットし直す
                                Vue.set(this, "searchForm", {});
                                for (const [key, value] of Object.entries(
                                    $searchForm
                                )) {
                                    Vue.set(this.searchForm, key, value);
                                }
                            }

                            // ローディング終了
                            self._loadingForCardOff(id);

                            // 検索後の処理
                            option["afterSearch"]();
                        })
                        .catch(AjaxCom.fail);
                },

                //--------------------------
                // ページャのリンクをクリック
                //--------------------------
                page_link: function(page) {
                    // 検索
                    this.search(this.searchForm, page, true);
                },

                //--------------------------
                // 再描画
                //--------------------------
                // 検索条件・ページ数はそのままで再検索する
                // 受付ボタンなどをクリックし、再度一覧を描画し直す際、
                // 1ページ目に戻らず、そのままのページでかつ検索条件も同じとする
                refresh: function() {
                    // 検索
                    this.search(this.searchForm, this.page, false);
                }
            }
        });
    }

    //--------------------------------------------
    // 送信処理
    //--------------------------------------------

    /**
     * フォームのIDを取得
     */
    _getVueFormData(formId) {
        var inputVals = {};
        $(formId)
            .find("input,select,textarea")
            .each(function(index, element) {
                // ID
                var id = $(element).attr("id");
                // name
                var name = $(element).attr("name");

                if (!self._isEmpty(id) && !$(element).is(":disabled")) {
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
                        // ラジオの場合。選択されたいるものを取得

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

    /*
     * 入力フォームのVue
     */
    getVueInputForm(option = {}) {
        // 共通フィルター取得
        var filters = this.getFiltersCom();

        //--------------------
        // オプションの定義
        //--------------------

        // モックかどうか。通信処理は行われない
        if (option["isMock"] == undefined) {
            option["isMock"] = false;
        }

        // 確認ダイアログの文言(新規登録)
        if (option["confirmStrNew"] == undefined) {
            option["confirmStrNew"] = "送信";
        }
        // 確認ダイアログの文言(更新)
        if (option["confirmStrEdit"] == undefined) {
            option["confirmStrEdit"] = "送信";
        }

        // 編集完了後の処理(登録・変更・削除後)
        if (option["afterEdit"] == undefined) {
            option["afterEdit"] = () => {};
        }

        // Vueにdataを追加
        if (option["vueData"] == undefined) {
            option["vueData"] = {};
        }

        // Vueにmethodsを追加
        if (option["vueMethods"] == undefined) {
            option["vueMethods"] = {};
        }

        // Vueのmountedイベントを呼ぶ
        // プルダウンのチェンジイベントを発生させる想定
        if (option["vueMounted"] == undefined) {
            option["vueMounted"] = ($vue, option) => {};
        }

        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (self._isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else {
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // モーダル確認ダイアログを使用するかどうか(使用する場合、モーダルIDを指定)
        if (self._isEmpty(option["confirmModal"])) {
            option["confirmModal"] = "";
        }

        // 送信時、処理中ダイアログを強制的に表示するかどうか
        if (option["progressShow"] == undefined) {
            option["progressShow"] = false;
        }

        //--------------------
        // Vueの定義
        //--------------------

        // フォームのID
        var formId = "#app-form";

        // 編集時にformのvalueから値を取得するためformの定義を作成する。
        var formData = this._getVueFormData(formId);

        // Vue:フォーム
        return new Vue({
            el: formId,
            // オプションでdataを追加する
            data: Object.assign(
                {
                    // VueのIdを格納する
                    appId: formId,
                    // フォームインプット
                    //form: {},
                    form: formData,
                    // フォームエラー
                    form_err: {
                        msg: {},
                        class: {}
                    },

                    // プルダウン選択後の詳細を格納する
                    selectGetItem: {},

                    // 確認モーダル
                    vueModal: null,

                    // オプションを保持しておく
                    option: null,

                    // バリデーション後かどうか
                    afterValidate: false,
                    // エラーのキーを格納
                    validateErrKey: []
                },
                option["vueData"]
            ),
            mounted() {
                // optionを保持
                this.option = option;

                // ライブラリの初期化
                self._initLibs(this, option);

                // 確認モーダルの表示用
                if (!self._isEmpty(option["confirmModal"])) {
                    this.vueModal = self.getVueModal({
                        useShowEvent: false,
                        id: option["confirmModal"]
                    });
                }

                // 呼び出し元のmouted処理を呼ぶ
                option["vueMounted"](this, option);
            },
            updated() {
                // バリデーションエラー時に、エラー箇所にスクロールする
                // これはVueの更新後じゃないと、エラーメッセージが表示されず、スクロール位置が取れないため
                if (this.afterValidate) {
                    // 一番上のエラーを探す
                    var errSpan = null;
                    var errSpans = $.find(".form-validation");
                    for (var i = 0; i < errSpans.length; i++) {
                        var dataId = $(errSpans[i]).attr("data-id");
                        if (this.validateErrKey.indexOf(dataId) >= 0) {
                            errSpan = errSpans[i];
                            break;
                        }
                    }

                    // スクロール
                    if (errSpan) {
                        // エラー箇所へ移動する
                        var position = $(errSpan).offset().top;
                        var speed = 300;
                        $("html, body").animate(
                            { scrollTop: position },
                            speed,
                            "swing"
                        );
                    }

                    // リセット
                    this.afterValidate = false;
                    this.validateErrKey = [];
                }

                // Vue更新後、ライブラリの初期化
                self._updatedLibs();
            },
            // フィルターをセット
            filters: filters,
            // オプションでメソッドを追加する
            methods: Object.assign(
                {
                    // 送信ボタン
                    submitNew: function() {
                        // 新規登録処理
                        self._sendNew(this, option);
                    },
                    // 編集ボタン
                    submitEdit: function() {
                        // 変更処理
                        self._sendEdit(this, option);
                    },
                    // 削除ボタン
                    submitDelete: function() {
                        // 削除処理
                        self._sendDelete(this, option);
                    },
                    // プルダウンの変更イベントで詳細を取得
                    selectChangeGet: function(event) {
                        // プルダウン変更
                        // 選択された値(呼び出し元が直接呼ぶ可能性があるのでここでselectedを取るようにした)
                        var selected = event.target.value;
                        self._selectChangeGet(this, selected, option);
                    },
                    // ファイルアップロード(アップロード済みファイル削除。実際はhidden値を削除する)
                    fileUploadedDelete: function(event) {
                        var uploaded = $(event.target)
                            .parent()
                            .parent();
                        // hiddenを取得
                        var updHidden = uploaded
                            .find("input[type='hidden']")
                            .get(0);
                        // Vueのformから削除
                        this.form[updHidden.id] = "";
                        // divごと非表示にする
                        uploaded.remove();
                    }
                },
                option["vueMethods"]
            )
        });
    }

    /**
     * 送信用のフォームデータを取得
     *
     * @param vueインスタンス
     */
    _getSendFormData($vue) {
        // フォームの値を取得(アップロードファイルも取得)
        const formData = new FormData();

        // 通常の入力用
        for (const [key, inputElement] of Object.entries($vue.form)) {
            // フォームを追加
            formData.append(key, inputElement);
        }

        // ファイルのアップロード用(bladeでrefを指定した)
        var existFile = false;
        if ($vue.$refs) {
            for (const [key, fileElement] of Object.entries($vue.$refs)) {
                // 先頭にfile_がついているもののみ
                if (!key.indexOf("file_")) {
                    // ファイルを取得(1つのinput fileで複数選択しないので0しか無いはず)
                    for (var i = 0; i < fileElement.files.length; i++) {
                        // フラグ(アップロードするファイルがある場合)
                        existFile = true;

                        // ファイルを取得
                        let file = fileElement.files[i];
                        // MEMO: 複数ファイル選択に対応する場合は、i番目を追加しないといけないはず
                        formData.append(key, file);
                    }
                }
            }
        }

        // アップロード用のコンテンツタイプ
        var formHeader = {};
        if (existFile) {
            // これがないとコントローラへファイルがアップロードされない
            formHeader = {
                headers: { "Content-Type": "multipart/form-data" }
            };
        }

        return {
            data: formData,
            header: formHeader,
            // アップロードが含まれるかどうか
            upload: existFile
        };
    }

    /**
     * 新規登録
     *
     * @param vueインスタンス
     */
    _sendNew($vue, option) {
        // 送信フォームのデータを取得する
        var sendData = this._getSendFormData($vue);

        self._getPromise()
            .then(() => {
                // バリデート(例：http://localhost:8000/sample/new と同じ階層を想定)
                var url =
                    self._getFuncUrl() + "/vd_input" + option["urlSuffix"];

                // モック時は送信しない
                if (!option["isMock"]) {
                    // ファイルアップロード時は大きいファイルが想定されるのでローディングを表示
                    // 空き時間登録のチェックボックスが多くやや時間がかかるので強制的に表示する場合も想定
                    if (sendData.upload || option["progressShow"]) {
                        // ダイアログ
                        appDialogCom.progressShow();
                    }

                    // バリデーション
                    return axios.post(url, sendData.data, sendData.header);
                }
            })
            .then(response => {
                // モック時はチェックしない
                if (!option["isMock"]) {
                    // ファイルアップロード時は大きいファイルが想定されるのでローディングを表示
                    if (sendData.upload || option["progressShow"]) {
                        // ダイアログ
                        appDialogCom.progressHide();
                    }

                    // バリデーション結果をチェック
                    if (!self._validateCheck($vue, response)) {
                        // 処理を抜ける
                        return AjaxCom.exit();
                    }
                }

                // 確認ダイアログ
                if (
                    Object.keys(response.data).length == 1 &&
                    response.data["confirm_modal_data"]
                ) {
                    // 確認モーダルダイアログ

                    // モーダルに表示したいデータをセットする
                    Vue.set(
                        $vue.vueModal,
                        "item",
                        response.data["confirm_modal_data"]
                    );

                    // モーダルを表示する
                    $vue.vueModal.show();

                    // 確認
                    return $vue.vueModal.confirm();
                } else {
                    // 通常の確認ダイアログ
                    return appDialogCom.confirmSend(option["confirmStrNew"]);
                }
            })
            .then(flg => {
                if (!flg) {
                    // いいえを押した場合
                    return AjaxCom.exit();
                }

                // ダイアログ
                appDialogCom.progressShow();

                // 新規登録
                var url = self._getFuncUrl() + "/create" + option["urlSuffix"];

                // モック時は送信しない
                if (!option["isMock"]) {
                    // 送信
                    return axios.post(url, sendData.data, sendData.header);
                } else {
                    // ダミーウェイト
                    return DummyCom.wait();
                }
            })
            .then(response => {
                // ダイアログ
                appDialogCom.progressHide();

                // エラー応答の場合は、アラートを表示する
                if (
                    Object.keys(response.data).length == 1 &&
                    response.data["error"]
                ) {
                    appDialogCom.alert(response.data["error"]);
                    return AjaxCom.exit();
                }

                // 完了メッセージ
                return appDialogCom.success(option["confirmStrNew"]);
            })
            .then(
                // 後処理を実行する
                option["afterEdit"]
            )
            .catch(AjaxCom.fail);
    }

    /**
     * 編集
     *
     * @param vueインスタンス
     */
    _sendEdit($vue, option) {
        // 送信フォームのデータを取得する
        var sendData = this._getSendFormData($vue);

        self._getPromise()
            .then(() => {
                // バリデート(例：http://localhost:8000/sample/edit/1 と同じ階層を想定)
                var url =
                    self._getFuncUrl() + "/vd_input" + option["urlSuffix"];
                // モック時は送信しない
                if (!option["isMock"]) {
                    // ファイルアップロード時は大きいファイルが想定されるのでローディングを表示
                    // 空き時間登録のチェックボックスが多くやや時間がかかるので強制的に表示する場合も想定
                    if (sendData.upload || option["progressShow"]) {
                        // ダイアログ
                        appDialogCom.progressShow();
                    }

                    // バリデーション
                    return axios.post(url, sendData.data, sendData.header);
                }
            })
            .then(response => {
                // モック時はチェックしない
                if (!option["isMock"]) {
                    // ファイルアップロード時は大きいファイルが想定されるのでローディングを表示
                    if (sendData.upload || option["progressShow"]) {
                        // ダイアログ
                        appDialogCom.progressHide();
                    }

                    // バリデーション結果をチェック
                    if (!self._validateCheck($vue, response)) {
                        // 処理を抜ける
                        return AjaxCom.exit();
                    }
                }

                // 確認ダイアログ
                if (
                    Object.keys(response.data).length == 1 &&
                    response.data["confirm_modal_data"]
                ) {
                    // 確認モーダルダイアログ

                    // モーダルに表示したいデータをセットする
                    Vue.set(
                        $vue.vueModal,
                        "item",
                        response.data["confirm_modal_data"]
                    );

                    // モーダルを表示する
                    $vue.vueModal.show();

                    // 確認
                    return $vue.vueModal.confirm();
                } else {
                    // 通常の確認ダイアログ
                    return appDialogCom.confirmSend(option["confirmStrEdit"]);
                }
            })
            .then(flg => {
                if (!flg) {
                    // いいえを押した場合
                    return AjaxCom.exit();
                }

                // ダイアログ
                appDialogCom.progressShow();

                // 編集
                var url = self._getFuncUrl() + "/update" + option["urlSuffix"];

                // モック時は送信しない
                if (!option["isMock"]) {
                    // 送信
                    return axios.post(url, sendData.data, sendData.header);
                } else {
                    // ダミーウェイト
                    return DummyCom.wait();
                }
            })
            .then(response => {
                // ダイアログ
                appDialogCom.progressHide();

                // エラー応答の場合は、アラートを表示する
                if (
                    Object.keys(response.data).length == 1 &&
                    response.data["error"]
                ) {
                    appDialogCom.alert(response.data["error"]);
                    return AjaxCom.exit();
                }

                // 完了メッセージ
                return appDialogCom.success(option["confirmStrEdit"]);
            })
            .then(
                // 後処理を実行する
                option["afterEdit"]
            )
            .catch(AjaxCom.fail);
    }

    /**
     * 削除
     *
     * @param vueインスタンス
     */
    _sendDelete($vue, option) {
        self._getPromise()
            .then(response => {
                // 確認ダイアログ
                return appDialogCom.confirmDel();
            })
            .then(flg => {
                if (!flg) {
                    // いいえを押した場合
                    return AjaxCom.exit();
                }

                // ダイアログ
                appDialogCom.progressShow();

                // 削除
                var url = self._getFuncUrl() + "/delete" + option["urlSuffix"];

                // モック時は送信しない
                if (!option["isMock"]) {
                    // 送信
                    return axios.post(url, $vue.form);
                } else {
                    // ダミーウェイト
                    return DummyCom.wait();
                }
            })
            .then(response => {
                // ダイアログ
                appDialogCom.progressHide();

                // エラー応答の場合は、アラートを表示する
                if (
                    Object.keys(response.data).length == 1 &&
                    response.data["error"]
                ) {
                    appDialogCom.alert(response.data["error"]);
                    return AjaxCom.exit();
                }

                // 完了メッセージ
                return appDialogCom.success("削除");
            })
            .then(
                // 後処理を実行する
                option["afterEdit"]
            )
            .catch(AjaxCom.fail);
    }

    /**
     * プルダウンの変更イベントで詳細を取得
     */
    _selectChangeGet($vue, selected, option) {
        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (self._isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else if (option["urlSuffix"].charAt(0) == "_") {
            // 何もしない(vueのoptionを渡された場合)
        } else {
            // 直接指定された場合
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // objectかどうかチェック
        var sendData = {};
        if (selected instanceof Object) {
            // オブジェクトの場合
            sendData = selected;
        } else {
            // 空白なら終了
            if (self._isEmpty(selected)) {
                Vue.set($vue, "selectGetItem", {});
                return;
            }
            // IDをつけて送信する
            sendData = {
                id: selected
            };
        }

        // 選択されたプルダウンのIDをもとにデータを取得
        self._getPromise()
            .then(() => {
                // ローディング開始
                self._loadingForCardOn($vue.appId);

                // 予め用意されたプルダウンの選択なのでバリデーションは不要とする
                // ただし、コントローラではバリデーションのチェックは行う必要がある。
                // ハンドリングの必要がないということ
                var url =
                    self._getFuncUrl() +
                    "/get_data_select" +
                    option["urlSuffix"];

                // 選択された値を送信する
                return axios.post(url, sendData);
            })
            .then(response => {
                // selectGetItemにレスポンス結果を格納
                Vue.set($vue, "selectGetItem", response.data);

                // 入力エリアのエラーは一旦クリアする
                Vue.set($vue.form_err, "msg", {});
                Vue.set($vue.form_err, "class", {});

                // ローディング終了
                self._loadingForCardOff($vue.appId);
            })
            .catch(AjaxCom.fail);
    }

    /**
     * プルダウンの変更イベントで詳細を取得
     * コールバック用とした。selectGetItemは初期化しないのでcallbackで処理してもらう
     * 例：お知らせ登録
     */
    _selectChangeGetCallBack($vue, selected, option, callback) {
        // URLの接尾語(画面ごとにURLを変えたい場合)
        if (self._isEmpty(option["urlSuffix"])) {
            option["urlSuffix"] = "";
        } else if (option["urlSuffix"].charAt(0) == "_") {
            // 何もしない(vueのoptionを渡された場合)
        } else {
            // 直接指定された場合
            option["urlSuffix"] = "_" + option["urlSuffix"];
        }

        // objectかどうかチェック
        var sendData = {};
        if (selected instanceof Object) {
            // オブジェクトの場合
            sendData = selected;
        } else {
            // 空白なら終了
            if (self._isEmpty(selected)) {
                Vue.set($vue, "selectGetItem", {});
                return;
            }
            // IDをつけて送信する
            sendData = {
                id: selected
            };
        }

        // 選択されたプルダウンのIDをもとにデータを取得
        self._getPromise()
            .then(() => {
                // ローディング開始
                self._loadingForCardOn($vue.appId);

                // 予め用意されたプルダウンの選択なのでバリデーションは不要とする
                // ただし、コントローラではバリデーションのチェックは行う必要がある。
                // ハンドリングの必要がないということ
                var url =
                    self._getFuncUrl() +
                    "/get_data_select" +
                    option["urlSuffix"];

                // 選択された値を送信する
                return axios.post(url, sendData);
            })
            .then(response => {
                // コールバック
                callback(response.data);

                // 入力エリアのエラーは一旦クリアする
                Vue.set($vue.form_err, "msg", {});
                Vue.set($vue.form_err, "class", {});

                // ローディング終了
                self._loadingForCardOff($vue.appId);
            })
            .catch(AjaxCom.fail);
    }
}
