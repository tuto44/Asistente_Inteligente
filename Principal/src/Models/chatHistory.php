<?php
namespace App\Models;

use PDO;

class ChatHistory {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Guarda un mensaje individual en la BD
    public function saveMessage($sessionId, $userName, $role, $content) {
        $sql = "INSERT INTO chat_history (session_id, user_name, role, content) 
                VALUES (:session_id, :user_name, :role, :content)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':session_id' => $sessionId,
            ':user_name'  => $userName,
            ':role'       => $role,
            ':content'    => $content
        ]);
    }


    public function getLastMessages($sessionId, $limit = 8) {
        
        $sql = "SELECT role, content FROM (
                    SELECT id, role, content 
                    FROM chat_history 
                    WHERE session_id = :session_id 
                    AND created_at >= NOW() - INTERVAL 4 HOUR
                    ORDER BY id DESC 
                    LIMIT :limit
                ) sub 
                ORDER BY id ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':session_id', $sessionId, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}