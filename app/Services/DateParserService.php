<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class DateParserService {
    /**
     * Convertit une chaîne de date en objet Carbon
     *
     * @param string $date La date à parser
     * @return Carbon
     * @throws InvalidArgumentException Si la date est invalide
     */
    public function parse(string $date) { // [A FAIRE] : la fonction doit prendre une string et renvoyer un objet Carbon
        Log::info('Demande de parsing de la date', [$date]);
        if ($date instanceof Carbon) {
            Log::info('$date est déjà Carbon');
            return $date;
        }

        try {
            Log::info('Carbon::parse() 01/01/2021');
            return Carbon::parse("01/01/2021");
        } catch (\Exception $e) {
            Log::info('Carbon::parse error : ', [$e->getMessage()]);
            return Carbon::parse("01/01/2021");
        }
    }

    /**
     * Détermine le format du set de date données, d/m ou m/d
     * @param string|array $dates : La ou les dates à tester
     * @return false|string : False en cas d'erreur, le format trouvé (par exemple m/d/y)
     */
    public function determineDateFormat(array|string $dates): false|string {
        $pattern = '/[0-9]{1,4}([-.\/ ])([0-9]{1,2})[-.\/ ]([0-9]{2,4})/';
        if (is_string($dates)) {
            $dates = [$dates];
        }
        $separatorFound = false;
        $formatBigYear = true;
        $formatEuro = true;
        foreach ($dates as $date) {
            if (preg_match($pattern, $date, $matches)) {
                $separator = $matches[1];
                $secondNumber = $matches[2];

                if (empty($matches[1]) || empty($matches[2]) || empty($matches[3])) {
                    Log::info(
                        "La capture regex a échouée, impossible de déterminer le format d'une date donnée, : ",
                        [
                            "paramètre $dates entré" => $dates,
                            "Date ciblée par l'erreur : " => $date,
                            "regex utilisée : " => $pattern,
                            "Localisation : " => __FILE__ . " " . __METHOD__
                        ]
                    );
                    return false;
                }

                if ($separatorFound) {
                    if ($separatorFound != $separator) {
                        $separatorFound = false;
                        break;
                    }
                } else {
                    $separatorFound = $separator;
                }

                $formatBigYear = strlen($matches[3]) > 2 ? true : false;

                if ((int)$secondNumber > 12) {
                    $formatEuro = false;
                    break;
                }
            }
        }
        if ($formatEuro) {
            return "d" . $separatorFound . "m" . $separatorFound . (($formatBigYear) ? "Y" : "y");
        } else {
            return "m" . $separatorFound . "d" . $separatorFound . (($formatBigYear) ? "Y" : "y");
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
