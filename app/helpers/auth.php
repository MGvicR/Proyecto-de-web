<?php

declare(strict_types=1);

function loginUser(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'nombre' => $user['nombre'],
        'email' => $user['email'],
        'rol' => $user['rol'],
    ];
}

function logoutUser(): void
{
    unset($_SESSION['user']);
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function isAdmin(): bool
{
    $user = currentUser();

    return $user !== null && $user['rol'] === 'admin';
}

function isVendedor(): bool
{
    $user = currentUser();

    return $user !== null && $user['rol'] === 'vendedor';
}

function isComprador(): bool
{
    $user = currentUser();

    return $user !== null && $user['rol'] === 'comprador';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        flash('error', 'Debes iniciar sesión para continuar.');
        redirect('/login.php');
    }
}

function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        flash('error', 'No tienes permisos de administrador.');
        redirect('/index.php');
    }
}

function requireVendedor(): void
{
    requireLogin();
    if (!isVendedor()) {
        flash('error', 'Esta sección es solo para vendedores.');
        redirect('/index.php');
    }
}

function requireComprador(): void
{
    requireLogin();
    if (!isComprador()) {
        flash('error', 'Esta sección es solo para compradores.');
        redirect('/index.php');
    }
}

function redirectByRole(?array $user = null): never
{
    $sessionUser = currentUser();
    $rol = $user['rol'] ?? ($sessionUser !== null ? $sessionUser['rol'] : null);

    match ($rol) {
        'admin' => redirect('/admin/index.php'),
        'vendedor' => redirect('/vendedor/index.php'),
        'comprador' => redirect('/comprador/index.php'),
        default => redirect('/index.php'),
    };
}

function ensureActiveSession(): void
{
    if (!isLoggedIn()) {
        return;
    }

    $dbUser = Usuario::findById(currentUser()['id']);
    if (!$dbUser || !(int) $dbUser['activo']) {
        logoutUser();
        flash('error', 'Tu sesión expiró o la cuenta fue desactivada.');
        redirect('/login.php');
    }
}
