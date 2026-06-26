<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireAdmin();

$propiedades = Propiedad::todas();

renderHeader('Todas las propiedades');
?>
<section class="section dashboard-panel">
    <div class="section-header dashboard-header">
        <h1>Todas las propiedades</h1>
        <a href="<?= e(url('/admin/index.php')) ?>" class="btn btn-small">Pendientes de revisión</a>
    </div>

    <?php if (!$propiedades): ?>
        <p class="empty-state">No hay propiedades registradas.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Vendedor</th>
                        <th>Ubicación</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($propiedades as $prop): ?>
                        <tr>
                            <td><?= e($prop['titulo']) ?></td>
                            <td><?= e($prop['vendedor_nombre']) ?></td>
                            <td><?= e($prop['colonia']) ?>, <?= e($prop['alcaldia']) ?></td>
                            <td><?= formatMoney($prop['precio_mensual']) ?></td>
                            <td><span class="badge badge-<?= e($prop['estado']) ?>"><?= e(estadoLabel($prop['estado'])) ?></span></td>
                            <td class="actions-cell">
                                <div class="actions">
                                    <a href="<?= e(url('/admin/ver.php?id=' . (int) $prop['id'])) ?>">Ver</a>
                                    <?php if ($prop['estado'] === 'inactiva'): ?>
                                        <form method="post" action="<?= e(url('/admin/acciones.php')) ?>" class="inline-form inline-form-link"
                                              onsubmit="return confirm('¿Dar de alta esta propiedad? Volverá a mostrarse en el catálogo.');">
                                            <?php renderCsrfField(); ?>
                                            <input type="hidden" name="id" value="<?= (int) $prop['id'] ?>">
                                            <input type="hidden" name="accion" value="dar_alta">
                                            <button type="submit" class="link-button">Dar de alta</button>
                                        </form>
                                    <?php elseif (!in_array($prop['estado'], ['pendiente'], true)): ?>
                                        <form method="post" action="<?= e(url('/admin/acciones.php')) ?>" class="inline-form inline-form-link"
                                              onsubmit="return confirm('¿Dar de baja esta propiedad?');">
                                            <?php renderCsrfField(); ?>
                                            <input type="hidden" name="id" value="<?= (int) $prop['id'] ?>">
                                            <input type="hidden" name="accion" value="dar_baja">
                                            <button type="submit" class="link-button action-danger">Dar de baja</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php renderFooter(); ?>
