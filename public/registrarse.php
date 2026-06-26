<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

if (isLoggedIn()) {
    redirectByRole();
}

renderHeader('Registrarse', 'auth');
?>
<h1>Crear cuenta</h1>
<p class="auth-intro">Elige cómo quieres usar <?= e(SITE_NAME) ?>.</p>

<div class="auth-choice-grid">
    <a href="<?= e(url('/registro-comprador.php')) ?>" class="auth-choice-card">
        <span class="auth-choice-icon" aria-hidden="true">🔍</span>
        <strong>Soy comprador</strong>
        <span>Busco propiedades en renta y quiero contactar vendedores.</span>
    </a>
    <a href="<?= e(url('/registro.php')) ?>" class="auth-choice-card">
        <span class="auth-choice-icon" aria-hidden="true">🏠</span>
        <strong>Soy vendedor</strong>
        <span>Quiero publicar y administrar mis propiedades en renta.</span>
    </a>
</div>

<p class="auth-link">¿Ya tienes cuenta? <a href="<?= e(url('/login.php')) ?>">Iniciar sesión</a></p>
<?php renderFooter('auth'); ?>
