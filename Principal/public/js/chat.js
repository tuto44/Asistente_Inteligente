document.addEventListener('DOMContentLoaded', () => {
    const chatForm = document.getElementById('chat-form');
    const userInput = document.getElementById('user-input');
    const chatMessages = document.getElementById('chat-messages');
    let messageCounter = 0; // Contador para evitar IDs duplicados

    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const messageText = userInput.value.trim();
        if (!messageText) return;

        // 1. Mostrar mensaje del usuario en pantalla
        appendMessage('user', messageText);
        userInput.value = '';

        // 2. Mostrar indicador de "Escribiendo..."
        const loadingId = appendMessage('bot', 'Pensando respuesta...', true);

        try {
            const response = await fetch('/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    pregunta: messageText,
                    usuario: 'Usuario'
                })
            });

            const data = await response.json();

            // 3. Eliminar únicamente el indicador de carga
            removeMessage(loadingId);

            if (data.status === 'success') {
                appendMessage('bot', data.answer);
            } else {
                appendMessage('bot', '⚠️ ' + (data.message || data.answer || 'Ocurrió un error.'));
            }

        } catch (error) {
            console.error('Error al comunicarse con el servidor:', error);
            removeMessage(loadingId);
            appendMessage('bot', '❌ Error de conexión con el servidor PHP.');
        }
    });

    function appendMessage(sender, text, isTyping = false) {
        const messageDiv = document.createElement('div');
        
        // ID único garantizado (Timestamp + Contador)
        messageCounter++;
        const messageId = `msg-${Date.now()}-${messageCounter}`;
        
        messageDiv.id = messageId;
        messageDiv.classList.add('message', sender === 'user' ? 'user-message' : 'bot-message');

        const contentDiv = document.createElement('div');
        contentDiv.classList.add('message-content');
        if (isTyping) contentDiv.classList.add('typing');
        contentDiv.textContent = text;

        messageDiv.appendChild(contentDiv);
        chatMessages.appendChild(messageDiv);

        chatMessages.scrollTop = chatMessages.scrollHeight;

        return messageId;
    }

    function removeMessage(id) {
        const element = document.getElementById(id);
        if (element) {
            element.remove();
        }
    }
});