<?php
namespace App\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\ChatHistory;

class ChatController {
    
    private $chatHistoryModel;
    private $sessionId;

    public function __construct() {
        // Iniciamos sesión solo para generar un ID único temporal por usuario
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->sessionId = session_id();
        $this->chatHistoryModel = new ChatHistory();
    }

    public function sendMessage() {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $question = $input['pregunta'] ?? '';
        $userName = $input['usuario'] ?? 'Usuario';

        if (empty($question)) {
            echo json_encode(['status' => 'error', 'message' => 'La pregunta está vacía.']);
            return;
        }

        // 1. Obtener el historial DESDE LA BASE DE DATOS
        $historial = $this->chatHistoryModel->getLastMessages($this->sessionId, 8);

        // 2. Preparar el payload para enviar a Python
        $payload = [
            'question' => $question,
            'historial' => $historial,
            'user_name' => $userName
        ];

        try {
            // Enviar petición a Python
            $client = new Client();
            $pythonUrl = $_ENV['PYTHON_API_URL'];
            $apiKey = $_ENV['INTERNAL_API_KEY'];

            $response = $client->post($pythonUrl, [
                'json' => $payload,
                'headers' => [
                    'X-API-Key' => $apiKey,
                    'Accept' => 'application/json'
                ],
                'timeout' => 30
            ]);

            $result = json_decode($response->getBody(), true);
            $botAnswer = $result['answer'] ?? 'Respuesta vacía de IA';

            // 3. GUARDAR INTERACCIÓN EN LA BASE DE DATOS
            $this->chatHistoryModel->saveMessage($this->sessionId, $userName, 'user', $question);
            $this->chatHistoryModel->saveMessage($this->sessionId, $userName, 'assistant', $botAnswer);

            echo json_encode([
                'status' => 'success', 
                'answer' => $botAnswer
            ]);

        } catch (RequestException $e) {
            error_log("Fallo al conectar con Python: " . $e->getMessage());
            echo json_encode([
                'status' => 'error', 
                'answer' => 'El asistente no está disponible en este momento.'
            ]);
        }
    }


}