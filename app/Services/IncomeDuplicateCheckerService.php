<?php

namespace App\Services;

use App\Models\Income;
use Carbon\Carbon;

class IncomeDuplicateCheckerService {
    /**
     * Vérifie si un revenu avec la même date et le même montant existe déjà
     *
     * @param float $amount Le montant du revenu
     * @param string|Carbon $date La date du revenu
     * @return array Un tableau des revenus correspondants, vide si aucun doublon
     */
    public function checkDuplicate(float $amount, $date): array {
        // Normalisation de la date
        if ($date instanceof Carbon) {
            $formattedDate = $date->format('Y-m-d');
        } else {
            $formattedDate = Carbon::parse($date)->format('Y-m-d');
        }

        // Recherche des revenus avec la même date et le même montant
        $duplicates = Income::where('amount', $amount)
            ->whereDate('income_date', $formattedDate)
            ->get()
            ->toArray();

        return $duplicates;
    }
}
