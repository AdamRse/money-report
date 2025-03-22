<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Income;
use App\Services\IncomeDuplicateCheckerService;

class DocumentParserService {
    protected IncomeDuplicateCheckerService $duplicateChecker;
    public array $_errors = [];

    public function __construct(IncomeDuplicateCheckerService $duplicateChecker) {
        $this->duplicateChecker = $duplicateChecker;
    }

    public function errorDisplayHTML(): string {
        $rt = "<div><ul>";
        foreach ($this->_errors as $error) {
            $rt .= "<li>$error</li>";
        }
        return "</ul></div>" . $rt;
    }

    /**
     * Patterns à détecter dans les libellés pour exclusion (les résultats restent affichés mais décochés)
     * @var array<string>
     */
    private array $excludePatterns = [
        'adam rousselle',
        'rousselle adam'
    ];

    /**
     * Patterns pour la détection des types de revenus
     * Key = pattern à détecter (insensible à la casse)
     * Value = ID du type de revenu dans la base de données
     * @var array<string, int>
     */
    private array $typePatterns = [
        'FRANCE TRAVAIL' => 3,
        'POLE EMPLOI' => 3,
        'CAF' => 2,
        'ALLOCATIONS FAMILIALES' => 2,
        'REMBOURSEMENT' => 6,
        'SALAIRE' => 4,
        'CHOMAGE' => 5
    ];

    /**
     * Parse un document pour retourner un tableau de revenus avec des données supplémentaires.
     * La méthode identifie le type de document en apelle le bon parseur.
     * @param string $file : Le contenu du fichier
     * @return array : les revenus (Income) identifiées par le parseur (modèle Income augenté). Peut être un tableau vide.
     */
    public function parseDocument(string $file): array {
        //On détermine quel type de document on doit parser. Si un nouveau type de document, créer une nouvelle méthode.
        //Pour l'instant on ne supporte que le document de la banque postale, CSV ou TSV
        return $this->LaBanquePostaleParser($file);
    }

    /**
     * Vérifie si un libellé doit être exclu
     */
    private function shouldExclude(string $description): bool {
        $description = strtolower($description);
        return collect($this->excludePatterns)
            ->contains(fn($pattern) => str_contains($description, strtolower($pattern)));
    }

    /**
     * Détermine le type de revenu en fonction du libellé
     */
    private function detectincome_types(string $description): ?int {
        $description = strtolower($description);
        foreach ($this->typePatterns as $pattern => $typeId) {
            if (str_contains($description, strtolower($pattern))) {
                return $typeId;
            }
        }
        return null;
    }

    private function LaBanquePostaleParser(string $file): array {
        // Détection automatique du délimiteur (plus fiable)
        $firstLine = strtok($file, "\n");
        $delimiter = ";"; // Par défaut

        if (substr_count($firstLine, ';') > substr_count($firstLine, ',') && substr_count($firstLine, ';') > substr_count($firstLine, "\t")) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, ',') > substr_count($firstLine, ';') && substr_count($firstLine, ',') > substr_count($firstLine, "\t")) {
            $delimiter = ',';
        } elseif (substr_count($firstLine, "\t") > 0) {
            $delimiter = "\t";
        }

        $lines = explode("\n", $file);

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
            $income_typesId = $this->detectincome_types($description);

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
