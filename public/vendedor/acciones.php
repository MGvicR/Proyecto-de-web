<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireVendedor();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/vendedor/index.php');
}

verifyCsrf();

$user = currentUser();
$id = (int) post('id', 0);
$accion = (string) post('accion', '');
$prop = Propiedad::findById($id);

if (!$prop || (int) $prop['vendedor_id'] !== $user['id']) {
    flash('error', 'Propiedad no encontrada.');
    redirect('/vendedor/index.php');
}

if ($accion === 'enviar') {
    if (!in_array($prop['estado'], ['borrador', 'rechazada'], true)) {
        flash('error', 'Esta propiedad no puede enviarse a revisión.');
        redirect('/vendedor/index.php');
    }

    Propiedad::updateEstado($id, 'pendiente', [
        'motivo_rechazo' => null,
        'moderado_por' => null,
        'moderado_en' => null,
    ]);
    flash('success', 'Propiedad enviada a revisión. El administrador la revisará pronto.');
    redirect('/vendedor/index.php');
}

if ($accion === 'rentada') {
    if ($prop['estado'] !== 'activa') {
        flash('error', 'Solo puedes marcar como rentada una propiedad activa.');
        redirect('/vendedor/index.php');
    }

    Propiedad::updateEstado($id, 'rentada');
    flash('success', 'Propiedad marcada como rentada.');
    redirect('/vendedor/index.php');
}

if ($accion === 'baja') {
    if ($prop['estado'] === 'inactiva') {
        flash('error', 'Esta propiedad ya está dada de baja.');
        redirect('/vendedor/index.php');
    }

    Propiedad::updateEstado($id, 'inactiva');
    flash('success', 'Propiedad dada de baja. Ya no aparecerá en el catálogo público.');
    redirect('/vendedor/index.php');
}

flash('error', 'Acción no válida.');
redirect('/vendedor/index.php');
