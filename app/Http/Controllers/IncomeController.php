<?php

namespace App\Http\Controllers;

use App\Http\Requests\Income\StoreIncomeRequest;
use App\Http\Requests\Income\UpdateIncomeRequest;
use App\Interfaces\Repositories\IncomeRepositoryInterface;
use App\Interfaces\Services\IncomeDuplicateCheckerServiceInterface;
use App\Models\Income;
use App\Models\IncomeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class IncomeController extends Controller {

    // Injecter le service dans le constructeur
    protected IncomeDuplicateCheckerServiceInterface $duplicateChecker;
    protected IncomeRepositoryInterface $incomeRepository;

    public function __construct(IncomeDuplicateCheckerServiceInterface $duplicateChecker, IncomeRepositoryInterface $incomeRepository) {
        $this->duplicateChecker = $duplicateChecker;
        $this->incomeRepository = $incomeRepository;
    }
    /**
     * Affiche la liste des revenus pour l'année sélectionnée
     */
    public function index(Request $request): View {
        $selectedYear = $request->input('year_filter', date('Y'));
        $incomes = $this->incomeRepository->getUserIncomesByYear($selectedYear);
        if($this->incomeRepository->isError())
            return view('error.index', ["title" => "La requête retourne une erreur.", "message" => $this->incomeRepository->errorDisplayHTML()]);

        $incomeTypes = IncomeType::all();
        return view('incomes.index', compact('incomeTypes', 'incomes', 'selectedYear'));
    }

    /**
     * Enregistre un nouveau revenu
     * Peut également créer un nouveau type de revenu si nécessaire
     */
    public function store(StoreIncomeRequest $request): RedirectResponse {
        Log::info('Store un revenu : ', ['request' => $request->all()]);
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Création d'un nouveau type de revenu si demandé
            if ($validated['income_type_id'] == 0) {
                $income_types = IncomeType::create([
                    'name' => $validated['new_type_name'],
                    'description' => $validated['new_type_description'] ?? null,
                    'taxable' => $validated['taxable'] ?? false,
                    'must_declare' => $validated['must_declare'] ?? false
                ]);

                $validated['income_type_id'] = $income_types->id;
            }

            // Création du revenu
            Income::create([
                'amount' => $validated['amount'],
                'income_date' => $validated['income_date'],
                'income_type_id' => $validated['income_type_id'],
                'notes' => $validated['notes'] ?? null
            ]);

            DB::commit();

            return redirect()
                ->route('incomes.index')
                ->with('success', 'Le revenu a été enregistré avec succès');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('incomes.index')
                ->with('error', 'Une erreur est survenue lors de l\'enregistrement : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Met à jour un revenu existant
     */
    public function update(UpdateIncomeRequest $request, string $id): RedirectResponse {
        try {
            $income = Income::findOrFail($id);
            $income->update($request->validated());

            return redirect()
                ->route('incomes.index')
                ->with('success', 'Le revenu a été modifié avec succès');
        } catch (\Exception $e) {
            return redirect()
                ->route('incomes.index')
                ->with('error', 'Une erreur est survenue lors de la modification : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime un revenu
     */
    public function destroy(string $id): RedirectResponse {
        try {
            $income = Income::findOrFail($id);
            $income->delete();

            return redirect()
                ->route('incomes.index')
                ->with('success', 'Le revenu a été supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()
                ->route('incomes.index')
                ->with('error', 'Une erreur est survenue lors de la suppression : ' . $e->getMessage());
        }
    }
}
