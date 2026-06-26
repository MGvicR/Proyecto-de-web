<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireComprador();

$user = currentUser();
$contactos = Contacto::byComprador($user['id']);

renderHeader('Mi panel');
?>
<section class="section dashboard-panel">
    <div class="section-header dashboard-header">
        <h1>Panel de comprador</h1>
        <a href="<?= e(url('/propiedades.php')) ?>" class="btn">Buscar propiedades</a>
    </div>

    <p class="meta">Hola, <?= e($user['nombre']) ?>. Aquí puedes ver las propiedades que te interesan y los mensajes que enviaste a los vendedores.</p>

    <h2 class="section-subtitle">Mis consultas enviadas</h2>

    <?php if (!$contactos): ?>
        <p class="empty-state">Aún no has contactado a ningún vendedor. <a href="<?= e(url('/propiedades.php')) ?>">Explora el catálogo</a>.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Propiedad</th>
                        <th>Ubicación</th>
                        <th>Precio</th>
                        <th>Mensaje</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contactos as $contacto): ?>
                        <?php
                        $yaCalifico = (int) $contacto['leido'] === 1
                            && CalificacionVendedor::findByContacto((int) $contacto['id']) !== null;
                        ?>
                        <tr>
                            <td><?= e($contacto['propiedad_titulo']) ?></td>
                            <td><?= e($contacto['colonia']) ?>, <?= e($contacto['alcaldia']) ?></td>
                            <td><?= formatMoney($contacto['precio_mensual']) ?></td>
                            <td><?= e($contacto['mensaje'] ?: '—') ?></td>
                            <td>
                                <span class="badge badge-contacto-<?= (int) $contacto['leido'] === 1 ? 'contestado' : 'pendiente' ?>">
                                    <?= e(contactoEstadoLabel($contacto['leido'])) ?>
                                </span>
                            </td>
                            <td><?= e(date('d/m/Y H:i', strtotime($contacto['creado_en']))) ?></td>
                            <td>
                                <a href="<?= e(url('/propiedad.php?slug=' . urlencode($contacto['propiedad_slug']))) ?>">
                                    <?php if ((int) $contacto['leido'] === 1 && !$yaCalifico): ?>
                                        Calificar
                                    <?php else: ?>
                                        Ver propiedad
                                    <?php endif; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php renderFooter(); ?>
