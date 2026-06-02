-- ============================================
-- Task Manager - Script de creación de BD
-- Ejecutar en phpMyAdmin o consola MySQL de XAMPP
-- ============================================

CREATE DATABASE IF NOT EXISTS task_manager
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE task_manager;

-- ============================================
-- Tabla: proyectos
-- ============================================
CREATE TABLE IF NOT EXISTS proyectos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(255) NOT NULL,
    descripcion TEXT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Tabla: tareas
-- ============================================
CREATE TABLE IF NOT EXISTS tareas (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    titulo            VARCHAR(255) NOT NULL,
    descripcion       TEXT NULL,
    estado            ENUM('pendiente', 'en_progreso', 'completada') DEFAULT 'pendiente',
    proyecto_id       INT NULL,
    fecha_vencimiento DATE NULL,
    created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_tareas_proyecto
        FOREIGN KEY (proyecto_id)
        REFERENCES proyectos(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
