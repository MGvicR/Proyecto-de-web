<?php

declare(strict_types=1);

$alcaldias = Alcaldia::all();
$prop = $prop ?? [];

$alcaldiaSeleccionada = (int) ($prop['alcaldia_id'] ?? 0);
if ($alcaldiaSeleccionada <= 0 && !empty($prop['colonia_id'])) {
    $coloniaEncontrada = Colonia::find((int) $prop['colonia_id']);
    $alcaldiaSeleccionada = (int) ($coloniaEncontrada['alcaldia_id'] ?? 0);
    if ($alcaldiaSeleccionada > 0) {
        $prop['alcaldia_id'] = $alcaldiaSeleccionada;
    }
}

$colonias = $alcaldiaSeleccionada > 0
    ? Colonia::byAlcaldia($alcaldiaSeleccionada)
    : [];
$coloniasPorAlcaldia = Colonia::mapByAlcaldia();
$formDataRestaurado = $formDataRestaurado ?? false;
?>
<?php if ($formDataRestaurado): ?>
    <p class="hint form-restore-hint">Se conservaron tus datos. Si hubo un problema con las fotos, selecciónalas de nuevo antes de guardar.</p>
<?php endif; ?>
<div class="form-grid">
    <div class="form-group">
        <label for="titulo">Título *</label>
        <input type="text" id="titulo" name="titulo" required maxlength="150"
               value="<?= e($prop['titulo'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label for="tipo_inmueble">Tipo *</label>
        <select id="tipo_inmueble" name="tipo_inmueble" required>
            <option value="">Selecciona</option>
            <option value="casa" <?= ($prop['tipo_inmueble'] ?? '') === 'casa' ? 'selected' : '' ?>>Casa</option>
            <option value="departamento" <?= ($prop['tipo_inmueble'] ?? '') === 'departamento' ? 'selected' : '' ?>>Departamento</option>
        </select>
    </div>

    <div class="form-group">
        <label for="alcaldia_id">Alcaldía *</label>
        <select id="alcaldia_id" name="alcaldia_id" required>
            <option value="">Selecciona</option>
            <?php foreach ($alcaldias as $alcaldia): ?>
                <option value="<?= (int) $alcaldia['id'] ?>"
                    <?= (string) ($prop['alcaldia_id'] ?? '') === (string) $alcaldia['id'] ? 'selected' : '' ?>>
                    <?= e($alcaldia['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="colonia_id">Colonia *</label>
        <select id="colonia_id" name="colonia_id" required>
            <option value="">Selecciona alcaldía primero</option>
            <?php foreach ($colonias as $colonia): ?>
                <option value="<?= (int) $colonia['id'] ?>"
                    <?= (string) ($prop['colonia_id'] ?? '') === (string) $colonia['id'] ? 'selected' : '' ?>>
                    <?= e($colonia['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="precio_mensual">Precio mensual *</label>
        <input type="number" id="precio_mensual" name="precio_mensual" required min="1" step="0.01"
               value="<?= e((string) ($prop['precio_mensual'] ?? '')) ?>">
    </div>

    <div class="form-group">
        <label for="recamaras">Recámaras *</label>
        <select id="recamaras" name="recamaras" required>
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <option value="<?= $i ?>" <?= (int) ($prop['recamaras'] ?? 1) === $i ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="banos">Baños *</label>
        <select id="banos" name="banos" required>
            <?php for ($i = 1; $i <= 10; $i++): ?>
                <option value="<?= $i ?>" <?= (int) ($prop['banos'] ?? 1) === $i ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="cocinas">Cocinas *</label>
        <select id="cocinas" name="cocinas" required>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>" <?= (int) ($prop['cocinas'] ?? 1) === $i ? 'selected' : '' ?>><?= $i ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="tiene_estacionamiento">Estacionamiento *</label>
        <select id="tiene_estacionamiento" name="tiene_estacionamiento" required>
            <option value="0" <?= (int) ($prop['tiene_estacionamiento'] ?? 0) === 0 ? 'selected' : '' ?>>No</option>
            <option value="1" <?= (int) ($prop['tiene_estacionamiento'] ?? 0) === 1 ? 'selected' : '' ?>>Sí</option>
        </select>
    </div>

    <div class="form-group">
        <label for="permite_mascotas">Permite mascotas *</label>
        <select id="permite_mascotas" name="permite_mascotas" required>
            <option value="0" <?= (int) ($prop['permite_mascotas'] ?? 0) === 0 ? 'selected' : '' ?>>No</option>
            <option value="1" <?= (int) ($prop['permite_mascotas'] ?? 0) === 1 ? 'selected' : '' ?>>Sí</option>
        </select>
    </div>

    <div class="form-group">
        <label for="amueblado">Amueblado</label>
        <select id="amueblado" name="amueblado">
            <option value="0" <?= (int) ($prop['amueblado'] ?? 0) === 0 ? 'selected' : '' ?>>No</option>
            <option value="1" <?= (int) ($prop['amueblado'] ?? 0) === 1 ? 'selected' : '' ?>>Sí</option>
        </select>
    </div>

    <div class="form-group full-width">
        <label for="calle_referencia">Calle de referencia</label>
        <input type="text" id="calle_referencia" name="calle_referencia" maxlength="150"
               value="<?= e($prop['calle_referencia'] ?? '') ?>">
    </div>

    <div class="form-group full-width">
        <label for="descripcion">Descripción *</label>
        <textarea id="descripcion" name="descripcion" rows="6" required><?= e($prop['descripcion'] ?? '') ?></textarea>
    </div>

    <div class="form-group full-width fotos-espacios-section">
        <h3>Fotos por espacio *</h3>
        <p class="hint">Sube 1 foto por cada recámara, baño y cocina. Si incluye estacionamiento, agrega 1 foto. Máximo 5 MB por imagen (JPG, PNG o WEBP).</p>
        <div id="fotos-espacios-list" class="fotos-espacios-list"></div>
    </div>
</div>
<script>
window.COLONIAS_POR_ALCALDIA = <?= json_encode(
    $coloniasPorAlcaldia,
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;
window.FOTOS_EXISTENTES = <?= json_encode(
    array_map(
        static fn(array $foto): array => [
            'tipo' => $foto['tipo'],
            'numero' => (int) $foto['numero'],
            'url' => url('/uploads/propiedades/' . $foto['ruta_archivo']),
            'label' => fotoEspacioLabel($foto['tipo'], (int) $foto['numero']),
        ],
        array_values($fotosExistentes ?? [])
    ),
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;
</script>
<script src="<?= e(url('/assets/js/fotos-propiedad.js')) ?>"></script>
