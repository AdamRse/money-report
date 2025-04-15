<?php

namespace App\Interfaces\Repositories;

use App\Interfaces\Traits\ErrorManagementInterface;
use App\Models\IncomeType;

interface IncomeTypeRepositoryInterface extends ErrorManagementInterface{
    public function createIfNotExists(array $incomeType):IncomeType|false;
    public function selectId(int|string $id):IncomeType|false;
}
