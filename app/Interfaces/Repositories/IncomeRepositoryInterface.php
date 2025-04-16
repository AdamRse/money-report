<?php

namespace App\Interfaces\Repositories;

use App\Interfaces\Traits\ErrorManagementInterface;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface IncomeRepositoryInterface extends ErrorManagementInterface{
    public function selectId($id):Income|false;
    public function selectAllFromAuth():Collection|false;
    public function createIfNotExists(array $income):Income|false;
    public function update(array|Income $income):bool;
    public function delete(int|string|Income $income):bool;
    public function getUserIncomesByYear(null|string $year = null):Collection|false;
    public function getUserIncomesByDateRange(Carbon|string $dateStart, Carbon|string $dateEnd);
    public function findDuplicates(Income $income):Collection|false;
    public function isDuplicate(array|Income $income):Income|false;
}
