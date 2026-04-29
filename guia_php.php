<?php
/**
 * ============================================
 * GUÍA COMPLETA DE PHP - DE BÁSICO A AVANZADO
 * ============================================
 * Ejecutar: php guia_php.php
 * 
 * ORGANIZACIÓN:
 *   1. Variables y tipos de datos
 *   2. Strings y concatenación
 *   3. Arrays indexados
 *   4. Arrays asociativos (maps)
 *   5. Arrays multidimensionales (matrices)
 *   6. Condicionales (if/else, switch)
 *   7. Bucles (for, foreach, while)
 *   8. Funciones
 *   9. Funciones con parámetros por referencia
 *  10. Include y require
 *  11. Clases y objetos
 *  12. Herencia
 *  13. Clases abstractas e interfaces
 *  14. Métodos mágicos (__construct, __toString)
 *  15. Traits
 *  16. Manejo de archivos
 *  17. Manejo de errores (try/catch)
 *  18. Expresiones regulares
 *  19. Fechas y horas
 *  20. Sesiones
 *  21. JSON
 *  22. PDO (Conexión a MySQL)
 * ============================================
 */

echo "========== 1. VARIABLES Y TIPOS DE DATOS ==========\n\n";

// PHP es de tipado dinámico - NO se declara el tipo
$nombre = "Andrés";           // string
$edad = 25;                   // int
$altura = 1.75;               // float
$esProgramador = true;        // bool
$saldo = null;                // null

// Ver el tipo de una variable
echo "Tipo de \$nombre: " . gettype($nombre) . "\n";
echo "Tipo de \$edad: " . gettype($edad) . "\n";
echo "Tipo de \$esProgramador: " . gettype($esProgramador) . "\n";

// Constantes
define("IVA", 0.19);
const EMPRESA = "TIRESIA SAS";
echo "IVA: " . IVA . "\n";
echo "Empresa: " . EMPRESA . "\n\n";


echo "========== 2. STRINGS Y CONCATENACIÓN ==========\n\n";

$nombre = "Andrés";
$apellido = "Olivar";

// Concatenación con punto (.)
$nombreCompleto = $nombre . " " . $apellido;
echo "Concatenación: " . $nombreCompleto . "\n";

// Interpolación (solo con comillas dobles)
echo "Interpolación: $nombre $apellido\n";

// Comillas simples NO interpolan
echo 'Comillas simples: $nombre $apellido' . "\n";

// Heredoc (strings multilinea)
$texto = <<<TEXTO
Esto es un texto
multilínea con
heredoc en PHP
TEXTO;
echo $texto . "\n\n";

// Funciones útiles de strings
echo "Mayúsculas: " . strtoupper($nombre) . "\n";
echo "Minúsculas: " . strtolower($nombre) . "\n";
echo "Longitud: " . strlen($nombre) . "\n";
echo "Posición de 'dré': " . strpos($nombre, "dré") . "\n";
echo "Reemplazar: " . str_replace("Andrés", "Carlos", $nombre) . "\n";
echo "Substring (0,3): " . substr($nombre, 0, 3) . "\n\n";


echo "========== 3. ARRAYS INDEXADOS ==========\n\n";

// Forma tradicional
$frutas = array("Manzana", "Banano", "Naranja", "Uva");

// Forma corta (PHP 5.4+)
$numeros = [10, 20, 30, 40, 50];

echo "Primera fruta: " . $frutas[0] . "\n";
echo "Último número: " . $numeros[count($numeros) - 1] . "\n";

// Agregar elementos
$frutas[] = "Pera";  // Al final
array_push($frutas, "Mango");  // También al final

// Eliminar elementos
array_pop($frutas);     // Elimina el último
array_shift($frutas);   // Elimina el primero

// Ordenar
sort($frutas);          // Ascendente (modifica el original)
rsort($frutas);         // Descendente

echo "Frutas ordenadas: ";
print_r($frutas);

// Funciones útiles
echo "¿Hay Manzana?: " . (in_array("Manzana", $frutas) ? "Sí" : "No") . "\n";
echo "Total elementos: " . count($frutas) . "\n\n";


echo "========== 4. ARRAYS ASOCIATIVOS (MAPS/DICCIONARIOS) ==========\n\n";

$producto = array(
    "codigo" => "PROD001",
    "nombre" => "Laptop HP",
    "precio" => 2500000,
    "stock" => 10,
    "activo" => true
);

// Acceder
echo "Producto: " . $producto["nombre"] . "\n";
echo "Precio: $" . number_format($producto["precio"], 0, ",", ".") . "\n";

// Agregar nuevo key
$producto["categoria"] = "Electrónicos";

// Recorrer
foreach ($producto as $clave => $valor) {
    echo "$clave: $valor\n";
}

// Array de arrays asociativos (lista de productos)
$productos = [
    ["id" => 1, "nombre" => "Laptop", "precio" => 2500000],
    ["id" => 2, "nombre" => "Mouse", "precio" => 35000],
    ["id" => 3, "nombre" => "Teclado", "precio" => 45000]
];

echo "\nLista de productos:\n";
foreach ($productos as $p) {
    echo "  - {$p['nombre']}: \${$p['precio']}\n";
}

// Buscar en array asociativo
$buscado = "Mouse";
$encontrado = false;
foreach ($productos as $p) {
    if ($p["nombre"] == $buscado) {
        $encontrado = true;
        echo "¡$buscado encontrado! Precio: \${$p['precio']}\n";
        break;
    }
}

// array_column (extraer una columna)
$nombres = array_column($productos, "nombre");
print_r($nombres);

// array_filter (filtrar)
$caros = array_filter($productos, function($p) {
    return $p["precio"] > 100000;
});
echo "Productos caros: " . count($caros) . "\n\n";


echo "========== 5. ARRAYS MULTIDIMENSIONALES (MATRICES) ==========\n\n";

$matriz = [
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9]
];

echo "Elemento [1][1]: " . $matriz[1][1] . "\n"; // 5

// Recorrer matriz
for ($i = 0; $i < count($matriz); $i++) {
    for ($j = 0; $j < count($matriz[$i]); $j++) {
        echo $matriz[$i][$j] . " ";
    }
    echo "\n";
}

// Matriz asociativa (tabla de ventas)
$ventasDiarias = [
    "lunes" => ["productos" => 15, "total" => 450000],
    "martes" => ["productos" => 22, "total" => 680000],
    "miercoles" => ["productos" => 18, "total" => 520000]
];

$totalSemana = 0;
foreach ($ventasDiarias as $dia => $datos) {
    $totalSemana += $datos["total"];
    echo "$dia: \${$datos['total']}\n";
}
echo "Total semana: \$$totalSemana\n\n";


echo "========== 6. CONDICIONALES ==========\n\n";

$edad = 18;
$tieneLicencia = true;

// if/else
if ($edad >= 18 && $tieneLicencia) {
    echo "Puede conducir\n";
} elseif ($edad >= 18 && !$tieneLicencia) {
    echo "Necesita licencia\n";
} else {
    echo "No puede conducir\n";
}

// Operador ternario
$mensaje = ($edad >= 18) ? "Mayor de edad" : "Menor de edad";
echo $mensaje . "\n";

// Null coalescing operator (PHP 7+)
$usuario = $_GET["user"] ?? "Invitado";
echo "Usuario: $usuario\n";

// Switch
$dia = "lunes";
switch ($dia) {
    case "lunes":
    case "martes":
    case "miercoles":
    case "jueves":
    case "viernes":
        echo "Es día laboral\n";
        break;
    case "sabado":
    case "domingo":
        echo "Es fin de semana\n";
        break;
    default:
        echo "Día inválido\n";
}

// Match (PHP 8+)
$calificacion = "A";
$resultado = match($calificacion) {
    "A" => "Excelente",
    "B" => "Bueno",
    "C" => "Regular",
    default => "Deficiente"
};
echo "Calificación: $resultado\n\n";


echo "========== 7. BUCLES ==========\n\n";

// for
echo "For: ";
for ($i = 1; $i <= 5; $i++) {
    echo "$i ";
}
echo "\n";

// foreach (indexado)
echo "Foreach indexado: ";
$colores = ["Rojo", "Verde", "Azul"];
foreach ($colores as $color) {
    echo "$color ";
}
echo "\n";

// foreach con índice
foreach ($colores as $indice => $color) {
    echo "  [$indice] => $color\n";
}

// while
echo "While: ";
$contador = 1;
while ($contador <= 5) {
    echo "$contador ";
    $contador++;
}
echo "\n";

// do-while (se ejecuta al menos una vez)
echo "Do-while: ";
$i = 1;
do {
    echo "$i ";
    $i++;
} while ($i <= 5);
echo "\n";

// break y continue
echo "Break/Continue: ";
for ($i = 1; $i <= 10; $i++) {
    if ($i == 3) continue;  // Salta el 3
    if ($i == 7) break;     // Termina en el 7
    echo "$i ";
}
echo "\n\n";


echo "========== 8. FUNCIONES ==========\n\n";

// Función básica
function sumar($a, $b) {
    return $a + $b;
}
echo "Suma: " . sumar(5, 3) . "\n";

// Parámetros por defecto
function saludar($nombre = "Invitado") {
    return "Hola, $nombre!";
}
echo saludar() . "\n";
echo saludar("Andrés") . "\n";

// Tipado de parámetros (PHP 7+)
function calcularIva(float $precio): float {
    return $precio * 0.19;
}
echo "IVA de 1000: " . calcularIva(1000) . "\n";

// Tipado de retorno nullable
function buscarProducto(int $id): ?array {
    $productos = [
        1 => ["nombre" => "Laptop", "precio" => 2500000],
        2 => ["nombre" => "Mouse", "precio" => 35000]
    ];
    return $productos[$id] ?? null;
}
$prod = buscarProducto(1);
echo "Producto encontrado: " . ($prod["nombre"] ?? "Ninguno") . "\n";

// Funciones variádicas (...)
function sumarTodos(...$numeros) {
    return array_sum($numeros);
}
echo "Suma de todos: " . sumarTodos(1, 2, 3, 4, 5) . "\n";

// Funciones flecha (PHP 7.4+)
$duplicar = fn($n) => $n * 2;
echo "Duplicar 5: " . $duplicar(5) . "\n";

// Callbacks
$numeros = [1, 2, 3, 4, 5];
$pares = array_filter($numeros, fn($n) => $n % 2 == 0);
echo "Pares: " . implode(", ", $pares) . "\n\n";


echo "========== 9. FUNCIONES CON PARÁMETROS POR REFERENCIA ==========\n\n";

function agregarImpuesto(&$precio) {
    $precio *= 1.19;  // Modifica la variable original
}

$valor = 1000;
agregarImpuesto($valor);
echo "Valor con impuesto: $valor\n\n";


echo "========== 10. INCLUDE Y REQUIRE ==========\n\n";

// include "archivo.php";     // Si no existe, da warning y continúa
// require "archivo.php";    // Si no existe, da error fatal
// include_once "archivo.php";  // Solo incluye una vez
// require_once "archivo.php";  // Solo incluye una vez

echo "Los archivos se incluyen así:\n";
echo "  require_once 'config/database.php';\n";
echo "  include 'funciones.php';\n\n";


echo "========== 11. CLASES Y OBJETOS ==========\n\n";

class Producto {
    // Propiedades con visibilidad
    public string $codigo;
    public string $nombre;
    private float $precio;      // Solo accesible desde la clase
    protected int $stock;       // Accesible desde la clase y herencia
    public static int $contador = 0;  // Propiedad estática

    // Constructor (PHP 8+ con promoted properties)
    public function __construct(
        string $codigo = "",
        string $nombre = "",
        float $precio = 0,
        int $stock = 0
    ) {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->precio = $precio;
        $this->stock = $stock;
        self::$contador++;
    }

    // Getter
    public function getPrecio(): float {
        return $this->precio;
    }

    // Setter
    public function setPrecio(float $precio): void {
        if ($precio > 0) {
            $this->precio = $precio;
        }
    }

    // Método
    public function getInfo(): string {
        return "{$this->codigo} - {$this->nombre}: \${$this->precio} (Stock: {$this->stock})";
    }

    // Método estático
    public static function getContador(): int {
        return self::$contador;
    }

    // Destructor
    public function __destruct() {
        // Se ejecuta cuando el objeto se destruye
    }
}

// Crear objetos
$laptop = new Producto("PROD001", "Laptop HP", 2500000, 10);
$mouse = new Producto("PROD002", "Mouse Inalámbrico", 35000, 50);

echo $laptop->getInfo() . "\n";
echo $mouse->getInfo() . "\n";
echo "Total productos creados: " . Producto::getContador() . "\n\n";


echo "========== 12. HERENCIA ==========\n\n";

class ProductoDigital extends Producto {
    public string $formato;
    private string $linkDescarga;

    public function __construct(
        string $codigo,
        string $nombre,
        float $precio,
        string $formato,
        string $linkDescarga = ""
    ) {
        // Llamar al constructor del padre
        parent::__construct($codigo, $nombre, $precio, 999999);
        $this->formato = $formato;
        $this->linkDescarga = $linkDescarga;
    }

    // Sobrescribir método
    public function getInfo(): string {
        $infoBase = parent::getInfo();
        return "$infoBase - Formato: {$this->formato}";
    }

    public function getLink(): string {
        return $this->linkDescarga;
    }
}

$ebook = new ProductoDigital("DIG001", "PHP Guide", 15000, "PDF", "https://...");
echo $ebook->getInfo() . "\n";
echo "Herencia: ProductoDigital extiende Producto\n\n";


echo "========== 13. CLASES ABSTRACTAS E INTERFACES ==========\n\n";

// Interface
interface Imprimible {
    public function imprimir(): string;
}

interface Exportable {
    public function toJSON(): string;
    public function toXML(): string;
}

// Clase abstracta
abstract class Persona {
    protected string $nombre;
    protected string $documento;

    public function __construct(string $nombre, string $documento) {
        $this->nombre = $nombre;
        $this->documento = $documento;
    }

    // Método abstracto (debe ser implementado por los hijos)
    abstract public function getRol(): string;

    // Método concreto
    public function getNombre(): string {
        return $this->nombre;
    }
}

// Implementar interface y extender clase abstracta
class Cliente extends Persona implements Imprimible, Exportable {
    private string $email;
    private float $deuda;

    public function __construct(string $nombre, string $documento, string $email) {
        parent::__construct($nombre, $documento);
        $this->email = $email;
        $this->deuda = 0;
    }

    public function getRol(): string {
        return "Cliente";
    }

    public function imprimir(): string {
        return "Cliente: {$this->nombre} - {$this->documento}";
    }

    public function toJSON(): string {
        return json_encode([
            "nombre" => $this->nombre,
            "documento" => $this->documento,
            "email" => $this->email
        ]);
    }

    public function toXML(): string {
        return "<cliente><nombre>{$this->nombre}</nombre></cliente>";
    }
}

$cliente = new Cliente("Juan Pérez", "12345678", "juan@email.com");
echo $cliente->imprimir() . "\n";
echo "JSON: " . $cliente->toJSON() . "\n";
echo "Rol: " . $cliente->getRol() . "\n\n";


echo "========== 14. MÉTODOS MÁGICOS ==========\n\n";

class Usuario {
    private array $datos = [];

    // __get: se llama al acceder a propiedad inaccesible
    public function __get(string $name): mixed {
        return $this->datos[$name] ?? null;
    }

    // __set: se llama al asignar a propiedad inaccesible
    public function __set(string $name, mixed $value): void {
        $this->datos[$name] = $value;
    }

    // __toString: se llama al hacer echo del objeto
    public function __toString(): string {
        return "Usuario: " . ($this->datos["nombre"] ?? "Sin nombre");
    }

    // __call: se llama al invocar método inaccesible
    public function __call(string $name, array $arguments): mixed {
        return "Método '$name' no existe";
    }
}

$user = new Usuario();
$user->nombre = "Andrés";  // Llama a __set
echo $user->nombre . "\n";  // Llama a __get
echo $user . "\n";          // Llama a __toString
echo $user->metodoInexistente() . "\n\n";


echo "========== 15. TRAITS ==========\n\n";

// Los traits permiten reutilizar código en múltiples clases
trait Logeable {
    public function log(string $mensaje): void {
        echo "[LOG] " . date("Y-m-d H:i:s") . " - $mensaje\n";
    }
}

trait Timestampable {
    public function getTimestamp(): string {
        return date("Y-m-d H:i:s");
    }
}

class Venta {
    use Logeable, Timestampable;

    private float $total;

    public function __construct(float $total) {
        $this->total = $total;
    }

    public function procesar(): void {
        $this->log("Venta procesada por \$$this->total");
        echo "Venta procesada a las " . $this->getTimestamp() . "\n";
    }
}

$venta = new Venta(150000);
$venta->procesar();
echo "\n";


echo "========== 16. MANEJO DE ARCHIVOS ==========\n\n";

$archivo = "test_escritura.txt";
$contenido = "Este es un archivo de prueba\nCreado con PHP\n";

// Escribir archivo
file_put_contents($archivo, $contenido);
echo "Archivo creado: $archivo\n";

// Leer archivo
$leido = file_get_contents($archivo);
echo "Contenido leído:\n$leido\n";

// Leer línea por línea
$lineas = file($archivo);  // Devuelve un array
foreach ($lineas as $numLinea => $linea) {
    echo "Línea " . ($numLinea + 1) . ": $linea";
}

// fopen/fwrite/fclose (para más control)
$handle = fopen("test_controlado.txt", "w");
fwrite($handle, "Escritura controlada\n");
fwrite($handle, "Otra línea\n");
fclose($handle);

// Verificar si existe
if (file_exists($archivo)) {
    echo "Tamaño: " . filesize($archivo) . " bytes\n";
    echo "Última modificación: " . date("Y-m-d H:i:s", filemtime($archivo)) . "\n";
}

// Eliminar archivo de prueba
unlink($archivo);
unlink("test_controlado.txt");
echo "Archivos de prueba eliminados\n\n";


echo "========== 17. MANEJO DE ERRORES (TRY/CATCH) ==========\n\n";

function dividir($a, $b) {
    if ($b == 0) {
        throw new InvalidArgumentException("No se puede dividir por cero");
    }
    return $a / $b;
}

try {
    echo "10 / 2 = " . dividir(10, 2) . "\n";
    echo "10 / 0 = " . dividir(10, 0) . "\n";  // Esto lanza excepción
} catch (InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error general: " . $e->getMessage() . "\n";
} finally {
    echo "Esto siempre se ejecuta\n\n";
}

// try/catch con PDO
try {
    $pdo = new PDO("mysql:host=localhost;dbname=erp_pos", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexión a MySQL exitosa (simulada)\n";
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
}
echo "\n";


echo "========== 18. EXPRESIONES REGULARES ==========\n\n";

$email = "usuario@example.com";
$patron = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";

if (preg_match($patron, $email)) {
    echo "Email válido: $email\n";
} else {
    echo "Email inválido\n";
}

// Validar teléfono colombiano
$telefono = "3001234567";
if (preg_match("/^3\d{9}$/", $telefono)) {
    echo "Teléfono válido: $telefono\n";
}

// Reemplazar con regex
$texto = "Mi número es 3001234567 y mi email es test@test.com";
$textoLimpio = preg_replace("/\d{10}/", "[OCULTO]", $texto);
echo "Texto limpio: $textoLimpio\n";

// Extraer con regex
preg_match_all("/\d{10}/", $texto, $coincidencias);
echo "Números encontrados: " . implode(", ", $coincidencias[0]) . "\n\n";


echo "========== 19. FECHAS Y HORAS ==========\n\n";

echo "Fecha actual: " . date("Y-m-d H:i:s") . "\n";
echo "Fecha formateada: " . date("d/m/Y") . "\n";
echo "Hora: " . date("h:i A") . "\n";

// Timestamp
echo "Timestamp actual: " . time() . "\n";

// Crear fecha desde string
$fecha = strtotime("2026-04-29");
echo "Fecha creada: " . date("d/m/Y", $fecha) . "\n";

// Sumar días
$manana = strtotime("+1 day");
echo "Mañana: " . date("Y-m-d", $manana) . "\n";

// Diferencia entre fechas
$inicio = new DateTime("2026-04-01");
$fin = new DateTime("2026-04-29");
$diferencia = $inicio->diff($fin);
echo "Días transcurridos: " . $diferencia->days . "\n";

// Formatear con DateTime
$dt = new DateTime();
echo "DateTime formateado: " . $dt->format("l, d F Y") . "\n\n";


echo "========== 20. SESIONES ==========\n\n";

// session_start() debe ir al inicio del archivo ANTES de cualquier HTML
// session_start();

// Guardar datos en sesión
// $_SESSION["usuario"] = "admin";
// $_SESSION["rol"] = "administrador";

// Leer datos de sesión
// $usuario = $_SESSION["usuario"] ?? "Invitado";
// echo "Usuario en sesión: $usuario\n";

// Destruir sesión
// session_destroy();

echo "Las sesiones se manejan con \$_SESSION\n";
echo "Siempre llamar session_start() al inicio\n\n";


echo "========== 21. JSON ==========\n\n";

$datos = [
    "empresa" => "TIRESIA SAS",
    "productos" => [
        ["codigo" => "P001", "nombre" => "Laptop", "precio" => 2500000],
        ["codigo" => "P002", "nombre" => "Mouse", "precio" => 35000]
    ],
    "total_productos" => 2
];

// Convertir a JSON
$json = json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "JSON generado:\n$json\n\n";

// Convertir de JSON a array
$desdeJson = json_decode($json, true);  // true = array asociativo
echo "Empresa desde JSON: " . $desdeJson["empresa"] . "\n";

// json_last_error
$jsonInvalido = "{nombre: 'test'}";
json_decode($jsonInvalido);
echo "Error JSON: " . json_last_error_msg() . "\n\n";


echo "========== 22. PDO (CONEXIÓN A MySQL) ==========\n\n";

// Configuración
$host = "localhost";
$dbname = "erp_pos";
$username = "root";
$password = "";

try {
    // Conectar
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    echo "Conectado a MySQL correctamente\n";

    // SELECT con consulta preparada
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE precio_venta > :precio");
    $stmt->execute([":precio" => 100000]);
    $resultados = $stmt->fetchAll();
    echo "Productos caros encontrados: " . count($resultados) . "\n";

    // INSERT
    $stmt = $pdo->prepare("
        INSERT INTO productos (codigo, nombre, precio_venta, stock) 
        VALUES (:codigo, :nombre, :precio, :stock)
    ");
    $stmt->execute([
        ":codigo" => "TEST001",
        ":nombre" => "Producto Test",
        ":precio" => 50000,
        ":stock" => 100
    ]);
    echo "Producto insertado con ID: " . $pdo->lastInsertId() . "\n";

    // UPDATE
    $stmt = $pdo->prepare("UPDATE productos SET precio_venta = :precio WHERE codigo = :codigo");
    $stmt->execute([":precio" => 55000, ":codigo" => "TEST001"]);
    echo "Filas actualizadas: " . $stmt->rowCount() . "\n";

    // DELETE
    $stmt = $pdo->prepare("DELETE FROM productos WHERE codigo = :codigo");
    $stmt->execute([":codigo" => "TEST001"]);
    echo "Filas eliminadas: " . $stmt->rowCount() . "\n";

    // Transacciones
    $pdo->beginTransaction();
    try {
        $pdo->exec("UPDATE productos SET stock = stock - 1 WHERE id = 1");
        $pdo->exec("INSERT INTO ventas (total) VALUES (1000)");
        $pdo->commit();
        echo "Transacción completada\n";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Transacción revertida: " . $e->getMessage() . "\n";
    }

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage() . "\n";
    echo "Asegúrate de que MySQL esté corriendo\n";
}

echo "\n========== FIN DE LA GUÍA ==========\n";
echo "Estudia cada sección, modifica el código, experimenta.\n";
echo "¡La práctica es la clave!\n";
