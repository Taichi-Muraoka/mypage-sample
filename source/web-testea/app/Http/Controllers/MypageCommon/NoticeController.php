<?php

namespace App\Http\Controllers\MypageCommon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notice;
use App\Models\AdminUser;
use App\Consts\AppConst;
use App\Http\Controllers\Traits\FuncNoticeTrait;

/**
 * お知らせ - コントローラ
 */
class NoticeController extends Controller
{

    // 機能共通処理：お知らせ
    use FuncNoticeTrait;

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

        return view('pages.mypage-common.notice');
    }

    /**
     * 検索結果取得
     *
     * @param \Illuminate\Http\Request $request リクエスト
     * @return array 検索結果
     */
    public function search(Request $request)
    {

        // ログイン者の情報を取得する。
        $account = Auth::user();
        $account_type = $account->account_type;

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        switch ($account_type) {
            case AppConst::CODE_MASTER_7_1:
                //---------------
                // 生徒
                //---------------

                // お知らせIDの取得
                $notice_ids = $this->getStudentNoticeIds();

                // 生徒情報の取得
                $student = $this->getStudentInfo();

                // 生徒入会日
                $enter_date = $student->enter_date;

                // お知らせIDから生徒入会日以降のお知らせを取得する。
                $query = Notice::query();
                $notices = $query
                    ->select(
                        'notices.notice_id as id',
                        'title',
                        'notice_type',
                        'regist_time as date',
                        'admin_users.name as sender',
                        'room_name'
                    )
                    // 送信者名の取得
                    ->sdLeftJoin(AdminUser::class, 'admin_users.adm_id', '=', 'notices.adm_id')
                    // 教室名の取得
                    ->leftJoinSub($room_names, 'room_names', function ($join) {
                        $join->on('notices.campus_cd', '=', 'room_names.code');
                    })
                    // 対象のお知らせIDで絞る
                    ->whereIn('notices.notice_id', $notice_ids)
                    // 入会日がある場合は、入会日以降のデータを取得
                    ->when($enter_date, function ($query, $enter_date) {
                        return $query->where('notices.regist_time', '>=', $enter_date);
                    })
                    // ソート順
                    ->orderBy('notices.regist_time', 'desc');

                break;
            case AppConst::CODE_MASTER_7_2:
                //---------------
                // 教師
                //---------------

                // お知らせIDの取得
                $notice_ids = $this->getTutorNoticeIds();

                // お知らせIDからお知らせを取得する。
                $query = Notice::query();
                $notices = $query
                    ->select(
                        'notices.notice_id as id',
                        'title',
                        'notice_type',
                        //'tmid_event_id',
                        'regist_time as date',
                        'admin_users.name as sender',
                        'room_name'
                    )
                    // 送信者名の取得
                    ->sdLeftJoin(AdminUser::class, 'admin_users.adm_id', '=', 'notices.adm_id')
                    // 教室名の取得
                    ->leftJoinSub($room_names, 'room_names', function ($join) {
                        $join->on('notices.campus_cd', '=', 'room_names.code');
                    })
                    // 対象のお知らせIDで絞る
                    ->whereIn('notices.notice_id', $notice_ids)
                    // ソート順
                    ->orderBy('notices.regist_time', 'desc');

                break;
            default:
                // 該当しない場合
                $this->illegalResponseErr();
        }

        // ページネータで返却
        return $this->getListAndPaginator($request, $notices);
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

        // モーダルによって処理を行う
        $modal = $request->input('target');
        $notice_id = $request->input('id');

        // [ガード] お知らせIDが自分が見れるIDかチェックする
        $this->guardNoticeId($notice_id);

        // 教室名取得のサブクエリ
        $room_names = $this->mdlGetRoomQuery();

        switch ($modal) {
            case "#modal-dtl":
                //---------------
                // 共通（テスティ―ではこのルートのみ使用する）
                //---------------

                // お知らせIDからお知らせを取得する。
                $query = Notice::query();
                $notice = $query
                    ->select(
                        'regist_time as date',
                        'title',
                        'text as body',
                        'notice_type as type',
                        'admin_users.name as sender',
                        'room_name'
                    )
                    // 送信者名の取得
                    ->sdLeftJoin(AdminUser::class, 'admin_users.adm_id', '=', 'notices.adm_id')
                    // 教室名の取得
                    ->leftJoinSub($room_names, 'room_names', function ($join) {
                        $join->on('notices.campus_cd', '=', 'room_names.code');
                    })
                    ->where('notices.notice_id', '=', $notice_id)
                    ->firstOrFail();

                    return $notice;
        
            default:
                //---------------
                // 該当しない場合
                //---------------
                $this->illegalResponseErr();
        }
    }
}
