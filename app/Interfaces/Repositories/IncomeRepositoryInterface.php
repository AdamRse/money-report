<?php

namespace App\Interfaces\Repositories;

use App\Interfaces\Traits\ErrorManagementInterface;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface IncomeRepositoryInterface extends ErrorManagementInterface{
    public function getUserIncomesByYear(null|string $year = null):Collection|false;
    public function getUserIncomesByDateRange(Carbon|string $dateStart, Carbon|string $dateEnd);
    public static function findDuplicates(Income $income):Collection;

    // /**
    //  * Trait ErrorManagementTrait
    //  * Intelephense a besoin de ces références pour ne pas indiquer d'erreur
    //  */
    // public function isError();
    // public function errorDisplayHTML();
    // public function errorGetArray();
}
