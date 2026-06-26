<?php

declare(strict_types=1);

$alcaldias = Alcaldia::all();
$vendedores = Usuario::vendedoresConPropiedadesActivas();
$categorias = propiedadCategoriasBusqueda();
$coloniaId = get('colonia_id', '');
$alcaldiaId = get('alcaldia_id', '');

if ($coloniaId && !$alcaldiaId) {
    $alcaldiaIdEncontrada = Colonia::alcaldiaIdByColonia((int) $coloniaId);
    if ($alcaldiaIdEncontrada !== null) {
        $alcaldiaId = (string) $alcaldiaIdEncontrada;
    }
}

$selected = [
    'alcaldia_id' => $alcaldiaId,
    'colonia_id' => $coloniaId,
    'tipo_inmueble' => get('tipo_inmueble', ''),
    'categoria' => get('categoria', ''),
    'vendedor_id' => get('vendedor_id', ''),
    'publicado_desde' => get('publicado_desde', ''),
    'publicado_hasta' => get('publicado_hasta', ''),
    'recamaras' => get('recamaras', ''),
    'banos' => get('banos', ''),
    'cocinas' => get('cocinas', ''),
    'tiene_estacionamiento' => get('tiene_estacionamiento', ''),
    'permite_mascotas' => get('permite_mascotas', ''),
    'amueblado' => get('amueblado', ''),
    'valoracion_min' => get('valoracion_min', ''),
];
$coloniasPorAlcaldia = Colonia::mapByAlcaldia();
$coloniasIniciales = $alcaldiaId !== '' ? ($coloniasPorAlcaldia[(int) $alcaldiaId] ?? []) : [];
$variant = $variant ?? 'default';
$formClass = match ($variant) {
    'hero' => 'filtros-form filtros-form-hero main-search',
    'sidebar' => 'filtros-form filtros-form-sidebar',
    default => 'filtros-form',
};
$showExtendedFilters = $variant !== 'hero';
$publicadoDesdeValor = (string) $selected['publicado_desde'];
$publicadoHastaValor = (string) $selected['publicado_hasta'];
?>
<form class="<?= e($formClass) ?>" method="get" action="<?= e($action ?? url('/propiedades.php')) ?>">
    <?php if ($variant === 'sidebar'): ?>
        <div class="filter-group">
            <h4>Alcaldía</h4>
    <?php else: ?>
    <div class="filtros-grid">
    <?php endif; ?>

        <div class="form-group">
            <?php if ($variant !== 'sidebar'): ?>
            <label for="alcaldia_id">Alcaldía</label>
            <?php endif; ?>
            <select name="alcaldia_id" id="alcaldia_id" class="<?= $variant === 'sidebar' ? 'filter-select' : '' ?>">
                <option value="">Todas</option>
                <?php foreach ($alcaldias as $alcaldia): ?>
                    <option value="<?= (int) $alcaldia['id'] ?>" <?= (string) $selected['alcaldia_id'] === (string) $alcaldia['id'] ? 'selected' : '' ?>>
                        <?= e($alcaldia['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    <?php if ($variant === 'sidebar'): ?>
        </div>
        <div class="filter-group">
            <h4>Colonia</h4>
    <?php endif; ?>

        <div class="form-group">
            <?php if ($variant !== 'sidebar' && $variant !== 'hero'): ?>
            <label for="colonia_id">Colonia</label>
            <?php elseif ($variant === 'hero'): ?>
            <label for="colonia_id">Colonia</label>
            <?php endif; ?>
            <select name="colonia_id" id="colonia_id" data-filtro="1" class="<?= $variant === 'sidebar' ? 'filter-select' : '' ?>">
                <option value="">Todas</option>
                <?php foreach ($coloniasIniciales as $colonia): ?>
                    <option value="<?= (int) $colonia['id'] ?>" <?= (string) $selected['colonia_id'] === (string) $colonia['id'] ? 'selected' : '' ?>>
                        <?= e($colonia['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    <?php if ($variant === 'sidebar'): ?>
        </div>
        <div class="filter-group">
            <h4>Tipo de inmueble</h4>
    <?php endif; ?>

        <div class="form-group">
            <?php if ($variant === 'default'): ?>
            <label for="tipo_inmueble">Tipo</label>
            <?php elseif ($variant === 'hero'): ?>
            <label for="tipo_inmueble">Tipo</label>
            <?php endif; ?>
            <select name="tipo_inmueble" id="tipo_inmueble" class="<?= $variant === 'sidebar' ? 'filter-select' : '' ?>">
                <option value="">Todos</option>
                <option value="casa" <?= $selected['tipo_inmueble'] === 'casa' ? 'selected' : '' ?>>Casa</option>
                <option value="departamento" <?= $selected['tipo_inmueble'] === 'departamento' ? 'selected' : '' ?>>Departamento</option>
            </select>
        </div>

    <?php if ($variant === 'sidebar'): ?>
        </div>
        <div class="filter-group">
            <h4>Categoría</h4>
    <?php endif; ?>

        <div class="form-group">
            <?php if ($variant !== 'sidebar'): ?>
            <label for="categoria">Categoría</label>
            <?php endif; ?>
            <select name="categoria" id="categoria" class="<?= $variant === 'sidebar' ? 'filter-select' : '' ?>">
                <option value="">Todas</option>
                <?php foreach ($categorias as $slug => $label): ?>
                    <option value="<?= e($slug) ?>" <?= $selected['categoria'] === $slug ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    <?php if ($variant === 'sidebar'): ?>
        </div>
        <div class="filter-group">
            <h4>Agente inmobiliario</h4>
    <?php endif; ?>

        <div class="form-group">
            <?php if ($variant !== 'sidebar'): ?>
            <label for="vendedor_id">Agente</label>
            <?php endif; ?>
            <select name="vendedor_id" id="vendedor_id" class="<?= $variant === 'sidebar' ? 'filter-select' : '' ?>">
                <option value="">Todos</option>
                <?php foreach ($vendedores as $vendedor): ?>
                    <option value="<?= (int) $vendedor['id'] ?>" <?= (string) $selected['vendedor_id'] === (string) $vendedor['id'] ? 'selected' : '' ?>>
                        <?= e(vendedorDisplayName($vendedor)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    <?php if ($variant === 'sidebar'): ?>
        </div>
        <div class="filter-group">
            <h4>Fecha de publicación</h4>
        </div>
        <div class="form-group">
            <label for="publicado_desde">Desde</label>
            <input type="date" name="publicado_desde" id="publicado_desde" class="filter-select"
                   value="<?= e($publicadoDesdeValor) ?>">
        </div>
        <div class="form-group">
            <label for="publicado_hasta">Hasta</label>
            <input type="date" name="publicado_hasta" id="publicado_hasta" class="filter-select"
                   value="<?= e($publicadoHastaValor) ?>">
        </div>
        <div class="filter-group">
            <h4>Recámaras</h4>
    <?php endif; ?>

        <div class="form-group">
            <?php if ($variant === 'default'): ?>
            <label for="recamaras">Recámaras</label>
            <?php elseif ($variant === 'hero'): ?>
            <label for="recamaras">Recámaras</label>
            <?php endif; ?>
            <select name="recamaras" id="recamaras" class="<?= $variant === 'sidebar' ? 'filter-select' : '' ?>">
                <option value="">Cualquiera</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?= (string) $selected['recamaras'] === (string) $i ? 'selected' : '' ?>><?= $i ?>+</option>
                <?php endfor; ?>
            </select>
        </div>

    <?php if ($showExtendedFilters): ?>
        <?php if ($variant === 'sidebar'): ?>
        <div class="filter-group">
            <h4>Baños</h4>
        <?php endif; ?>
        <div class="form-group">
            <?php if ($variant === 'default'): ?>
            <label for="banos">Baños</label>
            <?php endif; ?>
            <select name="banos" id="banos" class="<?= $variant === 'sidebar' ? 'filter-select' : '' ?>">
                <option value="">Cualquiera</option>
                <?php for ($i = 1; $i <= 4; $i++): ?>
                    <option value="<?= $i ?>" <?= (string) $selected['banos'] === (string) $i ? 'selected' : '' ?>><?= $i ?>+</option>
                <?php endfor; ?>
            </select>
        </div>

        <?php if ($variant === 'sidebar'): ?>
        </div>
        <div class="filter-group">
            <h4>Cocinas</h4>
        <?php endif; ?>
        <div class="form-group">
            <?php if ($variant === 'default'): ?>
            <label for="cocinas">Cocinas</label>
            <?php endif; ?>
            <select name="cocinas" id="cocinas" class="<?= $variant === 'sidebar' ? 'filter-select' : '' ?>">
                <option value="">Cualquiera</option>
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <option value="<?= $i ?>" <?= (string) $selected['cocinas'] === (string) $i ? 'selected' : '' ?>><?= $i ?>+</option>
                <?php endfor; ?>
            </select>
        </div>

        <?php if ($variant === 'sidebar'): ?>
        </div>
        <div class="filter-group">
            <h4>Estacionamiento</h4>
        <?php endif; ?>
        <div class="form-group">
            <?php if ($variant === 'default'): ?>
            <label for="tiene_estacionamiento">Estacionamiento</label>
            <?php endif; ?>
            <select name="tiene_estacionamiento" id="tiene_estacionamiento" class="<?= $variant === 'sidebar' ? 'filter-select' : '' ?>">
                <option value="">Todos</option>
                <option value="1" <?= $selected['tiene_estacionamiento'] === '1' ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= $selected['tiene_estacionamiento'] === '0' ? 'selected' : '' ?>>No</option>
            </select>
        </div>

        <?php if ($variant === 'sidebar'): ?>
        </div>
        <div class="filter-group">
            <h4>Mascotas</h4>
        <?php endif; ?>
        <div class="form-group">
            <?php if ($variant === 'default'): ?>
            <label for="permite_mascotas">Mascotas</label>
            <?php endif; ?>
            <select name="permite_mascotas" id="permite_mascotas" class="<?= $variant === 'sidebar' ? 'filter-select' : '' ?>">
                <option value="">Todos</option>
                <option value="1" <?= $selected['permite_mascotas'] === '1' ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= $selected['permite_mascotas'] === '0' ? 'selected' : '' ?>>No</option>
            </select>
        </div>
        <?php if ($variant === 'sidebar'): ?>
        </div>
        <div class="filter-group">
            <h4>Amueblado</h4>
        <?php endif; ?>
        <div class="form-group">
            <?php if ($variant === 'default'): ?>
            <label for="amueblado">Amueblado</label>
            <?php endif; ?>
            <select name="amueblado" id="amueblado" class="<?= $variant === 'sidebar' ? 'filter-select' : '' ?>">
                <option value="">Todos</option>
                <option value="1" <?= $selected['amueblado'] === '1' ? 'selected' : '' ?>>Sí</option>
                <option value="0" <?= $selected['amueblado'] === '0' ? 'selected' : '' ?>>No</option>
            </select>
        </div>
        <?php if ($variant === 'sidebar'): ?>
        </div>
        <div class="filter-group">
            <h4>Valoración del vendedor</h4>
        <?php endif; ?>
        <div class="form-group">
            <?php if ($variant === 'default'): ?>
            <label for="valoracion_min">Valoración mínima</label>
            <?php endif; ?>
            <select name="valoracion_min" id="valoracion_min" class="<?= $variant === 'sidebar' ? 'filter-select' : '' ?>">
                <option value="">Cualquiera</option>
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <option value="<?= $i ?>" <?= (string) $selected['valoracion_min'] === (string) $i ? 'selected' : '' ?>>
                        <?= e(str_repeat('★', $i) . str_repeat('☆', 5 - $i)) ?> (<?= $i ?>+)
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <?php if ($variant === 'sidebar'): ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($variant !== 'sidebar'): ?>
    </div>
    <?php endif; ?>

    <?php if ($variant === 'hero'): ?>
        <button type="submit" class="btn-search">Buscar</button>
    <?php elseif ($variant === 'sidebar'): ?>
        <button type="submit" class="btn-apply-filters">Aplicar filtros</button>
        <a href="<?= e(url('/propiedades.php')) ?>" class="btn-clear-filters">Limpiar</a>
    <?php else: ?>
        <button type="submit" class="btn">Buscar</button>
    <?php endif; ?>
</form>
<script>
window.COLONIAS_POR_ALCALDIA = <?= json_encode($coloniasPorAlcaldia, JSON_UNESCAPED_UNICODE) ?>;

(function () {
    var desde = document.getElementById('publicado_desde');
    var hasta = document.getElementById('publicado_hasta');
    if (!desde || !hasta) {
        return;
    }

    desde.addEventListener('change', function () {
        if (desde.value !== '') {
            hasta.min = desde.value;
            if (hasta.value !== '' && hasta.value < desde.value) {
                hasta.value = '';
            }
        } else {
            hasta.removeAttribute('min');
        }
    });
})();
</script>
