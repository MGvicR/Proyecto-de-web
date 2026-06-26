<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireVendedor();

$user = currentUser();
$mensajes = Contacto::byVendedor($user['id']);

renderHeader('Mensajes recibidos');
?>
<section class="section dashboard-panel">
    <div class="section-header dashboard-header">
        <h1>Mensajes de compradores</h1>
        <a href="<?= e(url('/vendedor/index.php')) ?>" class="btn btn-small">Mis propiedades</a>
    </div>

    <p class="meta">Consultas enviadas desde el formulario de contacto de tus propiedades.</p>

    <?php if (!$mensajes): ?>
        <p class="empty-state">Aún no has recibido mensajes de compradores.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Propiedad</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Mensaje</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mensajes as $msg): ?>
                        <tr>
                            <td><?= e(date('d/m/Y H:i', strtotime($msg['creado_en']))) ?></td>
                            <td><?= e($msg['propiedad_titulo']) ?></td>
                            <td><?= e($msg['nombre_visitante']) ?></td>
                            <td><?= e($msg['telefono']) ?></td>
                            <td><?= e($msg['email'] ?: '—') ?></td>
                            <td><?= e($msg['mensaje'] ?: '—') ?></td>
                            <td class="estado-contacto-cell">
                                <?php if ((int) $msg['leido'] === 1): ?>
                                    <span class="badge badge-contacto-contestado">
                                        <?= e(contactoEstadoLabel($msg['leido'])) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-contacto-pendiente">
                                        <?= e(contactoEstadoLabel($msg['leido'])) ?>
                                    </span>
                                    <form method="post" action="<?= e(url('/vendedor/mensajes-acciones.php')) ?>" class="inline-form inline-form-link"
                                          onsubmit="return confirm('¿Marcar este mensaje como contestado?');">
                                        <?php renderCsrfField(); ?>
                                        <input type="hidden" name="id" value="<?= (int) $msg['id'] ?>">
                                        <button type="submit" class="link-button estado-contacto-link">Contestado</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php renderFooter(); ?>
