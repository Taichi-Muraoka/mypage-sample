<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Paginator::useBootstrap();

        // viewのパスをbladeで拾いたいためフィルターを定義。4
        // JSでviewのパスを使用したい
        view()->composer('*', function ($view) {
            $view_name = str_replace('.', '/', $view->getName());
            view()->share('view_name', $view_name);
        });

    }
}
