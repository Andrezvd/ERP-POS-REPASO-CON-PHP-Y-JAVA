<?php

// Activa tipado estricto en ESTE archivo.
// Con esto, PHP no hace conversiones silenciosas de tipos escalares.
declare(strict_types=1);

// interface define un contrato: que metodos deben existir.
// No guarda estado y no implementa logica concreta.
interface Poligono
{
    // public: en interfaces los metodos son publicos por contrato.
    // : float indica tipo de retorno obligatorio.
    public function calcularArea(): float;

    // Segundo metodo del contrato: imprimir un mensaje con el area.
    public function imprimirArea(): string;
}

// abstract class: puede tener logica reutilizable + metodos para heredar.
// No es obligatorio usarla, pero aqui evita duplicar imprimirArea() en cada clase.
// Si no quisieras clase abstracta, cada poligono tendria que repetir ese metodo.
abstract class Figura implements Poligono
{
    // protected: visible en esta clase y en clases hijas.
    protected string $nombre;

    // __construct se ejecuta automaticamente al crear un objeto con new.
    // Sirve para inicializar estado.
    public function __construct(string $nombre)
    {
        $this->nombre = $nombre;
    }

    // Implementacion comun para todas las figuras.
    // Usa polimorfismo: llama calcularArea() y cada hija aporta su formula.
    public function imprimirArea(): string
    {
        return "El area del {$this->nombre} es: " . $this->calcularArea();
    }
}

// extends hereda atributos/metodos de Figura.
class Triangulo extends Figura
{
    // private: solo accesible dentro de esta clase.
    private float $base;
    private float $altura;

    // Sobrescribe/inicializa datos especificos del triangulo.
    public function __construct(float $base, float $altura)
    {
        // parent::__construct llama al constructor de la clase padre.
        parent::__construct("triangulo");
        $this->base = $base;
        $this->altura = $altura;
    }

    // Este metodo cumple el contrato de la interface Poligono.
    // Aqui ocurre la sobrescritura de comportamiento segun la figura concreta.
    public function calcularArea(): float
    {
        return ($this->base * $this->altura) / 2;
    }
}

class Cuadrado extends Figura
{
    private float $lado;

    public function __construct(float $lado)
    {
        parent::__construct("cuadrado");
        $this->lado = $lado;
    }

    // Misma firma, distinta implementacion: polimorfismo/sobrescritura.
    public function calcularArea(): float
    {
        return $this->lado * $this->lado;
    }
}

class Rectangulo extends Figura
{
    private float $base;
    private float $altura;

    public function __construct(float $base, float $altura)
    {
        parent::__construct("rectangulo");
        $this->base = $base;
        $this->altura = $altura;
    }

    // Misma firma, distinta formula para rectangulo.
    public function calcularArea(): float
    {
        return $this->base * $this->altura;
    }
}

// Arreglo de objetos: permite tratar distintas clases bajo una misma interfaz.
$figuras = [
    new Triangulo(10, 5),
    new Cuadrado(4),
    new Rectangulo(8, 3),
];

// foreach recorre cada objeto y ejecuta el mismo metodo.
// Eso demuestra polimorfismo en tiempo de ejecucion.
foreach ($figuras as $figura) {
    echo $figura->imprimirArea() . PHP_EOL;
}

?>
