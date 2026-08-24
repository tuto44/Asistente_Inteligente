<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\ChatController;
use App\Controllers\AdminController;

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
elseif ($method === 'GET' && $uri === '/admin') {
    $controller = new AdminController();
    $controller->index();
}
elseif ($method === 'POST' && $uri === '/admin/upload') {
    $controller = new AdminController();
    $controller->upload();
}
elseif ($method === 'GET' && $uri === '/admin/documents') {
    $controller = new AdminController();
    $controller->getDocuments();
}
elseif ($method === 'POST' && $uri === '/admin/delete') {
    $controller = new AdminController();
    $controller->deleteDocument();
}
else {
    http_response_code(404);
    echo json_encode(["error" => "Ruta no encontrada"]);
}