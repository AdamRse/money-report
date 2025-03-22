<?php

namespace App\Repositories;

use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IncomeRepository {
    /**
     * Vérifie si un revenu avec le même montant et la même date existe déjà
     *
     * @param Income $income Le revenu à vérifier
     * @return array Un tableau des revenus correspondants, vide si aucun doublon
     */
    public static function findDuplicates(Income $income): array {
        return Income::where('amount', $income->amount)
            ->whereDate('income_date', $income->income_date)
            ->get()
            ->toArray();
    }
}
