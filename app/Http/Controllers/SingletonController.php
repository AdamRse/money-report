<?php

namespace App\Http\Controllers;

use App\Models\Revenu;
use App\Models\TypeRevenu;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SingletonController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $typeRevenus = TypeRevenu::all();
        return view('accueil', compact('typeRevenus'));
    }

    public function login(Request $request) {
        if ($request->has('email') && $request->has('password')) {
            dd($request);
            return view('accueil');
        }
        return view('login');
    }
    public function register(Request $request) {
        if ($request->isMethod('post')) {
            // Validation des champs
            $request->validate([
                'user' => 'required|string|max:31',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);

            // Création de l'utilisateur
            $user = User::create([
                'name' => $request->user,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Connexion automatique
            Auth::login($user);

            return redirect()->route('accueil')->with('success', 'Inscription réussie');
        }

        return view('register');
    }

    public function list(Request $request) {
        try {
            // Règles de base
            $rules = [
                'filter_type' => 'nullable|in:period,month',
            ];

            // Ajout des règles en fonction du type de filtre
            if ($request->filter_type === 'period') {
                $rules['start_date'] = [
                    'required',
                    'date',
                    'before_or_equal:today',
                ];
                $rules['end_date'] = [
                    'required',
                    'date',
                    'before_or_equal:today',
                ];
            } elseif ($request->filter_type === 'month') {
                $rules['month_number'] = 'required|numeric|between:1,12';
                $rules['year_number'] = [
                    'required',
                    'numeric',
                    'min:1900',
                    'max:' . date('Y')
                ];
            }

            $messages = [
                'start_date.required' => 'La date de début est requise',
                'end_date.required' => 'La date de fin est requise',
                'start_date.before_or_equal' => 'La date de début ne peut pas être dans le futur',
                'end_date.before_or_equal' => 'La date de fin ne peut pas être dans le futur',
                'month_number.required' => 'Le mois est requis',
                'month_number.between' => 'Le mois doit être compris entre 1 et 12',
                'year_number.required' => 'L\'année est requise',
                'year_number.max' => 'L\'année ne peut pas être dans le futur'
            ];

            $validated = $request->validate($rules, $messages);

            // Validation supplémentaire pour la cohérence des dates de période
            if ($request->filter_type === 'period' && $request->filled(['start_date', 'end_date'])) {
                if ($request->start_date > $request->end_date) {
                    throw new \Exception('La date de début doit être antérieure à la date de fin');
                }
            }

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

            // Récupération des revenus
            $revenus = $query->get();

            // Préparation du message pour période vide
            $periodMessage = '';
            if ($revenus->isEmpty() && $request->filled('filter_type')) {
                if ($request->filter_type === 'period') {
                    $debut = Carbon::parse($request->start_date)->format('d/m/Y');
                    $fin = Carbon::parse($request->end_date)->format('d/m/Y');
                    $periodMessage = "Aucun revenu trouvé entre le $debut et le $fin";
                } elseif ($request->filter_type === 'month') {
                    $date = Carbon::create()
                        ->setYear((int)$request->year_number)
                        ->setMonth((int)$request->month_number)
                        ->locale('fr');
                    $periodMessage = "Aucun revenu trouvé pour " . $date->isoFormat('MMMM YYYY');
                }
            }

            // Calcul des statistiques
            $stats = [
                'total' => $revenus->sum('montant'),
                'count' => $revenus->count(),
                'average' => $revenus->avg('montant'),
                'total_imposable' => $revenus->filter(function ($revenu) {
                    return $revenu->typeRevenu->imposable;
                })->sum('montant'),
                'total_declarable' => $revenus->filter(function ($revenu) {
                    return $revenu->typeRevenu->declarable;
                })->sum('montant'),
                'by_type' => $revenus->groupBy('typeRevenu.nom')
                    ->map(function ($group) {
                        return [
                            'total' => $group->sum('montant'),
                            'count' => $group->count(),
                        ];
                    }),
            ];

            return view('list', compact('revenus', 'stats', 'periodMessage'));
        } catch (\Exception $e) {
            return redirect()->route('revenus.list')
                ->withErrors(['filter_error' => $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        try {
            $validated = $request->validate([
                'montant' => 'required|numeric|min:0',
                'date_revenu' => 'required|date',
                'type_revenu_id' => [
                    'required',
                    'integer',
                    Rule::when($request->type_revenu_id != 0, ['exists:type_revenus,id'])
                ],
                'notes' => 'nullable|string|max:1000',
                'nvRevenu' => 'nullable|required_if:type_revenu_id,0|string|between:2,63',
                'nvRevenuDesc' => 'nullable|string|max:255',
                'imposable' => 'nullable|boolean',
                'declarable' => 'nullable|boolean'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage())
                ->withInput();
        }

        try {
            $createRevenu = [
                'montant' => $validated['montant'],
                'date_revenu' => $validated['date_revenu'],
                'type_revenu_id' => $validated['type_revenu_id'],
                'notes' => $validated['notes'] ?? null
            ];

            if ($validated['type_revenu_id'] == 0) {
                $newTypeRevenu = TypeRevenu::create([
                    'nom' => $validated['nvRevenu'],
                    'description' => $validated['nvRevenuDesc'] ?? null,
                    'imposable' => isset($validated['imposable']) ? 1 : 0,
                    'declarable' => isset($validated['declarable']) ? 1 : 0
                ]);
                $createRevenu['type_revenu_id'] = $newTypeRevenu->id;
            }

            // Création du revenu
            Revenu::create($createRevenu);

            return redirect()->route('accueil')
                ->with('success', 'Le revenu a été enregistré avec succès');
        } catch (\Exception $e) {
            return redirect()->route('accueil')
                ->with('error', 'Une erreur est survenue lors de l\'enregistrement : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        //
    }
}
