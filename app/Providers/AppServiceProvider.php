<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Trust all proxies on shared hosting (InfinityFree, etc.)
        // This ensures Laravel sees the correct HTTPS scheme instead of HTTP,
        // preventing ERR_TOO_MANY_REDIRECTS on hosts that terminate SSL at the edge.
        if (app()->environment('production')) {
            \Illuminate\Http\Request::setTrustedProxies(
                ['REMOTE_ADDR'],
                \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO
            );
            URL::forceScheme('https');
        }

        // Share colors globally so topnav/layouts can inject CSS variable overrides
        // Batch: 1 query for both colors instead of 2 individual Setting::get() calls
        try {
            $colorSettings  = Setting::whereIn('key', ['primary_color', 'secondary_color'])
                                     ->pluck('value', 'key');
            $primaryColor   = $colorSettings->get('primary_color', '#93c5fd');
            $secondaryColor = $colorSettings->get('secondary_color', '#bfdbfe');
        } catch (\Exception $e) {
            $primaryColor   = '#93c5fd';
            $secondaryColor = '#bfdbfe';
        }
        View::share('appPrimaryColor', $primaryColor);
        View::share('appSecondaryColor', $secondaryColor);

        View::composer(['splash', 'welcome'], function ($view) {
            // Batch: 1 query for all 4 splash/welcome settings instead of 4 individual calls
            try {
                $splashSettings = Setting::whereIn('key', ['splash_logo', 'welcome_logo', 'org_name', 'org_tagline'])
                                         ->pluck('value', 'key');
                $splashLogo  = $splashSettings->get('splash_logo');
                $welcomeLogo = $splashSettings->get('welcome_logo');
                $orgName     = $splashSettings->get('org_name', 'MAKERERE UNIVERSITY MEDICAL STUDENTS ASSOCIATION (MUMSA)');
                $orgTagline  = $splashSettings->get('org_tagline', 'All Rights Reserved © 2026');
            } catch (\Exception $e) {
                $splashLogo = $welcomeLogo = null;
                $orgName    = 'MAKERERE UNIVERSITY MEDICAL STUDENTS ASSOCIATION (MUMSA)';
                $orgTagline = 'All Rights Reserved © 2026';
            }
            $view->with(compact('splashLogo', 'welcomeLogo', 'orgName', 'orgTagline'));
        });
    }
}
