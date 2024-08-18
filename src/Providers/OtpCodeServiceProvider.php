<?php

namespace Ars\Otp\Providers;

use Ars\Otp\Commands\OtpCodeClearExpiredCommand;
use Ars\Otp\Repositories\OtpRepository;
use Illuminate\Support\ServiceProvider;

class OtpCodeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind OtpRepository to the service container
        $this->app->singleton(OtpRepository::class, function ($app) {
            return new OtpRepository();
        });

        // Bind 'otp-code' to an instance of OtpRepository
        $this->app->bind('otp-code', function ($app) {
            return $app->make(OtpRepository::class);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish migration files
        $this->publishes([
            __DIR__ . '/../../migrations' => database_path('migrations'),
        ], 'migrations');

        // Publish configuration file
        $this->publishes([
            __DIR__ . '/../../config/otp-code.php' => config_path('otp-code.php'),
        ], 'config');

        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../../lang', 'otp_code');

        // Register commands
        $this->commands([
            OtpCodeClearExpiredCommand::class,
        ]);
    }
}
