-- ================================================
-- SISTEMA DE ESTACIONAMIENTO PHP
-- Script SQL para Base de Datos y Tabla Usuarios
-- ================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS estacionamiento_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE estacionamiento_db;

-- ================================================
-- TABLA: usuarios
-- Descripción: Gestión de usuarios del sistema
-- ================================================
CREATE TABLE usuarios (
    id INT(11) NOT NULL AUTO_INCREMENT,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    rol ENUM('Administrador', 'Operador') NOT NULL DEFAULT 'Operador',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    intentos_fallidos INT(3) DEFAULT 0,
    bloqueado_hasta TIMESTAMP NULL,
    PRIMARY KEY (id),
    INDEX idx_usuario (usuario),
    INDEX idx_rol (rol),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- DATOS INICIALES - USUARIOS DE PRUEBA
-- ================================================

-- Usuario Administrador (password: admin123)
INSERT INTO usuarios (usuario, password, nombre, rol, activo) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador del Sistema', 'Administrador', 1);

-- Usuario Operador (password: operador123)  
INSERT INTO usuarios (usuario, password, nombre, rol, activo) VALUES 
('operador1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez', 'Operador', 1);

-- Usuario Operador adicional (password: operador123)
INSERT INTO usuarios (usuario, password, nombre, rol, activo) VALUES 
('operador2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María García', 'Operador', 1);

-- ================================================
-- TABLA: sesiones (para control de sesiones)
-- ================================================
CREATE TABLE sesiones (
    id VARCHAR(128) NOT NULL,
    usuario_id INT(11) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    payload LONGTEXT NOT NULL,
    ultimo_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_ultimo_activity (ultimo_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================
-- CONFIGURACIONES ADICIONALES
-- ================================================

-- Configurar zona horaria
SET time_zone = '-05:00'; -- Hora de Lima, Perú

-- Verificar instalación
SELECT 
    'Base de datos creada correctamente' as mensaje,
    COUNT(*) as total_usuarios 
FROM usuarios;

-- Mostrar usuarios creados
SELECT 
    id,
    usuario,
    nombre,
    rol,
    activo,
    fecha_creacion
FROM usuarios
ORDER BY id;