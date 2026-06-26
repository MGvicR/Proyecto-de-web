#!/bin/bash
set -e

PORT="${PORT:-80}"

sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

if [ -n "${DB_HOST:-}" ]; then
  php <<'PHP'
<?php

declare(strict_types=1);

$config = [
    'DB_HOST' => getenv('DB_HOST') ?: 'localhost',
    'DB_NAME' => getenv('DB_NAME') ?: '',
    'DB_USER' => getenv('DB_USER') ?: '',
    'DB_PASS' => getenv('DB_PASS') ?: '',
    'DB_PORT' => getenv('DB_PORT') ?: '3306',
];

$export = var_export($config, true);
$contents = "<?php\n\ndeclare(strict_types=1);\n\nreturn {$export};\n";

file_put_contents('/var/www/html/app/config/config.local.php', $contents);
PHP

  php /var/www/html/scripts/render-bootstrap-db.php || {
    echo "Aviso: no se pudieron crear las tablas automáticamente. Importa sql/schema_aiven.sql en Aiven."
  }
fi

apache2-foreground
