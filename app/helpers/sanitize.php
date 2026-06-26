<?php

declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = strtolower((string) $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'propiedad';
}

function post(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $default;
}

function get(string $key, mixed $default = null): mixed
{
    return $_GET[$key] ?? $default;
}

function url(string $path = ''): string
{
    if ($path === '') {
        return BASE_URL . '/index.php';
    }

    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    return BASE_URL . '/' . ltrim($path, '/');
}

function assetUrl(string $path): string
{
    $publicFile = PUBLIC_PATH . '/' . ltrim($path, '/');
    $version = file_exists($publicFile) ? (string) filemtime($publicFile) : (string) time();

    return url($path) . '?v=' . $version;
}

function redirect(string $target): never
{
    if (str_starts_with($target, 'http://') || str_starts_with($target, 'https://')) {
        header('Location: ' . $target);
    } elseif (str_starts_with($target, '/')) {
        header('Location: ' . url($target));
    } else {
        header('Location: ' . $target);
    }

    exit;
}

function formatMoney(float|string $amount): string
{
    return '$' . number_format((float) $amount, 0, '.', ',');
}

function boolLabel(int|bool $value): string
{
    return $value ? 'Sí' : 'No';
}

function estadoLabel(string $estado): string
{
    return match ($estado) {
        'borrador' => 'Borrador',
        'pendiente' => 'Pendiente de revisión',
        'activa' => 'Activa',
        'rechazada' => 'Rechazada',
        'rentada' => 'Rentada',
        'inactiva' => 'Dada de baja',
        default => $estado,
    };
}

function fotoEspacioLabel(string $tipo, int $numero): string
{
    return match ($tipo) {
        'recamara' => 'Recámara ' . $numero,
        'bano' => 'Baño ' . $numero,
        'cocina' => 'Cocina ' . $numero,
        'estacionamiento' => 'Estacionamiento',
        default => 'Foto',
    };
}

function contactoEstadoLabel(int|bool $leido): string
{
    return $leido ? 'Contestado' : 'Pendiente';
}

function rolLabel(string $rol): string
{
    return match ($rol) {
        'admin' => 'Administrador',
        'vendedor' => 'Vendedor',
        'comprador' => 'Comprador',
        default => ucfirst($rol),
    };
}

function estrellasLabel(int $estrellas): string
{
    $estrellas = max(1, min(5, $estrellas));

    return str_repeat('★', $estrellas) . str_repeat('☆', 5 - $estrellas);
}

function normalizarTelefono(string $telefono): string
{
    return preg_replace('/\D+/', '', $telefono) ?? '';
}

function telefonoValido(string $telefono, bool $requerido = false): bool
{
    if ($telefono === '') {
        return !$requerido;
    }

    return (bool) preg_match('/^\d+$/', $telefono) && strlen($telefono) <= 20;
}
