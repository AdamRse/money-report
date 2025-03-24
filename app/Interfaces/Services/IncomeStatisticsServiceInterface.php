<?php

namespace App\Interfaces\Services;

use Illuminate\Database\Eloquent\Collection;

interface IncomeStatisticsServiceInterface {
    public function calculateStatistics(Collection $incomes): array;
}
