/**
 * Manejo de autenticación
 * Login con Google y gestión de sesión
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Mostrar loader cuando se hace clic en el botón de Google
    const googleBtn = document.querySelector('.btn-google');
    const loader = document.getElementById('loader');
    
    if (googleBtn && loader) {
        googleBtn.addEventListener('click', function(e) {
            loader.classList.remove('hidden');
        });
    }

    // Verificar si hay error en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    
    if (error) {
        showError('Error al iniciar sesión. Por favor, intenta nuevamente.');
    }
});

/**
 * Mostrar mensaje de error
 */
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-error';
    errorDiv.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span>${message}</span>
    `;
    
    const loginCard = document.querySelector('.login-card');
    if (loginCard) {
        loginCard.insertBefore(errorDiv, loginCard.firstChild);
        
        // Remover después de 5 segundos
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
}

/**
 * Validar email (para login futuro con email)
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Mostrar/ocultar contraseña
 */
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.type = input.type === 'password' ? 'text' : 'password';
    }
}