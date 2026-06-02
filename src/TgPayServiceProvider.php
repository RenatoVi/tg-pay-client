<?php

declare(strict_types=1);

namespace TechGenus\TgPay;

use Illuminate\Support\ServiceProvider;
use TechGenus\TgPay\Config\TgPayConfig;

class TgPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tgpay.php', 'tgpay');

        $this->app->singleton(TgPayConfig::class, function () {
            return TgPayConfig::fromArray(config('tgpay', []));
        });

        $this->app->singleton(Client::class, function () {
            $config = $this->app->make(TgPayConfig::class);
            return new Client($config);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/tgpay.php' => config_path('tgpay.php'),
            ], 'tgpay-config');
        }
    }
}
