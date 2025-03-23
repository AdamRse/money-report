<?php

namespace App\Providers;

use App\Repositories\IncomeRepository;
use App\Services\DateParserService;
use App\Services\DocumentParserService;
use App\Services\IncomeDuplicateCheckerService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    // public function register(): void {
    //     $this->app->bind(IncomeRepository::class, function ($app) {
    //         return new IncomeRepository();
    //     });
    //     $this->app->bind(DateParserService::class, function ($app) {
    //         return new DateParserService();
    //     });
    //     $this->app->bind(IncomeDuplicateCheckerService::class, function ($app) {
    //         return new IncomeDuplicateCheckerService();
    //     });
    //     $this->app->bind(DocumentParserService::class, function ($app) {
    //         return new DocumentParserService($app->make(IncomeDuplicateCheckerService::class));
    //     });
    // }
    public function register(): void {
        $this->app->bind(IncomeRepository::class, IncomeRepository::class);
        $this->app->bind(DateParserService::class, DateParserService::class);
        $this->app->bind(IncomeDuplicateCheckerService::class, IncomeDuplicateCheckerService::class);
        $this->app->bind(DocumentParserService::class, DocumentParserService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        //
    }
}
