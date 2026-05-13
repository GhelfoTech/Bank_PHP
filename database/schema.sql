-- Base de datos para BANCO - PHP (XAMPP / MySQL)
-- Ejecutar en phpMyAdmin o: mysql -u root < database/schema.sql

CREATE DATABASE IF NOT EXISTS banco_php
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE banco_php;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'cliente') NOT NULL,
    estado TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cuentas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    numero_cuenta VARCHAR(32) NOT NULL UNIQUE,
    saldo DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    estado TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_cuentas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS movimientos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cuenta_id INT UNSIGNED NOT NULL,
    tipo VARCHAR(32) NOT NULL,
    monto DECIMAL(12, 2) NOT NULL,
    fecha DATETIME NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    CONSTRAINT fk_movimientos_cuenta FOREIGN KEY (cuenta_id) REFERENCES cuentas (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- Datos iniciales (panel de demostración).
-- Contraseña de ambos usuarios de prueba: password
-- (hash bcrypt compatible con password_verify en PHP).
INSERT INTO usuarios (id, cedula, nombres, email, password, rol, estado) VALUES
    (1, '0102030405', 'Ana Admin', 'ana.admin@banco.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', 1),
    (2, '1919191919', 'Carlos Cliente', 'carlos.cliente@banco.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', 1)
ON DUPLICATE KEY UPDATE
    cedula = VALUES(cedula),
    nombres = VALUES(nombres),
    email = VALUES(email),
    password = VALUES(password),
    rol = VALUES(rol),
    estado = VALUES(estado);

INSERT INTO cuentas (id, usuario_id, numero_cuenta, saldo, estado) VALUES
    (1, 2, '001-000001', 400.00, 1),
    (2, 2, '001-000002', 50.00, 1)
ON DUPLICATE KEY UPDATE
    usuario_id = VALUES(usuario_id),
    saldo = VALUES(saldo),
    estado = VALUES(estado);
