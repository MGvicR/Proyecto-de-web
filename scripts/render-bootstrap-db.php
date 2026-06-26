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

function ejecutarSchema(PDO $pdo, string $sql): void
{
    try {
        $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);
        $pdo->exec($sql);
        while ($pdo->nextRowset()) {
            // Vaciar result sets pendientes.
        }

        return;
    } catch (PDOException) {
        // Fallback: ejecutar sentencia por sentencia.
    }

    $buffer = '';
    foreach (explode("\n", $sql) as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '--')) {
            continue;
        }

        $buffer .= $line . "\n";
        if (!str_ends_with(rtrim($line), ';')) {
            continue;
        }

        $statement = trim($buffer);
        $buffer = '';
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }
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

    ejecutarSchema($pdo, $sql);

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
