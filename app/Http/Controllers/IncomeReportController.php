<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeReport\FilterIncomesRequest;
use App\Repositories\IncomeRepository;
use App\Interfaces\Services\IncomeStatisticsServiceInterface;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\View\View;

class IncomeReportController extends Controller {

    private IncomeStatisticsServiceInterface $statisticsService;
    private IncomeRepository $incomeRepository;

    public function __construct(private IncomeStatisticsServiceInterface $statistics, IncomeRepository $incomeRepository) {
        $this->statisticsService = $statistics;
        $this->incomeRepository = $incomeRepository;
    }

    /**
     * Affiche la liste des revenus avec les statistiques et les filtres
     */
    //Old, affiche tous les incomes lol
    // public function index(FilterIncomesRequest $request): View {
    //     // Construction de la requête de base
    //     $query = Income::with('incomeType')->orderBy('income_date', 'desc');
    //     dd($query);
    //     // Application des filtres
    //     $periodMessage = $this->applyFilters($query, $request);

    //     // Récupération des revenus
    //     $incomes = $query->get();

    //     // Calcul des statistiques
    //     $statistics = $this->statisticsService->calculateStatistics($incomes);

    //     return view('income-report.index', [
    //         'incomes' => $incomes,
    //         'statistics' => $statistics,
    //         'periodMessage' => $periodMessage
    //     ]);
    // }
    public function index(FilterIncomesRequest $request): View {
        // Construction de la requête de base
        //$query = Income::with('incomeType')->orderBy('income_date', 'desc');
        //dd($request);
        

        switch($request->get("filter_type")){
            case "period":
                break;
            case "month":
                $incomes = $this->incomeRepository->getUserIncomesByDateRange($request->get("month_select"), $request->get("month_select"));
                $periodMessage = "Revenus du mois de ".$request->get("month_select")." ".$request->get("year_select");
                break;
            default:
                $incomes = $this->incomeRepository->getUserIncomesByYear();
                $periodMessage = "Tous vos revenus de l'année en cours";
                break;
        }

        // Calcul des statistiques
        $statistics = $this->statisticsService->calculateStatistics($incomes);

        return view('income-report.index', [
            'incomes' => $incomes,
            'statistics' => $statistics,
            'periodMessage' => $periodMessage
        ]);
    }

    /**
     * Applique les filtres à la requête et retourne le message de période
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param FilterIncomesRequest $request
     */
    private function applyFilters($query, FilterIncomesRequest $request): string {
        if (!$request->filled('filter_type')) {
            return '';
        }

        if ($request->filter_type === 'period') {
            $query->whereBetween('income_date', [
                $request->start_date,
                $request->end_date
            ]);

            if ($query->doesntExist()) {
                $start = Carbon::parse($request->start_date)->format('d/m/Y');
                $end = Carbon::parse($request->end_date)->format('d/m/Y');
                return "Aucun revenu trouvé entre le $start et le $end";
            }
        } elseif ($request->filter_type === 'month') {
            $query->whereYear('income_date', $request->year_number)
                ->whereMonth('income_date', $request->month_number);

            if ($query->doesntExist()) {
                $date = Carbon::create()
                    ->setYear((int)$request->year_number)
                    ->setMonth((int)$request->month_number)
                    ->locale('fr');
                return "Aucun revenu trouvé pour " . $date->isoFormat('MMMM YYYY');
            }
        }

        return '';
    }
}
