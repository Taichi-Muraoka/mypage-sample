<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\NoticeTemplate;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use Illuminate\Support\Facades\Lang;

/**
 * お知らせ定型文登録 - コントローラ
 */
class NoticeTemplateController extends Controller
{

    // MEMO: 教室管理者であっても全てのデータを閲覧・変更可能なのでガードは不要

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
        return view('pages.admin.notice_template');
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
        $query = NoticeTemplate::query();

        // お知らせ定型文取得
        $templateList = $query->select(
            'template_id',
            'notice_templates.order_code',
            'template_name',
            'title',
            'mst_codes.name as type_name'
        )
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('notice_templates.notice_type', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_14);
            })
            ->orderBy('notice_templates.order_code')
            ->orderBy('template_id');

        // ページネータで返却
        return $this->getListAndPaginator($request, $templateList);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return mixed 詳細
     */
    public function getData(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'template_id');

        // クエリ作成
        $query = NoticeTemplate::query();

        // お知らせ定型文取得
        $templateList = $query->select(
            'template_name',
            'title',
            'text',
            'mst_codes.name as type_name',
            'notice_templates.order_code'
        )
            ->where('template_id', $request->template_id)
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('notice_templates.notice_type', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_14);
            })
            ->firstOrFail();

        return $templateList;
    }

    //==========================
    // 登録・編集
    //==========================

    /**
     * 登録画面
     *
     * @return view
     */
    public function new()
    {
        // お知らせ種別のプルダウン取得
        $typeList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_14);

        // 表示順の最大値 ＋１の値を表示させる
        $editData = [
            'order_code' => NoticeTemplate::max('order_code') + 1
        ];

        // 表示順の最大値が9999の場合表示順の初期値は9999にする
        if (NoticeTemplate::max('order_code') >= 9999) {
            $editData = [
                'order_code' => NoticeTemplate::max('order_code')
            ];
        }

        // テンプレートは編集と同じ
        return view('pages.admin.notice_template-input', [
            'editData' => $editData,
            'rules' => $this->rulesForInput(),
            'typeList' => $typeList
        ]);
    }

    /**
     * 登録処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function create(Request $request)
    {
        // 登録前バリデーション。NGの場合はレスポンスコード422を返却
        Validator::make($request->all(), $this->rulesForInput())->validate();

        $form = $request->only(
            'template_name',
            'title',
            'text',
            'notice_type',
            'order_code'
        );

        // 保存
        $noticeTemplate = new NoticeTemplate;
        $noticeTemplate->fill($form)->save();

        return;
    }

    /**
     * 編集画面
     *
     * @param int $templateId 定型文Id
     * @return view
     */
    public function edit($templateId)
    {
        // IDのバリデーション
        $this->validateIds($templateId);

        // お知らせ種別のプルダウン取得
        $typeList = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_14);

        // クエリ作成
        $query = NoticeTemplate::query();

        // お知らせ定型文取得
        $noticeTemplate = $query->select(
            'template_id',
            'template_name',
            'title',
            'text',
            'notice_type',
            'order_code',
        )
            ->where('template_id', $templateId)
            ->firstOrFail();

        return view('pages.admin.notice_template-input', [
            'editData' => $noticeTemplate,
            'rules' => $this->rulesForInput(),
            'typeList' => $typeList
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

        $form = $request->only(
            'template_id',
            'template_name',
            'title',
            'text',
            'notice_type',
            'order_code'
        );

        // 対象データを取得(IDでユニークに取る)
        $contact = NoticeTemplate::where('template_id', $form['template_id'])
            // 該当データがない場合はエラーを返す
            ->firstOrFail();

        // 登録
        $contact->fill($form)->save();

        return;
    }

    /**
     * 削除処理
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return void
     */
    public function delete(Request $request)
    {
        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'template_id');

        $form = $request->only(
            'template_id'
        );

        // 削除対象データの取得
        $contact = NoticeTemplate::where('template_id', $form['template_id'])
            ->firstOrFail();

        // 削除
        $contact->delete();
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

        // 独自バリデーション: リストのチェック お知らせ種別
        $validationTypeList =  function ($attribute, $value, $fail) {

            // お知らせ種別のプルダウン取得
            $type = $this->mdlMenuFromCodeMaster(AppConst::CODE_MASTER_14);
            if (!isset($type[$value])) {
                // 不正な値エラー
                return $fail(Lang::get('validation.invalid_input'));
            }
        };

        // 独自バリデーション: 最大4桁のバリデーション

        $rules += NoticeTemplate::fieldRules('template_id');
        $rules += NoticeTemplate::fieldRules('order_code', ['required']);
        $rules += NoticeTemplate::fieldRules('template_name', ['required']);
        $rules += NoticeTemplate::fieldRules('title', ['required']);
        $rules += NoticeTemplate::fieldRules('text', ['required']);
        $rules += NoticeTemplate::fieldRules('notice_type', ['required', $validationTypeList]);

        return $rules;
    }
}
