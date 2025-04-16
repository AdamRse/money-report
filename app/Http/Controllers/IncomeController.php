<?php

namespace App\Http\Controllers;

use App\Http\Requests\Income\StoreIncomeRequest;
use App\Http\Requests\Income\UpdateIncomeRequest;
use App\Interfaces\Repositories\IncomeRepositoryInterface;
use App\Interfaces\Services\IncomeDuplicateCheckerServiceInterface;
use App\Models\Income;
use App\Models\IncomeType;
use App\Repositories\IncomeTypeRepository;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class IncomeController extends Controller{

    // Injecter le service dans le constructeur
    protected IncomeDuplicateCheckerServiceInterface $duplicateChecker;
    protected IncomeRepositoryInterface $incomeRepository;
    protected IncomeTypeRepository $incomeTypeRepository;

    public function __construct(IncomeDuplicateCheckerServiceInterface $duplicateChecker, IncomeRepositoryInterface $incomeRepository, IncomeTypeRepository $incomeTypeRepository) {
        $this->duplicateChecker = $duplicateChecker;
        $this->incomeRepository = $incomeRepository;
        $this->incomeTypeRepository = $incomeTypeRepository;
    }

    /**
     * Affiche la liste des revenus pour l'année sélectionnée
     */
    public function index(Request $request):View{
        $selectedYear = $request->input('year_filter', date('Y'));

        $incomes = $this->incomeRepository->getUserIncomesByYear($selectedYear);
        if(!$incomes)
            return view('error.index', ["title" => "La requête retourne une erreur.", "message" => $this->incomeRepository->errorDisplayHTML("Impossible de récupérer la liste des revenus.")]);

        if($incomeTypes = $this->incomeTypeRepository->selectAll())
            return view('incomes.index', compact('incomeTypes', 'incomes', 'selectedYear'));
        else
            return view('error.index', ["title" => "La requête retourne une erreur.", "message" => $this->incomeRepository->errorDisplayHTML("Impossible de récupérer la liste des types de revenus.")]);
    }

    /**
     * Enregistre un nouveau revenu
     * Peut également créer un nouveau type de revenu si nécessaire
     */
    public function store(StoreIncomeRequest $request):RedirectResponse{
        $validatedForm = $request->validated();

        // Création d'un nouveau type de revenu si demandé
        if($validatedForm['income_type_id'] == 0){
            $incomeType = $this->incomeTypeRepository->createIfNotExists([
                'name' => $validatedForm['new_type_name'],
                'description' => $validatedForm['new_type_description'] ?? null,
                'taxable' => $validatedForm['taxable'] ?? false,
                'must_declare' => $validatedForm['must_declare'] ?? false
            ]);
        }
        else{
            $incomeType = $this->incomeTypeRepository->selectId($validatedForm['income_type_id']);
        }
        if(!$incomeType){
            return redirect()
                ->route('incomes.index')
                ->with('error', $this->incomeRepository->errorDisplayHTML("L'enregistrement a été refusé à cause d'un type de revenu incohérent. "))
                ->withInput();
        }
        // Création du revenu
        $income = $this->incomeRepository->createIfNotExists([
            'amount' => $validatedForm['amount'],
            'income_date' => $validatedForm['income_date'],
            'income_type_id' => $incomeType->id,
            'notes' => $validatedForm['notes'] ?? null
        ]);
        if(!$income){
            return redirect()
                ->route('incomes.index')
                ->with('error', $this->incomeTypeRepository->errorDisplayHTML("L'enregistrement a été refusé à cause d'un revenu incohérent."))
                ->withInput();
        }

        return redirect()
            ->route('incomes.index')
            ->with('success', 'Le revenu a été enregistré avec succès');
    }

    /**
     * Met à jour un revenu existant
     */
    public function update(UpdateIncomeRequest $request, string $id):RedirectResponse{
        $income = $request->validated();
        $income['id']=$id;

        if($updatedIncome = $this->incomeRepository->update($income))
            return redirect()
                ->route('incomes.index')
                ->with('success', 'Le revenu a été modifié avec succès !')
                ->with('income', $updatedIncome);
        else{
            return redirect()
                ->route('incomes.index')
                ->with('error', $this->incomeRepository->errorDisplayHTML("Une erreur est survenue lors de la modification."))
                ->withInput();
        }
    }

    /**
     * Supprime un revenu
     */
    public function destroy(string $id):RedirectResponse{
        if($this->incomeRepository->delete($id))
            return redirect()
                ->route('incomes.index')
                ->with('success', 'Le revenu a été supprimé avec succès');
        else{
            return redirect()
            ->route('incomes.index')
            ->with('error', $this->incomeRepository->errorDisplayHTML("Une erreur est survenue lors de la suppression"));
        }
    }
}
