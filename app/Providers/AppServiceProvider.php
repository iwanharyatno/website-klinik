<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
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
        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Owner') ? true : null;
        });

        config(['app.locale' => 'id']);
    }
}
