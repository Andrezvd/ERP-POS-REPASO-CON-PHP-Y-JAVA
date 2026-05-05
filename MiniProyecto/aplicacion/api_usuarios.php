<?php
/**
 * API REST DE USUARIOS Y AUTENTICACIÓN
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../infraestructura/repos/UsuarioRepository.php';

$repo = new UsuarioRepository();
$method = $_SERVER['REQUEST_METHOD'];
$format = $_GET['format'] ?? 'json';
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $result = $repo->getById((int)$_GET['id']);
                if (!$result) { http_response_code(404); $result = ['error' => 'Usuario no encontrado']; }
            } else {
                $result = $repo->getAll();
            }
            respond($result, $format);
            break;
            
        case 'POST':
            if (isset($_GET['action']) && $_GET['action'] === 'login') {
                if (empty($input['usuario']) || empty($input['password'])) {
                    http_response_code(400);
                    respond(['error' => 'usuario y password requeridos'], $format);
                    break;
                }
                $user = $repo->login($input['usuario'], $input['password']);
                if ($user) {
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nombre'] = $user['nombre'];
                    $_SESSION['user_usuario'] = $user['usuario'];
                    $_SESSION['user_rol'] = $user['rol_nombre'];
                    respond(['success' => true, 'user' => [
                        'id' => $user['id'],
                        'nombre' => $user['nombre'],
                        'usuario' => $user['usuario'],
                        'rol' => $user['rol_nombre']
                    ]], $format);
                } else {
                    http_response_code(401);
                    respond(['error' => 'Credenciales inválidas'], $format);
                }
            } else {
                if (empty($input['nombre']) || empty($input['usuario']) || empty($input['password'])) {
                    http_response_code(400);
                    respond(['error' => 'nombre, usuario y password requeridos'], $format);
                    break;
                }
                $id = $repo->create($input);
                http_response_code(201);
                respond(['success' => true, 'id' => $id], $format);
            }
            break;
            
        case 'PUT':
            if (!isset($_GET['id'])) { http_response_code(400); respond(['error' => 'id requerido'], $format); break; }
            $repo->update((int)$_GET['id'], $input);
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
