<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/index.php');
}

verifyCsrf();

$admin = currentUser();
$id = (int) post('id', 0);
$accion = (string) post('accion', '');
$prop = Propiedad::findById($id);

if (!$prop) {
    flash('error', 'Propiedad no encontrada.');
    redirect('/admin/index.php');
}

if ($accion === 'aprobar') {
    if ($prop['estado'] !== 'pendiente') {
        flash('error', 'Solo se pueden aprobar propiedades pendientes.');
        redirect('/admin/ver.php?id=' . $id);
    }

    $estadoAnterior = $prop['estado'];
    Propiedad::updateEstado($id, 'activa', [
        'moderado_por' => $admin['id'],
        'moderado_en' => date('Y-m-d H:i:s'),
        'publicado_en' => date('Y-m-d H:i:s'),
        'motivo_rechazo' => null,
    ]);

    HistorialModeracion::registrar($id, $admin['id'], 'aprobada', $estadoAnterior, 'activa');
    flash('success', 'Propiedad aprobada y publicada.');
    redirect('/admin/index.php');
}

if ($accion === 'rechazar') {
    $motivo = trim((string) post('motivo', ''));

    if ($prop['estado'] !== 'pendiente') {
        flash('error', 'Solo se pueden rechazar propiedades pendientes.');
        redirect('/admin/ver.php?id=' . $id);
    }

    if ($motivo === '') {
        flash('error', 'Debes indicar el motivo de rechazo.');
        redirect('/admin/ver.php?id=' . $id);
    }

    $estadoAnterior = $prop['estado'];
    Propiedad::updateEstado($id, 'rechazada', [
        'motivo_rechazo' => $motivo,
        'moderado_por' => $admin['id'],
        'moderado_en' => date('Y-m-d H:i:s'),
    ]);

    HistorialModeracion::registrar($id, $admin['id'], 'rechazada', $estadoAnterior, 'rechazada', $motivo);
    flash('success', 'Propiedad rechazada.');
    redirect('/admin/index.php');
}

if ($accion === 'dar_baja') {
    if ($prop['estado'] === 'pendiente') {
        flash('error', 'No puedes dar de baja una propiedad que aún no ha sido revisada.');
        redirect('/admin/ver.php?id=' . $id);
    }

    if ($prop['estado'] === 'inactiva') {
        flash('error', 'Esta propiedad ya está dada de baja.');
        redirect('/admin/ver.php?id=' . $id);
    }

    $motivo = trim((string) post('motivo', ''));
    $estadoAnterior = $prop['estado'];
    Propiedad::updateEstado($id, 'inactiva', [
        'moderado_por' => $admin['id'],
        'moderado_en' => date('Y-m-d H:i:s'),
    ]);

    HistorialModeracion::registrar(
        $id,
        $admin['id'],
        'desactivada',
        $estadoAnterior,
        'inactiva',
        $motivo !== '' ? $motivo : null
    );
    flash('success', 'Propiedad dada de baja.');
    redirect('/admin/propiedades.php');
}

if ($accion === 'dar_alta') {
    if ($prop['estado'] !== 'inactiva') {
        flash('error', 'Solo se pueden reactivar propiedades dadas de baja.');
        redirect('/admin/ver.php?id=' . $id);
    }

    $estadoAnterior = $prop['estado'];
    Propiedad::updateEstado($id, 'activa', [
        'moderado_por' => $admin['id'],
        'moderado_en' => date('Y-m-d H:i:s'),
        'publicado_en' => date('Y-m-d H:i:s'),
        'motivo_rechazo' => null,
    ]);

    HistorialModeracion::registrar(
        $id,
        $admin['id'],
        'aprobada',
        $estadoAnterior,
        'activa',
        'Reactivada por administrador'
    );
    flash('success', 'Propiedad dada de alta y publicada en el catálogo.');
    redirect('/admin/propiedades.php');
}

flash('error', 'Acción no válida.');
redirect('/admin/index.php');
