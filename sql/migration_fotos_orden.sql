-- Ampliar columna orden: TINYINT (max 255) no alcanza para estacionamiento (300) ni cocinas (200+)
-- Ejecutar: mariadb -u rentas -p rentas_cdmx < sql/migration_fotos_orden.sql

USE rentas_cdmx;

ALTER TABLE propiedad_fotos
    MODIFY orden SMALLINT UNSIGNED NOT NULL DEFAULT 0;
