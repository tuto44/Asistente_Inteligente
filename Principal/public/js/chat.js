document.addEventListener('DOMContentLoaded', () => {
    const chatForm = document.getElementById('chat-form');
    const userInput = document.getElementById('user-input');
    const chatMessages = document.getElementById('chat-messages');
    const resetBtn = document.getElementById('reset-btn');
    let messageCounter = 0;

    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const messageText = userInput.value.trim();
        if (!messageText) return;

        appendMessage('user', messageText);
        userInput.value = '';

        const loadingId = appendMessage('bot', 'Pensando respuesta...', true);

        try {
            const response = await fetch('/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    pregunta: messageText,
                    usuario: 'Usuario'
                })
            });

            const data = await response.json();
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

    // Evento de reinicio de conversación
    if (resetBtn) {
        resetBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const res = await fetch('/reset', { method: 'POST' });
                if (res.ok) {
                    chatMessages.innerHTML = '';
                    appendMessage('bot', '¡Hola! 👋 Soy tu asistente de Soporte TI. ¿En qué te puedo ayudar hoy? (Citrix, VPN, Contraseñas, etc.)');
                }
            } catch (error) {
                console.error('Error al reiniciar sesión:', error);
            }
        });
    }

    function formatText(text) {
        if (!text) return '';

        let safeText = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // 1. Convertir negritas Markdown **texto**
        safeText = safeText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // 2. Convertir enlaces Markdown [Texto] (URL) o [Texto](URL)
        safeText = safeText.replace(/\[([^\]]+)\]\s*\((https?:\/\/[^\s\)]+)\)/gi, (match, label, url) => {
            let cleanUrl = url.replace(/[\.\,;]+$/, '');
            let trailing = url.slice(cleanUrl.length);
            return `<a href="${cleanUrl}" target="_blank" rel="noopener noreferrer">${label}</a>${trailing}`;
        });

        // 3. Convertir URLs sueltas en texto plano
        safeText = safeText.replace(/(^|[\s\(])(https?:\/\/[^\s\)\<]+)/gi, (match, space, url) => {
            if (match.includes('href=')) return match;
            let cleanUrl = url.replace(/[\.\,;]+$/, '');
            let trailing = url.slice(cleanUrl.length);
            return `${space}<a href="${cleanUrl}" target="_blank" rel="noopener noreferrer">${cleanUrl}</a>${trailing}`;
        });

        return safeText;
    }

    function appendMessage(sender, text, isTyping = false) {
        const messageDiv = document.createElement('div');
        messageCounter++;
        const messageId = `msg-${Date.now()}-${messageCounter}`;

        messageDiv.id = messageId;
        messageDiv.classList.add('message', sender === 'user' ? 'user-message' : 'bot-message');

        const contentDiv = document.createElement('div');
        contentDiv.classList.add('message-content');

        if (isTyping) {
            contentDiv.classList.add('typing');
            contentDiv.textContent = text;
        } else {
            contentDiv.innerHTML = formatText(text);
        }

        messageDiv.appendChild(contentDiv);
        chatMessages.appendChild(messageDiv);

        chatMessages.scrollTo({
            top: chatMessages.scrollHeight,
            behavior: 'smooth'
        });

        return messageId;
    }

    function removeMessage(id) {
        const element = document.getElementById(id);
        if (element) {
            element.remove();
        }
    }
});