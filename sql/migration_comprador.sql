-- Rol comprador y vínculo en contactos
-- Ejecutar: mariadb -u rentas -p rentas_cdmx < sql/migration_comprador.sql

USE rentas_cdmx;

ALTER TABLE usuarios
    MODIFY rol ENUM('vendedor', 'admin', 'comprador') NOT NULL DEFAULT 'vendedor';

ALTER TABLE contactos
    ADD COLUMN comprador_id INT UNSIGNED NULL AFTER vendedor_id;

ALTER TABLE contactos
    ADD CONSTRAINT fk_contacto_comprador FOREIGN KEY (comprador_id) REFERENCES usuarios(id);

ALTER TABLE contactos
    ADD INDEX idx_contacto_comprador (comprador_id);
