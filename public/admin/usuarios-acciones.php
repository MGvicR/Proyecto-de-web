<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/usuarios.php');
}

verifyCsrf();

$admin = currentUser();
$id = (int) post('id', 0);
$accion = (string) post('accion', '');
$usuario = Usuario::findById($id);

if (!$usuario) {
    flash('error', 'Usuario no encontrado.');
    redirect('/admin/usuarios.php');
}

if ($id === $admin['id']) {
    flash('error', 'No puedes modificar tu propia cuenta desde este panel.');
    redirect('/admin/usuarios.php');
}

if ($accion === 'toggle_activo') {
    $nuevoEstado = !(int) $usuario['activo'];

    if (!$nuevoEstado && $usuario['rol'] === 'admin' && Usuario::countAdminsActivos() <= 1) {
        flash('error', 'No puedes desactivar al único administrador activo.');
        redirect('/admin/usuarios.php');
    }

    Usuario::updateActivo($id, $nuevoEstado);
    flash('success', $nuevoEstado ? 'Usuario activado.' : 'Usuario desactivado.');
    redirect('/admin/usuarios.php');
}

if ($accion === 'cambiar_rol') {
    $rol = (string) post('rol', '');

    if (!in_array($rol, ['admin', 'vendedor', 'comprador'], true)) {
        flash('error', 'Rol no válido.');
        redirect('/admin/usuarios.php');
    }

    if ($usuario['rol'] === 'admin' && $rol !== 'admin' && Usuario::countAdminsActivos() <= 1) {
        flash('error', 'Debe existir al menos un administrador activo.');
        redirect('/admin/usuarios.php');
    }

    Usuario::updateRol($id, $rol);
    flash('success', 'Rol actualizado a ' . rolLabel($rol) . '.');
    redirect('/admin/usuarios.php');
}

flash('error', 'Acción no válida.');
redirect('/admin/usuarios.php');
