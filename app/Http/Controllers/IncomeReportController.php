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
}
