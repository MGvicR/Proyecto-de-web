<?php

declare(strict_types=1);

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function renderCsrfField(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function verifyCsrf(): void
{
    $sent = (string) post('csrf_token', '');

    if ($sent === '' || !hash_equals(csrfToken(), $sent)) {
        flash('error', 'La solicitud no es válida. Recarga la página e intenta de nuevo.');
        redirect('/index.php');
    }
}
