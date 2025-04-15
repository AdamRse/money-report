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
            return IncomeType::find($id);
        }
        catch(Exception $e){
            $this->errorAdd("Le type de revenu donné n'a pas été trouvé : ".$e->getMessage());
            return false;
        }
    }
}
