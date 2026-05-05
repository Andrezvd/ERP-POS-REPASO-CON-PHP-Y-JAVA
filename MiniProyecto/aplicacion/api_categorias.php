<?php
/**
 * API REST DE CATEGORÍAS
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../infraestructura/repos/CategoriaRepository.php';

$repo = new CategoriaRepository();
$method = $_SERVER['REQUEST_METHOD'];
$format = $_GET['format'] ?? 'json';
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

try {
    switch ($method) {
        case 'GET':
            $result = isset($_GET['id']) ? $repo->getById((int)$_GET['id']) : $repo->getAll();
            if (!$result && isset($_GET['id'])) { http_response_code(404); $result = ['error' => 'No encontrado']; }
            respond($result, $format);
            break;
        case 'POST':
            if (empty($input['nombre'])) { http_response_code(400); respond(['error' => 'nombre requerido'], $format); break; }
            $id = $repo->create($input);
            http_response_code(201);
            respond(['success' => true, 'id' => $id], $format);
            break;
        case 'PUT':
            if (!isset($_GET['id'])) { http_response_code(400); respond(['error' => 'id requerido'], $format); break; }
            $repo->update((int)$_GET['id'], $input);
            respond(['success' => true], $format);
            break;
        case 'DELETE':
            if (!isset($_GET['id'])) { http_response_code(400); respond(['error' => 'id requerido'], $format); break; }
            $repo->delete((int)$_GET['id']);
            respond(['success' => true], $format);
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
        if (is_array($value)) { $child = $xml->addChild(is_numeric($key) ? 'item' : $key); arrayToXml($value, $child); }
        else { $xml->addChild(is_numeric($key) ? 'item' : $key, htmlspecialchars((string)$value)); }
    }
    return $xml;
}

?>