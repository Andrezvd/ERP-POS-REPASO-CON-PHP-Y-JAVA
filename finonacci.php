<?php 
    function fibonacci($n) {
        
        $a = 0;
        $b = 1;

        for ($i = 0; $i < $n; $i++) {
            $temp = $b;
            $b = $a + $b;
            $a = $temp;
            echo $a . "\n";
        }
    }

    fibonacci(50);
?> 