<?php

namespace App\Repositories;

use App\Interfaces\Repositories\IncomeRepositoryInterface;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use App\Traits\ErrorManagementTrait;
use Exception;

class IncomeRepository implements IncomeRepositoryInterface {

    use ErrorManagementTrait;

    public function selectId($id):Income|false{
        try{
            return Income::find($id);
        }
        catch(Exception $e){
            $this->errorAdd("Le revenu n'a pas été trouvé : ".$e->getMessage());
            return false;
        }
    }

    public function createIfNotExists(array $income):Income|false{
        if (empty($income['id'])) {
            try{
                return Income::create([
                    'amount' => $income['amount'],
                    'income_date' => $income['income_date'],
                    'income_type_id' => $income['income_type_id'],
                    'notes' => $income['notes'] ?? null
                ]);
            }
            catch(Exception $e){
                $this->errorAdd("Impossible de créer le revenu : ".$e->getMessage());
                return false;
            }
        }
        else
            return $this->selectId(empty($income['id'])) ?: false;
    }

    public function getUserIncomesByYear(null|string $year = null):Collection|false{
        $year = $year ? trim($year) : date('Y');
        if(!preg_match("/^[0-9]{4}$/", $year)){
            $this->errorAdd("L'année passée est incorrecte (4 chiffres attendus), format donné : $year");
            return false;
        }
        $idUser = Auth::id();
        $query = Income::with('income_types')
            ->where('user_id', $idUser)
            ->whereYear('income_date', $year);

        try{
            return $query->orderBy('income_date', 'desc')->get();
        }
        catch(Exception $e){
            $this->errorAdd("La requête a échouée : ".$e->getMessage());
            return false;
        }
    }

    public function getUserIncomesByDateRange(Carbon|string $dateStart, Carbon|string $dateEnd){
    }

    /**
     * Vérifie si un revenu avec le même montant et la même date existe déjà
     *
     * @param Income $income Le revenu à vérifier
     * @return array Un tableau des revenus correspondants, vide si aucun doublon
     */
    public function findDuplicates(Income $income):Collection|false{
        try{
            return Income::where('amount', $income->amount)
            ->whereDate('income_date', $income->income_date)
            ->get();
        }
        catch(Exception $e){
            $this->errorAdd("La requête a échouée : ".$e->getMessage());
            return false;
        }
    }
}
