<?php

namespace App\Providers;

use App\Repositories\IncomeRepository;
use App\Services\DateParserService;
use App\Services\IncomeDuplicateCheckerService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        $this->app->bind(IncomeRepository::class, function ($app) {
            return new IncomeRepository();
        });
        $this->app->bind(DateParserService::class, function ($app) {
            return new DateParserService();
        });
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
