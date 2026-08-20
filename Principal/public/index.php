<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\ChatController;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && $uri === '/') {
    require __DIR__ . '/../src/Views/chat.php';
} 
elseif ($method === 'POST' && $uri === '/chat') {
    $controller = new ChatController();
    $controller->sendMessage();
} 
elseif ($method === 'POST' && $uri === '/reset') {
    $controller = new ChatController();
    $controller->resetSession();
} 
else {
    http_response_code(404);
    echo json_encode(["error" => "Ruta no encontrada"]);
}