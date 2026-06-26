#!/usr/bin/env bash
# Ajusta límites de subida de fotos en PHP (Apache).
# Uso en el servidor: bash scripts/fix-php-uploads.sh

set -euo pipefail

PHP_INI=""
for candidate in /etc/php/8.2/apache2/php.ini /etc/php/8.3/apache2/php.ini /etc/php/*/apache2/php.ini; do
    if [ -f "$candidate" ]; then
        PHP_INI="$candidate"
        break
    fi
done

if [ -z "$PHP_INI" ]; then
    echo "No se encontró php.ini de Apache. Usa public/.user.ini si el servidor corre con PHP-FPM."
    exit 1
fi

echo "Actualizando $PHP_INI"
sudo sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 10M/' "$PHP_INI"
sudo sed -i 's/^post_max_size = .*/post_max_size = 64M/' "$PHP_INI"
sudo sed -i 's/^max_file_uploads = .*/max_file_uploads = 30/' "$PHP_INI"
sudo systemctl reload apache2 2>/dev/null || sudo service apache2 reload

echo "Listo. Valores actuales:"
php -c "$PHP_INI" -r 'echo "upload_max_filesize=".ini_get("upload_max_filesize").PHP_EOL; echo "post_max_size=".ini_get("post_max_size").PHP_EOL;'
