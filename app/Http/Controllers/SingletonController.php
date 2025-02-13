<?php
// app/Http/Controllers/SingletonController.php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\IncomeType;
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
    private function detectIncomeType(string $libelle): ?int {
        $libelle = strtolower($libelle);
        foreach ($this->typePatterns as $pattern => $typeId) {
            if (str_contains($libelle, strtolower($pattern))) {
                return $typeId;
            }
        }
        return null;
    }

    /**
     * Traite et analyse un fichier CSV/TSV de relevé bancaire pour en extraire les revenus.
     * En GET : Affiche le formulaire d'upload
     * En POST : Analyse le fichier ou importe les revenus sélectionnés
     *
     * @param Request $request Requête HTTP contenant soit le fichier à analyser, soit les revenus à importer
     * @return \Illuminate\Http\Response Vue avec les résultats du parsing ou redirection avec message
     */
    public function parse(Request $request) {
        // Affichage du formulaire pour GET
        if ($request->isMethod('get')) {
            // Récupération de tous les types de revenus pour le select
            $incomeType = IncomeType::all();
            return view('parse', compact('incomeType'));
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
                $incomes = [];

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
                    $incomeTypeId = $this->detectIncomeType($libelle);

                    $incomes[] = [
                        'date' => $date,
                        'libelle' => $libelle,
                        'montant' => floatval($montant),
                        'selected' => $shouldBeSelected,
                        'type_revenu_id' => $incomeTypeId
                    ];
                }

                // Tri des revenus par date décroissante
                usort($incomes, function ($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });

                // Récupération de tous les types de revenus pour le select
                $incomeType = IncomeType::all();

                return view('parse', [
                    'revenus' => $incomes,
                    'incomeType' => $incomeType,
                    'parseResults' => true
                ]);
            }
            // Si des revenus sont soumis pour import
            elseif ($request->has('revenus')) {
                $selectedRevenus = collect($request->input('revenus'))
                    ->filter(function ($income) {
                        return isset($income['selected']);
                    })
                    ->map(function ($income) {
                        return [
                            'date' => $income['date'],
                            'libelle' => $income['libelle'],
                            'montant' => $income['montant'],
                            'type_revenu_id' => $income['type_revenu_id']
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

    /**
     * Gère les types de revenus (création et affichage).
     * En GET : Affiche le formulaire et la liste des types
     * En POST : Crée un nouveau type de revenu
     *
     * @param Request $request Requête HTTP avec les données du type de revenu
     * @return \Illuminate\Http\Response Vue ou redirection avec message
     */
    public function typeRevenu(Request $request) {
        // GET pour afficher le formulaire
        if ($request->isMethod('get')) {
            // Récupérer tous les types de revenus pour l'affichage dans le tableau
            $incomeType = IncomeType::all();
            return view('type-revenu', compact('incomeType'));
        }

        // Le reste de la méthode reste inchangé pour le POST
        try {
            $validated = $request->validate([
                'name' => 'required|string|min:2|max:63|unique:incomeType,name',
                'description' => 'nullable|string|max:255',
                'taxable' => 'sometimes|boolean',
                'must_declare' => 'sometimes|boolean',
            ], [
                'name.required' => 'Le nom du type de revenu est obligatoire',
                'name.min' => 'Le nom doit faire au moins 2 caractères',
                'name.max' => 'Le nom ne peut pas dépasser 63 caractères',
                'name.unique' => 'Ce type de revenu existe déjà',
                'description.max' => 'La description ne peut pas dépasser 255 caractères',
            ]);

            IncomeType::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'taxable' => isset($validated['taxable']) ? 1 : 0,
                'must_declare' => isset($validated['must_declare']) ? 1 : 0,
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
     * Met à jour un type de revenu existant.
     *
     * @param Request $request Requête HTTP contenant les données de mise à jour
     * @param string $id Identifiant du type de revenu à modifier
     * @return \Illuminate\Http\RedirectResponse Redirection avec message de succès ou d'erreur
     */
    public function updateIncomeType(Request $request, $id) {
        try {
            $incomeType = IncomeType::findOrFail($id);

            // Validation
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    'max:63',
                    Rule::unique('incomeType')->ignore($incomeType->id),
                ],
                'description' => 'nullable|string|max:255',
                'taxable' => 'sometimes|boolean',
                'must_declare' => 'sometimes|boolean',
            ], [
                'name.required' => 'Le nom du type de revenu est obligatoire',
                'name.min' => 'Le nom doit faire au moins 2 caractères',
                'name.max' => 'Le nom ne peut pas dépasser 63 caractères',
                'name.unique' => 'Ce type de revenu existe déjà',
                'description.max' => 'La description ne peut pas dépasser 255 caractères',
            ]);

            // Mise à jour
            $incomeType->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'taxable' => isset($validated['taxable']) ? 1 : 0,
                'must_declare' => isset($validated['must_declare']) ? 1 : 0,
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
     * Supprime un type de revenu s'il n'est pas utilisé par des revenus existants.
     *
     * @param string $id Identifiant du type de revenu à supprimer
     * @return \Illuminate\Http\RedirectResponse Redirection avec message de succès ou d'erreur
     */
    public function deleteIncomeType(string $id) {
        try {
            $incomeType = IncomeType::findOrFail($id);

            // Vérifier si le type est utilisé dans des revenus
            if ($incomeType->revenus()->exists()) {
                return redirect()
                    ->route('typeRevenu')
                    ->with('error', 'Impossible de supprimer ce type de revenu car il est utilisé par des revenus existants');
            }

            $incomeType->delete();

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
     * Enregistre plusieurs revenus en base de données à partir des données parsées.
     * Utilise une transaction pour garantir l'intégrité des données.
     *
     * @param Request $request Requête HTTP contenant les revenus à importer
     * @return \Illuminate\Http\RedirectResponse Redirection avec message de succès ou d'erreur
     */
    public function multipleRevenus(Request $request) {
        try {
            // Filtrer d'abord pour ne garder que les revenus sélectionnés
            $incomesToImport = collect($request->revenus)
                ->filter(function ($income) {
                    return isset($income['selected']);
                })
                ->values() // Réindexe le tableau pour la validation
                ->toArray();

            // Si aucun revenu sélectionné
            if (empty($incomesToImport)) {
                return redirect()
                    ->route('parse')
                    ->with('error', 'Aucun revenu sélectionné pour l\'import');
            }

            // Validation uniquement des revenus sélectionnés
            $validator = validator($incomesToImport, [
                '*.date' => 'required|string', // On validera le format plus tard
                '*.libelle' => 'required|string',
                '*.montant' => 'required|numeric|min:0',
                '*.type_revenu_id' => 'required|exists:incomeType,id'
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->route('parse')
                    ->with('error', 'Données invalides : ' . implode(', ', $validator->errors()->all()));
            }

            // Utilisation d'une transaction pour assurer l'intégrité des données
            DB::beginTransaction();
            try {
                foreach ($incomesToImport as $incomeData) {
                    // Conversion de la date du format FR au format SQL
                    $date = \DateTime::createFromFormat('d/m/Y', $incomeData['date']);
                    if (!$date) {
                        throw new \Exception('Format de date invalide : ' . $incomeData['date']);
                    }

                    // Création du revenu
                    Income::create([
                        'income_date' => $date->format('Y-m-d'),
                        'montant' => $incomeData['montant'],
                        'type_revenu_id' => $incomeData['type_revenu_id'],
                        'notes' => $incomeData['libelle'] // On utilise le libellé comme note
                    ]);
                }

                DB::commit();

                // Succès : redirection vers la liste des revenus
                return redirect()
                    ->route('revenus.list')
                    ->with('success', count($incomesToImport) . ' revenus ont été importés avec succès');
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

    /**
     * Affiche la page principale des revenus avec le formulaire d'ajout et la liste
     * des revenus de l'année sélectionnée.
     *
     * @param Request $request Requête HTTP contenant éventuellement l'année à filtrer
     * @return \Illuminate\Http\Response Vue avec les revenus et les types
     */
    public function index(Request $request) {
        // Récupération de tous les types de revenus pour le formulaire
        $incomeType = IncomeType::all();

        // Récupération de l'année sélectionnée (par défaut année courante)
        $selectedYear = $request->input('annee_filtre', date('Y'));

        // Récupération des revenus de l'année sélectionnée
        $incomes = Income::with('typeRevenu')
            ->whereYear('income_date', $selectedYear)
            ->orderBy('income_date', 'desc')
            ->get();

        return view('revenu', compact('incomeType', 'revenus', 'selectedYear'));
    }

    /**
     * Déconnecte l'utilisateur et invalide sa session.
     *
     * @return \Illuminate\Http\RedirectResponse Redirection vers la page d'accueil
     */
    public function logout() {
        Auth::logout();
        // Invalider la session pour plus de sécurité
        request()->session()->invalidate();

        // Régénérer le token CSRF
        request()->session()->regenerateToken();

        // Rediriger vers la page d'accueil
        return redirect('/');
    }

    /**
     * Gère l'authentification des utilisateurs.
     * En GET : Affiche le formulaire de connexion
     * En POST : Traite la tentative de connexion
     *
     * @param Request $request Requête HTTP contenant les identifiants
     * @return \Illuminate\Http\Response Vue ou redirection
     */
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

    /**
     * Gère l'inscription des nouveaux utilisateurs.
     * En GET : Affiche le formulaire d'inscription
     * En POST : Crée le nouvel utilisateur
     *
     * @param Request $request Requête HTTP contenant les données d'inscription
     * @return \Illuminate\Http\Response Vue ou redirection
     */
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

    /**
     * Affiche la liste des revenus avec possibilité de filtrage par période
     * et calcule les statistiques associées.
     *
     * @param Request $request Requête HTTP contenant les filtres éventuels
     * @return \Illuminate\Http\Response Vue avec les revenus filtrés et les stats
     */
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
            $query = Income::with('typeRevenu')
                ->orderBy('income_date', 'desc');

            // Application des filtres
            if ($request->filled('filter_type')) {
                if ($request->filter_type === 'period') {
                    $query->whereBetween('income_date', [
                        $request->start_date,
                        $request->end_date
                    ]);
                } elseif ($request->filter_type === 'month') {
                    $query->whereYear('income_date', $request->year_number)
                        ->whereMonth('income_date', $request->month_number);
                }
            }

            // Récupération des revenus
            $incomes = $query->get();

            // Préparation du message pour période vide
            $periodMessage = '';
            if ($incomes->isEmpty() && $request->filled('filter_type')) {
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
                'total' => $incomes->sum('montant'),
                'count' => $incomes->count(),
                'average' => $incomes->avg('montant'),
                'total_taxable' => $incomes->filter(function ($income) {
                    return $income->typeRevenu->taxable;
                })->sum('montant'),
                'total_must_declare' => $incomes->filter(function ($income) {
                    return $income->typeRevenu->must_declare;
                })->sum('montant'),
                'by_type' => $incomes->groupBy('typeRevenu.name')
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
     * Enregistre un nouveau revenu en base de données.
     * Gère également la création d'un nouveau type de revenu si nécessaire.
     *
     * @param Request $request Requête HTTP contenant les données du revenu
     * @return \Illuminate\Http\RedirectResponse Redirection avec message de succès ou d'erreur
     */
    public function store(Request $request) {
        try {
            $validated = $request->validate([
                'montant' => 'required|numeric|min:0',
                'income_date' => 'required|date',
                'type_revenu_id' => [
                    'required',
                    'integer',
                    Rule::when($request->type_revenu_id != 0, ['exists:incomeType,id'])
                ],
                'notes' => 'nullable|string|max:1000',
                'nvRevenu' => 'nullable|required_if:type_revenu_id,0|string|between:2,63',
                'nvRevenuDesc' => 'nullable|string|max:255',
                'taxable' => 'nullable|boolean',
                'must_declare' => 'nullable|boolean'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Une erreur est survenue: ' . $e->getMessage())
                ->withInput();
        }

        try {
            $createRevenu = [
                'montant' => $validated['montant'],
                'income_date' => $validated['income_date'],
                'type_revenu_id' => $validated['type_revenu_id'],
                'notes' => $validated['notes'] ?? null
            ];

            if ($validated['type_revenu_id'] == 0) {
                $newIncomeType = IncomeType::create([
                    'name' => $validated['nvRevenu'],
                    'description' => $validated['nvRevenuDesc'] ?? null,
                    'taxable' => isset($validated['taxable']) ? 1 : 0,
                    'must_declare' => isset($validated['must_declare']) ? 1 : 0
                ]);
                $createRevenu['type_revenu_id'] = $newIncomeType->id;
            }

            // Création du revenu
            Income::create($createRevenu);

            return redirect()->route('revenu')
                ->with('success', 'Le revenu a été enregistré avec succès');
        } catch (\Exception $e) {
            return redirect()->route('revenu')
                ->with('error', 'Une erreur est survenue lors de l\'enregistrement : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Met à jour un revenu existant avec les nouvelles données.
     *
     * @param Request $request Requête HTTP contenant les données de mise à jour
     * @param string $id Identifiant du revenu à modifier
     * @return \Illuminate\Http\RedirectResponse Redirection avec message de succès ou d'erreur
     */
    public function update(Request $request, string $id) {
        try {
            // Validation des données
            $validated = $request->validate([
                'montant' => 'required|numeric|min:0',
                'income_date' => 'required|date',
                'type_revenu_id' => 'required|exists:incomeType,id',
                'notes' => 'nullable|string|max:1000'
            ]);

            // Récupération du revenu
            $income = Income::findOrFail($id);

            // Mise à jour
            $income->update($validated);

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
     * Supprime un revenu existant de la base de données.
     *
     * @param string $id Identifiant du revenu à supprimer
     * @return \Illuminate\Http\RedirectResponse Redirection avec message de succès ou d'erreur
     */
    public function destroy(string $id) {
        try {
            $income = Income::findOrFail($id);
            $income->delete();

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
