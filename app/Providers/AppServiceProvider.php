<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Task;
use App\Observers\InvoiceObserver;
use App\Observers\ProjectObserver;
use App\Observers\TaskObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Observers for Activity Logging
        Invoice::observe(InvoiceObserver::class);
        Project::observe(ProjectObserver::class);
        Task::observe(TaskObserver::class);
    }
}