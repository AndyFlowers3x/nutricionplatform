/**
 * Dashboard - Funcionalidad principal
 * Manejo de navegación, stats y acciones
 */

document.addEventListener('DOMContentLoaded', function() {
    initNavigation();
    initQuickActions();
    animateStats();
    checkAuthToken();
});

/**
 * Inicializar navegación del sidebar
 */
function initNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remover active de todos
            navItems.forEach(nav => nav.classList.remove('active'));
            
            // Agregar active al clickeado
            this.classList.add('active');
            
            // Obtener la sección
            const section = this.getAttribute('href').replace('#', '');
            
            // Aquí irá la lógica para cambiar de sección
            loadSection(section);
        });
    });
}

/**
 * Cargar sección del dashboard
 */
function loadSection(section) {
    console.log('Cargando sección:', section);
    
    // Por ahora solo mostramos un mensaje
    // En las siguientes fases implementaremos cada sección
    
    switch(section) {
        case 'dashboard':
            showNotification('Dashboard cargado', 'success');
            break;
        case 'meals':
            showNotification('Plan de comidas - Próximamente', 'info');
            break;
        case 'calories':
            showNotification('Registro de calorías - Próximamente', 'info');
            break;
        case 'shopping':
            showNotification('Lista de compras - Próximamente', 'info');
            break;
        case 'assistant':
            showNotification('Asistente personal - Próximamente', 'info');
            break;
        case 'settings':
            showNotification('Ajustes - Próximamente', 'info');
            break;
    }
}

/**
 * Inicializar acciones rápidas
 */
function initQuickActions() {
    const actionButtons = document.querySelectorAll('.action-btn');
    
    actionButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.querySelector('span').textContent;
            showNotification(`${action} - Funcionalidad en desarrollo`, 'info');
        });
    });
}

/**
 * Animar barras de progreso
 */
function animateStats() {
    const progressBars = document.querySelectorAll('.stat-progress-bar');
    
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0';
        
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
}

/**
 * Verificar token de autenticación
 */
function checkAuthToken() {
    // Verificar que el token existe
    const hasToken = document.cookie.includes('auth_token');
    
    if (!hasToken) {
        window.location.href = '/nutricion-platform/public/login.php';
    }
}

/**
 * Obtener datos del usuario
 */
async function getUserProfile() {
    try {
        const response = await fetch('/nutricion-platform/api/user/profile.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error('Error al obtener perfil');
        }
        
        const data = await response.json();
        
        if (data.success) {
            return data.user;
        } else {
            throw new Error(data.error || 'Error desconocido');
        }
        
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error al cargar datos del usuario', 'error');
        return null;
    }
}

/**
 * Mostrar notificación
 */
function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icons = {
        success: '<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
        error: '<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
        info: '<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>',
        warning: '<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>'
    };
    
    notification.innerHTML = `
        ${icons[type] || icons.info}
        <span>${message}</span>
    `;
    
    // Agregar al body
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

/**
 * Formatear fecha
 */
function formatDate(date) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(date).toLocaleDateString('es-MX', options);
}

/**
 * Formatear número con separadores
 */
function formatNumber(number) {
    return new Intl.NumberFormat('es-MX').format(number);
}