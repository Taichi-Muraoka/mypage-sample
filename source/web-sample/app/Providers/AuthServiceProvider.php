<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Libs\AuthEx;

use App\Providers\AuthAccountProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        //$this->registerPolicies();

        // 権限の追加
        AuthEx::addGateDefine();

        // 独自の認証用のプロバイダーを定義
        Auth::provider('auth_account', function ($app) {
            // configに定義したモデルを取得する
            $model = $app['config']['auth.providers.account.model'];
            // 独自認証プロバイダーを呼ぶ
            return new AuthAccountProvider($app['hash'], $model);
        });
    }
}
