<?php

    interface WordsCounter {
        public function getWordsCount($string):array;
    }


    class WordsCounterService1 Implements WordsCOunter{
        public function getWordsCount($string):array {
                $string = strtolower($string);
                $string = preg_replace("/[^\w\s]/","", $string);
                $words = str_word_count($string, 1);
                $count = array_count_values($words);
                return  $count;
        }
    }

    class WordsCounterService2 Implements WordsCOunter{
        public function getWordsCount($string):array {
                //DECLARAMOS UN MAPA PARA CONTAR LAS PALABRAS
                $palabras = array();
                //CONCERTIMOS LA CADENA A MINUSCULAS Y ELIMINAMOS PUNTUACIONES Y CARACTERES ESPECIALES
                $string = strtolower($string);
                $string = preg_replace("/[^\w\s]/","", $string);
                //SEPARAMOS LAS PALABRAS EN UN ARRAY
                $string = explode(" ", $string);
                foreach ($string as $palabra){
                    if (isset($palabras[$palabra])){
                        $palabras[$palabra]++;
                    } else {
                        $palabras[$palabra] = 1;
                    }
                }

                return  $palabras;
        }
    }

    $counter = new WordsCounterService1();
    $result = $counter->getWordsCount("Hola mundo, hola a todos. Hola a ti también.");
    print("El conteo es: " . implode(", ", $result));

    $counter2 = new WordsCounterService2();
    $result2 = $counter2->getWordsCount("Hola mundo, hola a todos. Hola a ti también.");
    foreach ($result2 as $palabra => $conteo) {
        echo "La palabra '$palabra' aparece $conteo veces.\n";
    }
?>