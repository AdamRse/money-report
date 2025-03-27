<?php

namespace App\Traits;

trait ErrorManagementTrait
{
    /**
     * Liste des erreurs rencontrées au cours du processus, pour éviter les imbrication de try
     * @var array<string>
     */
    protected array $_errors = [];

    protected function errorAdd(string|array $error):void {
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
     * @return string
     */
    public function errorDisplayHTML(): string {
        $rt = "<div><ul>";
        foreach ($this->_errors as $error) {
            $rt .= "<li>$error</li>";
        }
        return $rt."</ul></div>";
    }

    public function errorGetArray(): array{
        return $this->_errors;
    }
}
