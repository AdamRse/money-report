<?php

namespace App\Interfaces\Factories;
use App\Abstract\BankParserAbstract;

interface BankParserFactoryInterface {

    public function getBankParser(string $document, string $filename):BankParserAbstract|false;

    /**
     * Trait ErrorManagementTrait
     * Intelephense a besoin de ces références pour ne pas indiquer d'erreur
     */
    public function isError();
    public function errorDisplayHTML();
    public function errorGetArray();

}
