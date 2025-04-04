<?php

namespace App\Interfaces\Repositories;

use App\Models\Income;
use Illuminate\Database\Eloquent\Collection;

interface IncomeRepositoryInterface {
    public static function findDuplicates(Income $income):Collection;
}
