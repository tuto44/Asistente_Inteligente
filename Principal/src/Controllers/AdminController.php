<?php
namespace App\Controllers;

use GuzzleHttp\Client;
use Throwable;

class AdminController {
    
    public function index() {
        require __DIR__ . '/../Views/admin.php';
    }

    public function upload() {
        header('Content-Type: application/json');
        try {
            if (!isset($_FILES['documento']) || $_FILES['documento']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception("Por favor selecciona un archivo válido.");
            }

            $file = $_FILES['documento'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'md' && $ext !== 'txt') {
                throw new \Exception("Formato no soportado. Sube un archivo .md o .txt");
            }

            // Usamos ia_python y quitamos /chat para enviar a /upload-and-index
            $rawUrl = $_ENV['PYTHON_API_URL'] ?? 'http://ia_python:8000/api/chat';
            $baseUrl = str_replace('/chat', '', $rawUrl);
            $uploadUrl = rtrim($baseUrl, '/') . '/upload-and-index';

            $apiKey = $_ENV['INTERNAL_API_KEY'] ?? 'secreto_123';
            $client = new Client();
            
            $response = $client->post($uploadUrl, [
                'headers' => [
                    'X-API-Key' => $apiKey,
                    'Accept' => 'application/json'
                ],
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($file['tmp_name'], 'r'),
                        'filename' => $file['name']
                    ]
                ],
                'timeout' => 60
            ]);

            echo $response->getBody();

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getDocuments() {
        header('Content-Type: application/json');
        try {
            $rawUrl = $_ENV['PYTHON_API_URL'] ?? 'http://ia_python:8000/api/chat';
            $baseUrl = str_replace('/chat', '', $rawUrl);
            $apiKey = $_ENV['INTERNAL_API_KEY'] ?? 'secreto_123';

            $client = new Client();
            $response = $client->get(rtrim($baseUrl, '/') . '/documents', [
                'headers' => ['X-API-Key' => $apiKey]
            ]);
            
            echo $response->getBody();
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteDocument() {
        header('Content-Type: application/json');
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $filename = $input['filename'] ?? '';
            
            if (empty($filename)) {
                throw new \Exception("Nombre de archivo inválido.");
            }

            $rawUrl = $_ENV['PYTHON_API_URL'] ?? 'http://ia_python:8000/api/chat';
            $baseUrl = str_replace('/chat', '', $rawUrl);
            $apiKey = $_ENV['INTERNAL_API_KEY'] ?? 'secreto_123';

            $client = new Client();
            $response = $client->delete(rtrim($baseUrl, '/') . '/documents/' . rawurlencode($filename), [
                'headers' => ['X-API-Key' => $apiKey]
            ]);
            
            echo $response->getBody();
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}