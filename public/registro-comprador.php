<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

if (isLoggedIn()) {
    redirectByRole();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $nombre = trim((string) post('nombre', ''));
    $apellido = trim((string) post('apellido', ''));
    $email = trim((string) post('email', ''));
    $telefono = normalizarTelefono(trim((string) post('telefono', '')));
    $password = (string) post('password', '');
    $passwordConfirm = (string) post('password_confirm', '');

    if ($nombre === '' || $apellido === '' || $email === '' || $password === '' || $telefono === '') {
        flash('error', 'Completa los campos obligatorios.');
        redirect('/registro-comprador.php');
    }

    if (!telefonoValido($telefono, true)) {
        flash('error', 'El teléfono solo puede contener números.');
        redirect('/registro-comprador.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Email inválido.');
        redirect('/registro-comprador.php');
    }

    if (strlen($password) < 8) {
        flash('error', 'La contraseña debe tener al menos 8 caracteres.');
        redirect('/registro-comprador.php');
    }

    if ($password !== $passwordConfirm) {
        flash('error', 'Las contraseñas no coinciden.');
        redirect('/registro-comprador.php');
    }

    if (Usuario::emailExists($email)) {
        flash('error', 'Este email ya está registrado.');
        redirect('/registro-comprador.php');
    }

    $id = Usuario::create([
        'nombre' => $nombre,
        'apellido' => $apellido,
        'email' => $email,
        'telefono' => $telefono,
        'password' => $password,
        'rol' => 'comprador',
    ]);

    $user = Usuario::findById($id);
    loginUser($user);
    flash('success', 'Cuenta de comprador creada. Ya puedes contactar vendedores.');
    redirect('/comprador/index.php');
}

renderHeader('Registro comprador', 'auth');
?>
<h1>Registro de comprador</h1>
<p class="auth-intro">Crea una cuenta para guardar tus consultas y contactar vendedores más rápido.</p>
<form method="post" class="form-stack">
    <?php renderCsrfField(); ?>
    <div class="form-group">
        <label for="nombre">Nombre *</label>
        <input type="text" id="nombre" name="nombre" required maxlength="100">
    </div>
    <div class="form-group">
        <label for="apellido">Apellido *</label>
        <input type="text" id="apellido" name="apellido" required maxlength="100">
    </div>
    <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" required maxlength="150">
    </div>
    <div class="form-group">
        <label for="telefono">Teléfono *</label>
        <input type="tel" id="telefono" name="telefono" class="js-solo-numeros" required
               inputmode="numeric" pattern="[0-9]*" maxlength="20"
               title="Solo números">
    </div>
    <div class="form-group">
        <label for="password">Contraseña *</label>
        <input type="password" id="password" name="password" required minlength="8">
    </div>
    <div class="form-group">
        <label for="password_confirm">Confirmar contraseña *</label>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
    </div>
    <button type="submit" class="btn full-width">Crear cuenta de comprador</button>
</form>
<p class="auth-link">¿Ya tienes cuenta? <a href="<?= e(url('/login.php')) ?>">Inicia sesión</a></p>
<p class="auth-link">¿Quieres publicar propiedades? <a href="<?= e(url('/registrarse.php')) ?>">Elige otro tipo de cuenta</a></p>
<?php renderFooter('auth'); ?>
