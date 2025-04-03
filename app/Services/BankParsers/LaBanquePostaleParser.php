<?php

namespace App\Services\BankParsers;

use App\Abstract\BankParserAbstract;
use App\Models\Income;
use App\Traits\ErrorManagementTrait;
use Carbon\Carbon;

class LaBanquePostaleParser extends BankParserAbstract{

    use ErrorManagementTrait;

    public static function isParsable(string $document, string $filename): bool{
        $lignes = explode("\n", $document, 8);
        if(
            str_contains($lignes[0], "Numéro Compte") &&
            str_contains($lignes[1], "Type") &&
            str_contains($lignes[2], "Compte tenu en") &&
            str_contains($lignes[3], "Date")
        )
            return true;
        else
            return false;
    }

    public function parse(string $document = ""): array|false{
        if(empty($document)){
            if(empty($this->_document)){
                $this->errorAdd("Parseur de la banque postale : Aucun document passé pour trouver un délimiteur.");
                return false;
            }
            $document = $this->_document;
        }
        $this->findDelimiterInHead("", true);
        if(empty($this->_delimiter)){
            $this->errorAdd("Parseur de la banque postale : Impossible de trouver le délimiteur.");
            return false;
        }

        $lines = explode("\n", $document);
        $incomes = [];
        $allDates = [];

        //On cherche le format de date
        foreach($lines as $line){
            $firstFow = str_getcsv($line, $this->_delimiter)[0] ?? false;
            if($firstFow && $date = $this->dateParser->returnDateFromString($firstFow) )
                $allDates[]=$date;
        }
        $carbonDateFormat = $this->dateParser->findDateFormat($allDates);

        // Traitement des lignes après l'en-tête
        for ($i = 6; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $columns = str_getcsv($line, $this->_delimiter);
            if (count($columns) < 3) continue;

            $date = trim($columns[0]);
            $description = trim($columns[1], " \t\n\r\0\x0B\"");
            $amount = str_replace(',', '.', trim($columns[2]));

            // Vérifier si le montant est un nombre valide
            if (!is_numeric($amount) || floatval($amount) <= 0) continue;

            $shouldBeSelected = !$this->shouldExclude($description);
            $income_typesId = $this->detectIncome_types($description);

            $Model_Income = Income::make([ // Make permet de créer un modèle Income sans l'enregistrer en BDD, on l'utilise pour encapsuler la donnée
                'amount' => $amount,
                'income_date' => Carbon::createFromFormat($carbonDateFormat, $date),
                'income_type_id' => $income_typesId,
                'notes' => $description
            ]);

            // Détection des doublons
            $duplicate_level = $this->duplicateChecker->getDuplicateLevel($Model_Income);
            if ($duplicate_level > 0) {
                $shouldBeSelected = false;
            }

            $incomes[] = [
                'date' => $date,
                'description' => $description,
                'amount' => floatval($amount),
                'selected' => $shouldBeSelected,
                'income_type_id' => $income_typesId,
                'duplicate' => $duplicate_level
            ];
        }

        usort($incomes, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));

        return $incomes;
    }
}
