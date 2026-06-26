<?php

declare(strict_types=1);

function sitioConfigDefaults(): array
{
    return [
        'hero_titulo' => 'Encuentra tu lugar ideal',
        'hero_subtitulo' => 'Propiedades exclusivas en renta en las mejores zonas de Ciudad de México.',
    ];
}

function sitioConfig(string $clave, ?string $default = null): string
{
    static $cache = null;

    if ($cache === null) {
        $cache = sitioConfigCargar();
    }

    if ($default !== null) {
        return (string) ($cache[$clave] ?? $default);
    }

    return (string) ($cache[$clave] ?? sitioConfigDefaults()[$clave] ?? '');
}

function sitioConfigCargar(): array
{
    $cache = sitioConfigDefaults();

    try {
        $stmt = getDB()->query('SELECT clave, valor FROM configuracion_sitio');
        foreach ($stmt->fetchAll() as $row) {
            $cache[$row['clave']] = $row['valor'];
        }
    } catch (PDOException) {
        // Tabla aún no creada.
    }

    return $cache;
}

function sitioConfigGuardar(array $valores): void
{
    $stmt = getDB()->prepare(
        'INSERT INTO configuracion_sitio (clave, valor)
         VALUES (?, ?)
         ON DUPLICATE KEY UPDATE valor = VALUES(valor)'
    );

    foreach ($valores as $clave => $valor) {
        if (!array_key_exists($clave, sitioConfigDefaults())) {
            continue;
        }
        $stmt->execute([$clave, trim((string) $valor)]);
    }
}
