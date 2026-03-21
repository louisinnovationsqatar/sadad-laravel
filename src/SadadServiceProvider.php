<?php
// Built by Louis Innovations (www.louis-innovations.com)

namespace LouisInnovations\SadadLaravel;

use Illuminate\Support\ServiceProvider;
use LouisInnovations\Sadad\SadadClient;
use LouisInnovations\Sadad\SadadConfig;
use LouisInnovations\SadadLaravel\Console\SadadTestCommand;

class SadadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package services.
     */
    public function boot(): void
    {
        $this->publishConfig();
        $this->publishMigrations();
        $this->registerRoutes();
    }

    /**
     * Register package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sadad.php',
            'sadad'
        );

        $this->registerSadadClient();
        $this->registerCommands();
    }

    /**
     * Register the SadadClient as a singleton in the container.
     */
    protected function registerSadadClient(): void
    {
        $this->app->singleton(SadadClient::class, function ($app) {
            $config = new SadadConfig(
                merchantId:  (string) config('sadad.merchant_id'),
                secretKey:   (string) config('sadad.secret_key'),
                website:     (string) config('sadad.website'),
                environment: (string) config('sadad.environment', 'test'),
                language:    (string) config('sadad.language', 'eng'),
                callbackUrl: config('sadad.callback_url') ?: null,
                webhookUrl:  config('sadad.webhook_url') ?: null,
            );

            return new SadadClient($config);
        });

        // Allow resolution by the facade accessor string as well
        $this->app->alias(SadadClient::class, 'sadad');
    }

    /**
     * Register the Artisan commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SadadTestCommand::class,
            ]);
        }
    }

    /**
     * Publish the config file.
     */
    protected function publishConfig(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [__DIR__ . '/../config/sadad.php' => config_path('sadad.php')],
                'sadad-config'
            );
        }
    }

    /**
     * Publish the optional migration.
     */
    protected function publishMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                [
                    __DIR__ . '/../database/migrations/' => database_path('migrations'),
                ],
                'sadad-migrations'
            );
        }
    }

    /**
     * Register the built-in SADAD routes (callback + webhook endpoints).
     */
    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/sadad.php');
    }
}
