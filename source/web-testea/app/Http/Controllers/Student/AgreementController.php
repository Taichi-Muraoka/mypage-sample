<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Traits\FuncAgreementTrait;

/**
 * 契約内容 - コントローラ
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

        // 生徒の契約内容を取得
        $agreement = $this->getStudentAgreement($account_id);

        return view('pages.student.agreement', $agreement);
    }

    /**
     * 詳細取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 詳細データ
     */
    public function getData(Request $request)
    {
        //==========================
        // モック用処理
        //==========================
        return;

        //==========================
        // 本番処理
        //==========================
        // // MEMO: ログインアカウントのIDでデータを取得するのでガードは不要
        // // seqに関するガードはFuncAgreementTraitの処理の中でした

        // // IDのバリデーション
        // $this->validateIdsFromRequest($request, 'roomcd');

        // // モーダルによって処理を行う
        // $modal = $request->input('target');

        // // ログイン情報
        // $account = Auth::user();
        // $account_id = $account->account_id;
        // $sid = $account_id;

        // // MEMO: sidを条件にするのでroomcdに対するガードは不要
        // $roomcd = $request->input('roomcd');

        // switch ($modal) {
        //     case "#modal-dtl-regulation":

        //         // IDのバリデーション
        //         $this->validateIdsFromRequest($request, 'r_seq');
        //         $r_seq = $request->input('r_seq');

        //         // 規定情報を取得する
        //         return $this->getStudentRegular($sid, $roomcd, $r_seq);

        //     case "#modal-dtl-tutor":

        //         // IDのバリデーション
        //         $this->validateIdsFromRequest($request, 'std_seq');
        //         $std_seq = $request->input('std_seq');

        //         // 家庭教師標準情報を取得する
        //         return $this->getStudentHomeTeacherStd($sid, $roomcd, $std_seq);

        //     case "#modal-dtl-course":

        //         // IDのバリデーション
        //         $this->validateIdsFromRequest($request, 'i_seq');
        //         $i_seq = $request->input('i_seq');

        //         // 短期個別講習を取得する
        //         return $this->getStudentExtraIndividual($sid, $roomcd, $i_seq);

        //     default:
        //         // 該当しない場合
        //         $this->illegalResponseErr();
        // }
    }
}
