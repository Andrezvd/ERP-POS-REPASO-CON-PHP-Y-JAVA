<?php
namespace Api;

// 1. Importar todo lo necesario
require_once __DIR__ . '/../dominio/Categoria.php';
require_once __DIR__ . '/../dominio/repositorios/CategoriaRepositorio.php';
require_once __DIR__ . '/../infraestructura/reposql/CategoriaRepositorio.php';
require_once __DIR__ . '/../aplicacion/CategoriaServicio.php';

use Infraestructura\Reposql\CategoriaRepositorioSQL;
use Aplicacion\CategoriaServicio;

// 2. Configurar cabeceras para que devuelva JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 3. Si es una petición OPTIONS (preflight), terminamos aquí
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

// 4. Crear el servicio (que usa el repositorio, que usa la BD)
$repositorio = new CategoriaRepositorioSQL();
$servicio = new CategoriaServicio($repositorio);

// 5. Obtener el método HTTP
$metodo = $_SERVER['REQUEST_METHOD'];

// 6. Obtener el ID si viene en la URL (ej: /api/categorias.php?id=5)
$id = isset($_GET['id']) ? $_GET['id'] : null;

try {
    switch ($metodo) {
        case 'GET':
            if ($id) {
                // GET /api/categorias.php?id=5  →  Obtener una categoría
                $categoria = $servicio->obtenerCategoriaPorId($id);
                if ($categoria) {
                    echo json_encode([
                        'success' => true,
                        'data' => $categoria->toArray()
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Categoría no encontrada'
                    ]);
                }
            } else {
                // GET /api/categorias.php  →  Obtener todas las categorías
                $categorias = $servicio->obtenerCategorias();
                $data = array_map(function($cat) {
                    return $cat->toArray();
                }, $categorias);
                echo json_encode([
                    'success' => true,
                    'data' => $data
                ]);
            }
            break;

        case 'POST':
            // POST /api/categorias.php  →  Crear una categoría
            // Los datos vienen en el cuerpo de la petición en formato JSON
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || !isset($input['nombre'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'El campo nombre es obligatorio'
                ]);
                break;
            }

            $nuevoId = $servicio->crearCategoria($input);
            echo json_encode([
                'success' => true,
                'message' => 'Categoría creada',
                'id' => $nuevoId
            ]);
            break;

        case 'PUT':
            // PUT /api/categorias.php?id=5  →  Actualizar una categoría
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Se requiere el ID de la categoría'
                ]);
                break;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $input['id'] = $id;
            $actualizado = $servicio->actualizarCategoria($input);
            
            if ($actualizado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría actualizada'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ]);
            }
            break;

        case 'DELETE':
            // DELETE /api/categorias.php?id=5  →  Eliminar una categoría
            if (!$id) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Se requiere el ID de la categoría'
                ]);
                break;
            }

            $eliminado = $servicio->eliminarCategoria($id);
            if ($eliminado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría eliminada'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            break;
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
}
