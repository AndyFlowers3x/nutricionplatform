/**
 * Sistema de Registro de Calorías
 */

let selectedFood = null;
let currentCategory = '';
let searchTimeout = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeCaloriesSystem();
});

function initializeCaloriesSystem() {
    const searchInput = document.getElementById('foodSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchFoods(e.target.value);
            }, 300);
        });
    }

    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            categoryButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.getAttribute('data-category');
            searchFoods(searchInput.value);
        });
    });

    const servingsInput = document.getElementById('servingsInput');
    if (servingsInput) {
        servingsInput.addEventListener('input', updateServingPreview);
    }
}

function openAddFoodModal() {
    const modal = document.getElementById('addFoodModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    setTimeout(() => {
        document.getElementById('foodSearchInput').focus();
    }, 100);
}

function closeAddFoodModal() {
    const modal = document.getElementById('addFoodModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
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

    resultsContainer.innerHTML = `
        <div class="search-hint">
            <div class="spinner" style="width: 48px; height: 48px; margin: 0 auto; border: 4px solid #E5E7EB; border-top-color: #10B981; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
            <p>Buscando...</p>
        </div>
    `;

    try {
        let url = '/nutricion-platform/api/foods/search.php?';
        if (query) url += 'q=' + encodeURIComponent(query);
        if (currentCategory) url += '&category=' + currentCategory;

        const response = await fetch(url, { credentials: 'include' });

        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }

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
                </div>
            `;
        }

    } catch (error) {
        console.error('Error al buscar:', error);
        resultsContainer.innerHTML = `
            <div class="search-hint">
                <p style="color: #EF4444;">Error al buscar alimentos</p>
                <p style="font-size: 13px; margin-top: 8px;">${error.message}</p>
            </div>
        `;
    }
}

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

    const categoryColors = {
        'fruits': '#FEE2E2',
        'vegetables': '#DCFCE7',
        'proteins': '#DBEAFE',
        'grains': '#FEF3C7',
        'dairy': '#F3E8FF',
        'snacks': '#FCE7F3',
        'beverages': '#E0E7FF',
        'other': '#F3F4F6'
    };

    const html = foods.map(food => {
        const foodJson = JSON.stringify(food).replace(/'/g, "\\'").replace(/"/g, '&quot;');
        
        return `
            <div class="food-item" onclick='selectFood(${foodJson})'>
                <div class="food-image" style="background: ${categoryColors[food.category] || '#F3F4F6'};">
                    <span class="food-emoji">${categoryIcons[food.category] || '🍽️'}</span>
                </div>
                <div class="food-info">
                    <div class="food-name">${food.name}</div>
                    <div class="food-serving">${food.serving_size} ${food.serving_unit}</div>
                    <div class="food-macros-mini">
                        <span class="mini-macro">P: ${food.protein}g</span>
                        <span class="mini-macro">C: ${food.carbs}g</span>
                        <span class="mini-macro">G: ${food.fats}g</span>
                    </div>
                </div>
                <div class="food-calories-badge">
                    <div class="calories-number">${food.calories}</div>
                    <div class="calories-label">kcal</div>
                </div>
            </div>
        `;
    }).join('');

    resultsContainer.innerHTML = html;
}

function selectFood(food) {
    selectedFood = food;
    closeAddFoodModal();
    openServingModal();
}

function openServingModal() {
    if (!selectedFood) return;

    const modal = document.getElementById('servingModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

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

    document.getElementById('servingsInput').value = 1;
    updateServingPreview();
}

function closeServingModal() {
    const modal = document.getElementById('servingModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
    selectedFood = null;
}

function increaseServing() {
    const input = document.getElementById('servingsInput');
    let value = parseFloat(input.value) || 0;
    value += 0.5;
    if (value > 10) value = 10;
    input.value = value;
    updateServingPreview();
}

function decreaseServing() {
    const input = document.getElementById('servingsInput');
    let value = parseFloat(input.value) || 0;
    value -= 0.5;
    if (value < 0.5) value = 0.5;
    input.value = value;
    updateServingPreview();
}

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

async function confirmLogMeal() {
    if (!selectedFood) return;

    const servings = parseFloat(document.getElementById('servingsInput').value) || 1;
    const loader = document.getElementById('loader');
    
    loader.classList.remove('hidden');

    try {
        const response = await fetch('/nutricion-platform/api/calories/log-meal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                food_id: selectedFood.id,
                servings: servings
            })
        });

        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }

        const data = await response.json();

        if (data.success) {
            closeServingModal();
            showNotification('✅ Comida registrada exitosamente', 'success');
            updateTodayStats(data.today_totals);
            addLogEntry(data.logged);
        } else {
            throw new Error(data.error || 'Error desconocido');
        }

    } catch (error) {
        console.error('Error:', error);
        showNotification('❌ Error: ' + error.message, 'error');
    } finally {
        loader.classList.add('hidden');
    }
}

function updateTodayStats(totals) {
    document.getElementById('totalCalories').textContent = Math.round(totals.total_calories).toLocaleString();
    document.getElementById('totalProtein').textContent = parseFloat(totals.total_protein).toFixed(1) + 'g';
    document.getElementById('totalCarbs').textContent = parseFloat(totals.total_carbs).toFixed(1) + 'g';
    document.getElementById('totalFats').textContent = parseFloat(totals.total_fats).toFixed(1) + 'g';
}

function addLogEntry(logged) {
    const logList = document.getElementById('logList');
    
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

    logList.insertBefore(logItem, logList.firstChild);
}

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
        animation: slideInRight 0.3s ease;
        border-left: 4px solid ${type === 'success' ? '#10B981' : '#EF4444'};
        color: ${type === 'success' ? '#166534' : '#991B1B'};
        max-width: 400px;
    `;

    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Estilos adicionales
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(50px); }
        to { opacity: 1; transform: translateX(0); }
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
    }

    .loader.hidden {
        display: none;
    }

    .loader .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #E5E7EB;
        border-top-color: #10B981;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-bottom: 20px;
    }

    .loader p {
        color: #1F2937;
        font-weight: 600;
        font-size: 16px;
    }
`;
document.head.appendChild(style);
