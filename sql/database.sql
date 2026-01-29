-- =============================================
-- BASE DE DATOS: PLATAFORMA DE NUTRICIÓN
-- =============================================

CREATE DATABASE IF NOT EXISTS nutricion_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nutricion_platform;

-- =============================================
-- TABLA: users (Usuarios)
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    google_id VARCHAR(255) UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    picture VARCHAR(500),
    locale VARCHAR(10) DEFAULT 'es',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1,
    INDEX idx_email (email),
    INDEX idx_google_id (google_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: health_profiles (Perfiles de Salud)
-- =============================================
CREATE TABLE health_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    weight DECIMAL(5,2),
    height DECIMAL(5,2),
    age INT,
    gender ENUM('male', 'female', 'other'),
    activity_level ENUM('sedentary', 'light', 'moderate', 'active', 'very_active') DEFAULT 'sedentary',
    health_conditions TEXT,
    dietary_preferences TEXT,
    allergies TEXT,
    goal ENUM('lose_weight', 'maintain', 'gain_weight', 'muscle_gain') DEFAULT 'maintain',
    target_calories INT,
    target_protein DECIMAL(5,2),
    target_carbs DECIMAL(5,2),
    target_fats DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: meals (Comidas base)
-- =============================================
CREATE TABLE meals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    meal_type ENUM('breakfast', 'lunch', 'dinner', 'snack') NOT NULL,
    image_url VARCHAR(500),
    calories INT NOT NULL,
    protein DECIMAL(5,2) NOT NULL,
    carbs DECIMAL(5,2) NOT NULL,
    fats DECIMAL(5,2) NOT NULL,
    fiber DECIMAL(5,2),
    preparation_time INT,
    ingredients TEXT,
    instructions TEXT,
    is_vegetarian TINYINT(1) DEFAULT 0,
    is_vegan TINYINT(1) DEFAULT 0,
    is_gluten_free TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_meal_type (meal_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: meal_plans (Planes de comida personalizados)
-- =============================================
CREATE TABLE meal_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    meal_type ENUM('breakfast', 'lunch', 'dinner', 'snack') NOT NULL,
    meal_id INT NOT NULL,
    scheduled_time TIME,
    is_completed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (meal_id) REFERENCES meals(id) ON DELETE CASCADE,
    UNIQUE KEY unique_meal_plan (user_id, date, meal_type),
    INDEX idx_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: calories_log (Registro de calorías)
-- =============================================
CREATE TABLE calories_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    meal_plan_id INT,
    date DATE NOT NULL,
    calories INT NOT NULL,
    protein DECIMAL(5,2),
    carbs DECIMAL(5,2),
    fats DECIMAL(5,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (meal_plan_id) REFERENCES meal_plans(id) ON DELETE SET NULL,
    INDEX idx_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: shopping_lists (Listas de compras)
-- =============================================
CREATE TABLE shopping_lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    category ENUM('fruits', 'vegetables', 'proteins', 'grains', 'dairy', 'other') DEFAULT 'other',
    quantity VARCHAR(50),
    is_checked TINYINT(1) DEFAULT 0,
    week_start DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_week (user_id, week_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: products (Productos para venta)
-- =============================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    benefits TEXT,
    price DECIMAL(10,2) NOT NULL,
    image_url VARCHAR(500),
    category VARCHAR(100),
    stock INT DEFAULT 0,
    is_available TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: user_settings (Configuración de usuario)
-- =============================================
CREATE TABLE user_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    region VARCHAR(50) DEFAULT 'MX',
    language VARCHAR(10) DEFAULT 'es',
    weight_unit ENUM('kg', 'lb') DEFAULT 'kg',
    height_unit ENUM('cm', 'ft') DEFAULT 'cm',
    calorie_unit ENUM('kcal', 'kj') DEFAULT 'kcal',
    theme ENUM('light', 'dark', 'auto') DEFAULT 'light',
    notifications_enabled TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: chat_messages (Mensajes del asistente)
-- =============================================
CREATE TABLE chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_user TINYINT(1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DATOS DE EJEMPLO: Comidas
-- =============================================
INSERT INTO meals (name, description, meal_type, image_url, calories, protein, carbs, fats, preparation_time, ingredients, is_vegetarian, is_vegan) VALUES
('Avena con Frutas', 'Avena integral con plátano, fresas y miel', 'breakfast', 'https://images.unsplash.com/photo-1517673400267-0251440c45dc?w=800', 320, 12.5, 58.0, 6.5, 10, 'Avena, leche, plátano, fresas, miel, chía', 1, 0),
('Huevos Revueltos con Aguacate', 'Huevos revueltos con aguacate y pan integral', 'breakfast', 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=800', 380, 24.0, 28.0, 18.0, 15, 'Huevos, aguacate, pan integral, aceite de oliva', 1, 0),
('Ensalada de Pollo', 'Ensalada verde con pechuga de pollo a la plancha', 'lunch', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=800', 420, 38.0, 25.0, 18.0, 20, 'Lechuga, pollo, tomate, pepino, aceite de oliva', 0, 0),
('Salmón con Verduras', 'Salmón al horno con brócoli y zanahoria', 'dinner', 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=800', 480, 42.0, 22.0, 24.0, 30, 'Salmón, brócoli, zanahoria, limón, aceite de oliva', 0, 0);
```

---

## 📁 PARTE 3: ESTRUCTURA DE CARPETAS

Crea esta estructura en tu Hostinger (en `public_html` o la carpeta que uses):
```
nutricion-platform/
│
├── public/
│   ├── index.php
│   ├── login.php
│   └── dashboard.php
│
├── assets/
│   ├── css/
│   │   ├── main.css
│   │   └── dashboard.css
│   │
│   ├── js/
│   │   ├── auth.js
│   │   └── dashboard.js
│   │
│   └── images/
│       └── logo.svg
│
├── api/
│   ├── auth/
│   │   ├── google.php
│   │   └── logout.php
│   │
│   └── user/
│       └── profile.php
│
├── config/
│   ├── database.php
│   ├── google.php
│   └── jwt.php
│
├── middleware/
│   └── auth.php
│
├── sql/
│   └── database.sql
│
└── .env