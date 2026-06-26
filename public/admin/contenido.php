<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireAdmin();

$defaults = sitioConfigDefaults();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $heroTitulo = trim((string) post('hero_titulo', ''));
    $heroSubtitulo = trim((string) post('hero_subtitulo', ''));

    if ($heroTitulo === '' || $heroSubtitulo === '') {
        flash('error', 'El título y el subtítulo son obligatorios.');
        redirect('/admin/contenido.php');
    }

    sitioConfigGuardar([
        'hero_titulo' => $heroTitulo,
        'hero_subtitulo' => $heroSubtitulo,
    ]);

    flash('success', 'Contenido de la página de inicio actualizado.');
    redirect('/admin/contenido.php');
}

$heroTitulo = sitioConfig('hero_titulo', $defaults['hero_titulo']);
$heroSubtitulo = sitioConfig('hero_subtitulo', $defaults['hero_subtitulo']);

renderHeader('Contenido del sitio');
?>
<section class="card add-property-container">
    <h1>Personalizar página de inicio</h1>
    <p class="meta">Edita los textos principales del hero que ven los visitantes en la portada.</p>

    <form method="post" class="form-stack" style="max-width: 640px;">
        <?php renderCsrfField(); ?>
        <div class="form-group">
            <label for="hero_titulo">Título principal *</label>
            <input type="text" id="hero_titulo" name="hero_titulo" required maxlength="150"
                   value="<?= e($heroTitulo) ?>">
        </div>
        <div class="form-group">
            <label for="hero_subtitulo">Subtítulo *</label>
            <textarea id="hero_subtitulo" name="hero_subtitulo" rows="3" required maxlength="300"><?= e($heroSubtitulo) ?></textarea>
        </div>
        <button type="submit" class="btn">Guardar cambios</button>
    </form>

    <p class="meta" style="margin-top: 1.5rem;">
        <a href="<?= e(url('/index.php')) ?>" target="_blank" rel="noopener">Ver página de inicio →</a>
    </p>
</section>
<?php renderFooter(); ?>
