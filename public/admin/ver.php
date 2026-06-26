<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireAdmin();

$id = (int) get('id', 0);
$prop = Propiedad::findById($id);

if (!$prop) {
    flash('error', 'Propiedad no encontrada.');
    redirect('/admin/index.php');
}

$fotos = Propiedad::fotos($id);
$historial = HistorialModeracion::byPropiedad($id);

renderHeader('Revisar propiedad');
?>
<section class="card add-property-container">
    <h1><?= e($prop['titulo']) ?></h1>
    <p>Estado: <span class="badge badge-<?= e($prop['estado']) ?>"><?= e(estadoLabel($prop['estado'])) ?></span></p>
    <p>Vendedor: <?= e($prop['vendedor_nombre']) ?> (<?= e($prop['vendedor_telefono'] ?? 'sin teléfono') ?>)</p>

    <div class="property-gallery compact">
        <?php if ($fotos): ?>
            <?php foreach ($fotos as $foto): ?>
                <figure class="gallery-item">
                    <img src="<?= e(url('/uploads/propiedades/' . $foto['ruta_archivo'])) ?>"
                         alt="<?= e(fotoEspacioLabel($foto['tipo'] ?? 'recamara', (int) ($foto['numero'] ?? 1))) ?>">
                    <figcaption><?= e(fotoEspacioLabel($foto['tipo'] ?? 'recamara', (int) ($foto['numero'] ?? 1))) ?></figcaption>
                </figure>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-photo">Sin fotos</div>
        <?php endif; ?>
    </div>

    <ul class="features-list">
        <li><strong>Tipo:</strong> <?= e(ucfirst($prop['tipo_inmueble'])) ?></li>
        <li><strong>Ubicación:</strong> <?= e($prop['colonia']) ?>, <?= e($prop['alcaldia']) ?></li>
        <li><strong>Precio:</strong> <?= formatMoney($prop['precio_mensual']) ?> / mes</li>
        <li><strong>Recámaras:</strong> <?= (int) $prop['recamaras'] ?></li>
        <li><strong>Baños:</strong> <?= (int) $prop['banos'] ?></li>
        <li><strong>Cocinas:</strong> <?= (int) $prop['cocinas'] ?></li>
        <li><strong>Estacionamiento:</strong> <?= boolLabel($prop['tiene_estacionamiento']) ?></li>
        <li><strong>Mascotas:</strong> <?= boolLabel($prop['permite_mascotas']) ?></li>
    </ul>

    <h2>Descripción</h2>
    <p><?= nl2br(e($prop['descripcion'])) ?></p>

    <?php if ($prop['estado'] === 'pendiente'): ?>
        <div class="admin-actions">
            <form method="post" action="<?= e(url('/admin/acciones.php')) ?>" class="inline-form">
                <?php renderCsrfField(); ?>
                <input type="hidden" name="id" value="<?= (int) $prop['id'] ?>">
                <input type="hidden" name="accion" value="aprobar">
                <button type="submit" class="btn btn-success">Aprobar</button>
            </form>

            <form method="post" action="<?= e(url('/admin/acciones.php')) ?>" class="inline-form reject-form">
                <?php renderCsrfField(); ?>
                <input type="hidden" name="id" value="<?= (int) $prop['id'] ?>">
                <input type="hidden" name="accion" value="rechazar">
                <div class="form-group">
                    <label for="motivo">Motivo de rechazo *</label>
                    <textarea id="motivo" name="motivo" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-danger">Rechazar</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($prop['estado'] === 'inactiva'): ?>
        <div class="admin-actions">
            <form method="post" action="<?= e(url('/admin/acciones.php')) ?>" class="inline-form"
                  onsubmit="return confirm('¿Dar de alta esta propiedad? Volverá a mostrarse en el catálogo público.');">
                <?php renderCsrfField(); ?>
                <input type="hidden" name="id" value="<?= (int) $prop['id'] ?>">
                <input type="hidden" name="accion" value="dar_alta">
                <button type="submit" class="btn btn-success">Dar de alta</button>
            </form>
        </div>
    <?php elseif (!in_array($prop['estado'], ['inactiva', 'pendiente'], true)): ?>
        <div class="admin-actions">
            <form method="post" action="<?= e(url('/admin/acciones.php')) ?>" class="inline-form reject-form"
                  onsubmit="return confirm('¿Dar de baja esta propiedad? Dejará de mostrarse en el catálogo.');">
                <?php renderCsrfField(); ?>
                <input type="hidden" name="id" value="<?= (int) $prop['id'] ?>">
                <input type="hidden" name="accion" value="dar_baja">
                <div class="form-group">
                    <label for="motivo_baja">Motivo de baja (opcional)</label>
                    <textarea id="motivo_baja" name="motivo" rows="2" maxlength="500"
                              placeholder="Ej. contenido inapropiado, duplicado, solicitud del vendedor…"></textarea>
                </div>
                <button type="submit" class="btn btn-danger">Dar de baja</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($historial): ?>
        <h2>Historial de moderación</h2>
        <ul class="history-list">
            <?php foreach ($historial as $item): ?>
                <li>
                    <strong><?= e($item['accion']) ?></strong>
                    por <?= e($item['admin_nombre']) ?>
                    (<?= e($item['estado_anterior']) ?> → <?= e($item['estado_nuevo']) ?>)
                    — <?= e($item['creado_en']) ?>
                    <?php if ($item['comentario']): ?>
                        <br><em><?= e($item['comentario']) ?></em>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p><a href="<?= e(url('/admin/index.php')) ?>">← Volver a pendientes</a>
       · <a href="<?= e(url('/admin/propiedades.php')) ?>">Todas las propiedades</a></p>
</section>
<?php renderFooter(); ?>
