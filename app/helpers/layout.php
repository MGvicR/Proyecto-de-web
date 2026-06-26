<?php

declare(strict_types=1);

const SITE_NAME = 'Prestige Homes';
const SITE_TAGLINE = 'Élite Homes';
const SITE_INDUSTRY = 'Inmobiliaria';
const SITE_LOGO = '/assets/img/logo-prestige-homes.png';

function renderHeader(string $title = 'Inicio', string $layout = 'default'): void
{
    $user = currentUser();
    $bodyClass = 'layout-' . preg_replace('/[^a-z0-9_-]/', '', $layout);
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | <?= e(SITE_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(assetUrl('/assets/css/style.css')) ?>">
    <?php if ($layout === 'home'): ?>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <?php endif; ?>
</head>
<body class="<?= e($bodyClass) ?>">
<header class="site-header">
    <div class="header-container">
        <a href="<?= e(url('/index.php')) ?>" class="logo" aria-label="<?= e(SITE_NAME . ' - ' . SITE_TAGLINE) ?>">
            <img src="<?= e(url(SITE_LOGO)) ?>" alt="<?= e(SITE_NAME . ' - ' . SITE_TAGLINE) ?>" class="logo-img">
            <span class="logo-text logo-text-compact">
                <span class="logo-industry"><?= e(SITE_INDUSTRY) ?></span>
                <?= e(SITE_NAME) ?>
                <span><?= e(SITE_TAGLINE) ?></span>
            </span>
        </a>
        <nav class="main-nav">
            <ul>
                <li><a href="<?= e(url('/index.php')) ?>">Inicio</a></li>
                <li><a href="<?= e(url('/propiedades.php')) ?>">Buscar</a></li>
                <?php if ($user): ?>
                    <?php if (isComprador()): ?>
                        <li><a href="<?= e(url('/comprador/index.php')) ?>">Mis consultas</a></li>
                    <?php endif; ?>
                    <?php if (isVendedor()): ?>
                        <li><a href="<?= e(url('/vendedor/index.php')) ?>">Mis propiedades</a></li>
                        <li><a href="<?= e(url('/vendedor/mensajes.php')) ?>">Mensajes</a></li>
                        <li><a href="<?= e(url('/vendedor/nueva.php')) ?>" class="btn-header">+ Publicar</a></li>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="<?= e(url('/admin/index.php')) ?>">Moderación</a></li>
                        <li><a href="<?= e(url('/admin/propiedades.php')) ?>">Propiedades</a></li>
                        <li><a href="<?= e(url('/admin/usuarios.php')) ?>">Usuarios</a></li>
                        <li><a href="<?= e(url('/admin/estadisticas.php')) ?>">Estadísticas</a></li>
                        <li><a href="<?= e(url('/admin/contenido.php')) ?>">Contenido</a></li>
                        <li><a href="<?= e(url('/admin/ubicaciones.php')) ?>">Ubicaciones</a></li>
                    <?php endif; ?>
                    <li><span class="user-greeting"><?= e($user['nombre']) ?></span></li>
                    <li><a href="<?= e(url('/logout.php')) ?>">Salir</a></li>
                <?php else: ?>
                    <li><a href="<?= e(url('/login.php')) ?>" class="btn-header btn-header-outline">Iniciar sesión</a></li>
                    <li><a href="<?= e(url('/registrarse.php')) ?>" class="btn-header">Registrarse</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<?php if ($layout === 'auth'): ?>
<div class="auth-container">
    <div class="auth-box">
<?php elseif ($layout === 'search'): ?>
<div class="search-page">
<?php elseif ($layout === 'home'): ?>
<main class="site-main site-main-home">
<?php else: ?>
<main class="site-main container">
<?php endif; ?>
    <?php foreach (getFlashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
    <?php
}

function renderFooter(string $layout = 'default'): void
{
    if ($layout === 'auth'): ?>
    </div>
</div>
<?php elseif ($layout === 'search'): ?>
</div>
<?php else: ?>
</main>
<?php endif; ?>
<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h4><?= e(SITE_NAME) ?></h4>
            <p><?= e(SITE_INDUSTRY) ?> de lujo especializada en rentas exclusivas en Ciudad de México.</p>
        </div>
        <div class="footer-section">
            <h4>Enlaces</h4>
            <ul>
                <li><a href="<?= e(url('/index.php')) ?>">Inicio</a></li>
                <li><a href="<?= e(url('/propiedades.php')) ?>">Buscar propiedades</a></li>
                <li><a href="<?= e(url('/registrarse.php')) ?>">Registrarse</a></li>
                <li><a href="<?= e(url('/login.php')) ?>">Iniciar sesión</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Recursos</h4>
            <ul>
                <li><a href="<?= e(url('/api/propiedades.php')) ?>">Catálogo JSON</a></li>
                <li><a href="<?= e(url('/login.php')) ?>">Iniciar sesión</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p><?= e(SITE_NAME) ?> — <?= e(SITE_TAGLINE) ?> &copy; <?= date('Y') ?></p>
    </div>
</footer>
<script>window.APP_BASE_URL = <?= json_encode(BASE_URL, JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="<?= e(assetUrl('/assets/js/app.js')) ?>"></script>
<?php if ($layout === 'home'): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?= e(assetUrl('/assets/js/map.js')) ?>"></script>
<?php endif; ?>
</body>
</html>
    <?php
}
