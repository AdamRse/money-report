<?php

namespace App\Providers;

use App\Factories\BankParserFactory;
use App\Interfaces\Factories\BankParserFactoryInterface;
use App\Interfaces\Repositories\IncomeRepositoryInterface;
use App\Interfaces\Services\DateParserServiceInterface;
use App\Interfaces\Services\DocumentParserServiceInterface;
use App\Interfaces\Services\FileEncodingServiceInterface;
use App\Interfaces\Services\IncomeDuplicateCheckerServiceInterface;
use App\Interfaces\Services\IncomeStatisticsServiceInterface;
use App\Repositories\IncomeRepository;
use App\Services\DateParserService;
use App\Services\DocumentParserService;
use App\Services\FileEncodingService;
use App\Services\IncomeDuplicateCheckerService;
use App\Services\IncomeStatisticsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        $this->app->bind(IncomeRepositoryInterface::class, IncomeRepository::class);
        $this->app->bind(DateParserServiceInterface::class, DateParserService::class);
        $this->app->bind(IncomeDuplicateCheckerServiceInterface::class, IncomeDuplicateCheckerService::class);
        $this->app->bind(DocumentParserServiceInterface::class, DocumentParserService::class);
        $this->app->bind(IncomeStatisticsServiceInterface::class, IncomeStatisticsService::class);
        $this->app->bind(BankParserFactoryInterface::class, BankParserFactory::class);
        $this->app->bind(FileEncodingServiceInterface::class, FileEncodingService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        //
    }
}
