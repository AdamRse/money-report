<?php

namespace App\Interfaces\Repositories;

use App\Interfaces\Traits\ErrorManagementInterface;
use App\Models\IncomeType;

interface IncomeTypeRepositoryInterface extends ErrorManagementInterface{
    public function createIfNotExists(array $incomeType):IncomeType|false;
    public function update(array|IncomeType $incomeType):bool;
    public function selectId(int|string $id):IncomeType|false;
    public function delete(int|string|IncomeType $incomeType):bool;
    public function isDuplicate(array|IncomeType $incomeType):IncomeType|false;
}
