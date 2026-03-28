<?php

namespace App\Providers;

use App\Services\AssessmentService;
use App\Services\StageService;
use App\Services\SubmissionService;
use App\Services\TaskService;
use App\Services\Interfaces\AssessmentServiceInterface;
use App\Services\Interfaces\StageServiceInterface;
use App\Services\Interfaces\SubmissionServiceInterface;
use App\Services\Interfaces\TaskServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(StageServiceInterface::class, StageService::class);
        $this->app->bind(TaskServiceInterface::class, TaskService::class);
        $this->app->bind(SubmissionServiceInterface::class, SubmissionService::class);
        $this->app->bind(AssessmentServiceInterface::class, AssessmentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
