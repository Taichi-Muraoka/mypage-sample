<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BatchMng;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use App\Libs\AuthEx;

/**
 * 学年更新 - コントローラ
 */
class GradeUpdateController extends Controller
{
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
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {
        // クエリ作成
        $query = BatchMng::query();

        // バッヂリスト取得
        $batchList = $query
            ->select(
                'batch_id',
                'start_time',
                'end_time',
                'batch_state',
                // コードマスタの名称（バッチ状態）
                'mst_codes.name as state_name',
            )
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('batch_state', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_22);
            })
            // バッチ種別で絞り込み
            ->where('batch_type', AppConst::CODE_MASTER_23_11)
            ->orderBy('start_time', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $batchList);
    }

    /**
     * 初期画面
     *
     * @return view
     */
    public function index()
    {
        // 教室管理者の場合は画面を表示しない
        if (AuthEx::isRoomAdmin()) {
            return $this->illegalResponseErr();
        }

        return view('pages.admin.grade_update');
    }

}
