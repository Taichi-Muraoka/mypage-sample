<?php

namespace App\Http\Controllers\Traits;

use App\Libs\AuthEx;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Traits\CtrlModelTrait;
use App\Models\ExtRoom;

/**
 * ガード共通処理
 * 
 * 他人のIDを見れないかなど、リクエストを権限によってガードを掛けるための共通処理
 * 生徒・教師・教室管理者の場合にそれぞれガードをかける
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

            // 指定された教室コードのsidのみを絞り込む
            $account = Auth::user();
            // 主テーブルのテーブル名を取得する
            $query->where($query->getModel()->getTable() . '.sid', $account->account_id);
        };
    }

    //==========================
    // 教師
    //==========================

    /**
     * テーブルそのものに教師IDカラムを持っており、
     * 教師の教師IDでガードをかける
     * whereにそのまま指定する
     */
    protected function guardTutorTableWithTid()
    {
        // クロージャで呼んでもらうため、関数で返却
        return function ($query) {

            // 指定された教室コードのsidのみを絞り込む
            $account = Auth::user();
            // 主テーブルのテーブル名を取得する
            $query->where($query->getModel()->getTable() . '.tid', $account->account_id);
        };
    }

    /**
     * テーブルそのものに生徒IDカラムを持っており、
     * 教師の教室コードでガードを掛ける(受け持ち生徒に限定)
     * whereにそのまま指定する
     */
    protected function guardTutorTableWithSid()
    {
        // クロージャで呼んでもらうため、関数で返却
        return function ($query) {

            $this->mdlWhereSidByRoomQueryForT($query, get_class($query->getModel()));
        };
    }


    //==========================
    // 教室管理者
    //==========================

    /**
     * テーブルそのものに教室コードカラムを持っており、
     * 教室管理者の教室コードでガードを掛ける
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
                // 指定されたテーブルそのものにroomcdがある場合
                if ($model) {
                    // モデルから取得する
                    $modelObj = new $model();
                    $query->where($modelObj->getTable() . '.roomcd', $account->roomcd);
                } else {
                    // 主テーブルのテーブル名を取得する
                    $query->where($query->getModel()->getTable() . '.roomcd', $account->roomcd);
                }
            }
        };
    }

    /**
     * テーブルそのものに生徒IDカラムを持っており、
     * 教室管理者の教室コードの生徒のみにガードを掛ける
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
                $this->mdlWhereSidByRoomQuery($query, get_class($query->getModel()), $account->roomcd);
            }
        };
    }

    /**
     * 教室管理者の教室コードの生徒IDかチェックしガードを掛ける
     */
    protected function guardRoomAdminSid($sid)
    {
        // 教室管理者の場合、見れていいidかチェックする
        if (AuthEx::isRoomAdmin()) {
            $account = Auth::user();
            $exists = ExtRoom::where('roomcd', $account->roomcd)->where('sid', $sid)->exists();
            if (!$exists) {
                return $this->illegalResponseErr();
            }
        }
    }

    /**
     * 教室管理者の教室コードとPOSTされた教室コードが一致しない場合にエラーを発生させたい時、使用する。
     * @param $roomcd POSTされた教室コード
     */
    protected function guardRoomAdminRoomcd($roomcd)
    {
        // 教室管理者の場合、見れていいidかチェックする
        if (AuthEx::isRoomAdmin()) {
            $account = Auth::user();
            if ($roomcd != $account->roomcd) {
                return $this->illegalResponseErr();
            }
        }
    }
}
