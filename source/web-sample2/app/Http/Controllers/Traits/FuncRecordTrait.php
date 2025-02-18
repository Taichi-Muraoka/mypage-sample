<?php

namespace App\Http\Controllers\Traits;

use App\Consts\AppConst;
use App\Models\Student;
use App\Models\AdminUser;
use App\Models\CodeMaster;

/**
 * 連絡記録 - 機能共通処理
 */
trait FuncRecordTrait
{
    //==========================
    // 関数名を区別するために
    // fncRecdを先頭につける
    //==========================

    /**
     * 連絡記録情報表示用のquery作成
     * select句・join句を設定する
     * 個別のwhere条件・ガード・ソートは呼び元で行うこと
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    private function fncRecdGetRecordQuery($query)
    {
        // 校舎名取得のサブクエリ
        $campus_names = $this->mdlGetRoomQuery();

        return $query
            ->select(
                'records.record_id',
                'records.student_id',
                // 生徒情報の名前
                'students.name as student_name',
                'records.campus_cd',
                // 校舎名
                'campus_names.room_name as campus_name',
                'records.record_kind',
                // コードマスタの名称（記録種別）
                'mst_codes.name as kind_name',
                'records.adm_id',
                // 管理者の名前
                'admin_users.name as admin_name',
                'records.received_date',
                'records.received_time',
                'records.regist_time',
                'records.memo'
            )
            // 校舎名の取得
            ->leftJoinSub($campus_names, 'campus_names', function ($join) {
                $join->on('records.campus_cd', '=', 'campus_names.code');
            })
            // 生徒名を取得
            ->sdLeftJoin(Student::class, 'records.student_id', '=', 'students.student_id')
            // コードマスターとJOIN
            ->sdLeftJoin(CodeMaster::class, function ($join) {
                $join->on('records.record_kind', '=', 'mst_codes.code')
                    ->where('data_type', AppConst::CODE_MASTER_46);
            })
            // 管理者名を取得
            ->sdLeftJoin(AdminUser::class, 'records.adm_id', '=', 'admin_users.adm_id');
    }
}
