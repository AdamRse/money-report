<?php
$appLocation = "./app";

$firstRecursive = true;
function arborescence($location, $recursiveness = 0) {
    global $firstRecursive;
    if ($firstRecursive) {
        if ($recursiveness !== 0)
            die("arborescence() n'accepte qu'un paramètre.");
        echo "app/\n";
        $firstRecursive = false;
    }

    $dirList = scandir($location);

    for ($i = sizeof($dirList) - 1; $i >= 0; $i--) {
        $element = $dirList[$i];
        $pathElement = "$location/$element";

        if ($i > 1)
            displayRecursiveness($recursiveness, $i == 2);

        if ($element != "." && $element != "..") {

            if (is_dir($pathElement)) {
                echo "$element/\n";
                arborescence($pathElement, ++$recursiveness);
                $recursiveness--;
            } else {
                echo "$element\n";
            }
        }
    }
}

function displayRecursiveness($recursiveness, $last = false) {
    for ($i = 0; $i <= $recursiveness; $i++) {
        echo ($i == $recursiveness) ? (($last) ? "└── " : "├── ") : "|    ";
    }
}
arborescence($appLocation);
