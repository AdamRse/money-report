<?php

namespace App\Interfaces\Services;

interface BankParserInterface{
    public function isParsable(string $document):bool;
    public function parse(string $document):array|false;
}
