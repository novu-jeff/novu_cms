<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\OrganizationSetting;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $resolve = config('app.http_resolve', []);
        if (is_array($resolve) && $resolve !== []) {
            Http::globalOptions([
                'curl' => [
                    CURLOPT_RESOLVE => $resolve,
                ],
            ]);
        }

        View::composer('components.navbar', function ($view) {
            $view->with(
                'organizationName',
                OrganizationSetting::query()->value('organization_name')
            );
        });

        // Force HTTPS when APP_URL is https (behind reverse proxy / nginx)
        $appUrl = config('app.url');
        if ($appUrl && str_starts_with(strtolower($appUrl), 'https://')) {
            URL::forceScheme('https');
            URL::forceRootUrl($appUrl);
        }
    }
}
