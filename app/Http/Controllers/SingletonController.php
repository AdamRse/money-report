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
     * Patterns à détecter dans les libellés pour exclusion lors du parsing (les résultats restent affichés mais décochés, il est possible de les rajoutter quand même)
     * Les patterns sont insensibles à la casse
     */
    private array $excludePatterns = [
        'adam rousselle',
        'rousselle adam'
    ];
    /**
     * Patterns pour la détection des types de revenus
     * Key = pattern à détecter (insensible à la casse)
     * Value = ID du type de revenu dans la base de données
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
     * Vérifie si un libellé contient un des patterns d'exclusion
     */
    private function shouldExclude(string $libelle): bool {
        $libelle = strtolower($libelle);
        foreach ($this->excludePatterns as $pattern) {
            if (str_contains($libelle, strtolower($pattern))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Détermine le type de revenu en fonction du libellé
     * Retourne null si aucun pattern ne correspond
     */
    private function detectTypeRevenu(string $libelle): ?int {
        $libelle = strtolower($libelle);
        foreach ($this->typePatterns as $pattern => $typeId) {
            if (str_contains($libelle, strtolower($pattern))) {
                return $typeId;
            }
        }
        return null;
    }

    public function parse(Request $request) {
        // Affichage du formulaire pour GET
        if ($request->isMethod('get')) {
            // Récupération de tous les types de revenus pour le select
            $typeRevenus = TypeRevenu::all();
            return view('parse', compact('typeRevenus'));
        }

        // Traitement du POST
        try {
            // Si un fichier est uploadé
            if ($request->hasFile('bankFile')) {
                // Validation du fichier
                $request->validate([
                    'bankFile' => 'required|file|mimes:csv,tsv,txt|max:2048'
                ]);

                $file = $request->file('bankFile');
                $content = file_get_contents($file->path());

                // Détection du délimiteur (CSV ou TSV)
                $delimiter = str_contains($file->getClientOriginalName(), '.tsv') ? "\t" : ";";

                // Conversion en tableau de lignes
                $lines = explode("\n", $content);

                // Recherche de la ligne d'en-tête
                $headerIndex = -1;
                foreach ($lines as $index => $line) {
                    if (str_contains($line, 'Date') && str_contains($line, 'Montant')) {
                        $headerIndex = $index;
                        break;
                    }
                }

                if ($headerIndex === -1) {
                    throw new \Exception("Format de fichier invalide : impossible de trouver l'en-tête des colonnes");
                }

                // Initialisation du tableau des revenus
                $revenus = [];

                // Traitement des lignes après l'en-tête
                for ($i = $headerIndex + 1; $i < count($lines); $i++) {
                    $line = trim($lines[$i]);
                    if (empty($line)) continue;

                    // Découpage de la ligne
                    $columns = str_getcsv($line, $delimiter);
                    if (count($columns) < 3) continue;

                    // Extraction des données
                    $date = trim($columns[0]);
                    $libelle = trim($columns[1], " \t\n\r\0\x0B\""); // Nettoyage des guillemets et espaces
                    $montant = str_replace(',', '.', trim($columns[2])); // Conversion virgule en point

                    // Ne garder que les montants positifs
                    if (floatval($montant) <= 0) continue;

                    // Détermine si la ligne doit être exclue en fonction des patterns
                    $shouldBeSelected = !$this->shouldExclude($libelle);

                    // Détection du type de revenu
                    $typeRevenuId = $this->detectTypeRevenu($libelle);

                    $revenus[] = [
                        'date' => $date,
                        'libelle' => $libelle,
                        'montant' => floatval($montant),
                        'selected' => $shouldBeSelected,
                        'type_revenu_id' => $typeRevenuId
                    ];
                }

                // Tri des revenus par date décroissante
                usort($revenus, function ($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });

                // Récupération de tous les types de revenus pour le select
                $typeRevenus = TypeRevenu::all();

                return view('parse', [
                    'revenus' => $revenus,
                    'typeRevenus' => $typeRevenus,
                    'parseResults' => true
                ]);
            }
            // Si des revenus sont soumis pour import
            elseif ($request->has('revenus')) {
                $selectedRevenus = collect($request->input('revenus'))
                    ->filter(function ($revenu) {
                        return isset($revenu['selected']);
                    })
                    ->map(function ($revenu) {
                        return [
                            'date' => $revenu['date'],
                            'libelle' => $revenu['libelle'],
                            'montant' => $revenu['montant'],
                            'type_revenu_id' => $revenu['type_revenu_id']
                        ];
                    });

                return redirect()
                    ->route('parse')
                    ->with('success', count($selectedRevenus) . ' revenus sélectionnés pour import');
            }

            throw new \Exception("Aucune action valide détectée");
        } catch (\Exception $e) {
            return redirect()
                ->route('parse')
                ->with('error', 'Erreur lors du parsing : ' . $e->getMessage());
        }
    }

    public function typeRevenu(Request $request) {
        // GET pour afficher le formulaire
        if ($request->isMethod('get')) {
            // Récupérer tous les types de revenus pour l'affichage dans le tableau
            $typeRevenus = TypeRevenu::all();
            return view('type-revenu', compact('typeRevenus'));
        }

        // Le reste de la méthode reste inchangé pour le POST
        try {
            $validated = $request->validate([
                'nom' => 'required|string|min:2|max:63|unique:type_revenus,nom',
                'description' => 'nullable|string|max:255',
                'imposable' => 'sometimes|boolean',
                'declarable' => 'sometimes|boolean',
            ], [
                'nom.required' => 'Le nom du type de revenu est obligatoire',
                'nom.min' => 'Le nom doit faire au moins 2 caractères',
                'nom.max' => 'Le nom ne peut pas dépasser 63 caractères',
                'nom.unique' => 'Ce type de revenu existe déjà',
                'description.max' => 'La description ne peut pas dépasser 255 caractères',
            ]);

            TypeRevenu::create([
                'nom' => $validated['nom'],
                'description' => $validated['description'] ?? null,
                'imposable' => isset($validated['imposable']) ? 1 : 0,
                'declarable' => isset($validated['declarable']) ? 1 : 0,
            ]);

            return redirect()
                ->route('typeRevenu')
                ->with('success', 'Type de revenu créé avec succès');
        } catch (\Exception $e) {
            return redirect()
                ->route('typeRevenu')
                ->with('error', 'Une erreur est survenue lors de la création du type de revenu : ' . $e->getMessage())
                ->withInput();
        }
    }


    /**
     * Met à jour un type de revenu
     */
    public function updateTypeRevenu(Request $request, $id) {
        try {
            $typeRevenu = TypeRevenu::findOrFail($id);

            // Validation
            $validated = $request->validate([
                'nom' => [
                    'required',
                    'string',
                    'min:2',
                    'max:63',
                    Rule::unique('type_revenus')->ignore($typeRevenu->id),
                ],
                'description' => 'nullable|string|max:255',
                'imposable' => 'sometimes|boolean',
                'declarable' => 'sometimes|boolean',
            ], [
                'nom.required' => 'Le nom du type de revenu est obligatoire',
                'nom.min' => 'Le nom doit faire au moins 2 caractères',
                'nom.max' => 'Le nom ne peut pas dépasser 63 caractères',
                'nom.unique' => 'Ce type de revenu existe déjà',
                'description.max' => 'La description ne peut pas dépasser 255 caractères',
            ]);

            // Mise à jour
            $typeRevenu->update([
                'nom' => $validated['nom'],
                'description' => $validated['description'] ?? null,
                'imposable' => isset($validated['imposable']) ? 1 : 0,
                'declarable' => isset($validated['declarable']) ? 1 : 0,
            ]);

            return redirect()
                ->route('typeRevenu')
                ->with('success', 'Type de revenu modifié avec succès');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()
                ->route('typeRevenu')
                ->with('error', 'Type de revenu non trouvé');
        } catch (\Exception $e) {
            return redirect()
                ->route('typeRevenu')
                ->with('error', 'Une erreur est survenue lors de la modification : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime un type de revenu.
     */
    public function deleteTypeRevenu(string $id) {
        try {
            $typeRevenu = TypeRevenu::findOrFail($id);

            // Vérifier si le type est utilisé dans des revenus
            if ($typeRevenu->revenus()->exists()) {
                return redirect()
                    ->route('typeRevenu')
                    ->with('error', 'Impossible de supprimer ce type de revenu car il est utilisé par des revenus existants');
            }

            $typeRevenu->delete();

            return redirect()
                ->route('typeRevenu')
                ->with('success', 'Type de revenu supprimé avec succès');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()
                ->route('typeRevenu')
                ->with('error', 'Type de revenu non trouvé');
        } catch (\Exception $e) {
            return redirect()
                ->route('typeRevenu')
                ->with('error', 'Une erreur est survenue lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Enregistre plusieurs revenus en base de données
     */
    public function multipleRevenus(Request $request) {
        try {
            // Filtrer d'abord pour ne garder que les revenus sélectionnés
            $revenusToImport = collect($request->revenus)
                ->filter(function ($revenu) {
                    return isset($revenu['selected']);
                })
                ->values() // Réindexe le tableau pour la validation
                ->toArray();

            // Si aucun revenu sélectionné
            if (empty($revenusToImport)) {
                return redirect()
                    ->route('parse')
                    ->with('error', 'Aucun revenu sélectionné pour l\'import');
            }

            // Validation uniquement des revenus sélectionnés
            $validator = validator($revenusToImport, [
                '*.date' => 'required|string', // On validera le format plus tard
                '*.libelle' => 'required|string',
                '*.montant' => 'required|numeric|min:0',
                '*.type_revenu_id' => 'required|exists:type_revenus,id'
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->route('parse')
                    ->with('error', 'Données invalides : ' . implode(', ', $validator->errors()->all()));
            }

            // Utilisation d'une transaction pour assurer l'intégrité des données
            DB::beginTransaction();
            try {
                foreach ($revenusToImport as $revenuData) {
                    // Conversion de la date du format FR au format SQL
                    $date = \DateTime::createFromFormat('d/m/Y', $revenuData['date']);
                    if (!$date) {
                        throw new \Exception('Format de date invalide : ' . $revenuData['date']);
                    }

                    // Création du revenu
                    Revenu::create([
                        'date_revenu' => $date->format('Y-m-d'),
                        'montant' => $revenuData['montant'],
                        'type_revenu_id' => $revenuData['type_revenu_id'],
                        'notes' => $revenuData['libelle'] // On utilise le libellé comme note
                    ]);
                }

                DB::commit();

                // Succès : redirection vers la liste des revenus
                return redirect()
                    ->route('revenus.list')
                    ->with('success', count($revenusToImport) . ' revenus ont été importés avec succès');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return redirect()
                ->route('parse')
                ->with('error', 'Erreur lors de l\'import des revenus : ' . $e->getMessage());
        }
    }

    public function index(Request $request) {
        // Récupération de tous les types de revenus pour le formulaire
        $typeRevenus = TypeRevenu::all();

        // Récupération de l'année sélectionnée (par défaut année courante)
        $selectedYear = $request->input('annee_filtre', date('Y'));

        // Récupération des revenus de l'année sélectionnée
        $revenus = Revenu::with('typeRevenu')
            ->whereYear('date_revenu', $selectedYear)
            ->orderBy('date_revenu', 'desc')
            ->get();

        return view('revenu', compact('typeRevenus', 'revenus'));
    }

    public function logout() {
        Auth::logout();
        // Invalider la session pour plus de sécurité
        request()->session()->invalidate();

        // Régénérer le token CSRF
        request()->session()->regenerateToken();

        // Rediriger vers la page d'accueil
        return redirect('/');
    }
    public function login(Request $request) {
        //POST pour l'envoi des données
        if ($request->isMethod('post')) {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect()->intended('revenu');
            }
            return back()->withErrors([
                'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
            ])->onlyInput('email');
        }

        return view('login');
    }
    public function register(Request $request) {
        //POST pour l'envoi des données
        if ($request->isMethod('post')) {
            // Validation des champs
            $request->validate([
                'user' => 'required|string|max:31',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:4|confirmed',
            ]);

            // Création de l'utilisateur
            $user = User::create([
                'name' => $request->user,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Connexion automatique
            Auth::login($user);

            return redirect()->route('revenu')->with('success', 'Inscription réussie');
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

            return redirect()->route('revenu')
                ->with('success', 'Le revenu a été enregistré avec succès');
        } catch (\Exception $e) {
            return redirect()->route('revenu')
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
     * Met à jour un revenu existant
     */
    public function update(Request $request, string $id) {
        try {
            // Validation des données
            $validated = $request->validate([
                'montant' => 'required|numeric|min:0',
                'date_revenu' => 'required|date',
                'type_revenu_id' => 'required|exists:type_revenus,id',
                'notes' => 'nullable|string|max:1000'
            ]);

            // Récupération du revenu
            $revenu = Revenu::findOrFail($id);

            // Mise à jour
            $revenu->update($validated);

            return redirect()
                ->route('revenu')
                ->with('success', 'Le revenu a été modifié avec succès');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()
                ->route('revenu')
                ->with('error', 'Revenu non trouvé');
        } catch (\Exception $e) {
            return redirect()
                ->route('revenu')
                ->with('error', 'Une erreur est survenue lors de la modification : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprime un revenu
     */
    public function destroy(string $id) {
        try {
            $revenu = Revenu::findOrFail($id);
            $revenu->delete();

            return redirect()
                ->route('revenu')
                ->with('success', 'Le revenu a été supprimé avec succès');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()
                ->route('revenu')
                ->with('error', 'Revenu non trouvé');
        } catch (\Exception $e) {
            return redirect()
                ->route('revenu')
                ->with('error', 'Une erreur est survenue lors de la suppression : ' . $e->getMessage());
        }
    }
}
