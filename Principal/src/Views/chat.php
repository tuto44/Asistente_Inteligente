<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartSupport - Asistente TI</title>
    <link rel="stylesheet" href="/css/style.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="chat-container">
        <!-- Encabezado con botón de reset -->
        <header class="chat-header">
            <div class="header-info">
                <div class="status-dot"></div>
                <div>
                    <h1>SmartSupport IA</h1>
                    <span class="subtitle">Asistente de TI Nivel 1</span>
                </div>
            </div>
            <button id="reset-btn" type="button" class="reset-btn" title="Nueva consulta">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                    <path d="M3 3v5h5"></path>
                </svg>
            </button>
        </header>

        <!-- Caja de Mensajes -->
        <div id="chat-messages" class="chat-messages">
            <div class="message bot-message">
                <div class="message-content">
                    ¡Hola! 👋 Soy tu asistente de Soporte TI. ¿En qué te puedo ayudar hoy? (Citrix, VPN, Contraseñas, etc.)
                </div>
            </div>
        </div>

        <!-- Formulario de Envío -->
<form id="chat-form" class="chat-input-container">
    <input type="text" id="user-input" placeholder="Escribe tu consulta aquí..." autocomplete="off" required>
    <button type="submit" class="send-btn" id="send-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
    </button>
</form>
    </div>

    <script src="/js/chat.js?v=<?= time() ?>"></script>
</body>
</html>