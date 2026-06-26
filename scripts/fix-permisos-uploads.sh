#!/bin/bash
# Corrige permisos de la carpeta de fotos para Apache/Nginx + PHP (www-data)
# Uso en el servidor: bash scripts/fix-permisos-uploads.sh

set -e

BASE_DIR="$(cd "$(dirname "$0")/.." && pwd)"
UPLOADS_DIR="$BASE_DIR/public/uploads"
PROPIEDADES_DIR="$UPLOADS_DIR/propiedades"

mkdir -p "$PROPIEDADES_DIR"

if id www-data >/dev/null 2>&1; then
    sudo chown -R www-data:www-data "$UPLOADS_DIR"
    sudo chmod -R 775 "$UPLOADS_DIR"
    echo "Permisos aplicados con propietario www-data en: $UPLOADS_DIR"
elif id apache >/dev/null 2>&1; then
    sudo chown -R apache:apache "$UPLOADS_DIR"
    sudo chmod -R 775 "$UPLOADS_DIR"
    echo "Permisos aplicados con propietario apache en: $UPLOADS_DIR"
else
    chmod -R 777 "$UPLOADS_DIR"
    echo "Permisos 777 aplicados en: $UPLOADS_DIR"
fi

ls -la "$UPLOADS_DIR"
ls -la "$PROPIEDADES_DIR"
