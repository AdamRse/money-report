<?php
// Chemin vers le fichier JSON
$file_path = "./country list.json";

// Lire le contenu du fichier
$json_content = file_get_contents($file_path);

// Vérifier si la lecture du fichier a réussi
if ($json_content === false) {
    die("Erreur : Impossible de lire le fichier JSON.");
}

// Décoder le JSON en un tableau associatif
$data = json_decode($json_content, true);

// Vérifier si le décodage a réussi
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Erreur : Impossible de décoder le fichier JSON.");
}

// Chemin vers le fichier
$file_path2 = "./country list.json";

// Ouvrir le fichier en mode lecture
$file = fopen($file_path2, "r");

// Vérifier si l'ouverture du fichier a réussi
if ($file === false) {
    die("Erreur : Impossible d'ouvrir le fichier $file_path2");
}

$tabZones = [];
// Lire le fichier ligne par ligne
while (($line = fgets($file)) !== false) {
    if (substr($line, 0, 1) != "#") {
        if (preg_match("/([A-Z]{2})/", $line, $m)) {
        }
    }
}

// Fermer le fichier
fclose($file);
