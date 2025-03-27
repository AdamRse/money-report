<?php

namespace App\Abstract;

use App\Interfaces\Services\BankParserInterface;
use App\Traits\ErrorManagementTrait;
use App\Interfaces\Services\IncomeDuplicateCheckerServiceInterface;
use App\Interfaces\Services\DateParserServiceInterface;
use App\Models\Income;
use Carbon\Carbon;


abstract class BankParserAbstract implements BankParserInterface{

    use ErrorManagementTrait;

    /**
     * Patterns à détecter dans les libellés pour exclusion (les résultats restent affichés mais décochés)
     * @var array<string>
     */
    protected array $excludePatterns = [
        'adam rousselle',
        'rousselle adam'
    ];

    /**
     * Patterns pour la détection des types de revenus
     * Key = pattern à détecter (insensible à la casse)
     * Value = ID du type de revenu dans la base de données
     * @var array<string, int>
     */
    protected array $typePatterns = [
        'FRANCE TRAVAIL' => 3,
        'POLE EMPLOI' => 3,
        'CAF' => 2,
        'ALLOCATIONS FAMILIALES' => 2,
        'REMBOURSEMENT' => 6,
        'SALAIRE' => 4,
        'CHOMAGE' => 5
    ];

    protected IncomeDuplicateCheckerServiceInterface $duplicateChecker;
    protected DateParserServiceInterface $dateParser;

    public function __construct(IncomeDuplicateCheckerServiceInterface $duplicateChecker, DateParserServiceInterface $dateParser) {
        $this->duplicateChecker = $duplicateChecker;
        $this->dateParser = $dateParser;
    }

    /**
     * Vérifie si un libellé doit être exclu
     */
    protected function shouldExclude(string $description): bool {
        $description = strtolower($description);
        return collect($this->excludePatterns)
            ->contains(fn($pattern) => str_contains($description, strtolower($pattern)));
    }

    /**
     * Détermine le type de revenu en fonction du libellé
     */
    protected function detectIncome_types(string $description): ?int {
        $description = strtolower($description);
        foreach ($this->typePatterns as $pattern => $typeId) {
            if (str_contains($description, strtolower($pattern))) {
                return $typeId;
            }
        }
        return null;
    }

}
