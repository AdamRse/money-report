<?php

namespace App\Interfaces\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface IncomeStatisticsServiceInterface {
    public function calculateStatistics(Collection $incomes): array;
}
