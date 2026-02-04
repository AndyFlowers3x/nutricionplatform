/**
 * Cuestionario de Salud - JavaScript CORREGIDO
 * Manejo de navegación multi-step y cálculos nutricionales
 */

let currentStep = 1;
const totalSteps = 6;
let formData = {};

document.addEventListener('DOMContentLoaded', function() {
    initQuestionnaire();
});

/**
 * Inicializar cuestionario
 */
function initQuestionnaire() {
    const form = document.getElementById('questionnaireForm');
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');

    // Navegación
    if (nextBtn) nextBtn.addEventListener('click', () => nextStep());
    if (prevBtn) prevBtn.addEventListener('click', () => prevStep());
    
    // Submit
    if (form) form.addEventListener('submit', handleSubmit);
}

/**
 * Siguiente paso
 */
function nextStep() {
    console.log('Intentando avanzar del paso', currentStep);
    
    if (!validateStep(currentStep)) {
        console.log('Validación falló en paso', currentStep);
        return;
    }

    // Guardar datos del paso actual
    saveStepData(currentStep);
    console.log('Datos guardados:', formData);

    // Ocultar paso actual
    const currentStepEl = document.querySelector(`[data-step="${currentStep}"]`);
    if (currentStepEl) {
        currentStepEl.classList.remove('active');
    }

    // Avanzar
    currentStep++;
    console.log('Avanzando a paso', currentStep);

    // Mostrar siguiente paso
    const nextStepEl = document.querySelector(`[data-step="${currentStep}"]`);
    if (nextStepEl) {
        nextStepEl.classList.add('active');
    }

    // Actualizar progreso
    updateProgress();

    // Actualizar botones
    updateButtons();

    // Si es el último paso, mostrar resumen
    if (currentStep === totalSteps) {
        console.log('Último paso alcanzado, mostrando resumen');
        showSummary();
        calculateNutrition();
    }

    // Scroll al inicio
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Paso anterior
 */
function prevStep() {
    // Ocultar paso actual
    const currentStepEl = document.querySelector(`[data-step="${currentStep}"]`);
    if (currentStepEl) {
        currentStepEl.classList.remove('active');
    }
    if (currentStep < totalSteps) {
    summaryCalculated = false;
}


    // Retroceder
    currentStep--;

    // Mostrar paso anterior
    const prevStepEl = document.querySelector(`[data-step="${currentStep}"]`);
    if (prevStepEl) {
        prevStepEl.classList.add('active');
    }

    // Actualizar progreso
    updateProgress();

    // Actualizar botones
    updateButtons();

    // Scroll al inicio
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Validar paso actual
 */
function validateStep(step) {
    const stepEl = document.querySelector(`[data-step="${step}"]`);
    if (!stepEl) {
        console.error('No se encontró el elemento del paso', step);
        return false;
    }
    
    const requiredFields = stepEl.querySelectorAll('[required]');
    console.log('Campos requeridos encontrados:', requiredFields.length);
    
    let isValid = true;
    let errorFields = [];

    requiredFields.forEach(field => {
        if (field.type === 'radio') {
            const name = field.name;
            const checked = stepEl.querySelector(`input[name="${name}"]:checked`);
            if (!checked) {
                isValid = false;
                errorFields.push(name);
                console.log('Radio no seleccionado:', name);
            }
        } else if (field.type === 'checkbox') {
            // Los checkboxes son opcionales
        } else {
            // Input normal (text, number, etc)
            if (!field.value || field.value.trim() === '') {
                isValid = false;
                field.style.borderColor = '#EF4444';
                errorFields.push(field.name || field.id);
                console.log('Campo vacío:', field.name || field.id);
            } else {
                field.style.borderColor = '';
            }
        }
    });

    if (!isValid) {
        const message = errorFields.length > 0 
            ? `Por favor completa: ${errorFields.join(', ')}`
            : 'Por favor, completa todos los campos requeridos';
        showNotification(message, 'error');
    }

    return isValid;
}

/**
 * Guardar datos del paso
 */
function saveStepData(step) {
    const stepEl = document.querySelector(`[data-step="${step}"]`);
    if (!stepEl) return;
    
    const inputs = stepEl.querySelectorAll('input, select, textarea');

    inputs.forEach(input => {
        const name = input.name;
        
        if (input.type === 'radio') {
            if (input.checked) {
                formData[name] = input.value;
                console.log('Radio guardado:', name, '=', input.value);
            }
        } else if (input.type === 'checkbox') {
            if (name.includes('[]')) {
                const baseName = name.replace('[]', '');
                if (!formData[baseName]) {
                    formData[baseName] = [];
                }
                if (input.checked) {
                    formData[baseName].push(input.value);
                }
            }
        } else if (input.type !== 'radio' && input.type !== 'checkbox') {
            if (input.value && input.value.trim() !== '') {
                formData[name] = input.value;
                console.log('Campo guardado:', name, '=', input.value);
            }
        }
    });
}

/**
 * Actualizar barra de progreso
 */
function updateProgress() {
    const progressFill = document.getElementById('progressFill');
    const currentStepText = document.getElementById('currentStep');
    
    if (progressFill && currentStepText) {
        const percentage = (currentStep / totalSteps) * 100;
        progressFill.style.width = percentage + '%';
        currentStepText.textContent = currentStep;
    }
}

/**
 * Actualizar botones de navegación
 */
function updateButtons() {
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const submitBtn = document.getElementById('submitBtn');

    // Botón anterior
    if (prevBtn) {
        prevBtn.style.display = currentStep === 1 ? 'none' : 'block';
    }

    // Botón siguiente/enviar
    if (currentStep === totalSteps) {
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'block';
    } else {
        if (nextBtn) nextBtn.style.display = 'block';
        if (submitBtn) submitBtn.style.display = 'none';
    }
}

/**
 * Mostrar resumen
 */
function showSummary() {
    const summaryContent = document.getElementById('summaryContent');
    if (!summaryContent) return;
    
    const weight = formData.weight_unit === 'lb' 
        ? `${formData.weight} lb (${(parseFloat(formData.weight) * 0.453592).toFixed(1)} kg)`
        : `${formData.weight} kg`;

    const height = formData.height_unit === 'ft'
        ? `${formData.height} ft (${(parseFloat(formData.height) * 30.48).toFixed(0)} cm)`
        : `${formData.height} cm`;

    const activityLabels = {
        'sedentary': 'Sedentario',
        'light': 'Ligero',
        'moderate': 'Moderado',
        'active': 'Activo',
        'very_active': 'Muy Activo'
    };

    const goalLabels = {
        'lose_weight': 'Perder Peso',
        'maintain': 'Mantener',
        'gain_weight': 'Subir Peso',
        'muscle_gain': 'Ganar Músculo'
    };

    const genderLabels = {
        'male': 'Masculino',
        'female': 'Femenino',
        'other': 'Otro'
    };

    summaryContent.innerHTML = `
        <div class="summary-item">
            <div class="summary-label">Peso</div>
            <div class="summary-value">${weight}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Altura</div>
            <div class="summary-value">${height}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Edad</div>
            <div class="summary-value">${formData.age} años</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Género</div>
            <div class="summary-value">${genderLabels[formData.gender] || 'No especificado'}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Actividad</div>
            <div class="summary-value">${activityLabels[formData.activity_level] || 'No especificado'}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Objetivo</div>
            <div class="summary-value">${goalLabels[formData.goal] || 'No especificado'}</div>
        </div>
    `;
}

/**
 * Calcular nutrición
 */
function calculateNutrition() {
    console.log('Calculando nutrición con datos:', formData);
    
    // Convertir a kg y cm si es necesario
    let weightKg = parseFloat(formData.weight);
    if (formData.weight_unit === 'lb') {
        weightKg = weightKg * 0.453592;
    }

    let heightCm = parseFloat(formData.height);
    if (formData.height_unit === 'ft') {
        heightCm = heightCm * 30.48;
    }

    const age = parseInt(formData.age);
    const gender = formData.gender;
    const activityLevel = formData.activity_level;
    const goal = formData.goal;

    console.log('Valores convertidos - Peso (kg):', weightKg, 'Altura (cm):', heightCm);

    // Calcular IMC
    const heightM = heightCm / 100;
    const bmi = (weightKg / (heightM * heightM)).toFixed(1);

    // Calcular TMB (Tasa Metabólica Basal) - Fórmula de Harris-Benedict
    let bmr;
    if (gender === 'male') {
        bmr = 88.362 + (13.397 * weightKg) + (4.799 * heightCm) - (5.677 * age);
    } else {
        bmr = 447.593 + (9.247 * weightKg) + (3.098 * heightCm) - (4.330 * age);
    }

    // Multiplicadores de actividad
    const activityMultipliers = {
        'sedentary': 1.2,
        'light': 1.375,
        'moderate': 1.55,
        'active': 1.725,
        'very_active': 1.9
    };

    // Calcular TDEE (Total Daily Energy Expenditure)
    let tdee = bmr * activityMultipliers[activityLevel];

    // Ajustar según objetivo
    let targetCalories;
    if (goal === 'lose_weight') {
        targetCalories = tdee - 500;
    } else if (goal === 'gain_weight' || goal === 'muscle_gain') {
        targetCalories = tdee + 300;
    } else {
        targetCalories = tdee;
    }

    targetCalories = Math.round(targetCalories);

    // Calcular macros
    let protein, carbs, fats;

    if (goal === 'muscle_gain') {
        protein = Math.round(weightKg * 2.2);
        fats = Math.round((targetCalories * 0.25) / 9);
        const remainingCals = targetCalories - (protein * 4) - (fats * 9);
        carbs = Math.round(remainingCals / 4);
    } else if (goal === 'lose_weight') {
        protein = Math.round(weightKg * 2.0);
        fats = Math.round((targetCalories * 0.25) / 9);
        const remainingCals = targetCalories - (protein * 4) - (fats * 9);
        carbs = Math.round(remainingCals / 4);
    } else {
        protein = Math.round(weightKg * 1.6);
        fats = Math.round((targetCalories * 0.30) / 9);
        const remainingCals = targetCalories - (protein * 4) - (fats * 9);
        carbs = Math.round(remainingCals / 4);
    }

    console.log('Resultados - IMC:', bmi, 'Cal:', targetCalories, 'P:', protein, 'C:', carbs, 'F:', fats);

    // Mostrar resultados
    const bmiEl = document.getElementById('bmiValue');
    const calEl = document.getElementById('calcCalories');
    const proteinEl = document.getElementById('calcProtein');
    const carbsEl = document.getElementById('calcCarbs');
    const fatsEl = document.getElementById('calcFats');

    if (bmiEl) bmiEl.textContent = bmi;
    if (calEl) calEl.textContent = targetCalories;
    if (proteinEl) proteinEl.textContent = protein + 'g';
    if (carbsEl) carbsEl.textContent = carbs + 'g';
    if (fatsEl) fatsEl.textContent = fats + 'g';

    // Guardar en formData
    formData.bmi = bmi;
    formData.target_calories = targetCalories;
    formData.target_protein = protein;
    formData.target_carbs = carbs;
    formData.target_fats = fats;
    formData.weight_kg = weightKg.toFixed(2);
    formData.height_cm = heightCm.toFixed(0);

    console.log('FormData actualizado con cálculos:', formData);
}

/**
 * Manejar envío del formulario
 */
/**
 * Manejar envío del formulario
 */
async function handleSubmit(e) {
    e.preventDefault();
    console.log('Formulario enviado');

    // Guardar último paso
    saveStepData(currentStep);

    const loader = document.getElementById('loader');
    if (loader) loader.classList.remove('hidden');

    console.log('Datos a enviar:', formData);

    // Asegurar que todos los datos numéricos sean números
    const cleanData = {
        weight: parseFloat(formData.weight) || 0,
        weight_kg: parseFloat(formData.weight_kg) || 0,
        weight_unit: formData.weight_unit || 'kg',
        height: parseFloat(formData.height) || 0,
        height_cm: parseFloat(formData.height_cm) || 0,
        height_unit: formData.height_unit || 'cm',
        age: parseInt(formData.age) || 0,
        gender: formData.gender || 'other',
        activity_level: formData.activity_level || 'sedentary',
        goal: formData.goal || 'maintain',
        health_conditions: formData.health_conditions || '',
        allergies_other: formData.allergies_other || '',
        diet_type: formData.diet_type || 'normal',
        meals_per_day: formData.meals_per_day || '3',
        bmi: parseFloat(formData.bmi) || 0,
        target_calories: parseInt(formData.target_calories) || 0,
        target_protein: parseFloat(formData.target_protein) || 0,
        target_carbs: parseFloat(formData.target_carbs) || 0,
        target_fats: parseFloat(formData.target_fats) || 0
    };

    // Agregar arrays si existen
    if (formData.conditions && Array.isArray(formData.conditions)) {
        cleanData.conditions = formData.conditions;
    }
    if (formData.allergies && Array.isArray(formData.allergies)) {
        cleanData.allergies = formData.allergies;
    }

    console.log('Datos limpios:', cleanData);

    try {
        const response = await fetch('/nutricion-platform/api/health/save-profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify(cleanData)
        });

        console.log('Respuesta status:', response.status);
        
        const responseText = await response.text();
        console.log('Respuesta texto:', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Error al parsear JSON:', parseError);
            throw new Error('Respuesta del servidor no es JSON válido: ' + responseText.substring(0, 100));
        }

        console.log('Datos parseados:', data);

        if (data.success) {
            showNotification('¡Perfil creado exitosamente!', 'success');
            
            setTimeout(() => {
                window.location.href = '/nutricion-platform/public/dashboard.php';
            }, 1500);
        } else {
            throw new Error(data.error || 'Error desconocido al guardar perfil');
        }

    } catch (error) {
        console.error('Error completo:', error);
        showNotification('Error al guardar tu perfil: ' + error.message, 'error');
        if (loader) loader.classList.add('hidden');
    }
}

/**
 * Mostrar notificación
 */
function showNotification(message, type = 'info') {
    console.log('Notificación:', type, message);
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type} show`;
    
    const icons = {
        success: '<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
        error: '<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
        info: '<svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>'
    };
    
    notification.innerHTML = `
        ${icons[type] || icons.info}
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}