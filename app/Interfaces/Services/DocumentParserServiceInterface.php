<?php

namespace App\Interfaces\Services;

use App\Interfaces\Factories\BankParserFactoryInterface;

interface DocumentParserServiceInterface {

    public function __construct(BankParserFactoryInterface $bankParserFactory, IncomeDuplicateCheckerServiceInterface $duplicateChecker, DateParserServiceInterface $dateParser);

    public function errorDisplayHTML(): string;

    public function parseDocument(string $file, string $filename): array|false;
}
