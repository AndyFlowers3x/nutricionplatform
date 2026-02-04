/**
 * Sistema de Lista de Compras
 */

document.addEventListener('DOMContentLoaded', function() {
    loadShoppingList();
});

/**
 * Cargar lista de compras
 */
async function loadShoppingList() {
    const container = document.getElementById('shoppingListContainer');
    
    try {
        const response = await fetch('/nutricion-platform/api/shopping/get-list.php', {
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error('Error al cargar: ' + response.status);
        }

        const data = await response.json();

        if (data.success && data.has_list) {
            displayShoppingList(data.grouped, data.items);
        }

    } catch (error) {
        console.error('Error al cargar lista:', error);
    }
}

/**
 * Mostrar lista de compras
 */
function displayShoppingList(grouped, allItems) {
    const container = document.getElementById('shoppingListContainer');
    
    const categoryIcons = {
        'Frutas y Verduras': '🥬',
        'Proteínas': '🍗',
        'Granos y Cereales': '🌾',
        'Lácteos': '🥛',
        'Despensa': '🏺',
        'Otros': '🛒'
    };

    const totalItems = allItems.length;
    const checkedItems = allItems.filter(item => item.is_checked == 1).length;
    const progress = totalItems > 0 ? (checkedItems / totalItems) * 100 : 0;

    let html = `
        <div class="shopping-list">
            <div class="shopping-stats">
                <div class="stat-item">
                    <div class="stat-number">${totalItems}</div>
                    <div class="stat-label">Items Totales</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">${checkedItems}</div>
                    <div class="stat-label">Comprados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">${totalItems - checkedItems}</div>
                    <div class="stat-label">Pendientes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">${Math.round(progress)}%</div>
                    <div class="stat-label">Completado</div>
                </div>
            </div>
    `;

    for (const [category, items] of Object.entries(grouped)) {
        html += `
            <div class="category-section">
                <div class="category-header">
                    <div class="category-icon">${categoryIcons[category] || '📦'}</div>
                    <div class="category-info">
                        <h3>${category}</h3>
                        <div class="category-count">${items.length} items</div>
                    </div>
                </div>
                <div class="items-list">
                    ${items.map(item => `
                        <div class="shopping-item ${item.is_checked == 1 ? 'checked' : ''}" onclick="toggleItem(${item.id})">
                            <div class="item-checkbox">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="item-info">
                                <div class="item-name">${item.item_name}</div>
                                ${item.quantity > 1 ? `<div class="item-quantity"><span class="item-quantity-badge">Cantidad: ${item.quantity}</span></div>` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    html += '</div>';
    container.innerHTML = html;
}

/**
 * Generar lista de compras
 */
async function generateShoppingList() {
    if (!confirm('¿Generar lista de compras desde tu plan semanal?')) {
        return;
    }

    const loader = document.getElementById('loader');
    loader.classList.remove('hidden');

    try {
        const response = await fetch('/nutricion-platform/api/shopping/generate-list.php', {
            method: 'POST',
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }

        const data = await response.json();

        if (data.success) {
            showNotification('✅ Lista generada: ' + data.items_added + ' items', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            throw new Error(data.error || 'Error desconocido');
        }

    } catch (error) {
        console.error('Error:', error);
        showNotification('❌ ' + error.message, 'error');
    } finally {
        loader.classList.add('hidden');
    }
}

/**
 * Marcar/desmarcar item
 */
async function toggleItem(itemId) {
    try {
        const response = await fetch('/nutricion-platform/api/shopping/toggle-item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({ item_id: itemId })
        });

        const data = await response.json();

        if (data.success) {
            loadShoppingList();
        }

    } catch (error) {
        console.error('Error:', error);
    }
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
        max-width: 400px;
    `;

    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}