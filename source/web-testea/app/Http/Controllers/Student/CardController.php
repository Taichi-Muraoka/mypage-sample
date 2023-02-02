<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Card;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\FuncCardTrait;

/**
 * ギフトカード - コントローラ
 */
class CardController extends Controller
{

    // 機能共通処理：カード
    use FuncCardTrait;

    /**
     * コンストラクタ
     *
     * @return void
     */
    public function __construct()
    {
    }

    //==========================
    // 一覧
    //==========================

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {

        return view('pages.student.card');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // クエリ作成
        $query = Card::query();

        // 一覧に表示する項目を取得
        $cardList = $query->select(
            'card_id',
            'grant_time',
            'card_name',
            'term_start',
            'term_end',
            'card_state as state',
            'code_master.name as card_state'
        )
            // 状態の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('card.card_state', '=', 'code_master.code')
                    ->where('data_type', AppConst::CODE_MASTER_4);
            })
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // ソート
            ->orderBy('grant_time', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $cardList, function ($items) {
            // 本日の日付を取得
            $today = Carbon::today();

            // ギフトカードが申請済み又は使用期間外にある時、「使用」ボタンを非活性
            foreach ($items as $item) {

                // 開始日と終了日のCarbonインスタンスを取得
                $termStart = $item->term_start;
                $termEnd = $item->term_end;

                if ($item->state > AppConst::CODE_MASTER_4_0 || !$today->between($termStart, $termEnd)) {
                    // 使用ボタンを非活性
                    $item['disabled'] = true;
                } else {
                    // 使用可能
                    $item['disabled'] = false;
                }
            }

            return $items;
        });
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細データ
     */
    public function getData(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'id');

        // IDを取得
        $id = $request->input('id');

        // クエリを作成
        $query = Card::query();

        // データを取得
        $cardInfo = $query
            ->select(
                'apply_time',
                'grant_time',
                'card_name',
                'discount',
                'term_start',
                'term_end',
                'code_master.name as card_state',
                'comment'
            )
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // キー
            ->where('card_id', $id)
            // 状態の取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('card.card_state', '=', 'code_master.code')
                    ->where('data_type', AppConst::CODE_MASTER_4);
            })
            ->firstOrFail();

        return $cardInfo;
    }

    //==========================
    // 申請
    //==========================

    /**
     * 申請画面
     *
     * @param int $cardId カードID
     * @return view
     */
    public function use($cardId)
    {
        // IDのバリデーション
        $this->validateIds($cardId);

        // クエリ作成
        $query = Card::query();

        // データを取得
        $editData = $query
            ->select(
                'card_id',
                'grant_time',
                'card_name',
                'discount',
                'term_start',
                'term_end'
            )
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // キー
            ->where('card_id', $cardId)
            // 使用できるかチェック(日付の範囲チェックのため特別rawを使用)
            ->where('card_state', AppConst::CODE_MASTER_4_0)
            ->whereBetween(DB::raw('date(NOW())'), [DB::raw('term_start'), DB::raw('term_end')])
            ->firstOrFail();

        return view('pages.student.card-use', [
            'rules' => $this->rulesForInput(),
            'editData' => $editData
        ]);
    }

    /**
     * 編集処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function update(Request $request)
    {

        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        // 更新対象のIDを取得
        $form = $request->only(
            'card_id'
        );

        // 本日のCarbonインスタンスを取得
        $today = Carbon::now();

        // 登録する申請日とステータスを配列化
        $data = [
            'card_id' => $form['card_id'],
            'apply_time' => $today,
            'card_state' => AppConst::CODE_MASTER_4_1
        ];

        // 対象データを取得(IDでユニークに取る)
        $card = Card::where('card_id', $form['card_id'])
            // 自分の生徒IDのみにガードを掛ける
            ->where($this->guardStudentTableWithSid())
            // 使用できるかチェック(日付の範囲チェックのため特別rawを使用)
            ->where('card_state', AppConst::CODE_MASTER_4_0)
            ->whereBetween(DB::raw('date(NOW())'), [DB::raw('term_start'), DB::raw('term_end')])
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 保存
        $card->fill($data)->save();

        return;
    }

    /**
     * バリデーション(登録用)
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed バリデーション結果
     */
    public function validationForInput(Request $request)
    {
        // リクエストデータチェック
        $validator = Validator::make($request->all(), $this->rulesForInput());
        return $validator->errors();
    }

    /**
     * バリデーションルールを取得(登録用)
     *
     * @return array ルール
     */
    private function rulesForInput()
    {
        $rules = array();

        // MEMO: 不正アクセス対策として、card_idもルールに追加する
        $rules += Card::fieldRules('card_id', ['required']);

        return $rules;
    }
}
