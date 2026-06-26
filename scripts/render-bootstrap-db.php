#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Crea las tablas en Aiven si aún no existen (Render).
 * Se ejecuta al arrancar el contenedor.
 */

require_once dirname(__DIR__) . '/app/config/database.php';

function tablaExiste(PDO $pdo, string $tabla): bool
{
    $stmt = $pdo->query('SHOW TABLES');
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return in_array($tabla, $tablas, true);
}

try {
    $pdo = getDB();

    if (tablaExiste($pdo, 'propiedades')) {
        fwrite(STDOUT, "Bootstrap DB: tablas ya existen.\n");
        exit(0);
    }

    $schemaFile = dirname(__DIR__) . '/sql/schema_aiven.sql';
    if (!is_readable($schemaFile)) {
        fwrite(STDERR, "Bootstrap DB: no se encontró {$schemaFile}\n");
        exit(1);
    }

    $sql = file_get_contents($schemaFile);
    if ($sql === false || trim($sql) === '') {
        fwrite(STDERR, "Bootstrap DB: schema_aiven.sql vacío.\n");
        exit(1);
    }

    $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);
    $pdo->exec($sql);

    do {
        // Consumir todos los result sets del script multi-statement.
    } while ($pdo->nextRowset());

    if (!tablaExiste($pdo, 'propiedades')) {
        fwrite(STDERR, "Bootstrap DB: importación terminó pero falta la tabla propiedades.\n");
        exit(1);
    }

    fwrite(STDOUT, "Bootstrap DB: tablas creadas correctamente.\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, 'Bootstrap DB: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
