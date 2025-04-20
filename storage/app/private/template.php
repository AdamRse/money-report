<?php

namespace App\Services\BankParsers;

use App\Abstract\BankParserAbstract;
use App\Traits\ErrorManagementTrait;

class LaBanquePostaleParser extends BankParserAbstract{

    use ErrorManagementTrait;

    public static function isParsable(string $document, string $filename): bool{
        $lignes = explode("\n", $document, 8);
        if(str_contains($lignes[0], "Numéro Compte"))
            return true;
    }

    public function parse(string $document = ""){
        $this->parse();
    }
}

$obj = new LaBanquePostaleParser();
$obj->parse();
