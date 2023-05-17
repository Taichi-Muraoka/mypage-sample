<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\PasswordResetNotification;
use App\Models\ExtStudentKihon;
use App\Consts\AppConst;

/**
 * アカウント情報 - モデル
 *
 * 認証に使用するモデルということでAuthenticatableを継承
 */
class Account extends Authenticatable
{

    // モデルの共通処理
    use \App\Traits\ModelTrait;

    // パスワードリセット通知用
    use Notifiable;

    // 論理削除
    use SoftDeletes;

    /**
     * モデルと関連しているテーブル
     *
     * @var string
     */
    protected $table = 'account';

    /**
     * テーブルの主キー
     *
     * @var array
     */
    protected $primaryKey = [
        'account_id',
        'account_type'
    ];

    /**
     * IDが自動増分されるか
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'account_id'
    ];

    /**
     * 日付項目の定義
     *
     * @var array
     */
    protected $dates = [];

    /**
     * 属性に対するモデルのデフォルト値
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * 配列に含めない属性
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * モデルの「初期起動」メソッド
     *
     * @return void
     */
    protected static function booted()
    {
        // 更新時、空白をnullに変換する処理
        self::whenSaveEmptyToNull();

        // テーブル操作時、ログを残す処理
        self::saveToLog();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        // Laravelの認証用の関数(複合キー対応)
        // 今回、複合キーなのでデフォルトで以下になっているがエラーになる。
        // return $this->{$this->getAuthIdentifierName()};
        // 配列で返却するとエラーになったので、以下のような文字列にして返却
        return $this->account_id . '_' . $this->account_type;
    }

    /**
     * Override to send for password reset notification.
     *
     * @param [type] $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        // パスワードリセットの通知クラスを変更する。
        // メールの本文の日本語化など
        $this->notify(new PasswordResetNotification($token));
    }

    //-------------------------------
    // 項目定義
    //-------------------------------

    /**
     * テーブル項目の定義
     *
     * @return array
     */
    protected static function getFieldRules()
    {
        static $_fieldRules = [
            //'email' => ['email'],
            // emailバリデーションを見直し・string/maxも追加
            // MEMO: emailはテーブル定義上 varchar(120) だが、バリデーションルールはmax:100とすること
            // （削除アカウントのメールアドレスに文字列を付加するためのバッファとする）
            'email' => ['string', 'email:rfc,filter', 'max:100'],
            // 独自バリデーションは英数字混合8文字以上20文字以内。
            // max指定はパスワード変更フォームの最大入力桁数用にあえて指定。
            'password' => ['string', 'vdPassword', 'max:20']
        ];
        return $_fieldRules;
    }
}
