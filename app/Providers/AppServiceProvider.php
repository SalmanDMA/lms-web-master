<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

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
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
        RateLimiter::for('rate', function (Request $request) {
            return Limit::perMinute(120)->response(function (Request $request, array $headers) {
                return response()->json(['success' => false, 'message' => 'Too many requests. Please try again later.'], 429);
            })->by($request->ip());
        });
    }
}
