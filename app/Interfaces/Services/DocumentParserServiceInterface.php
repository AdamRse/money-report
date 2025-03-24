<?php

namespace App\Interfaces\Services;

interface DocumentParserServiceInterface {

    public function __construct(IncomeDuplicateCheckerServiceInterface $duplicateChecker);

    public function errorDisplayHTML(): string;

    public function parseDocument(string $file): array;
}
