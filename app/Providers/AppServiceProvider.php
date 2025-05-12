<?php

namespace App\Providers;

use App\Services\GeocodingService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(GeocodingService::class, function ($app) {
            return new GeocodingService();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Switch to S3 filesystem when running in Railway environment
        if (env('RAILWAY_ENVIRONMENT')) {
            Config::set('filesystems.default', env('RAILWAY_FILESYSTEM_DISK', 's3'));
            
            // Update the MediaResource URL generation to use absolute URLs from S3
            \App\Models\Media::resolveUrlUsing(function ($media) {
                if ($media->disk === 's3') {
                    return Config::get('filesystems.disks.s3.url') . '/' . $media->path;
                }
                return url('/storage/' . $media->path);
            });
        }
    }
}
