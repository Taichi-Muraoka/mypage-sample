<?php

namespace App\Filters;

use App\Consts\AppConst;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;
use App\Libs\AuthEx;
use App\Http\Controllers\Traits\CtrlModelTrait;
use App\Models\AbsentApply;
use App\Models\Card;
use App\Models\Contact;
use App\Models\CourseApply;
use App\Models\EventApply;
use App\Models\ExtSchedule;
use App\Models\LeaveApply;
use App\Models\TransferApply;
use App\Models\TrialApply;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * AdminLteの左メニューのカスタマイズ
 */
class AppMenuFilter implements FilterInterface
{
    // モデル共通処理
    use CtrlModelTrait;

    public function transform($item)
    {

        // 管理者の場合で、機能ごとに件数を取得する。
        if (AuthEx::isAdmin()) {

            // 会員メニュー
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_member")) {

                // コース変更・授業追加
                $query = CourseApply::where('changes_state', AppConst::CODE_MASTER_2_0);

                // アカウント情報取得
                $account = Auth::user();

                // 教室の絞り込み(生徒基本情報参照)
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に教室コードで検索する
                    $this->mdlWhereSidByRoomQuery($query, CourseApply::class, $account->roomcd);
                }
                $countCourse = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // 退会申請
                $query = LeaveApply::where('leave_state', AppConst::CODE_MASTER_5_0);

                // 教室の絞り込み(生徒基本情報参照)
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に教室コードで検索する
                    $this->mdlWhereSidByRoomQuery($query, LeaveApply::class, $account->roomcd);
                }
                $countLeave = $query->select(
                    DB::raw('count(1) as count')
                )->first();

                // 合計件数
                $item["label"] = $countCourse->count + $countLeave->count;
                $item["label_color"] = "info";

                // サブメニューの件数表示
                foreach ($item["submenu"] as &$submenu) {

                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_course_accept")) {
                        // コース変更・授業追加
                        $submenu["label"] = $countCourse->count;
                        $submenu["label_color"] = "info";
                    }

                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_leave_accept")) {
                        // 退会申請
                        $submenu["label"] = $countLeave->count;
                        $submenu["label_color"] = "info";
                    }
                }
            }

            // 授業メニュー
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_lesson")) {

                // 欠席申請
                $query = AbsentApply::where('state', AppConst::CODE_MASTER_1_0);

                // アカウント情報取得
                $account = Auth::user();

                // 教室の絞り込み(生徒基本情報参照)
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に教室コードで検索する
                    $this->mdlWhereSidByRoomQuery($query, AbsentApply::class, $account->roomcd);
                }
                $countAbsent = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // 振替連絡
                $query = TransferApply::where('state', AppConst::CODE_MASTER_1_0)
                    ->sdLeftJoin(ExtSchedule::class, function ($join) {
                        $join->on('transfer_apply.id', '=', 'ext_schedule.id');
                    });

                // 教室の絞り込み(生徒基本情報参照)
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に教室コードで検索する
                    $query->where('roomcd', $account->roomcd);
                }

                $countTransfer = $query->select(
                    DB::raw('count(1) as count')
                )->first();

                // 合計件数
                $item["label"] = $countAbsent->count + $countTransfer->count;
                $item["label_color"] = "info";

                // サブメニューの件数表示
                foreach ($item["submenu"] as &$submenu) {

                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_absent_accept")) {
                        // 欠席申請
                        $submenu["label"] = $countAbsent->count;
                        $submenu["label_color"] = "info";
                    }

                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_transfer_accept")) {
                        // 振替連絡
                        $submenu["label"] = $countTransfer->count;
                        $submenu["label_color"] = "info";
                    }
                }
            }

            // 模試・イベント管理メニュー
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_trial_event")) {

                // 模試
                $query = TrialApply::where('apply_state', AppConst::CODE_MASTER_3_0);

                // アカウント情報取得
                $account = Auth::user();

                // 教室の絞り込み(生徒基本情報参照)
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に教室コードで検索する
                    $this->mdlWhereSidByRoomQuery($query, TrialApply::class, $account->roomcd);
                }
                $countTrial = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // イベント
                $query = EventApply::where('changes_state', AppConst::CODE_MASTER_2_0);

                // 教室の絞り込み(生徒基本情報参照)
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に教室コードで検索する
                    $this->mdlWhereSidByRoomQuery($query, EventApply::class, $account->roomcd);
                }
                $countEvent = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // !の表示
                if ($countTrial->count || $countEvent->count) {
                    $item["label"] = '!';
                    $item["label_color"] = "info";

                    // サブメニューの件数表示
                    foreach ($item["submenu"] as &$submenu) {

                        if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_trial_mng") && ($countTrial->count)) {
                            // 模試管理
                            $submenu["label"] = '!';
                            $submenu["label_color"] = "info";
                        }

                        if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_event_mng") && ($countEvent->count)) {
                            // イベント
                            $submenu["label"] = '!';
                            $submenu["label_color"] = "info";
                        }
                    }
                }
            }

            // カードメニュー
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_card")) {

                // ギフトカード
                $query = Card::where('card_state', AppConst::CODE_MASTER_4_1);

                // アカウント情報取得
                $account = Auth::user();

                // 教室の絞り込み(生徒基本情報参照)
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に教室コードで検索する
                    $this->mdlWhereSidByRoomQuery($query, Card::class, $account->roomcd);
                }
                $countCard = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // 合計件数
                $item["label"] = $countCard->count;
                $item["label_color"] = "info";

                // サブメニューの件数表示
                foreach ($item["submenu"] as &$submenu) {

                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_card_mng")) {
                        // ギフトカード管理
                        $submenu["label"] = $countCard->count;
                        $submenu["label_color"] = "info";
                    }
                }
            }

            // 問い合わせメニュー
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_contact")) {

                // 問い合わせ
                $query = Contact::where('contact_state', AppConst::CODE_MASTER_17_0);

                // アカウント情報取得
                $account = Auth::user();

                // 教室の絞り込み(生徒基本情報参照)
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に教室コードで検索する
                    $query->where('roomcd',$account->roomcd);
                }
                $countContact = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // 合計件数
                $item["label"] = $countContact->count;
                $item["label_color"] = "info";

                // サブメニューの件数表示
                foreach ($item["submenu"] as &$submenu) {

                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_contact_mng")) {
                        // 問い合わせ管理
                        $submenu["label"] = $countContact->count;
                        $submenu["label_color"] = "info";
                    }
                }
            }
        }

        return $item;
    }
}
