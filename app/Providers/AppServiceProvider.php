<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Http\Controllers\Auth\LoginResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        try {
            if (Schema::hasTable('system_configurations')) {
                $configs = \App\Models\SystemConfiguration::all();
                foreach ($configs as $config) {
                    \Illuminate\Support\Facades\Config::set($config->key, $config->value);
                }
            }
        } catch (\Exception $e) {
            // Ignore if DB not ready
        }
    }
}
