<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeImport\ImportFileRequest;
use App\Http\Requests\IncomeImport\ImportIncomesRequest;
use App\Models\Income;
use App\Models\IncomeType;
use App\Services\IncomeDuplicateCheckerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class IncomeImportController extends Controller {
    // Injecter le service dans le constructeur
    protected IncomeDuplicateCheckerService $duplicateChecker;

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

    public function __construct(IncomeDuplicateCheckerService $duplicateChecker) {
        $this->duplicateChecker = $duplicateChecker;
    }

    /**
     * Parse le fichier uploadé et retourne les revenus détectés
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return array<int, array<string, mixed>>
     * @throws \Exception
     */
    private function parseFile($file): array {
        $content = file_get_contents($file->path());

        // Détection automatique du délimiteur (plus fiable)
        $firstLine = strtok($content, "\n");
        $delimiter = ";"; // Par défaut

        if (substr_count($firstLine, ';') > substr_count($firstLine, ',') && substr_count($firstLine, ';') > substr_count($firstLine, "\t")) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, ',') > substr_count($firstLine, ';') && substr_count($firstLine, ',') > substr_count($firstLine, "\t")) {
            $delimiter = ',';
        } elseif (substr_count($firstLine, "\t") > 0) {
            $delimiter = "\t";
        }

        $lines = explode("\n", $content);

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

            // Détection des doublons
            $duplicate_level = 0;
            $duplicates = $this->duplicateChecker->checkDuplicate(floatval($amount), $date);

            if (!empty($duplicates)) {
                // Niveau 1: Même montant et même date (déjà vérifié par checkDuplicate)
                $duplicate_level = 1;

                // Niveau 2: Vérifier si le libellé est similaire à un des doublons
                foreach ($duplicates as $duplicate) {
                    if (
                        !empty($duplicate['notes']) && !empty($description) &&
                        (stripos($duplicate['notes'], $description) !== false ||
                            stripos($description, $duplicate['notes']) !== false)
                    ) {
                        $duplicate_level = 2;
                        break;
                    }
                }

                if ($duplicate_level > 0) {
                    $shouldBeSelected = false;
                }
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

    /**
     * Affiche le formulaire d'import
     */
    public function showForm(): View {
        return view('imports.index', [
            'incomeTypes' => IncomeType::all()
        ]);
    }

    /**
     * Traite le fichier uploadé et affiche les résultats
     */
    public function processFile(ImportFileRequest $request): View|RedirectResponse {
        try {
            $parsedIncomes = $this->parseFile($request->file('bankFile'));

            return view('imports.index', [
                'incomes' => $parsedIncomes,
                'incomeTypes' => IncomeType::all(),
                'parseResults' => true
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->route('incomes.import')
                ->with('error', 'Erreur lors du parsing : ' . $e->getMessage());
        }
    }

    /**
     * Importe les revenus sélectionnés
     */
    public function import(ImportIncomesRequest $request): RedirectResponse {
        try {
            // Récupérer toutes les données validées
            $allIncomes = $request->validated('incomes');

            // Filtrer les revenus qui ont la clé 'selected'
            $selectedIncomes = collect($allIncomes)
                ->filter(function ($income) {
                    return isset($income['selected']) && !empty($income['income_type_id']);
                })
                ->values();

            if ($selectedIncomes->isEmpty()) {
                return redirect()
                    ->route('incomes.import')
                    ->with('error', 'Aucun revenu sélectionné avec un type valide pour l\'import');
            }

            DB::beginTransaction();

            foreach ($selectedIncomes as $incomeData) {
                $date = \DateTime::createFromFormat('d/m/Y', $incomeData['date']);
                if (!$date) {
                    throw new \Exception('Format de date invalide : ' . $incomeData['date']);
                }

                Income::create([
                    'income_date' => $date->format('Y-m-d'),
                    'amount' => $incomeData['amount'],
                    'income_type_id' => $incomeData['income_type_id'],
                    'notes' => $incomeData['description']
                ]);
            }

            DB::commit();

            return redirect()
                ->route('incomes.index')
                ->with('success', count($selectedIncomes) . ' revenus ont été importés avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('incomes.import')
                ->with('error', 'Erreur lors de l\'import des revenus : ' . $e->getMessage());
        }
    }
}
