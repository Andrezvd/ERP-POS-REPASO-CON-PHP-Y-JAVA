<?php

    function esPrimo ($numero) {

        if ($numero < 1) {
            return false;
        }
        for ($i = 2; $i <= sqrt($numero); $i++) {
               if ($numero % $i == 0) {
                return false;
               }
        }
        return true;
    }

    echo "el nmero 2 es primo?" . esPrimo(9) ? "si" : "no";

?>