<?php

$pattern = "/[0-9]{1,4}([-\/])([0-3]?[0-9])[-\/]([0-9]{2,4})/";
$line = "01/02/2023 04-08-2016 19-01-2024 01-31-2022 12/12/2012 13/3 14/44";
preg_match_all($pattern, $line, $out, PREG_PATTERN_ORDER);

echo "<pre>";
var_dump($out);
echo "</pre>";
