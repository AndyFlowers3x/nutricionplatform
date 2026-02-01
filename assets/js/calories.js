/**
 * Sistema de Registro de Calorías
 * Búsqueda de alimentos y registro de comidas
 */

let selectedFood = null;
let currentCategory = '';
let searchTimeout = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeCaloriesSystem();
});

/**
 * Inicializar sistema
 */
function initializeCaloriesSystem() {
    // Event listener para búsqueda
    const searchInput = document.getElementById('foodSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchFoods(e.target.value);
            }, 300);
        });
    }

    // Event listeners para filtros de categoría
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remover active de todos
            categoryButtons.forEach(b => b.classList.remove('active'));
            // Agregar active al clickeado
            this.classList.add('active');
            
            currentCategory = this.getAttribute('data-category');
            const query = searchInput.value;
            searchFoods(query);
        });
    });

    // Event listener para input de porciones
    const servingsInput = document.getElementById('servingsInput');
    if (servingsInput) {
        servingsInput.addEventListener('input', updateServingPreview);
    }
}

/**
 * Abrir modal de agregar comida
 */
function openAddFoodModal() {
    const modal = document.getElementById('addFoodModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Focus en el input de búsqueda
    setTimeout(() => {
        document.getElementById('foodSearchInput').focus();
    }, 100);
}

/**
 * Cerrar modal de agregar comida
 */
function closeAddFoodModal() {
    const modal = document.getElementById('addFoodModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
    
    // Limpiar búsqueda
    document.getElementById('foodSearchInput').value = '';
    document.getElementById('searchResults').innerHTML = `
        <div class="search-hint">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/>
                <path d="M21 21l-4.35-4.35"/>
            </svg>
            <p>Busca un alimento para ver los resultados</p>
        </div>
    `;
}

/**
 * Buscar alimentos
 */
async function searchFoods(query) {
    const resultsContainer = document.getElementById('searchResults');
    
    if (!query && !currentCategory) {
        resultsContainer.innerHTML = `
            <div class="search-hint">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="M21 21l-4.35-4.35"/>
                </svg>
                <p>Busca un alimento para ver los resultados</p>
            </div>
        `;
        return;
    }

    // Mostrar loader
    resultsContainer.innerHTML = `
        <div class="search-hint">
            <div class="spinner" style="width: 48px; height: 48px; margin: 0 auto;"></div>
            <p>Buscando...</p>
        </div>
    `;

    try {
        let url = '/nutricion-platform/api/foods/search.php?';
        if (query) url += 'q=' + encodeURIComponent(query);
        if (currentCategory) url += '&category=' + currentCategory;

        const response = await fetch(url, {
            credentials: 'include'
        });

        const data = await response.json();

        if (data.success && data.foods && data.foods.length > 0) {
            displaySearchResults(data.foods);
        } else {
            resultsContainer.innerHTML = `
                <div class="search-hint">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <p>No se encontraron alimentos</p>
                    <p style="font-size: 13px; margin-top: 8px;">Intenta con otro término de búsqueda</p>
                </div>
            `;
        }

    } catch (error) {
        console.error('Error al buscar alimentos:', error);
        resultsContainer.innerHTML = `
            <div class="search-hint">
                <p style="color: #EF4444;">Error al buscar alimentos</p>
            </div>
        `;
    }
}

/**
 * Mostrar resultados de búsqueda
 */
function displaySearchResults(foods) {
    const resultsContainer = document.getElementById('searchResults');
    
    const categoryIcons = {
        'fruits': '🍎',
        'vegetables': '🥦',
        'proteins': '🍗',
        'grains': '🌾',
        'dairy': '🥛',
        'snacks': '🍿',
        'beverages': '🥤',
        'other': '🍽️'
    };

    const html = foods.map(food => `
        <div class="food-item" onclick='selectFood(${JSON.stringify(food)})'>
            <div class="food-icon">${categoryIcons[food.category] || '🍽️'}</div>
            <div class="food-info">
                <div class="food-name">${food.name}</div>
                <div class="food-serving">${food.serving_size} ${food.serving_unit} • ${food.protein}g P • ${food.carbs}g C • ${food.fats}g G</div>
            </div>
            <div class="food-calories">${food.calories} kcal</div>
        </div>
    `).join('');

    resultsContainer.innerHTML = html;
}

/**
 * Seleccionar alimento
 */
function selectFood(food) {
    selectedFood = food;
    
    // Cerrar modal de búsqueda
    closeAddFoodModal();
    
    // Abrir modal de porciones
    openServingModal();
}

/**
 * Abrir modal de porciones
 */
function openServingModal() {
    if (!selectedFood) return;

    const modal = document.getElementById('servingModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    // Mostrar información del alimento
    const infoContainer = document.getElementById('selectedFoodInfo');
    const categoryIcons = {
        'fruits': '🍎',
        'vegetables': '🥦',
        'proteins': '🍗',
        'grains': '🌾',
        'dairy': '🥛',
        'snacks': '🍿',
        'beverages': '🥤',
        'other': '🍽️'
    };

    infoContainer.innerHTML = `
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 48px; margin-bottom: 12px;">${categoryIcons[selectedFood.category] || '🍽️'}</div>
            <h3 style="font-size: 20px; font-weight: 700; color: #1F2937; margin-bottom: 4px;">${selectedFood.name}</h3>
            <p style="font-size: 14px; color: #6B7280;">${selectedFood.serving_size} ${selectedFood.serving_unit} = ${selectedFood.calories} kcal</p>
        </div>
    `;

    // Reset porciones a 1
    document.getElementById('servingsInput').value = 1;

    // Actualizar preview
    updateServingPreview();
}

/**
 * Cerrar modal de porciones
 */
function closeServingModal() {
    const modal = document.getElementById('servingModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
    selectedFood = null;
}

/**
 * Aumentar porción
 */
function increaseServing() {
    const input = document.getElementById('servingsInput');
    let value = parseFloat(input.value) || 0;
    value += 0.5;
    if (value > 10) value = 10;
    input.value = value;
    updateServingPreview();
}

/**
 * Disminuir porción
 */
function decreaseServing() {
    const input = document.getElementById('servingsInput');
    let value = parseFloat(input.value) || 0;
    value -= 0.5;
    if (value < 0.5) value = 0.5;
    input.value = value;
    updateServingPreview();
}

/**
 * Actualizar preview de porciones
 */
function updateServingPreview() {
    if (!selectedFood) return;

    const servings = parseFloat(document.getElementById('servingsInput').value) || 1;
    const previewContainer = document.getElementById('servingPreview');

    const calories = Math.round(selectedFood.calories * servings);
    const protein = (selectedFood.protein * servings).toFixed(1);
    const carbs = (selectedFood.carbs * servings).toFixed(1);
    const fats = (selectedFood.fats * servings).toFixed(1);

    previewContainer.innerHTML = `
        <div class="selected-food-card">
            <div class="selected-food-name">${selectedFood.name}</div>
            <div class="selected-food-amount">${servings} ${selectedFood.serving_unit}${servings > 1 ? 's' : ''}</div>
        </div>
        <div class="preview-macros">
            <div class="preview-macro calories">
                <div class="preview-macro-label">Calorías</div>
                <div class="preview-macro-value">${calories}</div>
            </div>
            <div class="preview-macro protein">
                <div class="preview-macro-label">Proteína</div>
                <div class="preview-macro-value">${protein}g</div>
            </div>
            <div class="preview-macro carbs">
                <div class="preview-macro-label">Carbos</div>
                <div class="preview-macro-value">${carbs}g</div>
            </div>
            <div class="preview-macro fats">
                <div class="preview-macro-label">Grasas</div>
                <div class="preview-macro-value">${fats}g</div>
            </div>
        </div>
    `;
}

/**
 * Confirmar y registrar comida
 */
async function confirmLogMeal() {
    if (!selectedFood) return;

    const servings = parseFloat(document.getElementById('servingsInput').value) || 1;
    const loader = document.getElementById('loader');
    
    loader.classList.remove('hidden');

    try {
        const response = await fetch('/nutricion-platform/api/calories/log-meal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                food_id: selectedFood.id,
                servings: servings
            })
        });

        const data = await response.json();

        if (data.success) {
            // Cerrar modal
            closeServingModal();
            
            // Mostrar notificación de éxito
            showNotification('✅ Comida registrada exitosamente', 'success');
            
            // Actualizar estadísticas
            updateTodayStats(data.today_totals);
            
            // Agregar a la lista
            addLogEntry(data.logged);
            
        } else {
            throw new Error(data.error || 'Error al registrar comida');
        }

    } catch (error) {
        console.error('Error:', error);
        showNotification('❌ Error al registrar comida: ' + error.message, 'error');
    } finally {
        loader.classList.add('hidden');
    }
}

/**
 * Actualizar estadísticas del día
 */
function updateTodayStats(totals) {
    // Actualizar valores
    document.getElementById('totalCalories').textContent = Math.round(totals.total_calories).toLocaleString();
    document.getElementById('totalProtein').textContent = parseFloat(totals.total_protein).toFixed(1) + 'g';
    document.getElementById('totalCarbs').textContent = parseFloat(totals.total_carbs).toFixed(1) + 'g';
    document.getElementById('totalFats').textContent = parseFloat(totals.total_fats).toFixed(1) + 'g';

    // Actualizar barra de progreso (necesitamos el target del usuario)
    // Por ahora solo animamos la barra
    const progressBar = document.getElementById('caloriesProgressBar');
    if (progressBar) {
        const currentWidth = progressBar.style.width;
        progressBar.style.width = '0%';
        setTimeout(() => {
            progressBar.style.width = currentWidth;
        }, 100);
    }
}

/**
 * Agregar entrada al log
 */
function addLogEntry(logged) {
    const logList = document.getElementById('logList');
    
    // Remover empty state si existe
    const emptyState = logList.querySelector('.empty-state');
    if (emptyState) {
        emptyState.remove();
    }

    const now = new Date();
    const timeStr = now.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });

    const logItem = document.createElement('div');
    logItem.className = 'log-item';
    logItem.style.animation = 'slideInRight 0.3s ease';
    logItem.innerHTML = `
        <div class="log-icon">🍴</div>
        <div class="log-details">
            <div class="log-name">${logged.food}</div>
            <div class="log-time">${timeStr}</div>
        </div>
        <div class="log-macros">
            <span class="macro-badge calories">${logged.calories} kcal</span>
            <span class="macro-badge protein">${logged.protein}g P</span>
            <span class="macro-badge carbs">${logged.carbs}g C</span>
            <span class="macro-badge fats">${logged.fats}g G</span>
        </div>
    `;

    // Insertar al inicio
    logList.insertBefore(logItem, logList.firstChild);
}

/**
 * Mostrar notificación
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type} show`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 16px 20px;
        border-radius: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 300px;
        font-size: 14px;
        font-weight: 500;
        animation: slideInRight 0.3s ease;
    `;

    if (type === 'success') {
        notification.style.borderLeft = '4px solid #10B981';
        notification.style.color = '#166534';
    } else if (type === 'error') {
        notification.style.borderLeft = '4px solid #EF4444';
        notification.style.color = '#991B1B';
    }

    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Animaciones CSS adicionales
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(50px);
        }
    }

    .spinner {
        border: 3px solid #E5E7EB;
        border-top-color: #10B981;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.95);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        backdrop-filter: blur(5px);
    }

    .loader.hidden {
        display: none;
    }

    .loader .spinner {
        width: 50px;
        height: 50px;
        margin-bottom: 20px;
    }

    .loader p {
        color: #1F2937;
        font-weight: 600;
        font-size: 16px;
    }
`;
document.head.appendChild(style);
