<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireAdmin();

$propiedadesPorEstado = Propiedad::countByEstado();
$usuariosPorRol = Usuario::countByRol();
$topVendedores = Propiedad::topVendedores(5);

$totales = [
    'propiedades' => Propiedad::countAll(),
    'propiedades_activas' => $propiedadesPorEstado['activa'] ?? 0,
    'usuarios' => array_sum($usuariosPorRol),
    'usuarios_activos' => Usuario::countActivos(),
    'contactos' => Contacto::countAll(),
    'contactos_pendientes' => Contacto::countPendientes(),
    'calificaciones' => CalificacionVendedor::countAll(),
    'alcaldias' => Alcaldia::count(),
    'colonias' => Colonia::count(),
];

renderHeader('Estadísticas');
?>
<section class="section dashboard-panel">
    <div class="section-header dashboard-header">
        <h1>Estadísticas del sistema</h1>
        <a href="<?= e(url('/admin/usuarios.php')) ?>" class="btn btn-small">Usuarios</a>
    </div>

    <div class="admin-stats-grid">
        <article class="admin-stat-card">
            <h3><?= $totales['propiedades'] ?></h3>
            <p>Propiedades totales</p>
        </article>
        <article class="admin-stat-card">
            <h3><?= $totales['propiedades_activas'] ?></h3>
            <p>Propiedades activas</p>
        </article>
        <article class="admin-stat-card">
            <h3><?= $totales['usuarios'] ?></h3>
            <p>Usuarios registrados</p>
        </article>
        <article class="admin-stat-card">
            <h3><?= $totales['contactos'] ?></h3>
            <p>Consultas enviadas</p>
        </article>
        <article class="admin-stat-card">
            <h3><?= $totales['contactos_pendientes'] ?></h3>
            <p>Consultas pendientes</p>
        </article>
        <article class="admin-stat-card">
            <h3><?= $totales['calificaciones'] ?></h3>
            <p>Calificaciones</p>
        </article>
    </div>

    <div class="admin-stats-panels">
        <section class="card admin-stats-panel">
            <h2>Propiedades por estado</h2>
            <?php if (!$propiedadesPorEstado): ?>
                <p class="empty-state">Sin datos.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Estado</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($propiedadesPorEstado as $estado => $total): ?>
                            <tr>
                                <td><span class="badge badge-<?= e($estado) ?>"><?= e(estadoLabel($estado)) ?></span></td>
                                <td><?= $total ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="card admin-stats-panel">
            <h2>Usuarios por rol</h2>
            <table>
                <thead>
                    <tr><th>Rol</th><th>Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($usuariosPorRol as $rol => $total): ?>
                        <?php if ($total === 0) continue; ?>
                        <tr>
                            <td><?= e(rolLabel($rol)) ?></td>
                            <td><?= $total ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td><strong>Activos</strong></td>
                        <td><strong><?= $totales['usuarios_activos'] ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="card admin-stats-panel">
            <h2>Top vendedores (propiedades)</h2>
            <?php if (!$topVendedores): ?>
                <p class="empty-state">Sin vendedores con propiedades.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Vendedor</th><th>Propiedades</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topVendedores as $vendedor): ?>
                            <tr>
                                <td><?= e(vendedorDisplayName($vendedor)) ?></td>
                                <td><?= (int) $vendedor['total_propiedades'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="card admin-stats-panel">
            <h2>Cobertura geográfica</h2>
            <table>
                <tbody>
                    <tr><td>Alcaldías</td><td><?= $totales['alcaldias'] ?></td></tr>
                    <tr><td>Colonias</td><td><?= $totales['colonias'] ?></td></tr>
                </tbody>
            </table>
        </section>
    </div>
</section>
<?php renderFooter(); ?>
