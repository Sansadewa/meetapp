<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Force HTTPS for external access (not for internal IP)
        $internalIp = env('INTERNAL_IP', '10.63.0.234');
        $currentHost = request()->getHost();
        
        // Check if accessed via Cloudflare (has CF headers) or public domain
        $hasCloudflare = request()->hasHeader('CF-Connecting-IP') || request()->hasHeader('CF-Ray');
        $publicDomains = ['meetapp.statkalsel.com', 'statkalsel.com', 'bpskalsel.com'];
        $isPublicDomain = in_array($currentHost, $publicDomains);
        
        // Force HTTPS if accessed via public domain or Cloudflare
        if (($hasCloudflare || $isPublicDomain) && env('FORCE_HTTPS', true)) {
            URL::forceScheme('https');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
