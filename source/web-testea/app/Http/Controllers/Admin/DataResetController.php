<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use App\Models\BatchMng;
use App\Models\CodeMaster;
use App\Consts\AppConst;
use App\Libs\AuthEx;

/**
 * 保持期限データ削除 - コントローラ
 */
class DataResetController extends Controller
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

        return view('pages.admin.data_reset');
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
        $query = BatchMng::query();

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
            ->where('batch_type', AppConst::CODE_MASTER_23_13)
            ->orderBy('start_time', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $batchList);
    }

    /**
     * バックアップファイルのダウンロード
     *
     * @param int $batchId バッチID
     * @return mixed ファイル
     */
    public function download($batchId)
    {
        // IDのバリデーション
        $this->validateIds($batchId);

        // バックアップ作成したバッチ処理の処理開始日時を取得
        $batch = BatchMng::select('start_time')
            ->where('batch_id', $batchId)
            ->where('batch_state', AppConst::CODE_MASTER_22_0)
            ->firstOrFail();

        // バッチ処理開始日時を14桁の数値に変換
        // $dirName例：20230301000000
        // $fileName例：保持期限超過データ削除バックアップ_20230301000000.zip
        $dirName = preg_replace('/[^0-9]/', '', $batch->start_time);
        $fileName = config('appconf.download_file_name_exceeding_data_backup') . $dirName . '.zip';

        // バックアップ保存場所のパス取得
        $backupDir = config("appconf.download_dir_exceeding_data_backup");
        $filePath = Storage::path($backupDir . $dirName . '/' . $fileName);

        // 存在チェック
        if (!File::exists($filePath)) {
            // 存在しなければエラー
            $this->illegalResponseErr();
        }

        // MIME TYPEの取得
        $mimeType = Storage::mimeType($backupDir . $dirName . '/' . $fileName);
        $headers = [['Content-Type' => $mimeType]];

        return response()->download($filePath, $fileName, $headers);
    }
}
