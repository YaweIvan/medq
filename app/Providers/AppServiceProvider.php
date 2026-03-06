<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Share colors globally so topnav/layouts can inject CSS variable overrides
        try {
            $primaryColor   = Setting::get('primary_color', '#93c5fd');
            $secondaryColor = Setting::get('secondary_color', '#bfdbfe');
        } catch (\Exception $e) {
            $primaryColor   = '#93c5fd';
            $secondaryColor = '#bfdbfe';
        }
        View::share('appPrimaryColor', $primaryColor);
        View::share('appSecondaryColor', $secondaryColor);

        View::composer(['splash', 'welcome'], function ($view) {
            try {
                $splashLogo  = Setting::get('splash_logo');
                $welcomeLogo = Setting::get('welcome_logo');
                $orgName     = Setting::get('org_name', 'MAKERERE UNIVERSITY MEDICAL STUDENTS ASSOCIATION (MUMSA)');
                $orgTagline  = Setting::get('org_tagline', 'All Rights Reserved © 2026');
            } catch (\Exception $e) {
                $splashLogo = $welcomeLogo = null;
                $orgName    = 'MAKERERE UNIVERSITY MEDICAL STUDENTS ASSOCIATION (MUMSA)';
                $orgTagline = 'All Rights Reserved © 2026';
            }
            $view->with(compact('splashLogo', 'welcomeLogo', 'orgName', 'orgTagline'));
        });
    }
}
