<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeImport\ImportFileRequest;
use App\Http\Requests\IncomeImport\ImportIncomesRequest;
use App\Interfaces\Services\DateParserServiceInterface;
use App\Interfaces\Services\DocumentParserServiceInterface;
use App\Interfaces\Services\IncomeDuplicateCheckerServiceInterface;
use App\Repositories\IncomeRepository;
use App\Repositories\IncomeTypeRepository;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class IncomeImportController extends Controller{

    //services
    protected IncomeDuplicateCheckerServiceInterface $duplicateChecker;
    protected DateParserServiceInterface $dateParser;
    protected DocumentParserServiceInterface $documentParser;
    protected IncomeTypeRepository $incomeTypeRepository;
    protected IncomeRepository $incomeRepository;

    public function __construct(DocumentParserServiceInterface $documentParser, IncomeDuplicateCheckerServiceInterface $duplicateChecker, DateParserServiceInterface $dateParser, IncomeRepository $incomeRepository, IncomeTypeRepository $incomeTypeRepository) {
        $this->duplicateChecker = $duplicateChecker;
        $this->dateParser = $dateParser;
        $this->documentParser = $documentParser;
        $this->incomeRepository = $incomeRepository;
        $this->incomeTypeRepository = $incomeTypeRepository;
    }

    /**
     * Affiche le formulaire d'import
     */
    public function showForm():View{
        if($incomeTypeList = $this->incomeTypeRepository->selectAll())
            return view('imports.index', [
                'incomeTypes' => $incomeTypeList
            ]);
        else
            return view('error.index', [
                'title' => "La requête SQL a échoué",
                'message' => $this->incomeRepository->errorDisplayHTML("Aucune erreur n'a été renvoyée lors de la tentative de requête")
            ]);
    }

    /**
     * Traite le fichier uploadé et affiche les résultats
     */
    public function processFile(ImportFileRequest $request):View|RedirectResponse{
        $file = $request->getEncodedFile('bankFile');
        $parsedIncomes = $this->documentParser->parseDocument($file["content"], $file["name"]);

        if(!$incomeTypeList = $this->incomeTypeRepository->selectAll())
            return view('error.index', [
                'title' => "La requête SQL a échoué",
                'message' => $this->incomeRepository->errorDisplayHTML("Aucune erreur n'a été renvoyée lors de la tentative de requête")
            ]);

        if (!empty($parsedIncomes)) {
            return view('imports.index', [
                'incomes' => $parsedIncomes,
                'incomeTypes' => $incomeTypeList,
                'parseResults' => true
            ]);
        }

        return redirect()
            ->route('incomes.import')
            ->with('error', $this->documentParser->errorDisplayHTML("Aucun revenu détecté dans le document."));
    }

    /**
     * Importe les revenus sélectionnés
     */
    public function import(ImportIncomesRequest $request):RedirectResponse{
        $allIncomes = $request->validated('incomes');
        $selectedIncomes = collect($allIncomes)
            ->where('selected', true)
            ->whereNotNull('income_type_id')
            ->values();
        if($selectedIncomes->isEmpty()){
            return redirect()
                ->route('incomes.import')
                ->with('error', 'Aucun revenu sélectionné avec un type valide pour l\'import');
        }

        $allDates = $selectedIncomes->pluck('date')->toArray();
        if(!$dateFormat = $this->dateParser->findDateFormat($allDates))
            return redirect()
                ->route('incomes.import')
                ->with('error', $this->dateParser->errorDisplayHTML('Conflit dans le format de date'));

        foreach ($selectedIncomes as $incomeData) {
            $date = Carbon::createFromFormat($dateFormat, $incomeData['date']);

            $this->incomeRepository->createIfNotExists([
                'income_date' => $date->format('Y-m-d'),
                'amount' => $incomeData['amount'],
                'user_id' => Auth::id(),
                'income_type_id' => $incomeData['income_type_id'],
                'notes' => $incomeData['description']
            ]);
        }

        if($this->incomeRepository->isError()){
            return redirect()
                ->route('incomes.import')
                ->with('error', $this->incomeRepository->errorDisplayHTML('Impossible de créer certains revenus en base de données.'));
        }
        else{
            $numberIncomeCreated = count($selectedIncomes);
            return redirect()
                ->route('incomes.index')
                ->with('success', $numberIncomeCreated.(($numberIncomeCreated > 1) ? ' revenus ont été importés avec succès !' : ' revenu & été importé avec succès !'));
        }

    }
    // public function import(ImportIncomesRequest $request): RedirectResponse {
    //     try {
    //         // Récupérer toutes les données validées
    //         $allIncomes = $request->validated('incomes');

    //         // Filtrer les revenus qui ont la clé 'selected'
    //         $selectedIncomes = collect($allIncomes)
    //             ->filter(function ($income) {
    //                 return isset($income['selected']) && !empty($income['income_type_id']);
    //             })
    //             ->values();

    //         if ($selectedIncomes->isEmpty()) {
    //             return redirect()
    //                 ->route('incomes.import')
    //                 ->with('error', 'Aucun revenu sélectionné avec un type valide pour l\'import');
    //         }

    //         DB::beginTransaction();

    //         foreach ($selectedIncomes as $incomeData) {
    //             $date = \DateTime::createFromFormat('d/m/Y', $incomeData['date']);
    //             if (!$date) {
    //                 throw new \Exception('Format de date invalide : ' . $incomeData['date']);
    //             }

    //             Income::create([
    //                 'income_date' => $date->format('Y-m-d'),
    //                 'amount' => $incomeData['amount'],
    //                 'user_id' => Auth::id(),
    //                 'income_type_id' => $incomeData['income_type_id'],
    //                 'notes' => $incomeData['description']
    //             ]);
    //         }

    //         DB::commit();

    //         return redirect()
    //             ->route('incomes.index')
    //             ->with('success', count($selectedIncomes) . ' revenus ont été importés avec succès');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()
    //             ->route('incomes.import')
    //             ->with('error', 'Erreur lors de l\'import des revenus : ' . $e->getMessage());
    //     }
    // }
}
