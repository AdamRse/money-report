<?php
// $PATTERN = "/([A-Z])[0-9]/";
// $subject = "A2 A3 E5 Z LL G6 89 56 B0";
// preg_match_all($PATTERN, $subject, $matches);

// var_dump($matches);


require 'vendor/autoload.php';

use Carbon\Carbon;

// Afficher la locale système
echo "Locale système : " . setlocale(LC_ALL, 0) . "\n";

// Afficher la locale utilisée par Carbon
echo "Locale Carbon : " . Carbon::getLocale() . "\n";
