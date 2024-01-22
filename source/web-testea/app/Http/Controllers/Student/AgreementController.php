<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Traits\FuncAgreementTrait;
use App\Models\Badge;
use App\Models\MstCampus;

/**
 * 生徒情報（旧；契約内容） - コントローラ
 */
class AgreementController extends Controller
{

    // 機能共通処理：契約内容
    use FuncAgreementTrait;

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
        // MEMO: ログインアカウントのIDでデータを取得するのでガードは不要

        // ログイン者の生徒No.を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        // 生徒情報を取得
        $agreement = $this->fncAgreGetStudentDetail($account_id);

        return view('pages.student.agreement', $agreement);
    }

    /**
     * 詳細取得（バッジ付与情報）
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getData(Request $request)
    {
        // MEMO: ログインアカウントのIDでデータを取得するのでガードは不要

        // IDのバリデーション
        $this->validateIdsFromRequest($request, 'student_id');

        // ログイン情報
        $account = Auth::user();
        $account_id = $account->account_id;
        $sid = $account_id;

        // クエリ作成
        $query = Badge::query();

        // データを取得
        $badgeList = $query
            ->select(
                'badges.badge_id',
                'badges.student_id',
                'badges.campus_cd',
                // 校舎マスタの名称
                'mst_campuses.name as campus_name',
                'badges.reason',
                'badges.authorization_date',
            )
            ->where('badges.student_id', '=', $sid)
            ->sdLeftJoin(MstCampus::class, 'badges.campus_cd', '=', 'mst_campuses.campus_cd')
            ->orderBy('badges.authorization_date', 'desc')
            ->get();

        return ['badgeList' => $badgeList];
    }
}
