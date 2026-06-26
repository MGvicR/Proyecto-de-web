<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once BASE_PATH . '/app/helpers/propiedad_form.php';

requireVendedor();

$user = currentUser();
$context = propiedadFormContext();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $input = propiedadOldInputFromPost();
    $error = validatePropiedadData($input);

    if ($error) {
        redirectPropiedadFormError('/vendedor/nueva.php', $error, $input, $context);
    }

    $errorFotos = validatePropiedadFotos($input, $_FILES['fotos'] ?? []);
    if ($errorFotos) {
        redirectPropiedadFormError('/vendedor/nueva.php', $errorFotos, $input, $context);
    }

    clearPropiedadOldInput($context);

    $input['vendedor_id'] = $user['id'];
    $input['estado'] = 'borrador';

    $id = Propiedad::create($input);

    try {
        $guardadas = handlePropiedadFotosEstructuradas($id, $_FILES['fotos'] ?? []);
        $esperadas = count(slotsFotosRequeridos($input));

        if ($guardadas < $esperadas) {
            flash('error', 'La propiedad se creó pero no se guardaron todas las fotos. Intenta editarla y subirlas de nuevo.');
            redirect('/vendedor/editar.php?id=' . $id);
        }
    } catch (Throwable $exception) {
        flash('error', 'La propiedad se creó pero hubo un error con las fotos: ' . $exception->getMessage());
        redirect('/vendedor/editar.php?id=' . $id);
    }

    flash('success', 'Propiedad creada como borrador. Puedes enviarla a revisión cuando esté lista.');
    redirect('/vendedor/index.php');
}

$prop = pullPropiedadOldInput($context);
$formDataRestaurado = $prop !== [];

renderHeader('Nueva propiedad');
?>
<section class="card add-property-container">
    <h1>Nueva propiedad</h1>
    <form method="post" enctype="multipart/form-data" class="property-form">
        <?php renderCsrfField(); ?>
        <?php require BASE_PATH . '/app/views/partials/form-propiedad.php'; ?>
        <button type="submit" class="btn">Guardar borrador</button>
    </form>
</section>
<?php renderFooter(); ?>
