<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\UrlWindow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * Form - コントローラ共通処理
 */
trait CtrlFormTrait
{
    //------------------------------
    // 検索
    //------------------------------

    /**
     * 検索結果一覧をページャ付きで取得する
     * 
     * @param \Illuminate\Http\Request $request リクエスト
     * @param \Illuminate\Database\Eloquent\Builder $query 一覧取得クエリ
     * @param Closure $closure 結果レコードセットを加工するクロージャ
     * @return array ページャの結果
     */
    protected function getListAndPaginator(Request $request, $query, $closure = null)
    {
        // 1ページあたりの件数
        $countPage = config('appconf.page_count');

        // 念の為pageのバリデートもする(数値のみ)
        Validator::make($request->all(), [
            'page' => ['integer'],
        ])->validate();

        // ページ数を取得
        $page = $request->input('page', 1);

        if (!filled($page)) {
            // 空白の場合を考慮
            $page = 1;
        }

        // オフセット
        $offset = ($page * $countPage) - $countPage;

        // クエリの使い回しができないのでクローン
        $query2 = clone $query;

        // 一覧に表示する分だけ取得する
        $items = $query
            ->offset($offset)
            ->limit($countPage)
            ->get();

        // トータル件数の取得
        // 直接カウントは取得できないのでサブクエリとしてカウントを取得
        // from句にクエリを指定している感じ。orderbyもgroupbyも問題なくなる
        $itemCount = DB::table($query2)->select(
            DB::raw('count(1) as count')
        )->first();

        // 取得データの加工
        if ($closure) {
            $items = $closure($items);
        }

        // ページャ取得
        $paginator = new LengthAwarePaginator(
            // 表示するデータ
            $items,
            // 件数
            $itemCount->count,
            // 1ページあたりの件数
            $countPage,
            // 現在のページ数
            $page,
        );

        // ページャを取得する
        $window = UrlWindow::make($paginator);
        $elements = array_filter([
            $window['first'],
            is_array($window['slider']) ? '...' : null,
            $window['slider'],
            is_array($window['last']) ? '...' : null,
            $window['last'],
        ]);

        $this->debug($items);
        // 結果を返却
        return ['paginator' => $paginator, 'elements' => $elements];
    }

    /**
     * 検索結果一覧をページャ付きで取得する(モック用)
     * 
     * @return array ページャの結果
     */
    protected function getListAndPaginatorMock()
    {
        // 1ページあたりの件数
        $countPage = config('appconf.page_count');

        // ページャへのパラメータを固定で設定
        $page = 1;
        $items = null;
        $itemCount = 1;

        // ページャ取得
        $paginator = new LengthAwarePaginator(
            // 表示するデータ
            $items,
            // 件数
            $itemCount,
            // 1ページあたりの件数
            $countPage,
            // 現在のページ数
            $page,
        );

        // ページャを取得する
        $window = UrlWindow::make($paginator);
        $elements = array_filter([
            $window['first'],
            is_array($window['slider']) ? '...' : null,
            $window['slider'],
            is_array($window['last']) ? '...' : null,
            $window['last'],
        ]);

        // 結果を返却
        return ['paginator' => $paginator, 'elements' => $elements];
    }

    //------------------------------
    // バリデーション
    //------------------------------

    /**
     * リクエストのIDの数値・必須チェック
     * getData, edit, delete, execModalなど
     * 
     * @param  mixed  $ids idが格納されている変数(複数指定可能)
     */
    protected function validateIds(...$ids)
    {
        // LaravelのValidationを使う
        $rules = array();

        // IDを追加。key名は任意
        $arrCheck = [];
        $keyNum = 0;
        foreach ($ids as $id) {

            // キーを作成
            $keyNum++;
            $key = 'key-' . $keyNum;

            // 数値、必須チェック
            $rules += [$key => ['required', 'integer']];
            // チェック対象を配列に格納
            $arrCheck[$key] = $id;
        }

        // バリデーション
        $validator = Validator::make($arrCheck, $rules);
        if ($validator->fails()) {
            // エラー時。エラー時は不正な値としてエラーレスポンスを返却
            $this->illegalResponseErr();
        }
    }

    /**
     * リクエストの中のIDの数値・必須チェック
     * getData, edit, delete, execModalなど
     * 
     * @param  mixed  $request チェック対象のリクエスト
     * @param  mixed  $ids idのキー名(複数指定可能)
     */
    protected function validateIdsFromRequest(Request $request, ...$ids)
    {
        $arrCheck = [];
        foreach ($ids as $id) {
            // チェック対象を配列に格納
            $arrCheck[] = $request[$id];
        }

        // 可変引数で呼び出す
        $this->validateIds(...$arrCheck);
    }

    /**
     * リクエストの日付・必須チェック
     * getData, edit, delete, execModalなど
     * 
     * @param  mixed  $dates dateが格納されている変数(複数指定可能)
     */
    protected function validateDates(...$dates)
    {
        // LaravelのValidationを使う
        $rules = array();

        // IDを追加。key名は任意
        $arrCheck = [];
        $keyNum = 0;
        foreach ($dates as $date) {

            // キーを作成
            $keyNum++;
            $key = 'key-' . $keyNum;

            // 日付、必須チェック
            $rules += [$key => ['required', 'date_format:Y-m-d']];
            // チェック対象を配列に格納
            $arrCheck[$key] = $date;
        }

        // バリデーション
        $validator = Validator::make($arrCheck, $rules);
        if ($validator->fails()) {
            // エラー時。エラー時は不正な値としてエラーレスポンスを返却
            $this->illegalResponseErr();
        }
    }

    /**
     * リクエストの中の日付・必須チェック
     * getData, edit, delete, execModalなど
     * 
     * @param  mixed  $request チェック対象のリクエスト
     * @param  mixed  $dates dateのキー名(複数指定可能)
     */
    protected function validateDatesFromRequest(Request $request, ...$dates)
    {
        $arrCheck = [];
        foreach ($dates as $date) {
            // チェック対象を配列に格納
            $arrCheck[] = $request[$date];
        }

        // 可変引数で呼び出す
        $this->validateDates(...$arrCheck);
    }

    //------------------------------
    // 変換
    //------------------------------

    /**
     * Ym形式の値を、Y-m-dに変換する
     * 
     * @param int $ym Ym形式の日付
     */
    protected function fmYmToDate($ym)
    {

        if (strlen($ym) == 6) {
            // Ym→Y-m-d
            $date = substr($ym, 0, 4) . '-' . substr($ym, -2) . '-01';
        } else {
            // エラー
            $this->illegalResponseErr();
        }

        // 日付のバリデーション
        $this->validateDates($date);

        return $date;
    }

    /**
     * オブジェクトを配列に変換
     * プルダウンのリストを非同期で返却する際に使用する。
     * (オブジェクトのまま返却すると並び順が変わってしまうため)
     * 
     * @param object $obj
     * @return array 配列
     */
    function objToArray($obj)
    {
        $rtn = [];
        foreach ($obj as $value) {
            $rtn[] = $value;
        }
        return $rtn;
    }
}
