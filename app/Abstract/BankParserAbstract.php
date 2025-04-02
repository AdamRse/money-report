<?php

namespace App\Abstract;

use App\Interfaces\Services\BankParserInterface;
use App\Traits\ErrorManagementTrait;
use App\Interfaces\Services\IncomeDuplicateCheckerServiceInterface;
use App\Interfaces\Services\DateParserServiceInterface;
use App\Models\Income;
use Carbon\Carbon;


abstract class BankParserAbstract implements BankParserInterface{

    use ErrorManagementTrait;

    /**
     * Patterns à détecter dans les libellés pour exclusion (les résultats restent affichés mais décochés)
     * @var array<string>
     */
    protected array $excludePatterns = [
        'adam rousselle',
        'rousselle adam'
    ];

    /**
     * Patterns pour la détection des types de revenus
     * Key = pattern à détecter (insensible à la casse)
     * Value = ID du type de revenu dans la base de données
     * @var array<string, int>
     */
    protected array $typePatterns = [
        'FRANCE TRAVAIL' => 3,
        'POLE EMPLOI' => 3,
        'CAF' => 2,
        'ALLOCATIONS FAMILIALES' => 2,
        'REMBOURSEMENT' => 6,
        'SALAIRE' => 4,
        'CHOMAGE' => 5
    ];

    public string $_delimiter;
    public string $_document;
    protected IncomeDuplicateCheckerServiceInterface $duplicateChecker;
    protected DateParserServiceInterface $dateParser;
    protected array $_delimiterList = [';', "\t", ',', '|', ':'];

    public function __construct(IncomeDuplicateCheckerServiceInterface $duplicateChecker, DateParserServiceInterface $dateParser) {
        $this->duplicateChecker = $duplicateChecker;
        $this->dateParser = $dateParser;
    }

    public function findDelimiterInHead(string $document = "", bool $allowDeepSearch = false): static{
        if(empty($document)){
            if(empty($this->_document)){
                $this->errorAdd("Parseur abstrait : Aucun document passé pour trouver un délimiteur.");
                return $this;
            }
            $document = $this->_document;
        }
        $firstLine = strtok($document, "\n");
        $bestHint=[];

        $sizeDelimiterArray = sizeof($this->_delimiterList);
        for ($i=0; $i < $sizeDelimiterArray; $i++) {
            $occurences=substr_count($firstLine, $this->_delimiter[$i]);
            if(empty($bestHint["occurrences"])){
                $bestHint=["delimiter" => $this->_delimiter[$i], "occurrences" => $occurences];
            }
            else{
                if($bestHint["occurrences"]<$occurences)
                    $bestHint=["delimiter" => $this->_delimiter[$i], "occurrences" => $occurences];
            }
        }

        if($bestHint["delimiter"]){
            $this->_delimiter = $bestHint["delimiter"];
        }
        else{
            if($allowDeepSearch)
                return $this->findDelimiterDeep($document);
            $this->errorAdd("Impossible de trouver le délimiteur dans le header du document parmi : ".function(){
                $listDelimiters = "";
                foreach ($this->_delimiterList as $delimiter) {
                    $listDelimiters .= $delimiter;
                }
                return $listDelimiters;
            });
        }
        return $this;
    }

    /**
     * $nbHints : nombre de fois où on a trouvé le même nombre de colonnes avec le même délimiteur considéré comme un indice suffisant.
     *              Si false, alors on vérifie tout le document pout tous le slimiters
     */
    public function findDelimiterDeep(string $document, int|false $nbHints = false): static{
        $hints=[];
        $sizeDelimiterArray = sizeof($this->_delimiterList);
        $bestHint=[];

        for($i = 0; $i<$sizeDelimiterArray; $i++){
            $linesDoc = explode("\n", $document);
            foreach ($linesDoc as $line) {
                if(trim($line)=="")
                    continue;

                $rows = substr_count($line, $this->_delimiter[$i]);
                if(isset($hints[$i][$rows])){
                    $hints[$i][$rows]++;
                    if($nbHints && $hints[$i][$rows] >= $nbHints){//On considère que $nbHints lignes avec le même nombre de colonnes est un indice suffisant
                        $bestHint = ["delimiter" => $this->_delimiter[$i]];
                        break 2;
                    }
                }
                else
                    $hints[$i][$rows]=1;

                if(isset($bestHint["delimiter"])){
                    if($bestHint["occurrences"]<$hints[$i][$rows])
                        $bestHint=["delimiter" => $this->_delimiter[$i], "occurrences" => $hints[$i][$rows]];
                }
                else
                    $bestHint=["delimiter" => $this->_delimiter[$i], "occurrences" => $hints[$i][$rows]];
            }
        }

        if($bestHint["delimiter"]){
            $this->_delimiter = $bestHint["delimiter"];
        }
        else
            $this->errorAdd("Impossible de trouver le délimiteur dans le document (recherche profonde) parmi : ".function(){
                $listDelimiters = "";
                foreach ($this->_delimiterList as $delimiter) {
                    $listDelimiters .= $delimiter;
                }
                return $listDelimiters;
            });

        return $this;
    }

    /**
     * Vérifie si un libellé doit être exclu
     */
    protected function shouldExclude(string $description): bool {
        $description = strtolower($description);
        return collect($this->excludePatterns)
            ->contains(fn($pattern) => str_contains($description, strtolower($pattern)));
    }

    /**
     * Détermine le type de revenu en fonction du libellé
     */
    protected function detectIncome_types(string $description): ?int {
        $description = strtolower($description);
        foreach ($this->typePatterns as $pattern => $typeId) {
            if (str_contains($description, strtolower($pattern))) {
                return $typeId;
            }
        }
        return null;
    }

    abstract public static function isParsable(string $document, string $filename): bool;

}
