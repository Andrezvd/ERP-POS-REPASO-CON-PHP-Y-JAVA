<?php 

    interface Polygon{
        public function getArea(): float;
        public function imprimirArea();
    }

    abstract class Figuras implements Polygon {
       protected $nombre;

       public function __construct($nombre) {
        $this->nombre = $nombre;
       }

       public function imprimirArea() {
        echo "el área de {$this->nombre} es: " . $this->getArea();
       }
    }

    class Triangulo extends Figuras {
        private $base;
        private $altura;

        public function __construct($base, $altura) {
            parent::__construct("Triangulo");
            $this->base = $base;
            $this->altura = $altura;
        }

        public function getArea(): float {
            return ($this->base * $this->altura) / 2;
        }

    }

    $figuras = [
        new Triangulo(50,90),
        new Triangulo(30,60)
    ];

    foreach ($figuras as $figura) {
        echo $figura->imprimirArea() . PHP_EOL;
    }
?>