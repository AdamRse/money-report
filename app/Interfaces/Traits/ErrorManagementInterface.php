<?php

namespace App\Interfaces\Traits;

interface ErrorManagementInterface{
    public function errorAdd(string|array $error):void;
    public function isError();
    /**
     * Affiche les erreurs rencontrées lord du processus dans un format html
     * @var string $alt : texte alternatif à afficher si aucune erreur n'est présente
     * @return string
     */
    public function errorDisplayHTML(string $alt = ""): string;
    public function errorGetArray(): array;
    public function errorHeritFrom(ErrorManagementInterface $parent):void;
}
