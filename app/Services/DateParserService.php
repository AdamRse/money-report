<?php

namespace App\Services;

use App\Interfaces\Services\DateParserServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DateParserService implements DateParserServiceInterface {

    protected $_patternDateStrong = '/[0-9]{1,4}([-\/])([0-3?][0-9])[-\/]([0-9]{2,4})/';
    protected $_patternDateSoft = '/[0-9]{1,4}([-\/])([0-3?][0-9])[-\/]([0-9]{2,4})/';

    public function documentParsableWithCurrentLocale(string $document): bool {
        if (preg_match_all($this->_patternDateSoft, $document, $matches)) {
            foreach ($matches[0] as $date) {
                try {
                    Carbon::parse($date);
                } catch (\Carbon\Exceptions\InvalidFormatException) {
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
}
