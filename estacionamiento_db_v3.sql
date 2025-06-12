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


select * from usuarios;



-- ================================================
-- CORREGIR CONTRASEÑAS DE USUARIOS
-- Script SQL para actualizar las contraseñas correctas
-- ================================================

USE estacionamiento_db;

-- Actualizar contraseña del administrador (admin123)
UPDATE usuarios 
SET password = '$2y$10$YourHashedPasswordHere' 
WHERE usuario = 'admin';

-- Actualizar contraseña de operador1 (operador123)  
UPDATE usuarios 
SET password = '$2y$10$YourHashedPasswordHere' 
WHERE usuario = 'operador1';

-- Actualizar contraseña de operador2 (operador123)
UPDATE usuarios 
SET password = '$2y$10$YourHashedPasswordHere' 
WHERE usuario = 'operador2';

-- ================================================
-- ALTERNATIVA: INSERTAR USUARIOS CON CONTRASEÑAS CORRECTAS
-- (Ejecutar solo si prefieres recrear los usuarios)
-- ================================================

-- Eliminar usuarios existentes (opcional)
-- DELETE FROM usuarios WHERE usuario IN ('admin', 'operador1', 'operador2');

-- Insertar usuarios con contraseñas correctas
-- Estas serán generadas por el script PHP
-- Usuario: admin, Contraseña: admin123
-- Usuario: operador1, Contraseña: operador123
-- Usuario: operador2, Contraseña: operador123

-- ================================================
-- VERIFICAR USUARIOS EXISTENTES
-- ================================================
SELECT 
    id,
    usuario,
    nombre,
    rol,
    activo,
    DATE_FORMAT(fecha_creacion, '%d/%m/%Y %H:%i') as fecha_creacion,
    CASE 
        WHEN LENGTH(password) > 50 THEN 'Hash válido'
        ELSE 'Hash inválido'
    END as estado_password
FROM usuarios
ORDER BY id;

USE estacionamiento_db;
UPDATE usuarios SET password = '$2y$10$CUcnLI41POL0s4c0MYm/UezmdgkOG2bhKhjXD2uyr5bmv.KAuKu5m' WHERE usuario = 'admin';
UPDATE usuarios SET password = '$2y$10$mKn9tVOeln9zKLkqR5NrIeQMhVmKPNzUqnvoZ6GgDCp5Pv/7eJ26m' WHERE usuario = 'operador1';
UPDATE usuarios SET password = '$2y$10$mKn9tVOeln9zKLkqR5NrIeQMhVmKPNzUqnvoZ6GgDCp5Pv/7eJ26m' WHERE usuario = 'operador2';
SELECT usuario, 'Password actualizado' as estado FROM usuarios;



-- Script para crear las tablas necesarias para el sistema de vehículos

-- Tabla de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_cliente VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    direccion TEXT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de vehículos
CREATE TABLE IF NOT EXISTS vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(20) UNIQUE NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    color VARCHAR(50) NOT NULL,
    tipo_vehiculo ENUM('auto', 'moto', 'camioneta', 'bus', 'otro') DEFAULT 'auto',
    cliente_id INT,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
);

-- Tabla de espacios de estacionamiento
CREATE TABLE IF NOT EXISTS espacios_estacionamiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_espacio VARCHAR(10) UNIQUE NOT NULL,
    tipo_espacio ENUM('auto', 'moto', 'discapacitado', 'vip') DEFAULT 'auto',
    estado ENUM('disponible', 'ocupado', 'reservado', 'mantenimiento') DEFAULT 'disponible',
    tarifa_por_hora DECIMAL(6,2) DEFAULT 3.00,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de registros de estacionamiento
CREATE TABLE IF NOT EXISTS registros_estacionamiento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vehiculo_id INT NOT NULL,
    espacio_id INT NOT NULL,
    fecha_entrada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_salida TIMESTAMP NULL,
    tiempo_total_minutos INT NULL,
    tarifa_aplicada DECIMAL(6,2) NOT NULL,
    monto_total DECIMAL(8,2) NULL,
    estado ENUM('activo', 'finalizado', 'cancelado') DEFAULT 'activo',
    observaciones TEXT,
    usuario_entrada INT,
    usuario_salida INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id),
    FOREIGN KEY (espacio_id) REFERENCES espacios_estacionamiento(id),
    FOREIGN KEY (usuario_entrada) REFERENCES usuarios(id),
    FOREIGN KEY (usuario_salida) REFERENCES usuarios(id)
);

-- Tabla de tarifas
CREATE TABLE IF NOT EXISTS tarifas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_vehiculo ENUM('auto', 'moto', 'camioneta', 'bus', 'otro') NOT NULL,
    tarifa_por_hora DECIMAL(6,2) NOT NULL,
    tarifa_fraccion DECIMAL(6,2) NOT NULL,
    minutos_gracia INT DEFAULT 15,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar espacios de estacionamiento (50 espacios)
INSERT INTO espacios_estacionamiento (numero_espacio, tipo_espacio, tarifa_por_hora) VALUES
('A01', 'auto', 3.00), ('A02', 'auto', 3.00), ('A03', 'auto', 3.00), ('A04', 'auto', 3.00), ('A05', 'auto', 3.00),
('A06', 'auto', 3.00), ('A07', 'auto', 3.00), ('A08', 'auto', 3.00), ('A09', 'auto', 3.00), ('A10', 'auto', 3.00),
('B01', 'auto', 3.00), ('B02', 'auto', 3.00), ('B03', 'auto', 3.00), ('B04', 'auto', 3.00), ('B05', 'auto', 3.00),
('B06', 'auto', 3.00), ('B07', 'auto', 3.00), ('B08', 'auto', 3.00), ('B09', 'auto', 3.00), ('B10', 'auto', 3.00),
('C01', 'auto', 3.00), ('C02', 'auto', 3.00), ('C03', 'auto', 3.00), ('C04', 'auto', 3.00), ('C05', 'auto', 3.00),
('C06', 'auto', 3.00), ('C07', 'auto', 3.00), ('C08', 'auto', 3.00), ('C09', 'auto', 3.00), ('C10', 'auto', 3.00),
('M01', 'moto', 2.00), ('M02', 'moto', 2.00), ('M03', 'moto', 2.00), ('M04', 'moto', 2.00), ('M05', 'moto', 2.00),
('M06', 'moto', 2.00), ('M07', 'moto', 2.00), ('M08', 'moto', 2.00), ('M09', 'moto', 2.00), ('M10', 'moto', 2.00),
('D01', 'discapacitado', 3.00), ('D02', 'discapacitado', 3.00),
('V01', 'vip', 5.00), ('V02', 'vip', 5.00), ('V03', 'vip', 5.00),
('T01', 'camioneta', 4.00), ('T02', 'camioneta', 4.00), ('T03', 'camioneta', 4.00), ('T04', 'camioneta', 4.00), ('T05', 'camioneta', 4.00);
SHOW CREATE TABLE espacios_estacionamiento;

ALTER TABLE espacios_estacionamiento 
MODIFY tipo_espacio ENUM('auto','moto','discapacitado','vip','camioneta') 
COLLATE utf8mb4_unicode_ci DEFAULT 'auto';
-- Insertar tarifas por defecto
INSERT INTO tarifas (tipo_vehiculo, tarifa_por_hora, tarifa_fraccion, minutos_gracia) VALUES
('auto', 3.00, 1.50, 15),
('moto', 2.00, 1.00, 15),
('camioneta', 4.00, 2.00, 15),
('bus', 6.00, 3.00, 10),
('otro', 3.50, 1.75, 15);

-- Insertar algunos clientes de ejemplo
INSERT INTO clientes (codigo_cliente, nombre, apellido, telefono, email) VALUES
('CLI001', 'Juan', 'Pérez', '987654321', 'juan.perez@email.com'),
('CLI002', 'María', 'González', '912345678', 'maria.gonzalez@email.com'),
('CLI003', 'Carlos', 'López', '923456789', 'carlos.lopez@email.com'),
('CLI004', 'Ana', 'Rodríguez', '934567890', 'ana.rodriguez@email.com'),
('CLI005', 'Luis', 'Martínez', '945678901', 'luis.martinez@email.com');

select * from tarifas;
select * from clientes;
select * from vehiculos;
select * from espacios_estacionamiento;
select * from registros_estacionamiento;

