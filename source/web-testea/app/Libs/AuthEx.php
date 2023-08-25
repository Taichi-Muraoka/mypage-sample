<?php

namespace App\Libs;

use App\Consts\AppConst;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

/**
 * 認証に関する共通クラス
 */
class AuthEx
{
    /**
     * 権限の追加
     */
    public static function addGateDefine()
    {

        //--------------------
        // 権限の追加
        //--------------------

        // 管理者(教室管理者含む)
        Gate::define('admin', function ($user) {
            return ($user->account_type == AppConst::CODE_MASTER_7_3);
        });

        // 全体管理者のみ
        Gate::define('allAdmin', function ($user) {
            // 本部の教室コード:0
            return ($user->account_type == AppConst::CODE_MASTER_7_3 && $user->roomcd == AppConst::CODE_MASTER_6_0);
        });

        // 教室管理者のみ
        Gate::define('roomAdmin', function ($user) {
            return ($user->account_type == AppConst::CODE_MASTER_7_3 && $user->roomcd !== AppConst::CODE_MASTER_6_0);
        });

        // 教師
        Gate::define('tutor', function ($user) {
            return ($user->account_type == AppConst::CODE_MASTER_7_2);
        });

        // 生徒
        Gate::define('student', function ($user) {
            return ($user->account_type == AppConst::CODE_MASTER_7_1);
        });

        //--------------------
        // 権限グループ
        //--------------------

        // マイページ共通
        Gate::define('mypage-common', function ($user) {
            // 生徒・教師
            return ($user->account_type == AppConst::CODE_MASTER_7_1 || $user->account_type == AppConst::CODE_MASTER_7_2);
        });
    }

    /**
     * ログインユーザが管理者権限を持っているか(教室管理者含む)
     */
    public static function isAdmin()
    {
        $user = Auth::user();
        return Gate::allows('admin', $user);
    }

    /**
     * ログインユーザが教室管理者権限を持っているか
     */
    public static function isRoomAdmin()
    {
        $user = Auth::user();
        return Gate::allows('roomAdmin', $user);
    }

    /**
     * ログインユーザが生徒権限を持っているか
     */
    public static function isStudent()
    {
        $user = Auth::user();
        return Gate::allows('student', $user);
    }

    /**
     * ログインユーザが教師権限を持っているか
     */
    public static function isTutor()
    {
        $user = Auth::user();
        return Gate::allows('tutor', $user);
    }
}
