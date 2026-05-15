CREATE DATABASE IF NOT EXISTS taller_cookies
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE taller_cookies;

CREATE TABLE IF NOT EXISTS usuarios (
    usuario_id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'usuario') DEFAULT 'usuario',
    activo TINYINT(1) DEFAULT 1,
    visitas INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
