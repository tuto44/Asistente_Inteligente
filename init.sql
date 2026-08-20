CREATE DATABASE IF NOT EXISTS smartsupport;
USE smartsupport;

CREATE TABLE IF NOT EXISTS chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100) NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    role ENUM('user', 'assistant') NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);