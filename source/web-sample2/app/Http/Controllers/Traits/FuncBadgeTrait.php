<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Models\Student;
use App\Models\AdminUser;
use App\Models\CodeMaster;

/**
 * バッジ付与 - 機能共通処理
 */
trait FuncBadgeTrait
{
    //==========================
    // 関数名を区別するために
    // fncBageを先頭につける
    //==========================

    /**
     * バッジ付与情報表示用のquery作成
     * select句・join句を設定する
     * 個別のwhere条件・ガード・ソートは呼び元で行うこと
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    private function fncBageGetBadgeQuery($query)
    {
        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        return $query
            ->select(
                'badges.badge_id',
                'badges.campus_cd',
                // 校舎の名称
                'campus_names.room_name as campus_name',
                // コードマスタの名称（バッジ種別）
                'mst_codes.name as kind_name',
                'badges.reason',
                'badges.authorization_date',
                // 管理者アカウントの名前
                'admin_users.name as admin_name',
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('badges.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'badges.student_id', '=', 'students.student_id')
            // 管理者名を取得
            ->sdLeftJoin(AdminUser::class, 'badges.adm_id', '=', 'admin_users.adm_id')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('badges.badge_type', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_55);
            });
        }
}
