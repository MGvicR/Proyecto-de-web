-- Colonias de CDMX para rentas_cdmx
-- También incluido al final de sql/schema.sql
-- Ejecutar por separado si solo faltan ubicaciones:
-- mariadb -u rentas -p rentas_cdmx < sql/seed_colonias.sql

USE rentas_cdmx;

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
