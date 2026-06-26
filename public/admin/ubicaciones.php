<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

requireAdmin();

$totalAlcaldias = Alcaldia::count();
$totalColonias = Colonia::count();
$mapaColonias = Colonia::mapByAlcaldia();

renderHeader('Ubicaciones');
?>
<section class="card">
    <h1>Ubicaciones en MariaDB</h1>
    <p>
        Alcaldías y colonias se gestionan exclusivamente en la base de datos
        <code>rentas_cdmx</code>.
    </p>

    <div class="stats" style="padding: 1.5rem 0; background: transparent;">
        <div class="container" style="padding: 0;">
            <div class="stat-item">
                <h3><?= $totalAlcaldias ?></h3>
                <p>Alcaldías</p>
            </div>
            <div class="stat-item">
                <h3><?= $totalColonias ?></h3>
                <p>Colonias</p>
            </div>
            <div class="stat-item">
                <h3>BD</h3>
                <p>Fuente única de datos</p>
            </div>
        </div>
    </div>

    <?php if ($totalAlcaldias === 0 || $totalColonias === 0): ?>
        <div class="alert alert-error">
            No hay ubicaciones cargadas. Ejecuta el seed en MariaDB:
            <code>mariadb -u rentas -p rentas_cdmx &lt; sql/seed_colonias.sql</code>
        </div>
    <?php endif; ?>

    <h2>Colonias por alcaldía</h2>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Alcaldía</th>
                    <th>Colonias</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (Alcaldia::all() as $alcaldia): ?>
                    <?php $id = (int) $alcaldia['id']; ?>
                    <tr>
                        <td><?= $id ?></td>
                        <td><?= e($alcaldia['nombre']) ?></td>
                        <td><?= count($mapaColonias[$id] ?? []) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p><a href="<?= e(url('/admin/index.php')) ?>">← Volver a moderación</a></p>
</section>
<?php renderFooter(); ?>
