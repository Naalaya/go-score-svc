<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\ScoreServiceInterface;
use App\Services\ScoreService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ScoreServiceInterface::class, ScoreService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
