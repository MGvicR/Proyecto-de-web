-- Plataforma de rentas CDMX
-- Ejecutar con MariaDB: mariadb -u root -p < sql/schema.sql
-- Alcaldías y colonias se cargan desde sql/seed_colonias.sql (incluido al final).

CREATE DATABASE IF NOT EXISTS rentas_cdmx
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE rentas_cdmx;

CREATE TABLE usuarios (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(100) NOT NULL,
    apellido        VARCHAR(100) NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    telefono        VARCHAR(20) NULL,
    password_hash   VARCHAR(255) NOT NULL,
    rol             ENUM('vendedor', 'admin', 'comprador') NOT NULL DEFAULT 'vendedor',
    activo          TINYINT(1) NOT NULL DEFAULT 1,
    creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en  DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE alcaldias (
    id      SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre  VARCHAR(80) NOT NULL UNIQUE
);

CREATE TABLE colonias (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    alcaldia_id   SMALLINT UNSIGNED NOT NULL,
    nombre        VARCHAR(120) NOT NULL,
    codigo_postal VARCHAR(10) NULL,
    CONSTRAINT fk_colonia_alcaldia
        FOREIGN KEY (alcaldia_id) REFERENCES alcaldias(id),
    UNIQUE KEY uk_colonia_alcaldia (alcaldia_id, nombre),
    INDEX idx_colonia_alcaldia (alcaldia_id)
);

CREATE TABLE propiedades (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vendedor_id             INT UNSIGNED NOT NULL,
    colonia_id              INT UNSIGNED NOT NULL,
    titulo                  VARCHAR(150) NOT NULL,
    slug                    VARCHAR(180) NOT NULL UNIQUE,
    descripcion             TEXT NOT NULL,
    tipo_inmueble           ENUM('casa', 'departamento') NOT NULL,
    recamaras               TINYINT UNSIGNED NOT NULL,
    banos                   TINYINT UNSIGNED NOT NULL,
    cocinas                 TINYINT UNSIGNED NOT NULL,
    tiene_estacionamiento   TINYINT(1) NOT NULL DEFAULT 0,
    permite_mascotas        TINYINT(1) NOT NULL DEFAULT 0,
    precio_mensual          DECIMAL(10,2) NOT NULL,
    deposito                DECIMAL(10,2) NULL,
    amueblado               TINYINT(1) NOT NULL DEFAULT 0,
    calle_referencia        VARCHAR(150) NULL,
    estado                  ENUM('borrador','pendiente','activa','rechazada','rentada','inactiva')
                            NOT NULL DEFAULT 'borrador',
    motivo_rechazo          VARCHAR(500) NULL,
    moderado_por            INT UNSIGNED NULL,
    moderado_en             DATETIME NULL,
    publicado_en            DATETIME NULL,
    creado_en               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en          DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_prop_vendedor   FOREIGN KEY (vendedor_id) REFERENCES usuarios(id),
    CONSTRAINT fk_prop_colonia    FOREIGN KEY (colonia_id) REFERENCES colonias(id),
    CONSTRAINT fk_prop_moderador  FOREIGN KEY (moderado_por) REFERENCES usuarios(id),
    CONSTRAINT chk_recamaras_min CHECK (recamaras >= 1),
    CONSTRAINT chk_banos_min     CHECK (banos >= 1),
    CONSTRAINT chk_cocinas_min   CHECK (cocinas >= 1),
    INDEX idx_busqueda (estado, tipo_inmueble, recamaras, banos, cocinas, tiene_estacionamiento, permite_mascotas),
    INDEX idx_pendientes (estado, creado_en),
    INDEX idx_prop_colonia (colonia_id),
    INDEX idx_prop_vendedor (vendedor_id)
);

CREATE TABLE propiedad_fotos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    propiedad_id    INT UNSIGNED NOT NULL,
    ruta_archivo    VARCHAR(255) NOT NULL,
    nombre_original VARCHAR(150) NULL,
    tipo            ENUM('recamara', 'bano', 'cocina', 'estacionamiento') NOT NULL,
    numero          TINYINT UNSIGNED NOT NULL DEFAULT 1,
    es_principal    TINYINT(1) NOT NULL DEFAULT 0,
    orden           SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_foto_propiedad
        FOREIGN KEY (propiedad_id) REFERENCES propiedades(id) ON DELETE CASCADE,
    UNIQUE KEY uk_foto_espacio (propiedad_id, tipo, numero),
    INDEX idx_fotos_propiedad (propiedad_id, tipo, numero)
);

CREATE TABLE contactos (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    propiedad_id     INT UNSIGNED NOT NULL,
    vendedor_id      INT UNSIGNED NOT NULL,
    comprador_id     INT UNSIGNED NULL,
    nombre_visitante VARCHAR(100) NOT NULL,
    email            VARCHAR(150) NULL,
    telefono         VARCHAR(20) NOT NULL,
    mensaje          TEXT NULL,
    leido            TINYINT(1) NOT NULL DEFAULT 0,
    creado_en        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_contacto_propiedad FOREIGN KEY (propiedad_id) REFERENCES propiedades(id),
    CONSTRAINT fk_contacto_vendedor  FOREIGN KEY (vendedor_id) REFERENCES usuarios(id),
    CONSTRAINT fk_contacto_comprador FOREIGN KEY (comprador_id) REFERENCES usuarios(id),
    INDEX idx_contacto_vendedor (vendedor_id, leido),
    INDEX idx_contacto_comprador (comprador_id)
);

CREATE TABLE calificaciones_vendedor (
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

CREATE TABLE historial_moderacion (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    propiedad_id    INT UNSIGNED NOT NULL,
    admin_id        INT UNSIGNED NOT NULL,
    accion          ENUM('aprobada', 'rechazada', 'desactivada') NOT NULL,
    estado_anterior ENUM('borrador','pendiente','activa','rechazada','rentada','inactiva') NOT NULL,
    estado_nuevo    ENUM('borrador','pendiente','activa','rechazada','rentada','inactiva') NOT NULL,
    comentario      VARCHAR(500) NULL,
    creado_en       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_hist_propiedad FOREIGN KEY (propiedad_id) REFERENCES propiedades(id),
    CONSTRAINT fk_hist_admin     FOREIGN KEY (admin_id) REFERENCES usuarios(id),
    INDEX idx_hist_propiedad (propiedad_id)
);

-- Admin: Administrador@gmail.com / Admin123!
INSERT INTO usuarios (nombre, apellido, email, telefono, password_hash, rol) VALUES
    ('Administrador', 'Sistema', 'Administrador@gmail.com', '5555555555',
     '$2b$10$8AmoZL.KhlrUcNzJL6qON.JFkugyz9Acc5JNzNBsXSS/SCFxovBNe', 'admin');

INSERT INTO alcaldias (id, nombre) VALUES
    (1, 'Álvaro Obregón'),
    (2, 'Azcapotzalco'),
    (3, 'Benito Juárez'),
    (4, 'Coyoacán'),
    (5, 'Cuajimalpa de Morelos'),
    (6, 'Cuauhtémoc'),
    (7, 'Gustavo A. Madero'),
    (8, 'Iztacalco'),
    (9, 'Iztapalapa'),
    (10, 'La Magdalena Contreras'),
    (11, 'Miguel Hidalgo'),
    (12, 'Milpa Alta'),
    (13, 'Tláhuac'),
    (14, 'Tlalpan'),
    (15, 'Venustiano Carranza'),
    (16, 'Xochimilco')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO colonias (id, alcaldia_id, nombre, codigo_postal) VALUES
    (1, 3, 'Del Valle Centro', '03100'),
    (2, 3, 'Nápoles', '03810'),
    (3, 3, 'Narvarte Poniente', '03020'),
    (4, 4, 'Copilco Universidad', '04360'),
    (5, 4, 'Del Carmen', '04100'),
    (6, 4, 'Campestre Churubusco', '04200'),
    (7, 6, 'Roma Norte', '06700'),
    (8, 6, 'Condesa', '06140'),
    (9, 6, 'Centro Histórico', '06000'),
    (10, 11, 'Polanco', '11560'),
    (11, 11, 'Granada', '11520'),
    (12, 11, 'Anáhuac', '11320'),
    (13, 14, 'Pedregal de San Ángel', '04500'),
    (14, 14, 'Fuentes del Pedregal', '14140'),
    (15, 1, 'San Ángel', '01000'),
    (16, 1, 'Florida', '01030'),
    (17, 2, 'Clavería', '02080'),
    (18, 2, 'Santo Domingo', '02160'),
    (19, 2, 'Azcapotzalco Centro', '02000'),
    (20, 5, 'Cuajimalpa', '05000'),
    (21, 5, 'Contadero', '05500'),
    (22, 5, 'San Mateo Tlaltenango', '05600'),
    (23, 7, 'Lindavista', '07300'),
    (24, 7, 'Buenavista', '06350'),
    (25, 7, 'Tepeyac Insurgentes', '07020'),
    (26, 8, 'Agrícola Oriental', '08500'),
    (27, 8, 'Pantitlán', '08100'),
    (28, 8, 'Viaducto Piedad', '08200'),
    (29, 9, 'Iztapalapa Centro', '09000'),
    (30, 9, 'Culhuacán', '09800'),
    (31, 9, 'Santa Cruz Meyehualco', '09290'),
    (32, 10, 'San Jerónimo Lídice', '10200'),
    (33, 10, 'La Fama', '10100'),
    (34, 10, 'Las Águilas', '01710'),
    (35, 12, 'Villa Milpa Alta', '12000'),
    (36, 12, 'San Pablo Oztotepec', '12400'),
    (37, 12, 'San Antonio Tecomitl', '12200'),
    (38, 13, 'Tláhuac Centro', '13300'),
    (39, 13, 'San Pedro Tláhuac', '13400'),
    (40, 13, 'San Francisco Tlaltenco', '13420'),
    (41, 15, 'Moctezuma', '15530'),
    (42, 15, 'Jamaica', '15800'),
    (43, 15, 'Pensador Mexicano', '15900'),
    (44, 16, 'Xochimilco Centro', '16070'),
    (45, 16, 'San Gregorio Atlapulco', '16650'),
    (46, 16, 'Santiago Tepalcatlapan', '16200')
ON DUPLICATE KEY UPDATE
    alcaldia_id = VALUES(alcaldia_id),
    nombre = VALUES(nombre),
    codigo_postal = VALUES(codigo_postal);
