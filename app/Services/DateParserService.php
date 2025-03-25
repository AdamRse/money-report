<?php

namespace App\Services;

use App\Interfaces\Services\DateParserServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DateParserService implements DateParserServiceInterface {

    protected $_patternDateStrong = '/[0-9]{1,4}([-\/])([0-3]?[0-9])[-\/]([0-9]{2,4})/';
    protected $_patternDateSoft = '/[0-9]{1,4}([-\/])([0-3]?[0-9])[-\/]([0-9]{2,4})/';
    public string $_defaultLocale;

    public function __construct() {
        $this->_defaultLocale = Carbon::getLocale();
    }

    public function documentParsableWithCurrentLocale(string $document): bool {
        info("---------------------------------------------");
        info("Test avec la locale '" . Carbon::getLocale() . "'");
        if (preg_match_all($this->_patternDateSoft, $document, $matches)) {
            info(sizeof($matches[0]) . " dates à tester");
            info($matches[0]);
            foreach ($matches[0] as $date) {
                try {
                    Carbon::parse($date);
                } catch (\Carbon\Exceptions\InvalidFormatException) {
                    info("Date échouée :");
                    info($date);
                    return false;
                }
            }
            return true;
        } else
            return true;
    }

    public function arrayDateParsableWithCurrentLocale(array $arrayDate): bool {
        foreach ($arrayDate as $date) {
            try {
                Carbon::parse($date);
            } catch (\Carbon\Exceptions\InvalidFormatException) {
                return false;
            }
        }
        return true;
    }

    public function isDateParsable(string $date): false|Carbon {
        try {
            $date = Carbon::parse($date);
            return $date;
        } catch (\Carbon\Exceptions\InvalidFormatException) {
            return false;
        }
        return false;
    }

    public function setLocaleForDocument(string $document): bool {
        $locales = [$this->_defaultLocale, "fr_FR", "en_US"];
        $parsed = false;
        foreach ($locales as $locAtester) {
            Carbon::setLocale($locAtester);
            if ($parsed = $this->documentParsableWithCurrentLocale($document))
                break;
        }
        if (!$parsed)
            $this->resetLocale();
        return $parsed;
    }

    public function resetLocale(): void {
        Carbon::setLocale($this->_defaultLocale);
    }
}
