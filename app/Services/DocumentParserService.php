<?php

namespace App\Services;

use App\Interfaces\Factories\BankParserFactoryInterface;
use App\Interfaces\Services\DateParserServiceInterface;
use App\Interfaces\Services\DocumentParserServiceInterface;
use App\Interfaces\Services\IncomeDuplicateCheckerServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Income;
use App\Services\IncomeDuplicateCheckerService;
use App\Traits\ErrorManagementTrait;

class DocumentParserService implements DocumentParserServiceInterface {

    use ErrorManagementTrait;

    /**
     * Services utilisés
     */
    protected IncomeDuplicateCheckerServiceInterface $duplicateChecker;
    protected DateParserServiceInterface $dateParser;
    protected BankParserFactoryInterface $bankParserFactory;

    public function __construct(BankParserFactoryInterface $bankParserFactory, IncomeDuplicateCheckerServiceInterface $duplicateChecker, DateParserServiceInterface $dateParser) {
        $this->duplicateChecker = $duplicateChecker;
        $this->dateParser = $dateParser;
        $this->bankParserFactory = $bankParserFactory;
    }

    /**
     * Parse un document pour retourner un tableau de revenus avec des données supplémentaires.
     * La méthode identifie le type de document en apelle le bon parseur.
     * @param string $file : Le contenu du fichier
     * @return array : les revenus (Income) identifiées par le parseur (modèle Income augenté). Peut être un tableau vide.
     */
    public function parseDocument(string $file): array|false {
        $bankParser = $this->bankParserFactory->getBankParser($file);
        if($bankParser){
            //[A FAIRE] Exploiter $bankParser, c'est un objet qui se trouve dans app/Services/BankParser
            return [];
        }
        else{
            /**
             * @var ErrorManagementTrait
             */
            if($this->bankParserFactory->isError())
                $this->errorAdd($this->bankParserFactory->errorGetArray());
            else
                $this->errorAdd("Le parseur de la banque n'a pas été trouvé.");
            return false;
        }
    }
}
