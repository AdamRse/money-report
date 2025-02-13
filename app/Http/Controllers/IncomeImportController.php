<?php
// app/Http/Controllers/IncomeImportController.php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeImport\ImportFileRequest;
use App\Http\Requests\IncomeImport\ImportIncomesRequest;
use App\Models\Income;
use App\Models\income_types;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IncomeImportController extends Controller {
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
     * Affiche le formulaire d'import ou traite le fichier uploadé
     */
    public function showForm(ImportFileRequest $request): View|RedirectResponse {
        if (!$request->isMethod('post')) {
            return view('incomes.import', [
                'income_typess' => income_types::all()
            ]);
        }

        try {
            $parsedIncomes = $this->parseFile($request->file('bankFile'));

            return view('incomes.import', [
                'incomes' => $parsedIncomes,
                'income_typess' => income_types::all(),
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
            $selectedIncomes = collect($request->validated('incomes'))
                ->filter(fn($income) => isset($income['selected']))
                ->values();

            if ($selectedIncomes->isEmpty()) {
                return redirect()
                    ->route('incomes.import')
                    ->with('error', 'Aucun revenu sélectionné pour l\'import');
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

    /**
     * Parse le fichier uploadé et retourne les revenus détectés
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return array<int, array<string, mixed>>
     * @throws \Exception
     */
    private function parseFile($file): array {
        $content = file_get_contents($file->path());
        $delimiter = str_contains($file->getClientOriginalName(), '.tsv') ? "\t" : ";";
        $lines = explode("\n", $content);

        // Recherche de la ligne d'en-tête
        $headerIndex = -1;
        foreach ($lines as $index => $line) {
            if (str_contains($line, 'Date') && str_contains($line, 'amount')) {
                $headerIndex = $index;
                break;
            }
        }

        if ($headerIndex === -1) {
            throw new \Exception("Format de fichier invalide : impossible de trouver l'en-tête des colonnes");
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

            if (floatval($amount) <= 0) continue;

            $shouldBeSelected = !$this->shouldExclude($description);
            $income_typesId = $this->detectincome_types($description);

            $incomes[] = [
                'date' => $date,
                'description' => $description,
                'amount' => floatval($amount),
                'selected' => $shouldBeSelected,
                'income_type_id' => $income_typesId
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
}
