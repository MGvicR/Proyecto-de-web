<?php

declare(strict_types=1);

function propiedadSearchFiltersFromRequest(): array
{
    return [
        'alcaldia_id' => get('alcaldia_id', ''),
        'colonia_id' => get('colonia_id', ''),
        'tipo_inmueble' => get('tipo_inmueble', ''),
        'categoria' => get('categoria', ''),
        'vendedor_id' => get('vendedor_id', ''),
        'publicado_desde' => get('publicado_desde', ''),
        'publicado_hasta' => get('publicado_hasta', ''),
        'recamaras' => get('recamaras', ''),
        'banos' => get('banos', ''),
        'cocinas' => get('cocinas', ''),
        'tiene_estacionamiento' => get('tiene_estacionamiento', ''),
        'permite_mascotas' => get('permite_mascotas', ''),
        'amueblado' => get('amueblado', ''),
        'valoracion_min' => get('valoracion_min', ''),
    ];
}

function propiedadCategoriasBusqueda(): array
{
    return [
        'amueblado' => 'Amueblado',
        'estacionamiento' => 'Con estacionamiento',
        'mascotas' => 'Acepta mascotas',
        'premium' => 'Premium (desde $20,000)',
        'economico' => 'Económico (hasta $12,000)',
    ];
}

function categoriaBusquedaLabel(string $slug): string
{
    return propiedadCategoriasBusqueda()[$slug] ?? ucfirst($slug);
}

function vendedorDisplayName(array $vendedor): string
{
    $nombre = trim((string) ($vendedor['nombre'] ?? $vendedor['vendedor_nombre'] ?? ''));
    $apellido = trim((string) ($vendedor['apellido'] ?? $vendedor['vendedor_apellido'] ?? ''));

    return trim($nombre . ' ' . $apellido);
}

function isValidDateFilter(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

    if ($date === false || $date->format('Y-m-d') !== $value) {
        return false;
    }

    return true;
}
