<?php

namespace App\Providers;

use App\Services\IncomeDuplicateCheckerService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        $this->app->bind(IncomeDuplicateCheckerService::class, function ($app) {
            return new IncomeDuplicateCheckerService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        //
    }
}
