<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use App\Models\Passport\Client;
use App\Services\Applications\Caches\UserCacheService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserCacheService::class, function ($app) {
            return new UserCacheService;
        });
    }

    public function boot(): void
    {
        Passport::tokensExpireIn(CarbonInterval::days(10));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
        Passport::personalAccessTokensExpireIn(CarbonInterval::months(6));
        Passport::useClientModel(Client::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('special-super-admin') ? true : null;
        });
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}
