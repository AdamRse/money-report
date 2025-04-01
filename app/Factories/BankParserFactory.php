<?php

namespace App\Factories;

use App\Interfaces\Factories\BankParserFactoryInterface;
use App\Abstract\BankParserAbstract;
use App\Traits\ErrorManagementTrait;
use Illuminate\Contracts\Container\Container;

class BankParserFactory implements BankParserFactoryInterface
{
    use ErrorManagementTrait;

    /**
     * @var Container L'instance du container IoC
     */
    protected Container $app;

    /**
     * @var array<string, string> Liste des parseurs disponibles
     */
    protected array $parsers;

    public function __construct(Container $app){
        $this->parsers = $app->make('bank.parsers');
    }

    /**
     * Retourne une instance du parseur approprié pour le document bancaire
     *
     * @param string $document Le contenu du document à analyser
     * @return BankParserAbstract|false Le parseur approprié ou false si aucun ne correspond
     */
    public function getBankParser(string $document, string $filename): BankParserAbstract|false {
        // Logique de détection de la banque basée sur le contenu du document
        // Pour l'instant, on utilise LaBanquePostale par défaut si disponible
        foreach ($this->parsers as $parserClass){
            if ($parserClass::isParsable($document, $filename)){
                try{
                    return $this->app->make($parserClass);
                }
                catch(\Exception $e){
                    $this->errorAdd("Erreur lors de la création du parseur {$parserClass}: " . $e->getMessage());
                    break;
                }
            }
        }

        // Si aucun parseur n'est trouvé, renvoie une erreur
        $this->errorAdd("Aucun parseur bancaire compatible n'a été trouvé pour ce document");
        return false;
    }
}
