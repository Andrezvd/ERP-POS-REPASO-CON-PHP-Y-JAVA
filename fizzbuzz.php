<?php

    for ($i = 1; $i <= 100; $i++) {
        echo $i;

        if ($i % 3 == 0 && $i % 5 == 0) {
            echo "FizzBuzz";
        } else {
            if ($i % 3 == 0) {
                echo "Fizz";
            } else if ($i % 5 == 0) {
                echo "Buzz";
            }
        }
        echo "\n";
    }

?>

<?php 

    function esAnagrama($palabra1, $palabra2): bool {
        $palabra1 = strtolower($palabra1);
        $palabra2 = strtolower($palabra2);

        if ($palabra1 == $palabra2) {
            return false;
        }

        if (strlen($palabra1) != strlen($palabra2)) {
            return false;
        }

        $sorted1 = str_split($palabra1);
        $sorted2 = str_split($palabra2);
        sort($sorted1);
        echo "sorted1 after sorting: " . implode('', $sorted1) . "\n";
        sort($sorted2);
        echo "sorted2 after sorting: " . implode('', $sorted2) . "\n";
        return $sorted1 == $sorted2;
    }

    $palabra1 = "bdca";
    $palabra2 = "dcab";

    esAnagrama($palabra1, $palabra2) ? print("Son anagramas") : print("No son anagramas");




?>