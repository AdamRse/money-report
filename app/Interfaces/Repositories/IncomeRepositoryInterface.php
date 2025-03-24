<?php

namespace App\Interfaces\Repositories;

use App\Models\Income;

interface IncomeRepositoryInterface {
    public static function findDuplicates(Income $income): array;
}
