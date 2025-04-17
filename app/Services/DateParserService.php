<?php

namespace App\Services;

use App\Interfaces\Services\DateParserServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Traits\ErrorManagementTrait;

class DateParserService implements DateParserServiceInterface{

    use ErrorManagementTrait;

    protected $_patternDateStrong = '/[0-9]{1,4}([-\/])([0-3]?[0-9])[-\/]([0-9]{2,4})/';
    protected $_patternDateSoft = '/[0-9]{1,4}([-\/])([0-3]?[0-9])[-\/]([0-9]{2,4})/';
    public string $_defaultLocale;
    public string $_separator = "";

    public function __construct(){
        $this->_defaultLocale = Carbon::getLocale();
    }

    public function returnDateFromString(string $str, bool $strongPattern = false):string|false{
        $pattern = $strongPattern ? $this->_patternDateStrong : $this->_patternDateSoft;
        if(preg_match($pattern, $str, $match)){
            return $match[0] ?? false;
        }
        return false;
    }

    /**
     * Certifie que le format retourné est parsable avec Carbon::createFromFormat pour toutes les dates, ou renvoie false
     */
    public function findDateFormat(array|string $find):string|false{
        return (is_string($find)) ? $this->findDateFormatDocument($find) : $this->findDateFormatArray($find);
    }

    private function findDateFormatDocument(string $document, bool $soft = true){
        $patternDate = ($soft) ? $this->_patternDateSoft : $this->_patternDateStrong;
        if(preg_match_all($patternDate, $document, $matches)){
            if(!empty($matches[0]) && !empty($matches[1])){
                $this->determineSeparatorFromArray($matches[1]);
                return $this->findDateFormatArray($matches[0]);
            }
        }
        return true;
    }
    private function findDateFormatArray(array $dates):false|string {
        if(empty($this->_separator)){
            $document = implode(' ', $dates);
            if(preg_match_all($this->_patternDateSoft, $document, $matches)){
                if(!empty($matches[1]))
                    $this->determineSeparatorFromArray($matches[1]);
                else
                    return false;
            }
            else
                return false;
        }
        //On a un séparateur ou on a déjà retourné false

        //liste des formats à tester
        $formatList = ['d'.$this->_separator.'m'.$this->_separator.'Y',
            'm'.$this->_separator.'d'.$this->_separator.'Y',
            'Y'.$this->_separator.'m'.$this->_separator.'d',
            'd'.$this->_separator.'m'.$this->_separator.'y',
            'm'.$this->_separator.'d'.$this->_separator.'y',
            'y'.$this->_separator.'m'.$this->_separator.'d',
        ];
        $foundFormat = false;

        foreach($formatList as $format) {
            $success = true;
            foreach($dates as $date) {
                try{
                    Carbon::createFromFormat($format, $date);
                }
                catch(\Carbon\Exceptions\InvalidFormatException){
                    $success = false;
                    break;
                }
            }
            if($success){
                $foundFormat = $format;
                break;
            }
        }

        return $foundFormat;
    }
    private function determineSeparatorFromArray(array $separators){
        $formatedList = [];
        foreach($separators as $separator){
            $isIn = false;

            foreach($formatedList as $tabSeparator){
                if($tabSeparator[0]==$separator){
                    $tabSeparator[1]++;
                    $isIn=true;
                }
            }

            if(!$isIn)
                $formatedList[]=[$separator, 1];
        }

        $bestSeparator=[];
        foreach($formatedList as $tabSeparator){
            if(empty($bestSeparator) || $tabSeparator[1]>$bestSeparator[1])
                $bestSeparator=$tabSeparator;
        }

        if(!empty($bestSeparator[0]))
            $this->_separator = $bestSeparator[0];
    }

    public function documentParsableWithCurrentLocale(string $document): bool{
        if (preg_match_all($this->_patternDateSoft, $document, $matches)){
            info(sizeof($matches[0]) . " dates à tester");
            info($matches[0]);
            foreach ($matches[0] as $date){
                try{
                    Carbon::parse($date);
                }
                catch(\Carbon\Exceptions\InvalidFormatException){
                    info("Date échouée :");
                    info($date);
                    return false;
                }
            }
            return true;
        } else
            return true;
    }

    public function arrayDateParsableWithCurrentLocale(array $arrayDate): bool{
        foreach($arrayDate as $date){
            try{
                Carbon::parse($date);
            }
            catch(\Carbon\Exceptions\InvalidFormatException){
                return false;
            }
        }
        return true;
    }

    public function isDateParsable(string $date): false|Carbon{
        try{
            $date = Carbon::parse($date);
            return $date;
        }
        catch (\Carbon\Exceptions\InvalidFormatException){
            return false;
        }
        return false;
    }

    public function setLocaleForDocument(string $document): bool{
        $locales = [$this->_defaultLocale, "fr_FR", "en_US"];
        $parsed = false;
        foreach ($locales as $locAtester){
            Carbon::setLocale($locAtester);
            if ($parsed = $this->documentParsableWithCurrentLocale($document))
                break;
        }
        if (!$parsed)
            $this->resetLocale();
        return $parsed;
    }

    public function resetLocale(): void{
        Carbon::setLocale($this->_defaultLocale);
    }
}
