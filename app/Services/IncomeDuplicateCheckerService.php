<?php

namespace App\Services;

use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        try {
            $formattedDate = $date instanceof Carbon
                ? $date->format('Y-m-d')
                : Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');

            // Recherche des revenus avec la même date et le même montant
            return Income::where('amount', $amount)
                ->whereDate('income_date', $formattedDate)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::warning('Erreur de parsing de date', ['date' => $date, 'error' => $e->getMessage()]);
            return [];
        }
    }
}
