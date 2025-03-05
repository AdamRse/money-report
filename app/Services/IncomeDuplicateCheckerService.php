<?php

namespace App\Services;

use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class IncomeDuplicateCheckerService {

    /**
     * Vérifie si un revenu avec le même montant et la même date existe déjà
     *
     * @param Income $income Le revenu à vérifier
     * @return array Un tableau des revenus correspondants, vide si aucun doublon
     */
    public function getDuplicateLevel(Income $income): array { // [A FAIRE] : doit se servir de IncomeRepository::findDuplicates() pour déterminer s'il y a un doublon, et quel est le niveau de suspicion du doublon
        return [];
    }
}
