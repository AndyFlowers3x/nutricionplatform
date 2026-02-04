/**
 * Sistema de Asistente Virtual
 */

document.addEventListener('DOMContentLoaded', function() {
    loadChatHistory();
});

/**
 * Cargar historial de conversación
 */
async function loadChatHistory() {
    try {
        const response = await fetch('/nutricion-platform/api/assistant/get-history.php?limit=20', {
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error('Error al cargar historial');
        }

        const data = await response.json();

        if (data.success && data.conversations.length > 0) {
            const chatMessages = document.getElementById('chatMessages');
            
            data.conversations.forEach(conv => {
                addMessageToChat('user', conv.message, false);
                addMessageToChat('assistant', conv.response, false);
            });

            scrollToBottom();
        }

    } catch (error) {
        console.error('Error:', error);
    }
}

/**
 * Enviar mensaje
 */
async function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();

    if (!message) return;

    // Agregar mensaje del usuario
    addMessageToChat('user', message, true);
    input.value = '';

    // Mostrar indicador de escritura
    showTypingIndicator();

    try {
        const response = await fetch('/nutricion-platform/api/assistant/chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ message: message })
        });

        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }

        const data = await response.json();

        hideTypingIndicator();

        if (data.success) {
            addMessageToChat('assistant', data.response, true);
        } else {
            throw new Error(data.error || 'Error desconocido');
        }

    } catch (error) {
        console.error('Error:', error);
        hideTypingIndicator();
        addMessageToChat('assistant', 'Lo siento, hubo un error al procesar tu mensaje. Por favor, intenta de nuevo.', true);
    }
}

/**
 * Enviar mensaje rápido
 */
function sendQuickMessage(message) {
    const input = document.getElementById('chatInput');
    input.value = message;
    sendMessage();
}

/**
 * Agregar mensaje al chat
 */
function addMessageToChat(type, text, animate) {
    const chatMessages = document.getElementById('chatMessages');
    
    const messageBubble = document.createElement('div');
    messageBubble.className = `message-bubble ${type}`;
    if (animate) {
        messageBubble.style.animation = 'messageSlideIn 0.3s ease';
    }

    const avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    avatar.textContent = type === 'user' ? '👤' : '🤖';

    const messageContent = document.createElement('div');
    messageContent.style.flex = '1';

    const messageText = document.createElement('div');
    messageText.className = 'message-text';
    messageText.textContent = text;

    const messageTime = document.createElement('div');
    messageTime.className = 'message-time';
    messageTime.textContent = new Date().toLocaleTimeString('es-MX', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });

    messageContent.appendChild(messageText);
    messageContent.appendChild(messageTime);

    messageBubble.appendChild(avatar);
    messageBubble.appendChild(messageContent);

    chatMessages.appendChild(messageBubble);
    
    if (animate) {
        scrollToBottom();
    }
}

/**
 * Mostrar indicador de escritura
 */
function showTypingIndicator() {
    const chatMessages = document.getElementById('chatMessages');
    
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typingIndicator';
    typingDiv.className = 'message-bubble assistant';
    typingDiv.innerHTML = `
        <div class="message-avatar">🤖</div>
        <div class="typing-indicator">
            <div class="typing-dots">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        </div>
    `;

    chatMessages.appendChild(typingDiv);
    scrollToBottom();
}

/**
 * Ocultar indicador de escritura
 */
function hideTypingIndicator() {
    const typingIndicator = document.getElementById('typingIndicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

/**
 * Scroll al final del chat
 */
function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

/**
 * Manejar tecla Enter
 */
function handleKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
}

/**
 * Limpiar chat
 */
function clearChat() {
    if (!confirm('¿Estás seguro de que quieres limpiar el chat?')) {
        return;
    }

    const chatMessages = document.getElementById('chatMessages');
    const welcomeMessage = chatMessages.querySelector('.welcome-message');
    
    chatMessages.innerHTML = '';
    if (welcomeMessage) {
        chatMessages.appendChild(welcomeMessage);
    }

    showNotification('Chat limpiado', 'success');
}

/**
 * Notificación
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 16px 20px;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        font-size: 14px;
        font-weight: 500;
        border-left: 4px solid ${type === 'success' ? '#10B981' : '#EF4444'};
        color: ${type === 'success' ? '#166534' : '#991B1B'};
    `;

    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}