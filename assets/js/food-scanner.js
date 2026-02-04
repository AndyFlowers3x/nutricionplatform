/**
 * Sistema de Escáner de Comida con IA
 */

let cameraStream = null;
let currentImageFile = null;
let currentNutritionData = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeScanner();
    loadScanHistory();
});

/**
 * Inicializar escáner
 */
function initializeScanner() {
    const fileInput = document.getElementById('fileInput');
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            currentImageFile = file;
            displayImagePreview(file);
        }
    });
}

/**
 * Mostrar vista previa de imagen
 */
function displayImagePreview(file) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
        const previewImg = document.getElementById('previewImage');
        const placeholder = document.querySelector('.upload-placeholder');
        
        previewImg.src = e.target.result;
        previewImg.style.display = 'block';
        placeholder.style.display = 'none';
        
        document.getElementById('analyzeBtn').style.display = 'flex';
    };
    
    reader.readAsDataURL(file);
}

/**
 * Iniciar cámara
 */
async function startCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ 
            video: { facingMode: 'environment' } 
        });
        
        cameraStream = stream;
        
        const videoElement = document.getElementById('cameraStream');
        const placeholder = document.querySelector('.upload-placeholder');
        
        videoElement.srcObject = stream;
        videoElement.style.display = 'block';
        placeholder.style.display = 'none';
        
        document.getElementById('captureBtn').style.display = 'flex';
        document.querySelector('.btn-camera').textContent = '🔴 Detener Cámara';
        document.querySelector('.btn-camera').onclick = stopCamera;
        
    } catch (error) {
        console.error('Error al acceder a la cámara:', error);
        showNotification('❌ No se pudo acceder a la cámara', 'error');
    }
}

/**
 * Detener cámara
 */
function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
        
        document.getElementById('cameraStream').style.display = 'none';
        document.querySelector('.upload-placeholder').style.display = 'block';
        document.getElementById('captureBtn').style.display = 'none';
        
        document.querySelector('.btn-camera').textContent = '📷 Usar Cámara';
        document.querySelector('.btn-camera').onclick = startCamera;
    }
}

/**
 * Capturar foto de la cámara
 */
function capturePhoto() {
    const video = document.getElementById('cameraStream');
    const canvas = document.createElement('canvas');
    
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0);
    
    canvas.toBlob(function(blob) {
        currentImageFile = new File([blob], 'camera-photo.jpg', { type: 'image/jpeg' });
        
        const previewImg = document.getElementById('previewImage');
        previewImg.src = canvas.toDataURL('image/jpeg');
        previewImg.style.display = 'block';
        
        stopCamera();
        
        document.getElementById('analyzeBtn').style.display = 'flex';
    }, 'image/jpeg');
}

/**
 * Analizar comida
 */
async function analyzeFood() {
    if (!currentImageFile) {
        showNotification('❌ Por favor selecciona una imagen primero', 'error');
        return;
    }

    const loader = document.getElementById('loader');
    loader.classList.remove('hidden');

    try {
        const formData = new FormData();
        formData.append('image', currentImageFile);

        const response = await fetch('/nutricion-platform/api/food-scanner/analyze-image.php', {
            method: 'POST',
            credentials: 'include',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Error HTTP: ' + response.status);
        }

        const data = await response.json();

        if (data.success) {
            currentNutritionData = data.nutrition;
            displayNutritionResults(data);
            loadScanHistory();
            showNotification('✅ Análisis completado exitosamente', 'success');
        } else {
            throw new Error(data.error || 'Error desconocido');
        }

    } catch (error) {
        console.error('Error:', error);
        showNotification('❌ Error al analizar: ' + error.message, 'error');
    } finally {
        loader.classList.add('hidden');
    }
}

/**
 * Mostrar resultados nutricionales
 */
function displayNutritionResults(data) {
    const resultsSection = document.getElementById('resultsSection');
    const nutritionResults = document.getElementById('nutritionResults');

    const nutrition = data.nutrition;
    
    let vitaminsHtml = '';
    if (nutrition.vitamins && nutrition.vitamins.length > 0) {
        vitaminsHtml = `
            <div class="vitamins-section">
                <h3>Vitaminas y Minerales</h3>
                <div class="vitamins-list">
                    ${nutrition.vitamins.map(v => `
                        <div class="vitamin-item">
                            <span class="vitamin-name">${v.name}</span>
                            <span class="vitamin-value">${v.value}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    nutritionResults.innerHTML = `
        <div class="nutrition-card">
            <div class="food-name-result">
                ${data.food_name}
                <span class="confidence-badge">IA Verificado</span>
            </div>
            <p style="color: #166534; margin-bottom: 20px;">Información nutricional por porción</p>
            
            <div class="nutrition-grid">
                <div class="nutrition-item highlight">
                    <div class="nutrition-label">Calorías</div>
                    <div class="nutrition-value">
                        ${nutrition.calories}
                        <span class="nutrition-unit">kcal</span>
                    </div>
                </div>
                
                <div class="nutrition-item">
                    <div class="nutrition-label">Proteína</div>
                    <div class="nutrition-value">
                        ${nutrition.protein}
                        <span class="nutrition-unit">g</span>
                    </div>
                </div>
                
                <div class="nutrition-item">
                    <div class="nutrition-label">Carbohidratos</div>
                    <div class="nutrition-value">
                        ${nutrition.carbs}
                        <span class="nutrition-unit">g</span>
                    </div>
                </div>
                
                <div class="nutrition-item">
                    <div class="nutrition-label">Grasas</div>
                    <div class="nutrition-value">
                        ${nutrition.fats}
                        <span class="nutrition-unit">g</span>
                    </div>
                </div>
                
                <div class="nutrition-item">
                    <div class="nutrition-label">Fibra</div>
                    <div class="nutrition-value">
                        ${nutrition.fiber}
                        <span class="nutrition-unit">g</span>
                    </div>
                </div>
                
                <div class="nutrition-item">
                    <div class="nutrition-label">Azúcar</div>
                    <div class="nutrition-value">
                        ${nutrition.sugar}
                        <span class="nutrition-unit">g</span>
                    </div>
                </div>
                
                <div class="nutrition-item">
                    <div class="nutrition-label">Sodio</div>
                    <div class="nutrition-value">
                        ${nutrition.sodium}
                        <span class="nutrition-unit">mg</span>
                    </div>
                </div>
            </div>
            
            ${vitaminsHtml}
            
            <button class="add-to-log-btn" onclick="addToCaloriesLog()">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                </svg>
                Agregar a Mi Registro de Calorías
            </button>
        </div>
    `;

    resultsSection.style.display = 'block';
    resultsSection.scrollIntoView({ behavior: 'smooth' });
}

/**
 * Agregar al registro de calorías
 */
async function addToCaloriesLog() {
    if (!currentNutritionData) return;

    const loader = document.getElementById('loader');
    loader.classList.remove('hidden');

    try {
        // Crear entrada temporal en foods si no existe
        const response = await fetch('/nutricion-platform/api/calories/log-meal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                food_name: currentNutritionData.name,
                calories: currentNutritionData.calories,
                protein: currentNutritionData.protein,
                carbs: currentNutritionData.carbs,
                fats: currentNutritionData.fats
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('✅ Agregado a tu registro de calorías', 'success');
            setTimeout(() => {
                window.location.href = 'calories.php';
            }, 1500);
        } else {
            throw new Error(data.error);
        }

    } catch (error) {
        console.error('Error:', error);
        showNotification('❌ Error al agregar: ' + error.message, 'error');
    } finally {
        loader.classList.add('hidden');
    }
}

/**
 * Cargar historial de escaneos
 */
async function loadScanHistory() {
    try {
        const response = await fetch('/nutricion-platform/api/food-scanner/get-history.php?limit=12', {
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error('Error al cargar historial');
        }

        const data = await response.json();

        if (data.success && data.scans.length > 0) {
            displayScanHistory(data.scans);
        }

    } catch (error) {
        console.error('Error:', error);
    }
}

/**
 * Mostrar historial de escaneos
 */
function displayScanHistory(scans) {
    const historyGrid = document.getElementById('historyGrid');
    
    const html = scans.map(scan => `
        <div class="history-card" onclick='viewScanDetail(${JSON.stringify(scan).replace(/'/g, "\\'")})'}>
            <img src="${scan.image_path}" alt="${scan.food_name}" class="history-image">
            <div class="history-info">
                <div class="history-food-name">${scan.food_name}</div>
                <div class="history-calories">${scan.calories} kcal</div>
                <div class="history-date">${new Date(scan.created_at).toLocaleDateString('es-MX')}</div>
            </div>
        </div>
    `).join('');

    historyGrid.innerHTML = html;
}

/**
 * Ver detalle de escaneo
 */
function viewScanDetail(scan) {
    currentNutritionData = scan;
    displayNutritionResults({
        food_name: scan.food_name,
        nutrition: scan
    });
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
