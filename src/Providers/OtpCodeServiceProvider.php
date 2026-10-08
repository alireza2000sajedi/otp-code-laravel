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
        // Request/job scoped — never freeze Carbon::now() across long-lived workers.
        $this->app->scoped(OtpRepository::class, fn (): OtpRepository => new OtpRepository);

        $this->app->bind('otp-code', fn ($app) => $app->make(OtpRepository::class));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../migrations' => database_path('migrations'),
        ], 'migrations');

        $this->publishes([
            __DIR__.'/../../config/otp-code.php' => config_path('otp-code.php'),
        ], 'config');

        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'otp_code');

        $this->commands([
            OtpCodeClearExpiredCommand::class,
        ]);
    }
}
