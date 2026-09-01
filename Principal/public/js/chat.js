document.addEventListener('DOMContentLoaded', () => {
    const chatForm = document.getElementById('chat-form');
    const userInput = document.getElementById('user-input');
    const chatMessages = document.getElementById('chat-messages');
    const resetBtn = document.getElementById('reset-btn');
    const imageInput = document.getElementById('image-input');
    const previewContainer = document.getElementById('image-preview-container');
    const previewName = document.getElementById('image-preview-name');
    const removeImageBtn = document.getElementById('remove-image-btn');

    let selectedBase64Image = null;
    let messageCounter = 0;

    // 1. Cargar imagen desde explorador de archivos
    if (imageInput) {
        imageInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (evt) => {
                    selectedBase64Image = evt.target.result.split(',')[1];
                    if (previewName) previewName.textContent = `${file.name}`;
                    if (previewContainer) previewContainer.style.display = 'flex';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // 2. Pegar imagen desde el portapapeles (Ctrl + V / Cmd + V)
    document.addEventListener('paste', (e) => {
        const items = (e.clipboardData || e.originalEvent?.clipboardData)?.items;
        if (!items) return;

        for (let item of items) {
            if (item.type.indexOf('image') !== -1) {
                const file = item.getAsFile();
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (evt) => {
                        selectedBase64Image = evt.target.result.split(',')[1];
                        if (previewName) previewName.textContent = `Captura pegada (portapapeles)`;
                        if (previewContainer) previewContainer.style.display = 'flex';
                    };
                    reader.readAsDataURL(file);
                }
                break;
            }
        }
    });

    // 3. Quitar imagen seleccionada/pegada
    if (removeImageBtn) {
        removeImageBtn.addEventListener('click', () => {
            selectedBase64Image = null;
            if (imageInput) imageInput.value = '';
            if (previewContainer) previewContainer.style.display = 'none';
        });
    }

    // 4. Envío del formulario
    chatForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const messageText = userInput.value.trim();
        if (!messageText && !selectedBase64Image) return;

        const textToDisplay = messageText || '[Captura de pantalla enviada]';
        const messageId = appendMessage('user', textToDisplay);

        if (selectedBase64Image) {
            const msgEl = document.getElementById(messageId);
            if (msgEl) {
                const contentDiv = msgEl.querySelector('.message-content');
                const imgBadge = document.createElement('div');
                imgBadge.style.cssText = 'margin-top: 4px; opacity: 0.8; font-size: 0.8em;';
                imgBadge.innerHTML = '<em>[Captura de pantalla adjunta]</em>';
                contentDiv.appendChild(imgBadge);
            }
        }

        const payloadData = {
            pregunta: messageText,
            usuario: 'Usuario',
            image_base64: selectedBase64Image
        };

        userInput.value = '';
        selectedBase64Image = null;
        if (imageInput) imageInput.value = '';
        if (previewContainer) previewContainer.style.display = 'none';

        const loadingId = appendMessage('bot', 'Pensando respuesta...', true);

        try {
            const response = await fetch('/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payloadData)
            });

            const data = await response.json();
            removeMessage(loadingId);

            if (data.status === 'success') {
                appendMessage('bot', data.answer);
            } else {
                appendMessage('bot', (data.message || data.answer || 'Ocurrió un error.'));
            }

        } catch (error) {
            console.error('Error al comunicarse con el servidor:', error);
            removeMessage(loadingId);
            appendMessage('bot', 'Error de conexión con el servidor PHP.');
        }
    });

    // 5. Reinicio de conversación
    if (resetBtn) {
        resetBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                const res = await fetch('/reset', { method: 'POST' });
                if (res.ok) {
                    chatMessages.innerHTML = '';
                    document.querySelectorAll('.quick-replies-container').forEach(el => el.remove());

                    selectedBase64Image = null;
                    if (imageInput) imageInput.value = '';
                    if (previewContainer) previewContainer.style.display = 'none';

                    appendMessage(
                        'bot', 
                        '¡Hola! 👋 Soy tu asistente de Soporte TI. ¿En qué te puedo ayudar hoy? (Citrix, VPN, Contraseñas, etc.)', 
                        false, 
                        false
                    );
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

        safeText = safeText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        safeText = safeText.replace(/\[([^\]]+)\]\s*\((https?:\/\/[^\s\)]+)\)/gi, (match, label, url) => {
            let cleanUrl = url.replace(/[\.\,;]+$/, '');
            let trailing = url.slice(cleanUrl.length);
            return `<a href="${cleanUrl}" target="_blank" rel="noopener noreferrer">${label}</a>${trailing}`;
        });

        safeText = safeText.replace(/(^|[\s\(])(https?:\/\/[^\s\)\<]+)/gi, (match, space, url) => {
            if (match.includes('href=')) return match;
            let cleanUrl = url.replace(/[\.\,;]+$/, '');
            let trailing = url.slice(cleanUrl.length);
            return `${space}<a href="${cleanUrl}" target="_blank" rel="noopener noreferrer">${cleanUrl}</a>${trailing}`;
        });

        return safeText;
    }

    function appendMessage(sender, text, isTyping = false, showQuickReplies = true) {
        document.querySelectorAll('.quick-replies-container').forEach(el => el.remove());

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

        const contienePregunta = text.includes('?');
        if (sender === 'bot' && !isTyping && showQuickReplies && contienePregunta) {
            const quickRepliesDiv = document.createElement('div');
            quickRepliesDiv.className = 'quick-replies-container';
            quickRepliesDiv.innerHTML = `
                <button type="button" class="btn-quick-reply" onclick="sendQuickReply('Sí')"> Sí</button>
                <button type="button" class="btn-quick-reply" onclick="sendQuickReply('No')"> No</button>
            `;
            messageDiv.appendChild(quickRepliesDiv);
        }

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

    window.sendQuickReply = function(text) {
        document.querySelectorAll('.quick-replies-container').forEach(el => el.remove());

        const inputField = document.getElementById('user-input');
        if (inputField) {
            inputField.value = text;
        }

        const sendButton = document.getElementById('send-btn');
        if (sendButton) {
            sendButton.click();
        }
    };
});