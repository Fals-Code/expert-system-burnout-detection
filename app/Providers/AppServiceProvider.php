<?php

namespace App\Providers;

use App\Models\Aturan;
use App\Models\Diagnosa;
use App\Models\Gejala;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
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
        // ── 1. Centralized Cache Invalidation Observers ──
        $clearCache = function () {
            Cache::forget('aturan_active_rules');
            Cache::forget('aturan_active_rules_base64');
            Cache::forget('diagnosa_ordered');
            Cache::forget('diagnosa_ordered_base64');
            Cache::forget('diagnosa_default_rendah');
            Cache::forget('diagnosa_default_rendah_base64');
        };

        Aturan::saved($clearCache);
        Aturan::deleted($clearCache);
        Gejala::saved($clearCache);
        Gejala::deleted($clearCache);
        Diagnosa::saved($clearCache);
        Diagnosa::deleted($clearCache);

        // ── 2. Custom API & Critical Route Rate Limiters ──
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('critical', function (Request $request) {
            return Limit::perMinute(15)->by($request->ip());
        });
    }
}
