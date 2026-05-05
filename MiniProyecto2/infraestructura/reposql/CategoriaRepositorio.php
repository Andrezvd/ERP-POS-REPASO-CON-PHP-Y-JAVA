<?php
namespace Infraestructura\Reposql;

require_once __DIR__ . '/../db/conectiondb.php';
require_once __DIR__ . '/../../dominio/repositorios/CategoriaRepositorio.php';
require_once __DIR__ . '/../../dominio/Categoria.php';

use Dominio\Repositorios\CategoriaRepositorio;
use Infraestructura\Db\ConexionDB;
use Dominio\Categoria;
use PDOException;
use Exception;

    class CategoriaRepositorioSQL implements CategoriaRepositorio {

        private $conexion;

        public function __construct() {
            $this->conexion = ConexionDB::obtenerInstancia();
        }

        public function obtenerCategorias(): array {
            $sql = "SELECT * FROM categorias";
            try {
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $stmt->execute();
                $categorias = [];
                while ($row = $stmt->fetch()) {
                    $categoria = new Categoria(
                        $row['id'],
                        $row['nombre'],
                        $row['descripcion'],
                        $row['activo'],
                        $row['created_at'],
                        $row['updated_at']
                    );
                    $categorias[] = $categoria;
                }
                return $categorias;
            } catch (PDOException $e) {
                throw new Exception("Error al obtener categorías: " . $e->getMessage());
            }

        }

        public function obtenerCategoriaPorId($id): ?Categoria {
            $sql = "SELECT * FROM categorias WHERE id = :id";
            try {
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
                $stmt->execute();
                $row = $stmt->fetch();
                
                if ($row) {
                    return new Categoria(
                        $row['id'],
                        $row['nombre'],
                        $row['descripcion'],
                        $row['activo'],
                        $row['created_at'],
                        $row['updated_at']
                    );
                }
                return null;
            } catch (PDOException $e) {
                throw new Exception("Error al obtener categoría: " . $e->getMessage());
            }
        }

        public function crearCategoria($categoria): int {
            $sql = "INSERT INTO categorias (nombre, descripcion, activo) VALUES (:nombre, :descripcion, :activo)";
            try {
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $stmt->bindParam(':nombre', $categoria['nombre'], \PDO::PARAM_STR);
                $stmt->bindParam(':descripcion', $categoria['descripcion'], \PDO::PARAM_STR);
                $activo = isset($categoria['activo']) ? $categoria['activo'] : true;
                $stmt->bindParam(':activo', $activo, \PDO::PARAM_BOOL);
                $stmt->execute();
                return $this->conexion->getConexion()->lastInsertId();
            } catch (PDOException $e) {
                throw new Exception("Error al crear categoría: " . $e->getMessage());
            }
        }

        public function actualizarCategoria($categoria): bool {
            $sql = "UPDATE categorias SET nombre = :nombre, descripcion = :descripcion, activo = :activo WHERE id = :id";
            try {
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $stmt->bindParam(':nombre', $categoria['nombre'], \PDO::PARAM_STR);
                $stmt->bindParam(':descripcion', $categoria['descripcion'], \PDO::PARAM_STR);
                $stmt->bindParam(':activo', $categoria['activo'], \PDO::PARAM_BOOL);
                $stmt->bindParam(':id', $categoria['id'], \PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                throw new Exception("Error al actualizar categoría: " . $e->getMessage());
            }
        }

        public function eliminarCategoria($id): bool {
            $sql = "DELETE FROM categorias WHERE id = :id";
            try {
                $stmt = $this->conexion->getConexion()->prepare($sql);
                $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                throw new Exception("Error al eliminar categoría: " . $e->getMessage());
            }
        }
    }
