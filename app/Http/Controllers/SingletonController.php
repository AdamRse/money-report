<?php

namespace App\Http\Controllers;

use App\Models\Revenu;
use App\Models\TypeRevenu;
use Illuminate\Http\Request;

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
