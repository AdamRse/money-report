<?php

namespace App\Services\BankParser;

use App\Abstract\BankParserAbstract;
use App\Models\Income;

class LaBanquePostaleParser extends BankParserAbstract{

    public function parse(string $document): array|false{
        // Détection automatique du délimiteur (plus fiable)
        $firstLine = strtok($document, "\n");
        $delimiter = ";"; // Par défaut

        if (substr_count($firstLine, ';') > substr_count($firstLine, ',') && substr_count($firstLine, ';') > substr_count($firstLine, "\t")) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, ',') > substr_count($firstLine, ';') && substr_count($firstLine, ',') > substr_count($firstLine, "\t")) {
            $delimiter = ',';
        } elseif (substr_count($firstLine, "\t") > 0) {
            $delimiter = "\t";
        }

        $lines = explode("\n", $document);

        // Afficher les premières lignes pour le débogage
        // echo "Premières lignes du fichier:";
        // var_dump(array_slice($lines, 0, 5));

        // Recherche de la ligne d'en-tête avec plus de flexibilité
        $headerIndex = -1;
        $dateKeywords = ['date', 'jour', 'day'];
        $amountKeywords = ['amount', 'montant', 'somme', 'valeur', 'crédit'];

        foreach ($lines as $index => $line) {
            $line = strtolower($line); // Convertir en minuscules pour recherche insensible à la casse

            $hasDateKeyword = false;
            foreach ($dateKeywords as $keyword) {
                if (str_contains($line, strtolower($keyword))) {
                    $hasDateKeyword = true;
                    break;
                }
            }

            $hasAmountKeyword = false;
            foreach ($amountKeywords as $keyword) {
                if (str_contains($line, strtolower($keyword))) {
                    $hasAmountKeyword = true;
                    break;
                }
            }

            if ($hasDateKeyword && $hasAmountKeyword) {
                $headerIndex = $index;
                break;
            }
        }

        // Si aucun en-tête reconnu, utiliser la première ligne
        if ($headerIndex === -1) {
            // On peut soit utiliser la première ligne comme en-tête
            $headerIndex = 0;

            // Ou déclencher une exception
            // throw new \Exception("Format de fichier invalide : impossible de trouver l'en-tête des colonnes");
        }

        $incomes = [];

        // Traitement des lignes après l'en-tête
        for ($i = $headerIndex + 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $columns = str_getcsv($line, $delimiter);
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
                'income_date' => $date,
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
