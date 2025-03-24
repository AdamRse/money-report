<?php

namespace App\Interfaces\Services;

use App\Services\IncomeDuplicateCheckerService;

interface DocumentParserServiceInterface {

    public function __construct(IncomeDuplicateCheckerService $duplicateChecker);

    public function errorDisplayHTML(): string;

    public function parseDocument(string $file): array;
}
