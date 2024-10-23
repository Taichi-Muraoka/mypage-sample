<?php

namespace App\Filters;

use App\Consts\AppConst;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;
use App\Libs\AuthEx;
use App\Http\Controllers\Traits\CtrlModelTrait;
use App\Models\Conference;
use App\Models\Report;
use App\Models\TransferApplication;
use App\Models\Schedule;
use App\Models\AbsentApplication;
use App\Models\ExtraClassApplication;
use App\Models\Contact;
use App\Models\SeasonStudentRequest;
use App\Models\Surcharge;
use App\Models\TrainingContent;
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
        // アカウント情報取得
        $account = Auth::user();

        // ユーザー権限に応じて、機能ごとに件数を取得する。
        if (AuthEx::isAdmin()) {
            //-------------
            // 管理者の場合
            //-------------

            // 会員メニュー
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_member")) {

                // 面談日程管理
                // 登録ステータス＝未登録 の件数
                $query = Conference::where('status', AppConst::CODE_MASTER_5_0);

                // 校舎の絞り込み
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に校舎で絞り込む
                    $query->where('campus_cd', $account->campus_cd);
                }
                $countContact = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // サブメニューの件数表示
                foreach ($item["submenu"] as &$submenu) {

                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_conference_accept")) {
                        // 面談日程管理
                        $submenu["label"] = $countContact->count;
                        $submenu["label"] === 0 ? $submenu["label_color"] = "info" : $submenu["label_color"] = "danger";
                    }
                }
                // 合計件数
                $item["label"] = $countContact->count;
                $item["label"] === 0 ? $item["label_color"] = "info" : $item["label_color"] = "danger";
            }

            // 授業メニュー
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_lesson")) {

                // 授業報告書
                // 承認ステータス＝承認待ち の件数
                $query = Report::where('approval_status', AppConst::CODE_MASTER_4_1);

                // 校舎の絞り込み
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に校舎で絞り込む
                    $query->where('campus_cd', $account->campus_cd);
                }

                $countReport = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // 振替授業調整
                // 承認ステータス＝管理者承認待ち or 承認待ち or 差戻し（日程不都合）or 差戻し（代講希望） の件数
                $query = TransferApplication::whereIn('approval_status', [AppConst::CODE_MASTER_3_0, AppConst::CODE_MASTER_3_1, AppConst::CODE_MASTER_3_3, AppConst::CODE_MASTER_3_4])
                    ->sdLeftJoin(Schedule::class, function ($join) {
                        $join->on('transfer_applications.schedule_id', '=', 'schedules.schedule_id');
                    });

                // 校舎の絞り込み
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に校舎で絞り込む
                    $query->where('schedules.campus_cd', $account->campus_cd);
                }

                $countTransfer = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // 要振替授業管理
                // 出欠ステータス＝振替中 or 未振替 の件数
                $query = Schedule::whereIn('absent_status', [AppConst::CODE_MASTER_35_3, AppConst::CODE_MASTER_35_4]);

                // 校舎の絞り込み
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に校舎で絞り込む
                    $query->where('campus_cd', $account->campus_cd);
                }

                $countTransferRequire = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // 欠席申請
                // ステータス＝未対応 の件数
                $query = AbsentApplication::where('status', AppConst::CODE_MASTER_1_0)
                    ->sdLeftJoin(Schedule::class, function ($join) {
                        $join->on('absent_applications.schedule_id', '=', 'schedules.schedule_id');
                    });

                // 校舎の絞り込み
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に校舎で絞り込む
                    $query->where('schedules.campus_cd', $account->campus_cd);
                }

                $countAbsent = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // 追加授業依頼
                // ステータス＝未対応 の件数
                $query = ExtraClassApplication::where('status', AppConst::CODE_MASTER_1_0);

                // 校舎の絞り込み
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に校舎で絞り込む
                    $query->where('campus_cd', $account->campus_cd);
                }

                $countExtraClass = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // サブメニューの件数表示
                foreach ($item["submenu"] as &$submenu) {

                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_report_check")) {
                        // 授業報告書
                        $submenu["label"] = $countReport->count;
                        $submenu["label"] === 0 ? $submenu["label_color"] = "info" : $submenu["label_color"] = "danger";
                    }
                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_transfer_check")) {
                        // 振替授業調整
                        $submenu["label"] = $countTransfer->count;
                        $submenu["label"] === 0 ? $submenu["label_color"] = "info" : $submenu["label_color"] = "danger";
                    }
                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_transfer_required")) {
                        // 要振替授業管理
                        $submenu["label"] = $countTransferRequire->count;
                        $submenu["label"] === 0 ? $submenu["label_color"] = "info" : $submenu["label_color"] = "danger";
                    }
                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_absent_accept")) {
                        // 欠席申請
                        $submenu["label"] = $countAbsent->count;
                        $submenu["label"] === 0 ? $submenu["label_color"] = "info" : $submenu["label_color"] = "danger";
                    }
                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_extra_lesson_mng")) {
                        // 追加授業依頼受付
                        $submenu["label"] = $countExtraClass->count;
                        $submenu["label"] === 0 ? $submenu["label_color"] = "info" : $submenu["label_color"] = "danger";
                    }
                }
                // 合計件数
                $item["label"] = $countReport->count + $countTransfer->count + $countTransferRequire->count + $countAbsent->count + $countExtraClass->count;
                $item["label"] === 0 ? $item["label_color"] = "info" : $item["label_color"] = "danger";
            }

            // 問い合わせメニュー
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_contact")) {

                // 問い合わせ
                // ステータス＝未回答 の件数
                $query = Contact::where('contact_state', AppConst::CODE_MASTER_17_0);

                // 校舎の絞り込み
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に校舎で絞り込む
                    $query->where('campus_cd', $account->campus_cd);
                }

                $countContact = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // サブメニューの件数表示
                foreach ($item["submenu"] as &$submenu) {

                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_contact_mng")) {
                        // 問い合わせ管理
                        $submenu["label"] = $countContact->count;
                        $submenu["label"] === 0 ? $submenu["label_color"] = "info" : $submenu["label_color"] = "danger";
                    }
                }
                // 合計件数
                $item["label"] = $countContact->count;
                $item["label"] === 0 ? $item["label_color"] = "info" : $item["label_color"] = "danger";
            }

            // 特別期間講習管理メニュー
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_season_lesson")) {

                // 生徒日程・コマ組み
                // 登録状態＝登録済み かつ （コマ組み状態＝未対応 または 対応中） の件数
                $query = SeasonStudentRequest::where('regist_status', AppConst::CODE_MASTER_5_1)
                    ->whereIn('plan_status', [AppConst::CODE_MASTER_47_0, AppConst::CODE_MASTER_47_1]);

                // 校舎の絞り込み
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に校舎で絞り込む
                    $query->where('campus_cd', $account->campus_cd);
                }

                $countSeason = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // サブメニューの件数表示
                foreach ($item["submenu"] as &$submenu) {

                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_season_mng_student")) {
                        // 生徒日程・コマ組み
                        $submenu["label"] = $countSeason->count;
                        $submenu["label"] === 0 ? $submenu["label_color"] = "info" : $submenu["label_color"] = "danger";
                    }
                }
                // 合計件数
                $item["label"] = $countSeason->count;
                $item["label"] === 0 ? $item["label_color"] = "info" : $item["label_color"] = "danger";
            }

            // 給与情報管理メニュー
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_mng_salary")) {

                // 追加請求受付
                // 承認ステータス＝承認待ちの件数
                $query = Surcharge::where('approval_status', AppConst::CODE_MASTER_2_0);

                // 校舎の絞り込み
                if (AuthEx::isRoomAdmin()) {
                    // 教室管理者の場合、強制的に校舎で絞り込む
                    $query->where('campus_cd', $account->campus_cd);
                }

                $countSurcharge = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // サブメニューの件数表示
                foreach ($item["submenu"] as &$submenu) {

                    if ((isset($submenu["menuid"])) && ($submenu["menuid"] === "id_surcharge_accept")) {
                        // 追加請求受付
                        $submenu["label"] = $countSurcharge->count;
                        $submenu["label"] === 0 ? $submenu["label_color"] = "info" : $submenu["label_color"] = "danger";
                    }
                }
                // 合計件数
                $item["label"] = $countSurcharge->count;
                $item["label"] === 0 ? $item["label_color"] = "info" : $item["label_color"] = "danger";
            }
        }

        if (AuthEx::isStudent()) {
            //-------------
            // 生徒の場合
            //-------------

            // 振替授業調整
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_transfer_check")) {

                // 振替連絡
                // 申請者種別＝講師 かつ 承認ステータス＝承認待ち の件数
                $query = TransferApplication::where('student_id', $account->account_id)
                    ->where('apply_kind', AppConst::CODE_MASTER_53_2)
                    ->where('approval_status', AppConst::CODE_MASTER_3_1);

                $countTransferStudent = $query->select(
                    DB::raw('count(1) as count')
                )->first();

                // 合計件数
                $item["label"] = $countTransferStudent->count;
                $item["label"] === 0 ? $item["label_color"] = "info" : $item["label_color"] = "danger";
            }
        } else if (AuthEx::isTutor()) {
            //-------------
            // 講師の場合
            //-------------

            // 振替授業調整
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_transfer_check")) {

                // 申請者種別＝生徒 かつ 承認ステータス＝承認待ち の件数
                $query = TransferApplication::where('tutor_id', $account->account_id)
                    ->where('apply_kind', AppConst::CODE_MASTER_53_1)
                    ->where('approval_status', AppConst::CODE_MASTER_3_1);

                $countTransferTutor = $query->select(
                    DB::raw('count(1) as count')
                )->first();

                // 合計件数
                $item["label"] = $countTransferTutor->count;
                $item["label"] === 0 ? $item["label_color"] = "info" : $item["label_color"] = "danger";
            }
            // 授業報告書
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_report_regist")) {

                // 承認ステータス＝差戻し の件数
                $query = Report::where('tutor_id', $account->account_id)
                    ->where('approval_status', AppConst::CODE_MASTER_4_3);

                $countReportTutor = $query->select(
                    DB::raw('count(1) as count')
                )->first();

                // 合計件数
                $item["label"] = $countReportTutor->count;
                $item["label"] === 0 ? $item["label_color"] = "info" : $item["label_color"] = "danger";
            }
            // 追加請求申請
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_surcharge")) {

                // 承認ステータス＝差戻し の件数
                $query = Surcharge::where('tutor_id', $account->account_id)
                    ->where('approval_status', AppConst::CODE_MASTER_4_3);

                $countSurchargeTutor = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // 合計件数
                $item["label"] = $countSurchargeTutor->count;
                $item["label"] === 0 ? $item["label_color"] = "info" : $item["label_color"] = "danger";
            }
            // 研修受講
            if ((isset($item["menuid"])) && ($item["menuid"] === "id_training")) {
                // 現在日を取得
                $today = date("Y/m/d");

                // 表示期限内で閲覧済みでない研修の件数
                $query = TrainingContent::
                    // 公開日が当日以前
                    where('release_date', '<=', $today)
                    // 期限日が当日以降かまたは無期限
                    ->where(function ($orQuery) use ($today) {
                        $orQuery
                            ->where('limit_date', '>=', $today)
                            ->orWhereNull('limit_date');
                    })
                    // 閲覧済みのものを除外
                    ->whereNotExists(function ($query) use ($account) {
                        $query->select(DB::raw(1))
                            ->from('training_browses')
                            ->whereColumn('training_contents.trn_id', 'training_browses.trn_id')
                            ->where('training_browses.tutor_id', $account->account_id)
                            // delete_dt条件の追加
                            ->whereNull('training_browses.deleted_at');
                    });

                $countTrainingContent = $query->select(DB::raw('count(1) as count'))
                    ->first();

                // 合計件数
                $item["label"] = $countTrainingContent->count;
                $item["label"] === 0 ? $item["label_color"] = "info" : $item["label_color"] = "danger";
            }
        }
        return $item;
    }
}
