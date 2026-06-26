#!/usr/bin/env bash
# Genera la documentación PDF a partir del HTML en docs/
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HTML="$ROOT/docs/documentacion.html"
PDF="$ROOT/docs/Documentacion_Prestige_Homes.pdf"

if [[ ! -f "$HTML" ]]; then
  echo "No se encontró: $HTML" >&2
  exit 1
fi

CHROME=""
for candidate in chromium chromium-browser google-chrome google-chrome-stable; do
  if command -v "$candidate" >/dev/null 2>&1; then
    CHROME="$candidate"
    break
  fi
done

if [[ -z "$CHROME" ]]; then
  echo "Instala Chromium o Chrome para generar el PDF." >&2
  exit 1
fi

"$CHROME" \
  --headless=new \
  --disable-gpu \
  --no-sandbox \
  --run-all-compositor-stages-before-draw \
  --virtual-time-budget=10000 \
  --print-to-pdf="$PDF" \
  "file://$HTML"

echo "PDF generado: $PDF"
