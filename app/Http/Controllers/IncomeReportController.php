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
    public function index(FilterIncomesRequest $request): View {
        switch($request->get("filter_type")){
            case "period":
                $dateRequestStart = Carbon::parse($request->get("start_date"));
                $dateRequestEnd = Carbon::parse($request->get("end_date"));
                $incomes = $this->incomeRepository->getUserIncomesByDateRange($dateRequestStart, $dateRequestEnd);
                $periodMessage = "Revenus du ".$dateRequestStart->format('d/m/Y')." au ".$dateRequestEnd->format('d/m/Y');
                break;
            case "month":
                $dateRequestStart = Carbon::createFromDate($request->get("year_number"), $request->get("month_number"), 1);
                $dateRequestEnd = Carbon::createFromDate($request->get("year_number"), $request->get("month_number"), 1)->endOfMonth();
                $incomes = $this->incomeRepository->getUserIncomesByDateRange($dateRequestStart, $dateRequestEnd);
                Carbon::setLocale('fr');
                $periodMessage = "Revenus pour ".$dateRequestStart->translatedFormat('F Y');
                break;
            default:
                $periodMessage = "Tous vos revenus de l'année en cours";
                $incomes = $this->incomeRepository->getUserIncomesByYear();
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
