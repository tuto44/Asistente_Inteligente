<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartSupport - Asistente TI</title>
    <link rel="stylesheet" href="/css/style.css">
    <!-- Fuente moderna desde Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="chat-container">
        <!-- Encabezado -->
        <header class="chat-header">
            <div class="status-dot"></div>
            <h1>SmartSupport IA</h1>
            <span class="subtitle">Asistente de TI Nivel 1</span>
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
        <form id="chat-form" class="chat-input-area">
            <input 
                type="text" 
                id="user-input" 
                placeholder="Escribe tu consulta aquí..." 
                autocomplete="off"
                required
            >
            <button type="submit" id="send-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </form>
    </div>

    <script src="/js/chat.js"></script>
</body>
</html>