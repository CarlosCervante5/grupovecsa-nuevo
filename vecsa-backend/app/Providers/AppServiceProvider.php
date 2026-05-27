<?php

namespace App\Providers;

use App\Services\DealershipAccessService;
use App\Services\UserService;
use App\Services\VehicleService;
use App\Support\UploadableImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(UserService::class, function ($app) {
            return new UserService();
        });

        $this->app->singleton(VehicleService::class, function ($app) {
            return new VehicleService(
                $app->make(UserService::class),
                $app->make(DealershipAccessService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Validator::extend('uploadable_image', function (string $attribute, mixed $value, array $parameters): bool {
            if (! $value instanceof UploadedFile) {
                return false;
            }

            $allowPdf = in_array('pdf', $parameters, true);

            return UploadableImage::isAllowed($value, $allowPdf);
        });

        Validator::replacer('uploadable_image', function () {
            return 'Formato no permitido. Use JPEG, PNG, GIF, WEBP o HEIC (foto iPhone).';
        });
    }
}
