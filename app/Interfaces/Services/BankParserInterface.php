<?php

namespace App\Interfaces\Services;

interface BankParserInterface{
    public static function isParsable(string $document, string $filename):bool;
    public function parse(string $document = ""):array|false;
    public function findDelimiterInHead(string $document = "", bool $allowDeepSearch = false): static;
    public function findDelimiterDeep(string $document, int|false $nbHints = false): static;
}
