<?php

namespace App\Http\Controllers\Traits;

use App\Libs\AuthEx;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Traits\CtrlModelTrait;
use App\Models\StudentCampus;

/**
 * ガード共通処理
 * 
 * 他人のIDを見れないかなど、リクエストを権限によってガードを掛けるための共通処理
 * 生徒・講師・教室管理者の場合にそれぞれガードをかける
 */
trait GuardTrait
{

    // モデル共通処理
    use CtrlModelTrait;

    //==========================
    // 共通
    //==========================

    /**
     * プルダウンリストの値が正しいかチェックする
     * 
     * @param $list プルダウンリスト
     * @param $value 値
     */
    protected function guardListValue($list, $value)
    {
        if (!isset($list[$value])) {
            $this->illegalResponseErr();
        }
    }

    //==========================
    // 生徒
    //==========================

    /**
     * テーブルそのものに生徒IDカラムを持っており、
     * 生徒の生徒IDでガードをかける
     * whereにそのまま指定する
     */
    protected function guardStudentTableWithSid()
    {
        // クロージャで呼んでもらうため、関数で返却
        return function ($query) {

            // 呼び元の主テーブルの生徒IDのみを絞り込む
            $account = Auth::user();
            // 主テーブルのテーブル名を取得する
            $query->where($query->getModel()->getTable() . '.student_id', $account->account_id);
        };
    }

    //==========================
    // 講師
    //==========================

    /**
     * テーブルそのものに講師IDカラムを持っており、
     * 講師の講師IDでガードをかける
     * whereにそのまま指定する
     */
    protected function guardTutorTableWithTid()
    {
        // クロージャで呼んでもらうため、関数で返却
        return function ($query) {

            // 呼び元の主テーブルの講師IDのみを絞り込む
            $account = Auth::user();
            // 主テーブルのテーブル名を取得する
            $query->where($query->getModel()->getTable() . '.tutor_id', $account->account_id);
        };
    }

    /**
     * テーブルそのものに生徒IDカラムを持っており、
     * 講師の受け持ち生徒でガードを掛ける
     * whereにそのまま指定する
     */
    protected function guardTutorTableWithSid()
    {
        // クロージャで呼んでもらうため、関数で返却
        return function ($query) {

            $account = Auth::user();
            $this->mdlWhereSidByRoomQueryForT($query, get_class($query->getModel()), null);
        };
    }


    //==========================
    // 教室管理者
    //==========================

    /**
     * テーブルそのものに校舎コードカラムを持っており、
     * 教室管理者の校舎コードでガードを掛ける
     * whereにそのまま指定する
     * 
     * @param $model 絞り込む対象のテーブルモデルを指定。nullの場合は主テーブルを取得するが、教室コードが主テーブルではない場合
     */
    protected function guardRoomAdminTableWithRoomCd($model = null)
    {
        // クロージャで呼んでもらうため、関数で返却
        return function ($query) use ($model) {

            if (AuthEx::isRoomAdmin()) {
                $account = Auth::user();
                // 指定されたテーブルそのものにcampus_cdがある場合
                if ($model) {
                    // モデルから取得する
                    $modelObj = new $model();
                    $query->where($modelObj->getTable() . '.campus_cd', $account->campus_cd);
                } else {
                    // 主テーブルのテーブル名を取得する
                    $query->where($query->getModel()->getTable() . '.campus_cd', $account->campus_cd);
                }
            }
        };
    }

    /**
     * テーブルそのものに生徒IDカラムを持っており、
     * 教室管理者の校舎コードの生徒のみにガードを掛ける
     * whereにそのまま指定する
     */
    protected function guardRoomAdminTableWithSid()
    {
        // クロージャで呼んでもらうため、関数で返却
        return function ($query) {

            if (AuthEx::isRoomAdmin()) {
                // 指定された教室コードのsidのみを絞り込む
                $account = Auth::user();
                // 主テーブルのsidに対して絞り込む
                $this->mdlWhereSidByRoomQuery($query, get_class($query->getModel()), $account->campus_cd);
            }
        };
    }

    /**
     * 教室管理者の校舎コードの生徒IDかチェックしガードを掛ける
     */
    protected function guardRoomAdminSid($sid)
    {
        // 教室管理者の場合、見れていいidかチェックする
        if (AuthEx::isRoomAdmin()) {
            $account = Auth::user();
            $exists = StudentCampus::where('campus_cd', $account->campus_cd)->where('student_id', $sid)->exists();
            if (!$exists) {
                return $this->illegalResponseErr();
            }
        }
    }

    /**
     * 教室管理者の校舎コードとPOSTされた校舎コードが一致しない場合にエラーを発生させたい時、使用する。
     * @param $campusCd POSTされた校舎コード
     */
    protected function guardRoomAdminRoomcd($campusCd)
    {
        // 教室管理者の場合、見れていいidかチェックする
        if (AuthEx::isRoomAdmin()) {
            $account = Auth::user();
            if ($campusCd != $account->campus_cd) {
                return $this->illegalResponseErr();
            }
        }
    }
}
