<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireComprador();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/propiedades.php');
}

verifyCsrf();

$user = currentUser();
$contactoId = (int) post('contacto_id', 0);
$estrellas = (int) post('estrellas', 0);
$comentario = trim((string) post('comentario', ''));
$slug = trim((string) post('slug', ''));

$redirectTarget = $slug !== ''
    ? '/propiedad.php?slug=' . urlencode($slug)
    : '/comprador/index.php';

if ($contactoId <= 0 || $estrellas < 1 || $estrellas > 5) {
    flash('error', 'Selecciona una valoración de 1 a 5 estrellas.');
    redirect($redirectTarget);
}

$stmt = getDB()->prepare(
    'SELECT c.*, p.slug AS propiedad_slug
     FROM contactos c
     INNER JOIN propiedades p ON p.id = c.propiedad_id
     WHERE c.id = ? AND c.comprador_id = ? AND c.leido = 1
     LIMIT 1'
);
$stmt->execute([$contactoId, $user['id']]);
$contacto = $stmt->fetch();

if (!$contacto) {
    flash('error', 'No puedes calificar este vendedor todavía.');
    redirect($redirectTarget);
}

if (CalificacionVendedor::findByContacto($contactoId)) {
    flash('error', 'Ya calificaste esta consulta.');
    redirect('/propiedad.php?slug=' . urlencode($contacto['propiedad_slug']));
}

CalificacionVendedor::create([
    'contacto_id' => $contactoId,
    'vendedor_id' => (int) $contacto['vendedor_id'],
    'comprador_id' => $user['id'],
    'propiedad_id' => (int) $contacto['propiedad_id'],
    'estrellas' => $estrellas,
    'comentario' => $comentario !== '' ? $comentario : null,
]);

flash('success', 'Gracias por calificar al vendedor.');
redirect('/propiedad.php?slug=' . urlencode($contacto['propiedad_slug']));
