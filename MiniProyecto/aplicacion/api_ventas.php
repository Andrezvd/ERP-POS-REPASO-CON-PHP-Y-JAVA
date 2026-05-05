<?php
/**
 * API REST DE VENTAS
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../infraestructura/repos/VentaRepository.php';

$repo = new VentaRepository();
$method = $_SERVER['REQUEST_METHOD'];
$format = $_GET['format'] ?? 'json';
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $venta = $repo->getById((int)$_GET['id']);
                if ($venta) {
                    $venta['detalle'] = $repo->getDetalle((int)$_GET['id']);
                    $venta['pagos'] = $repo->getPagos((int)$_GET['id']);
                }
                $result = $venta ?: ['error' => 'Venta no encontrada'];
                if (!$venta) http_response_code(404);
            } elseif (isset($_GET['dashboard'])) {
                $result = [
                    'resumen' => $repo->getDashboard(),
                    'ventas_hoy' => $repo->getVentasDelDia(),
                    'top_productos' => $repo->getTopProductos()
                ];
            } elseif (isset($_GET['resumen_diario'])) {
                $result = $repo->getResumenDiario() ?: ['message' => 'No hay ventas hoy'];
            } else {
                $result = $repo->getAll();
            }
            respond($result, $format);
            break;
            
        case 'POST':
            if (empty($input['productos']) || !is_array($input['productos'])) {
                http_response_code(400);
                respond(['error' => 'Se requiere el array de productos'], $format);
                break;
            }
            $result = $repo->registrarVenta($input);
            if (!$result['success']) {
                http_response_code(400);
            } else {
                http_response_code(201);
            }
            respond($result, $format);
            break;
            
        case 'PUT':
            if (!isset($_GET['id']) || !isset($_GET['action'])) {
                http_response_code(400);
                respond(['error' => 'Se requiere id y action'], $format);
                break;
            }
            if ($_GET['action'] === 'anular') {
                $result = $repo->anular((int)$_GET['id']);
                respond($result, $format);
            } else {
                http_response_code(400);
                respond(['error' => 'Acción no válida'], $format);
            }
            break;
            
        default:
            http_response_code(405);
            respond(['error' => 'Método no permitido'], $format);
    }
} catch (Exception $e) {
    http_response_code(500);
    respond(['error' => $e->getMessage()], $format);
}

function respond($data, $format) {
    if ($format === 'xml') {
        header('Content-Type: application/xml; charset=utf-8');
        $xml = new SimpleXMLElement('<response/>');
        arrayToXml($data, $xml);
        echo $xml->asXML();
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    exit;
}

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
