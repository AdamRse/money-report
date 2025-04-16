<?php

namespace App\Repositories;

use App\Interfaces\Repositories\IncomeTypeRepositoryInterface;
use App\Models\IncomeType;
use App\Traits\ErrorManagementTrait;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class IncomeTypeRepository implements IncomeTypeRepositoryInterface{

    use ErrorManagementTrait;

    /**
     * Ne génère pas d'erreur quand un doublon est détecté, rend le doublon à la place.
     */
    public function createIfNotExists(array $incomeType):IncomeType|false{
        if($duplicate = $this->isDuplicate($incomeType))
            return $duplicate;

        try{
            return IncomeType::create($incomeType);
        }
        catch(Exception $e){
            $this->errorAdd("Impossible de créer le type de revenu : ".$e->getMessage());
            return false;
        }
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

    public function selectAll():Collection|false{
        try{
            return IncomeType::all();
        }
        catch(Exception $e){
            $this->errorAdd("Impossible de récupérer toute la liste des revenus : ".$e->getMessage());
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

    public function delete(int|string|IncomeType $incomeType):bool{
        try{
            if($incomeType instanceof IncomeType == false){
                if(!$incomeType = IncomeType::find($incomeType)){
                    $this->errorAdd("Impossible de supprimer le type de revenu, introuvable en base de données.");
                    return false;
                }
            }
            return $incomeType->deleteOrFail();
        }
        catch(Exception $e){
            $this->errorAdd("Impossible de supprimer le type de revenu, la base de données renvoie une erreur : ".$e->getMessage());
            return false;
        }
    }

    public function isDuplicate(array|IncomeType $incomeType):IncomeType|false{
        if(!is_array($incomeType))
            $incomeType = $incomeType->toArray();

        $incomeTypeFiltered = Arr::except($incomeType, ['id', 'created_at', 'updated_at']);

        return IncomeType::where($incomeTypeFiltered)->first() ?: false;
    }
}
