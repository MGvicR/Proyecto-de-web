<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/propiedades.php');
}

verifyCsrf();

$propiedadId = (int) post('propiedad_id', 0);
$prop = Propiedad::findById($propiedadId);

if (!$prop || $prop['estado'] !== 'activa') {
    flash('error', 'Propiedad no disponible.');
    redirect('/propiedades.php');
}

if (isLoggedIn() && !isComprador()) {
    flash('error', 'Inicia sesión como comprador para enviar consultas.');
    redirect('/propiedad.php?slug=' . urlencode($prop['slug']));
}

if (isLoggedIn() && !isComprador()) {
    flash('error', 'Inicia sesión como comprador para enviar consultas.');
    redirect('/propiedad.php?slug=' . urlencode($prop['slug']));
}

$mensaje = trim((string) post('mensaje', ''));

if (isComprador()) {
    $perfil = Usuario::findById(currentUser()['id']);
    if (!$perfil) {
        flash('error', 'No se pudo cargar tu perfil.');
        redirect('/propiedad.php?slug=' . urlencode($prop['slug']));
    }

    $nombre = trim(($perfil['nombre'] ?? '') . ' ' . ($perfil['apellido'] ?? ''));
    $email = trim((string) ($perfil['email'] ?? ''));
    $telefono = normalizarTelefono(trim((string) ($perfil['telefono'] ?? '')));
} else {
    $nombre = trim((string) post('nombre_visitante', ''));
    $telefono = normalizarTelefono(trim((string) post('telefono', '')));
    $email = trim((string) post('email', ''));
}

if ($nombre === '' || $telefono === '') {
    flash('error', 'Nombre y teléfono son obligatorios.');
    redirect('/propiedad.php?slug=' . urlencode($prop['slug']));
}

if (!telefonoValido($telefono, true)) {
    flash('error', 'El teléfono solo puede contener números.');
    redirect('/propiedad.php?slug=' . urlencode($prop['slug']));
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Email inválido.');
    redirect('/propiedad.php?slug=' . urlencode($prop['slug']));
}

$compradorId = null;
if (isComprador()) {
    $compradorId = currentUser()['id'];
    $existente = Contacto::findByCompradorPropiedad($compradorId, $propiedadId);
    if ($existente) {
        flash('error', 'Ya enviaste una consulta por esta propiedad.');
        redirect('/propiedad.php?slug=' . urlencode($prop['slug']));
    }
}

Contacto::create([
    'propiedad_id' => $propiedadId,
    'vendedor_id' => (int) $prop['vendedor_id'],
    'comprador_id' => $compradorId,
    'nombre_visitante' => $nombre,
    'email' => $email ?: null,
    'telefono' => $telefono,
    'mensaje' => $mensaje !== '' ? $mensaje : null,
]);

flash('success', 'Tu mensaje fue enviado al vendedor.');
redirect(isComprador() ? '/comprador/index.php' : '/propiedad.php?slug=' . urlencode($prop['slug']));
