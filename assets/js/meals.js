/**
 * Sistema de Plan de Comidas
 */

document.addEventListener('DOMContentLoaded', function() {
    loadWeekPlan();
});

/**
 * Cargar plan de la semana
 */
async function loadWeekPlan() {
    const container = document.getElementById('weekPlanContainer');
    
    try {
        const response = await fetch('/nutricion-platform/api/meals/get-plan.php', {
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error('Error al cargar: ' + response.status);
        }

        const data = await response.json();
        console.log('Plan recibido:', data);

        if (data.success && data.has_plan && data.plan.length > 0) {
            displayWeekPlan(data.plan);
        } else {
            // Plan vacío - mostrar empty state pero sin error
            console.log('No hay plan disponible');
        }

    } catch (error) {
        console.error('Error al cargar plan:', error);
        // No mostrar mensaje de error si simplemente no hay plan
    }
}

/**
 * Mostrar plan de la semana
 */
function displayWeekPlan(plan) {
    const container = document.getElementById('weekPlanContainer');
    
    const mealTypeLabels = {
        'breakfast': 'Desayuno',
        'lunch': 'Almuerzo',
        'dinner': 'Cena',
        'snack': 'Snack'
    };

    const mealTypeIcons = {
        'breakfast': '🌅',
        'lunch': '☀️',
        'dinner': '🌙',
        'snack': '🍪'
    };

    const html = plan.map(day => {
        const totalCalories = day.meals.reduce((sum, meal) => {
            return sum + (parseFloat(meal.calories) || 0);
        }, 0);
        
        const totalProtein = day.meals.reduce((sum, meal) => {
            return sum + (parseFloat(meal.protein) || 0);
        }, 0);

        return `
            <div class="day-section">
                <div class="day-header">
                    <div class="day-info">
                        <h3>${day.day_name}</h3>
                        <div class="day-date">${formatDate(day.date)}</div>
                    </div>
                    <div class="day-stats">
                        <div class="day-stat">
                            <div class="day-stat-label">Calorías</div>
                            <div class="day-stat-value">${Math.round(totalCalories)}</div>
                        </div>
                        <div class="day-stat">
                            <div class="day-stat-label">Proteína</div>
                            <div class="day-stat-value">${totalProtein.toFixed(1)}g</div>
                        </div>
                        <div class="day-stat">
                            <div class="day-stat-label">Comidas</div>
                            <div class="day-stat-value">${day.meals.length}</div>
                        </div>
                    </div>
                </div>
                <div class="meals-grid">
                    ${day.meals.map(meal => {
                        // Escapar comillas para JSON
                        const safeMeal = {
                            meal_name: meal.meal_name || 'Sin nombre',
                            description: meal.description || '',
                            meal_type: meal.meal_type || 'other',
                            calories: parseFloat(meal.calories) || 0,
                            protein: parseFloat(meal.protein) || 0,
                            carbs: parseFloat(meal.carbs) || 0,
                            fats: parseFloat(meal.fats) || 0,
                            preparation_time: meal.preparation_time || null,
                            ingredients: meal.ingredients || '',
                            instructions: meal.instructions || '',
                            scheduled_time: meal.scheduled_time || null
                        };
                        
                        const mealJson = JSON.stringify(safeMeal).replace(/'/g, '\\\'');
                        
                        return `
                            <div class="meal-card" onclick='showMealDetail(${mealJson})'>
                                <span class="meal-type-badge ${safeMeal.meal_type}">
                                    ${mealTypeIcons[safeMeal.meal_type] || '🍽️'} ${mealTypeLabels[safeMeal.meal_type] || 'Comida'}
                                </span>
                                <h4>${safeMeal.meal_name}</h4>
                                <p class="meal-description">${safeMeal.description}</p>
                                <div class="meal-macros">
                                    <span class="macro-tag calories">${safeMeal.calories} kcal</span>
                                    <span class="macro-tag protein">${safeMeal.protein}g P</span>
                                    <span class="macro-tag carbs">${safeMeal.carbs}g C</span>
                                    <span class="macro-tag fats">${safeMeal.fats}g G</span>
                                </div>
                                ${safeMeal.scheduled_time ? `
                                    <div class="meal-time">
                                        <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        ${safeMeal.scheduled_time.substring(0, 5)}
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }).join('');

    container.innerHTML = `<div class="week-plan">${html}</div>`;
}

/**
 * Generar nuevo plan
 */
async function generatePlan() {
    if (!confirm('¿Generar un nuevo plan? Esto reemplazará el plan actual de la semana.')) {
        return;
    }

    const loader = document.getElementById('loader');
    loader.classList.remove('hidden');

    try {
        const response = await fetch('/nutricion-platform/api/meals/generate-plan.php', {
            method: 'POST',
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }

        const data = await response.json();

        if (data.success) {
            showNotification('✅ Plan generado: ' + data.meals_added + ' comidas', 'success');
            setTimeout(() => location.reload(), 1000);
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

/**
 * Mostrar detalle de comida
 */
function showMealDetail(meal) {
    try {
        const modal = document.getElementById('mealDetailModal');
        const titleEl = document.getElementById('mealDetailTitle');
        const bodyEl = document.getElementById('mealDetailBody');

        titleEl.textContent = meal.meal_name || 'Comida';

        const mealTypeIcons = {
            'breakfast': '🌅',
            'lunch': '☀️',
            'dinner': '🌙',
            'snack': '🍪'
        };

        bodyEl.innerHTML = `
            <div class="meal-detail-container">
                <div class="meal-detail-header">
                    <div class="meal-detail-icon">${mealTypeIcons[meal.meal_type] || '🍽️'}</div>
                    <h3>${meal.meal_name}</h3>
                    <p class="meal-detail-description">${meal.description}</p>
                </div>

                <div class="detail-macros-grid">
                    <div class="detail-macro">
                        <div class="detail-macro-label">Calorías</div>
                        <div class="detail-macro-value" style="color: #10B981;">${meal.calories}</div>
                    </div>
                    <div class="detail-macro">
                        <div class="detail-macro-label">Proteína</div>
                        <div class="detail-macro-value" style="color: #3B82F6;">${meal.protein}g</div>
                    </div>
                    <div class="detail-macro">
                        <div class="detail-macro-label">Carbohidratos</div>
                        <div class="detail-macro-value" style="color: #F59E0B;">${meal.carbs}g</div>
                    </div>
                    <div class="detail-macro">
                        <div class="detail-macro-label">Grasas</div>
                        <div class="detail-macro-value" style="color: #EF4444;">${meal.fats}g</div>
                    </div>
                </div>

                ${meal.preparation_time ? `
                    <div class="detail-section">
                        <h4>⏱️ Tiempo de Preparación</h4>
                        <p style="font-size: 15px; color: #6B7280;">${meal.preparation_time} minutos</p>
                    </div>
                ` : ''}

                ${meal.ingredients ? `
                    <div class="detail-section">
                        <h4>🛒 Ingredientes</h4>
                        <div class="ingredients-list">
                            <ul>
                                ${meal.ingredients.split(',').map(ing => `<li>${ing.trim()}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                ` : ''}

                ${meal.instructions ? `
                    <div class="detail-section">
                        <h4>📝 Instrucciones</h4>
                        <div class="instructions-list">
                            <ol>
                                ${meal.instructions.split('.').filter(i => i.trim()).map(inst => `<li>${inst.trim()}.</li>`).join('')}
                            </ol>
                        </div>
                    </div>
                ` : ''}
            </div>
        `;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    } catch (error) {
        console.error('Error al mostrar detalle:', error);
        showNotification('❌ Error al mostrar detalle', 'error');
    }
}

/**
 * Cerrar detalle
 */
function closeMealDetail() {
    const modal = document.getElementById('mealDetailModal');
    modal.classList.remove('active');
    document.body.style.overflow = 'auto';
}

/**
 * Formatear fecha
 */
function formatDate(dateStr) {
    const date = new Date(dateStr + 'T12:00:00');
    const days = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    const months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    
    const dayName = days[date.getDay()];
    const day = date.getDate();
    const month = months[date.getMonth()];
    const year = date.getFullYear();
    
    return `${dayName}, ${day} de ${month} de ${year}`;
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
        animation: slideIn 0.3s ease;
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