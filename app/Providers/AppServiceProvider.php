<?php

namespace App\Providers;

use App\Models\User;
use App\Support\CookieConsent;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        View::composer('layouts.app', function ($view) {
            $pending = 0;
            if (Auth::check() && Auth::user()->isAdmin()) {
                $pending = User::nonAdmin()->where('abilitato', 0)->count();
            }
            $view->with('adminPendingUsersCount', $pending);
        });

        // Direttive Blade per bloccare script di terze parti finché non c'è consenso.
        // Uso: @thirdparty ... @endthirdparty
        Blade::if('thirdparty', function () {
            $request = request();
            return CookieConsent::hasCategory($request, 'third_party');
        });

        // Variabile comoda per mostrare/nascondere il banner
        View::composer('*', function ($view) {
            $view->with('cookieBannerShouldShow', CookieConsent::shouldShowBanner(request()));
        });
    }
}
