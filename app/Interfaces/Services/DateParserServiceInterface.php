<?php

namespace App\Interfaces\Services;

use Carbon\Carbon;

interface DateParserServiceInterface {

    public function documentParsableWithCurrentLocale(string $document): bool;

    public function arrayDateParsableWithCurrentLocale(array $arrayDate): bool;

    public function isDateParsable(string $date): false|Carbon;

    public function setLocaleForDocument(string $document): bool;

    public function resetLocale(): void;
}
