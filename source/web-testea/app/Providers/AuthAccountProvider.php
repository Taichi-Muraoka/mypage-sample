<?php

namespace App\Providers;

use App\Consts\AppConst;
use Illuminate\Auth\EloquentUserProvider;
use App\Models\AdminUser;
use App\Models\Tutor;
use App\Models\Student;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Support\Arrayable;
use Closure;
//use Illuminate\Support\Facades\Log;

/**
 * 独自のログイン認証のプロバイダー(accountテーブル対応)
 */
class AuthAccountProvider extends EloquentUserProvider
{

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {

        // MEMO
        // この関数は、非同期通信でも毎回呼ばれていた。
        // ユーザテーブル変更について、複合キーと$identifierがアンダーバー区切りだが
        // 一通り動作確認とソースはOK。試行回数エラーもOK。remember_tokenもOK
        // SESSIONの中身を確認したが、きちんとアンダーバー区切りのIDが格納されていた

        // IDはアンダーバーで区切った account_id _ account_type
        $ids = explode("_", $identifier);
        if (count($ids) != 2) {
            return null;
        }

        // SQLテスト
        //\DB::enableQueryLog();

        // モデルの作成。一応直接Accountモデルを参照せず、auth.phpで設定されたモデルを参照するような形
        $model = $this->createModel();

        // 複数キー 以下の関数のreturn stringとなっているけど今回は配列で
        $primaryKey = $model->getKeyName();
        // Warning解消のため以下追加
        if(!is_array($primaryKey)){
            $primaryKey = explode(',', $primaryKey);
        }
        // クエリを取得
        $query = $model->newQuery();

        // 複数の主キーをwhereにいれる。$primaryKeyは@returnでStringになっているが、今回は配列
        foreach (array_map(NULL, $primaryKey, $ids) as [$key, $val]) {
            $query->where($key, $val);
        }
        // ログイン可否フラグの条件を付加
        $query->where('login_flg', AppConst::CODE_MASTER_9_1);

        // 最低限の情報のみ取得
        $query->select('account_id', 'account_type', 'remember_token');

        // 1件取得
        $resultAccount = $query->firstOrFail();

        // 今回はaccount_typeによって、参照先テーブルを変え取得する
        $account_id = $resultAccount['account_id'];
        $account_type = $resultAccount['account_type'];

        // アカウント種別により、それぞれのテーブルから名前を取得
        // 以下、直接モデルを参照した
        switch ($account_type) {
            case AppConst::CODE_MASTER_7_1:
                // 生徒
                $student = Student::where('student_id', $account_id)
                    ->firstOrFail();
                // 名前を取得
                $resultAccount['name'] = $student['name'];
                break;
            case AppConst::CODE_MASTER_7_2:
                // 教師
                $tutor = Tutor::where('tutor_id', $account_id)
                    ->firstOrFail();
                // 名前を取得
                $resultAccount['name'] = $tutor['name'];
                break;
            case AppConst::CODE_MASTER_7_3:
                // 管理者
                $admin = AdminUser::where('adm_id', $account_id)
                    ->firstOrFail();
                // 名前を取得
                $resultAccount['name'] = $admin['name'];
                // 教室コード（管理教室）
                // 教室管理者かどうかチェックするため
                $resultAccount['campus_cd'] = $admin['campus_cd'];
                break;
            default:
                // 該当しない場合
                abort(404);
        }

        // LOG出したら、大丈夫っぽい。論理削除。生徒、教師、管理者もOK
        //'query' => 'select * from `account` where `account_id` = ? and `account_type` = ? and `login_flg` = ? and `account`.`deleted_at` is null limit 1',
        //Log::debug(\DB::getQueryLog());

        // アカウント情報を返却
        return $resultAccount;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {

        // 「パスワードを記憶」時のトークンの取得
        // この関数も複合キー対応。
        // retrieveByIdと同じことやるので、関数直接呼んだ
        $retrievedModel = $this->retrieveById($identifier);

        if (!$retrievedModel) {
            return;
        }

        $rememberToken = $retrievedModel->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token)
            ? $retrievedModel : null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable|\Illuminate\Database\Eloquent\Model  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        // 「パスワードを記憶」時のトークンの更新
        // retrieveByIdでnameを追加すると、model->save()でエラーになる・・
        // nameは消す。nameが入ってくるのは、ログアウト時。
        // ログイン時はnameがなく、普通のaccoutしか来ない
        // なのでとりあえずnameは消すでよい
        unset($user->{"name"});
        unset($user->{"campus_cd"});

        // 元の処理を呼ぶ
        parent::updateRememberToken($user, $token);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        // ログイン条件追加のため、retrieveByCredentials をラップする

        $credentials = array_filter(
            $credentials,
            fn ($key) => ! str_contains($key, 'password'),
            ARRAY_FILTER_USE_KEY
        );

        if (empty($credentials)) {
            return;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->newModelQuery();

        foreach ($credentials as $key => $value) {
            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } elseif ($value instanceof Closure) {
                $value($query);
            } else {
                $query->where($key, $value);
            }
        }
        // ログイン可否フラグの条件を付加
        return $query->where('login_flg', AppConst::CODE_MASTER_9_1)->first();
    }
}
