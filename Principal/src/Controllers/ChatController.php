<?php
namespace App\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\ChatHistory;
use Throwable;

class ChatController {
    
    private $chatHistoryModel;
    private $sessionId;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->sessionId = session_id();
        $this->chatHistoryModel = new ChatHistory();
    }

    public function sendMessage() {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $question = $input['pregunta'] ?? '';
            $userName = $input['usuario'] ?? 'Usuario';

            if (empty($question)) {
                echo json_encode(['status' => 'error', 'answer' => 'La pregunta está vacía.']);
                return;
            }

            $historial = $this->chatHistoryModel->getLastMessages($this->sessionId, 8);

            $payload = [
                'question' => $question,
                'historial' => $historial,
                'user_name' => $userName
            ];

            $client = new Client();
            $pythonUrl = $_ENV['PYTHON_API_URL'] ?? 'http://ia_python:8000/api/chat';
            $apiKey = $_ENV['INTERNAL_API_KEY'] ?? 'secreto_123';

            $response = $client->post($pythonUrl, [
                'json' => $payload,
                'headers' => [
                    'X-API-Key' => $apiKey,
                    'Accept' => 'application/json'
                ],
                'timeout' => 30
            ]);

            $result = json_decode($response->getBody(), true);
            $botAnswer = $result['answer'] ?? 'Respuesta vacía del asistente.';

            $this->chatHistoryModel->saveMessage($this->sessionId, $userName, 'user', $question);
            $this->chatHistoryModel->saveMessage($this->sessionId, $userName, 'assistant', $botAnswer);

            echo json_encode([
                'status' => 'success', 
                'answer' => $botAnswer
            ]);

        } catch (Throwable $e) {
            error_log("Error en ChatController: " . $e->getMessage());
            echo json_encode([
                'status' => 'error', 
                'answer' => 'Ocurrió un error procesando la solicitud: ' . $e->getMessage()
            ]);
        }
    }

    public function resetSession() {
        header('Content-Type: application/json');
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            session_regenerate_id(true);
            echo json_encode(['status' => 'success', 'message' => 'Sesión reiniciada.']);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}