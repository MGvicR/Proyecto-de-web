<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once BASE_PATH . '/app/helpers/propiedad_form.php';

requireVendedor();

$user = currentUser();
$id = (int) get('id', 0);
$prop = Propiedad::findById($id);

if (!$prop || (int) $prop['vendedor_id'] !== $user['id']) {
    flash('error', 'Propiedad no encontrada.');
    redirect('/vendedor/index.php');
}

if (!in_array($prop['estado'], ['borrador', 'rechazada', 'pendiente', 'activa', 'inactiva'], true)) {
    flash('error', 'No puedes editar esta propiedad.');
    redirect('/vendedor/index.php');
}

$context = propiedadFormContext($id);
$fotosExistentes = Propiedad::fotosIndexadas($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $input = propiedadOldInputFromPost();
    $error = validatePropiedadData($input);

    if ($error) {
        redirectPropiedadFormError('/vendedor/editar.php?id=' . $id, $error, $input, $context);
    }

    $errorFotos = validatePropiedadFotos($input, $_FILES['fotos'] ?? [], $id);
    if ($errorFotos) {
        redirectPropiedadFormError('/vendedor/editar.php?id=' . $id, $errorFotos, $input, $context);
    }

    Propiedad::update($id, $input);

    try {
        handlePropiedadFotosEstructuradas($id, $_FILES['fotos'] ?? []);
        $errorFotosFinal = validatePropiedadFotos($input, [], $id);
        if ($errorFotosFinal) {
            redirectPropiedadFormError('/vendedor/editar.php?id=' . $id, $errorFotosFinal, $input, $context);
        }
    } catch (Throwable $exception) {
        redirectPropiedadFormError(
            '/vendedor/editar.php?id=' . $id,
            'Error al guardar fotos: ' . $exception->getMessage(),
            $input,
            $context
        );
    }

    clearPropiedadOldInput($context);

    if ($prop['estado'] === 'activa') {
        Propiedad::updateEstado($id, 'pendiente', [
            'motivo_rechazo' => null,
            'moderado_por' => null,
            'moderado_en' => null,
            'publicado_en' => null,
        ]);
        flash('success', 'Propiedad actualizada. Volverá al catálogo cuando el administrador la apruebe de nuevo.');
    } else {
        flash('success', 'Propiedad actualizada.');
    }
    redirect('/vendedor/editar.php?id=' . $id);
}

$oldInput = pullPropiedadOldInput($context);
$formDataRestaurado = $oldInput !== [];
if ($formDataRestaurado) {
    $prop = array_merge($prop, $oldInput);
} else {
    $colonia = Colonia::find((int) $prop['colonia_id']);
    $prop['alcaldia_id'] = $colonia['alcaldia_id'] ?? null;
}

renderHeader('Editar propiedad');
?>
<section class="card add-property-container">
    <h1>Editar propiedad</h1>
    <p>Estado: <span class="badge badge-<?= e($prop['estado']) ?>"><?= e(estadoLabel($prop['estado'])) ?></span></p>
    <?php if ($prop['estado'] === 'rechazada' && !empty($prop['motivo_rechazo'])): ?>
        <div class="alert alert-error">
            <strong>Propiedad rechazada.</strong>
            Motivo del administrador: <?= e($prop['motivo_rechazo']) ?>
        </div>
    <?php endif; ?>

    <?php if ($prop['estado'] === 'activa'): ?>
        <p class="meta">Si guardas cambios en una propiedad activa, volverá a <strong>pendiente</strong> hasta que el administrador la revise de nuevo.</p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="property-form">
        <?php renderCsrfField(); ?>
        <?php require BASE_PATH . '/app/views/partials/form-propiedad.php'; ?>
        <button type="submit" class="btn">Guardar cambios</button>
    </form>
</section>
<?php renderFooter(); ?>
