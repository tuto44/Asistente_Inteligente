<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\ChatController;

// 1. Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// 2. Enrutador básico nativo
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Ruta GET: Mostrar la interfaz web
if ($method === 'GET' && $uri === '/') {
    require __DIR__ . '/../src/Views/chat.php';
} 
// Ruta POST: Enviar mensaje a la IA
elseif ($method === 'POST' && $uri === '/chat') {
    $controller = new ChatController();
    $controller->sendMessage();
} 
// Ruta 404
else {
    http_response_code(404);
    echo json_encode(["error" => "Ruta no encontrada"]);
}