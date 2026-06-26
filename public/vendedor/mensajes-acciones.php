<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireVendedor();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/vendedor/mensajes.php');
}

verifyCsrf();

$user = currentUser();
$id = (int) post('id', 0);

if ($id <= 0) {
    flash('error', 'Mensaje no válido.');
    redirect('/vendedor/mensajes.php');
}

$contacto = Contacto::findForVendedor($id, $user['id']);

if (!$contacto) {
    flash('error', 'Mensaje no encontrado.');
    redirect('/vendedor/mensajes.php');
}

if ((int) $contacto['leido'] === 1) {
    flash('success', 'Este mensaje ya estaba marcado como contestado.');
    redirect('/vendedor/mensajes.php');
}

Contacto::marcarContestado($id, $user['id']);
flash('success', 'Mensaje marcado como contestado.');
redirect('/vendedor/mensajes.php');
