<?php
// Router para el servidor PHP built-in
// Permite servir archivos PHP desde subdirectorios

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Si el archivo existe, sírvelo
$filePath = __DIR__ . $path;
if (file_exists($filePath) && !is_dir($filePath)) {
    // Si es un archivo PHP, ejecútalo
    if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php') {
        require $filePath;
        return true;
    }
    // Si es un archivo estático, devuélvelo
    return false;
}

// Si no se encontró, devolver 404
http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Not Found']);
return true;
