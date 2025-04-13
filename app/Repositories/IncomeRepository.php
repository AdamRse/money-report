<?php

namespace App\Repositories;

use App\Interfaces\Repositories\IncomeRepositoryInterface;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Traits\ErrorManagementTrait;

class IncomeRepository implements IncomeRepositoryInterface {

    use ErrorManagementTrait;

    public function getUserIncomesByYear(null|string $year = null):Collection|false{
        $year = $year ? trim($year) : date('Y');
        if(preg_match("/^[0-9]{4}$/", $year)){
            $this->errorAdd("L'année passée est incorrecte (4 chiffres attendus), format donné : $year");
            return false;
        }
        $idUser = Auth::id();
        $query = Income::with('income_types')
            ->where('user_id', $idUser)
            ->whereYear('income_date', $year);

        return $query->orderBy('income_date', 'desc')->get();
    }
    public function getUserIncomesByDateRange(Carbon|string $dateStart, Carbon|string $dateEnd){
    }

    /**
     * Vérifie si un revenu avec le même montant et la même date existe déjà
     *
     * @param Income $income Le revenu à vérifier
     * @return array Un tableau des revenus correspondants, vide si aucun doublon
     */
    public static function findDuplicates(Income $income):Collection{
        return Income::where('amount', $income->amount)
            ->whereDate('income_date', $income->income_date)
            ->get();
    }
}
