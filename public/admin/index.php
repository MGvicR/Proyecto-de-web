<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireAdmin();

$pendientes = Propiedad::pendientes();

renderHeader('Moderación');
?>
<section class="section dashboard-panel">
    <div class="section-header dashboard-header">
        <h1>Propiedades pendientes de revisión</h1>
        <div class="dashboard-header-actions">
            <a href="<?= e(url('/admin/estadisticas.php')) ?>" class="btn btn-small">Estadísticas</a>
            <a href="<?= e(url('/admin/propiedades.php')) ?>" class="btn btn-small">Todas las propiedades</a>
        </div>
    </div>

    <?php if (!$pendientes): ?>
        <p class="empty-state">No hay propiedades pendientes.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Vendedor</th>
                        <th>Ubicación</th>
                        <th>Precio</th>
                        <th>Enviada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendientes as $prop): ?>
                        <tr>
                            <td><?= e($prop['titulo']) ?></td>
                            <td><?= e($prop['vendedor_nombre']) ?><br><small><?= e($prop['vendedor_email']) ?></small></td>
                            <td><?= e($prop['colonia']) ?>, <?= e($prop['alcaldia']) ?></td>
                            <td><?= formatMoney($prop['precio_mensual']) ?></td>
                            <td><?= e($prop['creado_en']) ?></td>
                            <td><a href="<?= e(url('/admin/ver.php?id=' . (int) $prop['id'])) ?>" class="btn btn-small">Revisar</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php renderFooter(); ?>
