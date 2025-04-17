<?php

namespace App\Traits;

use App\Interfaces\Traits\ErrorManagementInterface;

trait ErrorManagementTrait
{
    /**
     * Liste des erreurs rencontrées au cours du processus, pour éviter les imbrication de try
     * @var array<string>
     */
    protected array $_errors = [];

    public function errorAdd(string|array $error):void{
        if (!empty($error)){
            if(is_array($error))
                $this->_errors = array_merge($this->_errors, $error);
            else
                $this->_errors[] = $error;
        }
    }


    public function isError(){
        $nbErrors = sizeof($this->_errors);
        return $nbErrors > 0 ? $nbErrors : false;
    }

    /**
     * Affiche les erreurs rencontrées lord du processus dans un format html
     * @var string $alt : Text alternatif à afficher si pas d'erreur répertoriée
     * @return string
     */
    public function errorDisplayHTML(string $alt = ""):string{

        //Patttern qui encapsule tout le message
        $beforeBloc = "<div><ul>";
        $afterBloc = "</ul></div>";
        //Pattern qui encapsule chaque erreur
        $beforUnit = "<li>";
        $afterUnit = "</li>";
        //Valeur de retour
        $rt = null;

        if($this->isError()){
            $rt = $beforeBloc;
            foreach ($this->_errors as $error) {
                $rt .= $beforUnit.$error.$afterUnit;
            }
            $rt .= $afterBloc;
        }
        elseif(empty($alt)){
            $rt = $beforeBloc.$alt.$afterBloc;
        }

        return $rt;
    }

    public function errorGetArray(): array{
        return $this->_errors;
    }

    public function errorHeritFrom(ErrorManagementInterface $parent):void{
        $this->_errors = array_merge($parent->errorGetArray(), $this->_errors);
    }
}
