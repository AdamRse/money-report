<?php
// app/Services/IncomeStatisticsService.php

namespace App\Services;

use App\Models\Income;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class IncomeStatisticsService {
    /**
     * Calcule les statistiques pour une collection de revenus
     */
    public function calculateStatistics(Collection $incomes): array {
        if ($incomes->isEmpty()) {
            return $this->getEmptyStatistics();
        }

        return [
            'total' => $incomes->sum('amount'),
            'count' => $incomes->count(),
            'average' => $incomes->avg('amount'),
            'total_taxable' => $this->calculateTaxableTotal($incomes),
            'total_must_declare' => $this->calculatemust_declareTotal($incomes),
            'by_type' => $this->calculateStatsByType($incomes)
        ];
    }

    /**
     * Calcule le total des revenus taxables
     */
    private function calculateTaxableTotal(Collection $incomes): float {
        return $incomes->filter(function ($income) {
            return $income->incomeType->taxable;
        })->sum('amount');
    }

    /**
     * Calcule le total des revenus à déclarer
     */
    private function calculatemust_declareTotal(Collection $incomes): float {
        return $incomes->filter(function ($income) {
            return $income->incomeType->must_declare;
        })->sum('amount');
    }

    /**
     * Calcule les statistiques par type de revenu
     */
    private function calculateStatsByType(Collection $incomes): SupportCollection {
        return $incomes->groupBy('incomeType.name')
            ->map(function ($group) {
                return [
                    'total' => $group->sum('amount'),
                    'count' => $group->count(),
                    'average' => $group->avg('amount')
                ];
            });
    }

    /**
     * Retourne des statistiques vides
     */
    private function getEmptyStatistics(): array {
        return [
            'total' => 0,
            'count' => 0,
            'average' => 0,
            'total_taxable' => 0,
            'total_must_declare' => 0,
            'by_type' => collect()
        ];
    }
}
