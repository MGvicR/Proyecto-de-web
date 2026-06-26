<?php

declare(strict_types=1);

/**
 * Instalación única de tablas en Render + Aiven.
 * Visita una vez: /setup-db.php
 * Borra este archivo cuando el sitio funcione.
 */

require_once dirname(__DIR__) . '/app/config/database.php';

header('Content-Type: text/html; charset=UTF-8');

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
        }

        return;
    } catch (PDOException) {
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

echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Setup DB</title></head><body>';

try {
    $pdo = getDB();

    if (tablaExiste($pdo, 'propiedades')) {
        echo '<h1>Base de datos lista</h1><p>Las tablas ya existen. Abre <a href="index.php">index.php</a>.</p>';
        echo '<p>Puedes borrar <code>public/setup-db.php</code> del servidor.</p>';
        echo '</body></html>';
        exit;
    }

    $schemaFile = dirname(__DIR__) . '/sql/schema_aiven.sql';
    if (!is_readable($schemaFile)) {
        throw new RuntimeException('No se encontró sql/schema_aiven.sql');
    }

    $sql = file_get_contents($schemaFile);
    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException('schema_aiven.sql está vacío');
    }

    ejecutarSchema($pdo, $sql);

    if (!tablaExiste($pdo, 'propiedades')) {
        throw new RuntimeException('Importación terminó pero falta la tabla propiedades');
    }

    echo '<h1>Tablas creadas correctamente</h1>';
    echo '<p>Abre <a href="index.php">la página de inicio</a>.</p>';
    echo '<p>Admin: Administrador@gmail.com / Admin123!</p>';
    echo '<p><strong>Borra</strong> <code>public/setup-db.php</code> cuando confirmes que todo funciona.</p>';
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Error al crear tablas</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
}

echo '</body></html>';
