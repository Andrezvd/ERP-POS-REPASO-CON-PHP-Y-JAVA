<?php
namespace Infraestructura\Db;

use PDO;
use PDOException;

class ConexionDB {

    private static $instancia = null;
    private $conexion;

    // Datos de conexión - CAMBIA ESTOS VALORES SEGÚN TU CONFIGURACIÓN
    private $host = 'localhost';
    private $puerto = '3307';
    private $base_datos = 'erp_pos';
    private $usuario = 'root';
    private $password = 'root';

    // Constructor privado (patrón Singleton)
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->puerto};dbname={$this->base_datos};charset=utf8mb4";
            
            $this->conexion = new PDO($dsn, $this->usuario, $this->password);
            
            // Configurar PDO para que lance excepciones en errores
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Configurar PDO para que devuelva resultados como arrays asociativos
            $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    // Método estático para obtener la instancia única
    public static function obtenerInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new ConexionDB();
        }
        return self::$instancia;
    }

    // Método para obtener la conexión PDO
    public function getConexion() {
        return $this->conexion;
    }

    // Evitar que se clone el objeto
    private function __clone() {}
}
