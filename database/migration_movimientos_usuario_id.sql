-- Ejecutar si ya tenías la BD sin la columna usuario_id en movimientos.
USE banco_php;

ALTER TABLE movimientos
    ADD COLUMN usuario_id INT UNSIGNED NOT NULL DEFAULT 0 AFTER cuenta_id;

UPDATE movimientos m
INNER JOIN cuentas c ON c.id = m.cuenta_id
SET m.usuario_id = c.usuario_id;

ALTER TABLE movimientos
    ADD CONSTRAINT fk_movimientos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON UPDATE CASCADE ON DELETE RESTRICT;
