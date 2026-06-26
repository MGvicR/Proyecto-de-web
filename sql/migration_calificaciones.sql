-- Calificaciones de vendedor por compradores
-- Ejecutar: mariadb -u rentas -p rentas_cdmx < sql/migration_calificaciones.sql

USE rentas_cdmx;

CREATE TABLE IF NOT EXISTS calificaciones_vendedor (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contacto_id     INT UNSIGNED NOT NULL,
    vendedor_id     INT UNSIGNED NOT NULL,
    comprador_id    INT UNSIGNED NOT NULL,
    propiedad_id    INT UNSIGNED NOT NULL,
    estrellas       TINYINT UNSIGNED NOT NULL,
    comentario      TEXT NULL,
    creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_calif_contacto   FOREIGN KEY (contacto_id) REFERENCES contactos(id),
    CONSTRAINT fk_calif_vendedor   FOREIGN KEY (vendedor_id) REFERENCES usuarios(id),
    CONSTRAINT fk_calif_comprador  FOREIGN KEY (comprador_id) REFERENCES usuarios(id),
    CONSTRAINT fk_calif_propiedad  FOREIGN KEY (propiedad_id) REFERENCES propiedades(id),
    CONSTRAINT chk_calif_estrellas CHECK (estrellas BETWEEN 1 AND 5),
    UNIQUE KEY uk_calif_contacto (contacto_id),
    INDEX idx_calif_vendedor (vendedor_id)
);
