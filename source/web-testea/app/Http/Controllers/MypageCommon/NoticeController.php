<?php

namespace App\Http\Controllers\MypageCommon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ExtStudentKihon;
use App\Models\ExtRoom;
use App\Models\NoticeDestination;
use App\Models\Notice;
use App\Models\NoticeGroup;
use App\Models\Office;
use App\Models\ExtTrialMaster;
use App\Models\Event;
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
                $student = $this->getStudentKihon();

                // 生徒入会日
                $enter_date = $student->enter_date;

                // お知らせIDから生徒入会日以降のお知らせを取得する。
                $query = Notice::query();
                $notices = $query
                    ->select(
                        'notice.notice_id AS id',
                        'title',
                        'notice_type',
                        'tmid_event_id',
                        'regist_time AS date',
                        'office.name AS sender',
                        'room_name'
                    )
                    // 送信者名の取得
                    ->sdLeftJoin(Office::class, 'office.adm_id', '=', 'notice.adm_id')
                    // 教室名の取得
                    ->leftJoinSub($room_names, 'room_names', function ($join) {
                        $join->on('notice.roomcd', '=', 'room_names.code');
                    })
                    // 対象のお知らせIDで絞る
                    ->whereIn('notice.notice_id', $notice_ids)
                    // 入会日がある場合は、入会日以降のデータを取得
                    ->when($enter_date, function ($query, $enter_date) {
                        return $query->where('notice.regist_time', '>=', $enter_date);
                    })
                    // ソート順
                    ->orderBy('notice.regist_time', 'desc');

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
                        'notice.notice_id AS id',
                        'title',
                        'notice_type',
                        'tmid_event_id',
                        'regist_time AS date',
                        'office.name AS sender',
                        'room_name'
                    )
                    // 送信者名の取得
                    ->sdLeftJoin(Office::class, 'office.adm_id', '=', 'notice.adm_id')
                    // 教室名の取得
                    ->leftJoinSub($room_names, 'room_names', function ($join) {
                        $join->on('notice.roomcd', '=', 'room_names.code');
                    })
                    // 対象のお知らせIDで絞る
                    ->whereIn('notice.notice_id', $notice_ids)
                    // ソート順
                    ->orderBy('notice.regist_time', 'desc');

                break;
            default:
                // 該当しない場合
                $this->illegalResponseErr();
        }

        // ページネータで返却
        return $this->getListAndPaginator($request, $notices, function ($items) use ($account_type) {
            if ($account_type == AppConst::CODE_MASTER_7_1) {
                foreach ($items as $item) {
                    if ($item->notice_type == AppConst::CODE_MASTER_14_1 or $item->notice_type == AppConst::CODE_MASTER_14_2) {
                        $item['flg'] = 'event';
                    } else if ($item->notice_type == AppConst::CODE_MASTER_14_3) {
                        $item['flg'] = 'course';
                    } else {
                        $item['flg'] = 'absent';
                    }
                }
            } else {
                foreach ($items as $item) {
                    $item['flg'] = 'absent';
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
        return ['id' => $request->id];

    //---------------
    // 本番用
    //---------------
        // // IDのバリデーション
        // $this->validateIdsFromRequest($request, 'id');

        // // モーダルによって処理を行う
        // $modal = $request->input('target');
        // $notice_id = $request->input('id');

        // // [ガード] お知らせIDが自分が見れるIDかチェックする
        // $this->guardNoticeId($notice_id);

        // // 教室名取得のサブクエリ
        // $room_names = $this->mdlGetRoomQuery();

        // switch ($modal) {
        //     case "#modal-dtl-event":
        //         //---------------
        //         // イベント・模試
        //         //---------------

        //         // お知らせIDからお知らせを取得する。
        //         $query = Notice::query();
        //         $notice = $query
        //             ->select(
        //                 'regist_time AS date',
        //                 'title',
        //                 'text AS body',
        //                 'notice_type AS type',
        //                 'tmid_event_id AS id',
        //                 'office.name AS sender',
        //                 'room_name'
        //             )
        //             // 送信者名の取得
        //             ->sdLeftJoin(Office::class, 'office.adm_id', '=', 'notice.adm_id')
        //             // 教室名の取得
        //             ->leftJoinSub($room_names, 'room_names', function ($join) {
        //                 $join->on('notice.roomcd', '=', 'room_names.code');
        //             })
        //             ->where('notice.notice_id', '=', $notice_id)
        //             ->firstOrFail();

        //         $notice['tmid_event_name'] = '';
        //         $notice['tmid_event_date'] = '';

        //         if ($notice->type === AppConst::CODE_MASTER_14_1) {
        //             //---------------
        //             // 模試
        //             //---------------

        //             // 模試詳細の取得
        //             $query = ExtTrialMaster::query();
        //             $trial = $query
        //                 ->select(
        //                     'name',
        //                     'trial_date'
        //                 )
        //                 ->where('ext_trial_master.tmid', '=', $notice->id)
        //                 ->firstOrFail();

        //             if (isset($trial['name']) && isset($trial['trial_date'])) {
        //                 $notice['tmid_event_name'] = $trial->name;
        //                 $notice['tmid_event_date'] = $trial->trial_date;
        //             }
        //         } elseif ($notice->type === AppConst::CODE_MASTER_14_2) {
        //             //---------------
        //             // イベント
        //             //---------------

        //             // イベント詳細の取得
        //             $query = Event::query();
        //             $event = $query
        //                 ->select(
        //                     'name',
        //                     'event_date'
        //                 )
        //                 ->where('event.event_id', '=', $notice->id)
        //                 ->firstOrFail();

        //             if (isset($event['name']) && isset($event['event_date'])) {
        //                 $notice['tmid_event_name'] = $event->name;
        //                 $notice['tmid_event_date'] = $event->event_date;
        //             }
        //         } else {
        //             // エラー
        //             $this->illegalResponseErr();
        //         }

        //         return $notice;

        //     case "#modal-dtl-absent":
        //         //---------------
        //         // 欠席申請
        //         //---------------

        //         // お知らせIDからお知らせを取得する。
        //         $query = Notice::query();
        //         $notice = $query
        //             ->select(
        //                 'regist_time AS date',
        //                 'title',
        //                 'text AS body',
        //                 'office.name AS sender',
        //                 'room_name'
        //             )
        //             // 送信者名の取得
        //             ->sdLeftJoin(Office::class, 'office.adm_id', '=', 'notice.adm_id')
        //             // 教室名の取得
        //             ->leftJoinSub($room_names, 'room_names', function ($join) {
        //                 $join->on('notice.roomcd', '=', 'room_names.code');
        //             })
        //             ->where('notice.notice_id', '=', $notice_id)
        //             ->firstOrFail();

        //         return $notice;

        //     case "#modal-dtl-course":
        //         //---------------
        //         // 個別講習
        //         //---------------

        //         // お知らせIDからお知らせを取得する。
        //         $query = Notice::query();
        //         $notice = $query
        //             ->select(
        //                 'regist_time AS date',
        //                 'title',
        //                 'text AS body',
        //                 'office.name AS sender',
        //                 'room_name'
        //             )
        //             // 送信者名の取得
        //             ->sdLeftJoin(Office::class, 'office.adm_id', '=', 'notice.adm_id')
        //             // 教室名の取得
        //             ->leftJoinSub($room_names, 'room_names', function ($join) {
        //                 $join->on('notice.roomcd', '=', 'room_names.code');
        //             })
        //             ->where('notice.notice_id', '=', $notice_id)
        //             ->firstOrFail();

        //         return $notice;

        //     case "#modal-dtl":
        //         //---------------
        //         // 共通（テスティ―ではこのルートのみ使用する）
        //         //---------------

        //         // お知らせIDからお知らせを取得する。
        //         $query = Notice::query();
        //         $notice = $query
        //             ->select(
        //                 'regist_time AS date',
        //                 'title',
        //                 'text AS body',
        //                 'notice_type AS type',
        //                 'office.name AS sender',
        //                 'room_name'
        //             )
        //             // 送信者名の取得
        //             ->sdLeftJoin(Office::class, 'office.adm_id', '=', 'notice.adm_id')
        //             // 教室名の取得
        //             ->leftJoinSub($room_names, 'room_names', function ($join) {
        //                 $join->on('notice.roomcd', '=', 'room_names.code');
        //             })
        //             ->where('notice.notice_id', '=', $notice_id)
        //             ->firstOrFail();

        //             return $notice;
        
        //     default:
        //         //---------------
        //         // 該当しない場合
        //         //---------------
        //         $this->illegalResponseErr();
        // }
    }

    /**
     * 生徒基本情報を取得
     *
     * @return mixed 生徒情報
     */
    private function getStudentKihon()
    {
        // ログイン者の情報を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        // 生徒情報から学年・入会日を取得する。
        $query = ExtStudentKihon::query();
        $student = $query
            ->select(
                'cls_cd',
                'enter_date'
            )
            ->where('ext_student_kihon.sid', '=', $account_id)
            ->firstOrFail();

        return $student;
    }

    /**
     * 対象お知らせIDの取得(生徒)
     *
     * @return array お知らせIDの配列
     */
    private function getStudentNoticeIds()
    {

        // ログイン者の情報を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        // 当年度・前年度の範囲
        $fiscal_start_date = $this->dtGetFiscalDate("present", "start", true);
        $fiscal_end_date = $this->dtGetFiscalDate("present", "end", true);
        $prev_fiscal_start_date = $this->dtGetFiscalDate("prev", "start", true);
        $prev_fiscal_end_date = $this->dtGetFiscalDate("prev", "end", true);

        // 生徒情報から学年・入会日を取得する。
        $student = $this->getStudentKihon();
        $present_cls = $student->cls_cd;

        // 生徒No.から所属教室を取得する。
        $query = ExtRoom::query();
        $rooms = $query
            ->select(
                'roomcd'
            )
            ->where('ext_room.sid', '=', $account_id)
            ->get();

        // 複数所属の場合を考慮し、roomcdを取り出す。
        $roomcds = [];
        foreach ($rooms as $room) {
            array_push($roomcds, $room->roomcd);
        }

        // 生徒の学年からお知らせグループIDを取り出す。
        $query = NoticeGroup::query();
        $notice_groups = $query
            ->select(
                'notice_group_id'
            )
            ->where('notice_group.cls_cd', '=', $present_cls)
            ->first();

        if (isset($notice_groups['notice_group_id'])) {
            $present_group_id = $notice_groups->notice_group_id;
        } else {
            $present_group_id = 0;
        }

        // 前年度のグループIDを取得するため
        $query = NoticeGroup::query();
        $notice_groups_prev = $query
            ->select(
                'notice_group_id',
                'cls_cd'
            )
            ->Where('notice_group.cls_cd_next', '=', $student->cls_cd)
            ->first();

        if (isset($notice_groups_prev['notice_group_id'])) {
            $prev_group_id = $notice_groups_prev->notice_group_id;
        } else {
            $prev_group_id = 0;
        }

        // 宛先情報から対象となる個別のお知らせIDを抽出する。
        $query = NoticeDestination::query();
        $notice_destinations = $query
            ->select(
                'notice_destination.notice_id'
            )
            ->sdLeftJoin(Notice::class, 'notice.notice_id', '=', 'notice_destination.notice_id')
            ->where('notice_destination.destination_type', '=', AppConst::CODE_MASTER_15_2)
            ->where('notice_destination.sid', '=', $account_id)
            ->whereBetween('notice.regist_time', [$prev_fiscal_start_date, $fiscal_end_date])
            ->get();

        // 宛先情報から対象となるグループ一斉のお知らせIDを抽出する。
        $query = NoticeDestination::query();
        $notice_destinations_group = $query
            ->select(
                'notice_destination.notice_id',
                'regist_time'
            )
            ->sdLeftJoin(Notice::class, 'notice.notice_id', '=', 'notice_destination.notice_id')
            ->where('notice_destination.destination_type', '=', AppConst::CODE_MASTER_15_1)
            ->where(function ($orQuery) use ($present_group_id, $prev_group_id, $fiscal_start_date, $fiscal_end_date, $prev_fiscal_start_date, $prev_fiscal_end_date) {
                $orQuery
                    ->where(function ($orQuery) use ($present_group_id, $fiscal_start_date, $fiscal_end_date) {
                        $orQuery
                            ->where('notice_destination.notice_group_id', '=', $present_group_id)
                            ->whereBetween('notice.regist_time', [$fiscal_start_date, $fiscal_end_date]);
                    })
                    ->orWhere(function ($orQuery) use ($prev_group_id, $prev_fiscal_start_date, $prev_fiscal_end_date) {
                        $orQuery
                            ->where('notice_destination.notice_group_id', '=', $prev_group_id)
                            ->whereBetween('notice.regist_time', [$prev_fiscal_start_date, $prev_fiscal_end_date]);
                    });
            })
            ->where(function ($orQuery) use ($roomcds) {
                $orQuery->whereIn('notice_destination.roomcd', $roomcds)
                    ->orWhereNull('notice_destination.roomcd');
            })
            ->get();

        // お知らせIDを抽出する。
        $notice_ids = [];
        if (count($notice_destinations) > 0) {
            foreach ($notice_destinations as $notice) {
                array_push($notice_ids, $notice->notice_id);
            }
        }
        if (count($notice_destinations_group) > 0) {
            foreach ($notice_destinations_group as $notice) {
                array_push($notice_ids, $notice->notice_id);
            }
        }
        $notice_ids = array_unique($notice_ids);
        return $notice_ids;
    }

    /**
     * 対象お知らせIDの取得(教師)
     *
     * @return array お知らせIDの配列
     */
    private function getTutorNoticeIds()
    {
        // ログイン者の情報を取得する。
        $account = Auth::user();
        $account_id = $account->account_id;

        // 当年度・前年度の範囲
        $fiscal_end_date = $this->dtGetFiscalDate("present", "end", true);
        $prev_fiscal_start_date = $this->dtGetFiscalDate("prev", "start", true);

        // 教師一斉のお知らせグループIDを取り出す。
        $query = NoticeGroup::query();
        $notice_groups = $query
            ->select(
                'notice_group_id'
            )
            ->where('notice_group.group_type', AppConst::NOTICE_GROUP_TYPE_2)
            ->get();

        $notice_group_ids = [];
        if (count($notice_groups) > 0) {
            foreach ($notice_groups as $notice_group) {
                array_push($notice_group_ids, $notice_group->notice_group_id);
            }
        }
        $notice_group_ids = array_unique($notice_group_ids);

        // 宛先情報から対象となる個別のお知らせIDを抽出する。
        $query = NoticeDestination::query();
        $notice_destinations = $query
            ->select(
                'notice_destination.notice_id'
            )
            ->sdLeftJoin(Notice::class, 'notice.notice_id', '=', 'notice_destination.notice_id')
            ->where('notice_destination.destination_type', '=', AppConst::CODE_MASTER_15_3)
            ->where('notice_destination.tid', '=', $account_id)
            ->whereBetween('notice.regist_time', [$prev_fiscal_start_date, $fiscal_end_date])
            ->get();

        // 宛先情報から対象となるグループ一斉のお知らせIDを抽出する。
        $query = NoticeDestination::query();
        $notice_destinations_group = $query
            ->select(
                'notice_destination.notice_id'
            )
            ->sdLeftJoin(Notice::class, 'notice.notice_id', '=', 'notice_destination.notice_id')
            ->where('notice_destination.destination_type', '=', AppConst::CODE_MASTER_15_1)
            ->whereIn('notice_destination.notice_group_id', $notice_group_ids)
            ->whereBetween('notice.regist_time', [$prev_fiscal_start_date, $fiscal_end_date])
            ->get();

        // お知らせIDを抽出する。
        $notice_ids = [];
        if (count($notice_destinations) > 0) {
            foreach ($notice_destinations as $notice) {
                array_push($notice_ids, $notice->notice_id);
            }
        }
        if (count($notice_destinations_group) > 0) {
            foreach ($notice_destinations_group as $notice) {
                array_push($notice_ids, $notice->notice_id);
            }
        }
        $notice_ids = array_unique($notice_ids);

        return $notice_ids;
    }

    /**
     * [ガード] お知らせIDが自分が見れるIDかチェックする
     * 
     * @param int $noticeId お知らせID
     */
    private function guardNoticeId($noticeId)
    {

        // ログイン者の情報を取得する。
        $account = Auth::user();
        $account_type = $account->account_type;

        switch ($account_type) {
            case AppConst::CODE_MASTER_7_1:
                //---------------
                // 生徒
                //---------------

                // お知らせIDの取得
                $notice_ids = $this->getStudentNoticeIds();

                // 生徒情報の取得
                $student = $this->getStudentKihon();

                // 生徒入会日
                $enter_date = $student->enter_date;

                // お知らせIDから生徒入会日以降のお知らせを取得する。
                Notice::query()
                    // 対象のお知らせIDで絞る
                    ->whereIn('notice.notice_id', $notice_ids)
                    // 入会日がある場合は、入会日以降のデータを取得
                    ->when($enter_date, function ($query, $enter_date) {
                        return $query->where('notice.regist_time', '>=', $enter_date);
                    })
                    // キー項目指定されたお知らせID
                    ->where('notice.notice_id', '=', $noticeId)
                    // 見つからない場合はエラー
                    ->firstOrFail();

                break;
            case AppConst::CODE_MASTER_7_2:
                //---------------
                // 教師
                //---------------

                // お知らせIDの取得
                $notice_ids = $this->getTutorNoticeIds();

                // お知らせIDからお知らせを取得する。
                // こっちはSQL発行しなくても$notice_idsの存在チェックで良いが、生徒に合わせた
                Notice::query()
                    // 対象のお知らせIDで絞る
                    ->whereIn('notice.notice_id', $notice_ids)
                    // キー項目指定されたお知らせID
                    ->where('notice.notice_id', '=', $noticeId)
                    // 見つからない場合はエラー
                    ->firstOrFail();

                break;
            default:
                // 該当しない場合
                $this->illegalResponseErr();
        }
    }
}
