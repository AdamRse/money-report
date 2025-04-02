<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeImport\ImportFileRequest;
use App\Http\Requests\IncomeImport\ImportIncomesRequest;
use App\Interfaces\Services\DateParserServiceInterface;
use App\Interfaces\Services\DocumentParserServiceInterface;
use App\Interfaces\Services\IncomeDuplicateCheckerServiceInterface;
use App\Models\Income;
use App\Models\IncomeType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class IncomeImportController extends Controller {

    //services
    protected IncomeDuplicateCheckerServiceInterface $duplicateChecker;
    protected DateParserServiceInterface $dateParser;
    protected DocumentParserServiceInterface $documentParser;

    public function __construct(DocumentParserServiceInterface $documentParser, IncomeDuplicateCheckerServiceInterface $duplicateChecker, DateParserServiceInterface $dateParser) {
        $this->duplicateChecker = $duplicateChecker;
        $this->dateParser = $dateParser;
        $this->documentParser = $documentParser;
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
        $file = $request->getEncodedFile('bankFile');
        $parsedIncomes = $this->documentParser->parseDocument($file["content"], $file["name"]);

        if (!empty($parsedIncomes)) {
            return view('imports.index', [
                'incomes' => $parsedIncomes,
                'incomeTypes' => IncomeType::all(),
                'parseResults' => true
            ]);
        }

        $messageError = ($this->documentParser->isError())
            ? 'Erreur lors du parsing : ' . $this->documentParser->errorDisplayHTML()
            : "Aucun revenu détecté dans le document.";

        return redirect()
            ->route('incomes.import')
            ->with('error', $messageError);
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
