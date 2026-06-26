<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

if (isLoggedIn()) {
    redirectByRole();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email = trim((string) post('email', ''));
    $password = (string) post('password', '');

    $user = Usuario::findByEmail($email);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        flash('error', 'Credenciales incorrectas.');
        redirect('/login.php');
    }

    if (!(int) $user['activo']) {
        flash('error', 'Tu cuenta está desactivada. Contacta al administrador.');
        redirect('/login.php');
    }

    loginUser($user);
    flash('success', 'Bienvenido de nuevo.');
    redirectByRole($user);
}

renderHeader('Iniciar sesión', 'auth');
?>
<h1>Iniciar sesión</h1>
<form method="post" class="form-stack">
    <?php renderCsrfField(); ?>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit" class="btn full-width">Entrar</button>
</form>
<p class="auth-link">¿No tienes cuenta? <a href="<?= e(url('/registrarse.php')) ?>">Regístrate aquí</a></p>
<?php renderFooter('auth'); ?>
