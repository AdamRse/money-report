<?php

namespace App\Factories;

use App\Interfaces\Factories\BankParserFactoryInterface;
use App\Abstract\BankParserAbstract;
use App\Traits\ErrorManagementTrait;

//Classes
use App\Services\BankParser\LaBanquePostaleParser;

class BankParserFactory implements BankParserFactoryInterface{

    use ErrorManagementTrait;


    public function getBankParser(string $document):BankParserAbstract|false{
        //[A FAIRE] utiliser BankParserFactoryProvider spécialisé pour gérer les dépendances
        return new LaBanquePostaleParser();
    }
}
