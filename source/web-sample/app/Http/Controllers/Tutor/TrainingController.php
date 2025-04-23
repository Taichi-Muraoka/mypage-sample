<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Consts\AppConst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\TrainingContent;
use App\Models\TrainingBrowse;
use App\Models\CodeMaster;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Traits\FuncTrainingTrait;

/**
 * 研修受講 - コントローラ
 */
class TrainingController extends Controller
{
    // 機能共通処理：研修
    use FuncTrainingTrait;

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

        return view('pages.tutor.training');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {

        // ログイン者の教師No.を取得する。
        $account_id = Auth::user()->account_id;

        // 現在日を取得
        $today = date("Y/m/d");

        $query = TrainingContent::query();
        $trainings = $query
            ->select(
                'training_contents.trn_id AS id',
                'mst_codes.name AS trn_type_name',
                'text',
                'release_date',
                'limit_date',
                'training_browses.browse_time',
                'training_contents.created_at'
            )
            // 閲覧状況を取得
            ->sdLeftJoin(TrainingBrowse::class, function ($join) use ($account_id) {
                $join->on('training_browses.trn_id', '=', 'training_contents.trn_id')
                    ->where('training_browses.tutor_id', '=', $account_id);
            })
            // 形式名を取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('training_contents.trn_type', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_12);
            })
            ->where('release_date', '<=', $today)
            // 期限日が当日以降かまたは無期限を抽出する
            ->where(function ($orQuery) use ($today) {
                $orQuery
                    ->where('limit_date', '>=', $today)
                    ->orWhereNull('limit_date');
            })
            ->orderBy('release_date', 'desc')
            ->orderBy('created_at', 'desc');

        // ページネータで返却
        return $this->getListAndPaginator($request, $trainings);
    }

    //==========================
    // 受講
    //==========================

    /**
     * 受講画面
     *
     * @param int $trnId 研修ID
     * @return view
     */
    public function detail($trnId)
    {

        // IDのバリデーション
        $this->validateIds($trnId);

        // 現在日を取得
        $today = date("Y/m/d");

        // 詳細取得。公開日が来ていないもの・期限日を過ぎているものはエラーにする
        $query = TrainingContent::query();
        $training = $query
            ->select(
                'trn_type',
                'mst_codes.name AS trn_type_name',
                'text',
                'url',
                'release_date',
                'limit_date'
            )
            // 形式名を取得
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('training_contents.trn_type', '=', 'mst_codes.code')
                    ->where('mst_codes.data_type', AppConst::CODE_MASTER_12);
            })
            ->where('trn_id', '=', $trnId)
            ->where('release_date', '<=', $today)
            // 期限日が当日以降かまたは無期限を抽出する
            ->where(function ($orQuery) use ($today) {
                $orQuery
                    ->where('limit_date', '>=', $today)
                    ->orWhereNull('limit_date');
            })
            ->firstOrFail();

        return view('pages.tutor.training-detail', [
            'training' => $training,
            'editData' => [
                'trn_id' => $trnId
            ]
        ]);
    }

    /**
     * 動画の閲覧更新
     *
     * @param int $trnId 研修ID
     * @return void
     */
    public function movieBrowse(Request $request)
    {

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'trn_id');

        $trn_id = $request->input('trn_id');

        // 現在日を取得
        $today = date("Y/m/d");

        // 公開日前・期限日を過ぎているものはエラーを返す
        TrainingContent::where('trn_id', '=', $trn_id)
            ->where('trn_type', '=', AppConst::CODE_MASTER_12_2)
            ->where('release_date', '<=', $today)
            ->where(function ($orQuery) use ($today) {
                $orQuery
                    ->where('limit_date', '>=', $today)
                    ->orWhereNull('limit_date');
            })
            ->firstOrFail();

        // ログイン者の教師No.を取得する。
        $account_id = Auth::user()->account_id;

        // すでに閲覧しているか確認
        $exists = TrainingBrowse::where('trn_id', '=', $trn_id)
            ->where('tutor_id', '=', $account_id)
            ->exists();

        // 未閲覧だった場合、レコード作成
        if (!$exists) {
            // 保存
            $training_browse = new TrainingBrowse;
            $training_browse->trn_id = $trn_id;
            $training_browse->tutor_id = $account_id;
            $training_browse->browse_time = Carbon::now();
            $training_browse->save();
        }

        return;
    }

    /**
     * 資料のダウンロード
     *
     * @param int $trnId 研修ID
     * @return mixed ファイル
     */
    public function download($trnId)
    {

        // IDのバリデーション
        $this->validateIds($trnId);

        // 現在日を取得
        $today = date("Y/m/d");

        // 公開日前・期限日を過ぎているものはエラーを返す
        $training = TrainingContent::select('url')
            ->where('trn_id', '=', $trnId)
            ->where('trn_type', '=', AppConst::CODE_MASTER_12_1)
            ->where('release_date', '<=', $today)
            ->where(function ($orQuery) use ($today) {
                $orQuery
                    ->where('limit_date', '>=', $today)
                    ->orWhereNull('limit_date');
            })
            ->firstOrFail();

        // ファイル名を変換(iPhoneでもPCでも日本語ファイル名でダウンロードできた)
        $fileName = $training->url;
        $fileName = $this->convFileName($fileName);

        // パスの取得
        $uploadDir = config("appconf.upload_dir_training") . $trnId . '/';
        $filePath = Storage::path($uploadDir . $fileName);

        // 存在チェック
        if (!File::exists($filePath)) {
            // エラー
            $this->illegalResponseErr();
        }

        // MIME TYPEの取得
        $mimeType = Storage::mimeType($uploadDir . $fileName);
        $headers = [['Content-Type' => $mimeType]];

        // ログイン者の教師No.を取得する。
        $account_id = Auth::user()->account_id;

        // すでに閲覧しているか確認
        $exists = TrainingBrowse::where('trn_id', '=', $trnId)
            ->where('tutor_id', '=', $account_id)
            ->exists();

        // 未閲覧だった場合、レコード作成
        if (!$exists) {
            // 保存
            $training_browse = new TrainingBrowse;
            $training_browse->trn_id = $trnId;
            $training_browse->tutor_id = $account_id;
            $training_browse->browse_time = Carbon::now();
            $training_browse->save();
        }

        return response()->download($filePath, $fileName, $headers);
    }
}
