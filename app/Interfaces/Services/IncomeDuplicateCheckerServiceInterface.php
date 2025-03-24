<?php

namespace App\Interfaces\Services;

use App\Models\Income;

interface IncomeDuplicateCheckerServiceInterface {

    public function getDuplicateLevel(Income $income): int;
}
