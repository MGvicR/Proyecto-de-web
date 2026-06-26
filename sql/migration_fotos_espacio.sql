-- Fotos por espacio: 1 foto por recámara, baño, cocina y estacionamiento (si aplica)
-- Ejecutar: mariadb -u rentas -p rentas_cdmx < sql/migration_fotos_espacio.sql

USE rentas_cdmx;

ALTER TABLE propiedad_fotos
    ADD COLUMN tipo ENUM('recamara', 'bano', 'cocina', 'estacionamiento')
        NOT NULL DEFAULT 'recamara' AFTER nombre_original,
    ADD COLUMN numero TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER tipo,
    ADD UNIQUE KEY uk_foto_espacio (propiedad_id, tipo, numero);
