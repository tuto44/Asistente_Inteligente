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
        <!-- Encabezado -->
        <header class="chat-header">
            <div class="header-info">
                <div class="chat-header-title">
            <svg
                class="brand-icon" viewBox="0 0 64 64"fill="none" xmlns="http://www.w3.org/2000/svg" >
                <rect x="8" y="4" width="48" height="56" rx="9" class="logo-bg"/>
                <line x1="8" y1="19" x2="56" y2="19" class="logo-divider"/>
                <rect x="13" y="8" width="11" height="7" rx="2" class="logo-panel"/>
                <rect x="27" y="10" width="9" height="4" rx="1" class="logo-tech"/>
                <circle cx="46" cy="11.5" r="3.5" class="logo-button"/>
                <line x1="46" y1="11.5" x2="46" y2="9" class="logo-knob-mark"/>
                <circle cx="32" cy="40" r="14" class="logo-drum-border"/>
                <circle cx="32" cy="40" r="10.5" class="logo-drum"/>
                <path d="M 24 35 A 9 9 0 0 1 38 34" class="logo-shine"/>
                <path d="M 43 37 C 44.5 39 44.5 41 43 43" class="logo-handle"/>
            </svg>
                    <div class="brand-text">
                        <h2>SmartSupport <span>IA</span></h2>
                        <div class="brand-subtitle">
                            <span class="subtitle-line"></span>
                            <small>Asistente de TI Nivel 1</small>
                        </div>
                    </div>
                </div>
            </div>

            <button id="reset-btn" type="button" class="reset-btn" title="Nueva consulta" aria-label="Nueva consulta">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                    <path d="M3 3v5h5"></path>
                </svg>
            </button>
        </header>

        <main class="chat-main">
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
                <input type="text" id="user-input" placeholder="Escribe tu consulta aquí..." autocomplete="off" aria-label="Escribe tu consulta" required>
                <button type="submit" class="send-btn" id="send-btn" aria-label="Enviar mensaje">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </form>
        </main>
    </div>

    <script src="/js/chat.js?v=<?= time() ?>"></script>
</body>
</html>