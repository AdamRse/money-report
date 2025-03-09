<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class DateParserService {
    /**
     * Convertit une chaîne de date en objet Carbon
     *
     * @param string|mixed $date La date à parser
     * @return Carbon
     * @throws InvalidArgumentException Si la date est invalide
     */
    public function parse($date) { // [A FAIRE] : la fonction doit prendre une string et renvoyer un objet Carbon
        Log::info('Demande de parsing de la date');
        info('Demande de parsing de la date');
        if ($date instanceof Carbon) {
            return $date;
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            Log::info('Carbon::parse error : ', $e->getMessage());
            dump('Carbon::parse error : ', $e->getMessage());
        }
    }

    /**
     * Formate une date au format Y-m-d
     *
     * @param string|Carbon $date La date à formater
     * @return string
     */
    public function formatForDatabase($date): string {
        return $this->parse($date)->format('Y-m-d');
    }

    /**
     * Tente de convertir une date au format français (JJ/MM/AAAA) en objet Carbon
     *
     * @param string $date La date au format français
     * @return Carbon|null L'objet Carbon ou null si le format est invalide
     */
    public function parseFromFrenchFormat(string $date): ?Carbon {
        $dateObj = \DateTime::createFromFormat('d/m/Y', $date);

        if (!$dateObj) {
            return null;
        }

        return Carbon::instance($dateObj);
    }
}
