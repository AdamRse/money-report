<?php

namespace App\Interfaces\Factories;
use App\Abstract\BankParserAbstract;

interface BankParserFactoryInterface {

    public function getBankParser(string $document):BankParserAbstract|false;
}
