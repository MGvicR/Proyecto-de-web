<?php

declare(strict_types=1);

const FOTOS_TIPOS_VALIDOS = ['recamara', 'bano', 'cocina', 'estacionamiento'];

function slotsFotosRequeridos(array $data): array
{
    $slots = [];

    for ($i = 1; $i <= (int) $data['recamaras']; $i++) {
        $slots[] = ['tipo' => 'recamara', 'numero' => $i];
    }

    for ($i = 1; $i <= (int) $data['banos']; $i++) {
        $slots[] = ['tipo' => 'bano', 'numero' => $i];
    }

    for ($i = 1; $i <= (int) $data['cocinas']; $i++) {
        $slots[] = ['tipo' => 'cocina', 'numero' => $i];
    }

    if ((int) ($data['tiene_estacionamiento'] ?? 0) === 1) {
        $slots[] = ['tipo' => 'estacionamiento', 'numero' => 1];
    }

    return $slots;
}

function fotoFilesValue(array $files, string $field, string $tipo, int $numero): mixed
{
    if (!isset($files[$field][$tipo]) || !is_array($files[$field][$tipo])) {
        return null;
    }

    $numeros = $files[$field][$tipo];

    return $numeros[$numero] ?? $numeros[(string) $numero] ?? null;
}

function archivoFotoSubido(array $files, string $tipo, int $numero): bool
{
    $error = fotoFilesValue($files, 'error', $tipo, $numero);

    return $error === UPLOAD_ERR_OK;
}

function mensajeErrorArchivoFoto(array $files, string $tipo, int $numero): ?string
{
    $error = fotoFilesValue($files, 'error', $tipo, $numero);

    if ($error === null || $error === UPLOAD_ERR_NO_FILE || $error === UPLOAD_ERR_OK) {
        return null;
    }

    $etiqueta = fotoEspacioLabel($tipo, $numero);

    return match ((int) $error) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => "La foto de {$etiqueta} supera el tamaño máximo permitido (5 MB por foto).",
        UPLOAD_ERR_PARTIAL => "La foto de {$etiqueta} se subió incompleta. Intenta de nuevo.",
        UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => "El servidor no pudo guardar la foto de {$etiqueta}. Contacta al administrador.",
        default => "No se pudo subir la foto de {$etiqueta}. Intenta de nuevo.",
    };
}

function validatePropiedadFotos(array $data, array $files, ?int $propiedadId = null): ?string
{
    $slots = slotsFotosRequeridos($data);
    $existentes = $propiedadId ? Propiedad::fotosIndexadas($propiedadId) : [];

    foreach ($slots as $slot) {
        $tipo = $slot['tipo'];
        $numero = $slot['numero'];
        $clave = Propiedad::fotoClave($tipo, $numero);
        $errorSubida = mensajeErrorArchivoFoto($files, $tipo, $numero);

        if ($errorSubida !== null) {
            return $errorSubida;
        }

        $tieneArchivo = !empty($files['name']) && archivoFotoSubido($files, $tipo, $numero);
        $tieneExistente = isset($existentes[$clave]);

        if (!$tieneArchivo && !$tieneExistente) {
            return 'Debes subir la foto de ' . fotoEspacioLabel($tipo, $numero) . '.';
        }
    }

    return null;
}

function detectarMimeImagen(string $tmpPath): ?string
{
    $permitidos = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mime = @mime_content_type($tmpPath) ?: '';
    if (isset($permitidos[$mime])) {
        return $mime;
    }

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mime = finfo_file($finfo, $tmpPath) ?: '';
            finfo_close($finfo);
            if (isset($permitidos[$mime])) {
                return $mime;
            }
        }
    }

    $info = @getimagesize($tmpPath);
    if ($info === false) {
        return null;
    }

    $mime = match ($info[2]) {
        IMAGETYPE_JPEG => 'image/jpeg',
        IMAGETYPE_PNG => 'image/png',
        IMAGETYPE_WEBP => 'image/webp',
        default => null,
    };

    return isset($permitidos[$mime ?? '']) ? $mime : null;
}

function guardarArchivoFoto(array $archivo): ?string
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }

    if (($archivo['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Cada foto debe pesar máximo 5 MB.');
    }

    $tmp = $archivo['tmp_name'] ?? '';
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return null;
    }

    $mime = detectarMimeImagen($tmp);
    if ($mime === null) {
        throw new RuntimeException('Formato no válido. Usa JPG, PNG o WEBP.');
    }

    $extension = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => null,
    };

    if ($extension === null) {
        return null;
    }

    if (!is_dir(UPLOAD_PATH)) {
        if (!@mkdir(UPLOAD_PATH, 0775, true) && !is_dir(UPLOAD_PATH)) {
            throw new RuntimeException('No se pudo crear la carpeta de uploads.');
        }
    }

    if (!is_writable(UPLOAD_PATH)) {
        @chmod(UPLOAD_PATH, 0775);
    }

    if (!is_writable(UPLOAD_PATH)) {
        @chmod(UPLOAD_PATH, 0777);
    }

    if (!is_writable(UPLOAD_PATH)) {
        throw new RuntimeException(
            'La carpeta de uploads no tiene permisos de escritura. '
            . 'En el servidor ejecuta: sudo chmod -R 775 public/uploads && sudo chown -R www-data:www-data public/uploads'
        );
    }

    $filename = uniqid('prop_', true) . '.' . $extension;
    $destination = UPLOAD_PATH . '/' . $filename;

    if (!move_uploaded_file($tmp, $destination)) {
        throw new RuntimeException('No se pudo mover el archivo subido al servidor.');
    }

    return $filename;
}

function handlePropiedadFotosEstructuradas(int $propiedadId, array $files): int
{
    if (empty($files['name']) || !is_array($files['name'])) {
        return 0;
    }

    $guardadas = 0;

    foreach ($files['name'] as $tipo => $numeros) {
        if (!in_array($tipo, FOTOS_TIPOS_VALIDOS, true) || !is_array($numeros)) {
            continue;
        }

        foreach ($numeros as $numero => $nombre) {
            $numero = (int) $numero;

            if (!archivoFotoSubido($files, $tipo, $numero)) {
                continue;
            }

            $archivo = [
                'name' => (string) $nombre,
                'type' => (string) (fotoFilesValue($files, 'type', $tipo, $numero) ?? ''),
                'tmp_name' => (string) (fotoFilesValue($files, 'tmp_name', $tipo, $numero) ?? ''),
                'error' => (int) (fotoFilesValue($files, 'error', $tipo, $numero) ?? UPLOAD_ERR_NO_FILE),
                'size' => (int) (fotoFilesValue($files, 'size', $tipo, $numero) ?? 0),
            ];

            $filename = guardarArchivoFoto($archivo);

            Propiedad::replaceFotoEspacio(
                $propiedadId,
                $tipo,
                $numero,
                $filename,
                $archivo['name']
            );

            $guardadas++;
        }
    }

    return $guardadas;
}

function propiedadFromPost(): array
{
    return [
        'colonia_id' => (int) post('colonia_id', 0),
        'titulo' => trim((string) post('titulo', '')),
        'descripcion' => trim((string) post('descripcion', '')),
        'tipo_inmueble' => (string) post('tipo_inmueble', ''),
        'recamaras' => max(1, (int) post('recamaras', 1)),
        'banos' => max(1, (int) post('banos', 1)),
        'cocinas' => max(1, (int) post('cocinas', 1)),
        'tiene_estacionamiento' => (int) post('tiene_estacionamiento', 0),
        'permite_mascotas' => (int) post('permite_mascotas', 0),
        'precio_mensual' => (float) post('precio_mensual', 0),
        'amueblado' => (int) post('amueblado', 0),
        'calle_referencia' => trim((string) post('calle_referencia', '')) ?: null,
    ];
}

function validatePropiedadData(array $data): ?string
{
    if ($data['titulo'] === '' || $data['descripcion'] === '') {
        return 'Título y descripción son obligatorios.';
    }

    if (!in_array($data['tipo_inmueble'], ['casa', 'departamento'], true)) {
        return 'Selecciona un tipo de inmueble válido.';
    }

    if ($data['colonia_id'] <= 0) {
        return 'Selecciona una colonia válida.';
    }

    if ($data['precio_mensual'] <= 0) {
        return 'El precio mensual debe ser mayor a cero.';
    }

    if ($data['recamaras'] < 1 || $data['banos'] < 1 || $data['cocinas'] < 1) {
        return 'Recámaras, baños y cocinas deben ser al menos 1.';
    }

    return null;
}

function propiedadFormContext(?int $id = null): string
{
    return $id === null ? 'nueva' : 'editar:' . $id;
}

function propiedadOldInputFromPost(): array
{
    return array_merge(propiedadFromPost(), [
        'alcaldia_id' => (int) post('alcaldia_id', 0),
    ]);
}

function savePropiedadOldInput(array $data, string $context): void
{
    $_SESSION['propiedad_old_input'][$context] = $data;
}

function pullPropiedadOldInput(string $context): array
{
    $data = $_SESSION['propiedad_old_input'][$context] ?? [];
    unset($_SESSION['propiedad_old_input'][$context]);

    return is_array($data) ? $data : [];
}

function clearPropiedadOldInput(string $context): void
{
    unset($_SESSION['propiedad_old_input'][$context]);
}

function redirectPropiedadFormError(string $url, string $message, array $data, string $context): never
{
    savePropiedadOldInput($data, $context);
    flash('error', $message);
    redirect($url);
}
