<?php

namespace App\Services;

use App\Interfaces\Services\IncomeDuplicateCheckerServiceInterface;
use App\Models\Income;
use Illuminate\Support\Facades\Log;
use App\Repositories\IncomeRepository;

class IncomeDuplicateCheckerService implements IncomeDuplicateCheckerServiceInterface {

    /**
     * Vérifie si un revenu avec le même montant et la même date existe déjà
     *
     * @param Income $income : Le revenu à vérifier
     * @return int : plus le chiffre est élevé, plus la suspicion de boublon est forte.
     *      0 = aucun doublon certifié
     *      1 = Doublon potentiel (même montant et même date)
     *      2 = Doublon probable
     *      3 = doublon très probable
     *      4 = doublon certain
     */
    public function getDuplicateLevel(Income $income): int {
        $duplicates = IncomeRepository::findDuplicates($income);
        $sus = 0;
        if (!empty($duplicates[0])) {
            $sus++;
            if ($duplicates[0]->notes == $income->notes)
                $sus++;
            if ($duplicates[0]->created_at->diffInSeconds($income->created_at) < 2)
                $sus += 2;
        }
        return $sus;
    }
}
