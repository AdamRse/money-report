<?php

namespace App\Interfaces\Services;

use App\Interfaces\Factories\BankParserFactoryInterface;

interface DocumentParserServiceInterface {
    public function __construct(BankParserFactoryInterface $bankParserFactory, IncomeDuplicateCheckerServiceInterface $duplicateChecker, DateParserServiceInterface $dateParser);
    public function parseDocument(string $file, string $filename): array|false;

    /**
     * Trait ErrorManagementTrait
     * Intelephense a besoin de ces références pour ne pas indiquer d'erreur
     */
    public function isError();
    public function errorDisplayHTML();
    public function errorGetArray();
}
