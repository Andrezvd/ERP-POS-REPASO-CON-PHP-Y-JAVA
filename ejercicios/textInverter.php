<?php

interface TextInversor {
        public function invertirTexto($id, $textoNornmal): TextoInvertido;
    }

class Inversor implements TextInversor {
    
    public function invertirTexto($id, $textoNornmal): TextoInvertido {
        
         $textoCasiInvertido = str_split($textoNornmal);
         $textoInvertido = "";
         for($i = count($textoCasiInvertido) - 1; $i >= 0; $i--){
            $textoInvertido .= $textoCasiInvertido[$i];
         }
         return new TextoInvertido($id, $textoNornmal, $textoInvertido);
    }
}

class TextoInvertido {
    private $id;
    private $textoNormal;
    private $textoInvertido;
    
    public function __construct($id, $textoNormal, $textoInvertido) {
        $this->id = $id;
        $this->textoNormal = $textoNormal;
        $this->textoInvertido = $textoInvertido;
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getTextoNormal() {
        return $this->textoNormal;
    }
    
    public function getTextoInvertido() {
        return $this->textoInvertido;
    }
}

$textInvertido = new Inversor();
$claseInvertida = $textInvertido->invertirTexto(1, "Hola Mundo");

echo "Texto original: " . $claseInvertida->getTextoNormal() . "\n";
echo "Texto invertido: " . $claseInvertida->getTextoInvertido() . "\n";
?>