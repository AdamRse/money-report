<?php

namespace App\Interfaces\Traits;

interface ErrorManagementInterface{
    public function errorAdd(string|array $error):void;
    public function isError();
    /**
     * Affiche les erreurs rencontrées lord du processus dans un format html
     * @return string
     */
    public function errorDisplayHTML(): string;
    public function errorGetArray(): array;
}
