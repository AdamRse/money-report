<?php

namespace App\Interfaces\Services;

interface BankParserInterface{
    public function parse(string $document):array|false;
}
