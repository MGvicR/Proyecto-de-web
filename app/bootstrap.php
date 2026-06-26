<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads/propiedades');

$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$publicPath = realpath(PUBLIC_PATH);

if ($documentRoot !== false && $publicPath !== false) {
    $documentRoot = str_replace('\\', '/', $documentRoot);
    $publicPath = str_replace('\\', '/', $publicPath);
    $baseUrl = str_starts_with($publicPath, $documentRoot)
        ? substr($publicPath, strlen($documentRoot))
        : '';
    define('BASE_URL', rtrim($baseUrl, '/'));
} else {
    define('BASE_URL', '');
}

require_once BASE_PATH . '/app/config/database.php';
require_once BASE_PATH . '/app/helpers/sanitize.php';
require_once BASE_PATH . '/app/helpers/flash.php';
require_once BASE_PATH . '/app/helpers/csrf.php';
require_once BASE_PATH . '/app/helpers/auth.php';
require_once BASE_PATH . '/app/helpers/search.php';
require_once BASE_PATH . '/app/helpers/sitio.php';
require_once BASE_PATH . '/app/helpers/layout.php';

spl_autoload_register(static function (string $class): void {
    $file = BASE_PATH . '/app/models/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

ensureActiveSession();
