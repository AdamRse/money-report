<?php

namespace App\Http\Controllers;

use App\Models\Revenu;
use App\Models\TypeRevenu;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SingletonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $typeRevenus = TypeRevenu::all();
        return view('accueil', compact('typeRevenus'));
    }

    public function list(Request $request)
    {
        // Validation des filtres
        $validated = $request->validate([
            'filter_type' => 'nullable|in:period,month',
            'start_date' => 'nullable|date|required_if:filter_type,period',
            'end_date' => 'nullable|date|required_if:filter_type,period|after_or_equal:start_date',
            'month_number' => 'nullable|required_if:filter_type,month|integer|between:1,12',
            'year_number' => 'nullable|required_if:filter_type,month|integer|min:1900',
        ]);

        // Construction de la requête de base
        $query = Revenu::with('typeRevenu')
                      ->orderBy('date_revenu', 'desc');

        // Application des filtres
        if ($request->filled('filter_type')) {
            if ($request->filter_type === 'period') {
                $query->whereBetween('date_revenu', [
                    $request->start_date,
                    $request->end_date
                ]);
            } elseif ($request->filter_type === 'month') {
                $query->whereYear('date_revenu', $request->year_number)
                      ->whereMonth('date_revenu', $request->month_number);
            }
        }

        // Récupération des revenus et calcul des statistiques
        $revenus = $query->get();

        // Calcul des totaux
        $stats = [
            'total' => $revenus->sum('montant'),
            'count' => $revenus->count(),
            'average' => $revenus->avg('montant'),
            'by_type' => $revenus->groupBy('typeRevenu.nom')
                                ->map(function ($group) {
                                    return [
                                        'total' => $group->sum('montant'),
                                        'count' => $group->count(),
                                    ];
                                }),
        ];

        // Préparation des données pour les filtres
        $months = Revenu::selectRaw('DISTINCT DATE_FORMAT(date_revenu, "%Y-%m") as month')
                        ->orderBy('month', 'desc')
                        ->pluck('month');

        return view('list', compact('revenus', 'stats', 'months'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'montant' => 'required|numeric|min:0',
            'date_revenu' => 'required|date',
            'type_revenu_id' => 'required|exists:type_revenus,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            // Création du revenu
            Revenu::create($validated);

            // Redirection avec message de succès
            return redirect()->route('accueil')
                           ->with('success', 'Le revenu a été enregistré avec succès');
        } catch (\Exception $e) {
            // En cas d'erreur, redirection avec message d'erreur
            return redirect()->route('accueil')
                           ->with('error', 'Une erreur est survenue lors de l\'enregistrement')
                           ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
