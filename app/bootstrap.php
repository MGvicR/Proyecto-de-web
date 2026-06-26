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
$baseUrl = '';

if ($documentRoot !== false && $publicPath !== false) {
    $documentRoot = str_replace('\\', '/', $documentRoot);
    $publicPath = str_replace('\\', '/', $publicPath);

    if (str_starts_with($publicPath, $documentRoot)) {
        $baseUrl = rtrim(substr($publicPath, strlen($documentRoot)), '/');
    }

    // En hosting compartido Apache suele usar public/ como DocumentRoot;
    // ahí la ruta relativa queda vacía y hay que deducirla desde SCRIPT_NAME.
    if ($baseUrl === '') {
        $scriptFile = realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
        if ($scriptFile !== false) {
            $scriptFile = str_replace('\\', '/', $scriptFile);
            if (str_starts_with($scriptFile, $publicPath)) {
                $relativeScript = substr($scriptFile, strlen($publicPath));
                $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
                if ($relativeScript !== '' && str_ends_with($scriptName, $relativeScript)) {
                    $baseUrl = rtrim(substr($scriptName, 0, -strlen($relativeScript)), '/');
                }
            }
        }
    }
}

define('BASE_URL', $baseUrl);

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
