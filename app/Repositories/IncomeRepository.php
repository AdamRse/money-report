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
            if($income = Income::find($id))
                return $income;
            else{
                $this->errorAdd("Revenu introuvable avec l'identifiant $id");
                return false;
            }
        }
        catch(Exception $e){
            $this->errorAdd("La requête SQL Revenu n'a pu aboutir : ".$e->getMessage());
            return false;
        }
    }

    public function createIfNotExists(array $income):Income|false{
        if (empty($income['id'])) {
            try{
                return Income::create([
                    'amount' => $income['amount'],
                    'user_id' => Auth::id(),
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

    public function update(array|Income $income):bool{
        if(is_array($income))
            $income = Income::make($income);
        if(empty($income->id)){
            $this->errorAdd("Aucun identifiant de revenu donné.");
            return false;
        }
        try{
            if(!$DBIncome = Income::find($income->id)){
                if($DBIncome->fill($income->toArray())->isDirty())//Pas besoin de sauvegarder si aucun changements. isDirty se charche de faire la différence sans requête supplémentaire
                    return $DBIncome->saveOrFail();
                else
                    return true;
            }
            else{
                $this->errorAdd("L'identifiant du revenu donné ne correspond pas.");
                return false;
            }
        }
        catch(Exception $e){
            $this->errorAdd("La requête SQL modification revenu a échouée avec le message suivant : ".$e->getMessage());
            return false;
        }
    }

    public function delete(int|string|Income $income):bool{
        try{
            if($income instanceof Income == false){
                if(!$income = Income::find($income)){
                    $this->errorAdd("Impossible de supprimer le revenu, introuvable en base de données.");
                    return false;
                }
            }
            return $income->deleteOrFail();
        }
        catch(Exception $e){
            $this->errorAdd("Impossible de supprimer le revenu, la base de données renvoie une erreur : ".$e->getMessage());
            return false;
        }
    }

    public function getUserIncomesByYear(null|string $year = null):Collection|false{
        $year = $year ? trim($year) : date('Y');
        if(!preg_match("/^[0-9]{4}$/", $year)){
            $this->errorAdd("L'année passée est incorrecte (4 chiffres attendus), format donné : $year");
            return false;
        }
        $idUser = Auth::id();
        $query = Income::with('incomeType')
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
