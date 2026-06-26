<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$destacadas = Propiedad::search([], 6);
$totalActivas = Propiedad::countSearch([]);
$totalAlcaldias = Alcaldia::count();

renderHeader('Inicio', 'home');
?>
<section class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <img src="<?= e(url(SITE_LOGO)) ?>" alt="<?= e(SITE_NAME . ' - ' . SITE_TAGLINE) ?>" class="hero-logo">
        <h1><?= e(sitioConfig('hero_titulo')) ?></h1>
        <p><?= e(sitioConfig('hero_subtitulo')) ?></p>
        <?php
        $action = url('/propiedades.php');
        $variant = 'hero';
        require BASE_PATH . '/app/views/partials/filtros.php';
        ?>
    </div>
</section>

<section class="stats">
    <div class="container">
        <div class="stat-item">
            <h3><?= $totalActivas ?></h3>
            <p>Propiedades en renta</p>
        </div>
        <div class="stat-item">
            <h3><?= $totalAlcaldias ?></h3>
            <p>Alcaldías cubiertas</p>
        </div>
        <div class="stat-item">
            <h3>100%</h3>
            <p>Anuncios moderados</p>
        </div>
    </div>
</section>

<?php if ($destacadas): ?>
<section class="featured">
    <div class="container">
        <h2 class="section-title">Propiedades recientes</h2>
        <div class="properties-grid property-grid">
            <?php foreach ($destacadas as $prop): ?>
                <?php require BASE_PATH . '/app/views/partials/property-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="<?= e(url('/propiedades.php')) ?>" class="btn-primary">Ver todas las propiedades</a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="map-section">
    <div class="container">
        <h2 class="section-title">Explora por zona</h2>
        <div id="map"></div>
    </div>
</section>

<?php renderFooter('home'); ?>
