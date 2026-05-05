<?php
/**
 * API REST DE PRODUCTOS
 * Soporta JSON y XML
 * Endpoints:
 *   GET    /api_productos.php           - Listar todos
 *   GET    /api_productos.php?id=1      - Obtener uno
 *   GET    /api_productos.php?search=   - Buscar
 *   GET    /api_productos.php?stock_bajo - Stock bajo
 *   POST   /api_productos.php           - Crear
 *   PUT    /api_productos.php?id=1      - Actualizar
 *   DELETE /api_productos.php?id=1      - Eliminar
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../infraestructura/repos/ProductoRepository.php';

$repo = new ProductoRepository();
$method = $_SERVER['REQUEST_METHOD'];
$format = $_GET['format'] ?? 'json';

// Obtener el body de la petición
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $result = $repo->getById((int)$_GET['id']);
                if (!$result) {
                    http_response_code(404);
                    $result = ['error' => 'Producto no encontrado'];
                }
            } elseif (isset($_GET['search'])) {
                $result = $repo->search($_GET['search']);
            } elseif (isset($_GET['stock_bajo'])) {
                $result = $repo->getStockBajo();
            } elseif (isset($_GET['codigo'])) {
                $result = $repo->getByCodigo($_GET['codigo']);
            } else {
                $result = $repo->getAll();
            }
            respond($result, $format);
            break;
            
        case 'POST':
            if (empty($input['codigo']) || empty($input['nombre']) || !isset($input['precio_venta'])) {
                http_response_code(400);
                respond(['error' => 'Faltan campos requeridos: codigo, nombre, precio_venta'], $format);
                break;
            }
            $id = $repo->create($input);
            http_response_code(201);
            respond([
                'success' => true,
                'id' => $id,
                'message' => 'Producto creado exitosamente'
            ], $format);
            break;
            
        case 'PUT':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                respond(['error' => 'Se requiere el parámetro id'], $format);
                break;
            }
            $affected = $repo->update((int)$_GET['id'], $input);
            respond([
                'success' => true,
                'affected' => $affected,
                'message' => 'Producto actualizado exitosamente'
            ], $format);
            break;
            
        case 'DELETE':
            if (!isset($_GET['id'])) {
                http_response_code(400);
                respond(['error' => 'Se requiere el parámetro id'], $format);
                break;
            }
            $affected = $repo->delete((int)$_GET['id']);
            respond([
                'success' => true,
                'affected' => $affected,
                'message' => 'Producto eliminado exitosamente'
            ], $format);
            break;
            
        default:
            http_response_code(405);
            respond(['error' => 'Método no permitido'], $format);
    }
} catch (Exception $e) {
    http_response_code(500);
    respond(['error' => $e->getMessage()], $format);
}

/**
 * Responde en el formato solicitado (JSON o XML)
 */
function respond($data, $format) {
    if ($format === 'xml') {
        header('Content-Type: application/xml; charset=utf-8');
        echo arrayToXml($data, new SimpleXMLElement('<response/>'))->asXML();
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    exit;
}

/**
 * Convierte un array a XML
 */
function arrayToXml($data, SimpleXMLElement $xml) {
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $child = $xml->addChild(is_numeric($key) ? 'item' : $key);
            arrayToXml($value, $child);
        } else {
            $xml->addChild(is_numeric($key) ? 'item' : $key, htmlspecialchars((string)$value));
        }
    }
    return $xml;
}
