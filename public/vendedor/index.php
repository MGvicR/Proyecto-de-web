<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once BASE_PATH . '/app/helpers/propiedad_form.php';

requireVendedor();

$user = currentUser();
$propiedades = Propiedad::byVendedor($user['id']);

renderHeader('Mis propiedades');
?>
<section class="section dashboard-panel">
    <div class="section-header dashboard-header">
        <h1>Mis propiedades</h1>
        <div class="dashboard-header-actions">
            <a href="<?= e(url('/vendedor/mensajes.php')) ?>" class="btn btn-small">Mensajes</a>
            <a href="<?= e(url('/vendedor/nueva.php')) ?>" class="btn">Nueva propiedad</a>
        </div>
    </div>

    <?php if (!$propiedades): ?>
        <p class="empty-state">Aún no has publicado propiedades.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Ubicación</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Motivo / acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($propiedades as $prop): ?>
                        <tr class="<?= $prop['estado'] === 'rechazada' ? 'row-rechazada' : '' ?>">
                            <td><?= e($prop['titulo']) ?></td>
                            <td><?= e($prop['colonia']) ?>, <?= e($prop['alcaldia']) ?></td>
                            <td><?= formatMoney($prop['precio_mensual']) ?></td>
                            <td><span class="badge badge-<?= e($prop['estado']) ?>"><?= e(estadoLabel($prop['estado'])) ?></span></td>
                            <td class="actions-cell">
                                <?php if ($prop['estado'] === 'rechazada' && !empty($prop['motivo_rechazo'])): ?>
                                    <div class="motivo-rechazo-box">
                                        <strong>Motivo de rechazo:</strong>
                                        <p><?= e($prop['motivo_rechazo']) ?></p>
                                    </div>
                                <?php endif; ?>
                                <div class="actions">
                                    <a href="<?= e(url('/vendedor/editar.php?id=' . (int) $prop['id'])) ?>">Editar</a>
                                    <?php if (in_array($prop['estado'], ['borrador', 'rechazada'], true)): ?>
                                        <form method="post" action="<?= e(url('/vendedor/acciones.php')) ?>" class="inline-form inline-form-link">
                                            <?php renderCsrfField(); ?>
                                            <input type="hidden" name="id" value="<?= (int) $prop['id'] ?>">
                                            <input type="hidden" name="accion" value="enviar">
                                            <button type="submit" class="link-button">Enviar a revisión</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($prop['estado'] === 'activa'): ?>
                                        <form method="post" action="<?= e(url('/vendedor/acciones.php')) ?>" class="inline-form inline-form-link">
                                            <?php renderCsrfField(); ?>
                                            <input type="hidden" name="id" value="<?= (int) $prop['id'] ?>">
                                            <input type="hidden" name="accion" value="rentada">
                                            <button type="submit" class="link-button">Marcar rentada</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($prop['estado'] !== 'inactiva'): ?>
                                        <form method="post" action="<?= e(url('/vendedor/acciones.php')) ?>" class="inline-form inline-form-link"
                                              onsubmit="return confirm('¿Dar de baja esta propiedad? Dejará de mostrarse en el catálogo.');">
                                            <?php renderCsrfField(); ?>
                                            <input type="hidden" name="id" value="<?= (int) $prop['id'] ?>">
                                            <input type="hidden" name="accion" value="baja">
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
