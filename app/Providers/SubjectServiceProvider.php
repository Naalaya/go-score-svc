<?php

namespace App\Providers;

use App\Console\Validation\ScoreValidationService;
use App\Contracts\ScoringServiceInterface;
use App\Contracts\SubjectServiceInterface;
use App\Services\Subjects\ScoringService;
use App\Services\Subjects\SubjectService;
use Illuminate\Support\ServiceProvider;

class SubjectServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Subject Service
        $this->app->bind(SubjectServiceInterface::class, SubjectService::class);

        // Register Scoring Service
        $this->app->bind(ScoringServiceInterface::class, ScoringService::class);

        // Register as singletons for performance
        $this->app->singleton(SubjectService::class, function ($app) {
            return new SubjectService();
        });

        $this->app->singleton(ScoringService::class, function ($app) {
            return new ScoringService();
        });

        // Register Validation Service
        $this->app->singleton(ScoreValidationService::class, function ($app) {
            return new ScoreValidationService(
                $app->make(SubjectServiceInterface::class),
                $app->make(ScoringServiceInterface::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Config caching optimization
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/subjects.php' => config_path('subjects.php'),
                __DIR__.'/../../config/scoring.php' => config_path('scoring.php'),
            ], 'subject-config');
        }
    }
}
