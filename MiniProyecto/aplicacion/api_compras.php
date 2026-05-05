<?php
/**
 * API REST DE COMPRAS
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../infraestructura/repos/CompraRepository.php';

$repo = new CompraRepository();
$method = $_SERVER['REQUEST_METHOD'];
$format = $_GET['format'] ?? 'json';
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $compra = $repo->getById((int)$_GET['id']);
                if ($compra) {
                    $compra['detalle'] = $repo->getDetalle((int)$_GET['id']);
                }
                $result = $compra ?: ['error' => 'Compra no encontrada'];
                if (!$compra) http_response_code(404);
            } else {
                $result = $repo->getAll();
            }
            respond($result, $format);
            break;
        case 'POST':
            if (empty($input['proveedor_id']) || empty($input['productos'])) {
                http_response_code(400);
                respond(['error' => 'Faltan campos: proveedor_id, productos'], $format);
                break;
            }
            $result = $repo->registrarCompra($input);
            if (!$result['success']) http_response_code(400);
            else http_response_code(201);
            respond($result, $format);
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
