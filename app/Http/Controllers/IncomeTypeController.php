<?php
// app/Http/Controllers/income_typesController.php

namespace App\Http\Controllers;

use App\Http\Requests\income_types\Storeincome_typesRequest;
use App\Http\Requests\income_types\Updateincome_typesRequest;
use App\Models\IncomeType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class income_typesController extends Controller {
    /**
     * Affiche la liste des types de revenus
     */
    public function index(): View {
        $incomeTypes = IncomeType::all();
        return view('income-types.index', compact('incomeTypes'));
    }

    /**
     * Enregistre un nouveau type de revenu
     */
    public function store(Storeincome_typesRequest $request): RedirectResponse {
        try {
            IncomeType::create([
                'name' => $request->validated('name'),
                'description' => $request->validated('description'),
                'taxable' => $request->boolean('taxable'),
                'must_declare' => $request->boolean('must_declare'),
            ]);

            return redirect()
                ->route('income-types.index')
                ->with('success', 'Type de revenu créé avec succès');
        } catch (\Exception $e) {
            return redirect()
                ->route('income-types.index')
                ->with('error', 'Une erreur est survenue lors de la création du type de revenu : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Met à jour un type de revenu existant
     */
    public function update(Updateincome_typesRequest $request, string $id): RedirectResponse {
        try {
            $income_types = IncomeType::findOrFail($id);

            $income_types->update([
                'name' => $request->validated('name'),
                'description' => $request->validated('description'),
                'taxable' => $request->boolean('taxable'),
                'must_declare' => $request->boolean('must_declare'),
            ]);

            return redirect()
                ->route('income-types.index')
                ->with('success', 'Type de revenu modifié avec succès');
        } catch (\Exception $e) {
            return redirect()
                ->route('income-types.index')
                ->with('error', 'Une erreur est survenue lors de la modification : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime un type de revenu
     */
    public function destroy(string $id): RedirectResponse {
        try {
            $income_types = IncomeType::findOrFail($id);

            // Vérifier si le type est utilisé dans des revenus
            if ($income_types->incomes()->exists()) {
                return redirect()
                    ->route('income-types.index')
                    ->with('error', 'Impossible de supprimer ce type de revenu car il est utilisé par des revenus existants');
            }

            $income_types->delete();

            return redirect()
                ->route('income-types.index')
                ->with('success', 'Type de revenu supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()
                ->route('income-types.index')
                ->with('error', 'Une erreur est survenue lors de la suppression : ' . $e->getMessage());
        }
    }
}
