<?php

namespace App\Repositories;

use App\Interfaces\Repositories\IncomeRepositoryInterface;
use App\Interfaces\Repositories\IncomeTypeRepositoryInterface;
use App\Models\Income;
use App\Models\IncomeType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Traits\ErrorManagementTrait;
use Exception;

class IncomeTypeRepository implements IncomeTypeRepositoryInterface{

    use ErrorManagementTrait;

    public function createIfNotExists(array $incomeType):IncomeType|false{
        if (empty($incomeType['income_type_id'])) {
            try{
                return IncomeType::create([
                    'name' => $incomeType['new_type_name'],
                    'description' => $incomeType['new_type_description'] ?? null,
                    'taxable' => $incomeType['taxable'] ?? false,
                    'must_declare' => $incomeType['must_declare'] ?? false
                ]);
            }
            catch(Exception $e){
                $this->errorAdd("Impossible de créer le type de revenu : ".$e->getMessage());
                return false;
            }
        }
        else
            return $this->selectId(empty($incomeType['income_type_id'])) ?: false;
    }

    public function selectId(int|string $id):IncomeType|false{
        try{
            if($income = IncomeType::find($id))
                return $income;
            else{
                $this->errorAdd("Type de revenu introuvable avec l'identifiant $id");
                return false;
            }
        }
        catch(Exception $e){
            $this->errorAdd("La requête SQL Type de revenu n'a pu aboutir : ".$e->getMessage());
            return false;
        }
    }

    public function update(array|IncomeType $incomeType):bool{
        if(is_array($incomeType))
            $incomeType = IncomeType::make($incomeType);
        if(empty($incomeType->id)){
            $this->errorAdd("Aucun identifiant de type de revenu donné.");
            return false;
        }
        try{
            if(!$DBIncomeType = IncomeType::find($incomeType->id)){
                if($DBIncomeType->fill($incomeType->toArray())->isDirty())//Pas besoin de sauvegarder si aucun changements. isDirty se charche de faire la différence sans requête supplémentaire
                    return $DBIncomeType->saveOrFail();
                else
                    return true;
            }
            else{
                $this->errorAdd("L'identifiant du type de revenu donné ne correspond pas.");
                return false;
            }
        }
        catch(Exception $e){
            $this->errorAdd("La requête SQL modification type de revenu a échouée avec le message suivant : ".$e->getMessage());
            return false;
        }
    }
}
