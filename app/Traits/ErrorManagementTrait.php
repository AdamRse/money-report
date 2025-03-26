<?php

namespace App\Traits;

trait ErrorManagementTrait
{
    /**
     * Liste des erreurs rencontrées au cours du processus, pour éviter les imbrication de try
     * @var array<string>
     */
    public array $_errors = [];

    private function addError(string $error) {
        if (!empty($error))
            $this->_errors[] = $error;
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
}
